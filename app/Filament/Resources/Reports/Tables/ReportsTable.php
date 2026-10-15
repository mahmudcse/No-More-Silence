<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reports\Tables;

use App\Actions\Reports\PublishReport;
use App\Actions\Reports\RejectReport;
use App\Actions\Reports\RequestInfo;
use App\Actions\Reports\RetractReport;
use App\Actions\Reports\VerifyReport;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workflow_status')->badge()->sortable(),
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('visibility')->badge(),
                IconColumn::make('is_hidden_from_homepage')
                    ->label('Homepage')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn (Report $record): string => $record->is_hidden_from_homepage ? 'Hidden from homepage' : 'Visible on homepage'),
                TextColumn::make('comment_text')->limit(60)->searchable(),
                TextColumn::make('facebookProfile.current_display_name')->label('Profile'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('workflow_status')
                    ->label('Status')
                    ->options(self::enumOptions(WorkflowStatus::cases())),
                SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options(self::enumOptions(Visibility::cases())),
                SelectFilter::make('is_hidden_from_homepage')
                    ->label('Homepage Visibility')
                    ->options([
                        '0' => 'Visible on homepage',
                        '1' => 'Hidden from homepage',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('request_info')
                    ->label('Request info')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::InVerification)
                    ->action(fn (Report $record) => app(RequestInfo::class)->handle($record, auth()->user())),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->form([
                        TextInput::make('reason')->label('Reason')->required(),
                    ])
                    ->visible(fn (Report $record): bool => in_array($record->workflow_status, [WorkflowStatus::Triaged, WorkflowStatus::InVerification], true))
                    ->action(function (array $data, Report $record): void {
                        app(RejectReport::class)->handle($record, auth()->user(), $data['reason']);
                    }),
                Action::make('verify')
                    ->label('Verify')
                    ->color('success')
                    ->form([
                        Checkbox::make('checklist.permalink_or_corroborated')->label('Authentic evidence (permalink exists or corroborated)'),
                        Checkbox::make('checklist.profile_match')->label('Profile matches comment/post author'),
                        Checkbox::make('checklist.redaction_done')->label('Privacy redacted (victim, bystander, reporter)'),
                        Checkbox::make('checklist.not_minor')->label('Not a minor'),
                    ])
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::InVerification)
                    ->action(function (array $data, Report $record): void {
                        app(VerifyReport::class)->handle($record, auth()->user(), $data['checklist']);
                    }),
                Action::make('hide_from_homepage')
                    ->label('Hide from Homepage')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Hide report from homepage?')
                    ->modalDescription('This report will be hidden from the homepage feed. You can publish or restore it to the homepage at any time.')
                    ->visible(fn (Report $record): bool => ! $record->is_hidden_from_homepage)
                    ->action(function (Report $record): void {
                        $record->update(['is_hidden_from_homepage' => true]);
                        Notification::make()
                            ->title('Report hidden from homepage')
                            ->warning()
                            ->send();
                    }),
                Action::make('publish_to_homepage')
                    ->label('Publish to Homepage')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publish report to homepage?')
                    ->modalDescription('This report will become visible in the homepage feed.')
                    ->visible(fn (Report $record): bool => (bool) $record->is_hidden_from_homepage)
                    ->action(function (Report $record): void {
                        $record->update(['is_hidden_from_homepage' => false]);
                        Notification::make()
                            ->title('Report published to homepage')
                            ->success()
                            ->send();
                    }),
                Action::make('publish')
                    ->label('Publish')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::Verified && $record->visibility === Visibility::Hidden)
                    ->action(function (Report $record): void {
                        app(PublishReport::class)->handle($record, auth()->user(), Visibility::Public);
                        $record->update(['is_hidden_from_homepage' => false]);
                    }),
                Action::make('unpublish')
                    ->label('Unpublish')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->modalHeading('Unpublish Report?')
                    ->modalDescription('This report will revert to hidden visibility. You can publish it again later.')
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::Verified && $record->visibility !== Visibility::Hidden)
                    ->action(function (Report $record): void {
                        $record->forceFill([
                            'visibility' => Visibility::Hidden,
                            'is_hidden_from_homepage' => true,
                        ])->save();
                        Notification::make()
                            ->title('Report unpublished')
                            ->info()
                            ->send();
                    }),
                Action::make('retract')
                    ->label('Retract')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::Verified && $record->visibility !== Visibility::Hidden)
                    ->action(fn (Report $record) => app(RetractReport::class)->handle($record, auth()->user())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<int, WorkflowStatus|Visibility>  $cases
     * @return array<string, string>
     */
    private static function enumOptions(array $cases): array
    {
        return collect($cases)
            ->mapWithKeys(fn (object $case): array => [$case->value => $case->label()])
            ->all();
    }
}
