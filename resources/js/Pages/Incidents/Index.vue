<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { formatDateByLocale, formatNumberByLocale } from '@/utils/bangla';
import { Head, Link } from '@inertiajs/vue3';

export interface IncidentItem {
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
    reports_count: number;
}

defineProps<{
    incidents: {
        data: IncidentItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

const { isBn, locale } = useLanguage();
</script>

<template>
    <PublicLayout :title="isBn ? 'ঘটনাসমূহ' : 'Incidents'">
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'বাংলাদেশে সংঘটিত যৌন নিপীড়ন ও সহিংসতার ঘটনাসমূহ এবং সংশ্লিষ্ট মন্তব্যের রেকর্ড।'
                        : 'Documented incidents of violence in Bangladesh and associated comments on record.'
                "
            />
        </Head>

        <div class="mx-auto max-w-6xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <div class="border-b border-slate-800 pb-4">
                <h1
                    class="text-2xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{ isBn ? 'নথিবদ্ধ ঘটনাসমূহ' : 'Documented Incidents' }}
                </h1>
                <p class="mt-1 text-xs text-slate-400 sm:text-sm">
                    {{
                        isBn
                            ? 'যেসব ঘটনাকে কেন্দ্র করে সামাজিক যোগাযোগ মাধ্যমে ভিকটিম-ব্লেমিং বা সহিংসতার পক্ষে বক্তব্য নথিবদ্ধ হয়েছে।'
                            : 'Incidents where victim-blaming or justification of violence occurred on public social media.'
                    }}
                </p>
            </div>

            <!-- Incidents Grid -->
            <div
                v-if="incidents.data.length > 0"
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="incident in incidents.data"
                    :key="incident.id"
                    class="flex flex-col justify-between space-y-4 rounded-xl border border-slate-800 bg-slate-900/80 p-5 transition-colors hover:border-slate-700"
                >
                    <div class="space-y-2">
                        <div
                            class="flex items-center justify-between text-xs text-slate-400"
                        >
                            <span
                                v-if="incident.district"
                                class="font-medium text-rose-300"
                            >
                                📍
                                {{
                                    isBn
                                        ? incident.district.name_bn
                                        : incident.district.name_en
                                }}
                            </span>
                            <span v-if="incident.occurred_on">
                                {{
                                    formatDateByLocale(
                                        incident.occurred_on,
                                        locale,
                                    )
                                }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold leading-snug text-white">
                            <Link
                                :href="`/incidents/${incident.slug}`"
                                class="transition-colors hover:text-rose-400"
                            >
                                {{
                                    isBn ? incident.title_bn : incident.title_en
                                }}
                            </Link>
                        </h3>

                        <p
                            v-if="incident.summary"
                            class="line-clamp-3 text-xs leading-relaxed text-slate-400"
                        >
                            {{ incident.summary }}
                        </p>
                    </div>

                    <div
                        class="flex items-center justify-between border-t border-slate-800/80 pt-3 text-xs"
                    >
                        <span
                            class="flex items-center gap-1.5 font-semibold text-slate-300"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-rose-500"
                            ></span>
                            {{
                                formatNumberByLocale(
                                    incident.reports_count,
                                    locale,
                                )
                            }}
                            {{
                                isBn
                                    ? 'টি যাচাইকৃত মন্তব্য'
                                    : 'verified comments'
                            }}
                        </span>

                        <Link
                            :href="`/incidents/${incident.slug}`"
                            class="font-medium text-rose-400 hover:text-rose-300"
                        >
                            {{ isBn ? 'বিস্তারিত →' : 'View Details →' }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="rounded-xl border border-slate-800 bg-slate-900/40 py-12 text-center text-sm text-slate-400"
            >
                {{
                    isBn
                        ? 'বর্তমানে কোনো ঘটনা নথিবদ্ধ নেই।'
                        : 'No incidents currently on record.'
                }}
            </div>

            <!-- Pagination -->
            <div
                v-if="incidents.links.length > 3"
                class="flex justify-center gap-1 pt-4"
            >
                <template v-for="(link, i) in incidents.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'rounded px-3 py-1.5 text-xs font-medium transition-colors',
                            link.active
                                ? 'bg-rose-600 text-white'
                                : 'border border-slate-800 bg-slate-900 text-slate-300 hover:bg-slate-800',
                        ]"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span
                        v-else
                        class="rounded border border-slate-900 px-3 py-1.5 text-xs font-medium text-slate-600"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </PublicLayout>
</template>
