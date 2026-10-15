<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-slate-50 pt-8 sm:justify-center sm:pt-0 dark:bg-slate-950"
    >
        <div>
            <Link href="/" class="group flex flex-col items-center gap-2">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-rose-200/80 bg-rose-50/80 text-rose-600 shadow-sm backdrop-blur-md transition-transform group-hover:scale-105 dark:border-rose-900/60 dark:bg-rose-950/60 dark:text-rose-400">
                    <ApplicationLogo class="h-8 w-8 stroke-current" />
                </div>
                <div class="flex items-center gap-1.5 text-base font-extrabold tracking-tight text-slate-900 dark:text-white">
                    <span>NO MORE SILENCE</span>
                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-600 dark:bg-slate-800 dark:text-slate-400">Archive</span>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400">Public Record Archive</span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white px-6 py-6 shadow-xl sm:max-w-md dark:border-slate-800 dark:bg-slate-900"
        >
            <slot />
        </div>
    </div>
</template>
