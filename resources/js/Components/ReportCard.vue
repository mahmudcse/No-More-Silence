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
    victim_blaming: 'bg-amber-950/70 text-amber-300 border-amber-800/60',
    justifying_violence: 'bg-rose-950/70 text-rose-300 border-rose-800/60',
    trivialising: 'bg-blue-950/70 text-blue-300 border-blue-800/60',
    incitement: 'bg-red-950/70 text-red-300 border-red-800/60',
    harassment: 'bg-purple-950/70 text-purple-300 border-purple-800/60',
};

function getCategoryColor(category?: string | null): string {
    if (!category) return 'bg-slate-800 text-slate-300 border-slate-700';
    return (
        categoryColors[category] ??
        'bg-slate-800 text-slate-300 border-slate-700'
    );
}
</script>

<template>
    <div
        class="flex flex-col justify-between rounded-xl border border-slate-800 bg-slate-900/70 p-5 shadow-sm transition-colors hover:border-slate-700"
    >
        <div class="space-y-3">
            <!-- Header: Category & Verification Date -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span
                    :class="[
                        'rounded-full border px-2.5 py-1 text-xs font-semibold',
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
                    class="flex items-center gap-1 text-[11px] text-slate-400"
                >
                    <svg
                        class="h-3.5 w-3.5 text-emerald-400"
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
                class="flex items-center justify-between gap-2 border-t border-slate-800/60 pt-1"
            >
                <Link
                    :href="`/profiles/${report.profile.uuid}`"
                    class="flex items-center gap-1.5 text-sm font-semibold text-slate-200 transition-colors hover:text-rose-400"
                >
                    <span class="h-2 w-2 rounded-full bg-slate-600"></span>
                    <span>{{
                        report.profile.display_name ??
                        (isBn ? 'নামবিহীন ফেসবুক ব্যবহারকারী' : 'Facebook User')
                    }}</span>
                </Link>
                <a
                    :href="report.profile.canonical_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-1 text-[11px] text-slate-400 underline underline-offset-2 hover:text-slate-300"
                    title="View Facebook profile"
                >
                    {{ isBn ? 'মূল লিংক ↗' : 'Source Link ↗' }}
                </a>
            </div>

            <!-- Verbatim Comment Quote -->
            <div
                class="relative rounded-lg border border-slate-800 bg-slate-950/60 p-3.5 font-sans text-sm leading-relaxed text-slate-200"
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
                    class="mt-2 border-t border-slate-800/60 pt-2 text-xs italic text-slate-400"
                >
                    {{ report.comment_text_translation }}
                </div>
            </div>

            <!-- Context: Linked Incident -->
            <div
                v-if="report.incident"
                class="flex items-start gap-1.5 text-xs text-slate-400"
            >
                <span class="shrink-0 font-medium text-slate-400">{{
                    isBn ? 'ঘটনা:' : 'Incident:'
                }}</span>
                <Link
                    :href="`/incidents/${report.incident.slug}`"
                    class="truncate text-slate-300 underline underline-offset-2 hover:text-white"
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
                </Link>
            </div>
        </div>

        <!-- Footer Actions -->
        <div
            class="mt-4 flex items-center justify-between border-t border-slate-800/80 pt-3 text-xs"
        >
            <button
                v-if="report.evidence_url"
                type="button"
                @click="showEvidenceModal = true"
                class="flex items-center gap-1.5 rounded bg-slate-800 px-2.5 py-1 font-medium text-slate-300 transition-colors hover:bg-slate-700 hover:text-white"
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

            <Link
                href="/methodology"
                class="text-slate-400 transition-colors hover:text-slate-300"
            >
                {{ isBn ? 'কার্যপদ্ধতি →' : 'Methodology →' }}
            </Link>
        </div>

        <!-- Redacted Evidence Modal -->
        <div
            v-if="showEvidenceModal && report.evidence_url"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
            @click.self="showEvidenceModal = false"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-2xl flex-col space-y-3 rounded-xl border border-slate-800 bg-slate-900 p-4"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-800 pb-2"
                >
                    <h4
                        class="flex items-center gap-2 text-sm font-semibold text-white"
                    >
                        <span>{{
                            isBn
                                ? 'যাচাইকৃত ও রেড্যাক্ট করা প্রমাণ'
                                : 'Verified & Redacted Evidence'
                        }}</span>
                        <span
                            class="rounded border border-rose-800 bg-rose-950 px-2 py-0.5 text-[10px] text-rose-300"
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
                        class="p-1 text-lg font-bold text-slate-400 hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div
                    class="flex flex-1 items-center justify-center overflow-auto rounded-lg bg-black/50 p-2"
                >
                    <img
                        :src="report.evidence_url"
                        :alt="report.comment_text ?? 'Redacted Evidence'"
                        class="max-h-[70vh] rounded object-contain"
                    />
                </div>

                <div class="text-center text-[11px] text-slate-400">
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
