<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    Heart,
    LayoutDashboard,
    MapPin,
    Package,
    User,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper to check active route
const route = (window as any).route;
const isActive = (routeName: string) => {
    if (route && route().current) {
        return route().current(routeName);
    }
    return false;
};

const menu = [
    {
        name: 'ড্যাশবোর্ড',
        href: '/dashboard',
        icon: LayoutDashboard,
        route: 'dashboard',
    },
    { name: 'প্রোফাইল', href: '/profile', icon: User, route: 'profile' },
    { name: 'অর্ডার', href: '/orders', icon: Package, route: 'orders' },
    { name: 'ঠিকানা', href: '/addresses', icon: MapPin, route: 'addresses' },
    { name: 'উইশলিস্ট', href: '/wishlist', icon: Heart, route: 'wishlist' },
];
</script>

<template>
    <!-- ============================================= -->
    <!-- DESKTOP & TABLET SIDEBAR (MD+)                -->
    <!-- ============================================= -->
    <!-- Width: md:w-20 (Collapsed), lg:w-72 (Expanded) -->
    <aside
        class="hidden h-[calc(100vh-100px)] flex-shrink-0 flex-col justify-between transition-all duration-300 md:flex md:w-20 lg:w-72"
    >
        <div class="space-y-6">
            <!-- User Info Card -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-300 md:p-3 lg:p-6"
            >
                <div
                    class="flex items-center gap-4 md:justify-center lg:justify-start"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue/10 text-blue shadow-sm ring-2 ring-white"
                    >
                        <User class="h-6 w-6" />
                    </div>
                    <!-- Text hidden on tablet, visible on desktop -->
                    <div class="hidden flex-1 overflow-hidden lg:block">
                        <h3 class="truncate text-base font-bold text-slate-800">
                            {{ user?.name || 'Guest' }}
                        </h3>
                        <p class="truncate text-xs text-slate-500">
                            {{ user?.email || 'user@example.com' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div
                class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm lg:p-4"
            >
                <nav class="space-y-2 md:space-y-3 lg:space-y-1">
                    <Link
                        v-for="item in menu"
                        :key="item.name"
                        :href="item.href"
                        class="group relative flex items-center rounded-xl p-3 text-sm font-semibold transition-all duration-200 lg:justify-between lg:px-4"
                        :class="
                            isActive(item.route)
                                ? 'bg-blue text-white shadow-lg shadow-blue/30'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-blue'
                        "
                        :title="item.name"
                    >
                        <div
                            class="flex items-center gap-3 md:justify-center lg:justify-start"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 transition-colors"
                                :class="
                                    isActive(item.route)
                                        ? 'text-white'
                                        : 'text-slate-400 group-hover:text-blue'
                                "
                            />
                            <span class="hidden lg:inline">{{
                                item.name
                            }}</span>
                        </div>

                        <!-- Right Chevron (Desktop Only) -->
                        <ChevronRight
                            v-if="isActive(item.route)"
                            class="hidden h-4 w-4 opacity-50 lg:block"
                        />

                        <!-- Tooltip for Tablet -->
                        <div
                            class="absolute left-full ml-4 hidden rounded-md bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 md:group-hover:block lg:hidden"
                        >
                            {{ item.name }}
                        </div>
                    </Link>
                </nav>
            </div>
        </div>
    </aside>

    <!-- ============================================= -->
    <!-- MOBILE BOTTOM NAV (Hidden on MD+)             -->
    <!-- ============================================= -->
    <div class="fixed right-0 bottom-0 left-0 z-50 md:hidden">
        <!-- Floating Style Container -->
        <div class="px-4 pb-4">
            <nav
                class="relative flex w-full items-center justify-between rounded-2xl border border-white/20 bg-white/90 px-2 py-3 shadow-[0_8px_30px_rgb(0,0,0,0.12)] backdrop-blur-xl"
            >
                <!-- Horizontal Scroll Container for many items -->
                <div
                    class="scrollbar-hide flex w-full items-center justify-between overflow-x-auto px-1"
                >
                    <Link
                        v-for="item in menu"
                        :key="item.name"
                        :href="item.href"
                        class="group flex min-w-[60px] flex-col items-center justify-center gap-1.5 rounded-xl py-1 transition-all active:scale-95"
                    >
                        <div
                            class="relative flex h-10 w-10 items-center justify-center rounded-full transition-all duration-300"
                            :class="
                                isActive(item.route)
                                    ? 'bg-blue text-white shadow-lg ring-2 shadow-blue/30 ring-blue/20'
                                    : 'text-slate-400 group-hover:text-blue'
                            "
                        >
                            <component :is="item.icon" class="h-5 w-5" />

                            <!-- Active Dot Indicator -->
                            <span
                                v-if="isActive(item.route)"
                                class="absolute -bottom-1 h-1 w-1 rounded-full bg-blue"
                            ></span>
                        </div>
                        <span
                            class="text-[10px] font-bold tracking-tight"
                            :class="
                                isActive(item.route)
                                    ? 'text-blue'
                                    : 'text-slate-400'
                            "
                        >
                            {{ item.name }}
                        </span>
                    </Link>
                </div>
            </nav>
        </div>
    </div>
</template>

<style scoped>
/* Smooth Scrollbar Hiding */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
