<script setup lang="ts">
import { useCartStore } from '@/Stores/cart';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    Heart,
    Package,
    Share2,
    ShieldCheckIcon,
    ShoppingBag,
    ShoppingCart,
    Truck,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, shallowRef, watch } from 'vue';
import { route } from 'ziggy-js';

// --- PROPS ---
interface Props {
    id: number;
    name: string;
    writer: { name: string };
    category: { name: string };
    cover_photo: string | null;
    price: number;
    original_price: number | null;
    slug: string;
    highlightQuery?: string;
    highlightVariants?: string[];
}

const props = defineProps<Props>();
const cartStore = useCartStore();
const page = usePage();

// --- STATE ---
// Optimization: Use shallowRef for objects that don't need deep reactivity for better performance
const isImageLoaded = ref(false);
const showToast = ref(false);
const toastMessage = ref('');

// --- MOBILE MODAL STATE ---
const isModalOpen = ref(false);
const isLoadingDetails = ref(false);
const bookDetails = shallowRef<any>(null); // Optimization: shallowRef
const relatedBooks = shallowRef<any[]>([]); // Optimization: shallowRef
const activeTab = ref('summary');
const isAddingToCart = ref(false);
const addedToCartSuccess = ref(false);
const isBuyingNow = ref(false);

// --- COMPUTED ---
const isInCart = computed(() =>
    cartStore.items.some((item) => item.id === props.id),
);

const isWishlisted = computed(() => {
    // @ts-ignore
    const wishlistIds = page.props.auth?.wishlist_book_ids || [];
    // @ts-ignore
    return wishlistIds.includes(props.id);
});

const localIsWishlisted = ref(isWishlisted.value);
watch(isWishlisted, (val) => {
    localIsWishlisted.value = val;
});

// --- METHODS ---
function calculateDiscount(price: number, originalPrice: number): number {
    return Math.round(((originalPrice - price) / originalPrice) * 100);
}

function handleAddToCart() {
    cartStore.add({
        id: props.id,
        name: props.name,
        writer: props.writer,
        cover_photo: props.cover_photo,
        price: props.price,
        original_price: props.original_price,
        slug: props.slug,
    });
}

function handleBuyNow() {
    if (isBuyingNow.value) return;

    isBuyingNow.value = true;

    // Add to cart if not already there
    if (!isInCart.value) {
        handleAddToCart();
    }

    // Ensure this item is selected (in case it was in cart but unselected)
    const cartItem = cartStore.items.find((i) => i.id === props.id);
    if (cartItem && !cartItem.selected) {
        cartStore.toggleSelectedItem(props.id);
    }

    // Redirect to checkout with a small delay for feedback
    setTimeout(() => {
        router.visit(route('checkout'));
        // isBuyingNow.value = false; // Reset happens on page unload/navigation
    }, 400);
}

function toggleWishlist() {
    if (!page.props.auth?.user) {
        router.visit('/login');
        return;
    }

    // Optimistic toggle
    localIsWishlisted.value = !localIsWishlisted.value;

    router.post(
        route('wishlist.toggle'),
        { book_id: props.id },
        {
            preserveScroll: true,
            onError: () => {
                // Revert on error
                localIsWishlisted.value = !localIsWishlisted.value;
            },
        },
    );
}

function handleShare() {
    const shareData = {
        title: props.name,
        text: `Check out this book: ${props.name} by ${props.writer.name}`,
        url: route('book.show', props.slug),
    };

    if (
        navigator.share &&
        navigator.canShare &&
        navigator.canShare(shareData)
    ) {
        navigator.share(shareData).catch(() => copyToClipboard(shareData.url));
    } else {
        copyToClipboard(shareData.url);
    }
}

function copyToClipboard(url: string) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard
        .writeText(fullUrl)
        .then(() => triggerToast('লিংক কপি হয়েছে!'));
}

function triggerToast(message: string) {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
}

// --- OPTIMIZED MOBILE LOGIC ---
// removed: checkMobile listener (HUGE PERFORMANCE GAIN)

function handleGoToCart() {
    // Check mobile using matchMedia instead of state listener
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    if (isMobile) {
        window.dispatchEvent(new CustomEvent('open-cart-modal'));
    } else {
        router.visit('/cart');
    }
}

async function handleCardClick(e: Event) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const isMobile = window.matchMedia('(max-width: 768px)').matches;

    if (!isMobile) {
        router.visit(route('book.show', props.slug));
        return;
    }

    isModalOpen.value = true;
    document.body.style.overflow = 'hidden'; // Prevent background scrolling

    const bookUrl = route('book.show', props.slug);
    window.history.pushState(
        { modalOpen: true, slug: props.slug },
        '',
        bookUrl,
    );

    if (!bookDetails.value) {
        await fetchBookDetails();
    }
}

function closeModal() {
    isModalOpen.value = false;
    document.body.style.overflow = '';
    if (window.history.state?.modalOpen) {
        window.history.back();
    }
}

function handlePopState(event: PopStateEvent) {
    if (isModalOpen.value && !event.state?.modalOpen) {
        isModalOpen.value = false;
        document.body.style.overflow = '';
    }
}

async function fetchBookDetails() {
    isLoadingDetails.value = true;
    try {
        const response = await axios.get(route('book.show', props.slug), {
            headers: { Accept: 'application/json' },
        });
        bookDetails.value = response.data.book;
        relatedBooks.value = response.data.related_books || [];
    } catch (error) {
        console.error('Failed to fetch book details', error);
    } finally {
        isLoadingDetails.value = false;
    }
}

// Optimization: Memoize/Simplify highlighting
const highlightText = (
    text: string,
    query: string,
    variants: string[] = [],
) => {
    if (!query || query.length < 2) return text;
    // Simple return if complex logic causes lag, otherwise keep logic but ensure it's efficient
    const rawTokens = query.split(/\s+/).filter((w) => w.length >= 2);
    if (rawTokens.length === 0) return text;

    // Basic regex replacement is usually fast enough, keeping your logic
    // but moved regex creation inside loop to ensure safety
    let highlightedText = text;
    const allTokens = [...new Set([...rawTokens, ...variants])].sort(
        (a, b) => b.length - a.length,
    );

    allTokens.forEach((token) => {
        const escaped = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escaped})`, 'gi');
        highlightedText = highlightedText.replace(
            regex,
            `<span class="text-blue font-black">$1</span>`,
        );
    });
    return highlightedText;
};

const getImageUrl = (path: string | number | null | undefined) => {
    if (!path || path === '0' || path === 0) return undefined;
    return path.toString().startsWith('http')
        ? path.toString()
        : `/storage/${path}`;
};

// --- LIFECYCLE ---
onMounted(() => {
    // REMOVED: resize listener
    window.addEventListener('popstate', handlePopState);
});

onUnmounted(() => {
    window.removeEventListener('popstate', handlePopState);
    if (isModalOpen.value) {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <!-- REMOVED: Group hover heavy effects on parent to reduce paint repaints -->
    <div
        class="group relative mx-auto flex h-full w-full max-w-[170px] cursor-pointer flex-col will-change-transform"
    >
        <!-- Book Cover Section -->
        <div
            class="relative z-10 shrink-0 transition-transform duration-300 ease-out group-hover:-translate-y-1"
        >
            <a
                :href="`/book/${slug}`"
                class="block"
                @click.prevent="handleCardClick"
            >
                <div
                    class="relative w-full overflow-hidden rounded-r-sm bg-white shadow-sm"
                >
                    <!-- Reduced aspect ratio complexity -->
                    <div class="relative aspect-2/3 w-full bg-slate-100">
                        <!-- Simplified Loader (CSS only) -->
                        <div
                            v-if="!isImageLoaded"
                            class="absolute inset-0 z-20 animate-pulse bg-slate-200"
                        ></div>

                        <img
                            :src="getImageUrl(cover_photo)"
                            :alt="name"
                            loading="lazy"
                            decoding="async"
                            @load="isImageLoaded = true"
                            class="h-full w-full object-cover transition-opacity duration-300 select-none"
                            :class="isImageLoaded ? 'opacity-100' : 'opacity-0'"
                        />

                        <!-- Wishlist Button (Mobile focused, hides on desktop hover) -->
                        <div
                            class="absolute top-2 right-2 z-40 transition-all duration-300 md:group-hover:scale-50 md:group-hover:opacity-0"
                        >
                            <!-- Removed backdrop-blur for performance -->
                            <button
                                @click.stop.prevent="toggleWishlist"
                                class="cursor-pointer rounded-full bg-white/90 p-1.5 shadow-sm transition-transform active:scale-95"
                                :class="
                                    localIsWishlisted
                                        ? 'scale-110 text-red-500'
                                        : 'text-slate-400 hover:scale-110 hover:text-red-500'
                                "
                            >
                                <Heart
                                    class="h-4 w-4"
                                    :class="{
                                        'fill-current': localIsWishlisted,
                                    }"
                                    stroke-width="2.5"
                                />
                            </button>
                        </div>

                        <!-- DESKTOP OVERLAY (Simplified) -->
                        <div
                            class="absolute inset-0 z-30 hidden items-center justify-center bg-black/40 opacity-0 transition-opacity duration-300 group-hover:opacity-100 md:flex"
                        >
                            <div
                                class="flex w-full translate-y-4 items-center gap-1.5 px-1.5 transition-transform duration-300 group-hover:translate-y-0"
                            >
                                <button
                                    @click.stop.prevent="
                                        isInCart
                                            ? handleGoToCart()
                                            : handleAddToCart()
                                    "
                                    class="flex flex-1 transform cursor-pointer items-center justify-center gap-1 rounded-lg py-2.5 text-[10px] font-bold whitespace-nowrap shadow-xl transition-all active:scale-95"
                                    :class="
                                        isInCart
                                            ? 'bg-blue text-white shadow-blue/20'
                                            : 'bg-white text-slate-900 hover:bg-blue hover:text-white'
                                    "
                                >
                                    <ShoppingCart
                                        v-if="!isInCart"
                                        class="size-3"
                                    />
                                    <ArrowRight v-else class="size-3" />
                                    <span>{{
                                        isInCart
                                            ? 'কার্টে দেখুন'
                                            : 'কার্টে যোগ করুন'
                                    }}</span>
                                </button>
                                <button
                                    @click.stop.prevent="toggleWishlist"
                                    class="flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-lg shadow-xl transition-all active:scale-90"
                                    :class="
                                        localIsWishlisted
                                            ? 'bg-red-500 text-white'
                                            : 'bg-white text-slate-900 hover:text-red-500'
                                    "
                                >
                                    <Heart
                                        class="size-4.5"
                                        :class="{
                                            'fill-current': localIsWishlisted,
                                        }"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Optimized Overlays (CSS Gradients are faster than blurs) -->
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 w-4 bg-gradient-to-r from-black/10 via-black/5 to-transparent"
                    ></div>
                </div>
            </a>
        </div>

        <!-- Book Info -->
        <div class="mt-2 flex flex-1 flex-col justify-between px-0.5">
            <div>
                <div class="mb-1.5 h-10">
                    <h3
                        class="line-clamp-1 text-[15px] font-bold text-gray-900 group-hover:text-blue"
                        :title="name"
                        v-html="
                            highlightQuery
                                ? highlightText(
                                      name,
                                      highlightQuery,
                                      highlightVariants,
                                  )
                                : name
                        "
                    ></h3>
                    <p
                        class="line-clamp-1 text-[13px] font-medium text-gray-500"
                    >
                        {{ writer.name }}
                    </p>
                </div>

                <div
                    class="flex items-end justify-between border-t border-gray-100 pt-1.5"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-extrabold text-gray-900"
                            >৳{{ price }}</span
                        >
                        <div
                            v-if="original_price"
                            class="flex flex-col-reverse items-start leading-none"
                        >
                            <span
                                class="mb-0.5 rounded bg-blue/10 px-1 py-px text-[9px] font-bold text-blue"
                            >
                                {{ calculateDiscount(price, original_price) }}%
                                OFF
                            </span>
                            <span
                                class="text-[11px] font-medium text-gray-400 line-through"
                                >৳{{ original_price }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- MOBILE BUTTON -->
            <button
                @click.stop.prevent="
                    isInCart ? handleGoToCart() : handleAddToCart()
                "
                class="mt-3 flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-bold transition-transform active:scale-95 md:hidden"
                :class="
                    isInCart
                        ? 'bg-blue text-white shadow-lg shadow-blue/20'
                        : 'border border-blue text-blue'
                "
            >
                <ShoppingCart v-if="!isInCart" class="size-4" />
                <ArrowRight v-else class="size-4" />
                <span>{{ isInCart ? 'কার্টে দেখুন' : 'কার্টে যোগ করুন' }}</span>
            </button>
        </div>
    </div>

    <!-- MOBILE DETAIL MODAL -->
    <!-- Optimization: Only render Teleport content if modal is strictly open -->
    <Teleport to="body">
        <Transition name="modal-slide">
            <div
                v-if="isModalOpen"
                class="fixed inset-0 z-[100] flex flex-col bg-white"
            >
                <!-- TOP BAR - Removed backdrop-blur -->
                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-4 py-3"
                >
                    <button
                        @click="closeModal"
                        class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-slate-50 active:bg-slate-100"
                    >
                        <ArrowLeft class="size-6 text-slate-800" />
                    </button>
                    <h2
                        class="max-w-[150px] truncate text-base font-bold text-slate-900"
                    >
                        {{ name }}
                    </h2>
                    <div class="flex items-center gap-2">
                        <button
                            @click="handleShare"
                            class="flex h-10 w-10 cursor-pointer items-center justify-center"
                        >
                            <Share2 class="size-5 text-slate-600" />
                        </button>
                        <button
                            @click="handleGoToCart"
                            class="relative flex h-10 w-10 cursor-pointer items-center justify-center"
                        >
                            <ShoppingBag class="size-5 text-slate-600" />
                            <span
                                v-if="cartStore.cartCount > 0"
                                class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue text-[10px] font-bold text-white"
                                >{{ cartStore.cartCount }}</span
                            >
                        </button>
                    </div>
                </div>

                <!-- SCROLLABLE CONTENT -->
                <div class="flex-1 overflow-y-auto overscroll-contain">
                    <div
                        v-if="isLoadingDetails"
                        class="animate-pulse space-y-6 px-4 py-6"
                    >
                        <!-- Skeleton content simplified -->
                        <div
                            class="mx-auto aspect-2/3 w-64 rounded-xl bg-slate-200"
                        ></div>
                        <div class="h-8 w-3/4 rounded bg-slate-200"></div>
                    </div>

                    <div v-else-if="bookDetails" class="pb-24">
                        <!-- Book Cover Area -->
                        <div class="bg-slate-50 py-8 text-center">
                            <img
                                :src="
                                    bookDetails.cover_photo
                                        ? getImageUrl(bookDetails.cover_photo)
                                        : '/images/placeholder-book.jpg'
                                "
                                class="mx-auto w-40 rounded-lg shadow-xl"
                            />
                        </div>

                        <div class="px-5 pt-6">
                            <!-- Details Content -->
                            <div class="mb-4">
                                <span
                                    class="rounded bg-blue/10 px-2 py-0.5 text-xs font-bold text-blue"
                                    >{{ bookDetails.category?.name }}</span
                                >
                                <h1
                                    class="mt-2 text-2xl font-black text-slate-900"
                                >
                                    {{ bookDetails.name }}
                                </h1>
                                <p class="text-base font-bold text-slate-500">
                                    By {{ bookDetails.writer?.name }}
                                </p>
                            </div>

                            <!-- Pricing -->
                            <div class="mb-6 flex items-center gap-3">
                                <span class="text-3xl font-black text-slate-900"
                                    >৳{{ bookDetails.price }}</span
                                >
                                <div
                                    v-if="bookDetails.original_price"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="text-lg text-slate-400 line-through"
                                        >৳{{ bookDetails.original_price }}</span
                                    >
                                    <span
                                        class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-black text-red-500"
                                    >
                                        {{
                                            calculateDiscount(
                                                bookDetails.price,
                                                bookDetails.original_price,
                                            )
                                        }}% OFF
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mb-6 flex gap-3">
                                <button
                                    @click="toggleWishlist"
                                    class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 font-bold active:scale-95"
                                    :class="
                                        localIsWishlisted
                                            ? 'border-red-100 bg-red-50 text-red-600'
                                            : 'bg-white text-slate-700'
                                    "
                                >
                                    <Heart
                                        class="size-5"
                                        :class="{
                                            'fill-current': localIsWishlisted,
                                        }"
                                    />
                                    <span>{{
                                        localIsWishlisted
                                            ? 'সংরক্ষিত'
                                            : 'সেভ করুন'
                                    }}</span>
                                </button>
                                <button
                                    @click="handleShare"
                                    class="flex h-12 w-12 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white active:scale-95"
                                >
                                    <Share2 class="size-5 text-slate-600" />
                                </button>
                            </div>

                            <!-- Trust Badges (Simplified) -->
                            <div class="mb-8 grid grid-cols-2 gap-3">
                                <div
                                    class="flex items-center gap-2 rounded-xl border border-blue-100 bg-blue-50 p-3"
                                >
                                    <Truck class="size-5 text-blue-600" />
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold"
                                            >দ্রুত ডেলিভারি</span
                                        >
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3"
                                >
                                    <Package class="size-5 text-slate-600" />
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold"
                                            >ক্যাশ অন ডেলিভারি</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <div class="mb-8">
                                <div
                                    class="flex space-x-6 border-b border-slate-100"
                                >
                                    <button
                                        v-for="tab in [
                                            'summary',
                                            'specification',
                                            'author',
                                        ]"
                                        :key="tab"
                                        @click="activeTab = tab"
                                        class="cursor-pointer pb-3 text-sm font-bold transition-colors"
                                        :class="
                                            activeTab === tab
                                                ? 'border-b-2 border-blue text-blue'
                                                : 'text-slate-400'
                                        "
                                    >
                                        {{
                                            tab === 'summary'
                                                ? 'সারাংশ'
                                                : tab === 'specification'
                                                  ? 'স্পেসিফিকেশন'
                                                  : 'লেখক'
                                        }}
                                    </button>
                                </div>

                                <div class="mt-6">
                                    <div v-if="activeTab === 'summary'">
                                        <div
                                            class="text-sm leading-relaxed text-slate-600"
                                            v-html="bookDetails.description"
                                        ></div>

                                        <!-- Service Info (Simplified CSS) -->
                                        <div
                                            class="mt-8 space-y-3 rounded-2xl border border-slate-100 bg-slate-50 p-5"
                                        >
                                            <h4
                                                class="mb-3 text-sm font-bold text-slate-900"
                                            >
                                                আমাদের সেবা
                                            </h4>
                                            <div
                                                class="flex items-center gap-3 text-xs text-slate-600"
                                            >
                                                <ShieldCheckIcon
                                                    class="size-4 text-slate-700"
                                                /><span
                                                    >১০০% অরিজিনাল পণ্য</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-3 text-xs text-slate-600"
                                            >
                                                <Truck
                                                    class="size-4 text-blue-500"
                                                /><span
                                                    >সারাদেশে হোম ডেলিভারি</span
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-else-if="
                                            activeTab === 'specification'
                                        "
                                    >
                                        <div
                                            class="overflow-hidden rounded-xl border border-slate-100"
                                        >
                                            <div
                                                v-for="(
                                                    val, key
                                                ) in bookDetails.specification"
                                                :key="key"
                                                class="flex justify-between px-4 py-3 text-sm even:bg-slate-50"
                                            >
                                                <span
                                                    class="font-bold text-slate-500"
                                                    >{{ key }}</span
                                                >
                                                <span
                                                    class="font-black text-slate-800"
                                                    >{{ val }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else-if="activeTab === 'author'">
                                        <div class="flex items-center gap-4">
                                            <img
                                                :src="
                                                    bookDetails.writer?.photo
                                                        ? getImageUrl(
                                                              bookDetails.writer
                                                                  .photo,
                                                          )
                                                        : '/images/placeholder-author.jpg'
                                                "
                                                class="h-16 w-16 rounded-full bg-slate-100 object-cover"
                                            />
                                            <div>
                                                <h4
                                                    class="text-base font-black text-slate-900"
                                                >
                                                    {{
                                                        bookDetails.writer?.name
                                                    }}
                                                </h4>
                                                <p
                                                    class="text-xs font-bold text-slate-500"
                                                >
                                                    লেখক
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="mt-4 text-sm leading-relaxed text-slate-600"
                                        >
                                            {{
                                                bookDetails.writer?.biography ||
                                                'তথ্য শীঘ্রই আসবে।'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Related Books -->
                            <div
                                v-if="relatedBooks.length > 0"
                                class="mt-8 border-t border-slate-100 pt-8"
                            >
                                <h3
                                    class="mb-4 text-lg font-black text-slate-900"
                                >
                                    সম্পর্কিত বই
                                </h3>
                                <div
                                    class="scrollbar-hide flex gap-4 overflow-x-auto pb-4"
                                >
                                    <div
                                        v-for="relBook in relatedBooks"
                                        :key="relBook.id"
                                        class="w-28 shrink-0"
                                    >
                                        <div
                                            @click="
                                                router.visit(
                                                    `/book/${relBook.slug}`,
                                                )
                                            "
                                            class="aspect-2/3 cursor-pointer overflow-hidden rounded-lg bg-slate-100"
                                        >
                                            <img
                                                :src="
                                                    getImageUrl(
                                                        relBook.cover_photo,
                                                    )
                                                "
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            />
                                        </div>
                                        <p
                                            class="mt-2 line-clamp-1 text-xs font-bold"
                                        >
                                            {{ relBook.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM BUY BUTTON (Fixed Mobile) -->
                <!-- Removed heavy shadows and blur -->
                <div
                    class="pb-safe fixed right-0 bottom-0 left-0 z-50 border-t border-slate-200 bg-white px-4 py-3 md:hidden"
                >
                    <div class="flex items-center gap-3">
                        <button
                            @click="
                                isInCart ? handleGoToCart() : handleAddToCart()
                            "
                            :disabled="isAddingToCart"
                            class="flex h-12 flex-[1.4] cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-700 shadow-sm transition-all active:bg-slate-50"
                            :class="
                                isInCart
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-600'
                                    : 'hover:border-blue hover:text-blue'
                            "
                        >
                            <ShoppingCart v-if="!isInCart" class="size-5" />
                            <Check v-else class="size-5" />
                            <span>{{
                                isInCart ? 'কার্টে আছে' : 'কার্ডে যুক্ত করুন'
                            }}</span>
                        </button>
                        <button
                            @click="handleBuyNow"
                            :disabled="isBuyingNow"
                            class="flex h-12 flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue text-sm font-bold text-white shadow-lg shadow-blue/30 transition-all hover:bg-blue-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-80"
                        >
                            <template v-if="isBuyingNow">
                                <span
                                    class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                                ></span>
                                <span>প্রসেসিং...</span>
                            </template>
                            <template v-else>
                                <ShoppingBag class="size-5" />
                                <span>কিনুন</span>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Toast Notification -->
                <div
                    v-if="showToast"
                    class="fixed top-20 left-1/2 z-[110] flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-6 py-2 text-white shadow-lg"
                >
                    <Check class="size-4 text-green-400" />
                    <span class="text-sm font-bold">{{ toastMessage }}</span>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Only Essential CSS */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.pb-safe {
    padding-bottom: max(env(safe-area-inset-bottom, 20px), 20px);
}

/* Simplified Modal Transition */
.modal-slide-enter-active,
.modal-slide-leave-active {
    transition: transform 0.3s ease-out;
}
.modal-slide-enter-from,
.modal-slide-leave-to {
    transform: translateX(100%);
}
</style>
