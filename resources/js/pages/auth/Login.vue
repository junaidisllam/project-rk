<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Chrome,
    Facebook,
    Lock,
    Mail,
} from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(store.login ? store.login().url : '/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="লগইন" />

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
                href="/"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-600 active:scale-95"
            >
                <ArrowLeft class="h-6 w-6" />
            </Link>
            <span class="text-lg font-bold text-slate-800">লগইন</span>
            <div class="w-10"></div>
            <!-- Spacer for center alignment -->
        </div>

        <!-- ========================================== -->
        <!-- MAIN CONTENT                               -->
        <!-- ========================================== -->
        <main class="flex w-full flex-1">
            <!-- LEFT COLUMN: Form Section -->
            <div
                class="flex w-full flex-col justify-center px-6 py-10 sm:px-8 lg:w-1/2 lg:flex-none lg:px-20 xl:px-24"
            >
                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <!-- Stylized Header (Matches Image Style) -->
                    <div class="mb-10 text-center lg:text-left">
                        <h2
                            class="text-4xl leading-tight font-black tracking-tight text-slate-900 lg:text-5xl"
                        >
                            আপনার অ্যাকাউন্টে <br />
                            <span
                                class="bg-linear-to-r from-blue-600 to-sky-500 bg-clip-text text-transparent"
                            >
                                লগইন করুন
                            </span>
                        </h2>
                        <p class="mt-4 text-sm font-medium text-slate-500">
                            স্বাগতম! আপনার অ্যাকাউন্টে প্রবেশ করতে তথ্য দিন।
                        </p>
                    </div>

                    <!-- Status Message -->
                    <div
                        v-if="status"
                        class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-medium text-emerald-700"
                    >
                        {{ status }}
                    </div>

                    <!-- INERTIA FORM -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email/Mobile Input -->
                        <div class="space-y-2">
                            <Label
                                for="email"
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
                                    id="email"
                                    type="text"
                                    name="email"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="username"
                                    placeholder="আপনার মোবাইল নাম্বার বা ইমেইল দিন"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 pl-11 text-base transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    :class="{
                                        'border-red-500 focus:ring-red-100':
                                            form.errors.email,
                                    }"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label
                                    for="password"
                                    class="text-sm font-bold text-slate-700"
                                    >পাসওয়ার্ড</Label
                                >
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800"
                                    :tabindex="5"
                                >
                                    ভুলে গেছেন?
                                </TextLink>
                            </div>
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
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 pl-11 text-base transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    :class="{
                                        'border-red-500 focus:ring-red-100':
                                            form.errors.password,
                                    }"
                                />
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <Checkbox
                                id="remember"
                                name="remember"
                                v-model:checked="form.remember"
                                :tabindex="3"
                                class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <Label
                                for="remember"
                                class="ml-2 block cursor-pointer text-sm font-medium text-slate-600"
                            >
                                আমাকে মনে রাখুন
                            </Label>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue text-base font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-hover hover:shadow-xl active:scale-[0.98]"
                            :tabindex="4"
                            :disabled="form.processing"
                            data-test="login-button"
                        >
                            <Spinner
                                v-if="form.processing"
                                class="h-5 w-5 text-white"
                            />
                            <span v-else>লগইন করুন</span>
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
                                >Or continue with</span
                            >
                        </div>
                    </div>

                    <!-- Social Login (Modern Grid) -->
                    <div class="grid grid-cols-2 gap-4">
                        <a
                            href="/auth/google"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95"
                        >
                            <Chrome class="h-5 w-5 text-orange-500" />
                            Google
                        </a>
                        <button
                            type="button"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95"
                        >
                            <Facebook class="h-5 w-5 text-[#1877F2]" />
                            Facebook
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div
                        class="mt-8 text-center text-sm font-medium text-slate-500"
                        v-if="canRegister"
                    >
                        এখনও অ্যাকাউন্ট নেই?
                        <TextLink
                            :href="register()"
                            :tabindex="5"
                            class="ml-1 font-bold text-blue-600 hover:text-blue-800 hover:underline"
                        >
                            ফ্রি রেজিস্ট্রেশন করুন
                        </TextLink>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Visual Section (Dark Theme Match) -->
            <div class="relative hidden w-0 flex-1 lg:block">
                <!-- Image -->
                <img
                    class="absolute inset-0 h-full w-full object-cover"
                    src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1998&auto=format&fit=crop"
                    alt="Library"
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
                            "বই হচ্ছে এমন এক মৌমাছি <br />
                            যা মেধার বাগানে মধু সংগ্রহ করে।"
                        </h3>
                        <p
                            class="mt-6 text-lg font-medium tracking-widest text-blue-200 uppercase"
                        >
                            — সংগৃহীত
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer (Hidden on Mobile for cleaner app look) -->
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
