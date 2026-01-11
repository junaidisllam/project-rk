<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'অ্যাপিয়ারেন্স সেটিংস',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="অ্যাপিয়ারেন্স সেটিংস" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="অ্যাপিয়ারেন্স সেটিংস"
                    description="আপনার অ্যাকাউন্টের অ্যাপিয়ারেন্স সেটিংস আপডেট করুন (ডার্ক/লাইট মোড)"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
