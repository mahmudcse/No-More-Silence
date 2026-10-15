<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head, router } from '@inertiajs/vue3';

interface MonthlyReport {
    period: string;
    year: number;
    month: number;
    submissions: {
        total: number;
        anonymous: number;
        authenticated: number;
        web_share_target: number;
    };
    outcomes: {
        verified: number;
        rejected: number;
        duplicate: number;
        spam: number;
        retracted: number;
    };
    turnaround: {
        avg_verification_hours: number;
        total_reviews_logged: number;
    };
    appeals: {
        filed: number;
        accepted: number;
        rejected: number;
        pending: number;
        right_of_reply_published: number;
    };
}

defineProps<{
    currentReport: MonthlyReport;
    history: MonthlyReport[];
    selectedYear: number;
    selectedMonth: number;
}>();

const { isBn } = useLanguage();

function selectMonth(year: number, month: number) {
    router.get(
        '/transparency',
        { year, month },
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <PublicLayout
        :title="
            isBn
                ? 'স্বচ্ছতা ও জবাবদিহিতা প্রতিবেদন'
                : 'Transparency & Accountability Report'
        "
    >
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'নো মোর সাইলেন্সের মাসিক প্রতিবেদন: জমা পড়া রিপোর্ট, যাচাইকরণ হার, ভুল সংশোধনের আপিল ও গড় নিষ্পত্তির সময়।'
                        : 'Monthly operational transparency report: submissions received, verification rates, appeals handled, and review times.'
                "
            />
        </Head>

        <div class="mx-auto max-w-6xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="border-b border-slate-800 pb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span
                            class="rounded border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-400"
                        >
                            {{
                                isBn
                                    ? 'স্বচ্ছতা নীতি'
                                    : 'Accountability Standard'
                            }}
                        </span>
                        <h1
                            class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                        >
                            {{
                                isBn
                                    ? 'মাসিক স্বচ্ছতা ও পরিচালনা প্রতিবেদন'
                                    : 'Monthly Transparency Reports'
                            }}
                        </h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-400">
                            {{
                                isBn
                                    ? 'জনস্বার্থে পরিচালিত এই প্ল্যাটফর্মে আমরা প্রতিটি রিপোর্টের যাচাইকরণ প্রক্রিয়া, আপিল নিষ্পত্তি ও সম্পাদনার জবাবদিহিতা মাসিকভিত্তিতে উন্মুক্ত করি।'
                                    : 'Public trust requires operational transparency. Every month, we publish our editorial throughput, verification accuracy, reject reasons, and appeals adjudicated.'
                            }}
                        </p>
                    </div>

                    <!-- Month Selector Dropdown / Pills -->
                    <div
                        class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0"
                    >
                        <button
                            v-for="rep in history"
                            :key="`${rep.year}-${rep.month}`"
                            type="button"
                            @click="selectMonth(rep.year, rep.month)"
                            class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="
                                rep.year === selectedYear &&
                                rep.month === selectedMonth
                                    ? 'border-rose-500 bg-rose-500/20 font-bold text-rose-300'
                                    : 'border-slate-800 bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white'
                            "
                        >
                            {{ rep.period }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Current Report Banner -->
            <div
                class="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-900/90 p-4"
            >
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📊</span>
                    <div>
                        <div
                            class="text-xs font-medium uppercase tracking-wider text-slate-400"
                        >
                            {{ isBn ? 'নির্বাচিত সময়কাল' : 'Selected Period' }}
                        </div>
                        <div class="text-lg font-bold text-white">
                            {{ currentReport.period }}
                        </div>
                    </div>
                </div>

                <div class="text-right text-xs text-slate-400">
                    <div>
                        {{ isBn ? 'যাচাইকরণ মান:' : 'Verification Standard:' }}
                        <strong class="font-semibold text-emerald-400">{{
                            isBn ? '৮-দফা মানদণ্ড' : '8-Point System'
                        }}</strong>
                    </div>
                    <div>
                        {{ isBn ? 'সম্পাদকীয় নিয়ম:' : 'Editorial Rule:' }}
                        <strong class="font-semibold text-slate-300">{{
                            isBn
                                ? 'দ্বৈত যাচাইকরণ (Two-Person Rule)'
                                : 'Two-Person Rule'
                        }}</strong>
                    </div>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Card 1: Submissions -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900/70 p-5"
                >
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        {{ isBn ? 'জমা পড়া রিপোর্ট' : 'Submissions' }}
                    </div>
                    <div class="text-3xl font-black text-white">
                        {{ currentReport.submissions.total }}
                    </div>
                    <div
                        class="space-y-1.5 border-t border-slate-800 pt-2 text-xs text-slate-400"
                    >
                        <div class="flex justify-between">
                            <span>{{
                                isBn ? 'বেনামী সাবমিশন:' : 'Anonymous:'
                            }}</span>
                            <span class="font-bold text-slate-200">{{
                                currentReport.submissions.anonymous
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn ? 'নিবন্ধিত রিপোর্টার:' : 'Authenticated:'
                            }}</span>
                            <span class="font-bold text-slate-200">{{
                                currentReport.submissions.authenticated
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn
                                    ? 'মোবাইল শেয়ার টার্গেট:'
                                    : 'Web Share Target:'
                            }}</span>
                            <span class="font-bold text-slate-200">{{
                                currentReport.submissions.web_share_target
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Verification Outcomes -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900/70 p-5"
                >
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        {{ isBn ? 'যাচাই ও অনুমোদন' : 'Verification' }}
                    </div>
                    <div class="text-3xl font-black text-emerald-400">
                        {{ currentReport.outcomes.verified }}
                    </div>
                    <div
                        class="space-y-1.5 border-t border-slate-800 pt-2 text-xs text-slate-400"
                    >
                        <div class="flex justify-between">
                            <span>{{
                                isBn
                                    ? 'বাতিলকৃত (অপর্যাপ্ত প্রমাণ):'
                                    : 'Rejected:'
                            }}</span>
                            <span class="font-bold text-rose-400">{{
                                currentReport.outcomes.rejected
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn ? 'ডুপ্লিকেট একীভূত:' : 'Duplicates:'
                            }}</span>
                            <span class="font-bold text-slate-200">{{
                                currentReport.outcomes.duplicate
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn
                                    ? 'স্প্যাম / অপ্রাসঙ্গিক:'
                                    : 'Spam Filtered:'
                            }}</span>
                            <span class="font-bold text-slate-200">{{
                                currentReport.outcomes.spam
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Editorial Speed -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900/70 p-5"
                >
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        {{ isBn ? 'গড় নিষ্পত্তির সময়' : 'Avg Turnaround' }}
                    </div>
                    <div class="text-3xl font-black text-sky-400">
                        {{ currentReport.turnaround.avg_verification_hours }}
                        <span class="text-sm font-normal text-slate-400">{{
                            isBn ? 'ঘণ্টা' : 'hrs'
                        }}</span>
                    </div>
                    <div
                        class="space-y-1.5 border-t border-slate-800 pt-2 text-xs text-slate-400"
                    >
                        <div class="flex justify-between">
                            <span>{{
                                isBn ? 'সম্পন্ন রিভিউ একশন:' : 'Review Actions:'
                            }}</span>
                            <span class="font-bold text-slate-200">{{
                                currentReport.turnaround.total_reviews_logged
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn
                                    ? 'পর্যালোচনা চেকলিস্ট:'
                                    : 'Checklist Audit:'
                            }}</span>
                            <span class="font-bold text-emerald-400"
                                >100% Logged</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn ? 'প্রত্যাহারকৃত রেকর্ড:' : 'Retracted:'
                            }}</span>
                            <span class="font-bold text-amber-400">{{
                                currentReport.outcomes.retracted
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Appeals & Right of Reply -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900/70 p-5"
                >
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        {{ isBn ? 'আপিল ও আত্মপক্ষ' : 'Appeals & Reply' }}
                    </div>
                    <div class="text-3xl font-black text-amber-400">
                        {{ currentReport.appeals.filed }}
                    </div>
                    <div
                        class="space-y-1.5 border-t border-slate-800 pt-2 text-xs text-slate-400"
                    >
                        <div class="flex justify-between">
                            <span>{{
                                isBn ? 'গৃহীত আপিল:' : 'Accepted:'
                            }}</span>
                            <span class="font-bold text-emerald-400">{{
                                currentReport.appeals.accepted
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn ? 'খারিজকৃত আপিল:' : 'Rejected:'
                            }}</span>
                            <span class="font-bold text-slate-200">{{
                                currentReport.appeals.rejected
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{
                                isBn
                                    ? 'আত্মপক্ষ বক্তব্য প্রকাশ:'
                                    : 'Reply Published:'
                            }}</span>
                            <span class="font-bold text-sky-400">{{
                                currentReport.appeals.right_of_reply_published
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Overview Table -->
            <div
                class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900/60 p-6"
            >
                <h2 class="text-base font-bold text-white">
                    {{
                        isBn
                            ? 'বিগত ৬ মাসের সংক্ষিপ্ত পরিসংখ্যান'
                            : '6-Month Operational History'
                    }}
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead
                            class="border-b border-slate-800 bg-slate-950/50 font-semibold uppercase text-slate-400"
                        >
                            <tr>
                                <th class="px-3 py-2.5">
                                    {{ isBn ? 'মাস' : 'Month' }}
                                </th>
                                <th class="px-3 py-2.5 text-right">
                                    {{ isBn ? 'জমা পড়া' : 'Received' }}
                                </th>
                                <th class="px-3 py-2.5 text-right">
                                    {{ isBn ? 'যাচাইকৃত' : 'Verified' }}
                                </th>
                                <th class="px-3 py-2.5 text-right">
                                    {{ isBn ? 'বাতিল' : 'Rejected' }}
                                </th>
                                <th class="px-3 py-2.5 text-right">
                                    {{
                                        isBn ? 'আপিল গ্রহণ' : 'Appeals Accepted'
                                    }}
                                </th>
                                <th class="px-3 py-2.5 text-right">
                                    {{ isBn ? 'গড় গতি' : 'Avg Hours' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-mono">
                            <tr
                                v-for="rep in history"
                                :key="`${rep.year}-${rep.month}`"
                                class="transition-colors hover:bg-slate-800/40"
                                :class="
                                    rep.year === selectedYear &&
                                    rep.month === selectedMonth
                                        ? 'bg-slate-800/30'
                                        : ''
                                "
                            >
                                <td
                                    class="px-3 py-2.5 font-sans font-bold text-slate-200"
                                >
                                    {{ rep.period }}
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    {{ rep.submissions.total }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-right font-bold text-emerald-400"
                                >
                                    {{ rep.outcomes.verified }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-right text-rose-400"
                                >
                                    {{ rep.outcomes.rejected }}
                                </td>
                                <td class="px-3 py-2.5 text-right text-sky-400">
                                    {{ rep.appeals.accepted }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-right text-slate-400"
                                >
                                    {{ rep.turnaround.avg_verification_hours }}h
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
