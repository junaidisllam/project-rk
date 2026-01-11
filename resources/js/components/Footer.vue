<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Facebook, Instagram, Youtube } from 'lucide-vue-next';
</script>

<template>
    <!-- Main Footer Container with Gradient and Rounded Top -->
    <footer
        class="relative mt-auto overflow-hidden border-t border-gray-100 bg-white pt-20 pb-12"
    >
        <!-- Background decorative elements -->
        <div
            class="absolute top-0 right-0 -z-10 h-96 w-96 translate-x-1/2 -translate-y-1/2 rounded-full bg-blue-50/50 blur-3xl"
        ></div>
        <div
            class="absolute bottom-0 left-0 -z-10 h-96 w-96 -translate-x-1/2 translate-y-1/2 rounded-full bg-indigo-50/50 blur-3xl"
        ></div>

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <div
                class="mb-16 grid grid-cols-1 items-start gap-12 lg:grid-cols-12 lg:gap-8"
            >
                <!-- Branding & Tagline -->
                <div class="flex flex-col gap-8 lg:col-span-5">
                    <Link
                        href="/"
                        class="inline-block w-fit transition-transform hover:scale-105"
                    >
                        <img src="/logo.png" alt="logo" class="h-16" />
                    </Link>
                    <p
                        class="max-w-md text-xl leading-relaxed font-medium text-gray-600"
                    >
                        বই পড়ার এক নতুন দুনিয়া আবিষ্কার করুন আমাদের সাথে।
                        বাংলাদেশের সবচেয়ে সাশ্রয়ী এবং বিশ্বস্ত অনলাইন বইয়ের
                        দোকান।
                    </p>

                    <!-- Social Links -->
                    <div class="flex items-center gap-4">
                        <a
                            href="#"
                            class="group rounded-2xl bg-gray-50 p-3 transition-all duration-300 hover:bg-blue-600"
                        >
                            <Facebook
                                class="size-6 text-gray-400 transition-all group-hover:scale-110 group-hover:text-white"
                            />
                        </a>
                        <a
                            href="#"
                            class="group rounded-2xl bg-gray-50 p-3 transition-all duration-300 hover:bg-red-600"
                        >
                            <Youtube
                                class="size-6 text-gray-400 transition-all group-hover:scale-110 group-hover:text-white"
                            />
                        </a>
                        <a
                            href="#"
                            class="group rounded-2xl bg-gray-50 p-3 transition-all duration-300 hover:bg-pink-600"
                        >
                            <Instagram
                                class="size-6 text-gray-400 transition-all group-hover:scale-110 group-hover:text-white"
                            />
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="grid grid-cols-1 gap-10 lg:col-span-3">
                    <div>
                        <h3
                            class="mb-6 text-xs font-bold tracking-widest text-gray-400 uppercase"
                        >
                            দ্রুত লিঙ্ক
                        </h3>
                        <nav class="flex flex-col gap-4">
                            <Link
                                href="/"
                                class="group flex items-center gap-2 text-base font-semibold text-gray-600 transition-colors hover:text-blue-600"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-gray-300 transition-colors group-hover:bg-blue-600"
                                ></span>
                                হোম
                            </Link>
                            <Link
                                href="/catalog"
                                class="group flex items-center gap-2 text-base font-semibold text-gray-600 transition-colors hover:text-blue-600"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-gray-300 transition-colors group-hover:bg-blue-600"
                                ></span>
                                ক্যাটালগ
                            </Link>
                            <Link
                                href="/allbooks"
                                class="group flex items-center gap-2 text-base font-semibold text-gray-600 transition-colors hover:text-blue-600"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-gray-300 transition-colors group-hover:bg-blue-600"
                                ></span>
                                সকল বই
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- Legal Links -->
                <div class="grid grid-cols-1 gap-10 lg:col-span-2">
                    <div>
                        <h3
                            class="mb-6 text-xs font-bold tracking-widest text-gray-400 uppercase"
                        >
                            নীতিমালা
                        </h3>
                        <nav class="flex flex-col gap-4">
                            <Link
                                href="/terms"
                                class="text-base font-semibold text-gray-600 transition-colors hover:text-blue-600"
                                >শর্তাবলী</Link
                            >
                            <Link
                                href="/privacy"
                                class="text-base font-semibold text-gray-600 transition-colors hover:text-blue-600"
                                >গোপনীয়তা নীতি</Link
                            >
                        </nav>
                    </div>
                </div>

                <!-- Contact/Support -->
                <div class="lg:col-span-2">
                    <h3
                        class="mb-6 text-xs font-bold tracking-widest text-gray-400 uppercase"
                    >
                        সহায়তা
                    </h3>
                    <div class="flex flex-col gap-4">
                        <p class="text-base font-semibold text-gray-900">
                            +৮৮০ ১৭১১-০০০০০০
                        </p>
                        <p
                            class="text-sm font-medium whitespace-nowrap text-gray-500"
                        >
                            support@boighor.com
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div
                class="flex flex-col items-center justify-between gap-6 border-t border-gray-100 pt-12 md:flex-row"
            >
                <p class="text-sm font-medium text-gray-500">
                    © {{ new Date().getFullYear() }} এলাকালডটকম। সর্বস্বত্ব
                    সংরক্ষিত।
                </p>
                <div class="flex items-center gap-8">
                    <p
                        class="max-w-[200px] text-center text-xs text-gray-400 italic md:text-right"
                    >
                        ১২০৭, মোহাম্মদপুর, ঢাকা, বাংলাদেশ
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>
