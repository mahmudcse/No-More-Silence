<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Reports\MarkDuplicate;
use App\Actions\Reports\RejectReport;
use App\Actions\Reports\RequestInfo;
use App\Actions\Reports\VerifyReport;
use App\Enums\ReportCategory;
use App\Enums\VerificationCheck;
use App\Enums\WorkflowStatus;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportLock;
use BackedEnum;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class ReviewQueue extends Page
{
    protected static ?string $navigationLabel = 'Review Queue';

    protected static ?string $title = 'Moderation Review Queue';

    protected static \UnitEnum|string|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected string $view = 'filament.pages.review-queue';

    public ?int $selectedReportId = null;

    /** @var array<string, bool> */
    public array $checklist = [];

    public ?int $facebook_profile_id = null;

    public ?int $incident_id = null;

    public ?string $category = null;

    public ?int $severity = 2;

    public ?string $comment_text = null;

    public ?string $rejection_reason = null;

    public ?int $duplicate_of_id = null;

    public ?string $review_note = null;

    public bool $isLockedByOther = false;

    public ?string $lockedByName = null;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin')
            || $user->can('verify_report')
            || $user->can('triage_report');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Report::query()
            ->whereIn('workflow_status', [
                WorkflowStatus::Submitted,
                WorkflowStatus::Triaged,
                WorkflowStatus::InVerification,
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public function mount(?int $report = null): void
    {
        $this->resetChecklist();

        if ($report !== null) {
            $this->selectReport($report);
        } else {
            $this->loadNextInQueue();
        }
    }

    public function selectReport(int $id): void
    {
        /** @var User|null $admin */
        $admin = auth()->user();
        $reportLock = app(ReportLock::class);

        // Release old lock if switching
        if ($this->selectedReportId !== null && $this->selectedReportId !== $id && $admin !== null) {
            $prev = Report::find($this->selectedReportId);
            if ($prev !== null) {
                $reportLock->release($prev, $admin);
            }
        }

        $report = Report::with(['facebookProfile', 'incident', 'media'])->find($id);

        if (! $report) {
            $this->selectedReportId = null;

            return;
        }

        $this->selectedReportId = $report->id;
        $this->facebook_profile_id = $report->facebook_profile_id;
        $this->incident_id = $report->incident_id;
        $this->category = $report->category?->value;
        $this->severity = $report->severity ?? 2;
        $this->comment_text = $report->comment_text;
        $this->review_note = null;
        $this->rejection_reason = null;
        $this->duplicate_of_id = null;
        $this->resetChecklist();

        if ($admin !== null) {
            if ($reportLock->isLockedForOthers($report, $admin)) {
                $this->isLockedByOther = true;
                $holderId = $reportLock->holderId($report);
                $holder = $holderId ? User::find($holderId) : null;
                $this->lockedByName = $holder !== null ? $holder->name : "Admin #{$holderId}";
            } else {
                $this->isLockedByOther = false;
                $this->lockedByName = null;
                $reportLock->acquire($report, $admin);
            }
        }
    }

    public function loadNextInQueue(): void
    {
        $next = Report::query()
            ->whereIn('workflow_status', [
                WorkflowStatus::InVerification,
                WorkflowStatus::Triaged,
                WorkflowStatus::Submitted,
            ])
            ->orderByRaw("CASE workflow_status
                WHEN 'in_verification' THEN 1
                WHEN 'triaged' THEN 2
                WHEN 'submitted' THEN 3
                ELSE 4 END")
            ->orderBy('created_at', 'asc')
            ->first();

        if ($next) {
            $this->selectReport($next->id);
        } else {
            $this->selectedReportId = null;
        }
    }

    public function verify(): void
    {
        if ($this->isLockedByOther || ! $this->selectedReportId) {
            return;
        }

        /** @var User $admin */
        $admin = auth()->user();
        $report = Report::findOrFail($this->selectedReportId);

        // Save current updates to report before verification
        $report->facebook_profile_id = $this->facebook_profile_id;
        $report->incident_id = $this->incident_id;
        $report->category = $this->category ? ReportCategory::from($this->category) : null;
        $report->severity = $this->severity;
        $report->comment_text = $this->comment_text;
        $report->save();

        try {
            app(VerifyReport::class)->handle(
                report: $report,
                admin: $admin,
                checklist: $this->checklist,
                note: $this->review_note
            );

            app(ReportLock::class)->release($report, $admin);

            Notification::make()
                ->title('Report verified successfully')
                ->success()
                ->send();

            $this->loadNextInQueue();
        } catch (DomainException $e) {
            Notification::make()
                ->title('Verification failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function reject(): void
    {
        if ($this->isLockedByOther || ! $this->selectedReportId) {
            return;
        }

        if (blank($this->rejection_reason)) {
            Notification::make()
                ->title('Rejection reason required')
                ->danger()
                ->send();

            return;
        }

        /** @var User $admin */
        $admin = auth()->user();
        $report = Report::findOrFail($this->selectedReportId);

        try {
            app(RejectReport::class)->handle(
                report: $report,
                admin: $admin,
                reason: $this->rejection_reason,
                note: $this->review_note
            );

            app(ReportLock::class)->release($report, $admin);

            Notification::make()
                ->title('Report rejected')
                ->warning()
                ->send();

            $this->loadNextInQueue();
        } catch (DomainException $e) {
            Notification::make()
                ->title('Rejection failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function requestInfo(): void
    {
        if ($this->isLockedByOther || ! $this->selectedReportId) {
            return;
        }

        /** @var User $admin */
        $admin = auth()->user();
        $report = Report::findOrFail($this->selectedReportId);

        try {
            app(RequestInfo::class)->handle(
                report: $report,
                admin: $admin,
                note: $this->review_note
            );

            app(ReportLock::class)->release($report, $admin);

            Notification::make()
                ->title('Report moved to Needs Info')
                ->info()
                ->send();

            $this->loadNextInQueue();
        } catch (DomainException $e) {
            Notification::make()
                ->title('Action failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markDuplicate(): void
    {
        if ($this->isLockedByOther || ! $this->selectedReportId) {
            return;
        }

        if (! $this->duplicate_of_id) {
            Notification::make()
                ->title('Original report selection required')
                ->danger()
                ->send();

            return;
        }

        /** @var User $admin */
        $admin = auth()->user();
        $report = Report::findOrFail($this->selectedReportId);
        $original = Report::findOrFail($this->duplicate_of_id);

        try {
            app(MarkDuplicate::class)->handle(
                report: $report,
                original: $original,
                admin: $admin,
                note: $this->review_note
            );

            app(ReportLock::class)->release($report, $admin);

            Notification::make()
                ->title('Report marked as duplicate')
                ->success()
                ->send();

            $this->loadNextInQueue();
        } catch (DomainException $e) {
            Notification::make()
                ->title('Action failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getSelectedReportProperty(): ?Report
    {
        if (! $this->selectedReportId) {
            return null;
        }

        return Report::with(['facebookProfile', 'incident', 'media'])->find($this->selectedReportId);
    }

    /**
     * @return Collection<int, Report>
     */
    public function getPendingReportsProperty(): Collection
    {
        return Report::query()
            ->whereIn('workflow_status', [
                WorkflowStatus::InVerification,
                WorkflowStatus::Triaged,
                WorkflowStatus::Submitted,
            ])
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();
    }

    /**
     * @return Collection<int, FacebookProfile>
     */
    public function getProfilesProperty(): Collection
    {
        return FacebookProfile::query()->orderBy('current_display_name')->limit(50)->get();
    }

    /**
     * @return Collection<int, Incident>
     */
    public function getIncidentsProperty(): Collection
    {
        return Incident::query()->orderBy('occurred_on', 'desc')->limit(50)->get();
    }

    private function resetChecklist(): void
    {
        $this->checklist = [];
        foreach (VerificationCheck::cases() as $check) {
            $this->checklist[$check->value] = false;
        }
    }
}
