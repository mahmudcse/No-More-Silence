<script setup lang="ts">
import ReportCard, { type ReportItem } from '@/Components/ReportCard.vue';
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateByLocale, formatNumberByLocale } from '@/utils/bangla';
import { Head, Link } from '@inertiajs/vue3';

export interface IncidentDetail {
    id: number;
    title_en: string;
    title_bn: string;
    slug: string;
    occurred_on: string | null;
    summary: string | null;
    district?: {
        name_en: string;
        name_bn: string;
        slug: string;
    } | null;
    public_reports_count: number;
    public_reports_count_bn: string;
    reports?: ReportItem[];
}

defineProps<{
    incident: IncidentDetail;
}>();

const { isBn, locale } = useLanguage();
</script>

<template>
    <PublicLayout :title="isBn ? incident.title_bn : incident.title_en">
        <!-- OpenGraph & SEO Metadata -->
        <Head>
            <meta
                name="description"
                :content="
                    incident.summary ??
                    (isBn ? incident.title_bn : incident.title_en)
                "
            />
            <meta
                property="og:title"
                :content="`${isBn ? incident.title_bn : incident.title_en} | No More Silence`"
            />

            <meta property="og:description" :content="incident.summary ?? ''" />
            <meta property="og:type" content="article" />
        </Head>

        <div class="mx-auto max-w-5xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <div
                class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
            >
                <Link
                    href="/"
                    class="transition-colors hover:text-slate-900 dark:hover:text-white"
                    >{{ isBn ? 'হোম' : 'Home' }}</Link
                >
                <span>/</span>
                <span
                    class="max-w-xs truncate text-slate-800 dark:text-slate-300"
                    >{{ isBn ? incident.title_bn : incident.title_en }}</span
                >
            </div>

            <!-- Incident Overview Header Card -->
            <div
                class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-xl sm:p-8"
            >
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span
                        v-if="incident.district"
                        class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1 font-semibold text-rose-700 dark:border-rose-900/60 dark:bg-rose-950 dark:text-rose-300"
                    >
                        <svg
                            class="h-3.5 w-3.5 shrink-0"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"
                            />
                        </svg>
                        <span>{{
                            isBn
                                ? incident.district.name_bn
                                : incident.district.name_en
                        }}</span>
                    </span>
                    <span
                        v-if="incident.occurred_on"
                        class="rounded-md border border-slate-200 bg-slate-100 px-2.5 py-1 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    >
                        {{ formatDateByLocale(incident.occurred_on, locale) }}
                    </span>
                </div>

                <h1
                    class="text-2xl font-black leading-tight tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                >
                    {{ isBn ? incident.title_bn : incident.title_en }}
                </h1>

                <p
                    v-if="incident.summary"
                    class="max-w-4xl border-t border-slate-100 pt-2 text-sm leading-relaxed text-slate-600 dark:border-slate-800/80 dark:text-slate-300 sm:text-base"
                >
                    {{ incident.summary }}
                </p>

                <div
                    class="flex items-center gap-2 pt-4 text-xs text-slate-600 dark:text-slate-400"
                >
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    <span>{{
                        isBn
                            ? 'এই ঘটনার অধীনে নথিবদ্ধ ও যাচাইকৃত মন্তব্য:'
                            : 'Verified comments documented for this incident:'
                    }}</span>
                    <strong
                        class="text-sm font-bold text-rose-600 dark:text-rose-400"
                    >
                        {{
                            isBn
                                ? incident.public_reports_count_bn
                                : formatNumberByLocale(
                                      incident.public_reports_count,
                                      locale,
                                  )
                        }}
                    </strong>
                </div>
            </div>

            <!-- Verified Reports List -->
            <div class="space-y-6">
                <div
                    class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                >
                    <div>
                        <h2
                            class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{
                                isBn
                                    ? 'যাচাইকৃত সর্বজনীন মন্তব্যসমূহ'
                                    : 'Verified Public Comments'
                            }}
                        </h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{
                                isBn
                                    ? 'এই ঘটনাকে ঘিরে সামাজিক মাধ্যমে প্রকাশিত ভিকটিম-ব্লেমিং ও সহিংসতার সমর্থনে মন্তব্য।'
                                    : 'Comments justifying violence or blaming the victim.'
                            }}
                        </p>
                    </div>

                    <Link
                        href="/methodology"
                        class="inline-flex items-center gap-1 text-xs text-rose-600 transition-colors hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                    >
                        <span>{{
                            isBn ? 'যাচাইকরণ পদ্ধতি' : 'Verification rules'
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

                <div
                    v-if="incident.reports && incident.reports.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                >
                    <ReportCard
                        v-for="report in incident.reports"
                        :key="report.uuid"
                        :report="report"
                    />
                </div>
                <div
                    v-else
                    class="rounded-xl border border-slate-200 bg-white py-12 text-center text-sm text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400"
                >
                    {{
                        isBn
                            ? 'বর্তমানে এই ঘটনার অধীনে কোনো উন্মুক্ত পাবলিক মন্তব্য নেই।'
                            : 'No public comments currently recorded for this incident.'
                    }}
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
