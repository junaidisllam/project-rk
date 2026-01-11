<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    AlertCircle,
    ChevronRight,
    Copy,
    Key,
    Loader2,
    Lock,
    Shield,
    ShieldAlert,
    ShieldCheck,
    User,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    twoFactorEnabled: Boolean,
    twoFactorConfirmed: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- State ---
const activeTab = ref('profile'); // profile, security, 2fa
const enabling = ref(false);
const disabling = ref(false);
const qrCode = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);
const setupKey = computed(() => page.props.auth.user?.two_factor_secret);
const isLoadingQr = ref(false);

// --- Forms ---
const confirmationForm = useForm({ code: '' });
const profileForm = useForm({ name: user.value.name, email: user.value.email });
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// --- Helpers ---
const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    // Add toast logic here if needed
};

// --- Actions ---
const updateProfile = () => {
    profileForm.patch(route('profile.update'), { preserveScroll: true });
};

const updatePassword = () => {
    passwordForm.patch(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const showQrCode = () => {
    isLoadingQr.value = true;
    return axios
        .get('/user/two-factor-qr-code')
        .then((response) => {
            qrCode.value = response.data.svg || response.data;
        })
        .finally(() => {
            isLoadingQr.value = false;
        });
};

const showRecoveryCodes = () => {
    return axios.get('/user/two-factor-recovery-codes').then((response) => {
        recoveryCodes.value = response.data;
    });
};

const enableTwoFactorAuthentication = () => {
    enabling.value = true;
    router.post(
        '/user/two-factor-authentication',
        {},
        {
            preserveScroll: true,
            onSuccess: () => Promise.all([showQrCode(), showRecoveryCodes()]),
            onFinish: () => {
                enabling.value = false;
            },
        },
    );
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post('/user/confirmed-two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            qrCode.value = null;
            confirmationForm.reset();
            showRecoveryCodes();
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios.post('/user/two-factor-recovery-codes').then(() => {
        showRecoveryCodes();
    });
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;
    router.delete('/user/two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            qrCode.value = null;
        },
    });
};

// --- Lifecycle ---
onMounted(() => {
    if (props.twoFactorEnabled && !props.twoFactorConfirmed) {
        showQrCode();
    }
});

watch(
    () => props.twoFactorEnabled,
    (val) => {
        if (!val) {
            qrCode.value = null;
            recoveryCodes.value = [];
            confirmationForm.reset();
        }
    },
);
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-slate-50 pb-20 font-sans md:pb-10">
            <!-- Header -->
            <div class="bg-white shadow-sm">
                <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-bold text-slate-900">
                        Account Settings
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Manage your profile, security, and authentication.
                    </p>
                </div>
            </div>

            <div class="mx-auto mt-6 max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- TABS -->
                <div
                    class="scrollbar-hide mb-6 flex gap-2 overflow-x-auto pb-2"
                >
                    <button
                        @click="activeTab = 'profile'"
                        class="flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold transition-all"
                        :class="
                            activeTab === 'profile'
                                ? 'bg-blue text-white shadow-lg shadow-blue/30'
                                : 'bg-white text-slate-600 hover:bg-slate-100'
                        "
                    >
                        <User class="h-4 w-4" />
                        Profile
                    </button>
                    <button
                        @click="activeTab = 'security'"
                        class="flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold transition-all"
                        :class="
                            activeTab === 'security'
                                ? 'bg-blue text-white shadow-lg shadow-blue/30'
                                : 'bg-white text-slate-600 hover:bg-slate-100'
                        "
                    >
                        <Lock class="h-4 w-4" />
                        Password
                    </button>
                    <button
                        @click="activeTab = '2fa'"
                        class="flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold transition-all"
                        :class="
                            activeTab === '2fa'
                                ? 'bg-blue text-white shadow-lg shadow-blue/30'
                                : 'bg-white text-slate-600 hover:bg-slate-100'
                        "
                    >
                        <ShieldCheck class="h-4 w-4" />
                        2FA Auth
                    </button>
                </div>

                <!-- CONTENT AREA -->
                <div class="transition-all duration-300">
                    <!-- 1. PROFILE TAB -->
                    <div
                        v-if="activeTab === 'profile'"
                        class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100"
                    >
                        <h2 class="mb-6 text-lg font-bold text-slate-800">
                            Personal Information
                        </h2>
                        <form @submit.prevent="updateProfile" class="space-y-5">
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-wider text-slate-500 uppercase"
                                    >Full Name</label
                                >
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-blue focus:bg-white focus:ring-0"
                                />
                                <p
                                    v-if="profileForm.errors.name"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ profileForm.errors.name }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-wider text-slate-500 uppercase"
                                    >Email Address</label
                                >
                                <input
                                    v-model="profileForm.email"
                                    type="email"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-blue focus:bg-white focus:ring-0"
                                />
                                <p
                                    v-if="profileForm.errors.email"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ profileForm.errors.email }}
                                </p>
                            </div>
                            <div class="flex justify-end pt-4">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="rounded-xl bg-blue px-8 py-3 text-sm font-bold text-white shadow-lg shadow-blue/30 transition hover:bg-blue-hover active:scale-95 disabled:opacity-70"
                                >
                                    {{
                                        profileForm.processing
                                            ? 'Saving...'
                                            : 'Save Changes'
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- 2. SECURITY TAB -->
                    <div
                        v-if="activeTab === 'security'"
                        class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100"
                    >
                        <h2 class="mb-6 text-lg font-bold text-slate-800">
                            Change Password
                        </h2>
                        <form
                            @submit.prevent="updatePassword"
                            class="space-y-5"
                        >
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-wider text-slate-500 uppercase"
                                    >Current Password</label
                                >
                                <input
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-blue focus:bg-white focus:ring-0"
                                    placeholder="••••••••"
                                />
                                <p
                                    v-if="passwordForm.errors.current_password"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold tracking-wider text-slate-500 uppercase"
                                        >New Password</label
                                    >
                                    <input
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-blue focus:bg-white focus:ring-0"
                                    />
                                    <p
                                        v-if="passwordForm.errors.password"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ passwordForm.errors.password }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold tracking-wider text-slate-500 uppercase"
                                        >Confirm New Password</label
                                    >
                                    <input
                                        v-model="
                                            passwordForm.password_confirmation
                                        "
                                        type="password"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-blue focus:bg-white focus:ring-0"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end pt-4">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="rounded-xl bg-slate-900 px-8 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800 active:scale-95 disabled:opacity-70"
                                >
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- 3. 2FA TAB -->
                    <div
                        v-if="activeTab === '2fa'"
                        class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100"
                    >
                        <!-- Status Banner -->
                        <div
                            class="mb-8 flex items-center justify-between rounded-2xl p-5"
                            :class="
                                twoFactorEnabled
                                    ? 'bg-green-50 text-green-800'
                                    : 'bg-slate-50 text-slate-800'
                            "
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm"
                                >
                                    <ShieldCheck
                                        v-if="twoFactorEnabled"
                                        class="h-6 w-6 text-green-600"
                                    />
                                    <ShieldAlert
                                        v-else
                                        class="h-6 w-6 text-slate-400"
                                    />
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold md:text-base">
                                        {{
                                            twoFactorEnabled
                                                ? 'Two-Factor Authentication is ON'
                                                : 'Two-Factor Authentication is OFF'
                                        }}
                                    </h3>
                                    <p class="mt-0.5 text-xs opacity-80">
                                        {{
                                            twoFactorEnabled
                                                ? 'Your account is extra secure.'
                                                : 'Add extra security to your account.'
                                        }}
                                    </p>
                                </div>
                            </div>
                            <!-- Toggle Button (Visual only, triggers logic below) -->
                            <div v-if="!twoFactorEnabled">
                                <button
                                    @click="enableTwoFactorAuthentication"
                                    :disabled="enabling"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg transition hover:bg-slate-800 active:scale-90"
                                >
                                    <ChevronRight class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Main 2FA Content -->
                        <div v-if="twoFactorEnabled" class="space-y-8">
                            <!-- STEP 1: QR CODE (If not confirmed) -->
                            <div
                                v-if="!twoFactorConfirmed"
                                class="rounded-2xl border border-slate-200 p-6 text-center"
                            >
                                <h3 class="mb-4 font-bold text-slate-800">
                                    Finish Setup
                                </h3>
                                <p class="mb-6 text-sm text-slate-500">
                                    Scan this QR code with your Authenticator
                                    App (Google/Microsoft Auth).
                                </p>

                                <div
                                    v-if="isLoadingQr"
                                    class="flex justify-center py-8"
                                >
                                    <Loader2
                                        class="h-10 w-10 animate-spin text-blue"
                                    />
                                </div>
                                <div v-else-if="qrCode" class="mx-auto w-fit">
                                    <div
                                        class="rounded-xl border-4 border-white bg-white p-2 shadow-sm"
                                        v-html="qrCode"
                                    ></div>
                                </div>

                                <!-- Manual Key -->
                                <div class="mt-6">
                                    <p
                                        class="mb-2 text-xs font-bold text-slate-400 uppercase"
                                    >
                                        Or enter setup key
                                    </p>
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <code
                                            class="rounded-lg bg-slate-100 px-3 py-1.5 font-mono text-sm font-bold text-slate-700"
                                            >{{ setupKey }}</code
                                        >
                                        <button
                                            @click="copyToClipboard(setupKey)"
                                            class="rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-blue"
                                        >
                                            <Copy class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Confirmation Input -->
                                <div
                                    class="mt-8 border-t border-slate-100 pt-6"
                                >
                                    <form
                                        @submit.prevent="
                                            confirmTwoFactorAuthentication
                                        "
                                        class="mx-auto max-w-sm"
                                    >
                                        <label
                                            class="mb-2 block text-sm font-bold text-slate-700"
                                            >Enter Code from App</label
                                        >
                                        <div class="flex gap-2">
                                            <input
                                                v-model="confirmationForm.code"
                                                type="text"
                                                inputmode="numeric"
                                                class="w-full rounded-xl border-slate-200 bg-white py-3 text-center text-lg font-bold tracking-widest text-slate-900 focus:border-blue focus:ring-blue"
                                                placeholder="123 456"
                                            />
                                            <button
                                                type="submit"
                                                :disabled="
                                                    confirmationForm.processing
                                                "
                                                class="rounded-xl bg-blue px-6 font-bold text-white shadow-md hover:bg-blue-hover disabled:opacity-70"
                                            >
                                                Confirm
                                            </button>
                                        </div>
                                        <p
                                            v-if="confirmationForm.errors.code"
                                            class="mt-2 text-xs text-red-500"
                                        >
                                            {{ confirmationForm.errors.code }}
                                        </p>
                                    </form>
                                </div>
                            </div>

                            <!-- STEP 2: RECOVERY CODES -->
                            <div
                                v-if="
                                    twoFactorConfirmed &&
                                    recoveryCodes.length > 0
                                "
                            >
                                <div
                                    class="rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200"
                                >
                                    <div
                                        class="mb-4 flex items-center justify-between"
                                    >
                                        <h3
                                            class="flex items-center gap-2 font-bold text-slate-800"
                                        >
                                            <Key
                                                class="h-4 w-4 text-slate-500"
                                            />
                                            Recovery Codes
                                        </h3>
                                        <button
                                            @click="regenerateRecoveryCodes"
                                            class="text-xs font-bold text-blue hover:underline"
                                        >
                                            Regenerate
                                        </button>
                                    </div>
                                    <div
                                        class="grid grid-cols-2 gap-3 sm:grid-cols-4"
                                    >
                                        <div
                                            v-for="code in recoveryCodes"
                                            :key="code"
                                            class="rounded-lg bg-white px-2 py-2 text-center font-mono text-xs font-bold text-slate-600 shadow-sm ring-1 ring-slate-100"
                                        >
                                            {{ code }}
                                        </div>
                                    </div>
                                    <p class="mt-4 text-xs text-slate-400">
                                        * Save these codes in a secure place.
                                        You can use them to login if you lose
                                        access to your phone.
                                    </p>
                                </div>
                            </div>

                            <!-- ACTIONS -->
                            <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                                <button
                                    v-if="
                                        twoFactorConfirmed &&
                                        recoveryCodes.length === 0
                                    "
                                    @click="showRecoveryCodes"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Show Recovery Codes
                                </button>

                                <button
                                    @click="disableTwoFactorAuthentication"
                                    :disabled="disabling"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-red-100 bg-red-50 px-5 py-3 text-sm font-bold text-red-600 transition hover:bg-red-100 hover:text-red-700 active:scale-[0.98]"
                                >
                                    <AlertCircle class="h-4 w-4" />
                                    Disable 2FA
                                </button>
                            </div>
                        </div>

                        <!-- Empty State for Non-Enabled -->
                        <div v-else class="py-6 text-center">
                            <div
                                class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-50"
                            >
                                <Shield class="h-8 w-8 text-slate-300" />
                            </div>
                            <h3 class="mb-2 font-bold text-slate-900">
                                Secure Your Account
                            </h3>
                            <p
                                class="mx-auto mb-6 max-w-sm text-sm text-slate-500"
                            >
                                Enabling 2FA adds a significant layer of
                                security. We highly recommend turning it on.
                            </p>

                            <button
                                @click="enableTwoFactorAuthentication"
                                :disabled="enabling"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Enable Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
