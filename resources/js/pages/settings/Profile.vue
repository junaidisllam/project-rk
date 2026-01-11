<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'প্রোফাইল সেটিংস',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="প্রোফাইল সেটিংস" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="প্রোফাইল তথ্য"
                    description="আপনার নাম এবং ইমেইল ঠিকানা আপডেট করুন"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label
                            for="name"
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >পুরো নাম</Label
                        >
                        <Input
                            id="name"
                            class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-6 transition-all focus:bg-white"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="পুরো নাম লিখুন"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label
                            for="email"
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >ইমেইল ঠিকানা</Label
                        >
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-6 transition-all focus:bg-white"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="ইমেইল ঠিকানা লিখুন"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-slate-500">
                            আপনার ইমেইল ঠিকানাটি ভেরিফাই করা নেই।
                            <Link
                                :href="send()"
                                as="button"
                                class="font-bold text-blue underline decoration-blue/30 underline-offset-4 transition-all hover:decoration-blue"
                            >
                                পুনরায় ভেরিফিকেশন লিঙ্ক পাঠাতে এখানে ক্লিক করুন।
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-bold text-green-600"
                        >
                            আপনার ইমেইল ঠিকানায় একটি নতুন ভেরিফিকেশন লিঙ্ক
                            পাঠানো হয়েছে।
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <Button
                            :disabled="processing"
                            class="rounded-xl bg-blue px-8 py-6 font-bold text-white shadow-lg shadow-blue/20 transition-all hover:bg-blue-hover active:scale-95"
                            data-test="update-profile-button"
                        >
                            সেভ করুন
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
                                সফলভাবে সংরক্ষিত হয়েছে।
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <div class="mt-12 border-t border-slate-100 pt-12">
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
