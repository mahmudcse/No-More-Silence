<script setup lang="ts">
import ReportCard, { type ReportItem } from '@/Components/ReportCard.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { formatNumberByLocale } from '@/utils/bangla';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface DistrictDetail {
    id: number;
    code: string;
    name_en: string;
    name_bn: string;
    slug: string;
    population: number;
    population_bn: string;
    division?: {
        name_en: string;
        name_bn: string;
    } | null;
    reports_count?: number;
    reports_count_bn?: string;
    incidents_count?: number;
    incidents_count_bn?: string;
}

const props = defineProps<{
    district: DistrictDetail;
    reports?: {
        data: ReportItem[];
        links?: Array<{ url: string | null; label: string; active: boolean }>;
    } | ReportItem[];
    incidents?: any[];
}>();

const { isBn, locale } = useLanguage();

const reportsList = computed<ReportItem[]>(() => {
    if (Array.isArray(props.reports)) {
        return props.reports;
    }
    return props.reports?.data ?? [];
});
</script>

<template>
    <PublicLayout :title="isBn ? district.name_bn : district.name_en">
        <!-- SEO Metadata -->
        <Head>
            <meta
                name="description"
                :content="`${isBn ? district.name_bn : district.name_en} জেলার সহিংসতা ও ভিকটিম-ব্লেমিং সম্পর্কিত নথিবদ্ধ রেকর্ড।`"
            />
            <meta
                property="og:title"
                :content="`${isBn ? district.name_bn : district.name_en} জেলা | No More Silence`"
            />

            <meta
                property="og:description"
                :content="`${isBn ? district.name_bn : district.name_en} জেলার জনমিতি ও নথিবদ্ধ ঘটনার সার্বিক তথ্য।`"
            />
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
                <Link
                    href="/districts"
                    class="transition-colors hover:text-slate-900 dark:hover:text-white"
                    >{{ isBn ? 'জেলাসমূহ' : 'Districts' }}</Link
                >
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-300">{{
                    isBn ? district.name_bn : district.name_en
                }}</span>
            </div>

            <!-- District Overview Card -->
            <div
                class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-xl sm:p-8"
            >
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 dark:border-slate-800 sm:flex-row sm:items-center"
                >
                    <div class="space-y-1">
                        <span
                            class="rounded border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{
                                isBn
                                    ? (district.division?.name_bn ??
                                      district.division?.name_en)
                                    : district.division?.name_en
                            }}
                            {{ isBn ? 'বিভাগ' : 'Division' }}
                        </span>
                        <h1
                            class="text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                        >
                            {{ isBn ? district.name_bn : district.name_en }}
                            <span
                                class="block text-base font-normal text-slate-500 dark:text-slate-400 sm:ml-2 sm:inline"
                                >({{ district.name_en }})</span
                            >
                        </h1>
                    </div>

                    <div class="text-left sm:text-right">
                        <div
                            class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 sm:text-3xl"
                        >
                            {{
                                formatNumberByLocale(
                                    district.reports_count ?? district.incidents_count ?? 0,
                                    locale,
                                )
                            }}
                        </div>
                        <div
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{ isBn ? 'নথিবদ্ধ মন্তব্য' : 'Verified Comments' }}
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 pt-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-3"
                >
                    <div>
                        <span
                            class="block text-slate-500 dark:text-slate-400"
                            >{{
                                isBn
                                    ? 'জনসংখ্যা (আদমশুমারি ২০২২):'
                                    : 'Population (Census 2022):'
                            }}</span
                        >
                        <strong
                            class="font-mono text-sm text-slate-900 dark:text-white"
                            >{{
                                formatNumberByLocale(
                                    district.population,
                                    locale,
                                )
                            }}</strong
                        >
                    </div>

                    <div>
                        <span
                            class="block text-slate-500 dark:text-slate-400"
                            >{{
                                isBn
                                    ? 'বিবিএস জিওকোড (BBS Code):'
                                    : 'BBS Geocode:'
                            }}</span
                        >
                        <strong
                            class="font-mono text-sm text-slate-900 dark:text-white"
                            >{{ district.code }}</strong
                        >
                    </div>

                    <div>
                        <span
                            class="block text-slate-500 dark:text-slate-400"
                            >{{ isBn ? 'বিভাগীয় অঞ্চল:' : 'Division:' }}</span
                        >
                        <strong
                            class="text-sm text-slate-900 dark:text-white"
                            >{{
                                isBn
                                    ? (district.division?.name_bn ??
                                      district.division?.name_en)
                                    : district.division?.name_en
                            }}</strong
                        >
                    </div>
                </div>
            </div>

            <!-- Verified Comments in this District -->
            <div class="space-y-4">
                <div
                    class="border-b border-slate-200 pb-3 dark:border-slate-800"
                >
                    <h2
                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? `${district.name_bn} জেলার নথিবদ্ধ মন্তব্যসমূহ`
                                : `Verified Comments in ${district.name_en}`
                        }}
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        {{
                            isBn
                                ? 'এই জেলায় সংশ্লিষ্ট নথিবদ্ধ মন্তব্যের প্রমাণ ও বিবরণ।'
                                : 'Verified comments documented for this district.'
                        }}
                    </p>
                </div>

                <div
                    v-if="reportsList.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                >
                    <ReportCard
                        v-for="rep in reportsList"
                        :key="rep.uuid"
                        :report="rep"
                    />
                </div>
                <div
                    v-else
                    class="rounded-xl border border-slate-200 bg-white py-12 text-center text-sm text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400"
                >
                    {{
                        isBn
                            ? 'এই জেলায় বর্তমানে কোনো মন্তব্য নথিবদ্ধ নেই।'
                            : 'No verified comments currently documented in this district.'
                    }}
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
