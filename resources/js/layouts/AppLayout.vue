<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import Sidebar from '@/components/Sidebar.vue';
import { router } from '@inertiajs/vue3'; // Inertia router ইমপোর্ট করুন
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    title?: string;
}>();

// === ADVANCED BACK BUTTON LOGIC ===
const goBack = () => {
    // ব্রাউজারের হিস্ট্রি চেক করা
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // যদি হিস্ট্রি না থাকে (নতুন ট্যাব বা সরাসরি লিংক), তবে হোমপেজে নিয়ে যাবে
        router.visit('/');
    }
};
</script>

<template>
    <div class="font-bangla min-h-screen bg-slate-50 text-slate-800">
        <!-- ======================= -->
        <!-- DESKTOP HEADER          -->
        <!-- ======================= -->
        <div class="hidden lg:block">
            <Heading />
        </div>

        <!-- ======================= -->
        <!-- MOBILE APP HEADER       -->
        <!-- ======================= -->
        <div
            class="sticky top-0 z-40 flex h-14 items-center gap-4 border-b border-slate-200/60 bg-white/80 px-4 shadow-sm backdrop-blur-xl lg:hidden"
        >
            <!-- Back Button (Always Visible now) -->
            <button
                @click="goBack"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition-transform hover:bg-slate-200 active:scale-90"
            >
                <ArrowLeft class="h-5 w-5" />
            </button>

            <!-- Page Title -->
            <h1 class="truncate text-lg font-bold text-slate-900">
                <slot name="header">
                    {{ title || 'অ্যাকাউন্ট' }}
                </slot>
            </h1>
        </div>

        <!-- ======================= -->
        <!-- MAIN CONTENT AREA       -->
        <!-- ======================= -->
        <main class="mx-auto max-w-7xl lg:px-8 lg:py-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:gap-8">
                <!-- Sidebar / Bottom Nav -->
                <div class="lg:w-72 lg:flex-shrink-0">
                    <Sidebar />
                </div>

                <!-- Page Content -->
                <div class="min-w-0 flex-1 pb-24 lg:pb-0">
                    <div
                        class="animate-in duration-500 ease-out fade-in slide-in-from-bottom-4"
                    >
                        <div
                            class="bg-transparent lg:rounded-2xl lg:bg-white lg:p-8 lg:shadow-sm lg:ring-1 lg:ring-slate-100"
                        >
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');

.font-bangla {
    font-family: 'Hind Siliguri', sans-serif;
}
</style>
