<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

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

interface IncidentItem {
    id: number;
    title_bn: string;
    title_en: string;
    district_id: number | null;
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
    incidents: IncidentItem[];
    districts: DistrictItem[];
    categories: CategoryItem[];
    turnstileSiteKey?: string | null;
    preselectedIncidentId?: string | number | null;
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
    incident_id: props.preselectedIncidentId
        ? String(props.preselectedIncidentId)
        : '',
    new_incident_title: '',
    new_incident_district_id: '',
    reporter_email: '',
    cf_turnstile_response: '',
});

// File uploads
interface SelectedFile {
    file: File;
    previewUrl: string;
    sizeKb: number;
}
const selectedFiles = ref<SelectedFile[]>([]);
const isCompressing = ref(false);
const copiedCode = ref(false);

const canGoNextFromStep1 = computed(() => {
    return selectedFiles.value.length > 0;
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

async function handleFileSelect(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;

    isCompressing.value = true;
    try {
        for (let i = 0; i < input.files.length; i++) {
            const originalFile = input.files[i];
            const compressed = await compressImageFile(originalFile);
            const previewUrl = URL.createObjectURL(compressed);
            selectedFiles.value.push({
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

function removeFile(index: number) {
    const item = selectedFiles.value[index];
    if (item) {
        URL.revokeObjectURL(item.previewUrl);
    }
    selectedFiles.value.splice(index, 1);
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
        if (form.value.incident_id) {
            formData.append('incident_id', form.value.incident_id);
        }
        if (form.value.new_incident_title) {
            formData.append(
                'new_incident_title',
                form.value.new_incident_title,
            );
        }
        if (form.value.new_incident_district_id) {
            formData.append(
                'new_incident_district_id',
                form.value.new_incident_district_id,
            );
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

        // Attach compressed files
        selectedFiles.value.forEach((item) => {
            formData.append('evidence[]', item.file);
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
                        selectedFiles.value.push({
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
                class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-950/30 p-4 text-xs text-emerald-200"
            >
                <div class="flex items-start gap-3">
                    <span class="text-base text-emerald-400">📱</span>
                    <div class="space-y-1">
                        <strong class="font-semibold text-emerald-300">
                            {{
                                isBn
                                    ? 'মোবাইল বা ফেসবুক শেয়ার থেকে কনটেন্ট যুক্ত হয়েছে:'
                                    : 'Imported from Mobile Share:'
                            }}
                        </strong>
                        <p class="leading-relaxed text-emerald-200/90">
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
                    class="inline-flex items-center gap-1.5 rounded-full border border-rose-500/30 bg-rose-950/40 px-3 py-1 text-xs font-semibold text-rose-300"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    {{
                        isBn ? 'নাগরিক প্রমাণ ভান্ডার' : 'Civic Evidence Intake'
                    }}
                </span>
                <h1
                    class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'সহিংসতার সমর্থনে মন্তব্যের রিপোর্ট করুন'
                            : 'Submit Evidence of Violence Apologia'
                    }}
                </h1>
                <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-400">
                    {{
                        isBn
                            ? 'ধর্ষণ বা যৌন সহিংসতার সাফাই গাওয়া, ভিকটিম-ব্লেমিং বা সহিংসতায় উস্কানিমূলক মন্তব্যের ডিজিটাল স্ক্রিনশট ও লিঙ্ক নির্ভুলভাবে জমা দিন।'
                            : 'Submit verifiable screenshots and URLs of public comments condoning rape, blaming victims, or inciting sexual violence.'
                    }}
                </p>
            </div>

            <!-- Legal and Privacy Shield Notice -->
            <div
                class="mb-8 rounded-xl border border-amber-500/30 bg-amber-950/20 p-4 text-xs text-amber-200"
            >
                <div class="flex items-start gap-3">
                    <span class="text-base text-amber-400">⚠️</span>
                    <div class="space-y-1">
                        <strong class="font-semibold text-amber-300">
                            {{
                                isBn
                                    ? 'ভিকটিম সুরক্ষা ও আইনগত সতর্কতা:'
                                    : 'Victim Protection & Legal Notice:'
                            }}
                        </strong>
                        <p class="leading-relaxed text-amber-200/90">
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
                class="rounded-2xl border border-emerald-500/40 bg-emerald-950/20 p-8 text-center shadow-xl"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/20 text-2xl text-emerald-400"
                >
                    ✓
                </div>

                <div v-if="successData.already_reported">
                    <h2 class="mt-4 text-2xl font-bold text-white">
                        {{
                            isBn
                                ? 'রেকর্ডটি পূর্বেই জমা হয়েছে'
                                : 'Already Documented'
                        }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-300">
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
                    <h2 class="mt-4 text-2xl font-bold text-white">
                        {{
                            isBn
                                ? 'রিপোর্টটি সফলভাবে জমা হয়েছে!'
                                : 'Report Successfully Submitted!'
                        }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-300">
                        {{ successData.message }}
                    </p>

                    <!-- Tracking code box -->
                    <div
                        class="mx-auto mt-6 max-w-md rounded-xl border border-emerald-500/30 bg-slate-900/90 p-5"
                    >
                        <div
                            class="text-xs uppercase tracking-wider text-slate-400"
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
                                class="font-mono text-2xl font-black tracking-widest text-emerald-400"
                            >
                                {{ successData.tracking_code }}
                            </span>
                            <button
                                type="button"
                                @click="copyTrackingCode"
                                class="rounded border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-200 transition-colors hover:bg-slate-700 hover:text-white"
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
                        <p class="mt-3 text-[11px] text-slate-400">
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
                                selectedFiles = [];
                            "
                            class="rounded-lg border border-slate-700 bg-slate-800 px-6 py-2.5 text-sm font-semibold text-slate-200 hover:bg-slate-700"
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
                class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-xl sm:p-8"
            >
                <!-- Stepper Progress Bar -->
                <div class="mb-8">
                    <div
                        class="flex items-center justify-between text-xs font-semibold text-slate-400"
                    >
                        <div
                            class="flex items-center gap-2"
                            :class="{ 'text-rose-400': currentStep >= 1 }"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full border text-xs"
                                :class="
                                    currentStep >= 1
                                        ? 'border-rose-500 bg-rose-500/20 text-rose-300'
                                        : 'border-slate-700 text-slate-500'
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
                                    : 'bg-slate-800'
                            "
                        ></div>
                        <div
                            class="flex items-center gap-2"
                            :class="{ 'text-rose-400': currentStep >= 2 }"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full border text-xs"
                                :class="
                                    currentStep >= 2
                                        ? 'border-rose-500 bg-rose-500/20 text-rose-300'
                                        : 'border-slate-700 text-slate-500'
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
                                    : 'bg-slate-800'
                            "
                        ></div>
                        <div
                            class="flex items-center gap-2"
                            :class="{ 'text-rose-400': currentStep >= 3 }"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full border text-xs"
                                :class="
                                    currentStep >= 3
                                        ? 'border-rose-500 bg-rose-500/20 text-rose-300'
                                        : 'border-slate-700 text-slate-500'
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
                    class="mb-6 rounded-lg border border-rose-500/40 bg-rose-950/30 p-4 text-xs text-rose-300"
                >
                    {{ errorMessage }}
                </div>

                <form @submit.prevent="handleSubmit">
                    <!-- STEP 1: Evidence Screenshots -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{
                                    isBn
                                        ? '১. মন্তব্যের স্ক্রিনশট আপলোড করুন'
                                        : '1. Upload Screenshot Evidence'
                                }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-400">
                                {{
                                    isBn
                                        ? 'মন্তব্যকারীর নাম, প্রোফাইল ছবি এবং লিখিত মন্তব্য স্পষ্টভাবে দৃশ্যমান রয়েছে এমন স্ক্রিনশট দিন (সর্বোচ্চ ৫টি)।'
                                        : 'Upload clear screenshots showing the commenter profile name, picture, and public comment text (max 5).'
                                }}
                            </p>
                        </div>

                        <!-- Dropzone -->
                        <label
                            class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-700 bg-slate-800/40 p-8 text-center transition-colors hover:border-rose-500/50 hover:bg-slate-800/80"
                        >
                            <svg
                                class="h-10 w-10 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <div
                                class="mt-3 text-sm font-semibold text-slate-200"
                            >
                                {{
                                    isBn
                                        ? 'স্ক্রিনশট নির্বাচন করুন বা টেনে আনুন'
                                        : 'Click to select or drag screenshots'
                                }}
                            </div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{
                                    isBn
                                        ? 'PNG, JPG, WebP সমর্থিত — ক্লায়েন্ট-সাইডে স্বয়ংক্রিয় অপ্টিমাইজড'
                                        : 'PNG, JPG, WebP supported — auto-optimized'
                                }}
                            </div>
                            <input
                                type="file"
                                accept="image/png, image/jpeg, image/webp"
                                multiple
                                class="hidden"
                                @change="handleFileSelect"
                            />
                        </label>

                        <div
                            v-if="isCompressing"
                            class="text-center text-xs text-rose-300"
                        >
                            {{
                                isBn
                                    ? 'ছবি অপ্টিমাইজ ও কম্প্রেস হচ্ছে...'
                                    : 'Optimizing and compressing images...'
                            }}
                        </div>

                        <!-- Uploaded previews -->
                        <div
                            v-if="selectedFiles.length > 0"
                            class="grid grid-cols-2 gap-4 sm:grid-cols-3"
                        >
                            <div
                                v-for="(item, idx) in selectedFiles"
                                :key="idx"
                                class="group relative overflow-hidden rounded-lg border border-slate-700 bg-slate-800"
                            >
                                <img
                                    :src="item.previewUrl"
                                    class="h-32 w-full object-cover"
                                    alt="Preview"
                                />
                                <div
                                    class="flex items-center justify-between p-2 text-[10px] text-slate-400"
                                >
                                    <span>{{ item.sizeKb }} KB</span>
                                    <button
                                        type="button"
                                        @click="removeFile(idx)"
                                        class="font-bold text-rose-400 hover:text-rose-300"
                                    >
                                        ✕ {{ isBn ? 'মুছুন' : 'Remove' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1 Actions -->
                        <div class="flex justify-end pt-4">
                            <button
                                type="button"
                                :disabled="!canGoNextFromStep1"
                                @click="currentStep = 2"
                                class="rounded-lg bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-rose-500 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                {{
                                    isBn
                                        ? 'পরবর্তী ধাপ (ফেসবুক লিঙ্ক) →'
                                        : 'Next: Facebook URLs →'
                                }}
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Facebook Profile and Comment URLs -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{
                                    isBn
                                        ? '২. মন্তব্যকারীর ফেসবুক প্রোফাইল ও মন্তব্যের লিঙ্ক'
                                        : '2. Facebook Profile & Comment Links'
                                }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-400">
                                {{
                                    isBn
                                        ? 'মন্তব্যকারীর ফেসবুক প্রোফাইল লিঙ্কটি দিন। এটি সংখ্যাসূচক আইডি বা ইউজারনেম উভয় রূপেই গ্রহণযোগ্য।'
                                        : 'Enter the commenter Facebook profile link. Numeric IDs and vanity usernames are both supported.'
                                }}
                            </p>
                        </div>

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
                                v-model="form.submitted_profile_url"
                                type="url"
                                required
                                placeholder="https://www.facebook.com/username অথবা profile.php?id=..."
                                class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-300"
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
                                class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
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
                                class="block text-xs font-semibold text-slate-300"
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
                                class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                            ></textarea>
                        </div>

                        <!-- Step 2 Actions -->
                        <div class="flex justify-between pt-4">
                            <button
                                type="button"
                                @click="currentStep = 1"
                                class="rounded-lg border border-slate-700 px-5 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800"
                            >
                                {{ isBn ? '← পূর্ববর্তী' : '← Back' }}
                            </button>
                            <button
                                type="button"
                                :disabled="!canGoNextFromStep2"
                                @click="currentStep = 3"
                                class="rounded-lg bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-rose-500 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                {{
                                    isBn
                                        ? 'পরবর্তী ধাপ (শ্রেণিবিভাগ) →'
                                        : 'Next: Details →'
                                }}
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: Classification & Linking -->
                    <div v-if="currentStep === 3" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{
                                    isBn
                                        ? '৩. মন্তব্যের শ্রেণিবিভাগ ও ঘটনা সংযুক্তি'
                                        : '3. Category & Incident Context'
                                }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-400">
                                {{
                                    isBn
                                        ? 'মন্তব্যটি কোন ধরনের সহিংসতার সমর্থনে এবং এটি নির্দিষ্ট কোনো ঘটনার সাথে সম্পর্কিত কি না তা নির্বাচন করুন।'
                                        : 'Classify the nature of violation and optionally associate it with an existing incident.'
                                }}
                            </p>
                        </div>

                        <!-- Category Selector -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-300"
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
                                            ? 'border-rose-500 bg-rose-950/30 text-white'
                                            : 'border-slate-700 bg-slate-800/40 text-slate-300 hover:bg-slate-800'
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

                        <!-- Incident Linking -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-300"
                            >
                                {{
                                    isBn
                                        ? 'সম্পর্কিত ঘটনা নির্বাচন করুন (যদি থাকে)'
                                        : 'Link to Incident (Optional)'
                                }}
                            </label>
                            <select
                                v-model="form.incident_id"
                                class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                            >
                                <option value="">
                                    {{
                                        isBn
                                            ? '-- কোনো নির্দিষ্ট ঘটনা নয় / সাধারণ সহিংসতা --'
                                            : '-- No specific incident / General --'
                                    }}
                                </option>
                                <option
                                    v-for="inc in incidents"
                                    :key="inc.id"
                                    :value="String(inc.id)"
                                >
                                    {{ isBn ? inc.title_bn : inc.title_en }}
                                </option>
                            </select>
                        </div>

                        <!-- Or New Incident -->
                        <div
                            v-if="!form.incident_id"
                            class="space-y-3 rounded-xl border border-slate-800 bg-slate-900/60 p-4"
                        >
                            <div class="text-xs font-semibold text-slate-400">
                                {{
                                    isBn
                                        ? 'অথবা নতুন ঘটনার নাম লিখুন (ঐচ্ছিক)'
                                        : 'Or Specify a New Incident (Optional)'
                                }}
                            </div>
                            <input
                                v-model="form.new_incident_title"
                                type="text"
                                placeholder="যেমন: মাগুরা ধর্ষণ মামলা ২০২৬"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:border-rose-500"
                            />
                            <select
                                v-model="form.new_incident_district_id"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2 text-sm text-white focus:border-rose-500"
                            >
                                <option value="">
                                    {{
                                        isBn
                                            ? '-- জেলা নির্বাচন করুন --'
                                            : '-- Select District --'
                                    }}
                                </option>
                                <option
                                    v-for="dist in districts"
                                    :key="dist.id"
                                    :value="String(dist.id)"
                                >
                                    {{ isBn ? dist.name_bn : dist.name_en }}
                                </option>
                            </select>
                        </div>

                        <!-- Reporter Email (Optional) -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-300"
                            >
                                {{
                                    isBn
                                        ? 'আপনার ইমেইল (ঐচ্ছিক — ট্র্যাকিং ও রেপুটেশনের জন্য)'
                                        : 'Your Email (Optional — for portal login & tracking)'
                                }}
                            </label>
                            <input
                                v-model="form.reporter_email"
                                type="email"
                                placeholder="name@example.com"
                                class="mt-1.5 w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-rose-500"
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
                                class="rounded-lg border border-slate-700 px-5 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800"
                            >
                                {{ isBn ? '← পূর্ববর্তী' : '← Back' }}
                            </button>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="rounded-lg bg-rose-600 px-8 py-3 text-sm font-bold text-white shadow-lg transition-colors hover:bg-rose-500 disabled:opacity-50"
                            >
                                <span v-if="isSubmitting">{{
                                    isBn ? 'জমা হচ্ছে...' : 'Submitting...'
                                }}</span>
                                <span v-else>{{
                                    isBn
                                        ? 'রিপোর্ট চূড়ান্তভাবে জমা দিন ✓'
                                        : 'Submit Final Report ✓'
                                }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
