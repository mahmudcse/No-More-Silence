<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head } from '@inertiajs/vue3';

const { isBn } = useLanguage();
</script>

<template>
    <PublicLayout :title="isBn ? 'আমাদের সম্পর্কে' : 'About Us'">
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'নো মোর সাইলেন্স সংরক্ষণাগারের লক্ষ্য, প্রেক্ষাপট ও পরিচালনা কাঠামো।'
                        : 'About No More Silence: mission, methodology, and governance.'
                "
            />
        </Head>

        <div class="mx-auto max-w-4xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
                <h1
                    class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'আমাদের সম্পর্কে ও পরিচালনা কাঠামো'
                            : 'About No More Silence'
                    }}
                </h1>
                <p
                    class="mt-1 text-xs text-slate-600 dark:text-slate-400 sm:text-sm"
                >
                    {{
                        isBn
                            ? 'একটি স্বাধীন, নিরপেক্ষ ও জনস্বার্থমূলক ডিজিটাল সংরক্ষণাগার।'
                            : 'An independent, non-partisan public interest archive.'
                    }}
                </p>
            </div>

            <div
                class="prose prose-slate dark:prose-invert max-w-none space-y-6 text-sm leading-relaxed text-slate-700 dark:text-slate-300"
            >
                <section class="space-y-3">
                    <h2
                        class="text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ isBn ? 'আমাদের লক্ষ্য ও দর্শন' : 'Our Mission' }}
                    </h2>
                    <p>
                        {{
                            isBn
                                ? 'বাংলাদেশে সাম্প্রতিক বছরগুলোতে নারী ও শিশুর প্রতি সহিংসতা এবং ধর্ষণের মতো অপরাধের পর সামাজিক মাধ্যমে এক শ্রেণির ব্যবহারকারী প্রকাশ্যেই অপরাধীর পক্ষ নেন, ভিকটিমকে দোষারোপ (Victim Blaming) করেন কিংবা অপরাধকে লঘুকরণ করেন। এই ডিজিটাল বক্তব্যগুলো কেবল ভিকটিম ও তার পরিবারকে দ্বিতীয়বার ট্রমাটাইজ করে না, বরং সমাজে অপরাধের স্বাভাবিকীকরণ ঘটায়।'
                                : 'In Bangladesh, following incidents of gender-based violence and rape, a segment of social media users publicly justify the crime, blame the victim, or celebrate the abuse. This secondary victimization normalizes criminal behavior and chills survivors from seeking justice.'
                        }}
                    </p>
                    <p>
                        {{
                            isBn
                                ? 'নো মোর সাইলেন্স কোনো ব্যক্তির চরিত্র হনন বা অপবাদের স্থান নয়। এটি একটি সত্যনিষ্ঠ ডিজিটাল প্রমাণ ব্যাংক, যেখানে কেবল প্রকাশ্যে প্রকাশিত মন্তব্যসমূহ স্থায়ী ক্রিপ্টোগ্রাফিক হ্যাশ ও নিরপেক্ষ যাচাইকরণের মাধ্যমে সংরক্ষণ করা হয়।'
                                : 'No More Silence is not a platform for character assassination. It is a verifiable digital evidence repository where only publicly published statements are archived with tamper-evident digital hashes.'
                        }}
                    </p>
                </section>

                <section
                    class="space-y-3 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <h2
                        class="text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? 'মূল পরিচালন নীতিসমূহ'
                                : 'Core Operating Principles'
                        }}
                    </h2>
                    <ul
                        class="list-disc space-y-2 pl-5 text-slate-700 dark:text-slate-300"
                    >
                        <li>
                            <strong class="text-slate-900 dark:text-white">{{
                                isBn
                                    ? 'ভিকটিম সুরক্ষা (Victim Privacy):'
                                    : 'Victim Privacy:'
                            }}</strong>
                            {{
                                isBn
                                    ? 'আমরা কখনোই ভিকটিমের ছবি, নাম বা পরিচয় সংরক্ষণ বা প্রকাশ করি না। স্ক্রিনশট থেকে তা স্বয়ংক্রিয়ভাবে মুছে দেওয়া হয়।'
                                    : 'Victim names, photos, and identifiable details are permanently redacted.'
                            }}
                        </li>
                        <li>
                            <strong class="text-slate-900 dark:text-white">{{
                                isBn
                                    ? 'দুই-ব্যক্তি যাচাইকরণ (Two-Person Rule):'
                                    : 'Two-Person Rule:'
                            }}</strong>
                            {{
                                isBn
                                    ? 'কোনো রেকর্ড একজন ব্যক্তি একা অনুমোদন ও প্রকাশ করতে পারেন না। একজন মডারেটর যাচাই করবেন এবং অন্য একজন স্বতন্ত্র রিভিউয়ার তা প্রকাশ করবেন।'
                                    : 'No record can be verified and published by the same individual.'
                            }}
                        </li>
                        <li>
                            <strong class="text-slate-900 dark:text-white">{{
                                isBn
                                    ? 'অপ্রাপ্তবয়স্কদের সুরক্ষা:'
                                    : 'Protection of Minors:'
                            }}</strong>
                            {{
                                isBn
                                    ? 'কমেন্টকারী যদি অপ্রাপ্তবয়স্ক (শিশু/কিশোর) বলে প্রতীয়মান হয়, তবে তা জনসমক্ষে প্রকাশ করা হয় না।'
                                    : 'Profiles appearing to belong to minors are suppressed from public display.'
                            }}
                        </li>
                        <li>
                            <strong class="text-slate-900 dark:text-white">{{
                                isBn
                                    ? 'আপিল ও আত্মপক্ষ সমর্থনের অধিকার:'
                                    : 'Right of Reply & Appeals:'
                            }}</strong>
                            {{
                                isBn
                                    ? 'আইডি হ্যাক হওয়া বা ভুল সনাক্তকরণের ক্ষেত্রে যে কেউ প্রমাণের ভিত্তিতে উন্মুক্ত আপিল করতে পারেন।'
                                    : 'Anyone can dispute records by providing corroborating proof of compromise.'
                            }}
                        </li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h2
                        class="text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? 'আইনি ও প্রাতিষ্ঠানিক রূপান্তর'
                                : 'Governance & Institutional Transition'
                        }}
                    </h2>
                    <p>
                        {{
                            isBn
                                ? 'প্রকল্পটির স্থায়িত্ব ও নিরাপত্তা নিশ্চিত করতে এটি পরবর্তীতে একটি স্বীকৃত মানবাধিকার ও আইনি সহায়তা প্রদানকারী বেসরকারি সংস্থার (NGO / Legal Aid Trust) তত্ত্বাবধানে স্থানান্তরিত হওয়ার পরিকল্পনা রয়েছে।'
                                : 'To ensure long-term sustainability, digital security, and legal hygiene, the archive operates with a documented transition roadmap to an established legal-aid human rights organization in Bangladesh.'
                        }}
                    </p>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
