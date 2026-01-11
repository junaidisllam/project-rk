<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar,
    CheckCircle,
    ChevronDown,
    CreditCard,
    MapPin,
    Package,
    RefreshCw,
    ShoppingBag,
    Truck,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    orders: any[];
}>();

const openOrder = ref<number | null>(null);

const toggleOrder = (orderId: number) => {
    openOrder.value = openOrder.value === orderId ? null : orderId;
};

// Status Colors (Keeping semantic colors for logic, but styling is modern)
const getStatusClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'processing':
            return 'bg-blue/10 text-blue border-blue/20'; // Using your brand blue tint
        case 'completed':
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'cancelled':
            return 'bg-red-100 text-red-700 border-red-200';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'pending':
            return RefreshCw;
        case 'processing':
            return Truck;
        case 'completed':
            return CheckCircle;
        case 'cancelled':
            return XCircle;
        default:
            return Package;
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    };
    return new Date(dateString).toLocaleDateString('en-US', options);
};
</script>

<template>
    <Head title="My Orders" />
    <AppLayout>
        <div class="min-h-screen bg-slate-50 pb-20 font-sans md:pb-10">
            <!-- Header Section -->
            <div class="bg-white shadow-sm">
                <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-bold text-slate-900">
                        আপনার অর্ডারসমূহ
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        আপনার পূর্ববর্তী অর্ডারের তালিকা এবং বর্তমান স্ট্যাটাস
                    </p>
                </div>
            </div>

            <div class="mx-auto mt-6 max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Orders List -->
                <div v-if="orders.length > 0" class="space-y-4">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-200"
                        :class="{
                            'ring-2 ring-blue/20': openOrder === order.id,
                        }"
                    >
                        <!-- Card Header (Clickable) -->
                        <div
                            class="relative cursor-pointer bg-white p-4 active:bg-slate-50 sm:p-6"
                            @click="toggleOrder(order.id)"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <!-- Left Side: Icon & ID -->
                                <div class="flex items-start gap-3 sm:gap-4">
                                    <div
                                        class="hidden h-12 w-12 items-center justify-center rounded-full sm:flex"
                                        :class="
                                            order.status === 'processing'
                                                ? 'bg-blue/10 text-blue'
                                                : 'bg-slate-100 text-slate-500'
                                        "
                                    >
                                        <component
                                            :is="getStatusIcon(order.status)"
                                            class="h-6 w-6"
                                        />
                                    </div>
                                    <div>
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="font-bold text-slate-800 sm:text-lg"
                                            >
                                                #{{ order.invoice_no }}
                                            </span>
                                            <span
                                                class="flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold tracking-wider uppercase"
                                                :class="
                                                    getStatusClass(order.status)
                                                "
                                            >
                                                <component
                                                    :is="
                                                        getStatusIcon(
                                                            order.status,
                                                        )
                                                    "
                                                    class="h-3 w-3"
                                                />
                                                {{ order.status }}
                                            </span>
                                        </div>
                                        <div
                                            class="mt-1 flex items-center gap-2 text-xs text-slate-500 sm:text-sm"
                                        >
                                            <Calendar class="h-3.5 w-3.5" />
                                            {{ formatDate(order.created_at) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Side: Price & Toggle -->
                                <div class="text-right">
                                    <p
                                        class="text-base font-extrabold text-slate-900 sm:text-lg"
                                    >
                                        {{ formatCurrency(order.total_price) }}
                                    </p>
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        {{ order.items.length }} Items
                                    </p>
                                </div>
                            </div>

                            <!-- Expand Icon (Absolute positioned for alignment) -->
                            <div
                                class="absolute right-4 bottom-4 sm:static sm:mt-2 sm:flex sm:justify-end"
                            >
                                <div
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 transition-transform duration-300 sm:bg-transparent"
                                    :class="{
                                        'rotate-180 bg-blue text-white':
                                            openOrder === order.id,
                                    }"
                                >
                                    <ChevronDown
                                        class="h-4 w-4"
                                        :class="{
                                            'text-white':
                                                openOrder === order.id,
                                            'text-slate-400':
                                                openOrder !== order.id,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Content -->
                        <transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <div
                                v-if="openOrder === order.id"
                                class="border-t border-slate-100 bg-slate-50/50 p-4 sm:p-6"
                            >
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-3"
                                >
                                    <!-- Address -->
                                    <div
                                        class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100"
                                    >
                                        <div
                                            class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-700"
                                        >
                                            <MapPin class="h-4 w-4 text-blue" />
                                            ডেলিভারি অ্যাড্রেস
                                        </div>
                                        <address
                                            class="text-sm leading-relaxed text-slate-600 not-italic"
                                        >
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ order.name }}
                                            </p>
                                            <p>{{ order.address_details }}</p>
                                            <p>
                                                {{ order.upazila_name }},
                                                {{ order.district_name }}
                                            </p>
                                            <p
                                                class="mt-2 flex items-center gap-1.5 text-slate-500"
                                            >
                                                <span class="text-xs">📞</span>
                                                {{ order.phone }}
                                            </p>
                                        </address>
                                    </div>

                                    <!-- Payment Info -->
                                    <div
                                        class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100"
                                    >
                                        <div
                                            class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-700"
                                        >
                                            <CreditCard
                                                class="h-4 w-4 text-blue"
                                            />
                                            পেমেন্ট তথ্য
                                        </div>
                                        <div class="space-y-3">
                                            <div>
                                                <p
                                                    class="text-xs text-slate-400 uppercase"
                                                >
                                                    Method
                                                </p>
                                                <p
                                                    class="font-medium text-slate-700 capitalize"
                                                >
                                                    {{ order.payment_method }}
                                                </p>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs text-slate-400 uppercase"
                                                >
                                                    Status
                                                </p>
                                                <span
                                                    class="mt-1 inline-block rounded px-2 py-0.5 text-xs font-bold capitalize"
                                                    :class="
                                                        order.payment_status ===
                                                        'paid'
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-amber-100 text-amber-700'
                                                    "
                                                >
                                                    {{ order.payment_status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Summary -->
                                    <div
                                        class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100"
                                    >
                                        <div
                                            class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-700"
                                        >
                                            <Package
                                                class="h-4 w-4 text-blue"
                                            />
                                            সারাংশ
                                        </div>
                                        <div
                                            class="space-y-2 text-sm text-slate-600"
                                        >
                                            <div class="flex justify-between">
                                                <span>সাবটোটাল</span>
                                                <span>{{
                                                    formatCurrency(
                                                        order.subtotal,
                                                    )
                                                }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>ডেলিভারি চার্জ</span>
                                                <span>{{
                                                    formatCurrency(
                                                        order.shipping_charge,
                                                    )
                                                }}</span>
                                            </div>
                                            <div
                                                class="mt-2 border-t border-slate-100 pt-2"
                                            >
                                                <div
                                                    class="flex justify-between font-bold text-slate-900"
                                                >
                                                    <span>সর্বমোট</span>
                                                    <span class="text-blue">{{
                                                        formatCurrency(
                                                            order.total_price,
                                                        )
                                                    }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items List -->
                                <div class="mt-6">
                                    <h4
                                        class="mb-3 text-sm font-bold text-slate-700"
                                    >
                                        অর্ডারকৃত বইসমূহ ({{
                                            order.items.length
                                        }})
                                    </h4>
                                    <div
                                        class="divide-y divide-slate-100 rounded-xl bg-white shadow-sm ring-1 ring-slate-100"
                                    >
                                        <div
                                            v-for="item in order.items"
                                            :key="item.id"
                                            class="flex items-center gap-3 p-3 sm:gap-4 sm:p-4"
                                        >
                                            <div
                                                class="h-16 w-12 shrink-0 overflow-hidden rounded-md border border-slate-200 bg-slate-50"
                                            >
                                                <img
                                                    :src="
                                                        item.book?.cover_photo
                                                            ? '/storage/' +
                                                              item.book
                                                                  .cover_photo
                                                            : '/images/placeholder-book.jpg'
                                                    "
                                                    alt="Book"
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="truncate text-sm font-bold text-slate-800"
                                                >
                                                    {{ item.book?.name }}
                                                </p>
                                                <p
                                                    class="text-xs text-slate-500"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            item.price,
                                                        )
                                                    }}
                                                    x
                                                    {{ item.quantity }}
                                                </p>
                                            </div>
                                            <div
                                                class="text-sm font-bold text-slate-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        item.subtotal,
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center py-16 text-center"
                >
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100"
                    >
                        <ShoppingBag class="h-10 w-10 text-slate-400" />
                    </div>
                    <h2 class="mt-6 text-xl font-bold text-slate-900">
                        কোনো অর্ডার পাওয়া যায়নি
                    </h2>
                    <p class="mt-2 max-w-sm text-slate-500">
                        আপনি এখনো কোনো অর্ডার করেননি। আমাদের ক্যাটালগ থেকে বই
                        বেছে নিন।
                    </p>
                    <Link
                        href="/allbooks"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue px-6 py-3 text-sm font-bold text-white shadow-lg transition-all hover:bg-blue-hover hover:shadow-blue/30 active:scale-95"
                    >
                        কেনাকাটা শুরু করুন
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Safe area for mobile bottom spacing (iPhone Home Bar) */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
