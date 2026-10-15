<script setup lang="ts">
import DistrictMap, { type DistrictStat } from '@/Components/DistrictMap.vue';
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatNumberByLocale } from '@/utils/bangla';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

export interface DistrictListItem {
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
    incidents_count?: number;
    incidents_count_bn?: string;
}

const props = defineProps<{
    districts: DistrictListItem[];
    incidentStats: DistrictStat[];
    commenterStats: DistrictStat[];
}>();

const { isBn, locale } = useLanguage();
const filterSearch = ref('');
const activeMode = ref<'incident' | 'commenter'>('incident');

const currentStats = computed(() => {
    return activeMode.value === 'incident'
        ? props.incidentStats
        : props.commenterStats;
});

const statsLookup = computed(() => {
    const map = new Map<string, DistrictStat>();
    for (const stat of currentStats.value) {
        map.set(stat.slug, stat);
    }
    return map;
});

const filteredDistricts = computed(() => {
    const q = filterSearch.value.trim().toLowerCase();
    if (!q) return props.districts;

    return props.districts.filter(
        (d) =>
            d.name_en.toLowerCase().includes(q) ||
            d.name_bn.includes(q) ||
            (d.division?.name_en.toLowerCase().includes(q) ?? false) ||
            (d.division?.name_bn.includes(q) ?? false),
    );
});

function handleModeChange(mode: 'incident' | 'commenter') {
    activeMode.value = mode;
}
</script>

<template>
    <PublicLayout
        :title="isBn ? 'জেলা মানচিত্র ও পরিসংখ্যান' : 'Districts & Statistics'"
    >
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'বাংলাদেশের ৬৪ জেলার জনসংখ্যা, সহিংসতা এবং ভিকটিম-ব্লেমিং মন্তব্যের ভৌগোলিক বিস্তার।'
                        : '64 district geographic data, incidents and comments on record.'
                "
            />
        </Head>

        <div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="space-y-3 text-center">
                <h1
                    class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? '৬৪ জেলার ভৌগোলিক মানচিত্র ও পরিসংখ্যান'
                            : '64 District Map & Statistics'
                    }}
                </h1>
                <p
                    class="mx-auto max-w-2xl text-xs text-slate-600 dark:text-slate-400 sm:text-sm"
                >
                    {{
                        isBn
                            ? '২০২২ সালের বাংলাদেশ পরিসংখ্যান ব্যুরো (BBS) জনশুমারি অনুযায়ী প্রতি ১ লাখ জনসংখ্যায় সহিংসতা ও সহিংসতার সমর্থক মন্তব্যের তুলনামূলক হার।'
                            : 'Normalized rate of incidents and comments per 100k population using BBS Census 2022.'
                    }}
                </p>
            </div>

            <!-- Map View -->
            <div class="mx-auto max-w-4xl">
                <DistrictMap
                    :stats="currentStats"
                    :initial-mode="activeMode"
                    @mode-change="handleModeChange"
                />
            </div>

            <!-- District Table Filter & Search -->
            <div class="space-y-4">
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 dark:border-slate-800 sm:flex-row sm:items-center"
                >
                    <div>
                        <h2
                            class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{
                                isBn
                                    ? 'সকল জেলার পরিসংখ্যান তালিকা'
                                    : 'District Table View'
                            }}
                        </h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{
                                isBn
                                    ? 'জেলা অনুযায়ী বিস্তারিত দেখতে নামের উপর ক্লিক করুন।'
                                    : 'Click on any district to view its full record.'
                            }}
                        </p>
                    </div>

                    <div class="w-full sm:w-64">
                        <input
                            v-model="filterSearch"
                            type="text"
                            :placeholder="
                                isBn
                                    ? 'জেলা বা বিভাগ খুঁজুন...'
                                    : 'Filter districts or division...'
                            "
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 shadow-sm focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-400"
                        />
                    </div>
                </div>

                <!-- Districts Table -->
                <div
                    class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <table
                        class="w-full text-left text-xs text-slate-700 dark:text-slate-300"
                    >
                        <thead
                            class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-wider text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                        >
                            <tr>
                                <th class="px-4 py-3">
                                    {{ isBn ? 'জেলা' : 'District' }}
                                </th>
                                <th class="px-4 py-3">
                                    {{ isBn ? 'বিভাগ' : 'Division' }}
                                </th>
                                <th class="px-4 py-3 text-right">
                                    {{
                                        isBn
                                            ? 'জনসংখ্যা (আদমশুমারি ২২)'
                                            : 'Population (2022)'
                                    }}
                                </th>
                                <th class="px-4 py-3 text-right">
                                    {{ isBn ? 'রেকর্ড সংখ্যা' : 'Records' }}
                                </th>
                                <th class="px-4 py-3 text-right">
                                    {{
                                        isBn
                                            ? 'প্রতি ১ লাখে হার'
                                            : 'Rate / 100k'
                                    }}
                                </th>
                                <th class="px-4 py-3 text-center">
                                    {{ isBn ? 'অ্যাকশন' : 'Action' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-200 dark:divide-slate-800/80"
                        >
                            <tr
                                v-for="d in filteredDistricts"
                                :key="d.code"
                                class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td
                                    class="px-4 py-3 font-semibold text-slate-900 dark:text-white"
                                >
                                    <Link
                                        :href="`/districts/${d.slug}`"
                                        class="transition-colors hover:text-rose-600 dark:hover:text-rose-400"
                                    >
                                        {{ isBn ? d.name_bn : d.name_en }}
                                    </Link>
                                    <span
                                        class="block text-[10px] font-normal text-slate-500 dark:text-slate-400"
                                        >{{ d.name_en }}</span
                                    >
                                </td>

                                <td
                                    class="px-4 py-3 text-slate-600 dark:text-slate-400"
                                >
                                    {{
                                        isBn
                                            ? (d.division?.name_bn ??
                                              d.division?.name_en)
                                            : d.division?.name_en
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-right font-mono text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        formatNumberByLocale(
                                            d.population,
                                            locale,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-right font-bold text-rose-600 dark:text-rose-400"
                                >
                                    {{
                                        formatNumberByLocale(
                                            statsLookup.get(d.slug)?.count ?? 0,
                                            locale,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-right font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        formatNumberByLocale(
                                            statsLookup.get(d.slug)
                                                ?.rate_per_100k ?? 0,
                                            locale,
                                        )
                                    }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <Link
                                        :href="`/districts/${d.slug}`"
                                        class="rounded border border-slate-300 bg-white px-2.5 py-1 text-[11px] text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-transparent dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white"
                                    >
                                        {{ isBn ? 'দেখুন' : 'View' }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
