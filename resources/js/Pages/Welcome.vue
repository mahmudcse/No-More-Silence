<script setup lang="ts">
import DistrictMap, { type DistrictStat } from '@/Components/DistrictMap.vue';
import FacebookPostCard, { type ReportItem } from '@/Components/FacebookPostCard.vue';
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatNumberByLocale } from '@/utils/bangla';
import { Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface NationalStats {
    received: number;
    verified: number;
    public: number;
    profiles: number;
    districts: number;
    received_bn: string;
    verified_bn: string;
    public_bn: string;
    profiles_bn: string;
    districts_bn: string;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    has_more: boolean;
    total: number;
}

const props = defineProps<{
    nationalStats: NationalStats;
    districtStats: DistrictStat[];
    featuredReports: ReportItem[];
    initialPagination?: PaginationMeta;
}>();

const { isBn, locale } = useLanguage();
const searchQuery = ref('');
const activeDistrictStats = ref<DistrictStat[]>(props.districtStats);

// Feed & Infinite Scroll state
const reportsList = ref<ReportItem[]>([...props.featuredReports]);
const currentPage = ref(props.initialPagination?.current_page || 1);
const hasMore = ref(
    props.initialPagination?.has_more ?? (props.featuredReports.length >= 8),
);
const isLoadingMore = ref(false);
const sentinelRef = ref<HTMLElement | null>(null);
const activeFilter = ref<'all' | 'verified' | 'pending'>('all');

const filteredReports = computed(() => {
    if (activeFilter.value === 'verified') {
        return reportsList.value.filter((r) => r.is_verified);
    }
    if (activeFilter.value === 'pending') {
        return reportsList.value.filter((r) => !r.is_verified);
    }
    return reportsList.value;
});

function handleSearch() {
    if (searchQuery.value.trim()) {
        router.visit(
            `/search?q=${encodeURIComponent(searchQuery.value.trim())}`,
        );
    }
}

async function handleModeChange(mode: 'incident' | 'commenter') {
    try {
        const response = await fetch(`/api/v1/stats/districts?mode=${mode}`);
        const data = await response.json();
        if (data && data.data) {
            activeDistrictStats.value = data.data;
        }
    } catch {
        // Fallback to initial
    }
}

async function loadMore() {
    if (isLoadingMore.value || !hasMore.value) return;
    isLoadingMore.value = true;

    try {
        const nextPage = currentPage.value + 1;
        const res = await fetch(`/api/v1/reports?page=${nextPage}`);
        if (!res.ok) throw new Error('Failed to fetch reports');
        const json = await res.json();
        if (json && Array.isArray(json.data)) {
            const existingUuids = new Set(reportsList.value.map((r) => r.uuid));
            const newReports = json.data.filter(
                (r: ReportItem) => !existingUuids.has(r.uuid),
            );
            reportsList.value.push(...newReports);
            currentPage.value = json.meta?.current_page ?? nextPage;
            hasMore.value =
                json.meta?.has_more ??
                (json.data.length > 0 &&
                    json.meta?.current_page < json.meta?.last_page);
        } else {
            hasMore.value = false;
        }
    } catch (e) {
        console.error('Error loading more reports:', e);
        hasMore.value = false;
    } finally {
        isLoadingMore.value = false;
    }
}

let observer: IntersectionObserver | null = null;

onMounted(() => {
    if ('IntersectionObserver' in window && sentinelRef.value) {
        observer = new IntersectionObserver(
            (entries) => {
                if (
                    entries[0].isIntersecting &&
                    hasMore.value &&
                    !isLoadingMore.value
                ) {
                    loadMore();
                }
            },
            { rootMargin: '350px' },
        );
        observer.observe(sentinelRef.value);
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});
</script>

<template>
    <PublicLayout :title="isBn ? 'হোম' : 'Home'">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Two Main Columns Layout: Left 8 cols, Right 4 cols -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- ============================================== -->
                <!-- LEFT COLUMN (8 cols): Recent Reported Feed    -->
                <!-- ============================================== -->
                <main class="space-y-6 lg:col-span-8">
                    <!-- Feed Control Bar -->
                    <div
                        class="flex flex-col gap-3 rounded-2xl p-4 apple-glass-card shadow-glass sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-white">
                                    {{ isBn ? 'সাম্প্রতিক রিপোর্ট ও মন্তব্যের ফিড' : 'Recent Reported Records Feed' }}
                                </h1>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                {{ isBn ? 'ফেসবুকে প্রকাশিত সহিংসতা সমর্থন ও ভিকটিম-ব্লেমিং মন্তব্যের পাবলিক আর্কাইভ' : 'Public archive of comments with verification status' }}
                            </p>
                        </div>

                        <!-- Status Filter Tabs -->
                        <div
                            class="inline-flex self-start rounded-full border border-black/[0.06] bg-black/[0.03] p-1 text-xs dark:border-white/[0.08] dark:bg-white/[0.06] sm:self-auto"
                        >
                            <button
                                type="button"
                                @click="activeFilter = 'all'"
                                class="rounded-full px-3 py-1 font-medium transition-all"
                                :class="
                                    activeFilter === 'all'
                                        ? 'bg-white text-slate-900 shadow-xs dark:bg-white/20 dark:text-white'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white'
                                "
                            >
                                {{ isBn ? 'সব' : 'All' }}
                                <span class="ml-1 text-[10px] opacity-70">({{ reportsList.length }})</span>
                            </button>
                            <button
                                type="button"
                                @click="activeFilter = 'verified'"
                                class="rounded-full px-3 py-1 font-medium transition-all"
                                :class="
                                    activeFilter === 'verified'
                                        ? 'bg-white text-emerald-700 shadow-xs dark:bg-emerald-500/30 dark:text-emerald-300'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white'
                                "
                            >
                                {{ isBn ? 'যাচাইকৃত' : 'Verified' }}
                            </button>
                            <button
                                type="button"
                                @click="activeFilter = 'pending'"
                                class="rounded-full px-3 py-1 font-medium transition-all"
                                :class="
                                    activeFilter === 'pending'
                                        ? 'bg-white text-amber-700 shadow-xs dark:bg-amber-500/30 dark:text-amber-300'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white'
                                "
                            >
                                {{ isBn ? 'পর্যালোচনাধীন' : 'Under Review' }}
                            </button>
                        </div>
                    </div>

                    <!-- Post List (Facebook Group Style) -->
                    <div v-if="filteredReports.length > 0" class="space-y-6">
                        <FacebookPostCard
                            v-for="report in filteredReports"
                            :key="report.uuid"
                            :report="report"
                        />
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="rounded-2xl py-14 text-center apple-glass-card shadow-glass"
                    >
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-black/[0.04] text-slate-400 dark:bg-white/[0.08] dark:text-slate-400"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-base font-bold text-slate-900 dark:text-white">
                            {{ isBn ? 'বর্তমানে কোনো রেকর্ড প্রদর্শনের জন্য নেই' : 'No records currently listed' }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ isBn ? 'নতুন রিপোর্ট জমা দিয়ে এই আর্কাইভকে সমৃদ্ধ করুন।' : 'Be the first to submit evidence to this archive.' }}
                        </p>
                        <div class="mt-4">
                            <Link
                                href="/submit"
                                class="inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-500"
                            >
                                {{ isBn ? 'রিপোর্ট জমা দিন' : 'Submit Report' }}
                            </Link>
                        </div>
                    </div>

                    <!-- Sentinel element for IntersectionObserver -->
                    <div ref="sentinelRef" class="h-4"></div>

                    <!-- Load More & Loading Indicators -->
                    <div class="py-4 text-center">
                        <div
                            v-if="isLoadingMore"
                            class="inline-flex items-center gap-2 rounded-full border border-black/[0.08] bg-white/70 px-4 py-2 text-xs font-medium text-slate-700 shadow-sm backdrop-blur-md dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-slate-300"
                        >
                            <svg
                                class="h-4 w-4 animate-spin text-rose-600 dark:text-rose-400"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v8H4z"
                                />
                            </svg>
                            <span>{{ isBn ? 'আরও রেকর্ড লোড হচ্ছে...' : 'Loading more records...' }}</span>
                        </div>

                        <button
                            v-else-if="hasMore && reportsList.length > 0"
                            type="button"
                            @click="loadMore"
                            class="inline-flex items-center gap-1.5 rounded-full border border-black/[0.08] bg-black/[0.03] px-6 py-2.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-black/[0.06] active:scale-95 dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-slate-200 dark:hover:bg-white/[0.14]"
                        >
                            <span>{{ isBn ? 'আরও রেকর্ড দেখুন (Load More)' : 'Load More Records' }}</span>
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
                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                />
                            </svg>
                        </button>

                        <div
                            v-else-if="!hasMore && reportsList.length > 0"
                            class="text-xs text-slate-400 dark:text-slate-500"
                        >
                            {{ isBn ? 'সবগুলো সাম্প্রতিক রেকর্ড লোড করা হয়েছে।' : 'All recent records have been loaded.' }}
                        </div>
                    </div>
                </main>

                <!-- ============================================== -->
                <!-- RIGHT COLUMN (4 cols): Search, Stats, Map      -->
                <!-- ============================================== -->
                <aside class="space-y-6 lg:col-span-4">
                    <!-- 1. Search Box at the Top -->
                    <div class="rounded-2xl p-5 apple-glass-card shadow-glass space-y-3">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ isBn ? 'রেকর্ড ও প্রোফাইল অনুসন্ধান' : 'Search Profiles & Records' }}
                            </h2>
                        </div>

                        <form @submit.prevent="handleSearch" class="relative flex items-center">
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="
                                    isBn
                                        ? 'ফেসবুক লিঙ্ক বা নাম...'
                                        : 'Facebook URL or name...'
                                "
                                class="w-full rounded-xl apple-glass-input py-2.5 pl-3.5 pr-20 text-xs text-slate-900 placeholder-slate-400 focus:outline-none dark:text-white dark:placeholder-slate-500"
                            />
                            <button
                                type="submit"
                                class="absolute right-1 rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-500 active:scale-95"
                            >
                                {{ isBn ? 'খুঁজুন' : 'Search' }}
                            </button>
                        </form>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{
                                isBn
                                    ? 'আইডি, ফেসবুক প্রোফাইল লিংক বা নাম দিয়ে রেকর্ড যাচাই করুন।'
                                    : 'Verify records by Facebook URL or name.'
                            }}
                        </p>
                    </div>

                    <!-- 2. Numbers / Key Counters Grid -->
                    <div class="rounded-2xl p-5 apple-glass-card shadow-glass space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ isBn ? 'সার্বিক পরিসংখ্যান' : 'National Overview' }}
                            </h3>
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ isBn ? 'লাইভ উপাত্ত' : 'Live Archive' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <!-- Received -->
                            <div class="rounded-xl p-3 text-center apple-glass-subtle space-y-0.5">
                                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                    {{ isBn ? nationalStats.received_bn : formatNumberByLocale(nationalStats.received, locale) }}
                                </div>
                                <div class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                    {{ isBn ? 'প্রাপ্ত রিপোর্ট' : 'Reports Received' }}
                                </div>
                                <div class="text-[9px] text-slate-400">
                                    {{ isBn ? 'নাগরিক দ্বারা জমাকৃত' : 'From citizens' }}
                                </div>
                            </div>

                            <!-- Verified -->
                            <div class="rounded-xl p-3 text-center apple-glass-subtle space-y-0.5 border-rose-500/20 bg-rose-50/40 dark:bg-rose-950/20">
                                <div class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400">
                                    {{ isBn ? nationalStats.verified_bn : formatNumberByLocale(nationalStats.verified, locale) }}
                                </div>
                                <div class="text-[11px] font-semibold text-rose-900 dark:text-rose-200">
                                    {{ isBn ? 'যাচাইকৃত রেকর্ড' : 'Verified Records' }}
                                </div>
                                <div class="text-[9px] text-rose-700/80 dark:text-rose-300/70">
                                    {{ isBn ? '৪-দফায় যাচাইকৃত' : '4-point verified' }}
                                </div>
                            </div>

                            <!-- Documented Profiles -->
                            <div class="rounded-xl p-3 text-center apple-glass-subtle space-y-0.5">
                                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                    {{ isBn ? nationalStats.profiles_bn : formatNumberByLocale(nationalStats.profiles, locale) }}
                                </div>
                                <div class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                    {{ isBn ? 'নথিবদ্ধ প্রোফাইল' : 'Profiles Documented' }}
                                </div>
                                <div class="text-[9px] text-slate-400">
                                    {{ isBn ? 'অনন্য একাউন্ট' : 'Unique accounts' }}
                                </div>
                            </div>

                            <!-- Districts Covered -->
                            <div class="rounded-xl p-3 text-center apple-glass-subtle space-y-0.5">
                                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                    {{ isBn ? nationalStats.districts_bn : formatNumberByLocale(nationalStats.districts, locale) }}
                                    <span class="text-xs font-normal text-slate-400">/ {{ isBn ? '৬৪' : '64' }}</span>
                                </div>
                                <div class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                    {{ isBn ? 'জেলায় বিস্তার' : 'Districts Covered' }}
                                </div>
                                <div class="text-[9px] text-slate-400">
                                    {{ isBn ? '৬৪ জেলা মনিটরিং' : 'Across 64 districts' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. District Map Section -->
                    <div class="rounded-2xl p-5 apple-glass-card shadow-glass space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    {{ isBn ? 'জেলা মানচিত্র' : 'District Distribution' }}
                                </h3>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ isBn ? 'প্রতি ১ লাখ জনসংখ্যায় সহিংসতা ও মন্তব্যের বিস্তার' : 'Normalized per 100k BBS 2022' }}
                                </p>
                            </div>
                            <Link
                                href="/districts"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400"
                            >
                                {{ isBn ? 'পূর্ণ মানচিত্র →' : 'Full Map →' }}
                            </Link>
                        </div>

                        <DistrictMap
                            :stats="activeDistrictStats"
                            initial-mode="incident"
                            @mode-change="handleModeChange"
                        />
                    </div>

                    <!-- 4. Policies & Safeguards Card -->
                    <div class="rounded-2xl p-5 apple-glass-card shadow-glass space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ isBn ? 'নীতি ও ভিকটিম সুরক্ষা' : 'Policies & Safeguards' }}
                        </h3>
                        <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-emerald-500">✓</span>
                                <span>{{ isBn ? '২-ব্যক্তি নিরপেক্ষ যাচাইকরণ নীতি (Two-Person Rule)' : 'Strict Two-Person verification rule' }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-emerald-500">✓</span>
                                <span>{{ isBn ? 'ভিকটিম ও অপ্রাপ্তবয়স্কদের তথ্য শতভাগ গোপন ও রেড্যাক্ট করা' : '100% victim identities protected & redacted' }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-emerald-500">✓</span>
                                <span>{{ isBn ? 'ভুল দাবি বা হ্যাকড প্রোফাইলের ক্ষেত্রে প্রমাণসহ আপিল অধিকার' : 'Right of reply & dispute appeal mechanism' }}</span>
                            </li>
                        </ul>
                        <div class="flex items-center justify-between border-t border-black/[0.06] pt-3 text-xs dark:border-white/[0.08]">
                            <Link href="/methodology" class="font-medium text-rose-600 hover:underline dark:text-rose-400">
                                {{ isBn ? '৮-দফা যাচাইকরণ মানদণ্ড' : '8-Point Standard' }}
                            </Link>
                            <Link href="/safety-resources" class="text-slate-500 hover:underline dark:text-slate-400">
                                {{ isBn ? 'জরুরি হেল্পলাইন' : 'Helplines' }}
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </PublicLayout>
</template>
