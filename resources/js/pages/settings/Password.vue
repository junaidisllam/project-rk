<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import { Form, Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'পাসওয়ার্ড সেটিংস',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="পাসওয়ার্ড সেটিংস" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="পাসওয়ার্ড পরিবর্তন করুন"
                    description="আপনার অ্যাকাউন্ট সুরক্ষিত রাখতে একটি দীর্ঘ এবং শক্তিশালী পাসওয়ার্ড ব্যবহার করুন"
                />

                <Form
                    v-bind="PasswordController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label
                            for="current_password"
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >বর্তমান পাসওয়ার্ড</Label
                        >
                        <Input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-6 transition-all focus:bg-white"
                            autocomplete="current-password"
                            placeholder="বর্তমান পাসওয়ার্ড লিখুন"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label
                            for="password"
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >নতুন পাসওয়ার্ড</Label
                        >
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-6 transition-all focus:bg-white"
                            autocomplete="new-password"
                            placeholder="নতুন পাসওয়ার্ড লিখুন"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label
                            for="password_confirmation"
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >পাসওয়ার্ড নিশ্চিত করুন</Label
                        >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-6 transition-all focus:bg-white"
                            autocomplete="new-password"
                            placeholder="পাসওয়ার্ড পুনরায় লিখুন"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <Button
                            :disabled="processing"
                            class="rounded-xl bg-blue px-8 py-6 font-bold text-white shadow-lg shadow-blue/20 transition-all hover:bg-blue-hover active:scale-95"
                            data-test="update-password-button"
                        >
                            পাসওয়ার্ড আপডেট করুন
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm font-bold text-green-600"
                            >
                                সফলভাবে আপডেট হয়েছে।
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
