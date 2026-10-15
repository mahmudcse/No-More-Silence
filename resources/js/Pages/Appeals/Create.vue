<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps<{
    prefilledProfileUrl?: string;
    prefilledReportUuid?: string;
    turnstileSiteKey?: string | null;
}>();

const { isBn } = useLanguage();

const form = ref({
    facebook_profile_url: props.prefilledProfileUrl || '',
    report_uuid: props.prefilledReportUuid || '',
    contact: '',
    message: '',
    ownership_proof: '',
    cf_turnstile_response: '',
});

const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);
const successData = ref<{
    appeal_id?: number;
    message?: string;
} | null>(null);

async function submitAppeal() {
    errorMessage.value = null;
    isSubmitting.value = true;

    try {
        const response = await axios.post('/api/v1/appeals', form.value);
        successData.value = response.data;
    } catch (err: any) {
        if (err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else if (err.response?.data?.errors) {
            const firstErrorKey = Object.keys(err.response.data.errors)[0];
            errorMessage.value = err.response.data.errors[firstErrorKey][0];
        } else {
            errorMessage.value = isBn.value
                ? 'আপিল জমা দিতে সমস্যা হয়েছে। অনুগ্রহ করে তথ্য পরীক্ষা করে পুনরায় চেষ্টা করুন।'
                : 'Failed to submit appeal. Please check details and try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <PublicLayout
        :title="
            isBn
                ? 'আপিল ও আত্মপক্ষ সমর্থন (Right of Reply)'
                : 'Submit Appeal / Right of Reply'
        "
    >
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-rose-500/30 bg-rose-950/40 px-3 py-1 text-xs font-semibold text-rose-300"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                    {{
                        isBn
                            ? 'অধিকার ও নিরপেক্ষ পর্যালোচনা'
                            : 'Due Process & Right of Reply'
                    }}
                </span>
                <h1
                    class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'ভুল সংশোধন ও আপিল আবেদন'
                            : 'Submit an Appeal or Correction'
                    }}
                </h1>
                <p class="mx-auto mt-2 max-w-xl text-sm text-slate-400">
                    {{
                        isBn
                            ? 'যদি আপনার বিশ্বাস হয় যে কোনো রেকর্ড ভুলবশত তৈরি হয়েছে, প্রোফাইল হ্যাকড হয়েছিল, ফেক অ্যাকাউন্ট দ্বারা অপব্যবহার হয়েছে, বা আপনার বক্তব্য অপ্রাসঙ্গিকভাবে তুলে ধরা হয়েছে—তবে বিস্তারিত জানিয়ে আপিল করুন।'
                            : 'If you believe a record was made in error, your account was compromised, impersonated, or context was missing, please submit an appeal with corroborating details.'
                    }}
                </p>
            </div>

            <!-- Standard Procedure Notice -->
            <div
                class="mb-8 rounded-xl border border-slate-700 bg-slate-900/60 p-4 text-xs text-slate-300"
            >
                <div class="flex items-start gap-3">
                    <span class="text-base text-rose-400">ℹ️</span>
                    <div class="space-y-1">
                        <strong class="font-semibold text-white">
                            {{
                                isBn
                                    ? 'আপিল প্রক্রিয়ার নীতিমালা:'
                                    : 'Appeals Protocol & SLA:'
                            }}
                        </strong>
                        <p class="leading-relaxed text-slate-400">
                            {{
                                isBn
                                    ? 'প্রতিটি আপিল আমাদের নিরপেক্ষ রিভিউয়ার বোর্ড দ্বারা ৭২ ঘণ্টার মধ্যে স্বতন্ত্রভাবে যাচাই করা হয়। প্রাথমিক সত্যতা মিললে তদন্তকালীন সময়ে রেকর্ডটি সাময়িকভাবে অপ্রকাশিত রাখা হয়।'
                                    : 'All appeals are independently reviewed within 72 hours. If preliminary evidence indicates error or impersonation, the record is temporarily unlisted during investigation.'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Success State -->
            <div
                v-if="successData"
                class="rounded-2xl border border-emerald-500/40 bg-emerald-950/20 p-8 text-center shadow-xl"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/20 text-2xl text-emerald-400"
                >
                    ✓
                </div>
                <h2 class="mt-4 text-2xl font-bold text-white">
                    {{
                        isBn
                            ? 'আপিল আবেদন গৃহীত হয়েছে'
                            : 'Appeal Successfully Received'
                    }}
                </h2>
                <p class="mt-2 text-sm text-slate-300">
                    {{ successData.message }}
                </p>
                <div class="mt-4 font-mono text-xs text-slate-400">
                    {{ isBn ? 'রেফারেন্স আইডি:' : 'Reference ID:' }} #{{
                        successData.appeal_id
                    }}
                </div>
                <div class="mt-6 flex justify-center gap-4">
                    <Link
                        href="/"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500"
                    >
                        {{ isBn ? 'হোমে ফিরে যান' : 'Return Home' }}
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div
                v-else
                class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-xl sm:p-8"
            >
                <div
                    v-if="errorMessage"
                    class="mb-6 rounded-lg border border-rose-500/40 bg-rose-950/30 p-4 text-xs text-rose-300"
                >
                    {{ errorMessage }}
                </div>

                <form @submit.prevent="submitAppeal" class="space-y-6">
                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300"
                        >
                            {{
                                isBn
                                    ? 'ফেসবুক প্রোফাইল URL *'
                                    : 'Facebook Profile URL *'
                            }}
                        </label>
                        <input
                            v-model="form.facebook_profile_url"
                            type="url"
                            required
                            placeholder="https://www.facebook.com/..."
                            class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300"
                        >
                            {{
                                isBn
                                    ? 'নির্দিষ্ট রিপোর্টের UUID (যদি জানা থাকে)'
                                    : 'Report UUID (Optional)'
                            }}
                        </label>
                        <input
                            v-model="form.report_uuid"
                            type="text"
                            placeholder="যেমন: 550e8400-e29b-41d4-a716-446655440000"
                            class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 font-mono text-sm text-white placeholder-slate-500 focus:border-rose-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300"
                        >
                            {{
                                isBn
                                    ? 'যোগাযোগের ঠিকানা (ইমেইল অথবা মোবাইল নম্বর) *'
                                    : 'Contact (Email or Phone Number) *'
                            }}
                        </label>
                        <input
                            v-model="form.contact"
                            type="text"
                            required
                            placeholder="আপিলের সিদ্ধান্ত ও যোগাযোগের জন্য..."
                            class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300"
                        >
                            {{
                                isBn
                                    ? 'আপিলের বিবরণ ও ব্যাখ্যা *'
                                    : 'Statement of Appeal & Reason *'
                            }}
                        </label>
                        <textarea
                            v-model="form.message"
                            required
                            rows="4"
                            placeholder="কেন এই রেকর্ডটি ভুল বা বিভ্রান্তিকর, অথবা কোনো ব্যাখ্যা থাকলে বিস্তারিত লিখুন..."
                            class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-300"
                        >
                            {{
                                isBn
                                    ? 'প্রোফাইলের মালিকানা বা দাবির সপক্ষে প্রমাণ / লিঙ্ক (ঐচ্ছিক)'
                                    : 'Ownership Proof / Corroborating Links (Optional)'
                            }}
                        </label>
                        <textarea
                            v-model="form.ownership_proof"
                            rows="2"
                            placeholder="ফেসবুক আইডি ভেরিফিকেশন, জিডি নম্বর বা অন্য কোনো প্রামাণ্য তথ্য..."
                            class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500"
                        ></textarea>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full rounded-lg bg-rose-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition-colors hover:bg-rose-500 disabled:opacity-50"
                        >
                            <span v-if="isSubmitting">{{
                                isBn ? 'জমা হচ্ছে...' : 'Submitting Appeal...'
                            }}</span>
                            <span v-else>{{
                                isBn ? 'আপিল আবেদন জমা দিন' : 'Submit Appeal'
                            }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
