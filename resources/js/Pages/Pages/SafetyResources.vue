<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head } from '@inertiajs/vue3';

const { isBn } = useLanguage();
</script>

<template>
    <PublicLayout
        :title="
            isBn
                ? 'নিরাপত্তা ও আইনি সহায়তা কেন্দ্র'
                : 'Safety & Support Resources'
        "
    >
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'বাংলাদেশে জরুরি হেল্পলাইন, আইনি সহায়তা সংস্থা এবং মানসিক সহায়তা হেল্পলাইনের তালিকা।'
                        : 'Emergency helplines, legal aid NGOs, and mental health support services in Bangladesh.'
                "
            />
        </Head>

        <div class="mx-auto max-w-4xl space-y-10 px-4 py-10 sm:px-6 lg:px-8">
            <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
                <h1
                    class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'নিরাপত্তা, আইনি সহায়তা ও জরুরি তথ্য'
                            : 'Safety, Legal Aid & Support'
                    }}
                </h1>
                <p
                    class="mt-1 text-xs text-slate-600 dark:text-slate-400 sm:text-sm"
                >
                    {{
                        isBn
                            ? 'আপনি বা আপনার পরিচিত কেউ যদি সহিংসতা, নিপীড়ন বা সাইবার হয়রানির শিকার হন, তবে নিচের নম্বরগুলোতে অবিলম্বে যোগাযোগ করুন।'
                            : 'If you or someone you know is facing violence, abuse, or online harassment, contact these emergency services immediately.'
                    }}
                </p>
            </div>

            <!-- Emergency Helplines Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    class="space-y-3 rounded-xl border border-rose-200 bg-rose-50/70 p-6 shadow-sm dark:border-rose-900/50 dark:bg-rose-950/30"
                >
                    <span
                        class="block text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400"
                    >
                        {{ isBn ? 'টোল-ফ্রি ২৪/৭' : '24/7 Toll Free' }}
                    </span>
                    <div
                        class="text-3xl font-black text-rose-950 dark:text-white sm:text-4xl"
                    >
                        ১০৯ (109)
                    </div>
                    <h3
                        class="text-sm font-bold text-rose-900 dark:text-rose-200"
                    >
                        {{
                            isBn
                                ? 'নারী ও শিশু নির্যাতন প্রতিরোধ হেল্পলাইন'
                                : 'Violence Against Women Helpline'
                        }}
                    </h3>
                    <p class="text-xs text-slate-700 dark:text-slate-300">
                        {{
                            isBn
                                ? 'মহিলা ও শিশু বিষয়ক মন্ত্রণালয় কর্তৃক পরিচালিত সার্বক্ষণিক জরুরি সহায়তা ও উদ্ধার সার্ভিস।'
                                : 'Ministry of Women and Children Affairs 24/7 crisis and shelter response.'
                        }}
                    </p>
                </div>

                <div
                    class="space-y-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <span
                        class="block text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400"
                    >
                        {{ isBn ? 'জাতীয় জরুরি সেবা' : 'National Emergency' }}
                    </span>
                    <div
                        class="text-3xl font-black text-slate-900 dark:text-white sm:text-4xl"
                    >
                        ৯৯৯ (999)
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-slate-200"
                    >
                        {{
                            isBn
                                ? 'বাংলাদেশ পুলিশ ও জরুরি সেবা'
                                : 'Police, Ambulance & Fire Service'
                        }}
                    </h3>
                    <p class="text-xs text-slate-700 dark:text-slate-300">
                        {{
                            isBn
                                ? 'তাৎক্ষণিক পুলিশি সহায়তা, সাইবার ক্রাইম এবং যেকোনো বিপদে সরাসরি যোগাযোগের জন্য।'
                                : 'Immediate emergency dispatch for cyber security and physical protection.'
                        }}
                    </p>
                </div>

                <div
                    class="space-y-3 rounded-xl border border-amber-200 bg-amber-50/50 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                >
                    <span
                        class="block text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400"
                    >
                        {{ isBn ? 'শিশু সুরক্ষা' : 'Child Protection' }}
                    </span>
                    <div
                        class="text-3xl font-black text-slate-900 dark:text-white sm:text-4xl"
                    >
                        ১০৯৮ (1098)
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-slate-200"
                    >
                        {{
                            isBn
                                ? 'চাইল্ড হেল্পলাইন বাংলাদেশ'
                                : 'Child Helpline Bangladesh'
                        }}
                    </h3>
                    <p class="text-xs text-slate-700 dark:text-slate-300">
                        {{
                            isBn
                                ? 'যেকোনো শিশুর প্রতি শারীরিক, মানসিক বা অনলাইন নির্যাতনের বিরুদ্ধে তাত্ক্ষণিক সহায়তা।'
                                : 'Emergency rescue and emotional support for children in distress.'
                        }}
                    </p>
                </div>
            </div>

            <!-- Legal Aid Organizations -->
            <div class="space-y-4">
                <h2
                    class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    {{
                        isBn
                            ? 'আইনি সহায়তা প্রদানকারী শীর্ষ সংস্থা (Legal Aid NGOs)'
                            : 'Recognized Legal Aid Organizations'
                    }}
                </h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        class="space-y-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
                    >
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            আইন ও সালিশ কেন্দ্র (ASK)
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            {{
                                isBn
                                    ? 'মানবাধিকার ও বিনামূল্যে আইনি সহায়তা ও পুনর্বাসন প্রদান।'
                                    : 'Human rights legal defense, counseling and crisis support.'
                            }}
                        </p>
                        <div
                            class="font-mono text-xs font-semibold text-rose-600 dark:text-rose-400"
                        >
                            হটলাইন: ০১৭৬৬-৬৬৪৪০০ | ০২-৯১২৯৬৭৩
                        </div>
                    </div>

                    <div
                        class="space-y-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
                    >
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            বাংলাদেশ লিগ্যাল এইড অ্যান্ড সার্ভিসেস ট্রাস্ট
                            (BLAST)
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            {{
                                isBn
                                    ? 'দেশের অন্যতম বৃহৎ আইনি সহায়তা নেটওয়ার্ক।'
                                    : 'Nationwide free legal aid network for marginalized communities.'
                            }}
                        </p>
                        <div
                            class="font-mono text-xs font-semibold text-rose-600 dark:text-rose-400"
                        >
                            ফোন: ০২-৮৩১৭১৮৫ | blast.org.bd
                        </div>
                    </div>

                    <div
                        class="space-y-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
                    >
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            বাংলাদেশ জাতীয় মহিলা আইনজীবী সমিতি (BNWLA)
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            {{
                                isBn
                                    ? 'নারী ও শিশুর আইনি অধিকার ও সহিংসতা প্রতিরোধে নিবেদিত।'
                                    : 'Dedicated legal advocacy for women and children rights.'
                            }}
                        </p>
                        <div
                            class="font-mono text-xs font-semibold text-rose-600 dark:text-rose-400"
                        >
                            হটলাইন: ০১৭২৪-০১৮০৮০
                        </div>
                    </div>

                    <div
                        class="space-y-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
                    >
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            কান পেতে রই (Kaan Pete Roi)
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            {{
                                isBn
                                    ? 'মানসিক স্বাস্থ্য ও সংকটকালীন আবেগীয় সহায়তা হটলাইন।'
                                    : 'Emotional support helpline for mental health and distress.'
                            }}
                        </p>
                        <div
                            class="font-mono text-xs font-semibold text-rose-600 dark:text-rose-400"
                        >
                            হেল্পলাইন: ০১৭৭৯-৫৫৪৩৯১ | ০১৭৭৯-৫৫৪৩৯২
                        </div>
                    </div>
                </div>
            </div>

            <!-- Digital Safety Tips -->
            <div
                class="space-y-3 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/40"
            >
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    {{
                        isBn
                            ? 'ডিজিটাল নিরাপত্তা ও প্রমাণ সংরক্ষণের পরামর্শ'
                            : 'Digital Security & Evidence Preservation Tips'
                    }}
                </h2>
                <ul
                    class="list-disc space-y-2 pl-5 text-xs text-slate-600 dark:text-slate-300"
                >
                    <li>
                        {{
                            isBn
                                ? 'কোনো হুমকি বা আপত্তিকর মন্তব্য দেখার সাথে সাথে পুরো স্ক্রিন (তারিখ, সময় ও মন্তব্যকারীর নামসহ) স্ক্রিনশট নিন।'
                                : 'Take full-screen screenshots immediately capturing date, time, and author name.'
                        }}
                    </li>
                    <li>
                        {{
                            isBn
                                ? 'মন্তব্যের পারমালিংক বা ইউআরএল কপি করে নিরাপদ স্থানে সংরক্ষণ করুন।'
                                : 'Copy and save the direct permalink URL to the comment or post.'
                        }}
                    </li>
                    <li>
                        {{
                            isBn
                                ? 'আপনার ফেসবুক অ্যাকাউন্টে দুই-স্তরের যাচাইকরণ (Two-Factor Authentication / 2FA) চালু রাখুন।'
                                : 'Keep Two-Factor Authentication (2FA) active on all your social media accounts.'
                        }}
                    </li>
                </ul>
            </div>
        </div>
    </PublicLayout>
</template>
