<x-filament-panels::page>
    <div class="space-y-6">
        @if ($isLockedByOther)
            <div class="p-4 rounded-xl bg-danger-500/10 border border-danger-500 text-danger-700 dark:text-danger-400 font-medium flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-lock-closed class="w-6 h-6" />
                    <span>This report is currently being reviewed by <strong>{{ $lockedByName }}</strong>. Action buttons are disabled to prevent duplicate reviews.</span>
                </div>
                <button wire:click="loadNextInQueue" type="button" class="px-3 py-1.5 bg-danger-600 text-white rounded-lg text-sm font-semibold hover:bg-danger-500 transition">
                    Skip to Next
                </button>
            </div>
        @endif

        @if (! $this->selectedReport)
            <div class="text-center py-16 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
                <x-heroicon-o-check-circle class="w-16 h-16 mx-auto text-success-500 mb-4" />
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Review Queue is Empty</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2">All submitted reports have been triaged or verified.</p>
            </div>
        @else
            <!-- Top Queue Navigation Bar -->
            <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Queue:</span>
                    <select wire:change="selectReport($event.target.value)" class="text-sm font-medium bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg py-1.5 px-3">
                        @foreach ($this->pendingReports as $pending)
                            <option value="{{ $pending->id }}" @selected($pending->id === $selectedReportId)>
                                #{{ $pending->id }}: {{ $pending->category?->label() ?? 'Uncategorized' }} [{{ $pending->workflow_status->value }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">UUID: {{ $this->selectedReport->uuid }}</span>
                    <button wire:click="loadNextInQueue" type="button" class="text-sm text-primary-600 dark:text-primary-400 hover:underline font-medium">
                        Next Report &rarr;
                    </button>
                </div>
            </div>

            <!-- Two-Column Side-by-Side Reviewer -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column: Evidence & External Links -->
                <div class="lg:col-span-6 space-y-6">
                    <!-- Media Gallery -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-heroicon-o-photo class="w-5 h-5 text-primary-500" />
                                Submitted Evidence
                            </h4>
                            <span class="text-xs text-gray-400">{{ $this->selectedReport->getMedia('original')->count() }} image(s)</span>
                        </div>

                        <div class="space-y-4">
                            @forelse ($this->selectedReport->getMedia('original') as $media)
                                @php
                                    $evidenceType = $media->getCustomProperty('evidence_type', 'comment');
                                    $badgeConfig = match($evidenceType) {
                                        'post' => ['label' => 'Original Post', 'class' => 'bg-blue-500/10 text-blue-700 dark:text-blue-300 border-blue-500/20'],
                                        'comment' => ['label' => 'Offensive Comment', 'class' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20'],
                                        'profile' => ['label' => 'Commenter Profile', 'class' => 'bg-purple-500/10 text-purple-700 dark:text-purple-300 border-purple-500/20'],
                                        default => ['label' => 'Other Evidence', 'class' => 'bg-gray-500/10 text-gray-700 dark:text-gray-300 border-gray-500/20'],
                                    };
                                @endphp
                                <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-3 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold {{ $badgeConfig['class'] }}">
                                            {{ $badgeConfig['label'] }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 font-mono">{{ $media->file_name }}</span>
                                    </div>
                                    <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 bg-black/10">
                                        <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ $media->getUrl() }}" alt="Evidence screenshot" class="w-full h-auto object-contain max-h-[500px]" />
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-xl">
                                    No image evidence attached.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Submitted URLs -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 space-y-3">
                        <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-link class="w-5 h-5 text-primary-500" />
                            Submitted URLs
                        </h4>

                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-xs font-semibold text-gray-500 block">Profile URL:</span>
                                @if ($this->selectedReport->submitted_profile_url)
                                    <a href="{{ $this->selectedReport->submitted_profile_url }}" target="_blank" rel="noopener noreferrer" class="text-primary-600 dark:text-primary-400 hover:underline break-all font-mono text-xs">
                                        {{ $this->selectedReport->submitted_profile_url }} &UpperRightArrow;
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">None provided</span>
                                @endif
                            </div>

                            <div>
                                <span class="text-xs font-semibold text-gray-500 block">Comment / Post Permalink:</span>
                                @if ($this->selectedReport->submitted_comment_url)
                                    <a href="{{ $this->selectedReport->submitted_comment_url }}" target="_blank" rel="noopener noreferrer" class="text-primary-600 dark:text-primary-400 hover:underline break-all font-mono text-xs">
                                        {{ $this->selectedReport->submitted_comment_url }} &UpperRightArrow;
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">None provided</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Verification Workspace & Actions -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 space-y-5">
                        <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-identification class="w-5 h-5 text-primary-500" />
                            Profile & Incident Attribution
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1">Target Profile</label>
                                <select wire:model="facebook_profile_id" class="w-full text-sm bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg">
                                    <option value="">-- Link Profile --</option>
                                    @foreach ($this->profiles as $p)
                                        <option value="{{ $p->id }}">{{ $p->current_display_name }} ({{ $p->canonical_url }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1">Associated Incident</label>
                                <select wire:model="incident_id" class="w-full text-sm bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg">
                                    <option value="">-- Link Incident --</option>
                                    @foreach ($this->incidents as $inc)
                                        <option value="{{ $inc->id }}">{{ $inc->title_en }} ({{ $inc->occurred_on?->format('Y-m-d') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1">Category</label>
                                <select wire:model="category" class="w-full text-sm bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg">
                                    <option value="">-- Select Category --</option>
                                    @foreach (\App\Enums\ReportCategory::cases() as $cat)
                                        <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1">Severity</label>
                                <select wire:model="severity" class="w-full text-sm bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg">
                                    <option value="1">1 - Low</option>
                                    <option value="2">2 - Medium</option>
                                    <option value="3">3 - High</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1">Verbatim Comment Text</label>
                            <textarea wire:model="comment_text" rows="3" class="w-full text-sm bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg font-mono"></textarea>
                        </div>
                    </div>

                    <!-- 8-Point Verification Checklist -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-heroicon-o-check-badge class="w-5 h-5 text-success-500" />
                                8-Point Verification Checklist
                            </h4>
                            <span class="text-xs text-danger-500 font-medium">All 8 required for Verify</span>
                        </div>

                        <div class="space-y-3">
                            @foreach (\App\Enums\VerificationCheck::cases() as $check)
                                <label class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition">
                                    <input type="checkbox" wire:model="checklist.{{ $check->value }}" class="rounded text-success-600 focus:ring-success-500 mt-1" />
                                    <span class="text-xs text-gray-700 dark:text-gray-300">
                                        <strong>{{ $check->name }}:</strong> {{ $check->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1">Moderator Review Note</label>
                            <input type="text" wire:model="review_note" placeholder="Optional audit log note..." class="w-full text-sm bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg" />
                        </div>
                    </div>

                    <!-- Workflow Actions -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                wire:click="verify"
                                @disabled($isLockedByOther)
                                type="button"
                                class="px-5 py-2.5 bg-success-600 text-white rounded-xl text-sm font-bold hover:bg-success-500 disabled:opacity-50 transition shadow-sm">
                                Verify Report
                            </button>

                            <button
                                wire:click="requestInfo"
                                @disabled($isLockedByOther)
                                type="button"
                                class="px-4 py-2.5 bg-gray-600 text-white rounded-xl text-sm font-semibold hover:bg-gray-500 disabled:opacity-50 transition">
                                Request Info
                            </button>
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-800 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Reject Action Form -->
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-danger-600 dark:text-danger-400">Reject Report</label>
                                <input type="text" wire:model="rejection_reason" placeholder="Reason for rejection..." class="w-full text-xs bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg" />
                                <button
                                    wire:click="reject"
                                    @disabled($isLockedByOther)
                                    type="button"
                                    class="w-full px-3 py-1.5 bg-danger-600 text-white rounded-lg text-xs font-semibold hover:bg-danger-500 disabled:opacity-50 transition">
                                    Confirm Reject
                                </button>
                            </div>

                            <!-- Duplicate Action Form -->
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-purple-600 dark:text-purple-400">Mark as Duplicate</label>
                                <input type="number" wire:model="duplicate_of_id" placeholder="Original Report ID..." class="w-full text-xs bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 rounded-lg" />
                                <button
                                    wire:click="markDuplicate"
                                    @disabled($isLockedByOther)
                                    type="button"
                                    class="w-full px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-semibold hover:bg-purple-500 disabled:opacity-50 transition">
                                    Mark Duplicate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
