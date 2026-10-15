<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import { useTheme } from '@/composables/useTheme';
import bangladeshGeoData from '@/data/bangladesh-districts.json';
import { formatNumberByLocale } from '@/utils/bangla';
import { router } from '@inertiajs/vue3';
import { geoMercator, geoPath } from 'd3-geo';
import { computed, ref } from 'vue';

export interface DistrictStat {
    id: number;
    code: string;
    slug: string;
    name_en: string;
    name_bn: string;
    division: string;
    population: number;
    count: number;
    count_bn: string;
    rate_per_100k: number;
    rate_per_100k_bn: string;
}

const props = withDefaults(
    defineProps<{
        stats?: DistrictStat[];
        initialMode?: 'incident' | 'commenter';
    }>(),
    {
        stats: () => [],
        initialMode: 'incident',
    },
);

const emit = defineEmits<{
    (e: 'modeChange', mode: 'incident' | 'commenter'): void;
}>();

const { isBn, locale } = useLanguage();
const { isDark } = useTheme();
const currentMode = ref<'incident' | 'commenter'>(props.initialMode);
const hoveredDistrict = ref<{
    name_en: string;
    name_bn: string;
    division: string;
    slug: string;
    count: number;
    rate: number;
    population: number;
} | null>(null);

const tooltipPosition = ref({ x: 0, y: 0 });

// SVG Dimensions
const width = 600;
const height = 750;

// Setup Mercator projection fit to Bangladesh bounds
const projection = geoMercator().fitSize(
    [width, height],
    bangladeshGeoData as any,
);
const pathGenerator = geoPath().projection(projection);

// Map stats by district slug or name_en for quick lookup
const statsMap = computed(() => {
    const map = new Map<string, DistrictStat>();
    for (const item of props.stats) {
        map.set(item.slug.toLowerCase(), item);
        map.set(item.name_en.toLowerCase(), item);
    }
    return map;
});

// Prepare path elements with precomputed counts and color classes
const districtPaths = computed(() => {
    return (bangladeshGeoData.features as any[]).map((feature) => {
        const props = feature.properties || {};
        const slug = (props.slug || props.name_en || '').toLowerCase();
        const stat =
            statsMap.value.get(slug) ||
            statsMap.value.get(props.name_en?.toLowerCase());
        const count = stat ? stat.count : 0;
        const rate = stat ? stat.rate_per_100k : 0;
        const pathD = pathGenerator(feature) || '';

        return {
            id: props.id || slug,
            slug: props.slug || slug,
            name_en: props.name_en,
            name_bn: props.name_bn,
            division: props.division,
            population: props.population || 0,
            count,
            rate,
            d: pathD,
        };
    });
});

function getColor(count: number): string {
    if (count === 0) return isDark.value ? '#1e293b' : '#e2e8f0';
    if (count <= 2) return '#881337'; // rose-950
    if (count <= 5) return '#9f1239'; // rose-900
    if (count <= 10) return '#be123c'; // rose-800
    if (count <= 20) return '#e11d48'; // rose-600
    return '#f43f5e'; // rose-500
}

function handleMouseEnter(district: any, event: MouseEvent) {
    hoveredDistrict.value = district;
    updateTooltip(event);
}

function handleMouseMove(event: MouseEvent) {
    updateTooltip(event);
}

function updateTooltip(event: MouseEvent) {
    const container = (event.currentTarget as HTMLElement).closest(
        '.map-container',
    );
    if (!container) return;
    const rect = container.getBoundingClientRect();
    tooltipPosition.value = {
        x: event.clientX - rect.left + 15,
        y: event.clientY - rect.top + 15,
    };
}

function handleMouseLeave() {
    hoveredDistrict.value = null;
}

function handleDistrictClick(district: any) {
    if (district.slug) {
        router.visit(`/districts/${district.slug}`);
    }
}

function switchMode(newMode: 'incident' | 'commenter') {
    currentMode.value = newMode;
    emit('modeChange', newMode);
}
</script>

<template>
    <div
        class="relative flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <!-- Header & Mode Switcher -->
        <div
            class="flex w-full flex-col items-center justify-between gap-4 border-b border-slate-200 pb-4 dark:border-slate-800 sm:flex-row"
        >
            <div>
                <h3
                    class="flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white"
                >
                    <span>{{
                        isBn
                            ? '৬৪ জেলার ভৌগোলিক মানচিত্র'
                            : '64 District Geographic Map'
                    }}</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{
                        isBn
                            ? 'জেলায় ক্লিক করে বিস্তারিত রেকর্ড ও ঘটনা দেখুন'
                            : 'Click on a district to explore records and incidents'
                    }}
                </p>
            </div>

            <!-- Mode Switch Tabs -->
            <div
                class="flex items-center rounded-lg border border-slate-200 bg-slate-100 p-1 text-xs dark:border-slate-800 dark:bg-slate-950"
            >
                <button
                    type="button"
                    @click="switchMode('incident')"
                    :class="[
                        'rounded-md px-3 py-1.5 font-medium transition-colors',
                        currentMode === 'incident'
                            ? 'bg-rose-600 text-white shadow-sm'
                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                    ]"
                >
                    {{ isBn ? 'ঘটনার অবস্থান' : 'Incident Location' }}
                </button>
                <button
                    type="button"
                    @click="switchMode('commenter')"
                    :class="[
                        'rounded-md px-3 py-1.5 font-medium transition-colors',
                        currentMode === 'commenter'
                            ? 'bg-rose-600 text-white shadow-sm'
                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                    ]"
                >
                    {{ isBn ? 'মন্তব্যকারীর অবস্থান' : 'Commenter Location' }}
                </button>
            </div>
        </div>

        <!-- Disclaimer Banner for Mode -->
        <div
            class="mt-3 w-full rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-[11px] text-slate-600 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-300"
        >
            <span v-if="currentMode === 'incident'">
                <strong>{{
                    isBn ? 'ঘটনার অবস্থান:' : 'Incident Location:'
                }}</strong>
                {{
                    isBn
                        ? 'যে জেলায় মূল সহিংসতা বা যৌন নির্যাতনের ঘটনাটি সংঘটিত হয়েছে।'
                        : 'The geographic location where the primary harassment or violence incident occurred.'
                }}
            </span>
            <span v-else>
                <strong class="text-amber-600 dark:text-amber-400">{{
                    isBn
                        ? 'মন্তব্যকারীর অবস্থান (দাবি করা):'
                        : 'Commenter Location (Self-Declared):'
                }}</strong>
                {{
                    isBn
                        ? 'মন্তব্যকারীর ফেসবুক প্রোফাইলে উল্লেখিত জেলা। এটি ব্যবহারকারীর স্ব-ঘোষিত তথ্য, আমাদের দ্বারা স্বাধীনভাবে প্রমাণিত নয়।'
                        : 'Location self-declared by the commenter on their Facebook profile; not independently verified.'
                }}
            </span>
        </div>

        <!-- Map Container -->
        <div
            class="map-container relative flex w-full items-center justify-center overflow-hidden py-4"
        >
            <svg
                :viewBox="`0 0 ${width} ${height}`"
                class="h-auto w-full max-w-[500px] drop-shadow-lg"
                @mousemove="handleMouseMove"
            >
                <g>
                    <path
                        v-for="district in districtPaths"
                        :key="district.slug"
                        :d="district.d"
                        :fill="getColor(district.count)"
                        :stroke="isDark ? '#0f172a' : '#f8fafc'"
                        stroke-width="0.8"
                        class="cursor-pointer transition-all duration-200 hover:stroke-rose-400 hover:stroke-[1.5] hover:brightness-125"
                        @mouseenter="handleMouseEnter(district, $event)"
                        @mouseleave="handleMouseLeave"
                        @click="handleDistrictClick(district)"
                    />
                </g>
            </svg>

            <!-- Floating Tooltip -->
            <div
                v-if="hoveredDistrict"
                class="pointer-events-none absolute z-30 w-52 space-y-1 rounded-xl border border-slate-200 bg-white/95 p-3 text-xs shadow-xl backdrop-blur-sm dark:border-slate-700 dark:bg-slate-950/95"
                :style="{
                    left: `${tooltipPosition.x}px`,
                    top: `${tooltipPosition.y}px`,
                }"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 pb-1 font-bold text-slate-900 dark:border-slate-800 dark:text-white"
                >
                    <span>{{
                        isBn ? hoveredDistrict.name_bn : hoveredDistrict.name_en
                    }}</span>
                    <span
                        class="text-[10px] font-normal text-slate-500 dark:text-slate-400"
                        >{{ hoveredDistrict.division }}</span
                    >
                </div>
                <div
                    class="flex items-center justify-between text-slate-600 dark:text-slate-300"
                >
                    <span class="text-slate-500 dark:text-slate-400">{{
                        isBn ? 'যাচাইকৃত রেকর্ড:' : 'Verified Records:'
                    }}</span>
                    <strong
                        class="font-bold text-rose-600 dark:text-rose-400"
                        >{{
                            formatNumberByLocale(hoveredDistrict.count, locale)
                        }}</strong
                    >
                </div>
                <div
                    class="flex items-center justify-between text-slate-600 dark:text-slate-300"
                >
                    <span class="text-slate-500 dark:text-slate-400">{{
                        isBn ? 'প্রতি লাখে হার:' : 'Rate / 100k:'
                    }}</span>
                    <span
                        class="font-semibold text-slate-800 dark:text-slate-200"
                        >{{
                            formatNumberByLocale(hoveredDistrict.rate, locale)
                        }}</span
                    >
                </div>
                <div
                    class="flex items-center justify-between border-t border-slate-200 pt-1 text-[10px] text-slate-500 dark:border-slate-800/80 dark:text-slate-400"
                >
                    <span>{{
                        isBn ? 'জনসংখ্যা (আদমশুমারি ২২):' : 'Pop (Census 2022):'
                    }}</span>
                    <span>{{
                        formatNumberByLocale(hoveredDistrict.population, locale)
                    }}</span>
                </div>
                <div
                    class="pt-1 text-center text-[9px] font-medium text-rose-600 dark:text-rose-300/80"
                >
                    {{
                        isBn
                            ? 'বিস্তারিত দেখতে ক্লিক করুন'
                            : 'Click to view details'
                    }}
                </div>
            </div>
        </div>

        <!-- Legend & Attribution -->
        <div
            class="flex w-full flex-col items-center justify-between gap-4 border-t border-slate-200 pt-4 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400 sm:flex-row"
        >
            <!-- Legend -->
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-slate-600 dark:text-slate-400">{{
                    isBn ? 'রেকর্ড সংখ্যা:' : 'Record count:'
                }}</span>
                <div class="flex items-center gap-1">
                    <span
                        class="h-3 w-4 rounded-sm bg-slate-200 dark:bg-[#1e293b]"
                        title="0"
                    ></span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400"
                        >০</span
                    >
                </div>
                <div class="flex items-center gap-1">
                    <span
                        class="h-3 w-4 rounded-sm bg-[#881337]"
                        title="1-2"
                    ></span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400"
                        >১-২</span
                    >
                </div>
                <div class="flex items-center gap-1">
                    <span
                        class="h-3 w-4 rounded-sm bg-[#be123c]"
                        title="3-10"
                    ></span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400"
                        >৩-১০</span
                    >
                </div>
                <div class="flex items-center gap-1">
                    <span
                        class="h-3 w-4 rounded-sm bg-[#e11d48]"
                        title="11-20"
                    ></span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400"
                        >১১-২০</span
                    >
                </div>
                <div class="flex items-center gap-1">
                    <span
                        class="h-3 w-4 rounded-sm bg-[#f43f5e]"
                        title="21+"
                    ></span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400"
                        >২১+</span
                    >
                </div>
            </div>

            <div class="text-[10px] text-slate-500 dark:text-slate-400">
                মানচিত্র তথ্যসূত্র: BBS / UN OCHA (CC BY-IGO 3.0)
            </div>
        </div>
    </div>
</template>
