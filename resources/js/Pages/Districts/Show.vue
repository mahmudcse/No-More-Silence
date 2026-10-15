<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { formatDateByLocale, formatNumberByLocale } from '@/utils/bangla';
import { Head, Link } from '@inertiajs/vue3';

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
    incidents_count: number;
    incidents_count_bn: string;
}

export interface IncidentSummary {
    id: number;
    title_en: string;
    title_bn: string;
    slug: string;
    occurred_on: string | null;
    summary: string | null;
    reports_count?: number;
}

defineProps<{
    district: DistrictDetail;
    incidents: IncidentSummary[];
}>();

const { isBn, locale } = useLanguage();
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
                :content="`${isBn ? district.name_bn : district.name_en} জেলা — No More Silence`"
            />
            <meta
                property="og:description"
                :content="`${isBn ? district.name_bn : district.name_en} জেলার জনমিতি ও নথিবদ্ধ ঘটনার সার্বিক তথ্য।`"
            />
        </Head>

        <div class="mx-auto max-w-5xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <Link href="/" class="transition-colors hover:text-white">{{
                    isBn ? 'হোম' : 'Home'
                }}</Link>
                <span>/</span>
                <Link
                    href="/districts"
                    class="transition-colors hover:text-white"
                    >{{ isBn ? 'জেলাসমূহ' : 'Districts' }}</Link
                >
                <span>/</span>
                <span class="text-slate-300">{{
                    isBn ? district.name_bn : district.name_en
                }}</span>
            </div>

            <!-- District Overview Card -->
            <div
                class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl sm:p-8"
            >
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-800 pb-4 sm:flex-row sm:items-center"
                >
                    <div class="space-y-1">
                        <span
                            class="rounded border border-slate-700 bg-slate-800 px-2.5 py-0.5 text-xs font-semibold text-slate-300"
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
                            class="text-2xl font-black tracking-tight text-white sm:text-4xl"
                        >
                            {{ isBn ? district.name_bn : district.name_en }}
                            <span
                                class="block text-base font-normal text-slate-400 sm:ml-2 sm:inline"
                                >({{ district.name_en }})</span
                            >
                        </h1>
                    </div>

                    <div class="text-left sm:text-right">
                        <div
                            class="text-2xl font-extrabold text-rose-400 sm:text-3xl"
                        >
                            {{
                                formatNumberByLocale(
                                    district.incidents_count,
                                    locale,
                                )
                            }}
                        </div>
                        <div class="text-xs font-medium text-slate-400">
                            {{ isBn ? 'নথিবদ্ধ ঘটনা' : 'Incidents on Record' }}
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 pt-2 text-xs text-slate-300 sm:grid-cols-3"
                >
                    <div>
                        <span class="block text-slate-400">{{
                            isBn
                                ? 'জনসংখ্যা (আদমশুমারি ২০২২):'
                                : 'Population (Census 2022):'
                        }}</span>
                        <strong class="font-mono text-sm text-white">{{
                            formatNumberByLocale(district.population, locale)
                        }}</strong>
                    </div>

                    <div>
                        <span class="block text-slate-400">{{
                            isBn ? 'বিবিএস জিওকোড (BBS Code):' : 'BBS Geocode:'
                        }}</span>
                        <strong class="font-mono text-sm text-white">{{
                            district.code
                        }}</strong>
                    </div>

                    <div>
                        <span class="block text-slate-400">{{
                            isBn ? 'বিভাগীয় অঞ্চল:' : 'Division:'
                        }}</span>
                        <strong class="text-sm text-white">{{
                            isBn
                                ? (district.division?.name_bn ??
                                  district.division?.name_en)
                                : district.division?.name_en
                        }}</strong>
                    </div>
                </div>
            </div>

            <!-- Incidents in this District -->
            <div class="space-y-4">
                <div class="border-b border-slate-800 pb-3">
                    <h2 class="text-xl font-bold tracking-tight text-white">
                        {{
                            isBn
                                ? `${district.name_bn} জেলার নথিবদ্ধ ঘটনাসমূহ`
                                : `Incidents Recorded in ${district.name_en}`
                        }}
                    </h2>
                    <p class="text-xs text-slate-400">
                        {{
                            isBn
                                ? 'এই জেলায় সংঘটিত সহিংসতা ও সংশ্লিষ্ট নথিবদ্ধ মন্তব্যের তালিকা।'
                                : 'Incidents occurring in this district and documented comments.'
                        }}
                    </p>
                </div>

                <div
                    v-if="incidents.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                >
                    <div
                        v-for="inc in incidents"
                        :key="inc.id"
                        class="flex flex-col justify-between space-y-3 rounded-xl border border-slate-800 bg-slate-900/70 p-5 transition-colors hover:border-slate-700"
                    >
                        <div class="space-y-2">
                            <span
                                v-if="inc.occurred_on"
                                class="text-[11px] text-slate-400"
                            >
                                {{
                                    formatDateByLocale(inc.occurred_on, locale)
                                }}
                            </span>
                            <h3
                                class="text-base font-bold leading-snug text-white"
                            >
                                <Link
                                    :href="`/incidents/${inc.slug}`"
                                    class="transition-colors hover:text-rose-400"
                                >
                                    {{ isBn ? inc.title_bn : inc.title_en }}
                                </Link>
                            </h3>
                            <p
                                v-if="inc.summary"
                                class="line-clamp-2 text-xs leading-relaxed text-slate-400"
                            >
                                {{ inc.summary }}
                            </p>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-slate-800/80 pt-3 text-xs"
                        >
                            <span class="font-semibold text-rose-400">
                                {{
                                    formatNumberByLocale(
                                        inc.reports_count ?? 0,
                                        locale,
                                    )
                                }}
                                {{ isBn ? 'টি মন্তব্য' : 'comments' }}
                            </span>
                            <Link
                                :href="`/incidents/${inc.slug}`"
                                class="font-medium text-slate-300 hover:text-white"
                            >
                                {{ isBn ? 'বিস্তারিত দেখুন →' : 'View →' }}
                            </Link>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="rounded-xl border border-slate-800 bg-slate-900/40 py-12 text-center text-sm text-slate-400"
                >
                    {{
                        isBn
                            ? 'এই জেলায় বর্তমানে কোনো ঘটনা নথিবদ্ধ নেই।'
                            : 'No incidents currently recorded in this district.'
                    }}
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
