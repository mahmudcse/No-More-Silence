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
            <div class="border-b border-slate-800 pb-6">
                <div class="flex items-center gap-2">
                    <span
                        class="rounded border border-sky-500/20 bg-sky-500/10 px-2.5 py-0.5 text-xs font-semibold text-sky-400"
                    >
                        {{
                            isBn ? 'কমিউনিটি প্রতিক্রিয়া' : 'Community Feedback'
                        }}
                    </span>
                </div>
                <h1
                    class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'ত্রুটি রিপোর্ট ও ফিচার প্রস্তাবনা'
                            : 'Report a Bug or Suggest a Feature'
                    }}
                </h1>
                <p class="mt-2 text-sm text-slate-400">
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
                class="space-y-4 rounded-2xl border border-emerald-500/30 bg-emerald-950/20 p-8 text-center"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/20 text-2xl font-bold text-emerald-400"
                >
                    ✓
                </div>
                <h2 class="text-xl font-bold text-white">
                    {{
                        isBn
                            ? 'ধন্যবাদ! আপনার বার্তাটি পৌঁছেছে'
                            : 'Feedback Received!'
                    }}
                </h2>
                <p
                    class="mx-auto max-w-md text-sm leading-relaxed text-emerald-200"
                >
                    {{ successData.message }}
                </p>
                <div class="font-mono text-xs text-slate-400">
                    {{ isBn ? 'রেফারেন্স আইডি:' : 'Reference ID:' }}
                    <span class="ml-1 font-bold text-slate-200">{{
                        successData.feedback_uuid
                    }}</span>
                </div>
                <div class="flex justify-center gap-3 pt-4">
                    <button
                        type="button"
                        @click="resetForm"
                        class="rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-200 transition-colors hover:bg-slate-700"
                    >
                        {{ isBn ? 'আরেকটি মতামত পাঠান' : 'Submit Another' }}
                    </button>
                    <Link
                        href="/"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-rose-500"
                    >
                        {{ isBn ? 'হোমে ফিরে যান' : 'Back to Home' }}
                    </Link>
                </div>
            </div>

            <!-- Form State -->
            <form
                v-else
                @submit.prevent="submitFeedback"
                class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl sm:p-8"
            >
                <!-- Feedback Type Selector Tabs -->
                <div class="space-y-2">
                    <label
                        class="block text-xs font-semibold uppercase tracking-wider text-slate-300"
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
                                    ? 'border-rose-500 bg-rose-500/20 text-white shadow-lg'
                                    : 'border-slate-800 bg-slate-950/60 text-slate-400 hover:border-slate-700 hover:text-slate-200'
                            "
                        >
                            <div class="mb-1 text-base">
                                <span v-if="t.value === 'bug_report'">🐞</span>
                                <span v-else-if="t.value === 'feature_request'"
                                    >💡</span
                                >
                                <span
                                    v-else-if="
                                        t.value === 'improvement_suggestion'
                                    "
                                    >⚡</span
                                >
                                <span v-else>💬</span>
                            </div>
                            <div>{{ isBn ? t.label_bn : t.label_en }}</div>
                        </button>
                    </div>
                </div>

                <!-- Error Notice -->
                <div
                    v-if="errorMessage"
                    class="rounded-lg border border-rose-500/40 bg-rose-950/40 p-3 text-xs text-rose-300"
                >
                    {{ errorMessage }}
                </div>

                <!-- Title -->
                <div class="space-y-1">
                    <label
                        for="title"
                        class="block text-xs font-medium text-slate-300"
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
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                    />
                </div>

                <!-- Description -->
                <div class="space-y-1">
                    <label
                        for="description"
                        class="block text-xs font-medium text-slate-300"
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
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm leading-relaxed text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                    ></textarea>
                </div>

                <!-- Page URL (Optional) -->
                <div class="space-y-1">
                    <label
                        for="page_url"
                        class="block text-xs font-medium text-slate-300"
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
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                    />
                </div>

                <!-- Contact Email (Optional) -->
                <div class="space-y-1">
                    <label
                        for="contact_email"
                        class="block text-xs font-medium text-slate-300"
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
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                    />
                    <p class="text-[11px] text-slate-400">
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
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-rose-600 px-6 py-3 text-sm font-bold text-white transition-colors hover:bg-rose-500 disabled:opacity-50"
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
