<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ChevronRight, Eye, Lock, ShieldCheck } from 'lucide-vue-next';

defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <Head :title="title">
        <meta name="description" :content="description" />
    </Head>

    <div class="min-h-screen bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <!-- Header Section -->
            <div class="mb-12 text-center">
                <div
                    class="mb-4 inline-flex items-center justify-center rounded-2xl bg-indigo-100 p-3"
                >
                    <ShieldCheck class="h-8 w-8 text-indigo-600" />
                </div>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl"
                >
                    গোপনীয়তা নীতি
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-xl text-gray-500">
                    আপনার তথ্যের গোপনীয়তা এবং নিরাপত্তা আমাদের কাছে সবচেয়ে
                    গুরুত্বপূর্ণ।
                </p>
            </div>

            <!-- Content Card -->
            <div
                class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl shadow-indigo-200/50"
            >
                <div
                    class="prose prose-lg prose-indigo max-w-none px-8 py-10 text-gray-600 sm:px-12"
                >
                    <p class="mb-8 text-sm text-gray-400 italic">
                        সর্বশেষ আপডেট: জানুয়ারি ২০২৬
                    </p>

                    <section class="mb-10">
                        <h2
                            class="mb-4 flex items-center text-2xl font-bold text-gray-900"
                        >
                            <Eye class="mr-2 h-6 w-6 text-indigo-500" />
                            ১. আমরা কোন তথ্য সংগ্রহ করি?
                        </h2>
                        <p>
                            আপনার নাম, ঠিকানা, ফোন নম্বর এবং ইমেল ঠিকানা আমাদের
                            ওয়েবসাইটে অ্যাকাউন্ট খোলার সময় বা কোনো অর্ডার করার
                            সময় প্রয়োজন হয়। এছাড়া আপনার ব্রাউজিং প্যাটার্ন
                            সম্পর্কে কুকিজের মাধ্যমে কিছু তথ্য সংরক্ষিত হতে
                            পারে।
                        </p>
                    </section>

                    <section class="mb-10">
                        <h2
                            class="mb-4 flex items-center text-2xl font-bold text-gray-900"
                        >
                            <Lock class="mr-2 h-6 w-6 text-indigo-500" />
                            ২. তথ্যের নিরাপত্তা
                        </h2>
                        <p>
                            আপনার ব্যক্তিগত তথ্য সুরক্ষিত রাখতে আমরা সর্বোচ্চ
                            পর্যায়ের এনক্রিপশন এবং সিকিউরিটি প্রোটোকল ব্যবহার
                            করি। আপনার তথ্যের অপব্যবহার যাতে না ঘটে সেজন্য
                            আমাদের ডেটা সেন্টার সবসময় নজরদারিতে থাকে।
                        </p>
                    </section>

                    <section class="mb-10">
                        <h2
                            class="mb-4 flex items-center text-2xl font-bold text-gray-900"
                        >
                            <ChevronRight
                                class="mr-2 h-6 w-6 text-indigo-500"
                            />
                            ৩. তৃতীয় পক্ষের সাথে শেয়ারিং
                        </h2>
                        <p>
                            আমরা কখনোই আপনার অনুমতি ছাড়া কোনো তৃতীয় পক্ষের কাছে
                            আপনার ব্যক্তিগত তথ্য বিক্রি করি না। তবে ডেলিভারি
                            পার্টনারদের ক্ষেত্রে আপনার প্রয়োজনীয় তথ্য (নাম,
                            ঠিকানা ও ফোন নম্বর) শেয়ার করা হতে পারে শুধুমাত্র
                            ডেলিভারি সম্পন্ন করার জন্য।
                        </p>
                    </section>

                    <div
                        class="mt-12 rounded-2xl border border-indigo-100 bg-indigo-50 p-6"
                    >
                        <div class="flex items-start">
                            <ShieldCheck
                                class="mt-1 h-6 w-6 flex-shrink-0 text-indigo-600"
                            />
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-indigo-900">
                                    গোপনীয়তা আমাদের প্রতিশ্রুতি
                                </h4>
                                <p class="text-sm text-indigo-700">
                                    আমাদের গোপনীয়তা নীতি সম্পর্কে আপনার কোনো
                                    প্রশ্ন থাকলে সরাসরি ইমেল করুন:
                                    privacy@boighor.com
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="mt-12 text-center">
                <a
                    href="/"
                    class="inline-flex items-center font-semibold text-indigo-600 transition-colors hover:text-indigo-800"
                >
                    মূল পাতায় ফিরে যান
                    <ChevronRight class="ml-1 h-5 w-5" />
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
.prose h2 {
    margin-top: 0;
}
</style>
