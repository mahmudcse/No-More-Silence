<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { formatDateByLocale, formatNumberByLocale } from '@/utils/bangla';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

export interface SearchResultProfile {
    uuid: string;
    display_name: string | null;
    canonical_url: string;
    district?: {
        name_en: string;
        name_bn: string;
        slug: string;
    } | null;
    district_source: string;
    public_reports_count: number;
    public_reports_count_bn: string;
    first_recorded_at: string | null;
    last_recorded_at: string | null;
}

const props = defineProps<{
    initialQuery: string;
    searchType: 'empty' | 'url' | 'name';
    warning?: string | null;
    warningEn?: string | null;
    results: SearchResultProfile[];
}>();

const { isBn, locale } = useLanguage();
const searchQuery = ref(props.initialQuery || '');

const normalizedResults = computed<SearchResultProfile[]>(() => {
    return (props.results || []).map((item: any) => (item?.data ? item.data : item));
});

function handleSearch() {
    if (searchQuery.value.trim()) {
        router.visit(
            `/search?q=${encodeURIComponent(searchQuery.value.trim())}`,
        );
    }
}
</script>

<template>
    <PublicLayout :title="isBn ? 'অনুসন্ধান' : 'Search'">
        <div class="mx-auto max-w-4xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="space-y-2 text-center">
                <h1
                    class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'রেকর্ড ও প্রোফাইল অনুসন্ধান'
                            : 'Search Records & Profiles'
                    }}
                </h1>
                <p
                    class="text-xs text-slate-600 dark:text-slate-400 sm:text-sm"
                >
                    {{
                        isBn
                            ? 'ফেসবুক প্রোফাইল URL দিয়ে সরাসরি অথবা নাম দিয়ে পাবলিক মন্তব্যের রেকর্ড খুঁজুন।'
                            : 'Search by exact Facebook profile link or name to find documented records.'
                    }}
                </p>
            </div>

            <!-- Search Form -->
            <form
                @submit.prevent="handleSearch"
                class="relative flex items-center rounded-2xl p-1 apple-glass-card shadow-glass transition-all focus-within:shadow-glass-lg focus-within:border-black/20 dark:focus-within:border-white/20"
            >
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="
                        isBn
                            ? 'ফেসবুক প্রোফাইল লিঙ্ক (URL) অথবা নাম লিখুন...'
                            : 'Enter Facebook URL or profile name...'
                    "
                    class="w-full bg-transparent py-4 pl-12 pr-28 text-sm text-slate-900 placeholder-slate-400 focus:outline-none dark:text-white dark:placeholder-slate-400"
                />
                <button
                    type="submit"
                    class="absolute right-1.5 rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-rose-500 active:scale-95"
                >
                    {{ isBn ? 'অনুসন্ধান' : 'Search' }}
                </button>
            </form>

            <!-- MANDATORY SAME-NAME WARNING BANNER -->
            <div
                v-if="warning || searchType === 'name'"
                class="space-y-2 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5 text-amber-900 apple-glass-subtle dark:bg-amber-500/15 dark:text-amber-200"
            >
                <div
                    class="flex items-center gap-2 text-sm font-bold text-amber-800 dark:text-amber-300"
                >
                    <svg
                        class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                        />
                    </svg>
                    <span>{{
                        isBn
                            ? 'জরুরি আইনি ও নৈতিক সতর্কতা'
                            : 'Legal & Ethical Notice'
                    }}</span>
                </div>
                <p
                    class="text-xs font-medium leading-relaxed text-amber-800 dark:text-amber-200/90 sm:text-sm"
                >
                    {{
                        isBn
                            ? 'সতর্কতা: বাংলাদেশে একই নামে বহু মানুষ থাকতে পারেন। প্রোফাইলের সুনির্দিষ্ট ডিজিটাল লিঙ্ক ও অনন্য আইডি নিশ্চিত না হয়ে কাউকে দোষারোপ করবেন না।'
                            : 'Warning: Multiple individuals may share the same name in Bangladesh. Do not attribute comments to any person without confirming their specific profile link and numeric ID.'
                    }}
                </p>
                <p class="text-[11px] text-amber-700 dark:text-amber-300/70">
                    {{
                        isBn
                            ? 'কারো প্রতি অন্যায্য হয়রানি বা মিথ্যা অপবাদ আইনত ও নৈতিকভাবে দণ্ডনীয় অপরাধ।'
                            : 'False accusation or harassment based on name confusion is legally and ethically unacceptable.'
                    }}
                </p>
            </div>

            <!-- Results Section -->
            <div class="space-y-4">
                <div
                    v-if="initialQuery"
                    class="flex items-center justify-between border-b border-black/[0.06] pb-3 text-xs text-slate-600 dark:border-white/[0.08] dark:text-slate-400"
                >
                    <div>
                        {{ isBn ? 'অনুসন্ধানের ফলাফল:' : 'Results for:' }}
                        <strong class="text-slate-900 dark:text-slate-200"
                            >"{{ initialQuery }}"</strong
                        >
                    </div>
                    <div>
                        {{ isBn ? 'পাওয়া গেছে:' : 'Found:' }}
                        <strong class="text-slate-900 dark:text-white">{{
                            formatNumberByLocale(normalizedResults.length, locale)
                        }}</strong>
                        {{ isBn ? 'টি প্রোফাইল' : 'profile(s)' }}
                    </div>
                </div>

                <!-- Profile Cards List -->
                <div v-if="normalizedResults.length > 0" class="space-y-4">
                    <div
                        v-for="profile in normalizedResults"
                        :key="profile.uuid"
                        class="flex flex-col items-start justify-between gap-4 rounded-2xl p-5 apple-glass-card shadow-glass transition-all duration-300 hover:-translate-y-0.5 hover:shadow-glass-lg sm:flex-row sm:items-center"
                    >
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <h3
                                    class="text-base font-bold text-slate-900 transition-colors hover:text-rose-600 dark:text-white dark:hover:text-rose-400"
                                >
                                    <Link :href="`/profiles/${profile.uuid}`">
                                        {{
                                            profile.display_name ??
                                            (isBn
                                                ? 'নামবিহীন ফেসবুক ব্যবহারকারী'
                                                : 'Facebook User')
                                        }}
                                    </Link>
                                </h3>
                                <span
                                    class="rounded-full border border-black/[0.08] bg-black/[0.03] px-2.5 py-0.5 text-[10px] font-medium text-slate-600 dark:border-white/[0.1] dark:bg-white/[0.08] dark:text-slate-400"
                                >
                                    Facebook
                                </span>
                            </div>

                            <div
                                class="break-all font-mono text-xs text-slate-500 dark:text-slate-400"
                            >
                                <a
                                    :href="profile.canonical_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700 hover:underline dark:text-slate-400 dark:hover:text-slate-300"
                                >
                                    <span>{{ profile.canonical_url }}</span>
                                    <svg
                                        class="h-3 w-3 shrink-0"
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

                            <div
                                class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                <span
                                    class="flex items-center gap-1 font-semibold text-rose-600 dark:text-rose-400"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-rose-500"
                                    ></span>
                                    {{
                                        isBn
                                            ? 'যাচাইকৃত মন্তব্য:'
                                            : 'Verified comments:'
                                    }}
                                    {{
                                        isBn
                                            ? profile.public_reports_count_bn
                                            : formatNumberByLocale(
                                                  profile.public_reports_count,
                                                  locale,
                                              )
                                    }}
                                </span>

                                <span
                                    v-if="profile.district"
                                    class="text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        isBn ? 'জেলা (দাবি করা):' : 'District:'
                                    }}
                                    {{
                                        isBn
                                            ? profile.district.name_bn
                                            : profile.district.name_en
                                    }}
                                </span>

                                <span
                                    v-if="profile.first_recorded_at"
                                    class="text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        isBn
                                            ? 'প্রথম নথিবদ্ধ:'
                                            : 'First recorded:'
                                    }}
                                    {{
                                        formatDateByLocale(
                                            profile.first_recorded_at,
                                            locale,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <Link
                            :href="`/profiles/${profile.uuid}`"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-black/[0.08] bg-black/[0.03] px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-black/[0.06] active:scale-95 dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-slate-200 dark:hover:bg-white/[0.14]"
                        >
                            <span>{{
                                isBn ? 'বিস্তারিত রেকর্ড দেখুন' : 'View Records'
                            }}</span>
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
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="initialQuery"
                    class="space-y-3 rounded-2xl py-12 text-center apple-glass-card shadow-glass"
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
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                            />
                        </svg>
                    </div>
                    <div
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? 'কোনো যাচাইকৃত রেকর্ড পাওয়া যায়নি'
                                : 'No Verified Records Found'
                        }}
                    </div>
                    <p
                        class="mx-auto max-w-md text-xs text-slate-600 dark:text-slate-400"
                    >
                        {{
                            isBn
                                ? 'এই নাম বা প্রোফাইল লিঙ্কের অধীনে আমাদের সংরক্ষণাগারে কোনো প্রকাশিত রেকর্ড নেই। সঠিক ইউআরএল ব্যবহার করার চেষ্টা করুন।'
                                : 'No published records under this name or URL in our archive. Make sure you use the full profile URL.'
                        }}
                    </p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
