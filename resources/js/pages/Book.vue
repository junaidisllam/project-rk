<script setup lang="ts">
import BookSkeleton from '@/components/BookSkeleton.vue';
import Card from '@/components/Card.vue';
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import { useCartStore } from '@/Stores/cart';
import { Deferred, Link, router, usePage } from '@inertiajs/vue3';
import {
    Check,
    ChevronRight,
    CreditCard,
    FileText,
    Heart,
    List,
    Package,
    RefreshCw,
    Share2,
    ShieldCheck,
    ShoppingBagIcon,
    ShoppingCart,
    Truck,
    User,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

// --- Interfaces ---
interface Writer {
    name: string;
    photo?: string | null;
    biography?: string | null;
}

interface Category {
    name: string;
}

interface BookData {
    id: number;
    name: string;
    slug: string;
    writer: Writer;
    category: Category;
    cover_photo: string | null;
    description: string;
    price: number;
    original_price: number | null;
    rating?: number;
    reviews?: number;
    tags?: string[];
    isbn?: string;
    publisher?: string;
    pages?: number;
    specification?: Record<string, string> | null;
}

const props = defineProps<{
    book?: BookData;
    relatedBooks?: BookData[];
    title?: string;
    description?: string;
}>();

const cartStore = useCartStore();
const activeTab = ref('summary');
const isAddingToCart = ref(false);
const addedToCartSuccess = ref(false);
const isWishlisted = ref(false);
const showShareMenu = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);

// Update isWishlisted when book data is loaded
watch(
    () => props.book,
    (newBook) => {
        if (newBook && (newBook as any).is_wishlisted !== undefined) {
            isWishlisted.value = (newBook as any).is_wishlisted;
        }
    },
    { immediate: true },
);

// --- Computed & Methods ---

const isBuyingNow = ref(false);
const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

// --- Computed & Methods ---
const isInCart = computed(() =>
    props.book
        ? cartStore.items.some((item) => item.id === props.book?.id)
        : false,
);

const handleGoToCart = () => {
    if (isMobile.value) {
        window.dispatchEvent(new CustomEvent('open-cart-modal'));
    } else {
        router.visit('/cart');
    }
};

// --- Computed & Methods ---

const specifications = computed(() => {
    if (!props.book) return [];

    const baseSpecs = [
        { label: 'বইয়ের নাম', value: props.book.name },
        { label: 'লেখক', value: props.book.writer.name },
        { label: 'ক্যাটেগরি', value: props.book.category.name },
    ];

    // Convert dynamic JSON specifications to the required format and filter out empty values
    const dynamicSpecs = props.book.specification
        ? Object.entries(props.book.specification as Record<string, string>)
              .filter(([_, value]) => value && value.trim() !== '')
              .map(([key, value]) => ({
                  label: key,
                  value: value,
              }))
        : [];

    return [...baseSpecs, ...dynamicSpecs];
});

const handleAddToCart = () => {
    if (!props.book || isAddingToCart.value) return;

    isAddingToCart.value = true;

    setTimeout(() => {
        cartStore.add(props.book!);
        isAddingToCart.value = false;
        addedToCartSuccess.value = true;

        setTimeout(() => {
            addedToCartSuccess.value = false;
        }, 2500);
    }, 400);
};

// --- Buy Now Logic ---
const handleBuyNow = () => {
    if (!props.book || isBuyingNow.value) return;

    isBuyingNow.value = true;

    // Add to cart first
    cartStore.add(props.book);

    // Ensure item is selected (in case it was in cart but unselected)
    const cartItem = cartStore.items.find((i) => i.id === props.book?.id);
    if (cartItem && !cartItem.selected) {
        // Toggle to select it
        cartStore.toggleSelectedItem(props.book.id);
    }

    // Redirect to checkout page with a slight delay for better UX
    setTimeout(() => {
        router.visit('/checkout');
        // Reset loading state after navigation starts (optional, as page will unmount)
        // isBuyingNow.value = false;
    }, 400);
};

const toggleWishlist = () => {
    if (!user.value) {
        router.visit('/login');
        return;
    }

    if (!props.book) return;

    // Optimistic UI update
    isWishlisted.value = !isWishlisted.value;

    router.post(
        '/wishlist/toggle',
        {
            book_id: props.book.id,
        },
        {
            preserveScroll: true,
            onError: () => {
                // Revert on failure
                isWishlisted.value = !isWishlisted.value;
            },
        },
    );
};

const handleShare = async () => {
    if (navigator.share) {
        try {
            await navigator.share({
                title: props.title || props.book?.name,
                text: props.description || props.book?.description,
                url: window.location.href,
            });
        } catch (error) {
            console.error('Error sharing:', error);
            copyToClipboard();
        }
    } else {
        copyToClipboard();
    }
};

const copyToClipboard = () => {
    navigator.clipboard.writeText(window.location.href);
    alert('Link copied to clipboard!');
};

const calculateDiscount = (price: number, originalPrice: number | null) => {
    if (!originalPrice) return 0;
    return Math.round(((originalPrice - price) / originalPrice) * 100);
};

const authorBio =
    'John P. Strelecky is a #1 Bestselling inspirational author. His books have been translated into over forty languages and sold more than six million copies worldwide. He has been honored as one of the 100 Most Influential Thought Leaders in the field of Leadership and Personal Development.';

const getImageUrl = (path: string | number | null | undefined) => {
    if (!path || path === '0' || path === 0) return undefined;
    return path.toString().startsWith('http')
        ? path.toString()
        : `/storage/${path}`;
};
</script>
<style>
@media (width < 48rem) {
    footer {
        display: none !important;
    }
}
</style>
<template>
    <Head :title="props.title">
        <meta
            v-if="props.description"
            name="description"
            :content="props.description"
        />
    </Head>
    <Heading :hide-mobile-nav="true" />

    <div
        class="min-h-screen bg-gradient-to-b from-slate-50 to-white pb-28 font-sans text-slate-800 sm:pb-20"
    >
        <Deferred :data="['book', 'relatedBooks']">
            <template #fallback>
                <BookSkeleton />
            </template>

            <div
                v-if="book"
                class="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-6 lg:px-8"
            >
                <!-- Breadcrumbs -->
                <nav
                    class="scrollbar-hide mb-4 flex items-center gap-2 overflow-x-auto text-sm whitespace-nowrap sm:mb-6"
                >
                    <Link
                        href="/"
                        class="text-slate-500 transition-colors hover:text-blue"
                        >হোম</Link
                    >
                    <ChevronRight class="size-3.5 text-slate-300" />
                    <Link
                        href="/allbooks"
                        class="text-slate-500 transition-colors hover:text-blue"
                        >সকল বই</Link
                    >
                    <ChevronRight class="size-3.5 text-slate-300" />
                    <span class="font-medium text-slate-800">{{
                        book.name
                    }}</span>
                </nav>

                <!-- Top Section: Grid -->
                <div
                    class="mb-8 grid grid-cols-1 gap-6 sm:mb-12 sm:gap-8 lg:grid-cols-12 lg:gap-12"
                >
                    <!-- Left: Images -->
                    <div
                        class="flex flex-col items-center lg:col-span-4 xl:col-span-4"
                    >
                        <div
                            class="group perspective-1000 relative mx-auto w-full max-w-[280px] sm:max-w-[320px]"
                        >
                            <!-- Background Decor -->
                            <div
                                class="absolute -inset-4 rounded-full bg-gradient-to-tr from-blue-100 via-purple-50 to-pink-100 opacity-40 blur-3xl transition-opacity duration-500 group-hover:opacity-60"
                            ></div>

                            <!-- Main Image Container -->
                            <div
                                class="relative overflow-hidden rounded-l-md rounded-r-2xl bg-white shadow-xl transition-all duration-500 group-hover:-translate-y-2 group-hover:shadow-2xl"
                            >
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 z-10 w-3 bg-gradient-to-r from-slate-900/5 to-transparent"
                                ></div>
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-[2px] z-10 w-[1px] bg-white/40"
                                ></div>

                                <img
                                    :src="
                                        getImageUrl(book.cover_photo) ||
                                        '/images/placeholder-book.jpg'
                                    "
                                    :alt="book.name"
                                    class="aspect-[2/3] h-auto w-full object-cover"
                                />

                                <!-- Wishlist Button -->
                                <button
                                    @click="toggleWishlist"
                                    class="absolute top-3 right-3 z-20 rounded-full bg-white/95 p-2.5 shadow-lg backdrop-blur-md transition-all hover:scale-110 active:scale-95"
                                    :class="
                                        isWishlisted
                                            ? 'text-red-500'
                                            : 'text-slate-400 hover:text-red-400'
                                    "
                                >
                                    <Heart
                                        class="size-5"
                                        :class="
                                            isWishlisted ? 'fill-current' : ''
                                        "
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Trust Badges (Desktop) -->
                        <div
                            class="mt-6 hidden w-full max-w-[320px] grid-cols-2 gap-3 lg:grid"
                        >
                            <div
                                class="flex flex-col items-center justify-center gap-2 rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-center transition-all hover:border-blue-200 hover:bg-blue-50"
                            >
                                <ShieldCheck class="size-6 text-blue" />
                                <span class="text-xs font-bold text-blue-900"
                                    >১০০% আসল পণ্য</span
                                >
                            </div>
                            <div
                                class="flex flex-col items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-center transition-all hover:border-slate-300 hover:bg-slate-100"
                            >
                                <RefreshCw class="size-6 text-slate-600" />
                                <span class="text-xs font-bold text-slate-900"
                                    >৭ দিনের রিটার্ন</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Right: Product Info -->
                    <div class="flex flex-col lg:col-span-8 xl:col-span-8">
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-8 lg:p-10"
                        >
                            <!-- Book Meta -->
                            <div
                                class="mb-4 flex flex-wrap items-center justify-between gap-3"
                            >
                                <Link
                                    href="#"
                                    class="inline-flex items-center gap-2 rounded-full bg-blue/10 px-3.5 py-1.5 text-xs font-bold tracking-wide text-blue uppercase transition-all hover:bg-blue/20"
                                >
                                    {{ book.category.name }}
                                </Link>

                                <div
                                    class="flex items-center gap-3 text-sm sm:gap-4"
                                >
                                    <!-- REMOVED: Ratings and Reviews Section as requested -->

                                    <button
                                        @click="handleShare"
                                        class="relative flex items-center gap-1.5 text-slate-500 transition-colors hover:text-blue active:scale-95"
                                    >
                                        <Share2 class="size-4" />
                                        <span class="hidden sm:inline"
                                            >Share</span
                                        >
                                    </button>
                                </div>
                            </div>

                            <h1
                                class="mb-2 text-xl leading-tight font-extrabold text-slate-900 sm:text-2xl lg:text-4xl"
                            >
                                {{ book.name }}
                            </h1>

                            <div class="mb-5 flex items-center gap-2 sm:mb-6">
                                <span
                                    class="text-sm text-slate-500 sm:text-base"
                                    >by</span
                                >
                                <Link
                                    href="#"
                                    class="text-base font-semibold text-blue transition-colors hover:text-blue-hover hover:underline sm:text-lg"
                                    >{{ book.writer.name }}</Link
                                >
                            </div>

                            <!-- Tags -->
                            <div
                                v-if="book.tags && book.tags.length"
                                class="mb-6 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="tag in book.tags"
                                    :key="tag"
                                    class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-100"
                                >
                                    #{{ tag }}
                                </span>
                            </div>

                            <!-- Pricing Block -->
                            <div
                                class="mb-6 inline-flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 sm:mb-8 sm:gap-5 sm:p-5"
                            >
                                <div class="flex flex-col">
                                    <span
                                        class="text-2xl font-bold text-slate-900 sm:text-3xl"
                                        >৳{{ book.price }}</span
                                    >
                                </div>
                                <div
                                    v-if="book.original_price"
                                    class="flex flex-col items-start border-l border-slate-300 pl-4 sm:pl-5"
                                >
                                    <span
                                        class="text-base font-medium text-slate-400 line-through sm:text-lg"
                                        >৳{{ book.original_price }}</span
                                    >
                                    <span
                                        class="rounded-md bg-red-500 px-2 py-0.5 text-xs font-bold text-white shadow-sm"
                                    >
                                        {{
                                            calculateDiscount(
                                                book.price,
                                                book.original_price,
                                            )
                                        }}% OFF
                                    </span>
                                </div>
                            </div>

                            <!-- Short Description -->
                            <div
                                class="prose prose-slate prose-sm mb-6 max-w-2xl leading-relaxed text-slate-600 sm:mb-8"
                            >
                                <p class="line-clamp-3 text-sm sm:text-base">
                                    {{ book.description }}
                                </p>
                                <button
                                    @click="
                                        activeTab = 'summary';
                                        $el.closest('body')
                                            .querySelector('#details')
                                            ?.scrollIntoView({
                                                behavior: 'smooth',
                                                block: 'start',
                                            });
                                    "
                                    class="mt-2 inline-flex items-center gap-1 text-sm font-bold text-blue transition-colors hover:text-blue-hover hover:underline"
                                >
                                    বিস্তারিত পড়ুন
                                    <ChevronRight class="size-3.5" />
                                </button>
                            </div>

                            <!-- Desktop Actions -->
                            <div class="hidden items-center gap-4 md:flex">
                                <!-- Add to Cart (Dark) -->
                                <button
                                    @click="
                                        isInCart
                                            ? handleGoToCart()
                                            : handleAddToCart()
                                    "
                                    :disabled="
                                        isAddingToCart || addedToCartSuccess
                                    "
                                    class="group relative flex max-w-xs flex-1 items-center justify-center gap-2.5 overflow-hidden rounded-xl px-8 py-4 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0 active:shadow-md disabled:cursor-not-allowed"
                                    :class="
                                        isInCart || addedToCartSuccess
                                            ? 'bg-slate-900 shadow-slate-300'
                                            : 'bg-slate-900 shadow-slate-300 hover:bg-slate-800'
                                    "
                                >
                                    <span
                                        v-if="isAddingToCart"
                                        class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                                    ></span>
                                    <Check
                                        v-else-if="
                                            isInCart || addedToCartSuccess
                                        "
                                        class="size-5"
                                    />
                                    <ShoppingCart v-else class="size-5" />
                                    <span>{{
                                        isInCart || addedToCartSuccess
                                            ? isInCart && !addedToCartSuccess
                                                ? 'কার্টে আছে →'
                                                : 'কার্টে যুক্ত হয়েছে'
                                            : 'কার্টে যোগ করুন'
                                    }}</span>
                                </button>

                                <!-- Buy Now (Blue - Primary) -->
                                <button
                                    @click="handleBuyNow"
                                    :disabled="isBuyingNow"
                                    class="flex items-center justify-center gap-2 rounded-xl bg-blue px-8 py-4 text-sm font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-blue-hover hover:shadow-blue/30 active:translate-y-0 active:scale-95 disabled:cursor-not-allowed disabled:opacity-80"
                                >
                                    <span
                                        v-if="isBuyingNow"
                                        class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                                    ></span>
                                    <CreditCard v-else class="size-5" />
                                    <span>{{
                                        isBuyingNow
                                            ? 'প্রসেসিং...'
                                            : 'এখনই কিনুন'
                                    }}</span>
                                </button>
                            </div>

                            <!-- Mobile Quick Info Cards -->
                            <div
                                class="mt-6 grid grid-cols-2 gap-2.5 sm:gap-3 md:hidden"
                            >
                                <div
                                    class="flex items-center gap-2.5 rounded-xl border border-blue-100 bg-blue-50/50 p-3 transition-all active:scale-95"
                                >
                                    <Truck class="size-5 shrink-0 text-blue" />
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-blue-900"
                                            >দ্রুত ডেলিভারি</span
                                        >
                                        <span
                                            class="text-[10px] text-blue-700/70"
                                            >২-৫ দিনের মধ্যে</span
                                        >
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3 transition-all active:scale-95"
                                >
                                    <Package
                                        class="size-5 shrink-0 text-slate-600"
                                    />
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-slate-900"
                                            >ক্যাশ অন ডেলিভারি</span
                                        >
                                        <span class="text-[10px] text-slate-500"
                                            >সহজ পেমেন্ট</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Section -->
                <div
                    id="details"
                    class="mb-12 grid grid-cols-1 gap-6 sm:mb-16 sm:gap-8 lg:grid-cols-12 lg:gap-12"
                >
                    <!-- Sidebar (Desktop) -->
                    <div class="hidden lg:col-span-4 lg:block">
                        <div class="sticky top-24 space-y-4">
                            <!-- Help Card -->
                            <!-- <div
                                class="overflow-hidden rounded-2xl border border-blue-100 bg-blue-50/50 p-6 shadow-sm"
                            >
                                <div
                                    class="mb-3 flex items-center gap-3 text-blue"
                                >
                                    <Phone class="size-5" />
                                    <h4 class="font-bold">সাহায্য প্রয়োজন?</h4>
                                </div>
                                <p
                                    class="mb-4 text-sm leading-relaxed text-blue-800/90"
                                >
                                    আমাদের কল করুন <strong>১৬২৯৭</strong> নম্বরে
                                </p>
                                <div
                                    class="mb-3 flex items-center gap-2 text-xs text-blue-700/80"
                                >
                                    <Clock class="size-4" />
                                    <span>সকাল ৯টা - রাত ১০টা</span>
                                </div>
                                <button
                                    class="w-full rounded-xl bg-white py-3 text-sm font-bold text-blue shadow-sm transition-all hover:bg-blue-100 hover:shadow active:scale-95"
                                >
                                    এখনই কল করুন
                                </button>
                            </div> -->

                            <!-- Features List -->
                            <div
                                class="space-y-2.5 rounded-2xl border border-slate-200 bg-white p-5"
                            >
                                <h4 class="mb-3 font-bold text-slate-900">
                                    আমাদের সেবা
                                </h4>
                                <div
                                    class="flex items-center gap-3 text-sm text-slate-600"
                                >
                                    <ShieldCheck
                                        class="size-5 shrink-0 text-slate-600"
                                    />
                                    <span>১০০% অরিজিনাল পণ্য</span>
                                </div>
                                <div
                                    class="flex items-center gap-3 text-sm text-slate-600"
                                >
                                    <Truck class="size-5 shrink-0 text-blue" />
                                    <span>সারাদেশে হোম ডেলিভারি</span>
                                </div>
                                <div
                                    class="flex items-center gap-3 text-sm text-slate-600"
                                >
                                    <RefreshCw
                                        class="size-5 shrink-0 text-slate-600"
                                    />
                                    <span>৭ দিনের রিটার্ন পলিসি</span>
                                </div>
                                <div
                                    class="flex items-center gap-3 text-sm text-slate-600"
                                >
                                    <Package
                                        class="size-5 shrink-0 text-slate-600"
                                    />
                                    <span>ক্যাশ অন ডেলিভারি</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Content -->
                    <div class="col-span-1 lg:col-span-8">
                        <div
                            class="min-h-[400px] overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm sm:rounded-3xl"
                        >
                            <!-- Tab Headers -->
                            <div
                                class="scrollbar-hide flex overflow-x-auto border-b border-slate-200/60 bg-slate-50/50"
                            >
                                <button
                                    v-for="tab in [
                                        'summary',
                                        'specification',
                                        'author',
                                    ]"
                                    :key="tab"
                                    @click="activeTab = tab"
                                    class="relative flex-shrink-0 px-5 py-4 text-sm font-bold whitespace-nowrap text-slate-500 transition-all outline-none hover:bg-white hover:text-slate-800 focus:outline-none active:scale-95 sm:px-6 sm:py-5"
                                    :class="{
                                        'bg-white text-blue': activeTab === tab,
                                    }"
                                >
                                    <span class="flex items-center gap-2">
                                        <FileText
                                            v-if="tab === 'summary'"
                                            class="size-4"
                                        />
                                        <List
                                            v-if="tab === 'specification'"
                                            class="size-4"
                                        />
                                        <User
                                            v-if="tab === 'author'"
                                            class="size-4"
                                        />
                                        {{
                                            tab === 'summary'
                                                ? 'সারাংশ'
                                                : tab === 'specification'
                                                  ? 'স্পেসিফিকেশন'
                                                  : 'লেখক'
                                        }}
                                    </span>
                                    <span
                                        v-if="activeTab === tab"
                                        class="absolute bottom-0 left-0 h-1 w-full bg-blue transition-all"
                                    ></span>
                                </button>
                            </div>

                            <!-- Tab Content -->
                            <div class="p-5 sm:p-8">
                                <transition name="fade" mode="out-in">
                                    <div
                                        v-if="activeTab === 'summary'"
                                        key="summary"
                                        class="animate-in duration-300 fade-in slide-in-from-bottom-2"
                                    >
                                        <h3
                                            class="mb-4 text-lg font-bold text-slate-900 sm:text-xl"
                                        >
                                            বইয়ের বিস্তারিত
                                        </h3>
                                        <div
                                            class="prose prose-slate max-w-none text-sm leading-relaxed text-slate-600 sm:text-base"
                                        >
                                            <p>{{ book.description }}</p>
                                        </div>
                                    </div>

                                    <div
                                        v-else-if="
                                            activeTab === 'specification'
                                        "
                                        key="specs"
                                        class="animate-in duration-300 fade-in slide-in-from-bottom-2"
                                    >
                                        <h3
                                            class="mb-5 text-lg font-bold text-slate-900 sm:mb-6 sm:text-xl"
                                        >
                                            স্পেসিফিকেশন
                                        </h3>
                                        <div
                                            class="overflow-hidden rounded-xl border border-slate-200"
                                        >
                                            <table
                                                class="w-full text-left text-sm"
                                            >
                                                <tbody>
                                                    <tr
                                                        v-for="(
                                                            spec, idx
                                                        ) in specifications"
                                                        :key="idx"
                                                        class="border-b border-slate-100 transition-colors last:border-0 odd:bg-slate-50/50 hover:bg-slate-100/50"
                                                    >
                                                        <td
                                                            class="w-1/3 px-4 py-3.5 font-semibold text-slate-700 sm:px-6 sm:py-4"
                                                        >
                                                            {{ spec.label }}
                                                        </td>
                                                        <td
                                                            class="px-4 py-3.5 text-slate-600 sm:px-6 sm:py-4"
                                                        >
                                                            {{ spec.value }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div
                                        v-else-if="activeTab === 'author'"
                                        key="author"
                                        class="animate-in duration-300 fade-in slide-in-from-bottom-2"
                                    >
                                        <div
                                            class="flex flex-col items-start gap-5 sm:flex-row sm:gap-8"
                                        >
                                            <div class="relative shrink-0">
                                                <div
                                                    class="size-20 overflow-hidden rounded-full border-2 border-white shadow-lg ring-2 ring-slate-100 sm:size-24"
                                                >
                                                    <Avatar
                                                        class="h-full w-full"
                                                    >
                                                        <img
                                                            :src="
                                                                getImageUrl(
                                                                    book.writer
                                                                        .photo,
                                                                ) ||
                                                                '/images/placeholder-author.jpg'
                                                            "
                                                            :alt="
                                                                book.writer.name
                                                            "
                                                            class="h-full w-full object-cover"
                                                        />
                                                    </Avatar>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h3
                                                    class="mb-1 text-lg font-bold text-slate-900 sm:text-xl"
                                                >
                                                    {{ book.writer.name }}
                                                </h3>
                                                <p
                                                    class="mb-4 text-xs text-slate-500 sm:text-sm"
                                                >
                                                    লেখক ও মোটিভেশনাল স্পিকার
                                                </p>
                                                <div
                                                    class="prose prose-slate mb-4 text-xs leading-relaxed text-slate-600 sm:text-sm"
                                                >
                                                    {{
                                                        book.writer.biography ||
                                                        authorBio
                                                    }}
                                                </div>
                                                <Link
                                                    href="/allbooks"
                                                    class="inline-flex items-center gap-1.5 text-sm font-bold text-blue transition-all hover:gap-2 hover:text-blue-hover"
                                                >
                                                    লেখকের সকল বই দেখুন
                                                    <ChevronRight
                                                        class="size-4"
                                                    />
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Books -->
                <div class="border-t border-slate-200 pt-8 sm:pt-10">
                    <div class="mb-6 flex items-center justify-between sm:mb-8">
                        <div>
                            <h2
                                class="text-xl font-bold text-slate-900 sm:text-2xl"
                            >
                                সম্পর্কিত বই
                            </h2>
                            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                এই বইটির সাথে মিল রেখে আপনার জন্য সাজানো
                            </p>
                        </div>
                        <Link
                            href="/allbooks"
                            class="hidden items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition-all hover:border-blue-300 hover:bg-slate-50 active:scale-95 sm:flex"
                        >
                            সবগুলো দেখুন
                            <ChevronRight class="size-4" />
                        </Link>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 sm:gap-x-4 sm:gap-y-8 md:grid-cols-4 lg:grid-cols-6"
                    >
                        <div
                            v-for="relBook in relatedBooks"
                            :key="relBook.id"
                            class="group"
                        >
                            <Card
                                :id="relBook.id"
                                :name="relBook.name"
                                :writer="{ name: relBook.writer.name }"
                                :category="{ name: '' }"
                                :cover_photo="relBook.cover_photo"
                                :price="relBook.price"
                                :original_price="relBook.original_price"
                                :slug="relBook.slug"
                                class="h-full"
                            />
                        </div>
                    </div>

                    <div class="mt-6 text-center sm:hidden">
                        <Link
                            href="/allbooks"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 transition-all active:scale-95"
                        >
                            আরো দেখুন
                            <ChevronRight class="size-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </Deferred>
    </div>

    <!-- Enhanced Mobile Fixed Bottom Bar -->
    <div
        v-if="book"
        class="pb-safe fixed right-0 bottom-0 left-0 z-50 border-t border-slate-200/80 bg-white/95 px-4 py-3 shadow-[0_-8px_30px_rgba(0,0,0,0.08)] backdrop-blur-xl md:hidden"
    >
        <div class="mx-auto flex max-w-lg items-center gap-3">
            <button
                @click="isInCart ? handleGoToCart() : handleAddToCart()"
                :disabled="isAddingToCart || addedToCartSuccess"
                class="flex h-12 flex-[1.4] cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-700 shadow-sm transition-all active:bg-slate-50 disabled:cursor-not-allowed"
                :class="
                    isInCart || addedToCartSuccess
                        ? 'border-emerald-500 bg-emerald-50 text-emerald-600'
                        : 'hover:border-blue hover:text-blue'
                "
            >
                <div
                    v-if="!isAddingToCart && !addedToCartSuccess && !isInCart"
                    class="animate-shimmer absolute inset-0 -translate-x-full bg-linear-to-r from-transparent via-white/10 to-transparent"
                ></div>
                <span
                    v-if="isAddingToCart"
                    class="h-5 w-5 animate-spin rounded-full border-2 border-slate-400 border-t-transparent"
                ></span>
                <Check
                    v-else-if="isInCart || addedToCartSuccess"
                    class="size-5"
                />
                <ShoppingCart v-else class="size-5" />
                <span class="relative z-10">{{
                    isInCart || addedToCartSuccess
                        ? isInCart && !addedToCartSuccess
                            ? 'কার্টে আছে'
                            : 'যুক্ত হয়েছে ✓'
                        : 'কার্ডে যুক্ত করুন'
                }}</span>
            </button>
            <button
                @click="handleBuyNow"
                :disabled="isBuyingNow"
                class="flex h-12 flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue text-sm font-bold text-white shadow-lg shadow-blue/30 transition-all hover:bg-blue-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-80"
            >
                <span
                    v-if="isBuyingNow"
                    class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                ></span>
                <ShoppingBagIcon v-else class="size-5" />
                <span>{{ isBuyingNow ? 'প্রসেসিং...' : 'কিনুন' }}</span>
            </button>
        </div>
    </div>

    <div class="hidden max-md:hidden md:block">
        <Footer />
    </div>
</template>

<style scoped>
/* Utility for hiding scrollbars */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Safe area padding for mobile devices */
.pb-safe {
    padding-bottom: max(env(safe-area-inset-bottom, 20px), 20px);
}

/* Smooth fade transition for tabs */
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* Shimmer animation for CTA button */
@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}

.animate-shimmer {
    animation: shimmer 2s infinite;
}
</style>
