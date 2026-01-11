<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import { useCartStore } from '@/Stores/cart';
import { router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ChevronDown,
    ChevronRight,
    ChevronUp,
    Loader2,
    MapPin,
    Plus,
    Save,
    Search,
    Truck,
    User,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, reactive, ref } from 'vue';
import { route } from 'ziggy-js';

// --- Types ---
interface LocationItem {
    id: string;
    name: string;
    bn_name: string;
    url?: string;
}

const props = defineProps<{
    addresses?: any[];
    isLoggedIn: boolean;
    user?: any;
}>();

// --- Store ---
const cartStore = useCartStore();

// --- State ---
const isMobile = ref(false); // For responsive logic
const showPriceDetails = ref(false); // Mobile bottom sheet toggle
const locationModal = reactive({
    isOpen: false,
    type: '' as 'division' | 'district' | 'upazila' | 'union' | '',
    title: '',
    items: [] as LocationItem[],
    search: '',
    isLoading: false,
    transitionName: 'slide-left',
});

const addressDetailsInput = ref<HTMLTextAreaElement | null>(null);

const locationOptions = reactive({
    divisions: [] as LocationItem[],
    districts: [] as LocationItem[],
    upazilas: [] as LocationItem[],
    unions: [] as LocationItem[],
});

const apiBase = 'https://sohojapi.vercel.app/api';

// --- Address Management State ---
const isAddressModalOpen = ref(false);
const editingAddressId = ref<number | null>(null);
const isLoadingLocation = ref(false);
const showNewAddressForm = ref(false); // Toggle for logged in users

const addressForm = useForm({
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

// --- Checkout Form ---
const form = useForm({
    // Guest Fields
    email: '',
    password: 'guest(99))',
    password_confirmation: 'guest(99))',

    // Address Fields
    name: '',
    phone: '',
    altPhone: '',
    divisionId: '',
    divisionName: '',
    districtId: '',
    districtName: '',
    upazilaId: '',
    upazilaName: '',
    unionId: '',
    unionName: '',
    addressDetails: '',
    addressId: null as number | null,

    paymentMethod: 'cod',
    agreeToTerms: true,
    create_account: false, // Legacy field, now implicit for guests

    cartItems: [] as any[],
    subtotal: 0,
    shippingCharge: 0,
    totalPrice: 0,
});

// --- Computed ---
const subtotal = computed(() => cartStore.subtotal);
const cartItems = computed(() => cartStore.selectedItems || []);

const shippingInfo = computed(() => {
    const district = form.districtName ? form.districtName.toLowerCase() : '';
    if (!district) return { cost: 0, label: 'লোকেশন নির্বাচন করুন' };

    // Check English or Bengali spelling of Dhaka
    const isDhaka = district.includes('dhaka') || district.includes('ঢাকা');
    return isDhaka
        ? { cost: 60, label: 'ঢাকার ভিতরে' }
        : { cost: 120, label: 'ঢাকার বাইরে' };
});

const shippingCost = computed(() => shippingInfo.value.cost);
const totalAmount = computed(() => subtotal.value + shippingCost.value);
const formatPrice = (value: number) => '৳ ' + value.toLocaleString('en-BD');

// --- Helper Functions ---
const fetchData = async (url: string) => {
    try {
        const res = await fetch(url);
        return res.ok ? await res.json() : [];
    } catch (e) {
        return [];
    }
};

const deleteAddress = (addressId: number) => {
    if (confirm('Are you sure you want to delete this address?')) {
        router.delete(route('addresses.destroy', addressId), {
            preserveScroll: true,
        });
    }
};

const openAddressModal = async (address: any = null) => {
    addressForm.reset();
    addressForm.clearErrors();
    editingAddressId.value = address ? address.id : null;
    isLoadingLocation.value = true;
    isAddressModalOpen.value = true;

    // Load divisions if not loaded
    if (locationOptions.divisions.length === 0) {
        locationOptions.divisions = await fetchData(`${apiBase}/divisions`);
    }

    if (address) {
        // Pre-fill form
        addressForm.name = address.name;
        addressForm.phone = address.phone;
        addressForm.alt_phone = address.alt_phone;
        addressForm.division_id = address.division_id;
        addressForm.division_name = address.division_name;
        addressForm.district_id = address.district_id;
        addressForm.district_name = address.district_name;
        addressForm.upazila_id = address.upazila_id;
        addressForm.upazila_name = address.upazila_name;
        addressForm.union_id = address.union_id;
        addressForm.union_name = address.union_name;
        addressForm.address_details = address.address_details;
        addressForm.is_default = !!address.is_default;

        // Fetch dependent data
        if (address.division_id)
            locationOptions.districts = await fetchData(
                `${apiBase}/districts/${address.division_id}`,
            );
        if (address.district_id)
            locationOptions.upazilas = await fetchData(
                `${apiBase}/upzilas/${address.district_id}`,
            );
        if (address.upazila_id)
            locationOptions.unions = await fetchData(
                `${apiBase}/unions/${address.upazila_id}`,
            );
    } else {
        // Reset sub-levels
        locationOptions.districts = [];
        locationOptions.upazilas = [];
        locationOptions.unions = [];
    }

    isLoadingLocation.value = false;
};

const saveAddress = () => {
    if (editingAddressId.value) {
        addressForm.put(route('addresses.update', editingAddressId.value), {
            onSuccess: () => (isAddressModalOpen.value = false),
        });
    } else {
        addressForm.post(route('addresses.store'), {
            onSuccess: () => (isAddressModalOpen.value = false),
        });
    }
};

// --- Address Form Location Handlers ---
const handleAddrDivisionChange = async (event: any) => {
    const id = event.target.value;
    const item = locationOptions.divisions.find((i: any) => i.id == id);
    addressForm.division_id = id;
    addressForm.division_name = item?.bn_name || item?.name;

    addressForm.district_id = '';
    addressForm.district_name = '';
    addressForm.upazila_id = '';
    addressForm.upazila_name = '';
    addressForm.union_id = null;
    addressForm.union_name = null;

    locationOptions.districts = await fetchData(`${apiBase}/districts/${id}`);
};

const handleAddrDistrictChange = async (event: any) => {
    const id = event.target.value;
    const item = locationOptions.districts.find((i: any) => i.id == id);
    addressForm.district_id = id;
    addressForm.district_name = item?.bn_name || item?.name;

    addressForm.upazila_id = '';
    addressForm.upazila_name = '';
    addressForm.union_id = null;
    addressForm.union_name = null;

    locationOptions.upazilas = await fetchData(`${apiBase}/upzilas/${id}`);
};

const handleAddrUpazilaChange = async (event: any) => {
    const id = event.target.value;
    const item = locationOptions.upazilas.find((i: any) => i.id == id);
    addressForm.upazila_id = id;
    addressForm.upazila_name = item?.bn_name || item?.name;
    addressForm.union_id = null;
    addressForm.union_name = null;

    locationOptions.unions = await fetchData(`${apiBase}/unions/${id}`);
};

const handleAddrUnionChange = (event: any) => {
    const id = event.target.value;
    const item = locationOptions.unions.find((i: any) => i.id == id);
    addressForm.union_id = id;
    addressForm.union_name = item?.bn_name || item?.name;
};

const filterList = (list: LocationItem[], query: string) => {
    if (!query) return list;
    const lowerQ = query.toLowerCase();
    return list.filter(
        (item) =>
            item.name.toLowerCase().includes(lowerQ) ||
            item.bn_name.includes(lowerQ),
    );
};

// --- Address Selection ---
const selectAddress = async (address: any) => {
    showNewAddressForm.value = false;
    form.addressId = address.id; // Set ID for backend reuse
    form.name = address.name;
    form.phone = address.phone;
    form.altPhone = address.alt_phone; // Note: camelCase in form, snake_case in DB

    // Auto-fill email if logged in user has one (though backend handles it)
    if (props.user && props.user.email) {
        form.email = props.user.email;
    }

    // Location Mapping
    form.divisionId = address.division_id;
    form.divisionName = address.division_name;
    form.districtId = address.district_id;
    form.districtName = address.district_name;
    form.upazilaId = address.upazila_id;
    form.upazilaName = address.upazila_name;
    form.unionId = address.union_id;
    form.unionName = address.union_name;

    form.addressDetails = address.address_details;

    // We might need to fetch dependent location data (districts/upazilas)
    // to ensure the dropdowns work correctly if user change them
    // But since we set ID and Name, it might be fine for display.
    // Ideally we should populate locationOptions too.

    // Populate dropdown options for seamless editing if needed
    if (address.division_id) {
        locationOptions.districts = await fetchData(
            `${apiBase}/districts/${address.division_id}`,
        );
    }
    if (address.district_id) {
        locationOptions.upazilas = await fetchData(
            `${apiBase}/districts/${address.district_id}`, // Note: API endpoint correction to districts if needed, but original was districts for districts list?
            // Wait, apiBase/districts/ID returns districts under division ID.
            // apiBase/upzilas/ID returns upazilas under district ID.
        );
        locationOptions.upazilas = await fetchData(
            `${apiBase}/upzilas/${address.district_id}`,
        );
    }
    if (address.upazila_id) {
        locationOptions.unions = await fetchData(
            `${apiBase}/unions/${address.upazila_id}`,
        );
    }
};

// --- Modal Logic (The App-Like Experience) ---
const openLocationModal = async (type: typeof locationModal.type) => {
    // Validation chain
    if (type === 'district' && !form.divisionId)
        return alert('প্রথমে বিভাগ নির্বাচন করুন');
    if (type === 'upazila' && !form.districtId)
        return alert('প্রথমে জেলা নির্বাচন করুন');
    if (type === 'union' && !form.upazilaId)
        return alert('প্রথমে উপজেলা নির্বাচন করুন');

    locationModal.type = type;
    locationModal.search = '';
    locationModal.isOpen = true;

    // Set Data source and Title
    switch (type) {
        case 'division':
            locationModal.title = 'বিভাগ নির্বাচন করুন';
            locationModal.items = locationOptions.divisions;
            break;
        case 'district':
            locationModal.title = 'জেলা নির্বাচন করুন';
            locationModal.items = locationOptions.districts;
            break;
        case 'upazila':
            locationModal.title = 'উপজেলা নির্বাচন করুন';
            locationModal.items = locationOptions.upazilas;
            break;
        case 'union':
            locationModal.title = 'এলাকা নির্বাচন করুন';
            locationModal.items = locationOptions.unions;
            break;
    }
};

const handleSelection = async (item: LocationItem) => {
    const type = locationModal.type;
    form[`${type}Id`] = item.id;
    form[`${type}Name`] = item.bn_name || item.name;

    // Define next step map
    const nextStepMap: Record<
        string,
        'division' | 'district' | 'upazila' | 'union' | null
    > = {
        division: 'district',
        district: 'upazila',
        upazila: 'union',
        union: null,
    };

    const nextType = nextStepMap[type];

    if (nextType) {
        // Prepare for transition
        locationModal.isLoading = true;
        locationModal.transitionName = 'slide-left'; // Always slide left (forward)

        try {
            // Fetch next data
            let nextItems = [];
            let nextTitle = '';

            if (nextType === 'district') {
                nextItems = await fetchData(`${apiBase}/districts/${item.id}`);
                nextTitle = 'জেলা নির্বাচন করুন';
                locationOptions.districts = nextItems;

                // Reset child fields
                form.districtId = '';
                form.districtName = '';
                form.upazilaId = '';
                form.upazilaName = '';
                form.unionId = '';
                form.unionName = '';
            } else if (nextType === 'upazila') {
                nextItems = await fetchData(`${apiBase}/upzilas/${item.id}`);
                nextTitle = 'উপজেলা নির্বাচন করুন';
                locationOptions.upazilas = nextItems;

                // Reset child fields
                form.upazilaId = '';
                form.upazilaName = '';
                form.unionId = '';
                form.unionName = '';
            } else if (nextType === 'union') {
                nextItems = await fetchData(`${apiBase}/unions/${item.id}`);
                nextTitle = 'এলাকা নির্বাচন করুন';
                locationOptions.unions = nextItems;

                // Reset child fields
                form.unionId = '';
                form.unionName = '';
            }

            // Update Modal State (retain open state)
            locationModal.type = nextType;
            locationModal.title = nextTitle;
            locationModal.items = nextItems;
            locationModal.search = '';
        } catch (error) {
            console.error('Failed to fetch location data', error);
        } finally {
            // Small delay to allow transition effect to be noticeable if needed,
            // but usually strictly after fetch is better.
            // We use nextTick or just set loading false.
            locationModal.isLoading = false;
        }
    } else {
        // End of the line (Union selected)
        locationModal.isOpen = false;
        // Auto-focus on address details
        setTimeout(() => {
            addressDetailsInput.value?.focus();
        }, 300);
    }
};

// --- Lifecycle ---
onMounted(async () => {
    locationOptions.divisions = await fetchData(`${apiBase}/divisions`);
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);

    // Auto-select default address
    if (props.addresses && props.addresses.length > 0) {
        const defaultAddress = props.addresses.find((a) => a.is_default);
        if (defaultAddress) {
            selectAddress(defaultAddress);
        }
    }
});

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 1024;
};

// --- Submit ---
const isSubmitting = ref(false);
const handleOrder = () => {
    isSubmitting.value = true;
    form.cartItems = cartStore.items.map((item) => ({ ...item }));
    form.subtotal = subtotal.value;
    form.shippingCharge = shippingCost.value;
    form.totalPrice = totalAmount.value;

    form.post(route('checkout.store'), {
        onSuccess: () => {
            cartStore.clearCart();
            isSubmitting.value = false;
        },
        onError: (errors) => {
            isSubmitting.value = false;
            if (form.agreeToTerms === false) alert('শর্তাবলী মেনে নিন');

            // Scroll to error
            const firstError = Object.keys(errors)[0];
            const el = document.querySelector(`[data-error="${firstError}"]`);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },
    });
};
</script>

<template>
    <!-- Desktop Header -->
    <div class="hidden lg:block">
        <Heading />
    </div>

    <!-- MOBILE APP HEADER -->
    <div
        class="fixed top-0 right-0 left-0 z-40 flex h-14 items-center bg-white px-4 shadow-sm lg:hidden"
    >
        <button
            @click="$inertia.visit('/cart')"
            class="mr-3 rounded-full p-1 active:bg-slate-100"
        >
            <ArrowLeft class="size-6 text-slate-700" />
        </button>
        <h1 class="text-lg font-bold text-slate-800">চেকআউট</h1>
    </div>

    <!-- MAIN CONTAINER -->
    <div
        class="min-h-screen bg-[#F4F6F8] pt-16 pb-40 font-sans text-slate-900 lg:pt-0 lg:pb-20"
    >
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                <!-- LEFT COLUMN: FORMS -->
                <div class="flex-1 space-y-5">
                    <!-- Login Banner (Only for Guests) -->
                    <div
                        v-if="!isLoggedIn"
                        class="flex items-center justify-between overflow-hidden rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 transition-all hover:shadow-md"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex size-11 items-center justify-center rounded-2xl bg-linear-to-br from-blue/10 to-blue/5 text-blue shadow-inner"
                            >
                                <User class="size-6" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-extrabold text-slate-800"
                                >
                                    একাউন্ট আছে?
                                </p>
                                <p
                                    class="text-[11px] leading-relaxed text-slate-500"
                                >
                                    সহজে অর্ডার ট্র্যাক করতে লগইন করুন
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('login')"
                            class="rounded-xl bg-blue px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue/20 transition-all hover:-translate-y-0.5 active:scale-95"
                        >
                            লগইন
                        </Link>
                    </div>

                    <!-- Shipping Address Card -->
                    <div
                        class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 lg:p-8"
                    >
                        <div
                            class="mb-6 flex items-center gap-2 border-b border-slate-50 pb-4"
                        >
                            <MapPin class="size-5 text-blue" />
                            <h2 class="text-lg font-bold text-slate-800">
                                শিপিং তথ্য
                            </h2>
                        </div>

                        <!-- Saved Addresses (Only for Logged In) -->
                        <div
                            v-if="
                                isLoggedIn && addresses && addresses.length > 0
                            "
                            class="mb-8"
                        >
                            <h3
                                class="mb-3 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Saved Addresses
                            </h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div
                                    v-for="addr in addresses"
                                    :key="addr.id"
                                    @click="selectAddress(addr)"
                                    class="group relative cursor-pointer overflow-hidden rounded-2xl border p-4 transition-all hover:scale-[1.01] hover:bg-slate-50"
                                    :class="
                                        form.addressId === addr.id
                                            ? 'border-blue bg-blue/5 shadow-lg ring-1 shadow-blue/5 ring-blue'
                                            : 'border-slate-200 bg-white'
                                    "
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div class="space-y-1">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <p
                                                    class="text-sm font-black text-slate-800"
                                                >
                                                    {{ addr.name }}
                                                </p>
                                                <span
                                                    v-if="addr.is_default"
                                                    class="rounded-full bg-blue/10 px-1.5 py-0.5 text-[9px] font-bold text-blue uppercase"
                                                    >Default</span
                                                >
                                            </div>
                                            <p
                                                class="text-xs font-semibold text-slate-500"
                                            >
                                                {{ addr.phone }}
                                            </p>
                                            <p
                                                class="mt-3 line-clamp-2 text-[11px] leading-relaxed text-slate-600"
                                            >
                                                {{ addr.address_details }}<br />
                                                <span class="text-slate-400">
                                                    {{
                                                        addr.union_name
                                                            ? addr.union_name +
                                                              ', '
                                                            : ''
                                                    }}
                                                    {{ addr.upazila_name }},
                                                    {{ addr.district_name }}
                                                </span>
                                            </p>
                                        </div>
                                        <div
                                            v-if="form.addressId === addr.id"
                                            class="flex size-5 items-center justify-center rounded-full bg-blue text-white shadow-md shadow-blue/40"
                                        >
                                            <Check class="size-3" />
                                        </div>
                                    </div>
                                </div>
                                <button
                                    @click="
                                        showNewAddressForm = !showNewAddressForm
                                    "
                                    class="group flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-4 transition-all hover:border-blue hover:bg-blue/5 hover:text-blue"
                                >
                                    <div
                                        class="flex size-8 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-100 group-hover:ring-blue/30"
                                    >
                                        <Plus
                                            v-if="!showNewAddressForm"
                                            class="size-5"
                                        />
                                        <X v-else class="size-5" />
                                    </div>
                                    <span class="text-xs font-black">{{
                                        showNewAddressForm
                                            ? 'Cancel New'
                                            : 'Add New Address'
                                    }}</span>
                                </button>
                            </div>

                            <Transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                                enter-to-class="transform scale-100 opacity-100 translate-y-0"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="transform scale-100 opacity-100 translate-y-0"
                                leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                            >
                                <div
                                    v-if="!showNewAddressForm"
                                    class="mt-5 flex items-center gap-3 rounded-2xl bg-linear-to-r from-emerald-50 to-teal-50 p-4 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100"
                                >
                                    <div
                                        class="flex size-6 items-center justify-center rounded-full bg-white shadow-sm"
                                    >
                                        <Check
                                            class="size-3.5 stroke-2 text-emerald-500"
                                        />
                                    </div>
                                    <p>
                                        ঠিকানা সিলেক্ট করা হয়েছে! নিচের পেমেন্ট
                                        মেথড দিয়ে অর্ডার সম্পন্ন করুন।
                                    </p>
                                </div>
                            </Transition>
                        </div>

                        <!-- ADDRESS FORM (Shown if Guest, No Saved Address, or Explicitly Adding New) -->
                        <Transition
                            enter-active-class="transition duration-500 ease-out"
                            enter-from-class="transform -translate-y-4 opacity-0"
                            enter-to-class="transform translate-y-0 opacity-100"
                            leave-active-class="transition duration-300 ease-in"
                            leave-from-class="transform translate-y-0 opacity-100"
                            leave-to-class="transform -translate-y-4 opacity-0"
                        >
                            <div
                                v-if="
                                    !isLoggedIn ||
                                    addresses?.length === 0 ||
                                    showNewAddressForm
                                "
                                class="grid gap-6 md:grid-cols-2"
                            >
                                <!-- Guest Registration Fields -->
                                <!-- Guest Registration Fields (Hidden/Auto-filled) -->
                                <!-- We just show a message or nothing, since creation is automatic with default password -->
                                <div v-if="!isLoggedIn" class="mb-4">
                                    <p
                                        class="text-xs font-medium text-blue-600"
                                    >
                                        আপনার ফোন নম্বর দিয়েই অটোমেটিক একাউন্ট
                                        তৈরি হয়ে যাবে।
                                    </p>
                                </div>
                                <!-- Name -->
                                <div class="md:col-span-2" data-error="name">
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >আপনার নাম
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="পুরো নাম লিখুন"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition-all outline-none focus:border-blue focus:ring-1 focus:ring-blue"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500':
                                                form.errors.name,
                                        }"
                                    />
                                    <span
                                        v-if="form.errors.name"
                                        class="mt-1 text-[11px] font-medium text-red-500"
                                        >{{ form.errors.name }}</span
                                    >
                                </div>

                                <!-- Phone -->
                                <div data-error="phone">
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >মোবাইল নাম্বার
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="01XXXXXXXXX"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition-all outline-none focus:border-blue focus:ring-1 focus:ring-blue"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500':
                                                form.errors.phone,
                                        }"
                                    />
                                    <span
                                        v-if="form.errors.phone"
                                        class="mt-1 text-[11px] font-medium text-red-500"
                                        >{{ form.errors.phone }}</span
                                    >
                                </div>

                                <!-- Alt Phone (Shows only if Phone is filled) -->
                                <div
                                    v-if="form.phone && form.phone.length >= 11"
                                    class="animate-in duration-300 fade-in slide-in-from-top-2"
                                >
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >বিকল্প নাম্বার (অপশনাল)</label
                                    >
                                    <input
                                        v-model="form.altPhone"
                                        type="tel"
                                        placeholder="অন্য একটি নাম্বার"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition-all outline-none focus:border-blue focus:ring-1 focus:ring-blue"
                                    />
                                </div>

                                <!-- LOCATION SELECTORS (Modal Triggers) -->
                                <!-- Division -->
                                <div data-error="divisionId">
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >বিভাগ
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div
                                        @click="openLocationModal('division')"
                                        class="flex w-full cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition-all hover:bg-slate-50 active:bg-slate-100"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500':
                                                form.errors.divisionId,
                                        }"
                                    >
                                        <span
                                            :class="
                                                form.divisionName
                                                    ? 'text-slate-800'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            {{
                                                form.divisionName ||
                                                'বিভাগ নির্বাচন করুন'
                                            }}
                                        </span>
                                        <ChevronDown
                                            class="size-4 text-slate-400"
                                        />
                                    </div>
                                    <span
                                        v-if="form.errors.divisionId"
                                        class="mt-1 text-[11px] font-medium text-red-500"
                                        >{{ form.errors.divisionId }}</span
                                    >
                                </div>

                                <!-- District (Shows only if Division is selected) -->
                                <div
                                    v-if="form.divisionId"
                                    data-error="districtId"
                                    class="animate-in duration-300 fade-in slide-in-from-top-2"
                                >
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >জেলা
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div
                                        @click="openLocationModal('district')"
                                        class="flex w-full cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition-all hover:bg-slate-50 active:bg-slate-100"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500':
                                                form.errors.districtId,
                                        }"
                                    >
                                        <span
                                            :class="
                                                form.districtName
                                                    ? 'text-slate-800'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            {{
                                                form.districtName ||
                                                'জেলা নির্বাচন করুন'
                                            }}
                                        </span>
                                        <ChevronDown
                                            class="size-4 text-slate-400"
                                        />
                                    </div>
                                    <span
                                        v-if="form.errors.districtId"
                                        class="mt-1 text-[11px] font-medium text-red-500"
                                        >{{ form.errors.districtId }}</span
                                    >
                                </div>

                                <!-- Upazila (Shows only if District is selected) -->
                                <div
                                    v-if="form.districtId"
                                    data-error="upazilaId"
                                    class="animate-in duration-300 fade-in slide-in-from-top-2"
                                >
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >উপজেলা / থানা
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div
                                        @click="openLocationModal('upazila')"
                                        class="flex w-full cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition-all hover:bg-slate-50 active:bg-slate-100"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500':
                                                form.errors.upazilaId,
                                        }"
                                    >
                                        <span
                                            :class="
                                                form.upazilaName
                                                    ? 'text-slate-800'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            {{
                                                form.upazilaName ||
                                                'উপজেলা নির্বাচন করুন'
                                            }}
                                        </span>
                                        <ChevronDown
                                            class="size-4 text-slate-400"
                                        />
                                    </div>
                                    <span
                                        v-if="form.errors.upazilaId"
                                        class="mt-1 text-[11px] font-medium text-red-500"
                                        >{{ form.errors.upazilaId }}</span
                                    >
                                </div>

                                <!-- Union (Shows only if Upazila is selected) -->
                                <div
                                    v-if="form.upazilaId"
                                    data-error="unionId"
                                    class="animate-in duration-300 fade-in slide-in-from-top-2"
                                >
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >এলাকা / ইউনিয়ন
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div
                                        @click="openLocationModal('union')"
                                        class="flex w-full cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition-all hover:bg-slate-50 active:bg-slate-100"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500':
                                                form.errors.unionId,
                                        }"
                                    >
                                        <span
                                            :class="
                                                form.unionName
                                                    ? 'text-slate-800'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            {{
                                                form.unionName ||
                                                'এলাকা নির্বাচন করুন'
                                            }}
                                        </span>
                                        <ChevronDown
                                            class="size-4 text-slate-400"
                                        />
                                    </div>
                                    <span
                                        v-if="form.errors.unionId"
                                        class="mt-1 text-[11px] font-medium text-red-500"
                                        >{{ form.errors.unionId }}</span
                                    >
                                </div>

                                <!-- Address Textarea -->
                                <div
                                    class="md:col-span-2"
                                    data-error="addressDetails"
                                >
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-600"
                                        >বিস্তারিত ঠিকানা
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <textarea
                                        ref="addressDetailsInput"
                                        v-model="form.addressDetails"
                                        rows="2"
                                        placeholder="বাড়ি নং, রোড নং, ল্যান্ডমার্ক..."
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition-all outline-none focus:border-blue focus:ring-1 focus:ring-blue"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500':
                                                form.errors.addressDetails,
                                        }"
                                    ></textarea>
                                    <span
                                        v-if="form.errors.addressDetails"
                                        class="mt-1 text-[11px] font-medium text-red-500"
                                        >{{ form.errors.addressDetails }}</span
                                    >
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Account Creation Checkbox (Legacy - Removed as implicit now) -->
                    <!-- <div class="mt-6 border-t border-slate-50 pt-4"> -->
                    <!-- </div> -->

                    <!-- Payment Method -->
                    <div
                        class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"
                    >
                        <h2 class="mb-4 text-lg font-bold text-slate-800">
                            পেমেন্ট মেথড
                        </h2>
                        <div
                            class="relative flex cursor-pointer items-center justify-between rounded-xl border border-blue bg-blue/5 p-4"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-white shadow-sm"
                                >
                                    <Truck class="size-5 text-blue" />
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">
                                        ক্যাশ অন ডেলিভারি
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex size-5 items-center justify-center rounded-full border-2 border-blue bg-blue"
                            >
                                <div class="size-2 rounded-full bg-white"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: SUMMARY (Desktop Sticky) -->
                <div class="hidden w-[400px] lg:block">
                    <div
                        class="sticky top-24 overflow-hidden rounded-3xl bg-white p-6 shadow-xl ring-1 shadow-slate-200/50 ring-slate-100"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-xl font-black text-slate-900">
                                অর্ডার সামারি
                            </h2>
                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-500 uppercase"
                                >{{ cartItems.length }} Items</span
                            >
                        </div>

                        <!-- Items List -->
                        <div
                            class="custom-scrollbar -mr-2 mb-6 max-h-[400px] space-y-4 overflow-y-auto pr-2"
                        >
                            <div
                                v-for="item in cartItems"
                                :key="item.id"
                                class="group flex gap-4 rounded-2xl border border-slate-50 bg-slate-50/30 p-3 transition-colors hover:bg-slate-50"
                            >
                                <div
                                    class="relative size-16 shrink-0 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-100"
                                >
                                    <img
                                        :src="
                                            item.cover_photo
                                                ? `/storage/${item.cover_photo}`
                                                : undefined
                                        "
                                        class="size-full object-cover transition-transform group-hover:scale-110"
                                    />
                                </div>
                                <div
                                    class="flex flex-1 flex-col justify-between py-0.5"
                                >
                                    <h3
                                        class="line-clamp-1 text-sm font-bold text-slate-800 transition-colors group-hover:text-blue"
                                    >
                                        {{ item.name }}
                                    </h3>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-[11px] font-semibold text-slate-400"
                                        >
                                            {{ item.quantity }} ×
                                            {{ formatPrice(item.price) }}
                                        </span>
                                        <span
                                            class="text-sm font-black text-slate-900"
                                        >
                                            {{
                                                formatPrice(
                                                    item.price * item.quantity,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calculations Table -->
                        <div
                            class="space-y-3 border-t border-dashed border-slate-200 pt-6"
                        >
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-slate-500"
                                    >সাবটোটাল</span
                                >
                                <span class="font-bold text-slate-900">{{
                                    formatPrice(subtotal)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-slate-500"
                                    >শিপিং চার্জ</span
                                >
                                <div class="text-right">
                                    <span
                                        v-if="shippingCost > 0"
                                        class="font-bold text-slate-900"
                                        >{{ formatPrice(shippingCost) }}</span
                                    >
                                    <span
                                        v-else
                                        class="rounded-lg bg-blue/10 px-2 py-1 text-[10px] font-black text-blue"
                                        >লোকেশন দিন</span
                                    >
                                </div>
                            </div>

                            <!-- Total Divider -->
                            <div
                                class="my-4 h-px bg-linear-to-r from-transparent via-slate-200 to-transparent"
                            ></div>

                            <div class="flex items-end justify-between">
                                <div class="flex flex-col">
                                    <span
                                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >সর্বমোট</span
                                    >
                                    <span
                                        class="text-2xl font-black text-slate-900"
                                        >{{ formatPrice(totalAmount) }}</span
                                    >
                                </div>
                                <div
                                    class="pb-1 text-[10px] font-bold text-emerald-500"
                                >
                                    VAT Included
                                </div>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <div class="mt-8 space-y-4">
                            <div data-error="agreeToTerms">
                                <label class="group flex cursor-pointer gap-3">
                                    <div class="relative mt-0.5">
                                        <input
                                            type="checkbox"
                                            v-model="form.agreeToTerms"
                                            class="peer sr-only"
                                        />
                                        <div
                                            class="size-5 rounded-md border-2 border-slate-200 bg-white transition-all peer-checked:border-blue peer-checked:bg-blue"
                                        ></div>
                                        <Check
                                            class="absolute inset-0 size-5 scale-0 stroke-2 text-white transition-transform peer-checked:scale-75"
                                        />
                                    </div>
                                    <span
                                        class="text-[11px] leading-relaxed font-medium text-slate-500 group-hover:text-slate-700"
                                    >
                                        আমি
                                        <span
                                            class="font-bold text-blue underline decoration-blue/30 underline-offset-4"
                                            >শর্তাবলী</span
                                        >
                                        ও রিফান্ড পলিসি মেনে অর্ডার করছি।
                                    </span>
                                </label>
                            </div>

                            <button
                                @click="handleOrder"
                                :disabled="isSubmitting"
                                class="relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-blue py-4 font-black text-white shadow-xl shadow-blue/20 transition-all hover:-translate-y-1 hover:shadow-blue/30 active:scale-95 disabled:bg-slate-200 disabled:shadow-none"
                            >
                                <span v-if="!isSubmitting"
                                    >অর্ডার কনফার্ম করুন</span
                                >
                                <span v-else class="flex items-center gap-2">
                                    <Loader2 class="size-5 animate-spin" />
                                    প্রসেসিং...
                                </span>
                                <div
                                    class="absolute inset-0 -translate-x-full bg-linear-to-r from-white/0 via-white/10 to-white/0 transition-transform duration-1000 group-hover:translate-x-full"
                                ></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MOBILE STICKY BOTTOM BAR (App Style) -->
    <div
        class="fixed right-0 bottom-0 left-0 z-50 rounded-t-2xl border-t border-slate-100 bg-white px-4 py-3 shadow-[0_-5px_25px_rgba(0,0,0,0.1)] lg:hidden"
    >
        <!-- Collapsible Price Details -->
        <div
            v-if="showPriceDetails"
            class="absolute right-0 bottom-full left-0 animate-in rounded-t-2xl border-t border-slate-100 bg-white p-4 shadow-lg slide-in-from-bottom-5"
        >
            <div class="mb-4 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>সাবটোটাল</span
                    ><span>{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>শিপিং</span
                    ><span>{{
                        shippingCost > 0 ? formatPrice(shippingCost) : '0'
                    }}</span>
                </div>
            </div>
        </div>

        <div class="mx-auto flex max-w-md items-center justify-between gap-4">
            <div
                @click="showPriceDetails = !showPriceDetails"
                class="flex cursor-pointer flex-col"
            >
                <div
                    class="flex items-center gap-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                >
                    সর্বমোট
                    <ChevronUp
                        class="size-3 transition-transform"
                        :class="{ 'rotate-180': showPriceDetails }"
                    />
                </div>
                <span class="text-xl font-black text-slate-900">{{
                    formatPrice(totalAmount)
                }}</span>
            </div>

            <button
                @click="handleOrder"
                :disabled="isSubmitting"
                class="flex-1 rounded-xl bg-blue py-3.5 text-sm font-bold text-white shadow-lg shadow-blue/20 active:scale-95 disabled:bg-slate-300"
            >
                {{ isSubmitting ? 'প্রসেসিং...' : 'অর্ডার করুন' }}
            </button>
        </div>
    </div>

    <!-- NATIVE-LIKE LOCATION MODAL (Bottom Sheet on Mobile, Modal on Desktop) -->
    <Transition name="modal">
        <div
            v-if="locationModal.isOpen"
            class="fixed inset-0 z-100 flex items-end justify-center sm:items-center"
        >
            <!-- Backdrop -->
            <div
                @click="locationModal.isOpen = false"
                class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            ></div>

            <!-- Modal Content -->
            <div
                class="relative flex h-[85vh] w-full max-w-md animate-in flex-col rounded-t-3xl bg-white shadow-2xl slide-in-from-bottom sm:h-auto sm:max-h-[80vh] sm:rounded-2xl"
            >
                <!-- Modal Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-100 p-4"
                >
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ locationModal.title }}
                    </h3>
                    <button
                        @click="locationModal.isOpen = false"
                        class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <!-- Search -->
                <div class="p-4 pb-2">
                    <div class="relative">
                        <Search
                            class="absolute top-3 left-3 size-4 text-slate-400"
                        />
                        <input
                            v-model="locationModal.search"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-4 pl-10 text-sm outline-none focus:border-blue focus:bg-white"
                            placeholder="খুঁজুন..."
                            autoFocus
                        />
                    </div>
                </div>

                <!-- List -->
                <div
                    class="relative flex-1 overflow-x-hidden overflow-y-auto p-2"
                >
                    <div
                        v-if="locationModal.isLoading"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-white"
                    >
                        <Loader2 class="size-8 animate-spin text-blue" />
                    </div>

                    <Transition
                        :name="locationModal.transitionName"
                        mode="out-in"
                    >
                        <ul :key="locationModal.type" class="w-full space-y-1">
                            <li
                                v-for="item in filterList(
                                    locationModal.items,
                                    locationModal.search,
                                )"
                                :key="item.id"
                                @click="handleSelection(item)"
                                class="flex cursor-pointer items-center justify-between rounded-xl p-3 hover:bg-slate-50 active:bg-blue/5"
                            >
                                <span class="font-medium text-slate-700">{{
                                    item.bn_name || item.name
                                }}</span>
                                <ChevronRight class="size-4 text-slate-300" />
                            </li>
                        </ul>
                    </Transition>

                    <div
                        v-if="
                            !locationModal.isLoading &&
                            filterList(
                                locationModal.items,
                                locationModal.search,
                            ).length === 0
                        "
                        class="py-10 text-center text-sm text-slate-400"
                    >
                        কোন ফলাফল পাওয়া যায় নি
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <div class="hidden lg:block">
        <Footer />
    </div>
    <!-- Address Management Modal -->
    <div v-if="isAddressModalOpen" class="relative z-50">
        <div
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="isAddressModalOpen = false"
        ></div>
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-0 md:p-4"
        >
            <div
                class="flex h-full w-full transform flex-col bg-white shadow-xl transition-all md:h-auto md:max-w-2xl md:rounded-2xl"
            >
                <!-- Modal Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4"
                >
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-800">
                            {{
                                editingAddressId
                                    ? 'Edit Address'
                                    : 'New Address'
                            }}
                        </h2>
                        <Loader2
                            v-if="isLoadingLocation"
                            class="h-4 w-4 animate-spin text-blue-600"
                        />
                    </div>
                    <button
                        @click="isAddressModalOpen = false"
                        class="rounded-full p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <X class="h-6 w-6" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <form
                        @submit.prevent="saveAddress"
                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                    >
                        <!-- Contact Info -->
                        <div class="md:col-span-2">
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Contact Details
                            </h3>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >Full Name</label
                            >
                            <input
                                type="text"
                                v-model="addressForm.name"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue"
                                placeholder="e.g. John Doe"
                            />
                            <p
                                v-if="addressForm.errors.name"
                                class="mt-1 text-[11px] font-medium text-red-500"
                            >
                                {{ addressForm.errors.name }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >Phone</label
                            >
                            <input
                                type="text"
                                v-model="addressForm.phone"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue"
                                placeholder="e.g. 017..."
                            />
                            <p
                                v-if="addressForm.errors.phone"
                                class="mt-1 text-[11px] font-medium text-red-500"
                            >
                                {{ addressForm.errors.phone }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >Alt Phone
                                <span class="font-normal text-slate-400"
                                    >(Optional)</span
                                ></label
                            >
                            <input
                                type="text"
                                v-model="addressForm.alt_phone"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue"
                            />
                        </div>

                        <!-- Location Info -->
                        <div class="mt-2 md:col-span-2">
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Location Area
                            </h3>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >Division</label
                            >
                            <div class="relative">
                                <select
                                    :value="addressForm.division_id"
                                    @change="handleAddrDivisionChange"
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue"
                                >
                                    <option value="" disabled>
                                        Select Division
                                    </option>
                                    <option
                                        v-for="div in locationOptions.divisions"
                                        :key="div.id"
                                        :value="div.id"
                                    >
                                        {{ div.bn_name || div.name }}
                                    </option>
                                </select>
                                <ChevronDown
                                    class="pointer-events-none absolute top-1/2 right-4 size-4 -translate-y-1/2 text-slate-400"
                                />
                            </div>
                            <p
                                v-if="addressForm.errors.division_id"
                                class="mt-1 text-[11px] font-medium text-red-500"
                            >
                                {{ addressForm.errors.division_id }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >District</label
                            >
                            <div class="relative">
                                <select
                                    :value="addressForm.district_id"
                                    @change="handleAddrDistrictChange"
                                    :disabled="!addressForm.division_id"
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue disabled:bg-slate-50"
                                >
                                    <option value="" disabled>
                                        Select District
                                    </option>
                                    <option
                                        v-for="dist in locationOptions.districts"
                                        :key="dist.id"
                                        :value="dist.id"
                                    >
                                        {{ dist.bn_name || dist.name }}
                                    </option>
                                </select>
                                <ChevronDown
                                    class="pointer-events-none absolute top-1/2 right-4 size-4 -translate-y-1/2 text-slate-400"
                                />
                            </div>
                            <p
                                v-if="addressForm.errors.district_id"
                                class="mt-1 text-[11px] font-medium text-red-500"
                            >
                                {{ addressForm.errors.district_id }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >Upazila</label
                            >
                            <div class="relative">
                                <select
                                    :value="addressForm.upazila_id"
                                    @change="handleAddrUpazilaChange"
                                    :disabled="!addressForm.district_id"
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue disabled:bg-slate-50"
                                >
                                    <option value="" disabled>
                                        Select Upazila
                                    </option>
                                    <option
                                        v-for="upz in locationOptions.upazilas"
                                        :key="upz.id"
                                        :value="upz.id"
                                    >
                                        {{ upz.bn_name || upz.name }}
                                    </option>
                                </select>
                                <ChevronDown
                                    class="pointer-events-none absolute top-1/2 right-4 size-4 -translate-y-1/2 text-slate-400"
                                />
                            </div>
                            <p
                                v-if="addressForm.errors.upazila_id"
                                class="mt-1 text-[11px] font-medium text-red-500"
                            >
                                {{ addressForm.errors.upazila_id }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >Union
                                <span class="font-normal text-slate-400"
                                    >(Optional)</span
                                ></label
                            >
                            <div class="relative">
                                <select
                                    :value="addressForm.union_id"
                                    @change="handleAddrUnionChange"
                                    :disabled="!addressForm.upazila_id"
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue disabled:bg-slate-50"
                                >
                                    <option :value="null">Select Union</option>
                                    <option
                                        v-for="uni in locationOptions.unions"
                                        :key="uni.id"
                                        :value="uni.id"
                                    >
                                        {{ uni.bn_name || uni.name }}
                                    </option>
                                </select>
                                <ChevronDown
                                    class="pointer-events-none absolute top-1/2 right-4 size-4 -translate-y-1/2 text-slate-400"
                                />
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="md:col-span-2">
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-600"
                                >Address Details</label
                            >
                            <textarea
                                v-model="addressForm.address_details"
                                rows="3"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue"
                                placeholder="e.g. House 12, Road 5..."
                            ></textarea>
                            <p
                                v-if="addressForm.errors.address_details"
                                class="mt-1 text-[11px] font-medium text-red-500"
                            >
                                {{ addressForm.errors.address_details }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="inline-flex cursor-pointer items-center"
                            >
                                <input
                                    type="checkbox"
                                    v-model="addressForm.is_default"
                                    class="h-5 w-5 rounded border-slate-300 text-blue focus:ring-blue"
                                />
                                <span class="ml-2 text-sm text-slate-800"
                                    >Set as default</span
                                >
                            </label>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div
                    class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 md:rounded-b-2xl"
                >
                    <button
                        @click="isAddressModalOpen = false"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveAddress"
                        :disabled="addressForm.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue px-6 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <Loader2
                            v-if="addressForm.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        <Save v-else class="h-4 w-4" />
                        {{
                            editingAddressId ? 'Update Address' : 'Save Address'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

/* Slide Transition Types */
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
    transition: all 0.3s ease-in-out;
}

.slide-left-enter-from {
    transform: translateX(100%);
    opacity: 0;
}
.slide-left-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

.slide-right-enter-from {
    transform: translateX(-100%);
    opacity: 0;
}
.slide-right-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
</style>
