<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ChevronRight,
    Clock,
    CreditCard,
    Heart,
    MapPin,
    Package,
    Settings,
    ShoppingBag,
    User,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    user: any;
    recentOrders: any[];
}>();

const menu = [
    {
        name: 'প্রোফাইল',
        href: '/profile',
        icon: User,
        route: 'profile',
        color: 'text-purple-600',
        bg: 'bg-purple-50',
    },
    {
        name: 'অর্ডার',
        href: '/orders',
        icon: Package,
        route: 'orders',
        color: 'text-blue', // Using primary theme color
        bg: 'bg-blue/10',
    },
    {
        name: 'ঠিকানা',
        href: '/addresses',
        icon: MapPin,
        route: 'addresses',
        color: 'text-orange-600',
        bg: 'bg-orange-50',
    },
    {
        name: 'উইশলিস্ট',
        href: '/wishlist',
        icon: Heart,
        route: 'wishlist',
        color: 'text-pink-600',
        bg: 'bg-pink-50',
    },
    {
        name: 'পেমেন্ট',
        href: '/payment-methods', // Assuming route exists
        icon: CreditCard,
        route: 'payment',
        color: 'text-emerald-600',
        bg: 'bg-emerald-50',
    },
    {
        name: 'সেটিংস',
        href: '/settings',
        icon: Settings,
        route: 'settings',
        color: 'text-slate-600',
        bg: 'bg-slate-100',
    },
];

const getStatusClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-700';
        case 'processing':
            return 'bg-blue/10 text-blue';
        case 'completed':
            return 'bg-emerald-100 text-emerald-700';
        case 'cancelled':
            return 'bg-red-100 text-red-700';
        default:
            return 'bg-slate-100 text-slate-600';
    }
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(value);

const formatDate = (dateString: string) =>
    new Date(dateString).toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout title="Dashboard">
        <div class="min-h-screen bg-slate-50 pb-24 font-sans lg:pb-10">
            <!-- ======================= -->
            <!-- 1. APP HEADER SECTION   -->
            <!-- ======================= -->
            <div
                class="relative overflow-hidden bg-blue pb-16 shadow-lg lg:rounded-b-[2.5rem]"
            >
                <!-- Abstract Background Shapes -->
                <div
                    class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"
                ></div>
                <div
                    class="absolute top-10 -left-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"
                ></div>

                <div class="relative z-10 px-6 pt-8 pb-4">
                    <div class="flex items-center gap-4">
                        <!-- Avatar -->
                        <div class="relative">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-white/20 bg-white text-blue shadow-lg"
                            >
                                <span class="text-2xl font-bold">{{
                                    user.name?.charAt(0)
                                }}</span>
                            </div>
                            <div
                                class="absolute right-0 bottom-0 h-4 w-4 rounded-full border-2 border-blue bg-green-400"
                            ></div>
                        </div>

                        <!-- Greeting -->
                        <div class="text-white">
                            <p
                                class="text-sm font-medium text-blue-100 opacity-90"
                            >
                                শুভ সকাল,
                            </p>
                            <h2 class="text-2xl font-bold tracking-wide">
                                {{ user.name?.split(' ')[0] }}
                            </h2>
                            <p class="text-xs text-blue-50/80">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================= -->
            <!-- 2. FLOATING MENU GRID   -->
            <!-- ======================= -->
            <div class="relative z-20 mx-4 -mt-12">
                <div
                    class="rounded-3xl bg-white p-5 shadow-[0_8px_30px_rgb(0,0,0,0.06)]"
                >
                    <h3
                        class="mb-4 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        কুইক মেনু
                    </h3>
                    <div
                        class="grid grid-cols-3 gap-4 sm:grid-cols-3 lg:grid-cols-6"
                    >
                        <Link
                            v-for="item in menu"
                            :key="item.name"
                            :href="item.href"
                            class="group flex flex-col items-center justify-center gap-2 transition-transform active:scale-95"
                        >
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl shadow-sm transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-md"
                                :class="[item.bg, item.color]"
                            >
                                <component
                                    :is="item.icon"
                                    class="h-6 w-6 stroke-[1.5]"
                                />
                            </div>
                            <span
                                class="text-xs font-semibold text-slate-600 group-hover:text-slate-900"
                            >
                                {{ item.name }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ======================= -->
            <!-- 3. RECENT ORDERS        -->
            <!-- ======================= -->
            <div class="mx-auto mt-6 max-w-5xl px-4">
                <div class="mb-4 flex items-center justify-between">
                    <h3
                        class="flex items-center gap-2 text-base font-bold text-slate-800"
                    >
                        <ShoppingBag class="h-5 w-5 text-blue" />
                        সাম্প্রতিক অর্ডার
                    </h3>
                    <Link
                        :href="route('orders')"
                        class="flex items-center text-xs font-bold text-blue hover:underline"
                    >
                        সব দেখুন <ChevronRight class="h-3 w-3" />
                    </Link>
                </div>

                <div v-if="recentOrders.length > 0" class="space-y-3">
                    <div
                        v-for="order in recentOrders"
                        :key="order.id"
                        class="group relative overflow-hidden rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 transition-all hover:shadow-md active:scale-[0.99]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <!-- Order Icon Box -->
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-colors group-hover:bg-blue/10 group-hover:text-blue"
                                >
                                    <Package class="h-6 w-6" />
                                </div>

                                <!-- Details -->
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-slate-800">
                                            #{{ order.invoice_no }}
                                        </p>
                                        <span
                                            class="rounded px-1.5 py-0.5 text-[10px] font-bold capitalize"
                                            :class="
                                                getStatusClass(order.status)
                                            "
                                        >
                                            {{ order.status }}
                                        </span>
                                    </div>
                                    <div
                                        class="mt-1 flex items-center gap-3 text-xs text-slate-500"
                                    >
                                        <span class="flex items-center gap-1">
                                            <Clock class="h-3 w-3" />
                                            {{ formatDate(order.created_at) }}
                                        </span>
                                        <span
                                            class="h-1 w-1 rounded-full bg-slate-300"
                                        ></span>
                                        <span
                                            >{{
                                                order.items_count ||
                                                order.items?.length ||
                                                0
                                            }}
                                            Items</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Price & Arrow -->
                            <div class="text-right">
                                <p
                                    class="text-sm font-extrabold text-slate-800"
                                >
                                    {{ formatCurrency(order.total_price) }}
                                </p>
                                <ChevronRight
                                    class="mt-1 ml-auto h-4 w-4 text-slate-300"
                                />
                            </div>
                        </div>

                        <!-- Link Overlay -->
                        <Link
                            :href="route('orders')"
                            class="absolute inset-0"
                            aria-label="View Order"
                        ></Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-white py-12 text-center"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50"
                    >
                        <ShoppingBag class="h-8 w-8 text-slate-300" />
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-600">
                        কোনো অর্ডার নেই
                    </p>
                    <p class="text-xs text-slate-400">
                        আপনি এখনো কোনো অর্ডার করেননি
                    </p>
                    <Link
                        href="/allbooks"
                        class="mt-4 rounded-full bg-blue px-6 py-2 text-xs font-bold text-white shadow-lg hover:bg-blue-hover"
                    >
                        কেনাকাটা শুরু করুন
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
