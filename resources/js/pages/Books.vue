<script setup lang="ts">
import Card from '@/components/Card.vue';
import CardSkeleton from '@/components/CardSkeleton.vue';
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import {
    BookOpen,
    Check,
    ChevronDown,
    Filter,
    Search,
    SlidersHorizontal,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

// --- Helper: Expose Ziggy ---
const route = (window as any).route;

// --- Types ---
interface BookData {
    id: number;
    name: string;
    writer: { name: string };
    category: { name: string };
    cover_photo: string | null;
    price: number;
    original_price: number | null;
    slug: string;
}

interface CategoryData {
    id: number;
    name: string;
    slug: string;
}

interface AuthorData {
    id: number;
    name: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedResponse<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

// --- Props ---
const props = defineProps<{
    books?: PaginatedResponse<BookData>;
    categories: CategoryData[];
    authors: AuthorData[];
    filters: {
        search?: string;
        categories?: string[];
        authors?: string[];
        min_price?: string | number;
        max_price?: string | number;
        sort?: string;
    };
    title?: string;
    description?: string;
}>();

// --- State ---
const mobileFiltersOpen = ref(false);
const isFiltering = ref(false);
const sortDropdownOpen = ref(false);

// Form State
const form = ref({
    search: props.filters.search ?? '',
    categories: props.filters.categories ?? [],
    authors: props.filters.authors ?? [],
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
    sort: props.filters.sort ?? 'latest',
});

// Mobile Draft State
const mobileFormDraft = ref({ ...form.value });

// --- AI Search Suggestions ---
const suggestions = ref<any[]>([]);
const searchMeta = ref<{ variants: string[] }>({ variants: [] });
const isSuggestionsLoading = ref(false);
const showSuggestions = ref(false);
const activeSuggestionIndex = ref(-1);
const mobileSuggestions = ref<any[]>([]);

const fetchSuggestions = async () => {
    if (form.value.search.length < 2) {
        suggestions.value = [];
        return;
    }
    isSuggestionsLoading.value = true;
    showSuggestions.value = true;
    try {
        const url = route
            ? route('api.search.suggestions', { q: form.value.search })
            : `/api/search/suggestions?q=${form.value.search}`;
        const { data } = await axios.get(url);
        suggestions.value = data.data;
        searchMeta.value = data.meta || { variants: [] };
    } catch (error) {
        suggestions.value = [];
    } finally {
        isSuggestionsLoading.value = false;
    }
};

const handleMobileSearchInput = debounce(async () => {
    if (mobileFormDraft.value.search.length < 2) {
        mobileSuggestions.value = [];
        return;
    }
    try {
        const { data } = await axios.get(
            route('api.search.suggestions', {
                q: mobileFormDraft.value.search,
            }),
        );
        mobileSuggestions.value = data.data;
    } catch (e) {
        mobileSuggestions.value = [];
    }
}, 300);

const debouncedFetchSuggestions = debounce(fetchSuggestions, 300);

const highlightMatch = (text: string, query: string) => {
    if (!query || query.length < 2) return text;
    // Simple highlight logic
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<span class="text-blue font-bold">$1</span>');
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('bn-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(price);
};

// UI Toggle State
const filterSections = ref([
    { id: 'category', name: 'ক্যাটেগরি', open: true },
    { id: 'author', name: 'লেখক', open: false },
    { id: 'price', name: 'মূল্য (টাকা)', open: true },
]);

// Sort Options
const sortOptions = [
    { label: 'নতুন অগ্রে', value: 'latest' },
    { label: 'জনপ্রিয়', value: 'popular' },
    { label: 'কম মূল্য', value: 'price_low' },
    { label: 'বেশি মূল্য', value: 'price_high' },
];

// --- Actions ---

const updateParams = (newParams: any) => {
    router.get(route('allbooks'), newParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['books', 'filters'],
        onStart: () => {
            isFiltering.value = true;
        },
        onFinish: () => {
            isFiltering.value = false;
        },
    });
};

const debouncedUpdate = debounce(() => {
    updateParams(form.value);
}, 500);

// --- Watchers ---

// Immediate updates (Desktop)
watch(
    () => [form.value.categories, form.value.authors, form.value.sort],
    () => {
        if (!mobileFiltersOpen.value) updateParams(form.value);
    },
    { deep: true },
);

// Debounced updates (Desktop)
watch(
    () => [form.value.search, form.value.min_price, form.value.max_price],
    ([newSearch]) => {
        if (!mobileFiltersOpen.value) {
            debouncedUpdate();
            if (newSearch && String(newSearch).trim() !== '') {
                debouncedFetchSuggestions();
            } else {
                showSuggestions.value = false;
            }
        }
    },
);

const getItemKey = (sectionId: string, item: any) => {
    return sectionId === 'category' ? item.slug : String(item.id);
};

const handleBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

// Custom Checkbox Logic
const toggleSelection = (list: string[], item: string) => {
    const index = list.indexOf(item);
    if (index === -1) {
        list.push(item);
    } else {
        list.splice(index, 1);
    }
};

const isSelected = (list: string[], item: string) => list.includes(item);

// Custom Select Logic
const selectSort = (value: string) => {
    form.value.sort = value;
    sortDropdownOpen.value = false;
};

// Click Outside for Custom Select
const sortDropdownRef = ref<HTMLElement | null>(null);
const handleClickOutside = (event: MouseEvent) => {
    if (
        sortDropdownRef.value &&
        !sortDropdownRef.value.contains(event.target as Node)
    ) {
        sortDropdownOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.body.style.overflow = '';
});

// Mobile Logic
const openMobileFilters = () => {
    mobileFormDraft.value = JSON.parse(JSON.stringify(form.value));
    mobileFiltersOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeMobileFilters = () => {
    mobileFiltersOpen.value = false;
    document.body.style.overflow = '';
};

const applyMobileFilters = () => {
    form.value = { ...mobileFormDraft.value };
    closeMobileFilters();
    updateParams(form.value);
};

const resetFilters = () => {
    const empty = {
        search: '',
        categories: [],
        authors: [],
        min_price: '',
        max_price: '',
        sort: 'latest',
    };
    if (mobileFiltersOpen.value) {
        mobileFormDraft.value = { ...empty };
    } else {
        form.value = { ...empty };
        updateParams(form.value);
    }
};

const hasActiveFilters = computed(() => {
    const f = mobileFiltersOpen.value ? mobileFormDraft.value : form.value;
    return (
        f.search ||
        f.categories.length > 0 ||
        f.authors.length > 0 ||
        f.min_price ||
        f.max_price
    );
});

const handlePagination = (url: string | null) => {
    if (!url) return;
    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                isFiltering.value = true;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            onFinish: () => {
                isFiltering.value = false;
            },
        },
    );
};

const toggleFilterSection = (id: string) => {
    const s = filterSections.value.find((x) => x.id === id);
    if (s) s.open = !s.open;
};

// --- SEO Logic ---
const seoTitle = computed(() => {
    let title = 'বই সমূহ';

    if (form.value.categories.length === 1) {
        const cat = props.categories.find(
            (c) => c.slug === form.value.categories[0],
        );
        if (cat) title = `${cat.name} - সকল বই`;
    } else if (form.value.authors.length === 1) {
        const author = props.authors.find(
            (a) => String(a.id) === String(form.value.authors[0]),
        );
        if (author) title = `${author.name} - এর বইসমূহ`;
    } else if (form.value.search) {
        title = `"${form.value.search}" - সার্চ রেজাল্ট`;
    }

    return props.title || `${title} | এলাকাডটকম`;
});

const seoDescription = computed(() => {
    if (props.description) return props.description;
    if (form.value.categories.length === 1) {
        const cat = props.categories.find(
            (c) => c.slug === form.value.categories[0],
        );
        if (cat)
            return `${cat.name} বিষয়ের সকল বই এর সংগ্রহ আমাদের এলাকাডটকম এ দেখুন।`;
    } else if (form.value.authors.length === 1) {
        const author = props.authors.find(
            (a) => String(a.id) === String(form.value.authors[0]),
        );
        if (author)
            return `${author.name} এর লেখা সকল বই এর সংগ্রহ এলাকাডটকম এ।`;
    } else if (form.value.search) {
        return `"${form.value.search}" সম্পর্কিত সকল বই এর সংগ্রহ দেখুন।`;
    }

    return 'এলাকাডটকম এ আমাদের সকল বই এর বিশাল সংগ্রহ দেখুন। আপনার পছন্দের বই বা লেখক খুঁজে নিন সহজে।';
});
</script>

<template>
    <Head :title="seoTitle">
        <meta name="description" :content="seoDescription" />
    </Head>
    <Heading />

    <div
        class="min-h-screen bg-[#F8F9FA] pb-20 font-sans text-slate-800 lg:pb-0"
    >
        <!-- ========================================== -->
        <!-- MOBILE FILTER SHEET                        -->
        <!-- ========================================== -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="mobileFiltersOpen"
                    class="fixed inset-0 z-100 lg:hidden"
                >
                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                        @click="closeMobileFilters"
                    ></div>

                    <!-- Sheet -->
                    <div
                        class="ease-out-expo absolute inset-x-0 bottom-0 flex h-[90vh] flex-col rounded-t-3xl bg-white shadow-2xl transition-transform duration-500"
                    >
                        <!-- Header -->
                        <div
                            class="flex items-center justify-between border-b border-gray-100 px-6 py-5"
                        >
                            <h2 class="text-xl font-bold text-slate-900">
                                ফিল্টার
                            </h2>
                            <button
                                @click="resetFilters"
                                class="text-sm font-semibold text-rose-500 transition-transform active:scale-95"
                            >
                                রিসেট
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto px-6 py-4">
                            <!-- Mobile Search -->
                            <div class="relative mb-6">
                                <Search
                                    class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />
                                <input
                                    type="text"
                                    v-model="mobileFormDraft.search"
                                    placeholder="বই বা লেখক খুঁজুন..."
                                    class="w-full rounded-2xl border-none bg-slate-100 py-3.5 pr-4 pl-12 text-sm font-medium focus:ring-2 focus:ring-blue/20"
                                    @input="handleMobileSearchInput"
                                />
                                <!-- Mobile Suggestions -->
                                <div
                                    v-if="mobileSuggestions.length > 0"
                                    class="mt-2 overflow-hidden rounded-xl border border-slate-100 bg-white"
                                >
                                    <div
                                        v-for="item in mobileSuggestions"
                                        :key="item.id"
                                        @click="
                                            mobileFormDraft.search = item.name;
                                            mobileSuggestions = [];
                                        "
                                        class="border-b border-slate-50 p-3 text-sm last:border-0 active:bg-blue/5"
                                    >
                                        <p
                                            v-html="
                                                highlightMatch(
                                                    item.name,
                                                    mobileFormDraft.search,
                                                )
                                            "
                                        ></p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-for="section in filterSections"
                                :key="section.id"
                                class="mb-5 border-b border-gray-100 pb-5 last:border-0"
                            >
                                <button
                                    @click="toggleFilterSection(section.id)"
                                    class="flex w-full items-center justify-between py-2 text-base font-bold text-slate-800"
                                >
                                    {{ section.name }}
                                    <ChevronDown
                                        class="h-5 w-5 text-slate-400 transition-transform duration-300"
                                        :class="{ 'rotate-180': section.open }"
                                    />
                                </button>

                                <div
                                    v-show="section.open"
                                    class="space-y-3 pt-4"
                                >
                                    <!-- Mobile Categories (Custom Checkbox) -->
                                    <div
                                        v-if="
                                            section.id === 'category' ||
                                            section.id === 'author'
                                        "
                                    >
                                        <div
                                            v-for="item in section.id ===
                                            'category'
                                                ? categories
                                                : authors"
                                            :key="item.id"
                                            @click="
                                                toggleSelection(
                                                    section.id === 'category'
                                                        ? mobileFormDraft.categories
                                                        : mobileFormDraft.authors,
                                                    getItemKey(
                                                        section.id,
                                                        item,
                                                    ),
                                                )
                                            "
                                            class="flex cursor-pointer items-center justify-between rounded-lg py-2 select-none active:bg-gray-50"
                                        >
                                            <span
                                                class="text-sm font-medium text-slate-600"
                                                >{{ item.name }}</span
                                            >
                                            <!-- Custom Checkbox Visual -->
                                            <div
                                                class="flex h-5 w-5 items-center justify-center rounded border transition-all duration-300"
                                                :class="
                                                    isSelected(
                                                        section.id ===
                                                            'category'
                                                            ? mobileFormDraft.categories
                                                            : mobileFormDraft.authors,
                                                        getItemKey(
                                                            section.id,
                                                            item,
                                                        ),
                                                    )
                                                        ? 'border-blue bg-blue shadow-md shadow-blue/20'
                                                        : 'border-gray-300 bg-white'
                                                "
                                            >
                                                <Check
                                                    class="h-3.5 w-3.5 text-white transition-transform duration-200"
                                                    :class="
                                                        isSelected(
                                                            section.id ===
                                                                'category'
                                                                ? mobileFormDraft.categories
                                                                : mobileFormDraft.authors,
                                                            getItemKey(
                                                                section.id,
                                                                item,
                                                            ),
                                                        )
                                                            ? 'scale-100'
                                                            : 'scale-0'
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mobile Price -->
                                    <div
                                        v-if="section.id === 'price'"
                                        class="flex items-center gap-3"
                                    >
                                        <input
                                            type="number"
                                            v-model="mobileFormDraft.min_price"
                                            placeholder="Min"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 text-center text-sm font-bold focus:border-blue focus:ring-0"
                                        />
                                        <span class="font-bold text-gray-300"
                                            >-</span
                                        >
                                        <input
                                            type="number"
                                            v-model="mobileFormDraft.max_price"
                                            placeholder="Max"
                                            class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 text-center text-sm font-bold focus:border-blue focus:ring-0"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 bg-white p-6 pb-8">
                            <button
                                @click="applyMobileFilters"
                                class="w-full rounded-2xl bg-blue py-4 text-base font-bold text-white shadow-xl shadow-blue/20 transition-all hover:bg-blue-hover active:scale-[0.98]"
                            >
                                এপ্লাই ফিল্টার ({{ books?.total }})
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ========================================== -->
        <!-- MAIN CONTENT                               -->
        <!-- ========================================== -->
        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Desktop Header -->
            <div
                class="hidden items-end justify-between border-b border-gray-200 pt-12 pb-6 lg:flex"
            >
                <div>
                    <h1
                        class="text-4xl font-black tracking-tight text-slate-900"
                    >
                        সকল বই
                    </h1>
                    <p class="mt-2 font-medium text-slate-500">
                        আপনার পছন্দের বই খুঁজে নিন আমাদের বিশাল সংগ্রহ থেকে
                    </p>
                </div>

                <!-- CUSTOM SORT DROPDOWN -->
                <div class="relative z-30" ref="sortDropdownRef">
                    <button
                        @click="sortDropdownOpen = !sortDropdownOpen"
                        class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white py-2.5 pr-3 pl-4 shadow-sm transition-all duration-300 hover:border-blue-300"
                    >
                        <span
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >Sort By:</span
                        >
                        <span
                            class="min-w-[80px] text-left text-sm font-bold text-slate-800"
                        >
                            {{
                                sortOptions.find((o) => o.value === form.sort)
                                    ?.label
                            }}
                        </span>
                        <ChevronDown
                            class="h-4 w-4 text-slate-400 transition-transform duration-300"
                            :class="{ 'rotate-180': sortDropdownOpen }"
                        />
                    </button>

                    <Transition name="dropdown">
                        <div
                            v-if="sortDropdownOpen"
                            class="absolute right-0 mt-2 w-56 origin-top-right overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none"
                        >
                            <div class="p-1">
                                <button
                                    v-for="option in sortOptions"
                                    :key="option.value"
                                    @click="selectSort(option.value)"
                                    class="group flex w-full items-center justify-between rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200"
                                    :class="
                                        form.sort === option.value
                                            ? 'bg-blue/10 text-blue'
                                            : 'text-slate-600 hover:bg-gray-50'
                                    "
                                >
                                    {{ option.label }}
                                    <Check
                                        v-if="form.sort === option.value"
                                        class="h-4 w-4 text-blue"
                                    />
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Mobile Sticky Header -->
            <div
                class="sticky top-[52px] z-30 -mx-4 border-b border-gray-100 bg-white/90 shadow-sm backdrop-blur-md lg:hidden"
                style="z-index: 60"
            >
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-2">
                        <SlidersHorizontal class="h-5 w-5 text-blue" />
                        <span class="font-bold text-slate-800"
                            >{{ books?.total }} টি বই</span
                        >
                    </div>
                    <button
                        @click="openMobileFilters"
                        class="flex items-center gap-2 rounded-full bg-blue px-4 py-2 text-xs font-bold text-white shadow-lg shadow-blue/20 transition-transform active:scale-95"
                    >
                        ফিল্টার করুন <Filter class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <section class="pt-8 pb-24">
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-4">
                    <!-- SIDEBAR (DESKTOP) -->
                    <aside class="hidden lg:col-span-1 lg:block">
                        <div class="sticky top-28 space-y-1">
                            <!-- Header -->
                            <div
                                class="mb-4 flex items-center justify-between px-2"
                            >
                                <h3
                                    class="flex items-center gap-2 font-bold text-slate-900"
                                >
                                    <Filter class="h-4 w-4 text-blue" />
                                    ফিল্টার সমূহ
                                </h3>
                                <button
                                    v-if="hasActiveFilters"
                                    @click="resetFilters"
                                    class="text-xs font-bold text-rose-500 decoration-2 underline-offset-2 hover:underline"
                                >
                                    সব মুছুন
                                </button>
                            </div>

                            <!-- Filter Container -->
                            <div
                                class="custom-scrollbar max-h-[calc(100vh-12rem)] overflow-y-auto rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                            >
                                <div
                                    v-for="section in filterSections"
                                    :key="section.id"
                                    class="border-b border-gray-100 py-4 first:pt-0 last:border-0 last:pb-0"
                                >
                                    <button
                                        @click="toggleFilterSection(section.id)"
                                        class="group mb-3 flex w-full items-center justify-between text-sm font-bold text-slate-800"
                                    >
                                        {{ section.name }}
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-50 transition-colors group-hover:bg-blue/10"
                                        >
                                            <ChevronDown
                                                class="h-3.5 w-3.5 text-slate-400 transition-transform duration-300"
                                                :class="{
                                                    'rotate-180': section.open,
                                                }"
                                            />
                                        </div>
                                    </button>

                                    <div
                                        v-show="section.open"
                                        class="space-y-2.5"
                                    >
                                        <!-- Desktop Price Input -->
                                        <div
                                            v-if="section.id === 'price'"
                                            class="flex items-center gap-2 py-1"
                                        >
                                            <div class="relative flex-1">
                                                <span
                                                    class="absolute top-1/2 left-3 -translate-y-1/2 text-xs text-gray-400"
                                                    >৳</span
                                                >
                                                <input
                                                    type="number"
                                                    v-model="form.min_price"
                                                    class="w-full rounded-lg border-gray-200 bg-gray-50 py-2 pl-6 text-xs font-bold transition-all focus:border-blue focus:ring-0"
                                                    placeholder="0"
                                                />
                                            </div>
                                            <div
                                                class="h-0.5 w-2 bg-gray-300"
                                            ></div>
                                            <div class="relative flex-1">
                                                <span
                                                    class="absolute top-1/2 left-3 -translate-y-1/2 text-xs text-gray-400"
                                                    >৳</span
                                                >
                                                <input
                                                    type="number"
                                                    v-model="form.max_price"
                                                    class="w-full rounded-lg border-gray-200 bg-gray-50 py-2 pl-6 text-xs font-bold transition-all focus:border-blue focus:ring-0"
                                                    placeholder="Max"
                                                />
                                            </div>
                                        </div>

                                        <!-- CUSTOM ANIMATED CHECKBOX LIST (Categories & Authors) -->
                                        <div
                                            v-else
                                            v-for="item in section.id ===
                                            'category'
                                                ? categories
                                                : authors"
                                            :key="item.id"
                                            @click="
                                                toggleSelection(
                                                    section.id === 'category'
                                                        ? form.categories
                                                        : form.authors,
                                                    getItemKey(
                                                        section.id,
                                                        item,
                                                    ),
                                                )
                                            "
                                            class="group flex cursor-pointer items-center justify-between py-1 select-none"
                                        >
                                            <div
                                                class="flex items-center overflow-hidden"
                                            >
                                                <!-- Checkbox Box -->
                                                <div
                                                    class="relative flex h-[18px] w-[18px] flex-none items-center justify-center rounded border transition-all duration-300 ease-out"
                                                    :class="
                                                        isSelected(
                                                            section.id ===
                                                                'category'
                                                                ? form.categories
                                                                : form.authors,
                                                            getItemKey(
                                                                section.id,
                                                                item,
                                                            ),
                                                        )
                                                            ? 'border-blue bg-blue shadow-md shadow-blue/30'
                                                            : 'border-gray-300 bg-white group-hover:border-blue'
                                                    "
                                                >
                                                    <Check
                                                        class="h-3 w-3 text-white transition-all duration-300"
                                                        :class="
                                                            isSelected(
                                                                section.id ===
                                                                    'category'
                                                                    ? form.categories
                                                                    : form.authors,
                                                                getItemKey(
                                                                    section.id,
                                                                    item,
                                                                ),
                                                            )
                                                                ? 'scale-100 opacity-100'
                                                                : 'scale-0 opacity-0'
                                                        "
                                                    />
                                                </div>

                                                <!-- Label with Hover Slide Effect -->
                                                <span
                                                    class="ml-3 text-sm font-medium text-slate-600 transition-all duration-300 group-hover:translate-x-1"
                                                    :class="{
                                                        'font-bold text-blue':
                                                            isSelected(
                                                                section.id ===
                                                                    'category'
                                                                    ? form.categories
                                                                    : form.authors,
                                                                getItemKey(
                                                                    section.id,
                                                                    item,
                                                                ),
                                                            ),
                                                    }"
                                                >
                                                    {{ item.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- PRODUCT GRID AREA -->
                    <div class="lg:col-span-3">
                        <!-- Desktop Toolbar -->
                        <div class="mb-8 hidden lg:block">
                            <div
                                class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-2 shadow-sm"
                            >
                                <div class="relative flex-1">
                                    <Search
                                        class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <input
                                        v-model="form.search"
                                        type="text"
                                        placeholder="বই বা লেখকের নাম লিখুন..."
                                        class="w-full rounded-xl border-none bg-slate-50 py-3 pr-10 pl-11 text-sm font-medium transition-all focus:ring-2 focus:ring-blue/10"
                                        @focus="showSuggestions = true"
                                        @blur="handleBlur"
                                    />
                                    <button
                                        v-if="form.search"
                                        @click="form.search = ''"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-1 text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-500"
                                    >
                                        <X class="h-3.5 w-3.5" />
                                    </button>

                                    <!-- Suggestions Dropdown -->
                                    <div
                                        v-if="
                                            showSuggestions &&
                                            (isSuggestionsLoading ||
                                                suggestions.length > 0)
                                        "
                                        class="absolute top-full left-0 z-50 mt-2 w-full overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl ring-1 ring-black/5"
                                    >
                                        <div
                                            v-if="isSuggestionsLoading"
                                            class="space-y-3 p-4"
                                        >
                                            <div
                                                v-for="i in 3"
                                                :key="i"
                                                class="flex animate-pulse items-center gap-3"
                                            >
                                                <div
                                                    class="size-10 rounded-lg bg-slate-100"
                                                ></div>
                                                <div class="flex-1 space-y-2">
                                                    <div
                                                        class="h-3 w-1/2 rounded bg-slate-100"
                                                    ></div>
                                                    <div
                                                        class="h-2 w-1/4 rounded bg-slate-50"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="max-h-[400px] overflow-y-auto p-2"
                                        >
                                            <div
                                                v-for="item in suggestions"
                                                :key="item.id"
                                                @click="
                                                    form.search = item.name;
                                                    showSuggestions = false;
                                                    updateParams(form);
                                                "
                                                class="flex cursor-pointer items-center gap-3 rounded-xl p-3 transition-colors hover:bg-blue/5"
                                            >
                                                <div
                                                    class="relative size-10 shrink-0 overflow-hidden rounded-lg bg-slate-50 ring-1 ring-slate-100"
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
                                                        <Search
                                                            class="size-4"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p
                                                        class="truncate text-xs font-bold text-slate-800"
                                                        v-html="
                                                            highlightMatch(
                                                                item.name,
                                                                form.search,
                                                            )
                                                        "
                                                    ></p>
                                                    <div
                                                        class="mt-0.5 flex items-center justify-between"
                                                    >
                                                        <span
                                                            class="text-[10px] text-slate-400"
                                                            >{{
                                                                item.type
                                                            }}</span
                                                        >
                                                        <span
                                                            v-if="item.price"
                                                            class="text-[10px] font-black text-blue"
                                                            >{{
                                                                formatPrice(
                                                                    item.price,
                                                                )
                                                            }}</span
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-3 pr-4 text-sm font-medium text-slate-500"
                                >
                                    <span
                                        class="rounded-full bg-slate-100 px-3 py-1 font-bold text-slate-900"
                                        >{{ books?.from || 0 }}-{{
                                            books?.to || 0
                                        }}</span
                                    >
                                    <span>of</span>
                                    <span class="font-bold text-slate-900"
                                        >{{ books?.total }} books</span
                                    >
                                </div>
                            </div>

                            <!-- Active Filters Pills -->
                            <div
                                v-if="hasActiveFilters"
                                class="mt-4 flex flex-wrap gap-2"
                            >
                                <TransitionGroup name="list">
                                    <div
                                        v-for="cat in form.categories"
                                        :key="cat"
                                        class="flex items-center gap-1.5 rounded-full bg-blue/10 px-3 py-1.5 text-xs font-bold text-blue ring-1 ring-blue/20"
                                    >
                                        {{
                                            categories.find(
                                                (c) => c.slug === cat,
                                            )?.name
                                        }}
                                        <button
                                            @click="
                                                toggleSelection(
                                                    form.categories,
                                                    cat,
                                                )
                                            "
                                            class="hover:text-rose-500"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <div
                                        v-for="id in form.authors"
                                        :key="id"
                                        class="flex items-center gap-1.5 rounded-full bg-blue/10 px-3 py-1.5 text-xs font-bold text-blue ring-1 ring-blue/20"
                                    >
                                        {{
                                            authors.find(
                                                (a) => String(a.id) === id,
                                            )?.name
                                        }}
                                        <button
                                            @click="
                                                toggleSelection(
                                                    form.authors,
                                                    id,
                                                )
                                            "
                                            class="hover:text-rose-500"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </TransitionGroup>
                            </div>
                        </div>

                        <!-- Content with Loading State -->
                        <div class="relative min-h-[400px]">
                            <!-- Skeleton Loader -->
                            <Transition name="fade" mode="out-in">
                                <div
                                    v-if="isFiltering || !books"
                                    class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4"
                                >
                                    <div
                                        v-for="n in 8"
                                        :key="n"
                                        class="mx-auto w-full max-w-[180px]"
                                    >
                                        <CardSkeleton />
                                    </div>
                                </div>

                                <!-- Actual Grid -->
                                <div v-else>
                                    <div v-if="books && books.data.length > 0">
                                        <TransitionGroup
                                            tag="div"
                                            name="list"
                                            class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4"
                                        >
                                            <div
                                                v-for="book in books.data"
                                                :key="book.id"
                                                class="mx-auto w-full max-w-[180px]"
                                            >
                                                <Card
                                                    :id="book.id"
                                                    :name="book.name"
                                                    :writer="{
                                                        name: book.writer.name,
                                                    }"
                                                    :category="{
                                                        name:
                                                            book.category
                                                                ?.name || '',
                                                    }"
                                                    :cover_photo="
                                                        book.cover_photo
                                                    "
                                                    :price="book.price"
                                                    :original_price="
                                                        book.original_price
                                                    "
                                                    :slug="book.slug"
                                                />
                                            </div>
                                        </TransitionGroup>

                                        <!-- PAGINATION UI -->
                                        <div
                                            class="mt-16 flex flex-wrap items-center justify-center gap-2"
                                        >
                                            <template
                                                v-for="(
                                                    link, key
                                                ) in books.links"
                                                :key="key"
                                            >
                                                <!-- Disabled/Ellipsis -->
                                                <div
                                                    v-if="link.url === null"
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-100 bg-white text-sm font-medium text-gray-300"
                                                    v-html="
                                                        link.label
                                                            .replace(
                                                                '&laquo; Previous',
                                                                '←',
                                                            )
                                                            .replace(
                                                                'Next &raquo;',
                                                                '→',
                                                            )
                                                    "
                                                />

                                                <!-- Active Link -->
                                                <button
                                                    v-else-if="link.active"
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue text-sm font-bold text-white shadow-lg shadow-blue/30"
                                                    v-html="link.label"
                                                />

                                                <!-- Inactive Link -->
                                                <button
                                                    v-else
                                                    @click="
                                                        handlePagination(
                                                            link.url,
                                                        )
                                                    "
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-sm font-bold text-slate-600 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:scale-105 hover:bg-blue hover:text-white"
                                                    v-html="
                                                        link.label
                                                            .replace(
                                                                '&laquo; Previous',
                                                                '←',
                                                            )
                                                            .replace(
                                                                'Next &raquo;',
                                                                '→',
                                                            )
                                                    "
                                                />
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    <div
                                        v-else
                                        class="animate-fade-in-up flex flex-col items-center justify-center py-20 text-center"
                                    >
                                        <div
                                            class="mb-6 rounded-full bg-blue/5 p-8 ring-8 ring-blue/5"
                                        >
                                            <BookOpen
                                                class="h-12 w-12 text-blue"
                                            />
                                        </div>
                                        <h3
                                            class="mb-2 text-xl font-bold text-slate-900"
                                        >
                                            কোনো বই পাওয়া যায়নি
                                        </h3>
                                        <p
                                            class="mx-auto mb-6 max-w-xs text-slate-500"
                                        >
                                            আপনার ফিল্টার পরিবর্তন করে আবার
                                            চেষ্টা করুন অথবা নতুন অনুসন্ধান করুন
                                        </p>
                                        <button
                                            @click="resetFilters"
                                            class="rounded-xl bg-blue px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue/20 transition-colors hover:bg-blue-hover"
                                        >
                                            ফিল্টার রিসেট করুন
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <Footer />
</template>

<style scoped>
/* --- Transitions --- */

/* Dropdown Animation */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}

/* List Item Reordering Animation */
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
.list-leave-active {
    position: absolute; /* Ensures smooth removal flow */
}

/* Fade Generic */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Bottom Sheet Easing */
.ease-out-expo {
    transition-timing-function: cubic-bezier(0.19, 1, 0.22, 1);
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #cbd5e1;
}
nav.font-bangla .shadow-md {
    box-shadow: none !important;
}
</style>
