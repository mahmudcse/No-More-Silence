<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head } from '@inertiajs/vue3';

interface OpenDataSummary {
    metadata: {
        title: string;
        license: string;
        legal_compliance: string;
        generated_at: string;
    };
    totals: {
        verified_reports: number;
        incidents_covered: number;
        districts_affected: number;
        appeals_processed: number;
    };
    categories: Array<{
        key: string;
        label_en: string;
        label_bn: string;
        count: number;
        percentage: number;
    }>;
    districts: Array<{
        bbs_code: string;
        name_en: string;
        name_bn: string;
        division_en: string;
        population: number;
        verified_reports: number;
        rate_per_100k: number;
    }>;
    monthly_trends: Array<{
        period: string;
        verified_reports: number;
        appeals_filed: number;
        appeals_accepted: number;
    }>;
    appeals_resolution: {
        total: number;
        accepted: number;
        rejected: number;
        pending: number;
        acceptance_rate: number;
    };
}

defineProps<{
    summary: OpenDataSummary;
}>();

const { isBn } = useLanguage();
</script>

<template>
    <PublicLayout
        :title="
            isBn
                ? 'উন্মুক্ত গবেষণা উপাত্ত (Open Data)'
                : 'Open Research Data & Statistics'
        "
    >
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'গবেষক, মানবাধিকার কর্মী ও সাংবাদিকদের জন্য সহিংসতা-সমর্থনকারী মন্তব্যের উন্মুক্ত ও সম্পূর্ণ বেনামী উপাত্ত ভাণ্ডার।'
                        : 'Anonymized, aggregate open datasets and statistics on sexual violence apologia comments for researchers and journalists.'
                "
            />
        </Head>

        <div class="mx-auto max-w-6xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="border-b border-slate-200 pb-6 dark:border-slate-800">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-400"
                            >
                                {{ isBn ? 'উন্মুক্ত উপাত্ত' : 'Open Data' }}
                            </span>
                            <span
                                class="rounded border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                CC-BY 4.0
                            </span>
                        </div>
                        <h1
                            class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                        >
                            {{
                                isBn
                                    ? 'উন্মুক্ত গবেষণা উপাত্ত ও পরিসংখ্যান'
                                    : 'Public Open Data & Aggregates'
                            }}
                        </h1>
                        <p
                            class="mt-2 max-w-3xl text-sm text-slate-600 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? 'বাংলাদেশে নারীর প্রতি সহিংসতা ও সাইবার স্পেসে ভিকটিম-ব্লেমিংয়ের ধরণ বিশ্লেষণের জন্য গবেষক, শিক্ষাবিদ ও অনুসন্ধানী সাংবাদিকদের উদ্দেশ্যে তৈরি সম্পূর্ণ বেনামী ও সংকলিত ডাটাবেজ।'
                                    : 'A fully anonymized, aggregate public dataset designed for investigative journalists, criminologists, and human rights researchers analyzing patterns of violence justification in digital spaces.'
                            }}
                        </p>
                    </div>

                    <!-- Direct JSON API Button -->
                    <a
                        href="/api/v1/open-data/summary"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        <svg
                            class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"
                            />
                        </svg>
                        <span>JSON API Endpoint</span>
                    </a>
                </div>
            </div>

            <!-- Statutory Compliance Alert (Section 14) -->
            <div
                class="rounded-xl border border-rose-200 bg-rose-50/70 p-4 text-xs text-rose-900 shadow-sm dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200"
            >
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-rose-600 dark:text-rose-300">
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"
                            />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <strong
                            class="block font-bold text-rose-950 dark:text-white"
                        >
                            {{
                                isBn
                                    ? 'নারী ও শিশু নির্যাতন দমন আইন ২০০০ (ধারা ১৪) প্রতিপালন সংক্রান্ত নিশ্চয়তা:'
                                    : 'Statutory Privacy Compliance (Section 14, Prevention of Women & Children Repression Act 2000):'
                            }}
                        </strong>
                        <p
                            class="leading-relaxed text-rose-800 dark:text-rose-300"
                        >
                            {{ summary.metadata.legal_compliance }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Overview Stat Cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <div
                        class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400"
                    >
                        {{ isBn ? 'যাচাইকৃত রেকর্ড' : 'Verified Reports' }}
                    </div>
                    <div
                        class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ summary.totals.verified_reports }}
                    </div>
                    <div
                        class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400"
                    >
                        {{
                            isBn
                                ? '১০০% প্রমাণ যাচাইকৃত'
                                : '100% verified evidence'
                        }}
                    </div>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <div
                        class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400"
                    >
                        {{ isBn ? 'সম্পৃক্ত ঘটনা' : 'Incidents Covered' }}
                    </div>
                    <div
                        class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ summary.totals.incidents_covered }}
                    </div>
                    <div
                        class="mt-1 text-[11px] text-slate-500 dark:text-slate-400"
                    >
                        {{
                            isBn
                                ? 'সংবাদপত্রে প্রকাশিত ঘটনা'
                                : 'Publicized incidents'
                        }}
                    </div>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <div
                        class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400"
                    >
                        {{ isBn ? 'আক্রান্ত জেলা' : 'Districts Documented' }}
                    </div>
                    <div
                        class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ summary.totals.districts_affected }}
                    </div>
                    <div
                        class="mt-1 text-[11px] text-slate-500 dark:text-slate-400"
                    >
                        {{
                            isBn
                                ? '৬৪টি জেলার মধ্যে'
                                : 'Across 64 BBS districts'
                        }}
                    </div>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <div
                        class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400"
                    >
                        {{ isBn ? 'নিষ্পত্তিকৃত আপিল' : 'Appeals Processed' }}
                    </div>
                    <div
                        class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ summary.totals.appeals_processed }}
                    </div>
                    <div
                        class="mt-1 text-[11px] text-rose-600 dark:text-rose-400"
                    >
                        {{ summary.appeals_resolution.acceptance_rate }}%
                        {{ isBn ? 'গৃহীতের হার' : 'acceptance rate' }}
                    </div>
                </div>
            </div>

            <!-- Downloadable Datasets Card -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/60 dark:shadow-xl"
            >
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{
                        isBn
                            ? 'সরাসরি ডাউনলোডযোগ্য ডেটাসেট (CSV)'
                            : 'Downloadable Datasets (CSV)'
                    }}
                </h2>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                    {{
                        isBn
                            ? 'উপাত্তগুলো স্প্রেডশিট (Excel, Google Sheets) বা পাইথন/আর (Pandas, RStudio) এ সরাসরি বিশ্লেষণের উপযোগী।'
                            : 'Standardized UTF-8 CSV datasets formatted for Python, R, Stata, Excel, and GIS spatial mapping.'
                    }}
                </p>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <!-- Districts CSV -->
                    <div
                        class="flex flex-col justify-between rounded-xl border border-slate-200 bg-slate-50/80 p-4 transition-colors hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/70 dark:hover:border-slate-700"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-mono text-xs font-bold text-rose-600 dark:text-rose-400"
                                    >districts.csv</span
                                >
                                <span
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                    >64 rows</span
                                >
                            </div>
                            <h3
                                class="mt-2 text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? 'জেলাওয়ারি পরিসংখ্যান'
                                        : 'District Spatial Metrics'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'BBS কোড, জনসংখ্যা, যাচাইকৃত রিপোর্টের সংখ্যা এবং প্রতি লক্ষ জনসংখ্যায় অনুপাত।'
                                        : 'BBS codes, population, incident count, and per-100k normalized rates.'
                                }}
                            </p>
                        </div>
                        <a
                            href="/api/v1/open-data/districts.csv"
                            download="nomoresilence_districts_data.csv"
                            class="mt-4 flex items-center justify-center gap-2 rounded-lg border border-rose-200 bg-rose-100/70 px-3 py-2 text-xs font-bold text-rose-800 transition-colors hover:bg-rose-100 dark:border-rose-500/40 dark:bg-rose-600/20 dark:text-rose-300 dark:hover:bg-rose-600/30"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                />
                            </svg>
                            <span>Download CSV</span>
                        </a>
                    </div>

                    <!-- Categories CSV -->
                    <div
                        class="flex flex-col justify-between rounded-xl border border-slate-200 bg-slate-50/80 p-4 transition-colors hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/70 dark:hover:border-slate-700"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-mono text-xs font-bold text-amber-600 dark:text-amber-400"
                                    >categories.csv</span
                                >
                                <span
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                    >Categorical</span
                                >
                            </div>
                            <h3
                                class="mt-2 text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? 'সহিংসতার ধরণের শ্রেণীবিন্যাস'
                                        : 'Harassment Typology'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'ভিকটিম-ব্লেমিং, ধর্ষণের সাফাই, হুমকি ও ডক্সিং ক্যাটাগরির সংখ্যা ও শতাংশ।'
                                        : 'Aggregates by category (victim-blaming, sexual threat, incitement, doxxing).'
                                }}
                            </p>
                        </div>
                        <a
                            href="/api/v1/open-data/categories.csv"
                            download="nomoresilence_categories_data.csv"
                            class="mt-4 flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-100/70 px-3 py-2 text-xs font-bold text-amber-800 transition-colors hover:bg-amber-100 dark:border-amber-500/40 dark:bg-amber-600/20 dark:text-amber-300 dark:hover:bg-amber-600/30"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                />
                            </svg>
                            <span>Download CSV</span>
                        </a>
                    </div>

                    <!-- Monthly Trends CSV -->
                    <div
                        class="flex flex-col justify-between rounded-xl border border-slate-200 bg-slate-50/80 p-4 transition-colors hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/70 dark:hover:border-slate-700"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-mono text-xs font-bold text-sky-600 dark:text-sky-400"
                                    >monthly_trends.csv</span
                                >
                                <span
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                    >Time-series</span
                                >
                            </div>
                            <h3
                                class="mt-2 text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? 'মাসিক সময়ক্রম উপাত্ত'
                                        : 'Monthly Longitudinal Data'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'প্রতি মাসের রিপোর্ট প্রকাশ ও আপিল নিষ্পত্তির সময়ক্রম বিশ্লেষণ।'
                                        : 'Monthly time-series for tracking trends, viral spikes, and appeal rates.'
                                }}
                            </p>
                        </div>
                        <a
                            href="/api/v1/open-data/monthly.csv"
                            download="nomoresilence_monthly_trends_data.csv"
                            class="mt-4 flex items-center justify-center gap-2 rounded-lg border border-sky-200 bg-sky-100/70 px-3 py-2 text-xs font-bold text-sky-800 transition-colors hover:bg-sky-100 dark:border-sky-500/40 dark:bg-sky-600/20 dark:text-sky-300 dark:hover:bg-sky-600/30"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                />
                            </svg>
                            <span>Download CSV</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Categories Breakdown Table -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
            >
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    {{
                        isBn
                            ? 'ক্যাটাগরি অনুযায়ী সংকলিত রূপ'
                            : 'Typology Breakdown'
                    }}
                </h2>
                <div class="mt-4 overflow-x-auto">
                    <table
                        class="w-full text-left text-xs text-slate-700 dark:text-slate-300"
                    >
                        <thead
                            class="border-b border-slate-200 bg-slate-50 font-semibold uppercase text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400"
                        >
                            <tr>
                                <th class="px-3 py-2.5">
                                    {{
                                        isBn ? 'শ্রেণী (Category)' : 'Category'
                                    }}
                                </th>
                                <th class="px-3 py-2.5 text-right">
                                    {{
                                        isBn
                                            ? 'যাচাইকৃত সংখ্যা'
                                            : 'Verified Reports'
                                    }}
                                </th>
                                <th class="px-3 py-2.5 text-right">
                                    {{ isBn ? 'শতাংশ' : 'Share' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 font-mono dark:divide-slate-800/60"
                        >
                            <tr
                                v-for="cat in summary.categories"
                                :key="cat.key"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td
                                    class="px-3 py-2.5 font-sans text-slate-900 dark:text-slate-200"
                                >
                                    {{ isBn ? cat.label_bn : cat.label_en }}
                                    <span
                                        class="ml-2 font-mono text-[10px] text-slate-500 dark:text-slate-400"
                                        >({{ cat.key }})</span
                                    >
                                </td>
                                <td
                                    class="px-3 py-2.5 text-right font-bold text-slate-900 dark:text-white"
                                >
                                    {{ cat.count }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-right font-bold text-rose-600 dark:text-rose-400"
                                >
                                    {{ cat.percentage }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Academic Citation & Attribution Guide -->
            <div
                class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/40"
            >
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    {{
                        isBn
                            ? 'গবেষণায় তথ্যসূত্র উল্লেখের নিয়ম (Citation Guide)'
                            : 'Academic Citation & Methodology'
                    }}
                </h2>
                <p
                    class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                >
                    {{
                        isBn
                            ? 'আপনার গবেষণা প্রবন্ধ, জার্নাল কিংবা অনুসন্ধানী প্রতিবেদনে নো মোর সাইলেন্সের উপাত্ত ব্যবহারের সময় অনুগ্রহ করে নিম্নরূপ তথ্যসূত্র উল্লেখ করুন:'
                            : 'When utilizing these datasets in policy briefs, criminological studies, or news reporting, cite as follows:'
                    }}
                </p>

                <div
                    class="overflow-x-auto rounded-lg border border-slate-200 bg-slate-50 p-4 font-mono text-xs text-slate-800 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300"
                >
                    No More Silence Archive. ({{ new Date().getFullYear() }}).
                    <em
                        >Bangladesh Digital Violence Apologia and Victim-Blaming
                        Aggregate Dataset</em
                    >
                    (Version 1.0) [Data file]. Available from
                    https://nomoresilence.org/open-data
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
