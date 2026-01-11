<script setup lang="ts">
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    ChevronRight,
    Lock,
    MapPin,
    Palette,
    Smartphone,
    User,
} from 'lucide-vue-next';

const sidebarNavItems: NavItem[] = [
    {
        title: 'প্রোফাইল তথ্য',
        href: editProfile(),
        icon: User,
    },
    {
        title: 'সিকিউরিটি / পাসওয়ার্ড',
        href: editPassword(),
        icon: Lock,
    },
    {
        title: 'ঠিকানা সমূহ',
        href: '/addresses',
        icon: MapPin,
    },
    {
        title: 'টু-ফ্যাক্টর অথেন্টিকেশন',
        href: show(),
        icon: Smartphone,
    },
    {
        title: 'অ্যাপিয়ারেন্স (ডার্ক মোড)',
        href: editAppearance(),
        icon: Palette,
    },
];

const currentPath =
    typeof window !== 'undefined' ? window.location.pathname : '';
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-black tracking-tight text-slate-800">
                অ্যাকাউন্ট সেটিংস
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                আপনার ব্যক্তিগত তথ্য এবং সিকিউরিটি ম্যানেজ করুন
            </p>
        </div>

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="mb-8 w-full lg:mb-0 lg:w-64">
                <nav class="flex flex-col space-y-2">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        :href="item.href"
                        class="group flex items-center justify-between rounded-xl px-4 py-3 text-sm font-bold transition-all duration-200"
                        :class="[
                            urlIsActive(item.href, currentPath)
                                ? 'bg-blue text-white shadow-lg shadow-blue/20'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-blue',
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <component
                                :is="item.icon"
                                class="h-4.5 w-4.5"
                                :class="
                                    urlIsActive(item.href, currentPath)
                                        ? 'text-white'
                                        : 'text-slate-400 group-hover:text-blue'
                                "
                            />
                            <span>{{ item.title }}</span>
                        </div>
                        <ChevronRight
                            v-if="urlIsActive(item.href, currentPath)"
                            class="h-4 w-4 opacity-50"
                        />
                    </Link>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div
                class="flex-1 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
                <div class="p-6 md:p-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
