<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ChevronRight, FileText, ShieldCheck } from 'lucide-vue-next';

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
                    class="mb-4 inline-flex items-center justify-center rounded-2xl bg-blue-100 p-3"
                >
                    <FileText class="h-8 w-8 text-blue-600" />
                </div>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl"
                >
                    শর্তাবলী ও নীতিমালা
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-xl text-gray-500">
                    আমাদের পরিষেবা ব্যবহারের নিয়ম এবং শর্তাবলী সম্পর্কে
                    বিস্তারিত তথ্য।
                </p>
            </div>

            <!-- Content Card -->
            <div
                class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl shadow-gray-200/50"
            >
                <div
                    class="prose prose-lg prose-blue max-w-none px-8 py-10 text-gray-600 sm:px-12"
                >
                    <p class="mb-8 text-sm text-gray-400 italic">
                        সর্বশেষ আপডেট: জানুয়ারি ২০২৬
                    </p>

                    <section class="mb-10">
                        <h2
                            class="mb-4 flex items-center text-2xl font-bold text-gray-900"
                        >
                            <ChevronRight class="mr-2 h-6 w-6 text-blue-500" />
                            ১. ভূমিকা
                        </h2>
                        <p>
                            'এলাকাদটকম' এ স্বাগতম। এই ওয়েবসাইটটি ব্যবহারের
                            মাধ্যমে আপনি আমাদের সকল শর্তাবলীর সাথে একমত পোষণ
                            করছেন। এই সেবাগুলো ব্যবহারের পূর্বে দয়া করে সমস্ত
                            শর্তাবলী যত্ন সহকারে পড়ুন।
                        </p>
                    </section>

                    <section class="mb-10">
                        <h2
                            class="mb-4 flex items-center text-2xl font-bold text-gray-900"
                        >
                            <ChevronRight class="mr-2 h-6 w-6 text-blue-500" />
                            ২. অর্ডার ও পেমেন্ট
                        </h2>
                        <p>
                            আমাদের প্ল্যাটফর্মে আপনি যেকোনো সময় বই অর্ডার করতে
                            পারেন। অর্ডারের পর আমাদের প্রতিনিধি আপনার সাথে
                            যোগাযোগ করে অর্ডার নিশ্চিত করবেন। পেমেন্ট গেটওয়ে বা
                            ক্যাশ অন ডেলিভারি এর মাধ্যমে মূল্য পরিশোধ করা যাবে।
                        </p>
                    </section>

                    <section class="mb-10">
                        <h2
                            class="mb-4 flex items-center text-2xl font-bold text-gray-900"
                        >
                            <ChevronRight class="mr-2 h-6 w-6 text-blue-500" />
                            ৩. ডেলিভারি নীতিমালা
                        </h2>
                        <p>
                            আমরা সাধারণত সারা বাংলাদেশে ৩-৭ কার্যদিবসের মধ্যে বই
                            ডেলিভারি দিয়ে থাকি। বিশেষ ক্ষেত্রে এই সময় কিছুটা
                            দীর্ঘায়িত হতে পারে। ডেলিভারি সংক্রান্ত যেকোনো
                            সমস্যার জন্য আমাদের হটলাইন নম্বরে যোগাযোগ করুন।
                        </p>
                    </section>

                    <div
                        class="mt-12 rounded-2xl border border-blue-100 bg-blue-50 p-6"
                    >
                        <div class="flex items-start">
                            <ShieldCheck
                                class="mt-1 h-6 w-6 flex-shrink-0 text-blue-600"
                            />
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-blue-900">
                                    আপনার আস্থা আমাদের সম্পদ
                                </h4>
                                <p class="text-sm text-blue-700">
                                    যেকোনো আইনি তথ্যের জন্য অথবা আমাদের নীতিমালা
                                    পরিবর্তনের ব্যাপারে অবহিত হতে নিয়মিত এই
                                    পৃষ্ঠাটি ভিজিট করুন।
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
                    class="inline-flex items-center font-semibold text-blue-600 transition-colors hover:text-blue-800"
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
