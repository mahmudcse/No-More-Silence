<script setup lang="ts">
import PostDiscussion from '@/Components/PostDiscussion.vue';
import { useLanguage } from '@/composables/useLanguage';
import { formatDateByLocale, formatNumberByLocale } from '@/utils/bangla';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

export interface PhotoItem {
    id?: number | string;
    url: string;
    name?: string;
    type?: string;
}

export interface ReportItem {
    uuid: string;
    comment_text: string | null;
    comment_text_translation?: string | null;
    comment_posted_at?: string | null;
    created_at?: string | null;
    category?: string | null;
    category_label_bn?: string | null;
    category_label_en?: string | null;
    workflow_status?: string | null;
    workflow_status_label?: string | null;
    workflow_status_label_bn?: string | null;
    is_verified?: boolean;
    verified_at?: string | null;
    published_at?: string | null;
    evidence_url?: string | null;
    photos?: PhotoItem[];
    photos_count?: number;
    profile?: {
        uuid: string;
        display_name: string | null;
        canonical_url: string;
    } | null;
    incident?: {
        id: number;
        title_en: string;
        title_bn: string;
        slug: string;
        district?: string | null;
    } | null;
    comments_count?: number;
}

const props = defineProps<{
    report: ReportItem;
}>();

const { isBn, locale } = useLanguage();
const page = usePage();
const currentUser = computed(() => (page.props.auth as any)?.user ?? null);

// Discussion state
const showDiscussion = ref(false);
const commentsCount = ref(props.report.comments_count ?? 0);

function toggleDiscussion() {
    showDiscussion.value = !showDiscussion.value;
}

function handleUpdateCount(newCount: number) {
    commentsCount.value = newCount;
}

// Lightbox state
const lightboxOpen = ref(false);
const activePhotoIndex = ref(0);
const copied = ref(false);

const allPhotos = computed<PhotoItem[]>(() => {
    if (props.report.photos && props.report.photos.length > 0) {
        return props.report.photos;
    }
    if (props.report.evidence_url) {
        return [{ url: props.report.evidence_url, type: 'evidence' }];
    }
    return [];
});

function openLightbox(index: number) {
    activePhotoIndex.value = index;
    lightboxOpen.value = true;
}

function closeLightbox() {
    lightboxOpen.value = false;
}

function nextPhoto() {
    if (allPhotos.value.length === 0) return;
    activePhotoIndex.value = (activePhotoIndex.value + 1) % allPhotos.value.length;
}

function prevPhoto() {
    if (allPhotos.value.length === 0) return;
    activePhotoIndex.value = (activePhotoIndex.value - 1 + allPhotos.value.length) % allPhotos.value.length;
}

function handleKeyDown(e: KeyboardEvent) {
    if (!lightboxOpen.value) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft') prevPhoto();
}

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

function copyShareLink() {
    const url = `${window.location.origin}/reports/${props.report.uuid}`;
    navigator.clipboard.writeText(url);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}

const categoryColors: Record<string, string> = {
    victim_blaming:
        'bg-amber-500/10 text-amber-700 border-amber-500/20 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-400/20',
    justifying_violence:
        'bg-rose-500/10 text-rose-700 border-rose-500/20 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-400/20',
    trivialising:
        'bg-blue-500/10 text-blue-700 border-blue-500/20 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-400/20',
    incitement:
        'bg-red-500/10 text-red-700 border-red-500/20 dark:bg-red-500/15 dark:text-red-300 dark:border-red-400/20',
    harassment:
        'bg-purple-500/10 text-purple-700 border-purple-500/20 dark:bg-purple-500/15 dark:text-purple-300 dark:border-purple-400/20',
};

function getCategoryColor(category?: string | null): string {
    if (!category) {
        return 'bg-black/[0.04] text-slate-700 border-black/[0.08] dark:bg-white/[0.08] dark:text-slate-300 dark:border-white/[0.1]';
    }
    return (
        categoryColors[category] ??
        'bg-black/[0.04] text-slate-700 border-black/[0.08] dark:bg-white/[0.08] dark:text-slate-300 dark:border-white/[0.1]'
    );
}
</script>

<template>
    <article
        class="overflow-hidden rounded-2xl apple-glass-card shadow-glass transition-all duration-300 hover:shadow-glass-lg"
    >
        <!-- Post Header: Facebook Group Post Style -->
        <div class="p-4 sm:p-6 pb-2.5 sm:pb-3">
            <div class="flex items-start justify-between gap-3">
                <!-- User avatar + Info -->
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-black/[0.08] bg-gradient-to-br from-slate-100 to-slate-200 text-slate-600 shadow-sm dark:border-white/[0.12] dark:from-slate-800 dark:to-slate-900 dark:text-slate-300"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-2.625 6a2.625 2.625 0 115.25 0 2.625 2.625 0 01-5.25 0zM4.75 16.924a8.25 8.25 0 0114.5 0A7.48 7.48 0 0112 19.5a7.48 7.48 0 01-7.25-2.576z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <Link
                                v-if="report.profile"
                                :href="`/profiles/${report.profile.uuid}`"
                                class="truncate text-sm font-bold text-slate-900 transition-colors hover:text-rose-600 hover:underline dark:text-white dark:hover:text-rose-400"
                            >
                                {{ report.profile.display_name ?? (isBn ? 'নামবিহীন ফেসবুক ব্যবহারকারী' : 'Facebook User') }}
                            </Link>
                            <span
                                v-else
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ isBn ? 'নামবিহীন ফেসবুক ব্যবহারকারী' : 'Facebook User' }}
                            </span>

                            <a
                                v-if="report.profile?.canonical_url"
                                :href="report.profile.canonical_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
                                :title="isBn ? 'মূল ফেসবুক প্রোফাইল' : 'View Facebook Profile'"
                            >
                                <svg
                                    class="h-3.5 w-3.5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"
                                    />
                                </svg>
                            </a>
                        </div>

                        <!-- Date & Public Globe Icon -->
                        <div class="flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                            <span>{{ formatDateByLocale(report.comment_posted_at || report.created_at || report.verified_at, locale) }}</span>
                            <span>•</span>
                            <!-- Public Globe Icon -->
                            <svg
                                class="h-3 w-3"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-.2a1.8 1.8 0 00-1.8-1.8h-.4A1.8 1.8 0 006 12.2v-.4a1.8 1.8 0 00-1.668-1.773z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span v-if="report.incident?.district">
                                • {{ report.incident.district }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Status Badge & Category Pill -->
                <div class="flex flex-col items-end gap-1.5 sm:flex-row sm:items-center">
                    <!-- Verification Status Badge -->
                    <span
                        v-if="report.is_verified"
                        class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-500/20 dark:text-emerald-300"
                    >
                        <svg
                            class="h-3.5 w-3.5 text-emerald-500 dark:text-emerald-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>{{ isBn ? 'যাচাইকৃত রেকর্ড' : 'Verified Record' }}</span>
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:border-amber-400/30 dark:bg-amber-500/20 dark:text-amber-300"
                    >
                        <svg
                            class="h-3.5 w-3.5 text-amber-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>{{ isBn ? 'পর্যালোচনাধীন' : 'Under Review' }}</span>
                    </span>

                    <!-- Category Pill -->
                    <span
                        v-if="report.category"
                        :class="[
                            'rounded-full border px-2.5 py-0.5 text-[11px] font-medium',
                            getCategoryColor(report.category),
                        ]"
                    >
                        {{ isBn ? (report.category_label_bn ?? report.category) : (report.category_label_en ?? report.category) }}
                    </span>
                </div>
            </div>

            <!-- Post Text (Verbatim Facebook Comment) -->
            <div class="mt-3.5 text-sm leading-relaxed text-slate-800 dark:text-slate-100 sm:text-[15px]">
                <p class="whitespace-pre-line font-normal">
                    {{ report.comment_text || (isBn ? '[কোনো টেক্সট রেকর্ড নেই]' : '[No comment text]') }}
                </p>

                <!-- Translation if provided -->
                <div
                    v-if="report.comment_text_translation"
                    class="mt-2.5 rounded-xl border border-black/[0.04] bg-black/[0.02] p-2.5 text-xs italic text-slate-600 dark:border-white/[0.06] dark:bg-white/[0.03] dark:text-slate-400"
                >
                    <span class="font-semibold not-italic text-slate-700 dark:text-slate-300">{{ isBn ? 'অনুবাদ:' : 'Translation:' }}</span>
                    {{ report.comment_text_translation }}
                </div>

                <!-- Linked Incident Reference -->
                <div
                    v-if="report.incident"
                    class="mt-2.5 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"
                >
                    <span class="font-medium text-slate-600 dark:text-slate-300">
                        {{ isBn ? 'ঘটনা:' : 'Incident:' }}
                    </span>
                    <span
                        class="font-medium text-slate-700 dark:text-slate-300"
                    >
                        {{ isBn ? report.incident.title_bn : report.incident.title_en }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Facebook-Style Photo Collage with Padding & Rounded Framing -->
        <div v-if="allPhotos.length > 0" class="px-4 pb-3.5 pt-1 sm:px-6">
            <div class="overflow-hidden rounded-2xl border border-black/[0.08] bg-slate-100/60 shadow-xs dark:border-white/[0.1] dark:bg-slate-900/40">
                <!-- 1 Photo -->
                <div v-if="allPhotos.length === 1" class="relative overflow-hidden flex items-center justify-center bg-black/5 dark:bg-black/30">
                    <img
                        :src="allPhotos[0].url"
                        :alt="report.comment_text || 'Evidence'"
                        @click="openLightbox(0)"
                        class="max-h-[380px] w-full cursor-pointer object-cover object-top transition-transform duration-300 hover:scale-[1.01]"
                        loading="lazy"
                    />
                </div>

                <!-- 2 Photos (Side by side) -->
                <div
                    v-else-if="allPhotos.length === 2"
                    class="grid grid-cols-2 gap-2 overflow-hidden"
                >
                    <div
                        v-for="(photo, idx) in allPhotos"
                        :key="idx"
                        class="relative aspect-[16/10] sm:aspect-[16/9] cursor-pointer overflow-hidden bg-black/5 dark:bg-black/30"
                        @click="openLightbox(idx)"
                    >
                        <img
                            :src="photo.url"
                            class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                            loading="lazy"
                            alt="Evidence photo"
                        />
                    </div>
                </div>

                <!-- 3 Photos (1 large on left, 2 stacked on right) -->
                <div
                    v-else-if="allPhotos.length === 3"
                    class="grid grid-cols-3 gap-2 overflow-hidden aspect-[16/9]"
                >
                    <div
                        class="col-span-2 cursor-pointer overflow-hidden bg-black/5 dark:bg-black/30"
                        @click="openLightbox(0)"
                    >
                        <img
                            :src="allPhotos[0].url"
                            class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                            loading="lazy"
                            alt="Evidence photo"
                        />
                    </div>
                    <div class="col-span-1 grid grid-rows-2 gap-2">
                        <div
                            v-for="(photo, idx) in allPhotos.slice(1, 3)"
                            :key="idx + 1"
                            class="cursor-pointer overflow-hidden bg-black/5 dark:bg-black/30"
                            @click="openLightbox(idx + 1)"
                        >
                            <img
                                :src="photo.url"
                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                loading="lazy"
                                alt="Evidence photo"
                            />
                        </div>
                    </div>
                </div>

                <!-- 4+ Photos (2x2 grid with +N remaining overlay on 4th) -->
                <div
                    v-else
                    class="grid grid-cols-2 gap-2 overflow-hidden aspect-[16/9]"
                >
                    <div
                        v-for="(photo, idx) in allPhotos.slice(0, 4)"
                        :key="idx"
                        class="relative cursor-pointer overflow-hidden bg-black/5 dark:bg-black/30"
                        @click="openLightbox(idx)"
                    >
                        <img
                            :src="photo.url"
                            class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                            loading="lazy"
                            alt="Evidence photo"
                        />
                        <!-- Overlay on 4th photo if more exist -->
                        <div
                            v-if="idx === 3 && allPhotos.length > 4"
                            class="absolute inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs font-bold text-2xl text-white transition hover:bg-black/50"
                        >
                            +{{ allPhotos.length - 3 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Post Action Bar (Facebook-Style Footer) -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-black/[0.04] px-4 py-2 text-xs text-slate-600 dark:border-white/[0.04] dark:text-slate-400 sm:px-6">
            <!-- Left: Discussion Toggle Button -->
            <button
                type="button"
                @click="toggleDiscussion"
                :class="[
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 font-medium transition-all active:scale-95',
                    showDiscussion
                        ? 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400'
                        : 'bg-black/[0.03] text-slate-700 hover:bg-black/[0.06] dark:bg-white/[0.06] dark:text-slate-200 dark:hover:bg-white/[0.12]',
                ]"
            >
                <svg
                    class="h-3.5 w-3.5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.636 1.05.512 1.656l-.42 2.053 2.21-.904a2.25 2.25 0 011.36-.085c.99.336 2.05.55 3.065.55z"
                    />
                </svg>
                <span>{{ isBn ? 'আলোচনা' : 'Discussion' }}</span>
                <span
                    v-if="commentsCount > 0"
                    class="rounded-full bg-rose-600/15 px-1.5 py-0.2 text-[10px] font-bold text-rose-600 dark:bg-rose-400/25 dark:text-rose-300"
                >
                    {{ formatNumberByLocale(commentsCount, isBn ? 'bn' : 'en') }}
                </span>
            </button>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Dispute / Appeal Link -->
                <Link
                    :href="`/appeals?report_uuid=${report.uuid}${report.profile ? '&profile_url=' + encodeURIComponent(report.profile.canonical_url) : ''}`"
                    class="inline-flex items-center gap-1 font-medium text-rose-600 transition-colors hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                    :title="isBn ? 'এই রেকর্ডের ব্যাপারে আপত্তি জানান' : 'Dispute or appeal this record'"
                >
                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
                        />
                    </svg>
                    <span>{{ isBn ? 'আপত্তি / আপিল' : 'Dispute' }}</span>
                </Link>

                <!-- Share Link -->
                <button
                    type="button"
                    @click="copyShareLink"
                    class="inline-flex items-center gap-1 text-slate-500 transition-colors hover:text-slate-800 dark:text-slate-400 dark:hover:text-white"
                >
                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"
                        />
                    </svg>
                    <span>{{ copied ? (isBn ? 'কপি হয়েছে!' : 'Copied!') : (isBn ? 'শেয়ার' : 'Share') }}</span>
                </button>
            </div>
        </div>

        <!-- Discussion / Comments Section -->
        <PostDiscussion
            v-if="showDiscussion"
            :report-uuid="report.uuid"
            :initial-count="commentsCount"
            :is-bn="isBn"
            :current-user="currentUser"
            @update-count="handleUpdateCount"
        />

        <!-- Fullscreen Lightbox Modal for Photo Viewing -->
        <Teleport to="body">
            <div
                v-if="lightboxOpen && allPhotos.length > 0"
                class="fixed inset-0 z-50 flex flex-col items-center justify-between bg-black/90 p-4 backdrop-blur-2xl transition-all"
                @click.self="closeLightbox"
            >
                <!-- Lightbox Top bar -->
                <div class="flex w-full max-w-5xl items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold backdrop-blur-md">
                            {{ activePhotoIndex + 1 }} / {{ allPhotos.length }}
                        </span>
                        <span
                            v-if="report.is_verified"
                            class="rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs text-emerald-300"
                        >
                            {{ isBn ? 'যাচাইকৃত প্রমাণ' : 'Verified Evidence' }}
                        </span>
                        <span
                            v-else
                            class="rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs text-amber-300"
                        >
                            {{ isBn ? 'পর্যালোচনাধীন' : 'Under Review' }}
                        </span>
                    </div>

                    <!-- Close button -->
                    <button
                        type="button"
                        @click="closeLightbox"
                        class="rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20 active:scale-95"
                        aria-label="Close photo view"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Main photo display with Prev/Next buttons -->
                <div class="relative flex flex-1 w-full max-w-5xl items-center justify-center p-2">
                    <!-- Prev Button -->
                    <button
                        v-if="allPhotos.length > 1"
                        type="button"
                        @click="prevPhoto"
                        class="absolute left-2 z-10 rounded-full bg-white/10 p-3 text-white backdrop-blur-md transition hover:bg-white/25 active:scale-90"
                        aria-label="Previous photo"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 19.5L8.25 12l7.5-7.5"
                            />
                        </svg>
                    </button>

                    <!-- Active Image -->
                    <img
                        :src="allPhotos[activePhotoIndex].url"
                        :alt="report.comment_text || 'Evidence'"
                        class="max-h-[80vh] max-w-full rounded-xl object-contain shadow-2xl"
                    />

                    <!-- Next Button -->
                    <button
                        v-if="allPhotos.length > 1"
                        type="button"
                        @click="nextPhoto"
                        class="absolute right-2 z-10 rounded-full bg-white/10 p-3 text-white backdrop-blur-md transition hover:bg-white/25 active:scale-90"
                        aria-label="Next photo"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8.25 4.5l7.5 7.5-7.5 7.5"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Lightbox Footer -->
                <div class="w-full max-w-5xl text-center text-xs text-slate-400">
                    <p class="truncate">
                        {{ report.comment_text || (isBn ? 'প্রমাণিত সর্বজনীন ফেসবুক মন্তব্য' : 'Public Facebook Comment Evidence') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-500">
                        {{ isBn ? 'ভিকটিম বা অপ্রাপ্তবয়স্কদের তথ্য সুরক্ষিত ও সার্ভারে স্থায়ীভাবে কালো বক্সে ঢাকা রয়েছে।' : 'Victims and minors are permanently black-box redacted according to verification standards.' }}
                    </p>
                </div>
            </div>
        </Teleport>
    </article>
</template>
