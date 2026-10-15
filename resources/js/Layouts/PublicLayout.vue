<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    title?: string;
}>();

const { isBn, toggleLocale } = useLanguage();
const mobileMenuOpen = ref(false);
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-slate-950 font-sans text-slate-100 selection:bg-rose-500 selection:text-white"
    >
        <Head
            :title="
                title
                    ? `${title} — No More Silence`
                    : 'No More Silence — সহিসংতার সমর্থনে পাবলিক মন্তব্যের স্বাধীন প্রমাণ ও আর্কাইভ'
            "
        />

        <!-- Emergency Helpline Top Ribbon -->
        <div
            class="border-b border-rose-900/40 bg-rose-950/90 px-4 py-2 text-center text-xs text-rose-200"
        >
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-6 gap-y-1"
            >
                <span class="font-medium text-rose-300">
                    {{
                        isBn ? 'জরুরি সহায়তা প্রয়োজন?' : 'Need Emergency Help?'
                    }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span
                        class="h-1.5 w-1.5 animate-pulse rounded-full bg-rose-400"
                    ></span>
                    {{ isBn ? 'জাতীয় জরুরি সেবা:' : 'National Emergency:' }}
                    <strong class="font-bold tracking-wider text-white"
                        >৯৯৯ (999)</strong
                    >
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                    {{
                        isBn
                            ? 'নারী ও শিশু নির্যাতন প্রতিরোধ:'
                            : 'Women & Child Abuse Helpline:'
                    }}
                    <strong class="font-bold tracking-wider text-white"
                        >১০৯ (109)</strong
                    >
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                    {{ isBn ? 'শিশু হেল্পলাইন:' : 'Child Helpline:' }}
                    <strong class="font-bold tracking-wider text-white"
                        >১০৯৮ (1098)</strong
                    >
                </span>
            </div>
        </div>

        <!-- Main Header -->
        <header
            class="sticky top-0 z-40 border-b border-slate-800 bg-slate-900/90 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <!-- Brand / Logo -->
                <Link href="/" class="group flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-rose-500/40 bg-rose-600/20 text-xl font-black text-rose-400 transition-colors group-hover:bg-rose-600/30"
                    >
                        !
                    </div>
                    <div>
                        <div
                            class="flex items-center gap-2 text-base font-bold tracking-tight text-white"
                        >
                            <span>NO MORE SILENCE</span>
                            <span
                                class="rounded border border-slate-700 bg-slate-800 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-400"
                                >Archive</span
                            >
                        </div>
                        <div class="text-xs font-medium text-slate-400">
                            {{
                                isBn
                                    ? 'সহিংসতার সমর্থনে মন্তব্যের স্বাধীন প্রমাণ'
                                    : 'Independent Public Record Archive'
                            }}
                        </div>
                    </div>
                </Link>

                <!-- Desktop Nav -->
                <nav
                    class="hidden items-center gap-1 text-sm font-medium md:flex lg:gap-2"
                >
                    <Link
                        href="/"
                        class="rounded-md px-3 py-1.5 text-slate-300 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        {{ isBn ? 'হোম' : 'Home' }}
                    </Link>
                    <Link
                        href="/search"
                        class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-slate-300 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        <svg
                            class="h-4 w-4 text-slate-400"
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
                        class="rounded-md px-3 py-1.5 text-slate-300 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        {{ isBn ? 'জেলা মানচিত্র' : 'Map' }}
                    </Link>
                    <Link
                        href="/incidents"
                        class="rounded-md px-3 py-1.5 text-slate-300 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        {{ isBn ? 'ঘটনাসমূহ' : 'Incidents' }}
                    </Link>
                    <Link
                        href="/methodology"
                        class="rounded-md px-3 py-1.5 text-slate-300 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        {{ isBn ? 'কার্যপদ্ধতি' : 'Methodology' }}
                    </Link>
                    <Link
                        href="/safety-resources"
                        class="rounded-md px-3 py-1.5 text-rose-300 transition-colors hover:bg-slate-800 hover:text-rose-200"
                    >
                        {{ isBn ? 'নিরাপত্তা ও সহায়তা' : 'Safety' }}
                    </Link>
                </nav>

                <!-- Right Actions: Language Switch & Mobile Toggle -->
                <div class="flex items-center gap-3">
                    <!-- Language Toggle -->
                    <button
                        type="button"
                        @click="toggleLocale"
                        class="flex items-center gap-1.5 rounded-md border border-slate-700 bg-slate-800/80 px-2.5 py-1 text-xs font-semibold text-slate-200 transition-colors hover:bg-slate-700"
                        title="Change Language"
                    >
                        <span class="text-slate-400">🌐</span>
                        <span>{{ isBn ? 'English' : 'বাংলা' }}</span>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="rounded-md p-2 text-slate-400 hover:bg-slate-800 hover:text-white md:hidden"
                        aria-label="Toggle menu"
                    >
                        <svg
                            v-if="!mobileMenuOpen"
                            class="h-6 w-6"
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
                            class="h-6 w-6"
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
                class="space-y-1 border-b border-slate-800 bg-slate-900 px-4 pb-4 pt-2 md:hidden"
            >
                <Link
                    href="/"
                    class="block rounded-md px-3 py-2 text-base font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'হোম' : 'Home' }}
                </Link>
                <Link
                    href="/search"
                    class="block rounded-md px-3 py-2 text-base font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'অনুসন্ধান' : 'Search' }}
                </Link>
                <Link
                    href="/districts"
                    class="block rounded-md px-3 py-2 text-base font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'জেলা মানচিত্র' : 'Map' }}
                </Link>
                <Link
                    href="/incidents"
                    class="block rounded-md px-3 py-2 text-base font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'ঘটনাসমূহ' : 'Incidents' }}
                </Link>
                <Link
                    href="/methodology"
                    class="block rounded-md px-3 py-2 text-base font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'কার্যপদ্ধতি ও নীতি' : 'Methodology' }}
                </Link>
                <Link
                    href="/safety-resources"
                    class="block rounded-md px-3 py-2 text-base font-medium text-rose-300 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'নিরাপত্তা ও সহায়তা' : 'Safety' }}
                </Link>
                <Link
                    href="/about"
                    class="block rounded-md px-3 py-2 text-base font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    {{ isBn ? 'আমাদের সম্পর্কে' : 'About' }}
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Dignified Footer -->
        <footer
            class="mt-16 border-t border-slate-800 bg-slate-900 text-sm text-slate-400"
        >
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="mb-10 grid grid-cols-1 gap-8 md:grid-cols-4">
                    <!-- Column 1: Mission & Principles -->
                    <div class="space-y-3 md:col-span-1">
                        <div
                            class="flex items-center gap-2 text-base font-bold tracking-tight text-white"
                        >
                            <span>NO MORE SILENCE</span>
                        </div>
                        <p class="text-xs leading-relaxed text-slate-400">
                            {{
                                isBn
                                    ? 'সহিংসতার পক্ষে অবস্থান, ভিকটিম-ব্লেমিং এবং যৌন নির্যাতনের ন্যায্যতা প্রমাণের উদ্দেশ্যে সামাজিক মাধ্যমে প্রকাশিত সর্বজনীন মন্তব্যের স্বাধীন প্রমাণভিত্তিক ডিজিটাল সংরক্ষণাগার।'
                                    : 'An independent digital archive documenting verified public comments that justify sexual violence, blame victims, or incite hate.'
                            }}
                        </p>
                        <p class="text-[11px] text-slate-400">
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
                            class="text-xs font-semibold uppercase tracking-wider text-white"
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
                                    class="transition-colors hover:text-white"
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
                                    class="transition-colors hover:text-white"
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
                                    class="transition-colors hover:text-white"
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
                                    class="transition-colors hover:text-white"
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
                            class="text-xs font-semibold uppercase tracking-wider text-white"
                        >
                            {{
                                isBn
                                    ? 'আইনি ও মানসিক সহায়তা'
                                    : 'Helplines & Legal Aid'
                            }}
                        </h3>
                        <ul class="space-y-1.5 text-xs">
                            <li>
                                <span class="font-medium text-slate-300"
                                    >১০৯</span
                                >
                                —
                                {{
                                    isBn
                                        ? 'নারী ও শিশু নির্যাতন হেল্পলাইন'
                                        : 'Women & Children Hotline'
                                }}
                            </li>
                            <li>
                                <span class="font-medium text-slate-300"
                                    >৯৯৯</span
                                >
                                —
                                {{
                                    isBn
                                        ? 'জাতীয় জরুরি সেবা'
                                        : 'National Emergency'
                                }}
                            </li>
                            <li>
                                <span class="font-medium text-slate-300"
                                    >১০৯৮</span
                                >
                                —
                                {{ isBn ? 'শিশু হেল্পলাইন' : 'Child Helpline' }}
                            </li>
                            <li>
                                <Link
                                    href="/safety-resources"
                                    class="font-medium text-rose-400 transition-colors hover:text-rose-300"
                                    >{{
                                        isBn
                                            ? 'সবগুলো সহায়তাকেন্দ্র দেখুন →'
                                            : 'View All Resources →'
                                    }}</Link
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Column 4: Ethics & Right of Reply -->
                    <div class="space-y-2">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-white"
                        >
                            {{ isBn ? 'আপিল ও অধিকার' : 'Right of Reply' }}
                        </h3>
                        <p class="text-xs leading-relaxed text-slate-400">
                            {{
                                isBn
                                    ? 'আমরা ভুল সংশোধনে দায়বদ্ধ। কোনো রেকর্ডের ব্যাপারে আপত্তি থাকলে বা ভুল দাবি চিহ্নিত করতে চাইলে প্রমাণসহ আপিল করতে পারেন।'
                                    : 'We are committed to factual accuracy. If you believe any record contains an error or false attribution, you may submit an appeal.'
                            }}
                        </p>
                        <div class="pt-2">
                            <Link
                                href="/login"
                                class="text-[11px] text-slate-400 transition-colors hover:text-slate-300"
                            >
                                {{ isBn ? 'মডারেটর লগইন' : 'Moderator Login' }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col items-center justify-between gap-4 border-t border-slate-800/80 pt-6 text-xs text-slate-400 sm:flex-row"
                >
                    <p>
                        © {{ new Date().getFullYear() }} No More Silence.
                        {{
                            isBn
                                ? 'জনস্বার্থে ও গবেষণায় উন্মুক্ত।'
                                : 'Open public record for justice and research.'
                        }}
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Map Data: BBS / UN OCHA (CC BY-IGO 3.0)
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
