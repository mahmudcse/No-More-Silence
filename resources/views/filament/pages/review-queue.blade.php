<x-filament-panels::page>
    <style>
        .rq-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .rq-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .rq-topbar-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1 1 300px;
        }
        .rq-topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .rq-layout-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (min-width: 1024px) {
            .rq-layout-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        .rq-col {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .rq-field-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .rq-field-group-2col {
                grid-template-columns: 1fr 1fr;
            }
        }
        .rq-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.375rem;
            color: var(--gray-700, #374151);
        }
        :is(.dark) .rq-label {
            color: var(--gray-300, #d1d5db);
        }
        .rq-evidence-card {
            border-radius: 0.75rem;
            padding: 0.75rem;
            border: 1px solid rgba(156, 163, 175, 0.25);
            background: rgba(156, 163, 175, 0.05);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .rq-evidence-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .rq-evidence-img-wrap {
            border-radius: 0.5rem;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(156, 163, 175, 0.2);
            text-align: center;
        }
        .rq-evidence-img {
            max-height: 480px;
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
        }
        .rq-check-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .rq-check-item:hover {
            background: rgba(156, 163, 175, 0.1);
        }
        .rq-check-text {
            font-size: 0.8125rem;
            line-height: 1.25rem;
            color: var(--gray-700, #374151);
        }
        :is(.dark) .rq-check-text {
            color: var(--gray-300, #d1d5db);
        }
        .rq-actions-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }
        .rq-sub-actions-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(156, 163, 175, 0.2);
        }
        @media (min-width: 640px) {
            .rq-sub-actions-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        .rq-url-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.8125rem;
        }
    </style>

    <div class="rq-container">
        @if ($isLockedByOther)
            <x-filament::callout color="danger" icon="heroicon-o-lock-closed">
                <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 1rem;">
                    <span>This report is currently being reviewed by <strong>{{ $lockedByName }}</strong>. Action buttons are disabled to prevent duplicate reviews.</span>
                    <x-filament::button wire:click="loadNextInQueue" color="danger" size="sm">
                        Skip to Next
                    </x-filament::button>
                </div>
            </x-filament::callout>
        @endif

        @if (! $this->selectedReport)
            <x-filament::empty-state
                icon="heroicon-o-check-circle"
                heading="Review Queue is Empty"
                description="All submitted reports have been triaged or verified."
            />
        @else
            <!-- Top Queue Navigation -->
            <x-filament::section compact>
                <div class="rq-topbar">
                    <div class="rq-topbar-left">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.7;">Queue:</span>
                        <div style="min-width: 260px; max-width: 480px; flex: 1;">
                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:change="selectReport($event.target.value)">
                                    @foreach ($this->pendingReports as $pending)
                                        <option value="{{ $pending->id }}" @selected($pending->id === $selectedReportId)>
                                            #{{ $pending->id }}: {{ $pending->category?->label() ?? 'Uncategorized' }} [{{ $pending->workflow_status->value }}]
                                        </option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    <div class="rq-topbar-right">
                        @if ($this->selectedReport->is_hidden_from_homepage)
                            <x-filament::button
                                wire:click="toggleHomepageVisibility"
                                color="warning"
                                size="xs"
                                icon="heroicon-m-eye-slash">
                                Hidden from Homepage
                            </x-filament::button>
                        @else
                            <x-filament::button
                                wire:click="toggleHomepageVisibility"
                                color="gray"
                                size="xs"
                                icon="heroicon-m-eye">
                                Visible on Homepage
                            </x-filament::button>
                        @endif
                        <span style="font-family: monospace; font-size: 0.75rem; opacity: 0.6;">UUID: {{ $this->selectedReport->uuid }}</span>
                        <x-filament::button wire:click="loadNextInQueue" color="gray" size="sm" icon="heroicon-m-arrow-right" icon-position="after">
                            Next Report
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>

            <!-- 2-Column Workstation Grid -->
            <div class="rq-layout-grid">
                <!-- Left Column: Evidence & URLs -->
                <div class="rq-col">
                    <!-- Submitted Evidence Gallery -->
                    <x-filament::section heading="Submitted Evidence" icon="heroicon-o-photo">
                        <x-slot name="headerEnd">
                            <x-filament::badge color="gray">
                                {{ $this->selectedReport->getMedia('original')->count() }} image(s)
                            </x-filament::badge>
                        </x-slot>

                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            @forelse ($this->selectedReport->getMedia('original') as $media)
                                @php
                                    $evidenceType = $media->getCustomProperty('evidence_type', 'comment');
                                    $badgeConfig = match($evidenceType) {
                                        'post' => ['label' => 'Original Post', 'color' => 'info'],
                                        'comment' => ['label' => 'Offensive Comment', 'color' => 'danger'],
                                        'profile' => ['label' => 'Commenter Profile', 'color' => 'warning'],
                                        default => ['label' => 'Other Evidence', 'color' => 'gray'],
                                    };
                                @endphp
                                <div class="rq-evidence-card">
                                    <div class="rq-evidence-header">
                                        <x-filament::badge :color="$badgeConfig['color']">
                                            {{ $badgeConfig['label'] }}
                                        </x-filament::badge>
                                        <span style="font-size: 0.75rem; font-family: monospace; opacity: 0.6;">{{ $media->file_name }}</span>
                                    </div>
                                    <div class="rq-evidence-img-wrap">
                                        <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener noreferrer" title="Click to view full size">
                                            <img src="{{ $media->getUrl() }}" alt="Evidence screenshot" class="rq-evidence-img" />
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div style="padding: 2rem; text-align: center; opacity: 0.6;">
                                    No image evidence attached.
                                </div>
                            @endforelse
                        </div>
                    </x-filament::section>

                    <!-- Submitted URLs -->
                    <x-filament::section heading="Submitted URLs" icon="heroicon-o-link">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="rq-url-item">
                                <span class="rq-label">Profile URL:</span>
                                @if ($this->selectedReport->submitted_profile_url)
                                    <x-filament::link :href="$this->selectedReport->submitted_profile_url" target="_blank" icon="heroicon-m-arrow-top-right-on-square">
                                        {{ $this->selectedReport->submitted_profile_url }}
                                    </x-filament::link>
                                @else
                                    <span style="font-style: italic; opacity: 0.6;">None provided</span>
                                @endif
                            </div>

                            <div class="rq-url-item">
                                <span class="rq-label">Comment / Post Permalink:</span>
                                @if ($this->selectedReport->submitted_comment_url)
                                    <x-filament::link :href="$this->selectedReport->submitted_comment_url" target="_blank" icon="heroicon-m-arrow-top-right-on-square">
                                        {{ $this->selectedReport->submitted_comment_url }}
                                    </x-filament::link>
                                @else
                                    <span style="font-style: italic; opacity: 0.6;">None provided</span>
                                @endif
                            </div>
                        </div>
                    </x-filament::section>
                </div>

                <!-- Right Column: Verification Workspace & Actions -->
                <div class="rq-col">
                    <!-- Attribution Card -->
                    <x-filament::section heading="Profile & Incident Attribution" icon="heroicon-o-identification">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="rq-field-group rq-field-group-2col">
                                <div>
                                    <label class="rq-label">Target Profile</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model="facebook_profile_id">
                                            <option value="">-- Link Profile --</option>
                                            @foreach ($this->profiles as $p)
                                                <option value="{{ $p->id }}">{{ $p->current_display_name }} ({{ $p->canonical_url }})</option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>

                                <div>
                                    <label class="rq-label">Associated Incident</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model="incident_id">
                                            <option value="">-- Link Incident --</option>
                                            @foreach ($this->incidents as $inc)
                                                <option value="{{ $inc->id }}">{{ $inc->title_en }} ({{ $inc->occurred_on?->format('Y-m-d') }})</option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>
                            </div>

                            <div class="rq-field-group rq-field-group-2col">
                                <div>
                                    <label class="rq-label">Category</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model="category">
                                            <option value="">-- Select Category --</option>
                                            @foreach (\App\Enums\ReportCategory::cases() as $cat)
                                                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>

                                <div>
                                    <label class="rq-label">Severity</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model="severity">
                                            <option value="1">1 - Low</option>
                                            <option value="2">2 - Medium</option>
                                            <option value="3">3 - High</option>
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>
                            </div>

                            <div>
                                <label class="rq-label">Verbatim Comment Text</label>
                                <x-filament::input.wrapper>
                                    <textarea wire:model="comment_text" rows="3" class="fi-input" style="padding: 0.5rem 0.75rem; width: 100%; border: none; outline: none; background: transparent; font-family: monospace; font-size: 0.875rem;"></textarea>
                                </x-filament::input.wrapper>
                            </div>
                        </div>
                    </x-filament::section>

                    <!-- 4-Point Verification Checklist -->
                    <x-filament::section heading="4-Point Verification Checklist" icon="heroicon-o-check-badge">
                        <x-slot name="headerEnd">
                            <x-filament::badge color="danger">
                                All 4 required for Verify
                            </x-filament::badge>
                        </x-slot>

                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            @foreach (\App\Enums\VerificationCheck::cases() as $check)
                                <label class="rq-check-item">
                                    <x-filament::input.checkbox wire:model="checklist.{{ $check->value }}" />
                                    <span class="rq-check-text">
                                        <strong>{{ $check->name }}:</strong> {{ $check->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(156, 163, 175, 0.2);">
                            <label class="rq-label">Moderator Review Note</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model="review_note" placeholder="Optional audit log note..." />
                            </x-filament::input.wrapper>
                        </div>
                    </x-filament::section>

                    <!-- Moderation Actions -->
                    <x-filament::section heading="Moderation Actions" icon="heroicon-o-shield-check">
                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div class="rq-actions-row">
                                <x-filament::button
                                    wire:click="verify"
                                    :disabled="$isLockedByOther"
                                    color="success"
                                    size="lg"
                                    icon="heroicon-m-check">
                                    Verify Report
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="requestInfo"
                                    :disabled="$isLockedByOther"
                                    color="gray"
                                    size="lg"
                                    icon="heroicon-m-question-mark-circle">
                                    Request Info
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="toggleHomepageVisibility"
                                    :color="$this->selectedReport->is_hidden_from_homepage ? 'warning' : 'gray'"
                                    size="lg"
                                    :icon="$this->selectedReport->is_hidden_from_homepage ? 'heroicon-m-eye-slash' : 'heroicon-m-eye'">
                                    {{ $this->selectedReport->is_hidden_from_homepage ? 'Hidden (Show on Home)' : 'Hide from Home' }}
                                </x-filament::button>
                            </div>

                            <div class="rq-sub-actions-grid">
                                <!-- Reject Form -->
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label class="rq-label" style="color: var(--danger-600, #dc2626);">Reject Report</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="text" wire:model="rejection_reason" placeholder="Reason for rejection..." />
                                    </x-filament::input.wrapper>
                                    <x-filament::button
                                        wire:click="reject"
                                        :disabled="$isLockedByOther"
                                        color="danger"
                                        size="sm">
                                        Confirm Reject
                                    </x-filament::button>
                                </div>

                                <!-- Duplicate Form -->
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label class="rq-label" style="color: var(--warning-600, #d97706);">Mark as Duplicate</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="number" wire:model="duplicate_of_id" placeholder="Original Report ID..." />
                                    </x-filament::input.wrapper>
                                    <x-filament::button
                                        wire:click="markDuplicate"
                                        :disabled="$isLockedByOther"
                                        color="warning"
                                        size="sm">
                                        Mark Duplicate
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </x-filament::section>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
