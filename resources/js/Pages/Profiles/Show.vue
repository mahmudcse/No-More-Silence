<script setup lang="ts">
import ReportCard, { type ReportItem } from '@/Components/ReportCard.vue';
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateByLocale, formatNumberByLocale } from '@/utils/bangla';
import { Head, Link } from '@inertiajs/vue3';

export interface ProfileDetail {
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
    reports?: ReportItem[];
}

defineProps<{
    profile: ProfileDetail;
}>();

const { isBn, locale } = useLanguage();
</script>

<template>
    <PublicLayout :title="profile.display_name ?? 'Facebook Profile'">
        <!-- SEO ROBOTS NOINDEX FOR INDIVIDUAL PROFILE PAGES -->
        <Head>
            <meta name="robots" content="noindex, nofollow" />
        </Head>

        <div class="mx-auto max-w-4xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Profile Identity Card (NO PHOTOS/AVATARS EXPOSED) -->
            <div
                class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-xl sm:p-8"
            >
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 dark:border-slate-800 sm:flex-row sm:items-center"
                >
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Facebook Profile
                            </span>
                            <span
                                class="flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-rose-500"
                                ></span>
                                {{
                                    isBn
                                        ? 'যাচাইকৃত রেকর্ডভুক্ত'
                                        : 'Documented & Verified'
                                }}
                            </span>
                        </div>
                        <h1
                            class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl"
                        >
                            {{
                                profile.display_name ??
                                (isBn
                                    ? 'নামবিহীন ফেসবুক ব্যবহারকারী'
                                    : 'Facebook User')
                            }}
                        </h1>
                    </div>

                    <div class="text-right sm:text-right">
                        <div
                            class="text-2xl font-black text-rose-600 dark:text-rose-400 sm:text-3xl"
                        >
                            {{
                                isBn
                                    ? profile.public_reports_count_bn
                                    : formatNumberByLocale(
                                          profile.public_reports_count,
                                          locale,
                                      )
                            }}
                        </div>
                        <div
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isBn ? 'যাচাইকৃত মন্তব্য' : 'Verified Comments'
                            }}
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 pt-2 text-xs text-slate-700 dark:text-slate-300 sm:grid-cols-2"
                >
                    <div>
                        <span
                            class="block text-slate-500 dark:text-slate-400"
                            >{{
                                isBn
                                    ? 'স্থায়ী ফেসবুক প্রোফাইল লিংক:'
                                    : 'Permanent Profile Link:'
                            }}</span
                        >
                        <a
                            :href="profile.canonical_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 break-all font-mono text-rose-600 underline underline-offset-2 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                        >
                            <span>{{ profile.canonical_url }}</span>
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
                                    d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"
                                />
                            </svg>
                        </a>
                    </div>

                    <div v-if="profile.district">
                        <span
                            class="block text-slate-500 dark:text-slate-400"
                            >{{
                                isBn
                                    ? 'জেলা (মন্তব্যকারীর প্রোফাইল দাবি অনুসারে):'
                                    : 'Self-Declared District:'
                            }}</span
                        >
                        <span
                            class="font-medium text-slate-900 dark:text-slate-200"
                        >
                            {{
                                isBn
                                    ? profile.district.name_bn
                                    : profile.district.name_en
                            }}
                        </span>
                        <span
                            class="block text-[10px] text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? '(ব্যবহারকারীর স্ব-ঘোষিত তথ্য; স্বাধীনভাবে যাচাই করা নয়)'
                                    : '(Self-declared; not independently verified)'
                            }}
                        </span>
                    </div>

                    <div v-if="profile.first_recorded_at">
                        <span
                            class="block text-slate-500 dark:text-slate-400"
                            >{{
                                isBn
                                    ? 'প্রথম নথিবদ্ধকরণ তারিখ:'
                                    : 'First Recorded:'
                            }}</span
                        >
                        <span
                            class="font-medium text-slate-900 dark:text-slate-200"
                        >
                            {{
                                formatDateByLocale(
                                    profile.first_recorded_at,
                                    locale,
                                )
                            }}
                        </span>
                    </div>

                    <div v-if="profile.last_recorded_at">
                        <span
                            class="block text-slate-500 dark:text-slate-400"
                            >{{
                                isBn
                                    ? 'সর্বশেষ নথিবদ্ধকরণ তারিখ:'
                                    : 'Last Recorded:'
                            }}</span
                        >
                        <span
                            class="font-medium text-slate-900 dark:text-slate-200"
                        >
                            {{
                                formatDateByLocale(
                                    profile.last_recorded_at,
                                    locale,
                                )
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right of Reply / Appeal Banner -->
            <div
                class="space-y-2 rounded-xl border border-blue-200 bg-blue-50/70 p-5 text-xs text-blue-900 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-200"
            >
                <div
                    class="flex items-center gap-2 text-sm font-bold text-blue-900 dark:text-blue-300"
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
                            d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"
                        />
                    </svg>
                    <span>{{
                        isBn
                            ? 'উত্তর দেওয়ার অধিকার ও আপিল প্রক্রিয়া'
                            : 'Right of Reply & Appeal Process'
                    }}</span>
                </div>
                <p class="leading-relaxed text-blue-800 dark:text-blue-200/90">
                    {{
                        isBn
                            ? 'আমরা নিরপেক্ষ তথ্যের প্রতি দায়বদ্ধ। যদি আপনার ফেসবুক অ্যাকাউন্ট হ্যাক হয়ে থাকে, কিংবা প্রোফাইলটি আপনার না হয়, তবে উপযুক্ত প্রমাণসহ আপিল করতে পারেন। আমাদের স্বাধীন মডারেটর দল তা পুনঃমূল্যায়ন করবে।'
                            : 'We are committed to factual accuracy. If your account was compromised or if this is a mistaken identity, you have the right to appeal with corroborating evidence.'
                    }}
                </p>
                <div class="pt-1">
                    <Link
                        href="/methodology"
                        class="inline-flex items-center gap-1 font-medium text-blue-700 underline hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        <span>{{
                            isBn
                                ? 'আপিল নীতিমালা ও শর্তাবলি জানুন'
                                : 'Learn about our appeal guidelines'
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

            <!-- Verified Comments Records List -->
            <div class="space-y-6">
                <div
                    class="border-b border-slate-200 pb-3 dark:border-slate-800"
                >
                    <h2
                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? 'যাচাইকৃত ও সংরক্ষিত মন্তব্যের তালিকা'
                                : 'Verified Comments on Record'
                        }}
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        {{
                            isBn
                                ? 'প্রতিটি মন্তব্য ঘটনার প্রেক্ষাপটে এবং স্বাধীন স্ক্রিনশট ও ডিজিটাল হ্যাশ প্রমাণের ভিত্তিতে নথিবদ্ধ।'
                                : 'Every comment is preserved in incident context with tamper-evident hashes.'
                        }}
                    </p>
                </div>

                <div
                    v-if="profile.reports && profile.reports.length > 0"
                    class="space-y-6"
                >
                    <ReportCard
                        v-for="report in profile.reports"
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
                            ? 'এই প্রোফাইলের অধীনে কোনো উন্মুক্ত পাবলিক রেকর্ড পাওয়া যায়নি।'
                            : 'No public records found for this profile.'
                    }}
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
