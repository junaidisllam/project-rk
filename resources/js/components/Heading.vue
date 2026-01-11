<script setup lang="ts">
import { useCartStore } from '@/Stores/cart';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    AlignLeft,
    ArrowLeft,
    ArrowRight,
    Award,
    Check,
    ChevronRight,
    Clock,
    CreditCard,
    Grid,
    Heart,
    HelpCircle,
    Home,
    LayoutDashboard,
    Library,
    List,
    Loader,
    Lock,
    LogOut,
    MapPin,
    Minus,
    Package,
    Phone,
    Plus,
    Search,
    Settings,
    ShoppingBag,
    Ticket,
    Trash2,
    Truck,
    Undo2,
    User,
    UserPlus,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    hideMobileNav?: boolean;
}>();

// --- Click Outside Directive ---
const vClickOutside = {
    mounted(el: any, binding: any) {
        el.clickOutsideEvent = (event: Event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.body.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el: any) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
    },
};

// --- Helper: Expose Ziggy ---
const route = (window as any).route;

// --- Utils ---
function debounce<T extends (...args: any[]) => any>(fn: T, delay: number) {
    let timeoutId: ReturnType<typeof setTimeout>;
    return function (this: any, ...args: Parameters<T>) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(this, args), delay);
    } as T;
}

// --- Store & Logic ---
const cartStore = useCartStore();
const page = usePage();

// --- Auth Logic ---
const user = computed(() => page.props.auth.user);
const isProfileDropdownOpen = ref(false);

const logout = () => {
    isAccountModalOpen.value = false;
    router.post(route('logout'));
};

const toggleProfileDropdown = () => {
    isProfileDropdownOpen.value = !isProfileDropdownOpen.value;
};

// --- Mobile Menu & Modal State ---
const isMobileMenuOpen = ref(false);
const isSearchOpen = ref(false);
const isAccountModalOpen = ref(false);
const isCartModalOpen = ref(false); // New Cart State
const accountModalTransition = ref('slide-up');
const cartModalTransition = ref('slide-up');
const isPinned = ref(false);
const searchInputRef = ref<HTMLInputElement | null>(null);
const sentinelRef = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

// --- Search State ---
const searchQuery = ref('');
const suggestions = ref<any[]>([]);
const searchMeta = ref<{ variants: string[] }>({ variants: [] });
const searchHistory = ref<string[]>([]);
const isSuggestionsLoading = ref(false);
const showSuggestions = ref(false);
const activeSuggestionIndex = ref(-1);

// --- Scroll Lock & URL Sync Optimization ---
const toggleScrollLock = (shouldLock: boolean) => {
    const body = document.body;
    const html = document.documentElement;

    if (shouldLock) {
        body.style.overflow = 'hidden';
        html.style.overflow = 'hidden';
        body.style.position = 'fixed';
        body.style.width = '100%';
    } else {
        body.style.overflow = '';
        html.style.overflow = '';
        body.style.position = '';
        body.style.width = '';
    }
};

// Unified Watcher for Modals and URL State
watch(
    [isMobileMenuOpen, isSearchOpen, isAccountModalOpen, isCartModalOpen],
    ([menuOpen, searchOpen, accountOpen, cartOpen]) => {
        const shouldLock = menuOpen || searchOpen || accountOpen || cartOpen;
        toggleScrollLock(shouldLock);

        // Sync State with URL
        if (typeof window !== 'undefined') {
            const url = new URL(window.location.href);

            if (accountOpen) {
                url.searchParams.set('account_modal', 'true');
                url.searchParams.delete('cart_modal'); // Ensure only one modal param
                window.history.pushState({ modal: 'account' }, '', url);
            } else if (cartOpen) {
                url.searchParams.set('cart_modal', 'true');
                url.searchParams.delete('account_modal');
                window.history.pushState({ modal: 'cart' }, '', url);
            } else {
                // If closing modals, clear params
                if (
                    url.searchParams.has('account_modal') ||
                    url.searchParams.has('cart_modal')
                ) {
                    url.searchParams.delete('account_modal');
                    url.searchParams.delete('cart_modal');
                    // Use replaceState to avoid cluttering history when closing
                    window.history.replaceState({}, '', url);
                }
            }
        }
    },
);

// Handle Browser Back Button
const handlePopState = (event: PopStateEvent) => {
    if (isAccountModalOpen.value) isAccountModalOpen.value = false;
    if (isCartModalOpen.value) isCartModalOpen.value = false;
    if (isSearchOpen.value) isSearchOpen.value = false;
    if (isMobileMenuOpen.value) isMobileMenuOpen.value = false;
};

// --- Cart Logic ---
const allSelected = computed({
    get: () =>
        cartStore.items.length > 0 && cartStore.items.every((i) => i.selected),
    set: (val: boolean) => cartStore.toggleAllSelected(val),
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('bn-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(price);
};

// --- Cart Animation ---
const cartCountAnimating = ref(false);
watch(
    () => cartStore.cartCount,
    (newVal, oldVal) => {
        if (newVal !== oldVal && newVal > 0) {
            cartCountAnimating.value = true;
            setTimeout(() => (cartCountAnimating.value = false), 300);
        }
    },
);

// --- Active Route Helper ---
const isActive = (routeName: string) => {
    if (route && route().current) return route().current(routeName);
    return page.url === '/' && routeName === 'home';
};

// --- Account Modal Actions ---
const openAccountModal = () => {
    if (!user.value) {
        router.visit('/login');
    } else {
        isAccountModalOpen.value = true;
    }
};

// --- Search Logic ---
const fetchSuggestions = async () => {
    if (searchQuery.value.length < 2) {
        suggestions.value = [];
        return;
    }
    isSuggestionsLoading.value = true;
    showSuggestions.value = true;
    activeSuggestionIndex.value = -1;
    try {
        const url = route
            ? route('api.search.suggestions', { q: searchQuery.value })
            : `/api/search/suggestions?q=${searchQuery.value}`;
        const { data } = await axios.get(url);
        suggestions.value = data.data;
        searchMeta.value = data.meta || { variants: [] };
    } catch (error) {
        suggestions.value = [];
        searchMeta.value = { variants: [] };
    } finally {
        isSuggestionsLoading.value = false;
    }
};
const debouncedFetchSuggestions = debounce(fetchSuggestions, 300);
watch(searchQuery, (newQuery) => {
    if (newQuery.trim() === '') suggestions.value = [];
    else debouncedFetchSuggestions();
});
const performSearch = (term?: string) => {
    const query = term || searchQuery.value;
    if (!query) return;
    const history = new Set([
        query.trim(),
        ...searchHistory.value.filter((t) => t !== query.trim()),
    ]);
    searchHistory.value = Array.from(history).slice(0, 5);
    localStorage.setItem(
        'elakarbazar_search_history',
        JSON.stringify(searchHistory.value),
    );
    showSuggestions.value = false;
    searchQuery.value = query;
    closeSearch();
    const url = route
        ? route('allbooks', { search: query })
        : `/allbooks?search=${query}`;
    router.visit(url, { preserveState: false });
};
const selectSuggestion = (suggestion: any) => {
    if (suggestion.type === 'history') performSearch(suggestion.name);
    else {
        router.get(suggestion.url);
        closeSearch();
    }
};
const combinedSuggestions = computed(() => {
    if (searchQuery.value.length === 0 && searchHistory.value.length > 0) {
        return searchHistory.value.map((term) => ({
            id: `history-${term}`,
            name: term,
            type: 'history',
        }));
    }
    return suggestions.value;
});
const openSearch = () => {
    isSearchOpen.value = true;
    isMobileMenuOpen.value = false;
    showSuggestions.value = true;
    nextTick(() => searchInputRef.value?.focus());
};
const closeSearch = () => {
    isSearchOpen.value = false;
    showSuggestions.value = false;
    searchQuery.value = '';
};
const handleGlobalKeydown = (e: KeyboardEvent) => {
    if (
        e.key === '/' &&
        document.activeElement?.tagName !== 'INPUT' &&
        document.activeElement?.tagName !== 'TEXTAREA'
    ) {
        e.preventDefault();
        if (window.innerWidth >= 1024) {
            const desktopInput = document.querySelector(
                'input[placeholder*="খুঁজুন"]',
            ) as HTMLInputElement;
            desktopInput?.focus();
        } else {
            openSearch();
        }
    }
};
const clearSearchHistory = () => {
    searchHistory.value = [];
    localStorage.removeItem('elakarbazar_search_history');
};
const highlightMatch = (text: string, query: string) => {
    if (!query || query.length < 2) return text;
    const rawTokens = query.split(/\s+/).filter((w) => w.length >= 2);
    const variants = searchMeta.value.variants || [];
    const allTokensToHighlight = [...new Set([...rawTokens, ...variants])];
    if (allTokensToHighlight.length === 0) return text;
    let highlightedText = text;
    allTokensToHighlight.sort((a, b) => b.length - a.length);
    allTokensToHighlight.forEach((token) => {
        const escapedToken = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedToken})`, 'gi');
        highlightedText = highlightedText.replace(
            regex,
            (match, p1) => `<span class="text-blue font-black">${p1}</span>`,
        );
    });
    return highlightedText;
};
const onKeydown = (e: KeyboardEvent) => {
    const items = combinedSuggestions.value;
    if (!items.length) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeSuggestionIndex.value =
            (activeSuggestionIndex.value + 1) % items.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeSuggestionIndex.value =
            (activeSuggestionIndex.value - 1 + items.length) % items.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (activeSuggestionIndex.value >= 0)
            selectSuggestion(items[activeSuggestionIndex.value]);
        else performSearch();
    } else if (e.key === 'Escape') {
        showSuggestions.value = false;
        (e.target as HTMLInputElement).blur();
    }
};

const handleOpenCartModal = () => {
    isCartModalOpen.value = true;
};

// --- Lifecycle ---
onMounted(() => {
    window.addEventListener('keydown', handleGlobalKeydown);
    window.addEventListener('popstate', handlePopState);
    window.addEventListener('open-cart-modal', handleOpenCartModal);

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('account_modal') === 'true') {
        accountModalTransition.value = '';
        isAccountModalOpen.value = true;
        setTimeout(() => {
            accountModalTransition.value = 'slide-up';
        }, 100);
    }
    if (urlParams.get('cart_modal') === 'true') {
        cartModalTransition.value = '';
        isCartModalOpen.value = true;
        setTimeout(() => {
            cartModalTransition.value = 'slide-up';
        }, 100);
    }

    const savedHistory = localStorage.getItem('elakarbazar_search_history');
    if (savedHistory) searchHistory.value = JSON.parse(savedHistory);

    observer = new IntersectionObserver(
        ([entry]) => {
            const shouldPin = !entry.isIntersecting;
            if (isPinned.value !== shouldPin) {
                isPinned.value = shouldPin;
            }
        },
        { threshold: 0 },
    );
    if (sentinelRef.value) observer.observe(sentinelRef.value);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown);
    window.removeEventListener('popstate', handlePopState);
    window.removeEventListener('open-cart-modal', handleOpenCartModal);
    if (observer) observer.disconnect();
    toggleScrollLock(false);
});

const menuLinks = [{ label: 'ক্যাটালগ', href: '/catalog', icon: Grid }];
</script>

<template>
    <!-- ============================================== -->
    <!-- MOBILE ACCOUNT MODAL                           -->
    <!-- ============================================== -->
    <Teleport to="body">
        <Transition :name="accountModalTransition">
            <div
                v-if="isAccountModalOpen"
                class="font-bangla fixed inset-0 z-999 flex flex-col bg-slate-50 lg:hidden"
                style="overscroll-behavior: contain"
            >
                <!-- Header -->
                <!-- UPDATED: Used bg-blue instead of hex code -->
                <div
                    class="relative z-10 shrink-0 rounded-b-[2rem] bg-blue text-white shadow-lg"
                >
                    <div
                        class="flex items-center justify-between px-4 pt-4 pb-0"
                    >
                        <button
                            @click="isAccountModalOpen = false"
                            class="rounded-full p-2 transition hover:bg-white/20 active:bg-white/30"
                        >
                            <ArrowLeft class="size-6 text-white" />
                        </button>
                        <h2 class="text-base font-bold tracking-wide">
                            My Account
                        </h2>
                        <Link
                            href="/settings"
                            class="rounded-full p-2 transition hover:bg-white/20"
                        >
                            <Settings class="size-6 text-white" />
                        </Link>
                    </div>

                    <div class="flex items-center gap-4 px-6 pt-2 pb-8">
                        <div class="relative shrink-0">
                            <div
                                class="size-14 rounded-full border-2 border-white/30 bg-white p-0.5"
                            >
                                <img
                                    :src="`https://ui-avatars.com/api/?name=${user?.name}&background=random`"
                                    alt="User"
                                    class="h-full w-full rounded-full object-cover"
                                />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-lg font-bold">
                                {{ user?.name }}
                            </h3>
                            <p
                                class="truncate text-xs text-blue-100 opacity-90"
                            >
                                {{ user?.email }}
                            </p>
                            <div
                                class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-0.5 ring-1 ring-white/30 backdrop-blur-sm"
                            >
                                <div
                                    class="flex size-3.5 items-center justify-center rounded-full bg-yellow-400 text-[8px] font-bold text-yellow-900"
                                >
                                    ৳
                                </div>
                                <span class="text-xs font-bold text-yellow-300"
                                    >0 pts</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div
                    class="mt-2 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 pb-20"
                >
                    <!-- Orders -->
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <h4 class="mb-4 text-sm font-bold text-slate-800">
                            My Orders
                        </h4>
                        <div class="grid grid-cols-4 gap-2">
                            <Link
                                href="/orders"
                                class="flex flex-col items-center gap-2"
                            >
                                <Package class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >All</span
                                >
                            </Link>
                            <Link
                                href="/orders?status=processing"
                                class="flex flex-col items-center gap-2"
                            >
                                <Loader class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Processing</span
                                >
                            </Link>
                            <Link
                                href="/orders?status=shipped"
                                class="flex flex-col items-center gap-2"
                            >
                                <Truck class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Shipped</span
                                >
                            </Link>
                            <Link
                                href="/orders?status=returned"
                                class="flex flex-col items-center gap-2"
                            >
                                <Undo2 class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Returned</span
                                >
                            </Link>
                        </div>
                    </div>

                    <!-- Wallet/Payment -->
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <h4 class="mb-4 text-sm font-bold text-slate-800">
                            Payments & Discounts
                        </h4>
                        <div class="grid grid-cols-4 gap-2">
                            <Link
                                href="#"
                                class="flex cursor-not-allowed flex-col items-center gap-2 opacity-50"
                            >
                                <Wallet class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Balance</span
                                >
                            </Link>
                            <Link
                                href="#"
                                class="flex cursor-not-allowed flex-col items-center gap-2 opacity-50"
                            >
                                <Award class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Points</span
                                >
                            </Link>
                            <Link
                                href="#"
                                class="flex cursor-not-allowed flex-col items-center gap-2 opacity-50"
                            >
                                <Ticket class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Coupon</span
                                >
                            </Link>
                            <Link
                                href="/payment-methods"
                                class="flex flex-col items-center gap-2"
                            >
                                <CreditCard class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Payment</span
                                >
                            </Link>
                        </div>
                    </div>

                    <!-- Addresses -->
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <Link
                            href="/addresses"
                            class="flex w-full items-center justify-between"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                                >
                                    <MapPin class="size-5" />
                                </div>
                                <div class="text-left">
                                    <h4
                                        class="text-sm font-bold text-slate-800"
                                    >
                                        Address Book
                                    </h4>
                                    <p class="text-[10px] text-slate-500">
                                        Manage delivery addresses
                                    </p>
                                </div>
                            </div>
                            <ChevronRight class="size-5 text-slate-400" />
                        </Link>
                    </div>

                    <!-- Services -->
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <h4 class="mb-4 text-sm font-bold text-slate-800">
                            Services
                        </h4>
                        <div class="grid grid-cols-4 gap-x-2 gap-y-6">
                            <Link
                                href="#"
                                class="flex cursor-not-allowed flex-col items-center gap-2 opacity-50"
                            >
                                <UserPlus class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Invite</span
                                >
                            </Link>
                            <Link
                                href="/wishlist"
                                class="flex flex-col items-center gap-2"
                            >
                                <Heart class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Wishlist</span
                                >
                            </Link>
                            <Link
                                href="#"
                                class="flex cursor-not-allowed flex-col items-center gap-2 opacity-50"
                            >
                                <List class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >List</span
                                >
                            </Link>
                            <Link
                                href="#"
                                class="flex cursor-not-allowed flex-col items-center gap-2 opacity-50"
                            >
                                <Library class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Bookshelf</span
                                >
                            </Link>
                            <Link
                                href="/help"
                                class="flex flex-col items-center gap-2"
                            >
                                <HelpCircle class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Help</span
                                >
                            </Link>
                            <Link
                                href="/dashboard"
                                class="flex flex-col items-center gap-2"
                            >
                                <LayoutDashboard
                                    class="size-6 text-slate-600"
                                />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Dashboard</span
                                >
                            </Link>
                            <Link
                                href="/profile"
                                class="flex flex-col items-center gap-2"
                            >
                                <User class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Profile</span
                                >
                            </Link>
                            <Link
                                href="/password"
                                class="flex flex-col items-center gap-2"
                            >
                                <Lock class="size-6 text-slate-600" />
                                <span
                                    class="text-center text-[10px] text-slate-500"
                                    >Password</span
                                >
                            </Link>
                        </div>
                    </div>

                    <button
                        @click="logout"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-100 bg-red-50 py-3.5 font-bold text-red-500 transition-transform active:scale-95"
                    >
                        <LogOut class="size-5" />
                        লগ আউট করুন
                    </button>
                    <div class="h-8"></div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- 1. DESKTOP ANNOUNCEMENT BAR -->
    <div
        class="hidden h-9 w-full items-center justify-between bg-slate-900 px-6 text-[11px] font-medium tracking-wide text-slate-300 lg:flex"
    >
        <div class="flex items-center gap-4">
            <span
                class="flex items-center gap-1.5 transition-colors hover:text-white"
                ><Phone class="size-3" /> +880 1712 345678</span
            >
            <span class="opacity-30">|</span>
            <span>সারা বাংলাদেশে ক্যাশ অন ডেলিভারি</span>
        </div>
        <div class="flex items-center gap-4">
            <Link href="/help" class="transition-colors hover:text-blue"
                >সাহায্য প্রয়োজন?</Link
            >
        </div>
    </div>

    <!-- 2. SCROLL SENTINEL -->
    <div
        ref="sentinelRef"
        class="pointer-events-none absolute top-[56px] left-0 h-[1px] w-full opacity-0 lg:top-[36px]"
    ></div>

    <!-- 3. MOBILE MENU (Drawer) -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="isMobileMenuOpen"
                @click="isMobileMenuOpen = false"
                class="fixed inset-0 z-[90] bg-slate-900/60 backdrop-blur-sm lg:hidden"
                style="touch-action: none"
            ></div>
        </Transition>
        <Transition name="slide-in">
            <div
                v-if="isMobileMenuOpen"
                class="font-bangla fixed top-0 bottom-0 left-0 z-[100] w-[80%] max-w-[300px] overflow-hidden rounded-r-3xl bg-white shadow-2xl lg:hidden"
            >
                <!-- Drawer Header: Already using bg-blue -->
                <div class="relative overflow-hidden bg-blue p-6 text-white">
                    <div
                        class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"
                    ></div>
                    <div class="flex items-center justify-between">
                        <img
                            src="/logo.png"
                            class="h-8 w-auto brightness-0 invert"
                            alt="Logo"
                        />
                        <button
                            @click="isMobileMenuOpen = false"
                            class="rounded-full bg-white/20 p-1.5 transition hover:bg-white/30"
                        >
                            <X class="size-5" />
                        </button>
                    </div>
                    <div class="mt-8">
                        <template v-if="user">
                            <h2 class="text-lg font-bold">
                                হ্যালো, {{ user.name }}! 👋
                            </h2>
                            <p class="mt-1 truncate text-xs text-blue-100/90">
                                {{ user.email }}
                            </p>
                            <button
                                @click="
                                    isMobileMenuOpen = false;
                                    isAccountModalOpen = true;
                                "
                                class="mt-4 inline-block w-full rounded-xl bg-white/10 py-2.5 text-center text-sm font-bold text-white ring-1 ring-white/30 active:scale-95"
                            >
                                অ্যাকাউন্ট দেখুন
                            </button>
                        </template>
                        <template v-else>
                            <h2 class="text-lg font-bold">স্বাগতম! 👋</h2>
                            <p class="mt-1 text-sm text-blue-100/90">
                                অর্ডার করতে লগইন করুন
                            </p>
                            <Link
                                href="/login"
                                class="mt-4 inline-block w-full rounded-xl bg-white py-2.5 text-center text-sm font-bold text-blue shadow-lg active:scale-95"
                                >লগইন / সাইন আপ</Link
                            >
                        </template>
                    </div>
                </div>
                <div
                    class="h-[calc(100vh-220px)] space-y-1 overflow-y-auto overscroll-contain p-3"
                >
                    <Link
                        v-for="item in menuLinks"
                        :key="item.label"
                        :href="item.href"
                        class="flex items-center gap-4 rounded-xl px-4 py-3.5 text-[15px] font-medium text-slate-700 transition active:bg-blue/5 active:text-blue"
                    >
                        <component
                            :is="item.icon"
                            class="size-5 text-slate-400"
                        />
                        {{ item.label }}
                    </Link>
                </div>
            </div>
        </Transition>

        <!-- Search Modal -->
        <Transition name="slide-up">
            <div
                v-if="isSearchOpen"
                class="font-bangla fixed inset-0 z-[100] flex flex-col bg-slate-50 lg:hidden"
                style="overscroll-behavior: contain"
            >
                <div class="bg-white px-4 pt-4 pb-2 shadow-sm">
                    <div class="flex items-center gap-3">
                        <button
                            @click="closeSearch"
                            class="rounded-full p-2 text-slate-500 active:bg-slate-100"
                        >
                            <ArrowLeft class="size-6" />
                        </button>
                        <div
                            class="flex flex-1 items-center rounded-xl bg-slate-100 px-3 py-2.5"
                        >
                            <Search class="size-5 text-blue" />
                            <input
                                ref="searchInputRef"
                                v-model="searchQuery"
                                type="text"
                                placeholder="বই বা লেখকের নাম..."
                                class="ml-2 w-full border-none bg-transparent p-0 text-base font-medium text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0"
                                @keydown="onKeydown"
                            />
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="text-slate-400"
                            >
                                <X class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto overscroll-contain p-4">
                    <!-- Loading Skeleton -->
                    <div v-if="isSuggestionsLoading" class="space-y-4">
                        <div
                            v-for="i in 5"
                            :key="i"
                            class="flex animate-pulse items-center gap-3"
                        >
                            <div class="size-10 rounded-lg bg-slate-200"></div>
                            <div class="flex-1 space-y-2">
                                <div
                                    class="h-4 w-3/4 rounded bg-slate-200"
                                ></div>
                                <div
                                    class="h-3 w-1/2 rounded bg-slate-100"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else-if="combinedSuggestions.length > 0"
                        class="space-y-6"
                    >
                        <!-- Grouped results or flat list with premium styling -->
                        <div v-if="searchQuery.length === 0" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    সাম্প্রতিক অনুসন্ধান
                                </h3>
                                <button
                                    @click="clearSearchHistory"
                                    class="text-[10px] font-bold text-red-400 uppercase hover:text-red-500"
                                >
                                    মুছে ফেলুন
                                </button>
                            </div>
                            <div class="space-y-1">
                                <div
                                    v-for="(item, index) in combinedSuggestions"
                                    :key="item.id"
                                    @click="selectSuggestion(item)"
                                    class="flex items-center justify-between rounded-xl p-3 transition-colors active:bg-slate-100"
                                >
                                    <div class="flex items-center gap-3">
                                        <Clock class="size-4 text-slate-400" />
                                        <span
                                            class="text-sm font-medium text-slate-600"
                                            >{{ item.name }}</span
                                        >
                                    </div>
                                    <ArrowLeft
                                        class="size-4 rotate-135 text-slate-300"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="(item, index) in combinedSuggestions"
                                :key="item.id"
                                @click="selectSuggestion(item)"
                                class="group flex items-center gap-4 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-100 transition-all active:scale-[0.98]"
                            >
                                <div
                                    class="relative size-14 shrink-0 overflow-hidden rounded-xl bg-slate-50 ring-1 ring-slate-100"
                                >
                                    <img
                                        v-if="item.image"
                                        :src="item.image"
                                        class="size-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex size-full items-center justify-center bg-blue/5 text-blue"
                                    >
                                        <User
                                            v-if="item.type === 'লেখক'"
                                            class="size-6"
                                        />
                                        <Grid
                                            v-else-if="
                                                item.type === 'ক্যাটাগরি'
                                            "
                                            class="size-6"
                                        />
                                        <Search v-else class="size-6" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p
                                            class="line-clamp-1 text-sm font-bold text-slate-800"
                                            v-html="
                                                highlightMatch(
                                                    item.name,
                                                    searchQuery,
                                                )
                                            "
                                        ></p>
                                        <span
                                            class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500"
                                            >{{ item.type }}</span
                                        >
                                    </div>
                                    <div
                                        class="mt-1 flex items-center justify-between"
                                    >
                                        <div>
                                            <p
                                                v-if="item.writer"
                                                class="text-[10px] font-semibold text-slate-500"
                                            >
                                                {{ item.writer }}
                                            </p>
                                            <p
                                                v-else
                                                class="text-[10px] font-semibold text-slate-400"
                                            >
                                                বিস্তারিত দেখতে ক্লিক করুন
                                            </p>
                                        </div>
                                        <div
                                            v-if="item.price"
                                            class="text-right"
                                        >
                                            <p
                                                class="text-[11px] font-black text-blue"
                                            >
                                                {{ formatPrice(item.price) }}
                                            </p>
                                            <p
                                                v-if="
                                                    item.original_price >
                                                    item.price
                                                "
                                                class="text-[9px] text-slate-400 line-through"
                                            >
                                                {{
                                                    formatPrice(
                                                        item.original_price,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <ChevronRight
                                    class="size-4 text-slate-300 transition-transform group-hover:translate-x-1"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- ============================================== -->
    <!-- MOBILE CART MODAL                              -->
    <!-- ============================================== -->
    <Teleport to="body">
        <Transition :name="cartModalTransition">
            <div
                v-if="isCartModalOpen"
                class="font-bangla fixed inset-0 z-[9999] flex flex-col bg-slate-50 lg:hidden"
                style="overscroll-behavior: contain"
            >
                <!-- Header -->
                <div
                    class="relative z-10 shrink-0 border-b border-slate-100 bg-white shadow-sm"
                >
                    <div class="flex items-center justify-between p-4">
                        <button
                            @click="isCartModalOpen = false"
                            class="rounded-full p-2 text-slate-700 transition active:bg-slate-100"
                        >
                            <ArrowLeft class="size-6" />
                        </button>
                        <h2 class="text-sm font-bold text-slate-800">
                            শপিং ব্যাগ ({{ cartStore.items.length }})
                        </h2>
                        <button
                            @click="cartStore.clearCart"
                            class="text-xs font-bold text-red-500 active:opacity-70"
                        >
                            সব মুছুন
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="cartStore.items.length === 0"
                    class="flex flex-1 flex-col items-center justify-center p-6 text-center"
                >
                    <div
                        class="mb-6 flex size-24 items-center justify-center rounded-full bg-white shadow-xl shadow-blue-100"
                    >
                        <ShoppingBag class="size-10 text-blue" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">
                        ব্যাগটি খালি!
                    </h3>
                    <p class="mt-2 text-sm text-slate-500">
                        আপনার পছন্দের বইগুলো ব্যাগে যোগ করুন
                    </p>
                    <button
                        @click="isCartModalOpen = false"
                        class="mt-8 rounded-full bg-blue px-8 py-3 font-bold text-white shadow-lg shadow-blue/20 transition active:scale-95"
                    >
                        কেনাকাটা করুন
                    </button>
                </div>

                <!-- Filled State -->
                <template v-else>
                    <!-- Select All & Summary Header -->
                    <div class="bg-white p-4 shadow-sm">
                        <label
                            class="group flex cursor-pointer items-center gap-3"
                        >
                            <div class="relative flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="allSelected"
                                    class="peer size-5 cursor-pointer appearance-none rounded border-2 border-slate-300 transition-all checked:border-blue checked:bg-blue"
                                />
                                <Check
                                    class="pointer-events-none absolute left-0.5 size-3.5 text-white opacity-0 peer-checked:opacity-100"
                                />
                            </div>
                            <span
                                class="text-sm font-bold text-slate-700 select-none"
                                >সবগুলো সিলেক্ট করুন</span
                            >
                        </label>
                    </div>

                    <!-- Items List -->
                    <div
                        class="flex-1 space-y-3 overflow-y-auto overscroll-contain p-4 pb-32"
                    >
                        <div
                            v-for="item in cartStore.items"
                            :key="item.id"
                            class="relative flex gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-100 transition-all"
                            :class="
                                item.selected
                                    ? 'bg-blue-50/10 ring-blue-100'
                                    : ''
                            "
                        >
                            <!-- Selection Checkbox -->
                            <div class="flex items-center">
                                <label
                                    class="relative flex cursor-pointer items-center p-1"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="item.selected"
                                        @change="
                                            cartStore.toggleSelectedItem(
                                                item.id,
                                            )
                                        "
                                        class="peer size-5 cursor-pointer appearance-none rounded border-2 border-slate-300 transition-all checked:border-blue checked:bg-blue"
                                    />
                                    <Check
                                        class="pointer-events-none absolute left-1.5 size-3.5 text-white opacity-0 peer-checked:opacity-100"
                                    />
                                </label>
                            </div>

                            <!-- Image -->
                            <div
                                class="h-20 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-50 shadow-sm"
                            >
                                <img
                                    :src="
                                        item.cover_photo
                                            ? `/storage/${item.cover_photo}`
                                            : ''
                                    "
                                    class="h-full w-full object-cover"
                                />
                            </div>

                            <!-- Info -->
                            <div
                                class="flex flex-1 flex-col justify-between py-0.5"
                            >
                                <div class="flex justify-between gap-2">
                                    <h4
                                        class="line-clamp-1 text-sm font-bold text-slate-800"
                                    >
                                        {{ item.name }}
                                    </h4>
                                    <button
                                        @click="cartStore.remove(item.id)"
                                        class="text-slate-400 active:text-red-500"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                                <div class="flex items-end justify-between">
                                    <div class="flex items-baseline gap-1.5">
                                        <span
                                            class="text-sm font-black text-blue"
                                        >
                                            {{ formatPrice(item.price) }}
                                        </span>
                                        <span
                                            v-if="item.original_price"
                                            class="text-[10px] text-slate-400 line-through"
                                        >
                                            {{
                                                formatPrice(item.original_price)
                                            }}
                                        </span>
                                    </div>

                                    <!-- Counter -->
                                    <div
                                        class="flex items-center rounded-full border border-slate-100 bg-white shadow-sm"
                                    >
                                        <button
                                            @click="
                                                cartStore.decrementQuantity(
                                                    item.id,
                                                )
                                            "
                                            class="p-1 px-2 text-slate-600 disabled:opacity-30"
                                            :disabled="item.quantity <= 1"
                                        >
                                            <Minus class="size-3" />
                                        </button>
                                        <span
                                            class="w-6 text-center text-xs font-bold text-slate-800"
                                        >
                                            {{ item.quantity }}
                                        </span>
                                        <button
                                            @click="
                                                cartStore.incrementQuantity(
                                                    item.id,
                                                )
                                            "
                                            class="p-1 px-2 text-slate-600"
                                        >
                                            <Plus class="size-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Actions -->
                    <div
                        class="fixed right-0 bottom-0 left-0 bg-white p-5 pt-3 pb-8 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                    >সর্বমোট</span
                                >
                                <span class="text-2xl font-black text-blue">
                                    {{ formatPrice(cartStore.subtotal) }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span
                                    v-if="cartStore.totalSavings > 0"
                                    class="rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-bold text-green-600 ring-1 ring-green-100"
                                >
                                    সাশ্রয়:
                                    {{ formatPrice(cartStore.totalSavings) }}
                                </span>
                            </div>
                        </div>
                        <button
                            @click="
                                () => {
                                    isCartModalOpen = false;
                                    router.visit('/checkout');
                                }
                            "
                            class="flex w-full items-center justify-center gap-3 rounded-2xl bg-blue py-4 text-base font-bold text-white shadow-xl shadow-blue/20 transition active:scale-95"
                        >
                            অর্ডার করুন <ArrowRight class="size-5" />
                        </button>
                    </div>
                </template>
            </div>
        </Transition>
    </Teleport>

    <!-- 4. MAIN NAVBAR -->
    <nav
        class="font-bangla sticky -top-[60px] z-50 w-full transition-all duration-200 ease-out lg:top-0"
        :class="
            isPinned
                ? 'bg-white/95 shadow-sm lg:bg-white/80 lg:backdrop-blur-xl'
                : 'border-b border-slate-100 bg-white'
        "
    >
        <div class="mx-auto max-w-7xl lg:px-8">
            <!-- MOBILE NAVBAR CONTENT -->
            <div class="flex flex-col lg:hidden">
                <!-- Mobile Top Bar (Hides when pinned) -->
                <div
                    class="flex h-[56px] items-center justify-between px-4 transition-all duration-200 ease-out"
                    :class="
                        isPinned
                            ? 'pointer-events-none h-0 overflow-hidden opacity-0'
                            : 'opacity-100'
                    "
                >
                    <button
                        class="rounded-full p-2 text-slate-700 active:bg-slate-100"
                        @click="isMobileMenuOpen = true"
                    >
                        <AlignLeft class="size-6" />
                    </button>
                    <Link
                        href="/"
                        class="absolute left-1/2 -translate-x-1/2 transform"
                    >
                        <img src="/logo.png" alt="logo" class="h-8 w-auto" />
                    </Link>
                    <div class="flex items-center gap-2">
                        <button
                            @click="isCartModalOpen = true"
                            class="relative rounded-full p-2 text-slate-700 active:bg-slate-100"
                        >
                            <ShoppingBag class="size-6" />
                            <span
                                v-if="cartStore.cartCount"
                                class="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-blue text-[9px] font-bold text-white ring-2 ring-white"
                            >
                                {{ cartStore.cartCount }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- OPTIMIZED MOBILE SEARCH BAR (Smooth Animation) -->
                <div
                    class="relative flex items-center justify-center px-4 pb-3 transition-transform duration-300 ease-out will-change-transform"
                    :class="isPinned ? 'translate-y-2' : 'translate-y-0'"
                >
                    <div
                        @click="openSearch"
                        class="relative z-10 flex h-11 w-full items-center rounded-2xl border border-slate-200 bg-slate-50 transition-shadow duration-300"
                        :class="
                            isPinned
                                ? 'bg-white shadow-md ring-1 ring-black/5'
                                : 'shadow-sm'
                        "
                    >
                        <!-- Icon Swapping Wrapper (No v-if, uses transforms) -->
                        <div
                            class="relative ml-2 flex size-10 items-center justify-center"
                        >
                            <!-- Menu Icon (Shows when Pinned) -->
                            <button
                                @click.stop="isMobileMenuOpen = true"
                                class="absolute inset-0 flex items-center justify-center text-slate-500 transition-all duration-300 ease-out"
                                :class="
                                    isPinned
                                        ? 'scale-100 rotate-0 opacity-100'
                                        : 'pointer-events-none scale-75 -rotate-90 opacity-0'
                                "
                            >
                                <AlignLeft class="size-5" />
                            </button>
                            <!-- Search Icon (Shows when Not Pinned) -->
                            <div
                                class="absolute inset-0 flex items-center justify-center text-slate-400 transition-all duration-300 ease-out"
                                :class="
                                    isPinned
                                        ? 'scale-75 rotate-90 opacity-0'
                                        : 'scale-100 rotate-0 opacity-100'
                                "
                            >
                                <Search class="size-5" />
                            </div>
                        </div>

                        <div class="flex-1 pr-3 pl-1">
                            <span class="text-sm text-slate-400"
                                >বই খুঁজুন...</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- DESKTOP NAVBAR CONTENT -->
            <div
                class="hidden items-center justify-between gap-8 transition-all duration-200 ease-out lg:flex"
                :class="isPinned ? 'py-3' : 'py-4'"
            >
                <div class="flex shrink-0 items-center gap-8">
                    <Link href="/" class="shrink-0">
                        <img
                            src="/logo.png"
                            alt="logo"
                            class="h-10 w-auto object-contain transition-transform duration-200 ease-out"
                            :class="isPinned ? 'scale-90' : ''"
                        />
                    </Link>
                    <Link
                        v-for="item in menuLinks"
                        :key="item.label"
                        :href="item.href"
                        class="flex items-center gap-2 text-sm font-medium transition-colors hover:text-blue"
                        :class="
                            isActive(item.href)
                                ? 'font-bold text-blue'
                                : 'text-slate-600'
                        "
                    >
                        <component :is="item.icon" class="size-4" />
                        <span>{{ item.label }}</span>
                    </Link>
                </div>

                <!-- Search Input Desktop: Full & Center -->
                <div class="flex flex-1 justify-center px-10">
                    <div
                        class="relative w-full max-w-3xl"
                        v-click-outside="() => (showSuggestions = false)"
                    >
                        <div
                            class="relative flex items-center overflow-hidden rounded-xl border transition-all duration-200"
                            :class="
                                showSuggestions
                                    ? 'border-blue bg-white ring-4 ring-blue/10'
                                    : 'border-slate-200 bg-slate-50 hover:border-slate-300 hover:bg-white hover:shadow-sm'
                            "
                        >
                            <div class="pl-4 text-slate-400">
                                <Search class="size-4" />
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="বই, লেখক বা প্রকাশনীর নাম দিয়ে খুঁজুন..."
                                class="w-full border-none bg-transparent py-2.5 pr-12 pl-3 text-sm text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0"
                                @focus="showSuggestions = true"
                                @keydown="onKeydown"
                            />
                            <!-- Search Button -->
                            <button
                                @click="performSearch()"
                                class="absolute right-1.5 rounded-lg bg-blue px-4 py-1.5 text-xs font-bold text-white shadow-lg shadow-blue/20 transition-all hover:bg-blue-hover hover:shadow-blue/30 active:scale-95"
                            >
                                খুঁজুন
                            </button>
                        </div>

                        <!-- DESKTOP SUGGESTIONS (Stencil UI) -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                            enter-to-class="transform opacity-100 scale-100 translate-y-0"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="transform opacity-100 scale-100 translate-y-0"
                            leave-to-class="transform opacity-0 scale-95 -translate-y-2"
                        >
                            <div
                                v-if="
                                    showSuggestions &&
                                    (isSuggestionsLoading ||
                                        combinedSuggestions.length > 0)
                                "
                                class="absolute top-full left-0 mt-3 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white/95 shadow-2xl ring-1 ring-black/5 backdrop-blur-xl"
                            >
                                <!-- Loading Skeleton -->
                                <div
                                    v-if="isSuggestionsLoading"
                                    class="space-y-1 p-2"
                                >
                                    <div
                                        v-for="i in 4"
                                        :key="i"
                                        class="flex animate-pulse items-center gap-3 rounded-xl p-3"
                                    >
                                        <div
                                            class="size-10 rounded-lg bg-slate-100"
                                        ></div>
                                        <div class="flex-1 space-y-2">
                                            <div
                                                class="h-4 w-1/3 rounded bg-slate-100"
                                            ></div>
                                            <div
                                                class="h-3 w-1/4 rounded bg-slate-50"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="max-h-[70vh] overflow-y-auto p-2"
                                >
                                    <!-- History Section -->
                                    <div
                                        v-if="searchQuery.length === 0"
                                        class="pb-2"
                                    >
                                        <div
                                            class="flex items-center justify-between px-4 py-2"
                                        >
                                            <h3
                                                class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                            >
                                                সাম্প্রতিক অনুসন্ধান
                                            </h3>
                                            <button
                                                @click="clearSearchHistory"
                                                class="text-[10px] font-bold text-red-400 uppercase hover:text-red-500"
                                            >
                                                মুছে ফেলুন
                                            </button>
                                        </div>
                                        <div class="space-y-0.5">
                                            <div
                                                v-for="(
                                                    item, index
                                                ) in combinedSuggestions"
                                                :key="item.id"
                                                @click="selectSuggestion(item)"
                                                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-2.5 transition-colors hover:bg-slate-50 focus:bg-slate-50"
                                                :class="{
                                                    'bg-slate-50':
                                                        activeSuggestionIndex ===
                                                        index,
                                                }"
                                            >
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <Clock
                                                        class="size-4 text-slate-400"
                                                    />
                                                    <span
                                                        class="text-sm font-medium text-slate-600"
                                                        >{{ item.name }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Search Results -->
                                    <div v-else class="space-y-0.5">
                                        <div
                                            v-for="(
                                                item, index
                                            ) in combinedSuggestions"
                                            :key="item.id"
                                            @click="selectSuggestion(item)"
                                            class="group flex cursor-pointer items-center gap-4 rounded-xl p-3 transition-all hover:bg-blue/5 focus:bg-blue/5"
                                            :class="{
                                                'bg-blue/5':
                                                    activeSuggestionIndex ===
                                                    index,
                                            }"
                                        >
                                            <div
                                                class="relative size-14 shrink-0 overflow-hidden rounded-lg bg-slate-50 ring-1 ring-slate-100 group-hover:ring-blue/20"
                                            >
                                                <img
                                                    v-if="item.image"
                                                    :src="item.image"
                                                    class="size-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="flex size-full items-center justify-center bg-blue/5 text-blue"
                                                >
                                                    <User
                                                        v-if="
                                                            item.type === 'লেখক'
                                                        "
                                                        class="size-6"
                                                    />
                                                    <Grid
                                                        v-else-if="
                                                            item.type ===
                                                            'ক্যাটাগরি'
                                                        "
                                                        class="size-6"
                                                    />
                                                    <Search
                                                        v-else
                                                        class="size-6"
                                                    />
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div
                                                    class="flex items-center justify-between"
                                                >
                                                    <p
                                                        class="text-sm font-bold text-slate-800 transition-colors group-hover:text-blue"
                                                    >
                                                        {{ item.name }}
                                                    </p>
                                                    <span
                                                        class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500 group-hover:bg-blue/10 group-hover:text-blue"
                                                        >{{ item.type }}</span
                                                    >
                                                </div>
                                                <p
                                                    class="mt-0.5 text-[11px] text-slate-400"
                                                >
                                                    ভিতরে প্রবেশ করতে ক্লিক করুন
                                                </p>
                                            </div>
                                            <ChevronRight
                                                class="size-4 text-slate-300 transition-transform group-hover:translate-x-1"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <Link
                        href="/wishlist"
                        class="group relative text-slate-500 transition-colors hover:text-red-500"
                    >
                        <Heart
                            class="size-6 transition-transform group-hover:scale-110"
                        />
                        <span
                            class="absolute -top-1 -right-1 size-2 rounded-full bg-red-500"
                        ></span>
                    </Link>
                    <Link href="/cart" class="group flex items-center gap-3">
                        <div class="relative">
                            <ShoppingBag
                                class="size-6 text-slate-600 transition-colors group-hover:text-blue"
                            />
                            <span
                                v-if="cartStore.cartCount"
                                class="animate-bounce-short absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-blue text-[9px] font-bold text-white ring-2 ring-white"
                            >
                                {{ cartStore.cartCount }}
                            </span>
                        </div>
                    </Link>
                    <template v-if="user">
                        <div
                            class="relative"
                            v-click-outside="
                                () => (isProfileDropdownOpen = false)
                            "
                        >
                            <button
                                @click="toggleProfileDropdown"
                                class="flex items-center gap-3 rounded-full border border-slate-200 bg-white py-1 pr-1.5 pl-4 transition-all hover:border-blue-200 hover:ring-4 hover:ring-blue/5"
                            >
                                <span
                                    class="max-w-[120px] truncate text-sm font-bold text-slate-700"
                                    >{{ user.name }}</span
                                >
                                <div
                                    class="flex size-9 items-center justify-center rounded-full bg-blue text-white shadow-lg shadow-blue/20"
                                >
                                    <User class="size-5" />
                                </div>
                            </button>
                            <Transition name="fade">
                                <div
                                    v-if="isProfileDropdownOpen"
                                    class="absolute top-full right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5"
                                >
                                    <div class="p-1">
                                        <Link
                                            href="/profile"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue"
                                        >
                                            <User class="size-4" />আমার প্রোফাইল
                                        </Link>
                                        <Link
                                            href="/orders"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue"
                                        >
                                            <Package class="size-4" />আমার
                                            অর্ডার
                                        </Link>
                                        <Link
                                            href="/wishlist"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue"
                                        >
                                            <Heart class="size-4" />পছন্দের
                                            তালিকা
                                        </Link>
                                        <Link
                                            href="/settings"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue"
                                        >
                                            <Settings class="size-4" />সেটিংস
                                        </Link>
                                        <div
                                            class="my-1 h-px bg-slate-100"
                                        ></div>
                                        <button
                                            @click="logout"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-red-500 hover:bg-red-50"
                                        >
                                            <LogOut class="size-4" />লগ আউট
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </template>
                    <template v-else>
                        <!-- Login Button: Already using bg-blue and bg-blue-hover -->
                        <Link
                            href="/login"
                            class="rounded-xl bg-blue px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:-translate-y-0.5 hover:bg-blue-hover"
                            >লগইন</Link
                        >
                    </template>
                </div>
            </div>
        </div>
    </nav>

    <!-- 5. MOBILE BOTTOM NAV -->
    <div
        v-if="!hideMobileNav"
        class="font-bangla fixed right-0 bottom-0 left-0 z-40 lg:hidden"
    >
        <div
            class="pb-safe relative flex h-[70px] w-full items-center justify-around rounded-t-2xl border-t border-white/50 bg-white/90 shadow-[0_-5px_20px_-5px_rgba(0,0,0,0.1)] backdrop-blur-lg"
        >
            <Link
                href="/"
                class="group flex flex-col items-center gap-1 p-2"
                :class="isActive('home') ? 'text-blue' : 'text-slate-400'"
            >
                <Home
                    class="size-6 transition-transform group-active:scale-90"
                    :class="isActive('home') ? 'fill-current' : ''"
                />
                <span class="text-[10px] font-medium">হোম</span>
            </Link>
            <Link
                href="/catalog"
                class="group flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-blue"
            >
                <Grid
                    class="size-6 transition-transform group-active:scale-90"
                />
                <span class="text-[10px] font-medium">ক্যাটালগ</span>
            </Link>
            <div class="relative -top-6">
                <!-- Floating Cart: Open Modal instead of link -->
                <button
                    @click="isCartModalOpen = true"
                    class="flex size-14 items-center justify-center rounded-full bg-blue text-white shadow-xl ring-4 shadow-blue/40 ring-white transition-transform active:scale-90"
                >
                    <ShoppingBag class="size-6" />
                    <span
                        v-if="cartStore.cartCount"
                        class="absolute top-0 right-0 flex size-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold ring-2 ring-white"
                        >{{ cartStore.cartCount }}</span
                    >
                </button>
            </div>
            <Link
                href="/wishlist"
                class="group flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-blue"
            >
                <Heart
                    class="size-6 transition-transform group-active:scale-90"
                />
                <span class="text-[10px] font-medium">পছন্দ</span>
            </Link>
            <button
                @click="openAccountModal"
                class="group flex flex-col items-center gap-1 p-2 outline-none hover:text-blue"
                :class="
                    isActive('profile') || isAccountModalOpen
                        ? 'text-blue'
                        : 'text-slate-400'
                "
            >
                <User
                    class="size-6 transition-transform group-active:scale-90"
                    :class="
                        isActive('profile') || isAccountModalOpen
                            ? 'fill-current text-blue'
                            : ''
                    "
                />
                <span class="text-[10px] font-medium">অ্যাকাউন্ট</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');

.font-bangla {
    font-family: 'Hind Siliguri', sans-serif;
}
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
.overscroll-contain {
    overscroll-behavior: contain;
}

/* Animations */
.slide-in-enter-active,
.slide-in-leave-active {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-in-enter-from,
.slide-in-leave-to {
    transform: translate3d(-100%, 0, 0);
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translate3d(0, 100%, 0);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease-out;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.animate-bounce-short {
    animation: bounce-short 0.5s;
}
@keyframes bounce-short {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-25%);
    }
}

::-webkit-scrollbar {
    width: 4px;
}
::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}
</style>
