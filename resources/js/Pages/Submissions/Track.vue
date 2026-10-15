<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    initialCode?: string;
}>();

const { isBn } = useLanguage();

const trackingCode = ref(props.initialCode || '');
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);

interface ReportTrackingData {
    tracking_code: string;
    uuid: string;
    status: string;
    status_label_bn: string;
    status_label_en: string;
    visibility: string;
    category?: string | null;
    category_label_bn?: string | null;
    submitted_at?: string | null;
    verified_at?: string | null;
    incident?: {
        title_bn: string;
        title_en: string;
    } | null;
}

const result = ref<ReportTrackingData | null>(null);

async function searchTracking(codeToSearch?: string) {
    const code = (codeToSearch ?? trackingCode.value).trim();
    if (!code) return;

    isLoading.value = true;
    errorMessage.value = null;
    result.value = null;

    try {
        const response = await axios.get(
            `/api/v1/submissions/track/${encodeURIComponent(code)}`,
        );
        if (response.data?.success && response.data?.data) {
            result.value = response.data.data;
        } else {
            errorMessage.value = isBn.value
                ? 'প্রদত্ত কোডের কোনো রেকর্ড পাওয়া যায়নি।'
                : 'No report record found for this code.';
        }
    } catch (err: any) {
        if (err.response?.status === 404) {
            errorMessage.value = isBn.value
                ? 'এই ট্র্যাকিং কোডের বিপরীতে কোনো রিপোর্ট খুঁজে পাওয়া যায়নি। কোডটি পুনরায় যাচাই করুন।'
                : 'No submission found for this tracking code. Please verify and try again.';
        } else {
            errorMessage.value = isBn.value
                ? 'তথ্য অনুসন্ধানে সমস্যা হয়েছে। অনুগ্রহ করে কিছুক্ষণ পর চেষ্টা করুন।'
                : 'An error occurred while tracking. Please try again.';
        }
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    if (props.initialCode) {
        searchTracking(props.initialCode);
    }
});

// Helper for stage calculation
function getActiveStepIndex(status: string): number {
    switch (status) {
        case 'submitted':
            return 1;
        case 'triaged':
            return 2;
        case 'in_verification':
            return 3;
        case 'verified':
        case 'rejected':
        case 'duplicate':
        case 'needs_info':
        case 'retracted':
        case 'spam':
            return 4;
        default:
            return 1;
    }
}
</script>

<template>
    <PublicLayout :title="isBn ? 'রিপোর্ট ট্র্যাকিং' : 'Track Report Status'">
        <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-700 bg-slate-800/80 px-3 py-1 text-xs font-semibold text-slate-300"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                    ></span>
                    {{
                        isBn
                            ? 'বেনামী স্ট্যাটাস অনুসন্ধান'
                            : 'Anonymous Status Verification'
                    }}
                </span>
                <h1
                    class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'রিপোর্টের পর্যালোচনা ও অগ্রগতি ট্র্যাক করুন'
                            : 'Track Submission Progress'
                    }}
                </h1>
                <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-400">
                    {{
                        isBn
                            ? 'রিপোর্ট জমা দেওয়ার সময় প্রাপ্ত ট্র্যাকিং কোডটি দিয়ে আপনার রিপোর্টের বর্তমান অবস্থা এবং ৮-দফা যাচাইকরণের ধাপ জেনে নিন।'
                            : 'Enter your private tracking code to view the live status and 8-point verification pipeline of your submitted report.'
                    }}
                </p>
            </div>

            <!-- Search Bar -->
            <div class="mx-auto max-w-xl">
                <form @submit.prevent="searchTracking()" class="flex gap-2">
                    <input
                        v-model="trackingCode"
                        type="text"
                        placeholder="NMS-XXXX-XXXX"
                        class="flex-1 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 font-mono text-sm uppercase tracking-wider text-white placeholder-slate-500 focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                        required
                    />
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="rounded-xl bg-rose-600 px-6 py-3 text-sm font-semibold text-white shadow-md transition-colors hover:bg-rose-500 disabled:opacity-50"
                    >
                        <span v-if="isLoading">{{
                            isBn ? 'খোঁজা হচ্ছে...' : 'Tracking...'
                        }}</span>
                        <span v-else>{{ isBn ? 'অনুসন্ধান' : 'Track' }}</span>
                    </button>
                </form>
            </div>

            <!-- Error State -->
            <div
                v-if="errorMessage"
                class="mx-auto mt-8 max-w-xl rounded-xl border border-rose-500/40 bg-rose-950/20 p-4 text-center text-sm text-rose-300"
            >
                {{ errorMessage }}
            </div>

            <!-- Result Card -->
            <div
                v-if="result"
                class="mt-10 rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl sm:p-8"
            >
                <!-- Status Header -->
                <div
                    class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-6"
                >
                    <div>
                        <div
                            class="text-xs uppercase tracking-wider text-slate-400"
                        >
                            {{ isBn ? 'ট্র্যাকিং কোড' : 'Tracking Code' }}
                        </div>
                        <div
                            class="mt-1 font-mono text-xl font-black tracking-widest text-white"
                        >
                            {{ result.tracking_code }}
                        </div>
                    </div>

                    <div>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider"
                            :class="{
                                'border border-emerald-500/30 bg-emerald-950/60 text-emerald-300':
                                    result.status === 'verified',
                                'border border-amber-500/30 bg-amber-950/60 text-amber-300':
                                    [
                                        'in_verification',
                                        'triaged',
                                        'submitted',
                                    ].includes(result.status),
                                'border border-rose-500/30 bg-rose-950/60 text-rose-300':
                                    result.status === 'rejected',
                                'border border-slate-700 bg-slate-800 text-slate-400':
                                    ['duplicate', 'retracted', 'spam'].includes(
                                        result.status,
                                    ),
                            }"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-current"
                            ></span>
                            {{
                                isBn
                                    ? result.status_label_bn
                                    : result.status_label_en
                            }}
                        </span>
                    </div>
                </div>

                <!-- 4-Stage Stepper Pipeline -->
                <div class="my-8">
                    <div
                        class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        {{
                            isBn
                                ? 'যাচাইকরণ প্রক্রিয়া ও ধাপসমূহ'
                                : 'Verification Pipeline'
                        }}
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <!-- Step 1 -->
                        <div
                            class="rounded-xl border p-4 text-center"
                            :class="
                                getActiveStepIndex(result.status) >= 1
                                    ? 'border-rose-500/50 bg-rose-950/20 text-white'
                                    : 'border-slate-800 bg-slate-900/40 text-slate-500'
                            "
                        >
                            <div class="font-mono text-xs font-bold">
                                ০১. জমা গ্রহণ
                            </div>
                            <div class="mt-1 text-xs font-semibold">
                                {{ isBn ? 'নথিবদ্ধ হয়েছে' : 'Submitted' }}
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div
                            class="rounded-xl border p-4 text-center"
                            :class="
                                getActiveStepIndex(result.status) >= 2
                                    ? 'border-rose-500/50 bg-rose-950/20 text-white'
                                    : 'border-slate-800 bg-slate-900/40 text-slate-500'
                            "
                        >
                            <div class="font-mono text-xs font-bold">
                                ০২. প্রাথমিক বাছাই
                            </div>
                            <div class="mt-1 text-xs font-semibold">
                                {{
                                    isBn
                                        ? 'ডুপ্লিকেট ও স্প্যাম ছাঁটাই'
                                        : 'Triaged'
                                }}
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div
                            class="rounded-xl border p-4 text-center"
                            :class="
                                getActiveStepIndex(result.status) >= 3
                                    ? 'border-rose-500/50 bg-rose-950/20 text-white'
                                    : 'border-slate-800 bg-slate-900/40 text-slate-500'
                            "
                        >
                            <div class="font-mono text-xs font-bold">
                                ০৩. ৮-দফা যাচাই
                            </div>
                            <div class="mt-1 text-xs font-semibold">
                                {{
                                    isBn ? 'যাচাইকরণ চলমান' : 'In Verification'
                                }}
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div
                            class="rounded-xl border p-4 text-center"
                            :class="
                                getActiveStepIndex(result.status) >= 4
                                    ? 'border-emerald-500/50 bg-emerald-950/20 text-emerald-300'
                                    : 'border-slate-800 bg-slate-900/40 text-slate-500'
                            "
                        >
                            <div class="font-mono text-xs font-bold">
                                ০৪. চূড়ান্ত সিদ্ধান্ত
                            </div>
                            <div class="mt-1 text-xs font-semibold">
                                {{
                                    isBn ? 'পর্যালোচনা সমাপ্ত' : 'Final Outcome'
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Details Table -->
                <div class="border-t border-slate-800 pt-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-slate-400">
                                {{ isBn ? 'শ্রেণিবিভাগ' : 'Category' }}
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-slate-200"
                            >
                                {{
                                    isBn
                                        ? result.category_label_bn || 'সাধারণ'
                                        : result.category || 'General'
                                }}
                            </dd>
                        </div>

                        <div v-if="result.incident">
                            <dt class="text-xs font-medium text-slate-400">
                                {{ isBn ? 'সংযুক্ত ঘটনা' : 'Linked Incident' }}
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-slate-200"
                            >
                                {{
                                    isBn
                                        ? result.incident.title_bn
                                        : result.incident.title_en
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-400">
                                {{
                                    isBn
                                        ? 'জমা দেওয়ার তারিখ'
                                        : 'Submission Date'
                                }}
                            </dt>
                            <dd class="mt-1 font-mono text-sm text-slate-300">
                                {{
                                    result.submitted_at
                                        ? new Date(
                                              result.submitted_at,
                                          ).toLocaleDateString()
                                        : 'N/A'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-400">
                                {{
                                    isBn
                                        ? 'যাচাইকরণের তারিখ'
                                        : 'Verification Date'
                                }}
                            </dt>
                            <dd class="mt-1 font-mono text-sm text-slate-300">
                                {{
                                    result.verified_at
                                        ? new Date(
                                              result.verified_at,
                                          ).toLocaleDateString()
                                        : isBn
                                          ? 'প্রক্রিয়াধীন'
                                          : 'In Progress'
                                }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Public record link if verified -->
                <div
                    v-if="
                        result.status === 'verified' &&
                        (result.visibility === 'public' ||
                            result.visibility === 'featured')
                    "
                    class="mt-8 border-t border-slate-800 pt-6 text-center"
                >
                    <p class="mb-3 text-xs text-slate-400">
                        {{
                            isBn
                                ? 'এই রিপোর্টটি যাচাইকরণ সম্পন্ন হয়েছে এবং জনস্বার্থে আর্কাইভে প্রকাশিত হয়েছে।'
                                : 'This report is verified and publicly published in the archive.'
                        }}
                    </p>
                    <Link
                        :href="`/search?q=${result.tracking_code}`"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-5 py-2 text-xs font-semibold text-white hover:bg-emerald-500"
                    >
                        {{
                            isBn
                                ? 'পাবলিক আর্কাইভে দেখুন →'
                                : 'View in Public Archive →'
                        }}
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
