<script setup lang="ts">
import DistrictMap, { type DistrictStat } from '@/Components/DistrictMap.vue';
import ReportCard, { type ReportItem } from '@/Components/ReportCard.vue';
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatNumberByLocale } from '@/utils/bangla';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface NationalStats {
    received: number;
    verified: number;
    public: number;
    profiles: number;
    districts: number;
    received_bn: string;
    verified_bn: string;
    public_bn: string;
    profiles_bn: string;
    districts_bn: string;
}

const props = defineProps<{
    nationalStats: NationalStats;
    districtStats: DistrictStat[];
    featuredReports: ReportItem[];
}>();

const { isBn, locale } = useLanguage();
const searchQuery = ref('');
const activeDistrictStats = ref<DistrictStat[]>(props.districtStats);

function handleSearch() {
    if (searchQuery.value.trim()) {
        router.visit(
            `/search?q=${encodeURIComponent(searchQuery.value.trim())}`,
        );
    }
}

async function handleModeChange(mode: 'incident' | 'commenter') {
    try {
        const response = await fetch(`/api/v1/stats/districts?mode=${mode}`);
        const data = await response.json();
        if (data && data.data) {
            activeDistrictStats.value = data.data;
        }
    } catch {
        // Fallback to initial
    }
}
</script>

<template>
    <PublicLayout :title="isBn ? 'হোম' : 'Home'">
        <div class="space-y-16 py-8 sm:py-12">
            <!-- Hero Section -->
            <section
                class="mx-auto max-w-5xl space-y-6 px-4 text-center sm:px-6 lg:px-8"
            >
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-rose-900/60 bg-rose-950/40 px-3 py-1 text-xs font-medium text-rose-300"
                >
                    <span
                        class="h-2 w-2 animate-pulse rounded-full bg-rose-500"
                    ></span>
                    <span>{{
                        isBn
                            ? '৮-দফা কঠোর যাচাইকরণ নীতি অনুসৃত'
                            : 'Strict 8-Point Verification Standard'
                    }}</span>
                </div>

                <h1
                    class="text-3xl font-black leading-tight tracking-tight text-white sm:text-5xl"
                >
                    {{
                        isBn
                            ? 'সহিংসতার সমর্থনে মন্তব্যের স্বাধীন প্রমাণ ও ডিজিটাল সংরক্ষণাগার'
                            : 'Independent Public Archive of Comments Justifying Violence'
                    }}
                </h1>

                <p
                    class="mx-auto max-w-3xl text-base font-normal leading-relaxed text-slate-300 sm:text-lg"
                >
                    {{
                        isBn
                            ? 'সামাজিক মাধ্যমে যৌন নিপীড়ন ও সহিংসতার পক্ষে অবস্থান নেওয়া, ভিকটিম-ব্লেমিং এবং অপরাধকে লঘুকরণ করার উদ্দেশ্যে প্রকাশিত সর্বজনীন মন্তব্যের জবাবদিহিমূলক নথিপত্র।'
                            : 'A factual repository documenting public Facebook comments that excuse sexual abuse, blame victims, or incite hate in Bangladesh.'
                    }}
                </p>

                <!-- Search Input Bar -->
                <div class="mx-auto max-w-2xl pt-2">
                    <form
                        @submit.prevent="handleSearch"
                        class="relative flex items-center"
                    >
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="
                                isBn
                                    ? 'ফেসবুক প্রোফাইল লিঙ্ক অথবা নাম দিয়ে অনুসন্ধান করুন...'
                                    : 'Search by Facebook URL or name...'
                            "
                            class="w-full rounded-xl border border-slate-700 bg-slate-900 py-3.5 pl-12 pr-28 text-sm text-white placeholder-slate-400 shadow-inner focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500"
                        />
                        <button
                            type="submit"
                            class="absolute right-2 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-500"
                        >
                            {{ isBn ? 'খুঁজুন' : 'Search' }}
                        </button>
                    </form>
                    <div class="mt-2 px-2 text-left text-[11px] text-slate-400">
                        💡
                        {{
                            isBn
                                ? 'উদাহরণ: facebook.com/profile.php?id=... অথবা সুনির্দিষ্ট ব্যবহারকারীর নাম'
                                : 'Example: full Facebook profile link or name'
                        }}
                    </div>
                </div>
            </section>

            <!-- Headline Counters Grid -->
            <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    <div
                        class="space-y-1 rounded-xl border border-slate-800 bg-slate-900/90 p-5 text-center"
                    >
                        <div
                            class="text-2xl font-extrabold tracking-tight text-white sm:text-4xl"
                        >
                            {{
                                isBn
                                    ? nationalStats.received_bn
                                    : formatNumberByLocale(
                                          nationalStats.received,
                                          locale,
                                      )
                            }}
                        </div>
                        <div
                            class="text-xs font-semibold text-slate-400 sm:text-sm"
                        >
                            {{ isBn ? 'প্রাপ্ত রিপোর্ট' : 'Reports Received' }}
                        </div>
                        <div class="text-[10px] text-slate-400">
                            {{
                                isBn
                                    ? 'নাগরিকদের দ্বারা জমাকৃত'
                                    : 'Submitted by citizens'
                            }}
                        </div>
                    </div>

                    <div
                        class="space-y-1 rounded-xl border border-rose-900/40 bg-slate-900/90 p-5 text-center"
                    >
                        <div
                            class="text-2xl font-extrabold tracking-tight text-rose-400 sm:text-4xl"
                        >
                            {{
                                isBn
                                    ? nationalStats.verified_bn
                                    : formatNumberByLocale(
                                          nationalStats.verified,
                                          locale,
                                      )
                            }}
                        </div>
                        <div
                            class="text-xs font-semibold text-rose-200 sm:text-sm"
                        >
                            {{ isBn ? 'যাচাইকৃত রেকর্ড' : 'Verified Records' }}
                        </div>
                        <div class="text-[10px] text-rose-300/70">
                            {{
                                isBn
                                    ? '৮-দফা নিয়মে প্রমাণিত'
                                    : 'Passed 8-point check'
                            }}
                        </div>
                    </div>

                    <div
                        class="space-y-1 rounded-xl border border-slate-800 bg-slate-900/90 p-5 text-center"
                    >
                        <div
                            class="text-2xl font-extrabold tracking-tight text-white sm:text-4xl"
                        >
                            {{
                                isBn
                                    ? nationalStats.profiles_bn
                                    : formatNumberByLocale(
                                          nationalStats.profiles,
                                          locale,
                                      )
                            }}
                        </div>
                        <div
                            class="text-xs font-semibold text-slate-400 sm:text-sm"
                        >
                            {{
                                isBn
                                    ? 'নথিবদ্ধ প্রোফাইল'
                                    : 'Profiles Documented'
                            }}
                        </div>
                        <div class="text-[10px] text-slate-400">
                            {{
                                isBn
                                    ? 'অনন্য ফেসবুক একাউন্ট'
                                    : 'Unique Facebook accounts'
                            }}
                        </div>
                    </div>

                    <div
                        class="space-y-1 rounded-xl border border-slate-800 bg-slate-900/90 p-5 text-center"
                    >
                        <div
                            class="text-2xl font-extrabold tracking-tight text-white sm:text-4xl"
                        >
                            {{
                                isBn
                                    ? nationalStats.districts_bn
                                    : formatNumberByLocale(
                                          nationalStats.districts,
                                          locale,
                                      )
                            }}
                            <span class="text-sm font-normal text-slate-400"
                                >/ {{ isBn ? '৬৪' : '64' }}</span
                            >
                        </div>
                        <div
                            class="text-xs font-semibold text-slate-400 sm:text-sm"
                        >
                            {{
                                isBn
                                    ? 'জেলায় ঘটনার বিস্তার'
                                    : 'Districts Covered'
                            }}
                        </div>
                        <div class="text-[10px] text-slate-400">
                            {{
                                isBn
                                    ? '৬৪ জেলার ঘটনা মনিটরিং'
                                    : 'Monitoring all 64 districts'
                            }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Interactive Map Section -->
            <section class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
                <div class="space-y-2 text-center">
                    <h2
                        class="text-2xl font-bold tracking-tight text-white sm:text-3xl"
                    >
                        {{
                            isBn
                                ? 'জেলাওয়ারি সহিংসতা ও মন্তব্যের ভৌগোলিক বিস্তার'
                                : 'Geographic Distribution Across Districts'
                        }}
                    </h2>
                    <p
                        class="mx-auto max-w-2xl text-xs text-slate-400 sm:text-sm"
                    >
                        {{
                            isBn
                                ? '২০২২ সালের বাংলাদেশ জনশুমারি অনুযায়ী প্রতি ১ লাখ জনসংখ্যায় সহিংসতা ও সহিংসতার সমর্থক মন্তব্যের অনুপাত।'
                                : 'Incident and commenter rates normalized per 100k population using BBS Census 2022.'
                        }}
                    </p>
                </div>

                <DistrictMap
                    :stats="activeDistrictStats"
                    initial-mode="incident"
                    @mode-change="handleModeChange"
                />
            </section>

            <!-- Featured Verified Reports Feed -->
            <section class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-800 pb-4 sm:flex-row sm:items-end"
                >
                    <div>
                        <h2
                            class="text-xl font-bold tracking-tight text-white sm:text-2xl"
                        >
                            {{
                                isBn
                                    ? 'সর্বশেষ যাচাইকৃত রেকর্ডসমূহ'
                                    : 'Recent Verified Records'
                            }}
                        </h2>
                        <p class="text-xs text-slate-400">
                            {{
                                isBn
                                    ? 'দুই স্তরবিশিষ্ট নিরপেক্ষ পর্যালোচনার পর উন্মুক্ত করা সর্বজনীন মন্তব্য।'
                                    : 'Public comments published following strict two-person editorial verification.'
                            }}
                        </p>
                    </div>

                    <Link
                        href="/search"
                        class="flex items-center gap-1 text-xs font-semibold text-rose-400 transition-colors hover:text-rose-300"
                    >
                        <span>{{
                            isBn
                                ? 'সকল রেকর্ড ও প্রোফাইল অনুসন্ধান করুন →'
                                : 'Search all records & profiles →'
                        }}</span>
                    </Link>
                </div>

                <div
                    v-if="featuredReports.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <ReportCard
                        v-for="report in featuredReports"
                        :key="report.uuid"
                        :report="report"
                    />
                </div>
                <div
                    v-else
                    class="rounded-xl border border-slate-800 bg-slate-900/50 py-12 text-center text-sm text-slate-400"
                >
                    {{
                        isBn
                            ? 'বর্তমানে কোনো রেকর্ড প্রদর্শনের জন্য নেই।'
                            : 'No records currently featured.'
                    }}
                </div>
            </section>

            <!-- Methodology & Safety Callout Banner -->
            <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div
                    class="space-y-4 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 p-6 shadow-xl sm:p-8"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-slate-200"
                        >
                            ⚖️
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">
                                {{
                                    isBn
                                        ? 'স্বচ্ছতা, সততা এবং আইনি সুরক্ষা'
                                        : 'Transparency, Integrity & Legal Safeguards'
                                }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                {{
                                    isBn
                                        ? 'আমরা ব্যক্তিগত মতামত বা অপবাদের স্থান নই; কেবল প্রমাণিত সর্বজনীন বক্তব্য সংরক্ষণ করি।'
                                        : 'We document only proven public statements with tamper-evident digital hashes.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-4 pt-2 text-xs text-slate-300 md:grid-cols-3"
                    >
                        <div
                            class="space-y-1 rounded-lg border border-slate-800 bg-slate-950/60 p-3"
                        >
                            <strong class="block text-white">{{
                                isBn
                                    ? '১. দুই-ব্যক্তি নীতি (Two-Person Rule)'
                                    : '1. Two-Person Rule'
                            }}</strong>
                            <p class="text-[11px] text-slate-400">
                                {{
                                    isBn
                                        ? 'যে মডারেটর প্রমাণ যাচাই করবেন, তিনি কখনোই একা প্রকাশ করতে পারবেন না।'
                                        : 'The moderator who verifies evidence cannot publish it without independent sign-off.'
                                }}
                            </p>
                        </div>
                        <div
                            class="space-y-1 rounded-lg border border-slate-800 bg-slate-950/60 p-3"
                        >
                            <strong class="block text-white">{{
                                isBn
                                    ? '২. ভিকটিমের শতভাগ গোপনীয়তা'
                                    : '2. 100% Victim Privacy'
                            }}</strong>
                            <p class="text-[11px] text-slate-400">
                                {{
                                    isBn
                                        ? 'ভিকটিম বা সংশ্লিষ্ট অপ্রাপ্তবয়স্কদের কোনো নাম, ছবি বা পরিচয় সংরক্ষণ বা প্রকাশ করা হয় না।'
                                        : 'Victims and minors are never published; screenshots are server-side redacted.'
                                }}
                            </p>
                        </div>
                        <div
                            class="space-y-1 rounded-lg border border-slate-800 bg-slate-950/60 p-3"
                        >
                            <strong class="block text-white">{{
                                isBn
                                    ? '৩. স্বাধীন আপিল অধিকার'
                                    : '3. Right of Reply'
                            }}</strong>
                            <p class="text-[11px] text-slate-400">
                                {{
                                    isBn
                                        ? 'হ্যাক হওয়া আইডি বা ভুল পরিচয়ের ক্ষেত্রে যে কেউ প্রমাণের ভিত্তিতে আপিল করতে পারেন।'
                                        : 'Anyone claiming account compromise or mistaken identity can submit an appeal.'
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
