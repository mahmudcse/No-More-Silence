<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, reactive, ref } from 'vue';

interface FeedbackTypeOption {
    value: string;
    label_en: string;
    label_bn: string;
}

const props = defineProps<{
    types: FeedbackTypeOption[];
    initialPageUrl?: string;
    initialType?: string;
}>();

const { isBn } = useLanguage();

const form = reactive({
    type: props.initialType || 'bug_report',
    title: '',
    description: '',
    page_url: props.initialPageUrl || '',
    contact_email: '',
    device_info: {} as Record<string, unknown>,
});

const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);
const successData = ref<{
    message: string;
    feedback_uuid: string;
} | null>(null);

onMounted(() => {
    if (typeof window !== 'undefined') {
        form.device_info = {
            screen: `${window.screen.width}x${window.screen.height}`,
            userAgent: navigator.userAgent,
            language: navigator.language,
            pathname: window.location.pathname,
        };
        if (!form.page_url && document.referrer) {
            form.page_url = document.referrer;
        }
    }
});

async function submitFeedback() {
    errorMessage.value = null;
    isSubmitting.value = true;

    try {
        const response = await axios.post('/api/v1/feedback', {
            type: form.type,
            title: form.title,
            description: form.description,
            page_url: form.page_url || null,
            contact_email: form.contact_email || null,
            device_info: form.device_info,
        });

        if (response.data.success) {
            successData.value = {
                message: response.data.message,
                feedback_uuid: response.data.feedback_uuid,
            };
        }
    } catch (err: any) {
        if (err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else if (err.response?.data?.errors) {
            const firstError = Object.values(err.response.data.errors)[0];
            errorMessage.value = Array.isArray(firstError)
                ? firstError[0]
                : String(firstError);
        } else {
            errorMessage.value = isBn.value
                ? 'অনুরোধটি প্রক্রিয়াকরণে সমস্যা হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।'
                : 'Failed to submit feedback. Please check your inputs and try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
}

function resetForm() {
    form.title = '';
    form.description = '';
    form.contact_email = '';
    successData.value = null;
    errorMessage.value = null;
}
</script>

<template>
    <PublicLayout
        :title="
            isBn
                ? 'মতামত, ত্রুটি ও ফিচার প্রস্তাবনা'
                : 'Feedback, Bug Reports & Feature Suggestions'
        "
    >
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'নো মোর সাইলেন্স প্ল্যাটফর্মে কোনো ত্রুটি বা বাগ পেলে জানান, কিংবা নতুন ফিচার ও উন্নয়নের পরামর্শ দিন।'
                        : 'Report bugs, request new features, or suggest improvements to help us strengthen the platform.'
                "
            />
        </Head>

        <div class="mx-auto max-w-3xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="border-b border-black/[0.08] pb-6 dark:border-white/[0.08]">
                <div class="flex items-center gap-2">
                    <span
                        class="rounded-full border border-sky-500/30 bg-sky-500/10 px-3 py-0.5 text-xs font-semibold text-sky-700 dark:border-sky-400/30 dark:bg-sky-500/20 dark:text-sky-300"
                    >
                        {{
                            isBn ? 'কমিউনিটি প্রতিক্রিয়া' : 'Community Feedback'
                        }}
                    </span>
                </div>
                <h1
                    class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    {{
                        isBn
                            ? 'ত্রুটি রিপোর্ট ও ফিচার প্রস্তাবনা'
                            : 'Report a Bug or Suggest a Feature'
                    }}
                </h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{
                        isBn
                            ? 'আমাদের প্ল্যাটফর্মকে আরও নিরাপদ, নির্ভুল ও কার্যকর করতে আপনার যেকোনো পর্যবেক্ষণ বা পরামর্শ অত্যন্ত মূল্যবান।'
                            : 'Help us improve No More Silence. Report bugs, technical glitches, or share ideas for new capabilities and improvements.'
                    }}
                </p>
            </div>

            <!-- Success State -->
            <div
                v-if="successData"
                class="space-y-4 rounded-2xl border border-emerald-500/30 bg-emerald-50/80 p-8 text-center shadow-glass backdrop-blur-xl dark:border-emerald-500/30 dark:bg-emerald-950/20"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                >
                    <svg
                        class="h-7 w-7"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4.5 12.75l6 6 9-13.5"
                        />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                    {{
                        isBn
                            ? 'ধন্যবাদ! আপনার বার্তাটি পৌঁছেছে'
                            : 'Feedback Received!'
                    }}
                </h2>
                <p
                    class="mx-auto max-w-md text-sm leading-relaxed text-emerald-800 dark:text-emerald-200"
                >
                    {{ successData.message }}
                </p>
                <div class="font-mono text-xs text-slate-500 dark:text-slate-400">
                    {{ isBn ? 'রেফারেন্স আইডি:' : 'Reference ID:' }}
                    <span class="ml-1 font-bold text-slate-900 dark:text-slate-200">{{
                        successData.feedback_uuid
                    }}</span>
                </div>
                <div class="flex justify-center gap-3 pt-4">
                    <button
                        type="button"
                        @click="resetForm"
                        class="rounded-xl border border-black/[0.1] bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        {{ isBn ? 'আরেকটি মতামত পাঠান' : 'Submit Another' }}
                    </button>
                    <Link
                        href="/"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-500"
                    >
                        {{ isBn ? 'হোমে ফিরে যান' : 'Back to Home' }}
                    </Link>
                </div>
            </div>

            <!-- Form State -->
            <form
                v-else
                @submit.prevent="submitFeedback"
                class="space-y-6 rounded-2xl border border-black/[0.08] bg-white/[0.80] p-6 shadow-glass backdrop-blur-xl dark:border-white/[0.1] dark:bg-slate-900/60 sm:p-8"
            >
                <!-- Feedback Type Selector Tabs -->
                <div class="space-y-2">
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300"
                    >
                        {{
                            isBn
                                ? 'মতামতের ধরণ নির্বাচন করুন *'
                                : 'Select Feedback Type *'
                        }}
                    </label>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <button
                            v-for="t in types"
                            :key="t.value"
                            type="button"
                            @click="form.type = t.value"
                            class="rounded-xl border p-3 text-center text-xs font-semibold transition-all"
                            :class="
                                form.type === t.value
                                    ? 'border-rose-500 bg-rose-50 text-rose-700 ring-2 ring-rose-500/20 shadow-sm dark:border-rose-500 dark:bg-rose-500/20 dark:text-rose-200 dark:ring-rose-500/30'
                                    : 'border-black/[0.08] bg-black/[0.02] text-slate-600 hover:border-black/[0.16] hover:bg-black/[0.04] hover:text-slate-900 dark:border-white/[0.08] dark:bg-white/[0.03] dark:text-slate-400 dark:hover:border-white/[0.16] dark:hover:bg-white/[0.06] dark:hover:text-slate-200'
                            "
                        >
                            <div class="mb-1.5 flex justify-center">
                                <svg
                                    v-if="t.value === 'bug_report'"
                                    class="h-4 w-4 text-rose-500 dark:text-rose-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    />
                                </svg>
                                <svg
                                    v-else-if="t.value === 'feature_request'"
                                    class="h-4 w-4 text-amber-500 dark:text-amber-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                    />
                                </svg>
                                <svg
                                    v-else-if="
                                        t.value === 'improvement_suggestion'
                                    "
                                    class="h-4 w-4 text-emerald-500 dark:text-emerald-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="h-4 w-4 text-sky-500 dark:text-sky-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                    />
                                </svg>
                            </div>
                            <div>{{ isBn ? t.label_bn : t.label_en }}</div>
                        </button>
                    </div>
                </div>

                <!-- Error Notice -->
                <div
                    v-if="errorMessage"
                    class="rounded-xl border border-rose-500/30 bg-rose-50 p-3 text-xs text-rose-700 dark:border-rose-500/40 dark:bg-rose-950/40 dark:text-rose-300"
                >
                    {{ errorMessage }}
                </div>

                <!-- Title -->
                <div class="space-y-1">
                    <label
                        for="title"
                        class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        {{
                            isBn ? 'শিরোনাম বা মূল বিষয় *' : 'Summary / Title *'
                        }}
                    </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        maxlength="200"
                        :placeholder="
                            isBn
                                ? 'সংক্ষেপে সমস্যা বা প্রস্তাবনার বিষয় লিখুন (যেমন: ম্যাপে জেলা ক্লিক কাজ করছে না)'
                                : 'Brief summary (e.g. Map tooltip does not open on mobile Safari)'
                        "
                        class="w-full rounded-xl border border-black/[0.12] bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.14] dark:bg-slate-950 dark:text-white dark:placeholder-slate-500 dark:focus:border-rose-500 dark:focus:ring-rose-500/30"
                    />
                </div>

                <!-- Description -->
                <div class="space-y-1">
                    <label
                        for="description"
                        class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        {{
                            isBn
                                ? 'বিস্তারিত বর্ণনা *'
                                : 'Detailed Description *'
                        }}
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        required
                        minlength="10"
                        maxlength="3000"
                        rows="5"
                        :placeholder="
                            isBn
                                ? 'সমস্যাটি কীভাবে ঘটেছে তা বিস্তারিত লিখুন (ধাপগুলো উল্লেখ করুন) অথবা আপনার প্রস্তাবিত ফিচারের কার্যকারিতা বুঝিয়ে বলুন...'
                                : 'Describe the bug steps to reproduce, or explain how the requested feature would help...'
                        "
                        class="w-full rounded-xl border border-black/[0.12] bg-white px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 placeholder-slate-400 transition-colors focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.14] dark:bg-slate-950 dark:text-white dark:placeholder-slate-500 dark:focus:border-rose-500 dark:focus:ring-rose-500/30"
                    ></textarea>
                </div>

                <!-- Page URL (Optional) -->
                <div class="space-y-1">
                    <label
                        for="page_url"
                        class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        {{
                            isBn
                                ? 'সংশ্লিষ্ট পাতার লিঙ্ক (ঐচ্ছিক)'
                                : 'Affected Page URL (Optional)'
                        }}
                    </label>
                    <input
                        id="page_url"
                        v-model="form.page_url"
                        type="url"
                        maxlength="500"
                        placeholder="https://nomoresilence.org/districts/dhaka"
                        class="w-full rounded-xl border border-black/[0.12] bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.14] dark:bg-slate-950 dark:text-white dark:placeholder-slate-500 dark:focus:border-rose-500 dark:focus:ring-rose-500/30"
                    />
                </div>

                <!-- Contact Email (Optional) -->
                <div class="space-y-1">
                    <label
                        for="contact_email"
                        class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        {{
                            isBn
                                ? 'আপনার ইমেইল (ঐচ্ছিক - যোগাযোগের জন্য)'
                                : 'Your Email (Optional - for follow-up)'
                        }}
                    </label>
                    <input
                        id="contact_email"
                        v-model="form.contact_email"
                        type="email"
                        maxlength="255"
                        placeholder="researcher@example.org"
                        class="w-full rounded-xl border border-black/[0.12] bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.14] dark:bg-slate-950 dark:text-white dark:placeholder-slate-500 dark:focus:border-rose-500 dark:focus:ring-rose-500/30"
                    />
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{
                            isBn
                                ? 'ইমেইল দিলে আমরা সমস্যা সমাধানের পর আপনাকে আপডেট জানাতে পারব। এটি সম্পূর্ণ এনক্রিপ্ট করে রাখা হয়।'
                                : 'If provided, we will notify you once resolved. Encrypted at rest and never shared.'
                        }}
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-rose-500 active:scale-[0.99] disabled:opacity-50"
                    >
                        <span v-if="isSubmitting" class="animate-spin text-base"
                            >⏳</span
                        >
                        <span>
                            {{
                                isSubmitting
                                    ? isBn
                                        ? 'জমা দেওয়া হচ্ছে...'
                                        : 'Submitting...'
                                    : isBn
                                      ? 'মতামত জমা দিন'
                                      : 'Submit Feedback'
                            }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
