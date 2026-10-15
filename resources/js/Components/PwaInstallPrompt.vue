<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import { onMounted, ref } from 'vue';

const { isBn } = useLanguage();

const showPrompt = ref(false);
const deferredPrompt = ref<any>(null);
const DISMISS_KEY = 'nms_pwa_install_dismissed';

function checkDismissed(): boolean {
    if (typeof window === 'undefined') return true;
    const dismissedAt = localStorage.getItem(DISMISS_KEY);
    if (!dismissedAt) return false;
    const diffDays =
        (Date.now() - parseInt(dismissedAt, 10)) / (1000 * 60 * 60 * 24);
    return diffDays < 7; // Remember dismissal for 7 days
}

function dismissPrompt() {
    showPrompt.value = false;
    localStorage.setItem(DISMISS_KEY, Date.now().toString());
}

async function triggerInstall() {
    if (!deferredPrompt.value) return;

    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') {
        showPrompt.value = false;
    }
    deferredPrompt.value = null;
}

onMounted(() => {
    // Check if already in standalone / installed mode
    if (
        window.matchMedia('(display-mode: standalone)').matches ||
        (window.navigator as any).standalone === true
    ) {
        return;
    }

    if (checkDismissed()) {
        return;
    }

    window.addEventListener('beforeinstallprompt', (e: Event) => {
        e.preventDefault();
        deferredPrompt.value = e;
        showPrompt.value = true;
    });

    window.addEventListener('appinstalled', () => {
        showPrompt.value = false;
        deferredPrompt.value = null;
    });
});
</script>

<template>
    <div
        v-if="showPrompt"
        class="fixed bottom-4 left-4 right-4 z-50 mx-auto max-w-lg rounded-2xl border border-rose-500/40 bg-slate-900/95 p-4 shadow-2xl backdrop-blur-md transition-all sm:bottom-6 sm:right-6 sm:mx-0"
    >
        <div class="flex items-center gap-3.5">
            <img
                src="/icons/icon-192x192.png"
                alt="App Icon"
                class="h-12 w-12 shrink-0 rounded-xl border border-slate-700 bg-slate-800 object-cover p-1 shadow-sm"
            />

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h4 class="truncate text-sm font-bold text-white">
                        No More Silence
                    </h4>
                    <span
                        class="rounded border border-rose-500/40 bg-rose-600/20 px-1.5 py-0.5 text-[9px] font-semibold uppercase text-rose-300"
                        >PWA</span
                    >
                </div>
                <p class="mt-0.5 line-clamp-2 text-xs text-slate-300">
                    {{
                        isBn
                            ? 'ফেসবুক থেকে সরাসরি শেয়ার করে দ্রুত রিপোর্ট ও স্ক্রিনশট পাঠাতে অ্যাপটি ফোনে ইনস্টল করুন।'
                            : 'Install on your device for fast share-target reporting directly from Facebook.'
                    }}
                </p>
            </div>

            <button
                type="button"
                @click="dismissPrompt"
                class="self-start rounded p-1 text-slate-400 transition-colors hover:text-white"
                aria-label="Dismiss"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <div
            class="mt-3.5 flex items-center justify-end gap-2.5 border-t border-slate-800/80 pt-2"
        >
            <button
                type="button"
                @click="dismissPrompt"
                class="px-3 py-1.5 text-xs font-medium text-slate-400 transition-colors hover:text-slate-200"
            >
                {{ isBn ? 'পরে' : 'Not Now' }}
            </button>
            <button
                type="button"
                @click="triggerInstall"
                class="rounded-lg bg-rose-600 px-4 py-1.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-rose-500"
            >
                {{ isBn ? 'ইনস্টল করুন' : 'Install App' }}
            </button>
        </div>
    </div>
</template>
