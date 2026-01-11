<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    Briefcase,
    Check,
    ChevronDown,
    Edit3,
    Home,
    Loader2,
    MapPin,
    Phone,
    Plus,
    Trash2,
    X,
} from 'lucide-vue-next';
import { reactive, ref } from 'vue';

const props = defineProps<{
    addresses: any[];
}>();

// --- API Configuration ---
const apiBase = 'https://sohojapi.vercel.app/api';

// --- State ---
const isModalOpen = ref(false);
const editingAddress = ref(null);
const isLoadingLocation = ref(false);

const locationOptions = reactive({
    divisions: [],
    districts: [],
    upazilas: [],
    unions: [],
});

const form = useForm({
    name: '',
    phone: '',
    alt_phone: '',
    division_id: '',
    division_name: '',
    district_id: '',
    district_name: '',
    upazila_id: '',
    upazila_name: '',
    union_id: null,
    union_name: null,
    address_details: '',
    is_default: false,
});

// --- API Helpers ---
const fetchData = async (url: string) => {
    try {
        const res = await fetch(url);
        return res.ok ? await res.json() : [];
    } catch (e) {
        console.error('Location fetch error:', e);
        return [];
    }
};

// --- Form Actions ---
const openAddForm = async () => {
    editingAddress.value = null;
    form.reset();
    form.clearErrors();

    if (locationOptions.divisions.length === 0) {
        isLoadingLocation.value = true;
        locationOptions.divisions = await fetchData(`${apiBase}/divisions`);
        isLoadingLocation.value = false;
    }

    locationOptions.districts = [];
    locationOptions.upazilas = [];
    locationOptions.unions = [];

    isModalOpen.value = true;
};

const openEditForm = async (address: any) => {
    editingAddress.value = address;
    isLoadingLocation.value = true;
    isModalOpen.value = true;

    if (locationOptions.divisions.length === 0) {
        locationOptions.divisions = await fetchData(`${apiBase}/divisions`);
    }
    if (address.division_id) {
        locationOptions.districts = await fetchData(
            `${apiBase}/districts/${address.division_id}`,
        );
    }
    if (address.district_id) {
        locationOptions.upazilas = await fetchData(
            `${apiBase}/upzilas/${address.district_id}`,
        );
    }
    if (address.upazila_id) {
        locationOptions.unions = await fetchData(
            `${apiBase}/unions/${address.upazila_id}`,
        );
    }

    form.name = address.name;
    form.phone = address.phone;
    form.alt_phone = address.alt_phone;
    form.division_id = address.division_id;
    form.division_name = address.division_name;
    form.district_id = address.district_id;
    form.district_name = address.district_name;
    form.upazila_id = address.upazila_id;
    form.upazila_name = address.upazila_name;
    form.union_id = address.union_id;
    form.union_name = address.union_name;
    form.address_details = address.address_details;
    form.is_default = address.is_default;

    isLoadingLocation.value = false;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

// --- Location Handlers ---
const handleDivisionChange = async (event: any) => {
    const id = event.target.value;
    const item = locationOptions.divisions.find((i: any) => i.id == id);

    form.division_id = id;
    form.division_name = item?.bn_name || item?.name;

    form.district_id = '';
    form.district_name = '';
    form.upazila_id = '';
    form.upazila_name = '';
    form.union_id = null;
    form.union_name = null;

    locationOptions.districts = await fetchData(`${apiBase}/districts/${id}`);
    locationOptions.upazilas = [];
    locationOptions.unions = [];
};

const handleDistrictChange = async (event: any) => {
    const id = event.target.value;
    const item = locationOptions.districts.find((i: any) => i.id == id);

    form.district_id = id;
    form.district_name = item?.bn_name || item?.name;

    form.upazila_id = '';
    form.upazila_name = '';
    form.union_id = null;
    form.union_name = null;

    locationOptions.upazilas = await fetchData(`${apiBase}/upzilas/${id}`);
    locationOptions.unions = [];
};

const handleUpazilaChange = async (event: any) => {
    const id = event.target.value;
    const item = locationOptions.upazilas.find((i: any) => i.id == id);

    form.upazila_id = id;
    form.upazila_name = item?.bn_name || item?.name;

    form.union_id = null;
    form.union_name = null;

    locationOptions.unions = await fetchData(`${apiBase}/unions/${id}`);
};

const handleUnionChange = (event: any) => {
    const id = event.target.value;
    const item = locationOptions.unions.find((i: any) => i.id == id);
    form.union_id = id;
    form.union_name = item?.bn_name || item?.name;
};

// --- Submission ---
const saveAddress = () => {
    if (editingAddress.value) {
        form.put(route('addresses.update', editingAddress.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('addresses.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteAddress = (addressId: number) => {
    if (confirm('Are you sure you want to delete this address?')) {
        router.delete(route('addresses.destroy', addressId), {
            preserveScroll: true,
        });
    }
};

const setDefaultAddress = (addressId: number) => {
    router.patch(route('addresses.setDefault', addressId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-slate-50 pb-24 md:pb-10">
            <!-- ======================= -->
            <!-- 1. APP HEADER SECTION   -->
            <!-- ======================= -->
            <div
                class="relative overflow-hidden bg-blue pb-24 shadow-lg lg:rounded-b-[2.5rem]"
            >
                <!-- Abstract Decor -->
                <div
                    class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"
                ></div>
                <div
                    class="absolute top-10 -left-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"
                ></div>

                <div class="relative z-10 mx-auto max-w-5xl px-6 pt-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1
                                class="text-2xl font-bold tracking-wide text-white"
                            >
                                ঠিকানা সমূহ
                            </h1>
                            <p class="mt-1 text-sm text-blue-100 opacity-90">
                                আপনার ডেলিভারি লোকেশন ম্যানেজ করুন
                            </p>
                        </div>

                        <!-- Desktop Add Button -->
                        <button
                            @click="openAddForm"
                            class="hidden items-center gap-2 rounded-xl bg-white/10 px-5 py-2.5 text-sm font-bold text-white ring-1 ring-white/30 backdrop-blur-md transition-all hover:bg-white/20 active:scale-95 md:inline-flex"
                        >
                            <Plus class="h-5 w-5" />
                            নতুন ঠিকানা
                        </button>
                    </div>
                </div>
            </div>

            <!-- ======================= -->
            <!-- 2. ADDRESS LIST GRID    -->
            <!-- ======================= -->
            <div class="relative z-20 mx-auto -mt-16 max-w-5xl px-4">
                <!-- Address List -->
                <div
                    v-if="addresses.length > 0"
                    class="grid grid-cols-1 gap-5 md:grid-cols-2"
                >
                    <div
                        v-for="address in addresses"
                        :key="address.id"
                        class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg hover:shadow-blue/5"
                        :class="{ 'ring-2 ring-blue': address.is_default }"
                    >
                        <!-- Default Badge -->
                        <div
                            v-if="address.is_default"
                            class="absolute top-0 right-0 rounded-bl-xl bg-blue px-3 py-1 text-[10px] font-bold tracking-wider text-white uppercase shadow-sm"
                        >
                            Default
                        </div>

                        <div>
                            <!-- Header -->
                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-blue/10 text-blue"
                                >
                                    <component
                                        :is="
                                            address.union_name
                                                ? Home
                                                : Briefcase
                                        "
                                        class="h-6 w-6"
                                    />
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800">
                                        {{ address.name }}
                                    </h3>
                                    <p
                                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                                    >
                                        {{
                                            address.union_name
                                                ? 'Home'
                                                : 'Office / Other'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-3 pl-1">
                                <div class="flex items-start gap-3">
                                    <MapPin
                                        class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                    />
                                    <p
                                        class="text-sm leading-relaxed text-slate-600"
                                    >
                                        <span
                                            class="font-medium text-slate-800"
                                            >{{ address.address_details }}</span
                                        ><br />
                                        <span class="text-xs text-slate-500">
                                            {{
                                                address.union_name
                                                    ? address.union_name + ', '
                                                    : ''
                                            }}
                                            {{ address.upazila_name }},
                                            {{ address.district_name }}
                                        </span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <Phone
                                        class="h-4 w-4 shrink-0 text-slate-400"
                                    />
                                    <p
                                        class="font-mono text-sm font-bold tracking-tight text-slate-700"
                                    >
                                        {{ address.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Footer -->
                        <div
                            class="mt-6 flex items-center justify-between border-t border-slate-50 pt-4"
                        >
                            <button
                                v-if="!address.is_default"
                                @click="setDefaultAddress(address.id)"
                                class="flex items-center gap-1.5 text-xs font-bold text-slate-400 transition-colors hover:text-blue"
                            >
                                <Check class="h-3.5 w-3.5" />
                                ডিফল্ট করুন
                            </button>
                            <span v-else class="text-xs font-bold text-blue"
                                >ডিফল্ট ঠিকানা</span
                            >

                            <div class="flex gap-2">
                                <button
                                    @click="openEditForm(address)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition-colors hover:bg-amber-100"
                                    title="Edit"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deleteAddress(address.id)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 transition-colors hover:bg-red-100"
                                    title="Delete"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center shadow-sm"
                >
                    <div
                        class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 shadow-inner"
                    >
                        <MapPin class="h-10 w-10 text-slate-300" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">
                        কোনো ঠিকানা পাওয়া যায়নি
                    </h3>
                    <p class="mt-2 max-w-xs text-sm text-slate-500">
                        আপনার পণ্য ডেলিভারির জন্য সঠিক ঠিকানা যুক্ত করুন।
                    </p>
                    <button
                        @click="openAddForm"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue/30 transition-all hover:-translate-y-0.5 hover:bg-blue-hover active:scale-95"
                    >
                        <Plus class="h-5 w-5" />
                        প্রথম ঠিকানা যুক্ত করুন
                    </button>
                </div>
            </div>

            <!-- Mobile FAB (Floating Action Button) -->
            <button
                @click="openAddForm"
                class="fixed right-5 bottom-24 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-blue text-white shadow-lg shadow-blue/40 transition-transform hover:scale-110 active:scale-90 md:hidden"
            >
                <Plus class="h-7 w-7" />
            </button>

            <!-- ======================= -->
            <!-- 3. SMART MODAL FORM     -->
            <!-- ======================= -->
            <div v-if="isModalOpen" class="relative z-50">
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"
                    @click="closeModal"
                ></div>

                <!-- Modal Content -->
                <div
                    class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
                >
                    <div
                        class="flex h-[90vh] w-full flex-col rounded-t-[2rem] bg-white shadow-2xl transition-transform duration-300 sm:h-auto sm:max-h-[85vh] sm:max-w-2xl sm:rounded-3xl"
                    >
                        <!-- Modal Header -->
                        <div
                            class="flex shrink-0 items-center justify-between border-b border-slate-100 px-6 py-5"
                        >
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">
                                    {{
                                        editingAddress
                                            ? 'ঠিকানা পরিবর্তন'
                                            : 'নতুন ঠিকানা'
                                    }}
                                </h2>
                                <p
                                    class="text-xs text-slate-500"
                                    v-if="isLoadingLocation"
                                >
                                    লোকেশন লোড হচ্ছে...
                                    <Loader2
                                        class="inline h-3 w-3 animate-spin"
                                    />
                                </p>
                            </div>
                            <button
                                @click="closeModal"
                                class="rounded-full bg-slate-50 p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-500"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Modal Body (Scrollable) -->
                        <div class="scrollbar-hide flex-1 overflow-y-auto p-6">
                            <form
                                @submit.prevent="saveAddress"
                                class="grid grid-cols-1 gap-5 md:grid-cols-2"
                            >
                                <!-- Contact Section -->
                                <div class="md:col-span-2">
                                    <div class="mb-2 flex items-center gap-2">
                                        <div
                                            class="h-6 w-1 rounded-full bg-blue"
                                        ></div>
                                        <h3
                                            class="text-sm font-bold text-slate-800"
                                        >
                                            যোগাযোগের তথ্য
                                        </h3>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >পুরো নাম</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.name"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 focus:border-blue focus:bg-white focus:ring-0"
                                        :class="{
                                            'border-red-500 bg-red-50':
                                                form.errors.name,
                                        }"
                                        placeholder="আপনার নাম লিখুন"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="mt-1 text-[10px] font-bold text-red-500"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >মোবাইল নম্বর</label
                                    >
                                    <input
                                        type="tel"
                                        v-model="form.phone"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 focus:border-blue focus:bg-white focus:ring-0"
                                        :class="{
                                            'border-red-500 bg-red-50':
                                                form.errors.phone,
                                        }"
                                        placeholder="017..."
                                    />
                                    <p
                                        v-if="form.errors.phone"
                                        class="mt-1 text-[10px] font-bold text-red-500"
                                    >
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >বিকল্প মোবাইল (ঐচ্ছিক)</label
                                    >
                                    <input
                                        type="tel"
                                        v-model="form.alt_phone"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 focus:border-blue focus:bg-white focus:ring-0"
                                    />
                                </div>

                                <!-- Location Section -->
                                <div class="mt-2 md:col-span-2">
                                    <div class="mb-2 flex items-center gap-2">
                                        <div
                                            class="h-6 w-1 rounded-full bg-blue"
                                        ></div>
                                        <h3
                                            class="text-sm font-bold text-slate-800"
                                        >
                                            এলাকার তথ্য
                                        </h3>
                                    </div>
                                </div>

                                <!-- Dropdowns -->
                                <div
                                    v-for="(label, key) in {
                                        division: 'বিভাগ',
                                        district: 'জেলা',
                                        upazila: 'থানা / উপজেলা',
                                        union: 'ইউনিয়ন (ঐচ্ছিক)',
                                    }"
                                    :key="key"
                                >
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >{{ label }}</label
                                    >
                                    <div class="relative">
                                        <select
                                            :value="form[`${key}_id`]"
                                            @change="
                                                key === 'division'
                                                    ? handleDivisionChange(
                                                          $event,
                                                      )
                                                    : key === 'district'
                                                      ? handleDistrictChange(
                                                            $event,
                                                        )
                                                      : key === 'upazila'
                                                        ? handleUpazilaChange(
                                                              $event,
                                                          )
                                                        : handleUnionChange(
                                                              $event,
                                                          )
                                            "
                                            :disabled="
                                                key !== 'division' &&
                                                !form[
                                                    `${key === 'district' ? 'division' : key === 'upazila' ? 'district' : 'upazila'}_id`
                                                ]
                                            "
                                            class="w-full appearance-none rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 focus:border-blue focus:ring-0 disabled:bg-slate-100 disabled:text-slate-400"
                                        >
                                            <option
                                                :value="
                                                    key === 'union' ? null : ''
                                                "
                                                disabled
                                            >
                                                নির্বাচন করুন
                                            </option>
                                            <option
                                                v-for="opt in locationOptions[
                                                    `${key}s`
                                                ]"
                                                :key="opt.id"
                                                :value="opt.id"
                                            >
                                                {{ opt.bn_name || opt.name }}
                                            </option>
                                        </select>
                                        <ChevronDown
                                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        />
                                    </div>
                                </div>

                                <!-- Specific Address -->
                                <div class="mt-2 md:col-span-2">
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >বিস্তারিত ঠিকানা</label
                                    >
                                    <textarea
                                        v-model="form.address_details"
                                        rows="2"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-blue focus:bg-white focus:ring-0"
                                        placeholder="বাড়ির নাম/নম্বর, রোড নম্বর, এলাকা..."
                                    ></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="form.is_default"
                                            class="h-5 w-5 rounded border-slate-300 text-blue focus:ring-blue"
                                        />
                                        <span
                                            class="text-sm font-bold text-slate-700"
                                            >ডিফল্ট ঠিকানা হিসেবে সেভ করুন</span
                                        >
                                    </label>
                                </div>
                            </form>
                        </div>

                        <!-- Modal Footer -->
                        <div
                            class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-100 bg-white px-6 py-4 sm:rounded-b-3xl"
                        >
                            <button
                                @click="closeModal"
                                class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-50"
                            >
                                বাতিল
                            </button>
                            <button
                                @click="saveAddress"
                                :disabled="form.processing"
                                class="flex items-center gap-2 rounded-xl bg-blue px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue/30 transition hover:bg-blue-hover active:scale-95 disabled:opacity-70"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="h-4 w-4 animate-spin"
                                />
                                <span v-else>{{
                                    editingAddress ? 'আপডেট করুন' : 'সেভ করুন'
                                }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Hide Scrollbar in Modal Body */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
