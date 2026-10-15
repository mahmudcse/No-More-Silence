<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { formatDateByLocale, formatNumberByLocale } from '@/utils/bangla';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
                    class="text-2xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'রেকর্ড ও প্রোফাইল অনুসন্ধান'
                            : 'Search Records & Profiles'
                    }}
                </h1>
                <p class="text-xs text-slate-400 sm:text-sm">
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
                class="relative flex items-center shadow-lg"
            >
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"
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
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 py-4 pl-12 pr-28 text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500"
                />
                <button
                    type="submit"
                    class="absolute right-2 rounded-lg bg-rose-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-500"
                >
                    {{ isBn ? 'অনুসন্ধান' : 'Search' }}
                </button>
            </form>

            <!-- MANDATORY SAME-NAME WARNING BANNER -->
            <div
                v-if="warning || searchType === 'name'"
                class="space-y-2 rounded-xl border border-amber-800/80 bg-amber-950/40 p-4 text-amber-200 sm:p-5"
            >
                <div
                    class="flex items-center gap-2 text-sm font-bold text-amber-300"
                >
                    <span class="text-lg">⚠️</span>
                    <span>{{
                        isBn
                            ? 'জরুরি আইনি ও নৈতিক সতর্কতা'
                            : 'Legal & Ethical Notice'
                    }}</span>
                </div>
                <p
                    class="text-xs font-medium leading-relaxed text-amber-200/90 sm:text-sm"
                >
                    {{
                        isBn
                            ? 'সতর্কতা: বাংলাদেশে একই নামে বহু মানুষ থাকতে পারেন। প্রোফাইলের সুনির্দিষ্ট ডিজিটাল লিঙ্ক ও অনন্য আইডি নিশ্চিত না হয়ে কাউকে দোষারোপ করবেন না।'
                            : 'Warning: Multiple individuals may share the same name in Bangladesh. Do not attribute comments to any person without confirming their specific profile link and numeric ID.'
                    }}
                </p>
                <p class="text-[11px] text-amber-300/70">
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
                    class="flex items-center justify-between border-b border-slate-800 pb-3 text-xs text-slate-400"
                >
                    <div>
                        {{ isBn ? 'অনুসন্ধানের ফলাফল:' : 'Results for:' }}
                        <strong class="text-slate-200"
                            >"{{ initialQuery }}"</strong
                        >
                    </div>
                    <div>
                        {{ isBn ? 'পাওয়া গেছে:' : 'Found:' }}
                        <strong class="text-white">{{
                            formatNumberByLocale(results.length, locale)
                        }}</strong>
                        {{ isBn ? 'টি প্রোফাইল' : 'profile(s)' }}
                    </div>
                </div>

                <!-- Profile Cards List -->
                <div v-if="results.length > 0" class="space-y-4">
                    <div
                        v-for="profile in results"
                        :key="profile.uuid"
                        class="flex flex-col items-start justify-between gap-4 rounded-xl border border-slate-800 bg-slate-900/80 p-5 transition-colors hover:border-slate-700 sm:flex-row sm:items-center"
                    >
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <h3
                                    class="text-base font-bold text-white transition-colors hover:text-rose-400"
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
                                    class="rounded border border-slate-700 bg-slate-800 px-2 py-0.5 text-[10px] text-slate-400"
                                >
                                    Facebook
                                </span>
                            </div>

                            <div
                                class="break-all font-mono text-xs text-slate-400"
                            >
                                <a
                                    :href="profile.canonical_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-slate-400 hover:text-slate-300 hover:underline"
                                >
                                    {{ profile.canonical_url }} ↗
                                </a>
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-1 text-xs text-slate-400"
                            >
                                <span
                                    class="flex items-center gap-1 font-semibold text-rose-400"
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
                                    class="text-slate-400"
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
                                    class="text-slate-400"
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
                            class="shrink-0 rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-200 transition-colors hover:bg-slate-700 hover:text-white"
                        >
                            {{
                                isBn
                                    ? 'বিস্তারিত রেকর্ড দেখুন →'
                                    : 'View Records →'
                            }}
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="initialQuery"
                    class="space-y-3 rounded-xl border border-slate-800 bg-slate-900/50 py-12 text-center"
                >
                    <div class="text-3xl text-slate-400">🔍</div>
                    <div class="text-base font-bold text-white">
                        {{
                            isBn
                                ? 'কোনো যাচাইকৃত রেকর্ড পাওয়া যায়নি'
                                : 'No Verified Records Found'
                        }}
                    </div>
                    <p class="mx-auto max-w-md text-xs text-slate-400">
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
