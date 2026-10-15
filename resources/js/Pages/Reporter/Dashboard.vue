<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface ReporterInfo {
    id: number;
    email: string;
    reputation: number;
}

interface SubmittedReportItem {
    uuid: string;
    workflow_status: string;
    workflow_status_label: string;
    visibility: string;
    category?: string | null;
    category_label_bn?: string | null;
    comment_text?: string | null;
    submitted_at?: string | null;
    verified_at?: string | null;
    incident?: {
        title_bn: string;
        title_en: string;
    } | null;
}

const props = defineProps<{
    reporter?: ReporterInfo | null;
}>();

const { isBn } = useLanguage();

const emailInput = ref('');
const isRequestingMagicLink = ref(false);
const magicLinkSent = ref(false);
const magicLinkMessage = ref<string | null>(null);
const devVerifyUrl = ref<string | null>(null);

const currentReporter = ref<ReporterInfo | null>(props.reporter || null);
const submissionsList = ref<SubmittedReportItem[]>([]);
const isLoadingSubmissions = ref(false);

async function requestMagicLink() {
    if (!emailInput.value) return;

    isRequestingMagicLink.value = true;
    magicLinkMessage.value = null;

    try {
        const response = await axios.post('/api/v1/auth/magic-link', {
            email: emailInput.value,
        });
        magicLinkSent.value = true;
        magicLinkMessage.value = response.data?.message;
        if (response.data?.verify_url) {
            devVerifyUrl.value = response.data.verify_url;
        }
    } catch (err: any) {
        magicLinkMessage.value =
            err.response?.data?.message || 'ম্যাজিক লিঙ্ক পাঠানো সম্ভব হয়নি।';
    } finally {
        isRequestingMagicLink.value = false;
    }
}

async function fetchSubmissions() {
    isLoadingSubmissions.value = true;
    try {
        const response = await axios.get('/api/v1/me/submissions');
        if (response.data?.success && response.data?.data) {
            submissionsList.value = response.data.data;
        }
    } catch {
        // Not authenticated
    } finally {
        isLoadingSubmissions.value = false;
    }
}

onMounted(() => {
    // If verify URL contains signature and id, complete verification
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }

    if (currentReporter.value) {
        fetchSubmissions();
    }
});
</script>

<template>
    <PublicLayout :title="isBn ? 'রিপোর্টার পোর্টাল' : 'Reporter Portal'">
        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-700 bg-slate-800/80 px-3 py-1 text-xs font-semibold text-slate-300"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    {{
                        isBn
                            ? 'নাগরিক তদন্ত ও অবদান'
                            : 'Reporter Activity & Portal'
                    }}
                </span>
                <h1
                    class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'আমার জমাকৃত রিপোর্টসমূহ'
                            : 'My Submitted Reports'
                    }}
                </h1>
                <p class="mx-auto mt-2 max-w-xl text-sm text-slate-400">
                    {{
                        isBn
                            ? 'পাসওয়ার্ডহীন নিরাপদ ম্যাজিক লিঙ্কের মাধ্যমে আপনার ইমেইল যাচাই করে পূর্বে জমাকৃত সমস্ত রিপোর্টের লাইভ অগ্রগতি ও ফলাফল দেখুন।'
                            : 'Access all your past submissions, review verification statuses, and monitor your contributor score via passwordless magic-link.'
                    }}
                </p>
            </div>

            <!-- UN-AUTHENTICATED STATE: MAGIC LINK REQUEST -->
            <div
                v-if="!currentReporter"
                class="mx-auto max-w-md rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl sm:p-8"
            >
                <div v-if="magicLinkSent" class="space-y-4 text-center">
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/20 text-xl text-emerald-400"
                    >
                        ✉️
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        {{
                            isBn
                                ? 'ম্যাজিক লিঙ্ক পাঠানো হয়েছে'
                                : 'Magic Link Generated'
                        }}
                    </h3>
                    <p class="text-xs text-slate-300">
                        {{ magicLinkMessage }}
                    </p>

                    <!-- Direct link for quick development testing -->
                    <div
                        v-if="devVerifyUrl"
                        class="rounded-lg border border-slate-800 bg-slate-950 p-3 text-left"
                    >
                        <div
                            class="text-[10px] uppercase tracking-wider text-slate-500"
                        >
                            {{
                                isBn
                                    ? 'লগইন লিঙ্ক (সরাসরি):'
                                    : 'Direct Login Link:'
                            }}
                        </div>
                        <a
                            :href="devVerifyUrl"
                            class="break-all font-mono text-xs text-rose-400 underline hover:text-rose-300"
                        >
                            {{ devVerifyUrl }}
                        </a>
                    </div>

                    <button
                        type="button"
                        @click="
                            magicLinkSent = false;
                            emailInput = '';
                        "
                        class="pt-2 text-xs text-slate-400 underline hover:text-slate-200"
                    >
                        {{
                            isBn
                                ? 'ভিন্ন ইমেইল ব্যবহার করবেন?'
                                : 'Use another email?'
                        }}
                    </button>
                </div>

                <form
                    v-else
                    @submit.prevent="requestMagicLink"
                    class="space-y-4"
                >
                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300"
                        >
                            {{
                                isBn
                                    ? 'আপনার ইমেইল ঠিকানা'
                                    : 'Your Email Address'
                            }}
                        </label>
                        <input
                            v-model="emailInput"
                            type="email"
                            required
                            placeholder="reporter@example.com"
                            class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="isRequestingMagicLink"
                        class="w-full rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-bold text-white shadow-md transition-colors hover:bg-rose-500 disabled:opacity-50"
                    >
                        <span v-if="isRequestingMagicLink">{{
                            isBn ? 'লিঙ্ক তৈরি হচ্ছে...' : 'Sending...'
                        }}</span>
                        <span v-else>{{
                            isBn ? 'লগইন লিঙ্ক পাঠান →' : 'Send Magic Link →'
                        }}</span>
                    </button>
                </form>
            </div>

            <!-- AUTHENTICATED STATE: SUBMISSIONS DASHBOARD -->
            <div v-else class="space-y-6">
                <!-- Reporter Profile Card -->
                <div
                    class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-800 bg-slate-900/80 p-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-600/20 font-bold text-rose-400"
                        >
                            {{
                                currentReporter.email
                                    .substring(0, 1)
                                    .toUpperCase()
                            }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white">
                                {{ currentReporter.email }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{
                                    isBn
                                        ? 'নাগরিক রিপোর্টার আইডি:'
                                        : 'Reporter ID:'
                                }}
                                #{{ currentReporter.id }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-center"
                        >
                            <div
                                class="text-[10px] uppercase tracking-wider text-slate-400"
                            >
                                {{ isBn ? 'রেপুটেশন স্কোর' : 'Reputation' }}
                            </div>
                            <div
                                class="font-mono text-base font-bold text-emerald-400"
                            >
                                +{{ currentReporter.reputation }}
                            </div>
                        </div>

                        <Link
                            href="/submit"
                            class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-500"
                        >
                            {{ isBn ? '+ নতুন রিপোর্ট' : '+ New Report' }}
                        </Link>
                    </div>
                </div>

                <!-- Submissions Table / Cards -->
                <div
                    class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60"
                >
                    <div class="border-b border-slate-800 p-4">
                        <h3 class="text-sm font-bold text-white">
                            {{
                                isBn
                                    ? 'আমার জমাকৃত রিপোর্ট ও স্ট্যাটাস'
                                    : 'Submission History & Status'
                            }}
                        </h3>
                    </div>

                    <div
                        v-if="isLoadingSubmissions"
                        class="p-8 text-center text-xs text-slate-400"
                    >
                        {{
                            isBn
                                ? 'তথ্য লোড হচ্ছে...'
                                : 'Loading submissions...'
                        }}
                    </div>

                    <div
                        v-else-if="submissionsList.length === 0"
                        class="space-y-3 p-8 text-center"
                    >
                        <p class="text-xs text-slate-400">
                            {{
                                isBn
                                    ? 'আপনার এই ইমেইলে এখনো কোনো রিপোর্ট জমা দেওয়া হয়নি।'
                                    : 'No submissions found under this email.'
                            }}
                        </p>
                        <Link
                            href="/submit"
                            class="inline-block rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-500"
                        >
                            {{
                                isBn
                                    ? 'প্রথম রিপোর্ট জমা দিন'
                                    : 'Submit First Report'
                            }}
                        </Link>
                    </div>

                    <div v-else class="divide-y divide-slate-800">
                        <div
                            v-for="item in submissionsList"
                            :key="item.uuid"
                            class="flex flex-col justify-between gap-4 p-4 transition-colors hover:bg-slate-800/40 sm:flex-row sm:items-center"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-mono text-xs text-slate-400"
                                        >UUID:
                                        {{ item.uuid.substring(0, 8) }}...</span
                                    >
                                    <span
                                        class="rounded px-2 py-0.5 text-[10px] font-semibold uppercase"
                                        :class="{
                                            'border border-emerald-800 bg-emerald-950 text-emerald-300':
                                                item.workflow_status ===
                                                'verified',
                                            'border border-amber-800 bg-amber-950 text-amber-300':
                                                [
                                                    'in_verification',
                                                    'triaged',
                                                    'submitted',
                                                ].includes(
                                                    item.workflow_status,
                                                ),
                                            'border border-rose-800 bg-rose-950 text-rose-300':
                                                item.workflow_status ===
                                                'rejected',
                                            'border border-slate-700 bg-slate-800 text-slate-400':
                                                [
                                                    'duplicate',
                                                    'retracted',
                                                ].includes(
                                                    item.workflow_status,
                                                ),
                                        }"
                                    >
                                        {{ item.workflow_status_label }}
                                    </span>
                                </div>
                                <div
                                    v-if="item.incident"
                                    class="text-xs font-semibold text-slate-200"
                                >
                                    {{
                                        isBn
                                            ? item.incident.title_bn
                                            : item.incident.title_en
                                    }}
                                </div>
                                <p
                                    v-if="item.comment_text"
                                    class="line-clamp-1 text-xs italic text-slate-400"
                                >
                                    "{{ item.comment_text }}"
                                </p>
                            </div>

                            <div
                                class="text-right font-mono text-xs text-slate-500"
                            >
                                <div>
                                    {{
                                        item.submitted_at
                                            ? new Date(
                                                  item.submitted_at,
                                              ).toLocaleDateString()
                                            : ''
                                    }}
                                </div>
                                <div
                                    v-if="item.category_label_bn"
                                    class="text-[11px] text-slate-400"
                                >
                                    {{
                                        isBn
                                            ? item.category_label_bn
                                            : item.category
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
