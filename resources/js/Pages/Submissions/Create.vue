<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, type Ref } from 'vue';

interface SharedFileItem {
    filename: string;
    name: string;
    size: number;
    mime: string;
    url: string;
}

interface SharedData {
    share_id: string;
    title?: string;
    text?: string;
    extracted_url?: string | null;
    files?: SharedFileItem[];
}

interface DistrictItem {
    id: number;
    name_bn: string;
    name_en: string;
}

interface CategoryItem {
    value: string;
    label_en: string;
    label_bn: string;
}

const props = defineProps<{
    categories: CategoryItem[];
    districts?: DistrictItem[];
    turnstileSiteKey?: string | null;
    sharedData?: SharedData | null;
}>();

const { isBn } = useLanguage();
const hasSharedData = ref(false);

// Form state
const currentStep = ref(1);
const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);
const successData = ref<{
    tracking_code?: string;
    already_reported?: boolean;
    message?: string;
    workflow_status?: string;
} | null>(null);

const form = ref({
    submitted_profile_url: '',
    submitted_comment_url: '',
    comment_text: '',
    category: '',
    reporter_email: '',
    cf_turnstile_response: '',
});

// File uploads by section
interface SelectedFile {
    file: File;
    previewUrl: string;
    sizeKb: number;
}

const postFiles = ref<SelectedFile[]>([]);
const commentFiles = ref<SelectedFile[]>([]);
const profileFiles = ref<SelectedFile[]>([]);
const otherFiles = ref<SelectedFile[]>([]);

const isCompressing = ref(false);
const copiedCode = ref(false);

const totalFilesCount = computed(() => {
    return (
        postFiles.value.length +
        commentFiles.value.length +
        profileFiles.value.length +
        otherFiles.value.length
    );
});

const canGoNextFromStep1 = computed(() => {
    return totalFilesCount.value > 0;
});

const canGoNextFromStep2 = computed(() => {
    return form.value.submitted_profile_url.trim().length > 0;
});

// Client-side image compression
async function compressImageFile(file: File): Promise<File> {
    return new Promise((resolve) => {
        if (!file.type.startsWith('image/')) {
            return resolve(file);
        }

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target?.result as string;
            img.onload = () => {
                const maxDim = 1920;
                let width = img.width;
                let height = img.height;
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    return resolve(file);
                }

                ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            return resolve(file);
                        }
                        const newName =
                            file.name.replace(/\.[^/.]+$/, '') + '.jpg';
                        const compressed = new File([blob], newName, {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        });
                        resolve(compressed);
                    },
                    'image/jpeg',
                    0.85,
                );
            };
            img.onerror = () => resolve(file);
        };
        reader.onerror = () => resolve(file);
    });
}

function getSectionTarget(
    section: 'post' | 'comment' | 'profile' | 'other',
): Ref<SelectedFile[]> {
    if (section === 'post') return postFiles;
    if (section === 'comment') return commentFiles;
    if (section === 'profile') return profileFiles;
    return otherFiles;
}

async function handleSectionFileSelect(
    event: Event,
    section: 'post' | 'comment' | 'profile' | 'other',
) {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;

    const targetList = getSectionTarget(section);

    isCompressing.value = true;
    try {
        for (let i = 0; i < input.files.length; i++) {
            const originalFile = input.files[i];
            const compressed = await compressImageFile(originalFile);
            const previewUrl = URL.createObjectURL(compressed);
            targetList.value.push({
                file: compressed,
                previewUrl,
                sizeKb: Math.round(compressed.size / 1024),
            });
        }
    } finally {
        isCompressing.value = false;
        input.value = '';
    }
}

function removeSectionFile(
    section: 'post' | 'comment' | 'profile' | 'other',
    index: number,
) {
    const targetList = getSectionTarget(section);
    const item = targetList.value[index];
    if (item) {
        URL.revokeObjectURL(item.previewUrl);
    }
    targetList.value.splice(index, 1);
}

function copyTrackingCode() {
    if (!successData.value?.tracking_code) return;
    navigator.clipboard.writeText(successData.value.tracking_code);
    copiedCode.value = true;
    setTimeout(() => {
        copiedCode.value = false;
    }, 2500);
}

async function handleSubmit() {
    errorMessage.value = null;
    isSubmitting.value = true;

    try {
        const formData = new FormData();
        formData.append(
            'submitted_profile_url',
            form.value.submitted_profile_url,
        );
        if (form.value.submitted_comment_url) {
            formData.append(
                'submitted_comment_url',
                form.value.submitted_comment_url,
            );
        }
        if (form.value.comment_text) {
            formData.append('comment_text', form.value.comment_text);
        }
        if (form.value.category) {
            formData.append('category', form.value.category);
        }
        if (form.value.reporter_email) {
            formData.append('reporter_email', form.value.reporter_email);
        }
        if (form.value.cf_turnstile_response) {
            formData.append(
                'cf_turnstile_response',
                form.value.cf_turnstile_response,
            );
        }

        // Attach compressed files by section
        postFiles.value.forEach((item) => {
            formData.append('evidence_post[]', item.file);
        });
        commentFiles.value.forEach((item) => {
            formData.append('evidence_comment[]', item.file);
        });
        profileFiles.value.forEach((item) => {
            formData.append('evidence_profile[]', item.file);
        });
        otherFiles.value.forEach((item) => {
            formData.append('evidence_other[]', item.file);
        });

        const response = await axios.post('/api/v1/submissions', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        successData.value = response.data;
    } catch (err: any) {
        if (err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else if (err.response?.data?.errors) {
            const firstErrorKey = Object.keys(err.response.data.errors)[0];
            errorMessage.value = err.response.data.errors[firstErrorKey][0];
        } else {
            errorMessage.value = isBn.value
                ? 'জমা দিতে সমস্যা হয়েছে। অনুগ্রহ করে ইন্টারনেট সংযোগ পরীক্ষা করে পুনরায় চেষ্টা করুন।'
                : 'Failed to submit report. Please verify connection and try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
}

onMounted(async () => {
    if (props.sharedData) {
        hasSharedData.value = true;
        if (props.sharedData.extracted_url) {
            form.value.submitted_profile_url = props.sharedData.extracted_url;
        }
        if (props.sharedData.text) {
            form.value.comment_text = props.sharedData.text;
        }
        if (props.sharedData.files && props.sharedData.files.length > 0) {
            isCompressing.value = true;
            try {
                for (const item of props.sharedData.files) {
                    try {
                        const resp = await fetch(item.url);
                        const blob = await resp.blob();
                        const file = new File([blob], item.name, {
                            type: item.mime,
                        });
                        const compressed = await compressImageFile(file);
                        const previewUrl = URL.createObjectURL(compressed);
                        commentFiles.value.push({
                            file: compressed,
                            previewUrl,
                            sizeKb: Math.round(compressed.size / 1024),
                        });
                    } catch (err) {
                        console.error('Failed to load shared file', err);
                    }
                }
            } finally {
                isCompressing.value = false;
            }
        }
    }
});
</script>

<template>
    <PublicLayout :title="isBn ? 'রিপোর্ট জমা দিন' : 'Submit Report'">
        <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Mobile Share Target Notification -->
            <div
                v-if="hasSharedData"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs text-emerald-900 dark:border-emerald-500/40 dark:bg-emerald-950/30 dark:text-emerald-200"
            >
                <div class="flex items-start gap-3">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"
                        />
                    </svg>
                    <div class="space-y-1">
                        <strong
                            class="font-semibold text-emerald-800 dark:text-emerald-300"
                        >
                            {{
                                isBn
                                    ? 'মোবাইল বা ফেসবুক শেয়ার থেকে কনটেন্ট যুক্ত হয়েছে:'
                                    : 'Imported from Mobile Share:'
                            }}
                        </strong>
                        <p
                            class="leading-relaxed text-emerald-700 dark:text-emerald-200/90"
                        >
                            {{
                                isBn
                                    ? 'আপনার শেয়ার করা স্ক্রিনশট এবং লিঙ্ক স্বয়ংক্রিয়ভাবে ফর্মটিতে যুক্ত করা হয়েছে। তথ্য পর্যালোচনা করে পরবর্তী ধাপে এগিয়ে যান।'
                                    : 'Your shared screenshots and links were imported automatically. Review and proceed with submission.'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="mb-8 text-center">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-rose-500/20 bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-700 backdrop-blur-md dark:border-rose-400/20 dark:bg-rose-500/15 dark:text-rose-300"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    {{
                        isBn ? 'নাগরিক প্রমাণ ভান্ডার' : 'Civic Evidence Intake'
                    }}
                </span>
                <h1
                    class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'সহিংসতার সমর্থনে মন্তব্যের রিপোর্ট করুন'
                            : 'Submit Evidence of Violence Apologia'
                    }}
                </h1>
                <p
                    class="mx-auto mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-400"
                >
                    {{
                        isBn
                            ? 'ধর্ষণ বা যৌন সহিংসতার সাফাই গাওয়া, ভিকটিম-ব্লেমিং বা সহিংসতায় উস্কানিমূলক মন্তব্যের ডিজিটাল স্ক্রিনশট ও লিঙ্ক নির্ভুলভাবে জমা দিন।'
                            : 'Submit verifiable screenshots and URLs of public comments condoning rape, blaming victims, or inciting sexual violence.'
                    }}
                </p>
            </div>

            <!-- Legal and Privacy Shield Notice -->
            <div
                class="mb-8 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5 text-xs text-amber-900 apple-glass-subtle dark:bg-amber-500/15 dark:text-amber-200"
            >
                <div class="flex items-start gap-3">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                        />
                    </svg>
                    <div class="space-y-1">
                        <strong
                            class="font-semibold text-amber-800 dark:text-amber-300"
                        >
                            {{
                                isBn
                                    ? 'ভিকটিম সুরক্ষা ও আইনগত সতর্কতা:'
                                    : 'Victim Protection & Legal Notice:'
                            }}
                        </strong>
                        <p
                            class="leading-relaxed text-amber-800 dark:text-amber-200/90"
                        >
                            {{
                                isBn
                                    ? 'নারী ও শিশু নির্যাতন দমন আইনের ১৪ ধারা অনুযায়ী কোনো অবস্থাতেই ভিকটিমের ছবি বা নাম সম্বলিত তথ্য জমা দেওয়া বা প্রকাশ করা নিষিদ্ধ। জমা দেওয়ার আগে স্ক্রিনশট থেকে ভিকটিমের ব্যক্তিগত তথ্য বাদ দিন।'
                                    : 'Under Section 14 of the Nari o Shishu Nirjatan Daman Ain 2000, publishing or submitting victim identities and photos is strictly prohibited.'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- SUCCESS STATE / TRACKING CODE MODAL -->
            <div
                v-if="successData"
                class="rounded-3xl border border-emerald-500/20 bg-emerald-50/70 p-8 text-center apple-glass-card shadow-glass-lg dark:bg-emerald-950/20"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-emerald-300 bg-emerald-100 text-emerald-600 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400"
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

                <div v-if="successData.already_reported">
                    <h2
                        class="mt-4 text-2xl font-bold text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? 'রেকর্ডটি পূর্বেই জমা হয়েছে'
                                : 'Already Documented'
                        }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {{ successData.message }}
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <Link
                            href="/track"
                            class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500"
                        >
                            {{
                                isBn
                                    ? 'অন্যান্য রিপোর্ট খুঁজুন'
                                    : 'Search Reports'
                            }}
                        </Link>
                    </div>
                </div>

                <div v-else>
                    <h2
                        class="mt-4 text-2xl font-bold text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? 'রিপোর্টটি সফলভাবে জমা হয়েছে!'
                                : 'Report Successfully Submitted!'
                        }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {{ successData.message }}
                    </p>

                    <!-- Tracking code box -->
                    <div
                        class="mx-auto mt-6 max-w-md rounded-xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-500/30 dark:bg-slate-900/90"
                    >
                        <div
                            class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? 'আপনার গোপনীয় ট্র্যাকিং কোড'
                                    : 'Your Private Tracking Code'
                            }}
                        </div>
                        <div
                            class="mt-2 flex items-center justify-center gap-3"
                        >
                            <span
                                class="font-mono text-2xl font-black tracking-widest text-emerald-600 dark:text-emerald-400"
                            >
                                {{ successData.tracking_code }}
                            </span>
                            <button
                                type="button"
                                @click="copyTrackingCode"
                                class="rounded border border-slate-300 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white"
                            >
                                {{
                                    copiedCode
                                        ? isBn
                                            ? 'কপি হয়েছে!'
                                            : 'Copied!'
                                        : isBn
                                          ? 'কপি করুন'
                                          : 'Copy'
                                }}
                            </button>
                        </div>
                        <p
                            class="mt-3 text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? 'এই কোডটি সংরক্ষণ করুন। আপনার পরিচয় সম্পূর্ণ বেনামী রাখা হয়েছে, তাই স্ট্যাটাস দেখতে কেবল এই কোডটি প্রয়োজন হবে।'
                                    : 'Please save this code. Submissions are fully anonymous; you will need this code to track review status.'
                            }}
                        </p>
                    </div>

                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <Link
                            :href="`/track/${successData.tracking_code}`"
                            class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md transition-colors hover:bg-emerald-500"
                        >
                            {{
                                isBn
                                    ? 'রিপোর্টের স্ট্যাটাস দেখুন'
                                    : 'Track Status Now'
                            }}
                        </Link>
                        <button
                            type="button"
                            @click="
                                successData = null;
                                currentStep = 1;
                                postFiles = [];
                                commentFiles = [];
                                profileFiles = [];
                                otherFiles = [];
                            "
                            class="rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                        >
                            {{
                                isBn
                                    ? 'আরেকটি রিপোর্ট জমা দিন'
                                    : 'Submit Another Report'
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- FORM WIZARD -->
            <div
                v-else
                class="rounded-3xl apple-glass-card p-6 shadow-glass-lg sm:p-8"
            >
                <!-- Stepper Progress Bar -->
                <div class="mb-8">
                    <div
                        class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400"
                    >
                        <div
                            class="flex items-center gap-2"
                            :class="
                                currentStep >= 1
                                    ? 'text-rose-600 dark:text-rose-400'
                                    : 'text-slate-500 dark:text-slate-400'
                            "
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full border text-xs"
                                :class="
                                    currentStep >= 1
                                        ? 'border-rose-500 bg-rose-500/20 text-rose-600 dark:text-rose-300'
                                        : 'border-slate-300 text-slate-400 dark:border-slate-700 dark:text-slate-500'
                                "
                                >1</span
                            >
                            <span>{{
                                isBn ? 'প্রমাণ আপলোড' : 'Evidence'
                            }}</span>
                        </div>
                        <div
                            class="mx-2 h-0.5 flex-1"
                            :class="
                                currentStep >= 2
                                    ? 'bg-rose-500/60'
                                    : 'bg-slate-200 dark:bg-slate-800'
                            "
                        ></div>
                        <div
                            class="flex items-center gap-2"
                            :class="
                                currentStep >= 2
                                    ? 'text-rose-600 dark:text-rose-400'
                                    : 'text-slate-500 dark:text-slate-400'
                            "
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full border text-xs"
                                :class="
                                    currentStep >= 2
                                        ? 'border-rose-500 bg-rose-500/20 text-rose-600 dark:text-rose-300'
                                        : 'border-slate-300 text-slate-400 dark:border-slate-700 dark:text-slate-500'
                                "
                                >2</span
                            >
                            <span>{{ isBn ? 'ফেসবুক লিঙ্ক' : 'URLs' }}</span>
                        </div>
                        <div
                            class="mx-2 h-0.5 flex-1"
                            :class="
                                currentStep >= 3
                                    ? 'bg-rose-500/60'
                                    : 'bg-slate-200 dark:bg-slate-800'
                            "
                        ></div>
                        <div
                            class="flex items-center gap-2"
                            :class="
                                currentStep >= 3
                                    ? 'text-rose-600 dark:text-rose-400'
                                    : 'text-slate-500 dark:text-slate-400'
                            "
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full border text-xs"
                                :class="
                                    currentStep >= 3
                                        ? 'border-rose-500 bg-rose-500/20 text-rose-600 dark:text-rose-300'
                                        : 'border-slate-300 text-slate-400 dark:border-slate-700 dark:text-slate-500'
                                "
                                >3</span
                            >
                            <span>{{
                                isBn ? 'শ্রেণিবিভাগ ও তথ্য' : 'Details'
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Error Notice -->
                <div
                    v-if="errorMessage"
                    class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 dark:border-rose-500/40 dark:bg-rose-950/30 dark:text-rose-300"
                >
                    {{ errorMessage }}
                </div>

                <form @submit.prevent="handleSubmit">
                    <!-- STEP 1: Evidence Screenshots (Multi-section) -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '১. প্রমাণভিত্তিক স্ক্রিনশট আপলোড করুন'
                                        : '1. Upload Evidence Screenshots'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'সুনির্দিষ্ট যাচাইকরণ ও বিশ্বাসযোগ্যতার জন্য মূল পোস্ট, আপত্তিকর মন্তব্য, প্রোফাইল ও অন্যান্য সহায়ক প্রমাণ আলাদা আলাদা সেকশনে সংযুক্ত করুন। প্রতিটি সেকশনে একাধিক ছবি দিতে পারবেন।'
                                        : 'Attach screenshots in separate sections for original post, comment, profile, and other evidence. Multiple photos can be attached to each section.'
                                }}
                            </p>
                        </div>

                        <div
                            v-if="isCompressing"
                            class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-center text-xs font-semibold text-rose-700 dark:border-rose-500/30 dark:bg-rose-950/20 dark:text-rose-300"
                        >
                            {{
                                isBn
                                    ? 'ছবি অপ্টিমাইজ ও কম্প্রেস হচ্ছে... অনুগ্রহ করে অপেক্ষা করুন।'
                                    : 'Optimizing and compressing images... please wait.'
                            }}
                        </div>

                        <!-- SECTION 1: OFFENSIVE COMMENT SCREENSHOTS -->
                        <div
                            class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-rose-200 bg-rose-100 text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-400"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.502 49.198 49.198 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.769z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                isBn
                                                    ? 'আপত্তিকর মন্তব্যের স্ক্রিনশট'
                                                    : 'Offensive Comment Screenshots'
                                            }}
                                        </h4>
                                        <p
                                            class="text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                isBn
                                                    ? 'যৌন সহিংসতা সমর্থন, সাফাই বা ভিকটিম-ব্লেমিং মন্তব্যের স্পষ্ট স্ক্রিনশট'
                                                    : 'Screenshots showing the abusive comment or victim-blaming'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="commentFiles.length > 0"
                                        class="rounded-full border border-rose-200 bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/20 dark:text-rose-300"
                                    >
                                        {{ commentFiles.length }}
                                        {{ isBn ? 'টি ছবি' : 'photo(s)' }}
                                    </span>
                                    <span
                                        class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 dark:border-rose-800/40 dark:bg-rose-950/60 dark:text-rose-400"
                                    >
                                        {{
                                            isBn
                                                ? 'মূল অপরাধ প্রমাণ'
                                                : 'Primary Evidence'
                                        }}
                                    </span>
                                </div>
                            </div>

                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-4 text-center transition-colors hover:border-rose-500/50 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950/40 dark:hover:bg-slate-900/60"
                            >
                                <div
                                    class="text-xs font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        isBn
                                            ? '+ মন্তব্যের স্ক্রিনশট যুক্ত করুন (একাধিক নির্বাচনযোগ্য)'
                                            : '+ Add Comment Screenshots (Select Multiple)'
                                    }}
                                </div>
                                <div
                                    class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500"
                                >
                                    {{
                                        isBn
                                            ? 'PNG, JPG, WebP সমর্থিত'
                                            : 'PNG, JPG, WebP supported'
                                    }}
                                </div>
                                <input
                                    type="file"
                                    accept="image/png, image/jpeg, image/webp"
                                    multiple
                                    class="hidden"
                                    @change="
                                        handleSectionFileSelect(
                                            $event,
                                            'comment',
                                        )
                                    "
                                />
                            </label>

                            <div
                                v-if="commentFiles.length > 0"
                                class="grid grid-cols-2 gap-3 sm:grid-cols-4"
                            >
                                <div
                                    v-for="(item, idx) in commentFiles"
                                    :key="idx"
                                    class="group relative overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <img
                                        :src="item.previewUrl"
                                        class="h-24 w-full object-cover"
                                        alt="Comment preview"
                                    />
                                    <div
                                        class="flex items-center justify-between p-1.5 text-[10px] text-slate-500 dark:text-slate-400"
                                    >
                                        <span>{{ item.sizeKb }} KB</span>
                                        <button
                                            type="button"
                                            @click="
                                                removeSectionFile(
                                                    'comment',
                                                    idx,
                                                )
                                            "
                                            class="inline-flex items-center gap-0.5 font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                                        >
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                            <span>{{
                                                isBn ? 'বাদ' : 'Del'
                                            }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: ORIGINAL POST SCREENSHOTS -->
                        <div
                            class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-blue-200 bg-blue-100 text-blue-600 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                isBn
                                                    ? 'মূল পোস্টের স্ক্রিনশট'
                                                    : 'Original Post Screenshots'
                                            }}
                                        </h4>
                                        <p
                                            class="text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                isBn
                                                    ? 'যে পোস্ট বা সংবাদের প্রেক্ষাপটে মন্তব্যটি করা হয়েছে'
                                                    : 'Original news report or post being commented on'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="postFiles.length > 0"
                                        class="rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/20 dark:text-blue-300"
                                    >
                                        {{ postFiles.length }}
                                        {{ isBn ? 'টি ছবি' : 'photo(s)' }}
                                    </span>
                                    <span
                                        class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:border-blue-800/40 dark:bg-blue-950/60 dark:text-blue-400"
                                    >
                                        {{
                                            isBn
                                                ? 'প্রেক্ষাপট প্রমাণ'
                                                : 'Post Context'
                                        }}
                                    </span>
                                </div>
                            </div>

                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-4 text-center transition-colors hover:border-blue-500/50 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950/40 dark:hover:bg-slate-900/60"
                            >
                                <div
                                    class="text-xs font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        isBn
                                            ? '+ মূল পোস্টের স্ক্রিনশট যুক্ত করুন (একাধিক নির্বাচনযোগ্য)'
                                            : '+ Add Original Post Screenshots (Select Multiple)'
                                    }}
                                </div>
                                <div
                                    class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500"
                                >
                                    {{
                                        isBn
                                            ? 'PNG, JPG, WebP समर्थিত'
                                            : 'PNG, JPG, WebP supported'
                                    }}
                                </div>
                                <input
                                    type="file"
                                    accept="image/png, image/jpeg, image/webp"
                                    multiple
                                    class="hidden"
                                    @change="
                                        handleSectionFileSelect($event, 'post')
                                    "
                                />
                            </label>

                            <div
                                v-if="postFiles.length > 0"
                                class="grid grid-cols-2 gap-3 sm:grid-cols-4"
                            >
                                <div
                                    v-for="(item, idx) in postFiles"
                                    :key="idx"
                                    class="group relative overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <img
                                        :src="item.previewUrl"
                                        class="h-24 w-full object-cover"
                                        alt="Post preview"
                                    />
                                    <div
                                        class="flex items-center justify-between p-1.5 text-[10px] text-slate-500 dark:text-slate-400"
                                    >
                                        <span>{{ item.sizeKb }} KB</span>
                                        <button
                                            type="button"
                                            @click="
                                                removeSectionFile('post', idx)
                                            "
                                            class="inline-flex items-center gap-0.5 font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                                        >
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                            <span>{{
                                                isBn ? 'বাদ' : 'Del'
                                            }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: COMMENTER PROFILE SCREENSHOTS -->
                        <div
                            class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-purple-200 bg-purple-100 text-purple-600 dark:border-purple-500/20 dark:bg-purple-500/10 dark:text-purple-400"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                isBn
                                                    ? 'মন্তব্যকারীর প্রোফাইলের স্ক্রিনশট'
                                                    : 'Commenter Profile Screenshots'
                                            }}
                                        </h4>
                                        <p
                                            class="text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                isBn
                                                    ? 'মন্তব্যকারীর ফেসবুক প্রোফাইল পেজ, নাম, বায়ো বা শনাক্তকারী তথ্যের স্ক্রিনশট'
                                                    : 'Screenshots showing the commenter profile page, bio, and details'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="profileFiles.length > 0"
                                        class="rounded-full border border-purple-200 bg-purple-100 px-2.5 py-0.5 text-[11px] font-semibold text-purple-700 dark:border-purple-500/30 dark:bg-purple-500/20 dark:text-purple-300"
                                    >
                                        {{ profileFiles.length }}
                                        {{ isBn ? 'টি ছবি' : 'photo(s)' }}
                                    </span>
                                    <span
                                        class="rounded-full border border-purple-200 bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:border-purple-800/40 dark:bg-purple-950/60 dark:text-purple-400"
                                    >
                                        {{
                                            isBn
                                                ? 'প্রোফাইল তথ্য'
                                                : 'Profile Bio'
                                        }}
                                    </span>
                                </div>
                            </div>

                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-4 text-center transition-colors hover:border-purple-500/50 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950/40 dark:hover:bg-slate-900/60"
                            >
                                <div
                                    class="text-xs font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        isBn
                                            ? '+ প্রোফাইলের স্ক্রিনশট যুক্ত করুন (একাধিক নির্বাচনযোগ্য)'
                                            : '+ Add Profile Screenshots (Select Multiple)'
                                    }}
                                </div>
                                <div
                                    class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500"
                                >
                                    {{
                                        isBn
                                            ? 'PNG, JPG, WebP समर्थিত'
                                            : 'PNG, JPG, WebP supported'
                                    }}
                                </div>
                                <input
                                    type="file"
                                    accept="image/png, image/jpeg, image/webp"
                                    multiple
                                    class="hidden"
                                    @change="
                                        handleSectionFileSelect(
                                            $event,
                                            'profile',
                                        )
                                    "
                                />
                            </label>

                            <div
                                v-if="profileFiles.length > 0"
                                class="grid grid-cols-2 gap-3 sm:grid-cols-4"
                            >
                                <div
                                    v-for="(item, idx) in profileFiles"
                                    :key="idx"
                                    class="group relative overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <img
                                        :src="item.previewUrl"
                                        class="h-24 w-full object-cover"
                                        alt="Profile preview"
                                    />
                                    <div
                                        class="flex items-center justify-between p-1.5 text-[10px] text-slate-500 dark:text-slate-400"
                                    >
                                        <span>{{ item.sizeKb }} KB</span>
                                        <button
                                            type="button"
                                            @click="
                                                removeSectionFile(
                                                    'profile',
                                                    idx,
                                                )
                                            "
                                            class="inline-flex items-center gap-0.5 font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                                        >
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                            <span>{{
                                                isBn ? 'বাদ' : 'Del'
                                            }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: OTHER SUPPORTING SCREENSHOTS -->
                        <div
                            class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-300 bg-slate-100 text-slate-600 dark:border-slate-500/20 dark:bg-slate-500/10 dark:text-slate-400"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                isBn
                                                    ? 'অন্যান্য সহায়ক প্রমাণাদি'
                                                    : 'Other Supporting Screenshots'
                                            }}
                                        </h4>
                                        <p
                                            class="text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                isBn
                                                    ? 'ইনবক্স মেসেজ, সংশ্লিষ্ট শেয়ার বা অতিরিক্ত প্রাসঙ্গিক স্ক্রিনশট'
                                                    : 'Inbox messages, shares, or any other relevant evidence'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="otherFiles.length > 0"
                                        class="rounded-full border border-slate-300 bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700 dark:border-slate-500/30 dark:bg-slate-500/20 dark:text-slate-300"
                                    >
                                        {{ otherFiles.length }}
                                        {{ isBn ? 'টি ছবি' : 'photo(s)' }}
                                    </span>
                                    <span
                                        class="rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:border-slate-700/60 dark:bg-slate-800/80 dark:text-slate-400"
                                    >
                                        {{
                                            isBn ? 'ঐচ্ছিক প্রমাণ' : 'Optional'
                                        }}
                                    </span>
                                </div>
                            </div>

                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-4 text-center transition-colors hover:border-slate-400 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950/40 dark:hover:bg-slate-900/60"
                            >
                                <div
                                    class="text-xs font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        isBn
                                            ? '+ অন্যান্য স্ক্রিনশট যুক্ত করুন (একাধিক নির্বাচনযোগ্য)'
                                            : '+ Add Other Screenshots (Select Multiple)'
                                    }}
                                </div>
                                <div
                                    class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500"
                                >
                                    {{
                                        isBn
                                            ? 'PNG, JPG, WebP समर्थিত'
                                            : 'PNG, JPG, WebP supported'
                                    }}
                                </div>
                                <input
                                    type="file"
                                    accept="image/png, image/jpeg, image/webp"
                                    multiple
                                    class="hidden"
                                    @change="
                                        handleSectionFileSelect($event, 'other')
                                    "
                                />
                            </label>

                            <div
                                v-if="otherFiles.length > 0"
                                class="grid grid-cols-2 gap-3 sm:grid-cols-4"
                            >
                                <div
                                    v-for="(item, idx) in otherFiles"
                                    :key="idx"
                                    class="group relative overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <img
                                        :src="item.previewUrl"
                                        class="h-24 w-full object-cover"
                                        alt="Other preview"
                                    />
                                    <div
                                        class="flex items-center justify-between p-1.5 text-[10px] text-slate-500 dark:text-slate-400"
                                    >
                                        <span>{{ item.sizeKb }} KB</span>
                                        <button
                                            type="button"
                                            @click="
                                                removeSectionFile('other', idx)
                                            "
                                            class="inline-flex items-center gap-0.5 font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                                        >
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                            <span>{{
                                                isBn ? 'বাদ' : 'Del'
                                            }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1 Summary & Actions -->
                        <div
                            class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 pt-4 dark:border-slate-800"
                        >
                            <div
                                class="text-xs text-slate-600 dark:text-slate-400"
                            >
                                <span
                                    v-if="totalFilesCount > 0"
                                    class="font-semibold text-rose-600 dark:text-rose-400"
                                >
                                    {{
                                        isBn
                                            ? `সর্বমোট ${totalFilesCount}টি প্রমাণ ফাইল সংযুক্ত করা হয়েছে`
                                            : `Total ${totalFilesCount} evidence file(s) attached`
                                    }}
                                </span>
                                <span
                                    v-else
                                    class="text-amber-600 dark:text-amber-400/80"
                                >
                                    {{
                                        isBn
                                            ? 'এগিয়ে যেতে অন্তত যেকোনো একটি সেকশনে স্ক্রিনশট দিন'
                                            : 'Please attach at least one screenshot in any section to proceed'
                                    }}
                                </span>
                            </div>

                            <button
                                type="button"
                                :disabled="!canGoNextFromStep1"
                                @click="currentStep = 2"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-rose-500 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <span>{{
                                    isBn
                                        ? 'পরবর্তী ধাপ (ফেসবুক লিঙ্ক)'
                                        : 'Next: Facebook URLs'
                                }}</span>
                                <svg
                                    class="h-4 w-4"
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
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Facebook Profile and Comment URLs -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '২. মন্তব্যকারীর ফেসবুক প্রোফাইল ও মন্তব্যের লিঙ্ক'
                                        : '2. Facebook Profile & Comment Links'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যকারীর ফেসবুক প্রোফাইল লিঙ্কটি দিন। এটি সংখ্যাসূচক আইডি বা ইউজারনেম উভয় রূপেই গ্রহণযোগ্য।'
                                        : 'Enter the commenter Facebook profile link. Numeric IDs and vanity usernames are both supported.'
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    isBn
                                        ? 'ফেসবুক প্রোফাইল URL *'
                                        : 'Facebook Profile URL *'
                                }}
                            </label>
                            <input
                                v-model="form.submitted_profile_url"
                                type="url"
                                required
                                placeholder="https://www.facebook.com/username অথবা profile.php?id=..."
                                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    isBn
                                        ? 'নির্দিষ্ট মন্তব্যের লিঙ্ক (যদি থাকে)'
                                        : 'Direct Comment URL (Optional)'
                                }}
                            </label>
                            <input
                                v-model="form.submitted_comment_url"
                                type="url"
                                placeholder="https://www.facebook.com/.../posts/.../?comment_id=..."
                                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
                            />
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{
                                    isBn
                                        ? 'মন্তব্যের টাইমস্ট্যাম্পে রাইট-ক্লিক করে কপি লিঙ্ক নেওয়া যায়।'
                                        : 'Right-click the comment timestamp to copy direct link.'
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যের মূল বক্তব্য / টেক্সট'
                                        : 'Comment Text (Verbatim)'
                                }}
                            </label>
                            <textarea
                                v-model="form.comment_text"
                                rows="3"
                                placeholder="মন্তব্যে যা লেখা হয়েছিল হুবহু এখানে লিখুন..."
                                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
                            ></textarea>
                        </div>

                        <!-- Step 2 Actions -->
                        <div class="flex justify-between pt-4">
                            <button
                                type="button"
                                @click="currentStep = 1"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
                                    />
                                </svg>
                                <span>{{ isBn ? 'পূর্ববর্তী' : 'Back' }}</span>
                            </button>
                            <button
                                type="button"
                                :disabled="!canGoNextFromStep2"
                                @click="currentStep = 3"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-rose-500 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <span>{{
                                    isBn
                                        ? 'পরবর্তী ধাপ (শ্রেণিবিভাগ)'
                                        : 'Next: Details'
                                }}</span>
                                <svg
                                    class="h-4 w-4"
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
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: Classification & Linking -->
                    <div v-if="currentStep === 3" class="space-y-6">
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '৩. মন্তব্যের শ্রেণিবিভাগ'
                                        : '3. Category & Classification'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যটি কোন ধরনের সহিংসতার সমর্থনে তা নির্বাচন করুন।'
                                        : 'Classify the nature of the violation.'
                                }}
                            </p>
                        </div>

                        <!-- Category Selector -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যের ধরন / ক্যাটাগরি'
                                        : 'Violation Category'
                                }}
                            </label>
                            <div
                                class="mt-2 grid grid-cols-1 gap-2.5 sm:grid-cols-2"
                            >
                                <label
                                    v-for="cat in categories"
                                    :key="cat.value"
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors"
                                    :class="
                                        form.category === cat.value
                                            ? 'border-rose-500 bg-rose-50 text-rose-900 dark:border-rose-500 dark:bg-rose-950/30 dark:text-white'
                                            : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-300 dark:hover:bg-slate-800'
                                    "
                                >
                                    <input
                                        type="radio"
                                        name="category"
                                        :value="cat.value"
                                        v-model="form.category"
                                        class="text-rose-600 focus:ring-rose-500"
                                    />
                                    <span class="text-xs font-medium">{{
                                        isBn ? cat.label_bn : cat.label_en
                                    }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Reporter Email (Optional) -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    isBn
                                        ? 'আপনার ইমেইল (ঐচ্ছিক: পোর্টাল লগইন ও ট্র্যাকিংয়ের জন্য)'
                                        : 'Your Email (Optional: for portal login & status tracking)'
                                }}
                            </label>
                            <input
                                v-model="form.reporter_email"
                                type="email"
                                placeholder="name@example.com"
                                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
                            />
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{
                                    isBn
                                        ? 'ইমেইল দিলে পরবর্তীতে ম্যাজিক লিঙ্কের মাধ্যমে আপনার সমস্ত রিপোর্ট একসাথে দেখতে পারবেন। ইমেইল সম্পূর্ণ গোপন রাখা হয়।'
                                        : 'Providing an email allows you to track all your submissions together via magic-link. Kept private.'
                                }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-between pt-4">
                            <button
                                type="button"
                                @click="currentStep = 2"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
                                    />
                                </svg>
                                <span>{{ isBn ? 'পূর্ববর্তী' : 'Back' }}</span>
                            </button>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-8 py-3 text-sm font-bold text-white shadow-lg transition-colors hover:bg-rose-500 disabled:opacity-50"
                            >
                                <span v-if="isSubmitting">{{
                                    isBn ? 'জমা হচ্ছে...' : 'Submitting...'
                                }}</span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-2"
                                >
                                    <span>{{
                                        isBn
                                            ? 'রিপোর্ট চূড়ান্তভাবে জমা দিন'
                                            : 'Submit Final Report'
                                    }}</span>
                                    <svg
                                        class="h-4 w-4"
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
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
