<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'টু-ফ্যাক্টর অথেন্টিকেশন',
        href: show.url(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="টু-ফ্যাক্টর অথেন্টিকেশন" />
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="টু-ফ্যাক্টর অথেন্টিকেশন"
                    description="আপনার অ্যাকাউন্টের নিরাপত্তা বৃদ্ধিতে টু-ফ্যাক্টর অথেন্টিকেশন ব্যবহার করুন"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="destructive" class="rounded-full px-3 py-1"
                        >বন্ধ আছে</Badge
                    >

                    <p class="text-sm leading-relaxed text-slate-500">
                        আপনি যখন টু-ফ্যাক্টর অথেন্টিকেশন চালু করবেন, তখন লগইন
                        করার সময় আপনাকে একটি সিকিউর পিন দিতে হবে। এই পিনটি আপনি
                        আপনার ফোনের একটি TOTP-সাপোর্টেড অ্যাপ (যেমন: Google
                        Authenticator) থেকে পেতে পারেন।
                    </p>

                    <div class="pt-2">
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                            class="rounded-xl bg-blue px-6 py-6 font-bold text-white hover:bg-blue-hover"
                        >
                            <ShieldCheck class="mr-2 h-5 w-5" />সেটআপ চালিয়ে যান
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button
                                type="submit"
                                :disabled="processing"
                                class="rounded-xl bg-blue px-6 py-6 font-bold text-white hover:bg-blue-hover"
                            >
                                <ShieldCheck class="mr-2 h-5 w-5" />2FA চালু
                                করুন
                            </Button>
                        </Form>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge
                        variant="default"
                        class="rounded-full border-none bg-green-100 px-3 py-1 font-bold text-green-700 hover:bg-green-100"
                        >চালু আছে</Badge
                    >

                    <p class="text-sm leading-relaxed text-slate-500">
                        টু-ফ্যাক্টর অথেন্টিকেশন চালু থাকায়, লগইন করার সময় আপনাকে
                        একটি সিকিউর এবং র‍্যান্ডম পিন দিতে হবে, যা আপনি আপনার
                        ফোনের TOTP-সাপোর্টেড অ্যাপ থেকে সংগ্রহ করতে পারবেন।
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline pt-4">
                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                                class="rounded-xl px-6 py-6 font-bold"
                            >
                                <ShieldBan class="mr-2 h-5 w-5" />
                                2FA বন্ধ করুন
                            </Button>
                        </Form>
                    </div>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
