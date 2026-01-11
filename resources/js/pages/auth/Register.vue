<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Chrome,
    Facebook,
    Lock,
    Mail,
    User,
} from 'lucide-vue-next';

const form = useForm({
    name: '',
    contact: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(store.register ? store.register().url : '/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="রেজিস্ট্রেশন" />

    <div class="font-bangla flex min-h-screen flex-col bg-white">
        <!-- ========================================== -->
        <!-- DESKTOP HEADER (Hidden on Mobile)          -->
        <!-- ========================================== -->
        <div class="hidden lg:block">
            <Heading />
        </div>

        <!-- ========================================== -->
        <!-- MOBILE APP HEADER (Sticky)                 -->
        <!-- ========================================== -->
        <div
            class="sticky top-0 z-50 flex h-16 items-center justify-between bg-white px-4 shadow-sm lg:hidden"
        >
            <Link
                :href="login()"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-600 active:scale-95"
            >
                <ArrowLeft class="h-6 w-6" />
            </Link>
            <span class="text-lg font-bold text-slate-800">রেজিস্ট্রেশন</span>
            <div class="w-10"></div>
        </div>

        <!-- ========================================== -->
        <!-- MAIN CONTENT                               -->
        <!-- ========================================== -->
        <main class="flex w-full flex-1">
            <!-- LEFT COLUMN: Form Section -->
            <div
                class="flex w-full flex-col justify-center px-6 py-10 sm:px-8 lg:w-1/2 lg:flex-none lg:px-20 xl:px-24"
            >
                <div class="mx-auto w-full max-w-sm lg:w-[420px]">
                    <!-- Stylized Header -->
                    <div class="mb-10 text-center lg:text-left">
                        <h2
                            class="text-4xl leading-tight font-black tracking-tight text-slate-900 lg:text-5xl"
                        >
                            নতুন একটি <br />
                            <span
                                class="bg-linear-to-r from-blue to-[#00abff] bg-clip-text text-4xl text-transparent"
                            >
                                অ্যাকাউন্ট তৈরি করুন
                            </span>
                        </h2>
                        <p class="mt-4 text-sm font-medium text-slate-500">
                            আমাদের পরিবারের সদস্য হতে নিচের ফর্মটি পূরণ করুন।
                        </p>
                    </div>

                    <!-- INERTIA FORM -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name Input -->
                        <div class="space-y-2">
                            <Label
                                for="name"
                                class="text-sm font-bold text-slate-700"
                                >আপনার নাম</Label
                            >
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                                >
                                    <User class="h-5 w-5 text-slate-400" />
                                </div>
                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    placeholder="আপনার পূর্ণ নাম"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 pl-11 text-base transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    :class="{
                                        'border-red-500 focus:ring-red-100':
                                            form.errors.name,
                                    }"
                                />
                            </div>
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Contact Input -->
                        <div class="space-y-2">
                            <Label
                                for="contact"
                                class="text-sm font-bold text-slate-700"
                                >মোবাইল বা ইমেইল</Label
                            >
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                                >
                                    <Mail class="h-5 w-5 text-slate-400" />
                                </div>
                                <Input
                                    id="contact"
                                    type="text"
                                    name="contact"
                                    v-model="form.contact"
                                    required
                                    :tabindex="2"
                                    autocomplete="username"
                                    placeholder="আপনার মোবাইল নাম্বার বা ইমেইল দিন"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 pl-11 text-base transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    :class="{
                                        'border-red-500 focus:ring-red-100':
                                            form.errors.contact,
                                    }"
                                />
                            </div>
                            <InputError :message="form.errors.contact" />
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-2">
                            <Label
                                for="password"
                                class="text-sm font-bold text-slate-700"
                                >পাসওয়ার্ড</Label
                            >
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                                >
                                    <Lock class="h-5 w-5 text-slate-400" />
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    v-model="form.password"
                                    required
                                    :tabindex="3"
                                    autocomplete="new-password"
                                    placeholder="কমপক্ষে ৮টি অক্ষর"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 pl-11 text-base transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    :class="{
                                        'border-red-500 focus:ring-red-100':
                                            form.errors.password,
                                    }"
                                />
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password Input -->
                        <div class="space-y-2">
                            <Label
                                for="password_confirmation"
                                class="text-sm font-bold text-slate-700"
                                >পাসওয়ার্ড নিশ্চিত করুন</Label
                            >
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                                >
                                    <CheckCircle2
                                        class="h-5 w-5 text-slate-400"
                                    />
                                </div>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    v-model="form.password_confirmation"
                                    required
                                    :tabindex="4"
                                    autocomplete="new-password"
                                    placeholder="পাসওয়ার্ডটি পুনরায় লিখুন"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 pl-11 text-base transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    :class="{
                                        'border-red-500 focus:ring-red-100':
                                            form.errors.password_confirmation,
                                    }"
                                />
                            </div>
                            <InputError
                                :message="form.errors.password_confirmation"
                            />
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue text-base font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-hover hover:shadow-xl active:scale-[0.98]"
                            :tabindex="5"
                            :disabled="form.processing"
                            data-test="register-user-button"
                        >
                            <Spinner
                                v-if="form.processing"
                                class="h-5 w-5 text-white"
                            />
                            <span v-else>অ্যাকাউন্ট তৈরি করুন</span>
                            <ArrowRight
                                v-if="!form.processing"
                                class="h-5 w-5 opacity-80"
                            />
                        </Button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div
                            class="relative flex justify-center text-xs uppercase"
                        >
                            <span
                                class="bg-white px-3 font-semibold tracking-wider text-slate-400"
                                >Or join with</span
                            >
                        </div>
                    </div>

                    <!-- Social Login -->
                    <div class="grid grid-cols-2 gap-4">
                        <button
                            type="button"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95"
                        >
                            <Chrome class="h-5 w-5 text-orange-500" />
                            Google
                        </button>
                        <button
                            type="button"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95"
                        >
                            <Facebook class="h-5 w-5 text-[#1877F2]" />
                            Facebook
                        </button>
                    </div>

                    <!-- Footer Link -->
                    <div
                        class="mt-8 text-center text-sm font-medium text-slate-500"
                    >
                        আগেই অ্যাকাউন্ট আছে?
                        <TextLink
                            :href="login()"
                            :tabindex="6"
                            class="ml-1 font-bold text-blue-600 hover:text-blue-800 hover:underline"
                        >
                            লগইন করুন
                        </TextLink>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Visual/Image Section -->
            <div class="relative hidden w-0 flex-1 lg:block">
                <!-- Image -->
                <img
                    class="absolute inset-0 h-full w-full object-cover"
                    src="https://images.unsplash.com/photo-1519791883288-dc8bd696e667?q=80&w=2070&auto=format&fit=crop"
                    alt="Book Store"
                />

                <!-- Dark Overlay -->
                <div class="absolute inset-0 bg-slate-900/80"></div>

                <!-- Quote Section -->
                <div
                    class="absolute right-0 bottom-0 left-0 flex h-full flex-col justify-center p-20 text-white"
                >
                    <div class="max-w-xl">
                        <div
                            class="mb-6 h-1 w-20 bg-linear-to-r from-blue-500 to-cyan-400"
                        ></div>
                        <h3
                            class="font-bangla text-5xl leading-tight font-bold tracking-tight"
                        >
                            "বই হলো এমন এক মাধ্যম,<br />
                            যা আমাদের অজান্তেই বিশ্বভ্রমণ করায়।"
                        </h3>
                        <div class="mt-6 flex items-center gap-3">
                            <p
                                class="text-lg font-medium tracking-widest text-blue-200 uppercase"
                            >
                                — হুমায়ূন আহমেদ
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer (Hidden on Mobile) -->
        <div class="hidden lg:block">
            <Footer />
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700;900&display=swap');

.font-bangla {
    font-family: 'Hind Siliguri', sans-serif;
}
</style>
