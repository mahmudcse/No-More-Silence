<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import { formatDateByLocale } from '@/utils/bangla';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

export interface ReportItem {
    uuid: string;
    comment_text: string | null;
    comment_text_translation?: string | null;
    comment_posted_at?: string | null;
    category?: string | null;
    category_label_bn?: string | null;
    category_label_en?: string | null;
    verified_at?: string | null;
    published_at?: string | null;
    evidence_url?: string | null;
    profile?: {
        uuid: string;
        display_name: string | null;
        canonical_url: string;
    } | null;
    incident?: {
        id: number;
        title_en: string;
        title_bn: string;
        slug: string;
        district?: string | null;
    } | null;
}

defineProps<{
    report: ReportItem;
}>();

const { isBn, locale } = useLanguage();
const showEvidenceModal = ref(false);

const categoryColors: Record<string, string> = {
    victim_blaming:
        'bg-amber-500/10 text-amber-700 border-amber-500/20 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-400/20',
    justifying_violence:
        'bg-rose-500/10 text-rose-700 border-rose-500/20 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-400/20',
    trivialising:
        'bg-blue-500/10 text-blue-700 border-blue-500/20 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-400/20',
    incitement:
        'bg-red-500/10 text-red-700 border-red-500/20 dark:bg-red-500/15 dark:text-red-300 dark:border-red-400/20',
    harassment:
        'bg-purple-500/10 text-purple-700 border-purple-500/20 dark:bg-purple-500/15 dark:text-purple-300 dark:border-purple-400/20',
};

function getCategoryColor(category?: string | null): string {
    if (!category)
        return 'bg-black/[0.04] text-slate-700 border-black/[0.08] dark:bg-white/[0.08] dark:text-slate-300 dark:border-white/[0.1]';
    return (
        categoryColors[category] ??
        'bg-black/[0.04] text-slate-700 border-black/[0.08] dark:bg-white/[0.08] dark:text-slate-300 dark:border-white/[0.1]'
    );
}
</script>

<template>
    <div
        class="flex flex-col justify-between rounded-2xl p-5 apple-glass-card shadow-glass transition-all duration-300 hover:shadow-glass-lg hover:-translate-y-0.5"
    >
        <div class="space-y-3">
            <!-- Header: Category & Verification Date -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span
                    :class="[
                        'rounded-full border px-2.5 py-1 text-xs font-medium',
                        getCategoryColor(report.category),
                    ]"
                >
                    {{
                        isBn
                            ? (report.category_label_bn ??
                              report.category ??
                              'রেকর্ড')
                            : (report.category_label_en ??
                              report.category ??
                              'Record')
                    }}
                </span>

                <span
                    class="flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400"
                >
                    <svg
                        class="h-3.5 w-3.5 text-emerald-500 dark:text-emerald-400"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ isBn ? 'যাচাইকৃত:' : 'Verified:' }}
                    {{ formatDateByLocale(report.verified_at, locale) }}
                </span>
            </div>

            <!-- Profile Info (Display Name only, No Avatar) -->
            <div
                v-if="report.profile"
                class="flex items-center justify-between gap-2 border-t border-black/[0.04] pt-2 dark:border-white/[0.06]"
            >
                <Link
                    :href="`/profiles/${report.profile.uuid}`"
                    class="flex items-center gap-1.5 text-sm font-semibold text-slate-800 transition-colors hover:text-rose-600 dark:text-slate-200 dark:hover:text-rose-400"
                >
                    <span
                        class="h-2 w-2 rounded-full bg-slate-400 dark:bg-slate-500"
                    ></span>
                    <span>{{
                        report.profile.display_name ??
                        (isBn ? 'নামবিহীন ফেসবুক ব্যবহারকারী' : 'Facebook User')
                    }}</span>
                </Link>
                <a
                    :href="report.profile.canonical_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-1 text-[11px] text-slate-500 transition-colors hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                    title="View Facebook profile"
                >
                    <span>{{ isBn ? 'মূল লিংক' : 'Source' }}</span>
                    <svg
                        class="h-3 w-3 text-slate-400 dark:text-slate-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                        />
                    </svg>
                </a>
            </div>

            <!-- Verbatim Comment Quote -->
            <div
                class="relative rounded-xl border border-black/[0.05] bg-black/[0.02] p-3.5 font-sans text-sm leading-relaxed text-slate-800 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-200"
            >
                <span class="mr-1 select-none font-serif text-xl text-rose-500"
                    >“</span
                >
                <span>{{
                    report.comment_text ||
                    (isBn ? '[কোনো টেক্সট রেকর্ড নেই]' : '[No text record]')
                }}</span>
                <span class="ml-1 select-none font-serif text-xl text-rose-500"
                    >”</span
                >

                <div
                    v-if="report.comment_text_translation"
                    class="mt-2 border-t border-black/[0.04] pt-2 text-xs italic text-slate-500 dark:border-white/[0.06] dark:text-slate-400"
                >
                    {{ report.comment_text_translation }}
                </div>
            </div>

            <!-- Context: Linked Incident -->
            <div
                v-if="report.incident"
                class="flex items-start gap-1.5 text-xs text-slate-500 dark:text-slate-400"
            >
                <span
                    class="shrink-0 font-medium text-slate-600 dark:text-slate-400"
                    >{{ isBn ? 'ঘটনা:' : 'Incident:' }}</span
                >
                <span
                    class="truncate text-slate-700 dark:text-slate-300"
                >
                    {{
                        isBn
                            ? report.incident.title_bn
                            : report.incident.title_en
                    }}
                    <span
                        v-if="report.incident.district"
                        class="text-slate-400"
                    >
                        ({{ report.incident.district }})</span
                    >
                </span>
            </div>
        </div>

        <!-- Footer Actions -->
        <div
            class="mt-4 flex items-center justify-between border-t border-black/[0.05] pt-3 text-xs dark:border-white/[0.06]"
        >
            <button
                v-if="report.evidence_url"
                type="button"
                @click="showEvidenceModal = true"
                class="flex items-center gap-1.5 rounded-full border border-black/[0.06] bg-black/[0.03] px-3 py-1 font-medium text-slate-700 transition-all hover:bg-black/[0.06] active:scale-95 dark:border-white/[0.08] dark:bg-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.14]"
            >
                <svg
                    class="h-3.5 w-3.5 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
                <span>{{
                    isBn ? 'রেড্যাক্ট করা প্রমাণ দেখুন' : 'View Redacted Proof'
                }}</span>
            </button>
            <span v-else class="text-[11px] text-slate-400">
                {{
                    isBn
                        ? 'স্ক্রিনশট প্রক্রিয়াজাত হচ্ছে'
                        : 'Screenshot in review'
                }}
            </span>

            <div class="flex items-center gap-3">
                <Link
                    :href="`/appeals?report_uuid=${report.uuid}${report.profile ? '&profile_url=' + encodeURIComponent(report.profile.canonical_url) : ''}`"
                    class="text-rose-600 transition-colors hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                    :title="
                        isBn
                            ? 'এই রেকর্ডের ব্যাপারে আপত্তি জানান'
                            : 'Dispute or appeal this record'
                    "
                >
                    {{ isBn ? 'আপত্তি / আপিল' : 'Dispute' }}
                </Link>
                <Link
                    href="/methodology"
                    class="inline-flex items-center gap-1 text-slate-500 transition-colors hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300"
                >
                    <span>{{ isBn ? 'কার্যপদ্ধতি' : 'Methodology' }}</span>
                    <svg
                        class="h-3 w-3"
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

        <!-- Redacted Evidence Modal -->
        <div
            v-if="showEvidenceModal && report.evidence_url"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xl"
            @click.self="showEvidenceModal = false"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-2xl flex-col space-y-3 rounded-3xl apple-glass-card p-5 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-black/[0.06] pb-3 dark:border-white/[0.08]"
                >
                    <h4
                        class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white"
                    >
                        <span>{{
                            isBn
                                ? 'যাচাইকৃত ও রেড্যাক্ট করা প্রমাণ'
                                : 'Verified & Redacted Evidence'
                        }}</span>
                        <span
                            class="rounded-full border border-rose-500/20 bg-rose-500/10 px-2.5 py-0.5 text-[10px] font-medium text-rose-700 dark:border-rose-400/20 dark:bg-rose-500/15 dark:text-rose-300"
                        >
                            {{
                                isBn
                                    ? 'ভিকটিম তথ্য গোপন'
                                    : 'Victim info redacted'
                            }}
                        </span>
                    </h4>
                    <button
                        type="button"
                        @click="showEvidenceModal = false"
                        class="rounded-full p-1.5 text-slate-500 hover:bg-black/[0.05] hover:text-slate-800 dark:text-slate-400 dark:hover:bg-white/[0.1] dark:hover:text-white"
                        aria-label="Close"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div
                    class="flex flex-1 items-center justify-center overflow-auto rounded-2xl apple-glass-subtle p-3"
                >
                    <img
                        :src="report.evidence_url"
                        :alt="report.comment_text ?? 'Redacted Evidence'"
                        class="max-h-[70vh] rounded-xl object-contain"
                    />
                </div>

                <div
                    class="text-center text-[11px] text-slate-500 dark:text-slate-400"
                >
                    {{
                        isBn
                            ? 'আমাদের যাচাইকরণ নীতি অনুযায়ী ভিকটিম, অন্যান্য নির্দোষ ব্যক্তি এবং সংবেদনশীল তথ্য সার্ভারে স্থায়ীভাবে কালো বক্সে ঢেকে দেওয়া হয়েছে।'
                            : 'According to our verification standard, victim info and other innocent bystanders have been permanently black-box redacted.'
                    }}
                </div>
            </div>
        </div>
    </div>
</template>
