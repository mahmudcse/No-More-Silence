<script setup lang="ts">
import PwaInstallPrompt from '@/Components/PwaInstallPrompt.vue';
import { useLanguage } from '@/composables/useLanguage';
import { useTheme } from '@/composables/useTheme';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    title?: string;
}>();

const { isBn, toggleLocale } = useLanguage();
const { isDark, toggleTheme } = useTheme();
const mobileMenuOpen = ref(false);
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col bg-[#fbfbfd] font-sans text-[#1d1d1f] antialiased transition-colors duration-200 selection:bg-rose-500 selection:text-white dark:bg-[#000000] dark:text-[#f5f5f7]"
    >
        <Head
            :title="
                title
                    ? `${title} | No More Silence`
                    : isBn
                      ? 'No More Silence: সহিংসতার সমর্থনে মন্তব্যের স্বাধীন আর্কাইভ'
                      : 'No More Silence | Public Record Archive'
            "
        />

        <!-- Apple-style Ambient Aura Lighting (reveals frosted glass depth) -->
        <div
            class="pointer-events-none fixed inset-0 -z-10 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -top-40 left-1/2 h-[500px] w-[1000px] -translate-x-1/2 rounded-full bg-gradient-to-tr from-rose-200/30 via-slate-200/40 to-sky-200/25 blur-3xl dark:from-rose-950/20 dark:via-slate-900/30 dark:to-sky-950/15"
            ></div>
            <div
                class="absolute -left-40 top-[35%] h-[500px] w-[500px] rounded-full bg-gradient-to-br from-rose-100/30 to-transparent blur-3xl dark:from-rose-950/15"
            ></div>
            <div
                class="absolute -right-40 top-[60%] h-[600px] w-[600px] rounded-full bg-gradient-to-bl from-slate-200/40 via-sky-100/20 to-transparent blur-3xl dark:from-slate-900/30"
            ></div>
        </div>

        <!-- Emergency Helpline Top Ribbon -->
        <div
            class="border-b border-rose-200/50 bg-rose-50/50 px-4 py-1.5 text-center text-xs text-rose-950 backdrop-blur-md transition-colors dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200"
        >
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-6 gap-y-1"
            >
                <span class="font-medium text-rose-900 dark:text-rose-300">
                    {{
                        isBn ? 'জরুরি সহায়তা প্রয়োজন?' : 'Need Emergency Help?'
                    }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-rose-500 dark:bg-rose-400"
                    ></span>
                    {{ isBn ? 'জাতীয় জরুরি সেবা:' : 'National Emergency:' }}
                    <strong
                        class="font-semibold tracking-wider text-rose-950 dark:text-white"
                        >৯৯৯ (999)</strong
                    >
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-rose-500 dark:bg-rose-400"
                    ></span>
                    {{
                        isBn
                            ? 'নারী ও শিশু নির্যাতন প্রতিরোধ:'
                            : 'Women & Child Abuse Helpline:'
                    }}
                    <strong
                        class="font-semibold tracking-wider text-rose-950 dark:text-white"
                        >১০৯ (109)</strong
                    >
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-rose-500 dark:bg-rose-400"
                    ></span>
                    {{ isBn ? 'শিশু হেল্পলাইন:' : 'Child Helpline:' }}
                    <strong
                        class="font-semibold tracking-wider text-rose-950 dark:text-white"
                        >১০৯৮ (1098)</strong
                    >
                </span>
            </div>
        </div>

        <!-- Main Header (Apple Glass Navbar) -->
        <header
            class="sticky top-0 z-40 border-b border-black/[0.06] bg-white/[0.48] backdrop-blur-2xl saturate-180 transition-colors dark:border-white/[0.08] dark:bg-black/[0.50]"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <!-- Brand / Logo -->
                <Link href="/" class="group flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200/80 bg-rose-50/80 text-rose-600 shadow-sm backdrop-blur-md transition-all group-hover:scale-105 group-hover:border-rose-300 dark:border-rose-900/60 dark:bg-rose-950/60 dark:text-rose-400"
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
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>
                    </div>

                    <div>
                        <div
                            class="flex items-center gap-2 text-sm font-bold tracking-tight text-slate-900 sm:text-base dark:text-white"
                        >
                            <span>NO MORE SILENCE</span>
                            <span
                                class="rounded-full border border-black/[0.06] bg-black/[0.03] px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-600 backdrop-blur-md dark:border-white/[0.08] dark:bg-white/[0.06] dark:text-slate-400"
                                >Archive</span
                            >
                        </div>
                        <div
                            class="text-xs font-normal text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? 'সহিংসতার সমর্থনে মন্তব্যের স্বাধীন প্রমাণ'
                                    : 'Independent Public Record Archive'
                            }}
                        </div>
                    </div>
                </Link>

                <!-- Desktop Nav (Apple Pill Links) -->
                <nav
                    class="hidden items-center gap-0.5 text-[13px] font-medium md:flex lg:gap-1"
                >
                    <Link
                        href="/"
                        class="rounded-full px-3 py-1.5 text-slate-600 transition-all hover:bg-black/[0.04] hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white"
                    >
                        {{ isBn ? 'হোম' : 'Home' }}
                    </Link>
                    <Link
                        href="/search"
                        class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-slate-600 transition-all hover:bg-black/[0.04] hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white"
                    >
                        <svg
                            class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500"
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
                        {{ isBn ? 'অনুসন্ধান' : 'Search' }}
                    </Link>
                    <Link
                        href="/districts"
                        class="rounded-full px-3 py-1.5 text-slate-600 transition-all hover:bg-black/[0.04] hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white"
                    >
                        {{ isBn ? 'জেলা মানচিত্র' : 'Map' }}
                    </Link>
                    <Link
                        href="/methodology"
                        class="rounded-full px-3 py-1.5 text-slate-600 transition-all hover:bg-black/[0.04] hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white"
                    >
                        {{ isBn ? 'কার্যপদ্ধতি' : 'Methodology' }}
                    </Link>
                    <Link
                        href="/safety-resources"
                        class="rounded-full px-3 py-1.5 text-rose-700 transition-all hover:bg-rose-50 hover:text-rose-800 dark:text-rose-300 dark:hover:bg-white/[0.08] dark:hover:text-rose-200"
                    >
                        {{ isBn ? 'নিরাপত্তা' : 'Safety' }}
                    </Link>
                    <Link
                        href="/track"
                        class="rounded-full px-3 py-1.5 text-slate-600 transition-all hover:bg-black/[0.04] hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white"
                    >
                        {{ isBn ? 'ট্র্যাকিং' : 'Track' }}
                    </Link>
                    <Link
                        href="/feedback"
                        class="rounded-full px-3 py-1.5 text-slate-600 transition-all hover:bg-black/[0.04] hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white"
                    >
                        {{ isBn ? 'মতামত' : 'Feedback' }}
                    </Link>
                </nav>

                <!-- Right Actions: Submit CTA, Theme Switch, Language Switch & Mobile Toggle -->
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <Link
                        href="/submit"
                        class="hidden items-center gap-1.5 rounded-full bg-rose-600 px-3.5 py-1.5 text-xs font-medium text-white shadow-sm transition-all hover:bg-rose-500 active:scale-95 sm:inline-flex"
                    >
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        <span>{{
                            isBn ? 'রিপোর্ট জমা দিন' : 'Submit Report'
                        }}</span>
                    </Link>

                    <!-- Theme Toggle (Light / Dark) -->
                    <button
                        type="button"
                        @click="toggleTheme"
                        class="flex items-center gap-1.5 rounded-full border border-black/[0.08] bg-black/[0.03] px-3 py-1.5 text-xs font-medium text-slate-700 transition-all hover:bg-black/[0.06] active:scale-95 dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-slate-200 dark:hover:bg-white/[0.14]"
                        :title="
                            isDark
                                ? isBn
                                    ? 'লাইট মোড চালু করুন'
                                    : 'Switch to Light Mode'
                                : isBn
                                  ? 'ডার্ক মোড চালু করুন'
                                  : 'Switch to Dark Mode'
                        "
                        :aria-label="
                            isDark
                                ? 'Switch to Light Mode'
                                : 'Switch to Dark Mode'
                        "
                    >
                        <svg
                            v-if="isDark"
                            class="h-3.5 w-3.5 text-amber-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-3.5 w-3.5 text-slate-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                            />
                        </svg>
                        <span class="hidden sm:inline">{{
                            isDark
                                ? isBn
                                    ? 'লাইট'
                                    : 'Light'
                                : isBn
                                  ? 'ডার্ক'
                                  : 'Dark'
                        }}</span>
                    </button>

                    <!-- Language Toggle -->
                    <button
                        type="button"
                        @click="toggleLocale"
                        class="flex items-center gap-1.5 rounded-full border border-black/[0.08] bg-black/[0.03] px-3 py-1.5 text-xs font-medium text-slate-700 transition-all hover:bg-black/[0.06] active:scale-95 dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-slate-200 dark:hover:bg-white/[0.14]"
                        title="Change Language"
                    >
                        <svg
                            class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>{{ isBn ? 'English' : 'বাংলা' }}</span>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="rounded-full p-2 text-slate-600 hover:bg-black/[0.05] dark:text-slate-400 dark:hover:bg-white/[0.08] dark:hover:text-white md:hidden"
                        aria-label="Toggle menu"
                    >
                        <svg
                            v-if="!mobileMenuOpen"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer -->
            <div
                v-show="mobileMenuOpen"
                class="space-y-1 border-b border-black/[0.06] bg-white/70 px-4 pb-4 pt-2 backdrop-blur-2xl transition-colors dark:border-white/[0.08] dark:bg-black/70 md:hidden"
            >
                <Link
                    href="/"
                    class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-800 hover:bg-black/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'হোম' : 'Home' }}
                </Link>
                <Link
                    href="/search"
                    class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-800 hover:bg-black/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'অনুসন্ধান' : 'Search' }}
                </Link>
                <Link
                    href="/districts"
                    class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-800 hover:bg-black/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'জেলা মানচিত্র' : 'Map' }}
                </Link>
                <Link
                    href="/methodology"
                    class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-800 hover:bg-black/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'কার্যপদ্ধতি ও নীতি' : 'Methodology' }}
                </Link>
                <Link
                    href="/safety-resources"
                    class="block rounded-xl px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50/80 dark:text-rose-300 dark:hover:bg-white/[0.08]"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'নিরাপত্তা ও সহায়তা' : 'Safety' }}
                </Link>
                <Link
                    href="/about"
                    class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-800 hover:bg-black/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'আমাদের সম্পর্কে' : 'About' }}
                </Link>
                <div
                    class="space-y-1.5 border-t border-black/[0.06] pt-2 dark:border-white/[0.08]"
                >
                    <Link
                        href="/submit"
                        class="block rounded-full bg-rose-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-rose-500"
                        @click="mobileMenuOpen = false"
                    >
                        {{ isBn ? 'রিপোর্ট জমা দিন' : 'Submit Report' }}
                    </Link>
                    <Link
                        href="/track"
                        class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-black/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]"
                        @click="mobileMenuOpen = false"
                    >
                        {{ isBn ? 'ট্র্যাকিং' : 'Track Status' }}
                    </Link>
                    <Link
                        href="/appeals"
                        class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-black/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]"
                        @click="mobileMenuOpen = false"
                    >
                        {{ isBn ? 'আপিল আবেদন' : 'Submit Appeal' }}
                    </Link>
                    <Link
                        href="/my-submissions"
                        class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-black/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]"
                        @click="mobileMenuOpen = false"
                    >
                        {{ isBn ? 'আমার জমাকৃত রিপোর্ট' : 'My Submissions' }}
                    </Link>
                    <Link
                        href="/feedback"
                        class="block rounded-xl px-3 py-2 text-sm font-medium text-sky-600 hover:bg-sky-50 dark:text-sky-400 dark:hover:bg-white/[0.08]"
                        @click="mobileMenuOpen = false"
                    >
                        {{
                            isBn
                                ? 'মতামত বা ত্রুটি জানান'
                                : 'Feedback / Report Bug'
                        }}
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Dignified Footer -->
        <footer
            class="mt-16 border-t border-black/[0.06] bg-white/40 text-sm text-slate-600 backdrop-blur-xl transition-colors dark:border-white/[0.08] dark:bg-black/40 dark:text-slate-400"
        >
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="mb-10 grid grid-cols-1 gap-8 md:grid-cols-4">
                    <!-- Column 1: Mission & Principles -->
                    <div class="space-y-3 md:col-span-1">
                        <div
                            class="flex items-center gap-2 text-base font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            <span>NO MORE SILENCE</span>
                        </div>
                        <p
                            class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? 'সহিংসতার পক্ষে অবস্থান, ভিকটিম-ব্লেমিং এবং যৌন নির্যাতনের ন্যায্যতা প্রমাণের উদ্দেশ্যে সামাজিক মাধ্যমে প্রকাশিত সর্বজনীন মন্তব্যের স্বাধীন প্রমাণভিত্তিক ডিজিটাল সংরক্ষণাগার।'
                                    : 'An independent digital archive documenting verified public comments that justify sexual violence, blame victims, or incite hate.'
                            }}
                        </p>
                        <p
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? 'আমরা কখনোই ভিকটিম বা সংশ্লিষ্ট অপ্রাপ্তবয়স্কদের পরিচয় প্রকাশ করি না।'
                                    : 'Victim and minor identities are strictly protected and never published.'
                            }}
                        </p>
                    </div>

                    <!-- Column 2: Quick Links -->
                    <div class="space-y-2">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white"
                        >
                            {{
                                isBn
                                    ? 'নীতি ও পদ্ধতি'
                                    : 'Policies & Methodology'
                            }}
                        </h3>
                        <ul class="space-y-1.5 text-xs">
                            <li>
                                <Link
                                    href="/methodology"
                                    class="transition-colors hover:text-slate-900 dark:hover:text-white"
                                    >{{
                                        isBn
                                            ? '৮-দফা যাচাইকরণ মানদণ্ড'
                                            : '8-Point Verification'
                                    }}</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/about"
                                    class="transition-colors hover:text-slate-900 dark:hover:text-white"
                                    >{{
                                        isBn
                                            ? 'আমাদের উদ্দেশ্য ও পরিচালনা'
                                            : 'About & Governance'
                                    }}</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/terms"
                                    class="transition-colors hover:text-slate-900 dark:hover:text-white"
                                    >{{
                                        isBn
                                            ? 'ব্যবহারের শর্তাবলি (Terms)'
                                            : 'Terms of Service'
                                    }}</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/privacy"
                                    class="transition-colors hover:text-slate-900 dark:hover:text-white"
                                    >{{
                                        isBn
                                            ? 'গোপনীয়তা নীতি (Privacy)'
                                            : 'Privacy Policy'
                                    }}</Link
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Column 3: Emergency & Legal Aid -->
                    <div class="space-y-2">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white"
                        >
                            {{
                                isBn
                                    ? 'আইনি ও মানসিক সহায়তা'
                                    : 'Helplines & Legal Aid'
                            }}
                        </h3>
                        <ul class="space-y-1.5 text-xs">
                            <li>
                                <span
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                    >১০৯</span
                                >
                                <span class="text-slate-500 dark:text-slate-400"
                                    >:
                                    {{
                                        isBn
                                            ? 'নারী ও শিশু নির্যাতন হেল্পলাইন'
                                            : 'Women & Children Hotline'
                                    }}</span
                                >
                            </li>
                            <li>
                                <span
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                    >৯৯৯</span
                                >
                                <span class="text-slate-500 dark:text-slate-400"
                                    >:
                                    {{
                                        isBn
                                            ? 'জাতীয় জরুরি সেবা'
                                            : 'National Emergency'
                                    }}</span
                                >
                            </li>
                            <li>
                                <span
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                    >১০৯৮</span
                                >
                                <span class="text-slate-500 dark:text-slate-400"
                                    >:
                                    {{
                                        isBn
                                            ? 'শিশু হেল্পলাইন'
                                            : 'Child Helpline'
                                    }}</span
                                >
                            </li>
                            <li class="pt-1">
                                <Link
                                    href="/safety-resources"
                                    class="font-medium text-rose-600 transition-colors hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                                    >{{
                                        isBn
                                            ? 'সবগুলো সহায়তাকেন্দ্রের তালিকা'
                                            : 'All Support Resources'
                                    }}</Link
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Column 4: Ethics & Right of Reply -->
                    <div class="space-y-2">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white"
                        >
                            {{
                                isBn ? 'আপিল ও পোর্টাল' : 'Right of Reply & API'
                            }}
                        </h3>
                        <p
                            class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            {{
                                isBn
                                    ? 'আমরা ভুল সংশোধনে দায়বদ্ধ। কোনো রেকর্ডের ব্যাপারে আপত্তি থাকলে বা ভুল দাবি চিহ্নিত করতে চাইলে প্রমাণসহ আপিল করতে পারেন।'
                                    : 'We are committed to factual accuracy. If you believe any record contains an error or false attribution, you may submit an appeal.'
                            }}
                        </p>
                        <div class="space-y-1 pt-1 text-xs">
                            <div>
                                <Link
                                    href="/appeals"
                                    class="text-rose-600 transition-colors hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                                >
                                    {{
                                        isBn
                                            ? 'আপিল আবেদন ফরম'
                                            : 'Submit an Appeal'
                                    }}
                                </Link>
                            </div>

                            <div>
                                <Link
                                    href="/my-submissions"
                                    class="text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
                                >
                                    {{
                                        isBn
                                            ? 'রিপোর্টার পোর্টাল'
                                            : 'Reporter Portal'
                                    }}
                                </Link>
                            </div>
                            <div>
                                <Link
                                    href="/open-data"
                                    class="text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
                                >
                                    {{
                                        isBn
                                            ? 'উন্মুক্ত উপাত্ত ভাণ্ডার (Open Data)'
                                            : 'Open Research Data'
                                    }}
                                </Link>
                            </div>
                            <div>
                                <Link
                                    href="/transparency"
                                    class="text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
                                >
                                    {{
                                        isBn
                                            ? 'স্বচ্ছতা প্রতিবেদন (Transparency)'
                                            : 'Transparency Reports'
                                    }}
                                </Link>
                            </div>
                            <div>
                                <Link
                                    href="/feedback"
                                    class="text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
                                >
                                    {{
                                        isBn
                                            ? 'মতামত ও ত্রুটি জানান (Feedback / Bug)'
                                            : 'Report Bug / Feedback'
                                    }}
                                </Link>
                            </div>
                            <div>
                                <a
                                    href="/docs/api"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200"
                                >
                                    {{
                                        isBn
                                            ? 'ওপেন এপিআই নথিপত্র'
                                            : 'Public API Docs'
                                    }}
                                </a>
                            </div>
                        </div>
                        <div class="pt-2">
                            <Link
                                href="/login"
                                class="text-[11px] text-slate-500 transition-colors hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-400"
                            >
                                {{ isBn ? 'মডারেটর লগইন' : 'Staff Login' }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col items-center justify-between gap-4 border-t border-black/[0.06] pt-6 text-xs text-slate-500 transition-colors dark:border-white/[0.08] dark:text-slate-400 sm:flex-row"
                >
                    <p>
                        © {{ new Date().getFullYear() }} No More Silence.
                        {{
                            isBn
                                ? 'জনস্বার্থে ও গবেষণায় উন্মুক্ত।'
                                : 'Open public record for justice and research.'
                        }}
                    </p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Map Data: BBS / UN OCHA (CC BY-IGO 3.0)
                    </p>
                </div>
            </div>
        </footer>

        <!-- PWA Install Prompt Banner -->
        <PwaInstallPrompt />
    </div>
</template>
