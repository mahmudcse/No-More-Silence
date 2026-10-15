<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head } from '@inertiajs/vue3';

const { isBn } = useLanguage();
</script>

<template>
    <PublicLayout :title="isBn ? 'ব্যবহারের শর্তাবলি' : 'Terms of Service'">
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'নো মোর সাইলেন্স ব্যবহারের শর্তাবলি ও আইনি নীতিমালা।'
                        : 'Terms of service and legal conditions for No More Silence.'
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
                            ? 'ব্যবহারের শর্তাবলি (Terms of Service)'
                            : 'Terms of Service'
                    }}
                </h1>
                <p class="mt-1 text-xs text-slate-400 sm:text-sm">
                    {{
                        isBn
                            ? 'কার্যকর তারিখ: অক্টোবর ২০২৬ | আইনি কাঠামোর সাথে সঙ্গতিপূর্ণ খসড়া'
                            : 'Effective: October 2026 | Drafted under applicable legal frameworks'
                    }}
                </p>
            </div>

            <div
                class="prose prose-invert prose-slate max-w-none space-y-6 text-sm leading-relaxed text-slate-300"
            >
                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ১. সংরক্ষণাগারের প্রকৃতি ও পরিধি
                    </h2>
                    <p>
                        <strong>No More Silence</strong> একটি স্বাধীন ও
                        জনস্বার্থমূলক ডিজিটাল সংরক্ষণাগার। এর উদ্দেশ্য সামাজিক
                        মাধ্যমে প্রকাশ্যে প্রকাশিত যৌন সহিংসতা সমর্থনকারী,
                        ভিকটিম-ব্লেমিং ও নিপীড়নের পক্ষে করা বক্তব্যসমূহ
                        তথ্যপ্রমাণসহ সংরক্ষণ করা।
                    </p>
                    <p>
                        আমরা কোনো ব্যক্তিকে ব্যক্তিগতভাবে "অপরাধী" বা "ধর্ষক"
                        হিসেবে আখ্যায়িত করি না। কেবল **"নথিবদ্ধ মন্তব্য
                        (Recorded comment)"** এবং **"যাচাইকৃত রেকর্ড (Verified
                        record)"** হিসেবে সত্যনিষ্ঠ প্রতিলিপি প্রকাশ করি।
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ২. ভিকটিমের তথ্য সুরক্ষার পূর্ণ নিশ্চয়তা (Zero Victim
                        PII)
                    </h2>
                    <p>
                        নারী ও শিশু নির্যাতন দমন আইন ২০০০ এর ১৪ ধারা এবং বৈশ্বিক
                        মানবাধিকার মানদণ্ড অনুযায়ী, কোনো ভিকটিমের নাম, ছবি,
                        ঠিকানা বা ব্যক্তিগত পরিচয় কখনোই আমাদের প্ল্যাটফর্মে রাখা
                        বা প্রকাশ করা হয় না। সকল স্ক্রিনশট স্বয়ংক্রিয়ভাবে ও
                        সার্ভার সাইডে নিখুঁতভাবে রেড্যাক্ট করা হয়।
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ৩. আপিল ও ভুল সংশোধনের অধিকার
                    </h2>
                    <p>
                        যেকোনো প্রোফাইল মালিকের নিজস্ব অ্যাকাউন্টের তথ্যের বিষয়ে
                        আপিল করার অধিকার রয়েছে। একাউন্ট হ্যাকিং, ফেক আইডি বা
                        প্রমাণের বিকৃতির প্রমাণ উপস্থাপিত হলে ২৪ ঘণ্টার মধ্যে
                        রেকর্ড প্রত্যাহার বা সংশোধন করা হয়।
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-white">
                        ৪. দায়বদ্ধতার সীমাবদ্ধতা ও আচরণবিধি
                    </h2>
                    <p>
                        এই প্ল্যাটফর্মের তথ্যের ভিত্তিতে কোনো প্রকার আইনবহির্ভূত
                        পদক্ষেপ, ব্যক্তিগত আক্রমণ, ডক্সিং (Doxxing) বা
                        শারীরিকভাবে হেনস্তা করা সম্পূর্ণ নিষিদ্ধ। এই তথ্য কেবল
                        জনসচেতনতা, নীতি প্রণয়ন ও গবেষণার উদ্দেশ্যে উন্মুক্ত।
                    </p>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
