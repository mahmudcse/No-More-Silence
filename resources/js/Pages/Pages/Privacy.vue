<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head } from '@inertiajs/vue3';

const { isBn } = useLanguage();
</script>

<template>
    <PublicLayout :title="isBn ? 'গোপনীয়তা নীতি' : 'Privacy Policy'">
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'নো মোর সাইলেন্স গোপনীয়তা নীতি ও ডাটা সুরক্ষা স্ট্যান্ডার্ড।'
                        : 'Privacy policy and data protection standards for No More Silence.'
                "
            />
        </Head>

        <div class="mx-auto max-w-4xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
            <div class="border-b border-slate-800 pb-4">
                <h1
                    class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{
                        isBn
                            ? 'গোপনীয়তা নীতি (Privacy Policy)'
                            : 'Privacy Policy'
                    }}
                </h1>
                <p class="mt-1 text-xs text-slate-400 sm:text-sm">
                    {{
                        isBn
                            ? 'ব্যবহারকারীর তথ্য সুরক্ষা এবং জিরো-ট্র্যাকার অঙ্গীকার'
                            : 'User data protection and zero-tracker commitment'
                    }}
                </p>
            </div>

            <div
                class="prose prose-invert prose-slate max-w-none space-y-6 text-sm leading-relaxed text-slate-300"
            >
                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ১. কোনো ট্র্যাকিং বা বাণিজ্যিক অ্যানালিটিক্স নেই
                    </h2>
                    <p>
                        আমরা কোনো বাণিজ্যিক বিজ্ঞাপন, Google Analytics বা ফেসবুক
                        পিক্সেল ব্যবহার করি না। আপনার ইন্টারনেট ব্রাউজিং আচরণ
                        কোনো তৃতীয় পক্ষের সাথে শেয়ার করা হয় না।
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ২. আইপি অ্যাড্রেস এনক্রিপশন ও অটো-পার্জ
                    </h2>
                    <p>
                        সাইবার আক্রমণ ও স্প্যাম রোধ করার জন্য ব্যবহারকারীর আইপি
                        অ্যাড্রেস সরাসরি ডেটাবেজে সংরক্ষণ করা হয় না; বরং একটি
                        ঘূর্ণায়মান সিক্রেট কি দিয়ে ক্রিপ্টোগ্রাফিক হ্যাশ
                        (`HMAC-SHA256`) আকারে রাখা হয় এবং ৩০ দিন পর তা
                        স্বয়ংক্রিয়ভাবে মুছে ফেলা হয়।
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ৩. রিপোর্টকারীর নিরাপত্তা
                    </h2>
                    <p>
                        নাগরিকরা চাইলে সম্পূর্ণ অজ্ঞাতনামা (Anonymous) হিসেবে
                        রিপোর্ট জমা দিতে পারেন। রিপোর্টকারীর পরিচয় বা তথ্য কখনোই
                        সর্বজনীনভাবে প্রকাশিত হয় না এবং মডারেটরদের জন্যেও তা
                        সর্বোচ্চ সুরক্ষায় এনক্রিপ্ট করে রাখা হয়।
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ৪. সার্চ ইঞ্জিন নো-ইনডেক্স (Noindex) নীতি
                    </h2>
                    <p>
                        ব্যক্তিগত প্রতিশোধমূলক সার্চ ও অযাচিত অ্যালগরিদম হেনস্তা
                        কমাতে আমাদের স্বতন্ত্র প্রোফাইল পেজগুলোতে সার্চ ইঞ্জিন
                        নির্দেশক (<code>noindex, nofollow</code>) প্রয়োগ করা
                        হয়েছে।
                    </p>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
