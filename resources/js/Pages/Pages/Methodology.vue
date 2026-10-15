<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useLanguage } from '@/composables/useLanguage';
import { Head } from '@inertiajs/vue3';

const { isBn } = useLanguage();
</script>

<template>
    <PublicLayout
        :title="isBn ? 'যাচাইকরণ কার্যপদ্ধতি' : 'Verification Methodology'"
    >
        <Head>
            <meta
                name="description"
                :content="
                    isBn
                        ? 'নো মোর সাইলেন্স সংরক্ষণাগারের ৮-দফা যাচাইকরণ মানদণ্ড ও ডিজিটাল প্রমাণ সংরক্ষণ নীতিমালা।'
                        : '8-point verification standard and digital evidence preservation methodology.'
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
                            ? '৮-দফা যাচাইকরণ মানদণ্ড ও কার্যপদ্ধতি'
                            : '8-Point Verification Methodology'
                    }}
                </h1>
                <p
                    class="mt-1 text-xs text-slate-600 dark:text-slate-400 sm:text-sm"
                >
                    {{
                        isBn
                            ? 'একটি রিপোর্টকে সর্বজনীনভাবে প্রকাশের আগে যে কঠোর পরীক্ষাগুলো সম্পন্ন করা হয়।'
                            : 'The rigorous standards every submission must pass before publication.'
                    }}
                </p>
            </div>

            <div
                class="prose prose-slate dark:prose-invert max-w-none space-y-6 text-sm leading-relaxed text-slate-700 dark:text-slate-300"
            >
                <p>
                    {{
                        isBn
                            ? 'ভুল তথ্য, ফটোশপ বা অসৎ উদ্দেশ্যে করা অভিযোগ প্রতিহত করতে আমাদের সিস্টেম কঠোর ক্রিপ্টোগ্রাফিক ও সম্পাদকীয় স্ট্যান্ডার্ড অনুসরণ করে। প্রতিটি রিপোর্টকে অবশ্যই নিম্নলিখিত ৮টি ধাপে উত্তীর্ণ হতে হয়:'
                            : 'To eliminate fabricated submissions, deepfakes, and malicious reporting, every record must pass eight strict verification checks before approval:'
                    }}
                </p>

                <!-- 8-Point Standard List -->
                <div class="not-prose space-y-4">
                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ১
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '১. স্থায়ী পারমালিংক বা স্বাধীন প্রমাণ'
                                        : '1. Permanent Permalink or Independent Corroboration'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যটির সরাসরি ফেসবুক লিঙ্ক কাজ করতে হবে। মন্তব্যটি মুছে ফেলা হলেও অন্তত ২টি ভিন্ন উৎস থেকে অভিন্ন স্ক্রিনশট ও পারসেপচুয়াল হ্যাশ (pHash) মিলতে হবে।'
                                        : 'The direct comment URL must open and be visible, or at least two independent screenshots must corroborate via perceptual hash match.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ২
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '২. প্রোফাইলের সুনির্দিষ্ট সংযোগ'
                                        : '2. Profile Match'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যকারীর লিঙ্কটি প্রকৃত ফেসবুক প্রোফাইলে রিডাইরেক্ট হতে হবে এবং সিস্টেম তার ক্যানোনিক্যাল URL ও নিউমেরিক আইডি সংরক্ষণ করে।'
                                        : 'The author link must resolve to the verified profile, capturing the canonical URL and numeric Facebook ID.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ৩
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '৩. অক্ষরিক টেক্সট সাদৃশ্য (Verbatim Match)'
                                        : '3. Verbatim Text Match'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'নথিবদ্ধ টেক্সটটি স্ক্রিনশটের মন্তব্যের সাথে হুবহু বর্ণে বর্ণে মিলতে হবে (বানান ভুল থাকলে তা-ও অপরিবর্তিত রাখা হয়)।'
                                        : 'Transcribed text must match the evidence verbatim, preserving exact characters and typos.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ৪
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '৪. প্রাসঙ্গিক প্রেক্ষাপট পরীক্ষা'
                                        : '4. Context & Sarcasm Review'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যটি সত্যিই অপরাধের পক্ষে বা ভিকটিমকে দোষারোপ করে কি না তা নিশ্চিত করা হয়। অপরাধীকে ব্যঙ্গ বা উদ্ধৃত করে করা কোনো মন্তব্য বাদ দেওয়া হয়।'
                                        : 'Ensuring the comment was genuinely justifying violence or victim-blaming, not sarcasm or quoting someone to condemn them.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ৫
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '৫. সুস্পষ্ট ক্যাটাগরিকরণ'
                                        : '5. Objective Categorization'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'মন্তব্যের প্রকৃতি অনুযায়ী যথাযথ ক্যাটাগরি (Victim Blaming, Justifying Violence, Trivialising, Incitement) নির্ধারণ করা।'
                                        : 'Assigned to a predefined category without subjective character labels.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ৬
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '৬. অডিট স্ন্যাপশট ধারণ'
                                        : '6. Internal Audit Snapshot'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'মডারেটরদের জন্য প্রোফাইলের তৎকালীন নাম ও প্রমাণের স্ন্যাপশট অভ্যন্তরীণভাবে সুরক্ষিত রাখা হয়।'
                                        : 'Private metadata capture for internal audit and verification trace.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ৭
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '৭. নিখুঁত ব্ল্যাক-বক্স রেড্যাকশন (Victim Redaction)'
                                        : '7. Solid Black-Box Redaction'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'ভিকটিম, অন্যান্য নির্দোষ ব্যক্তি এবং রিপোর্টকারীর নিজস্ব নাম স্ক্রিনশট থেকে সার্ভার সাইডে নিরেট কালো বক্সে ঢেকে দেওয়া হয় (রিভার্সিবল ব্লার নয়)।'
                                        : 'Victims and innocent bystanders are masked with server-side rendered solid black boxes (not blur).'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-xs font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-600/20 dark:text-rose-400"
                        >
                            ৮
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    isBn
                                        ? '৮. অপ্রাপ্তবয়স্কদের সুরক্ষা (Not a Minor)'
                                        : '8. Minor Protection'
                                }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    isBn
                                        ? 'প্রোফাইলটি কোনো শিশুর বা অপ্রাপ্তবয়স্কের মনে হলে তা কোনো অবস্থাতেই জনসমক্ষে প্রকাশ করা হয় না।'
                                        : 'Profiles visibly belonging to minors are suppressed from public view.'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <section class="space-y-3 pt-4">
                    <h2
                        class="text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{
                            isBn
                                ? 'দুই-ব্যক্তি সম্পাদকীয় নীতি (Two-Person Rule)'
                                : 'Two-Person Editorial Rule'
                        }}
                    </h2>
                    <p>
                        {{
                            isBn
                                ? 'আমাদের সফটওয়্যার স্থাপত্যে কঠোরভাবে কোড করা হয়েছে যে, কোনো একক ব্যক্তি একা একটি রিপোর্ট প্রকাশ করতে পারবেন না। যে মডারেটর প্রমাণ যাচাই করে ৮-দফা চেকলিস্ট পূরণ করবেন, তিনি প্রকাশ বোতাম চাপতে পারবেন না। ভিন্ন একজন অনুমোদিত প্রকাশক (Publisher) তা পুনরায় নিরীক্ষা করার পরই রিপোর্টটি ওয়েবসাইটে দৃশ্যমান হয়।'
                                : 'Our backend enforces that no individual can single-handedly verify and publish a record. The verifier who signs the 8-point checklist is strictly barred by system policy from publishing it.'
                        }}
                    </p>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
