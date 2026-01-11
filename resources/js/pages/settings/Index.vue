<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AppLayout>
        <Head title="সেটিংস" />
        <SettingsLayout>
            <div class="py-12 text-center">
                <p class="text-slate-500">সেটিংস লোড হচ্ছে...</p>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
