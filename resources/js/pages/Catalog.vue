<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    ChevronRight,
    Filter,
    Layers,
    Search,
    Users,
} from 'lucide-vue-next';

// --- Helper: Expose Ziggy ---
const route = (window as any).route;

import { computed, ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    image?: string;
    books_count: number;
}

interface Author {
    id: number;
    name: string;
    photo?: string;
    books_count: number;
}

const props = defineProps<{
    categories: Category[];
    authors: Author[];
    title?: string;
    description?: string;
}>();

// --- State ---
const activeTab = ref<'categories' | 'authors'>('categories');
const searchQuery = ref('');

// --- Computed ---
const filteredCategories = computed(() => {
    if (!searchQuery.value) return props.categories;
    const query = searchQuery.value.toLowerCase();
    return props.categories.filter((c) => c.name.toLowerCase().includes(query));
});

const filteredAuthors = computed(() => {
    if (!searchQuery.value) return props.authors;
    const query = searchQuery.value.toLowerCase();
    return props.authors.filter((a) => a.name.toLowerCase().includes(query));
});

const getAuthorInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <Head :title="props.title">
        <meta
            v-if="props.description"
            name="description"
            :content="props.description"
        />
    </Head>
    <Heading />

    <main class="font-bangla min-h-screen bg-slate-50 pb-20">
        <!-- ======================= -->
        <!-- MOBILE APP HEADER       -->
        <!-- ======================= -->
        <div
            class="relative z-10 overflow-hidden rounded-b-[2.5rem] bg-blue pt-8 pb-16 shadow-xl lg:hidden"
        >
            <!-- Decorative Elements -->
            <div
                class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"
            ></div>
            <div
                class="absolute bottom-0 left-0 -mb-10 -ml-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"
            ></div>

            <div class="relative px-6">
                <h1 class="text-2xl font-black text-white">ক্যাটালগ</h1>
                <p class="mt-1 text-sm text-blue-100 opacity-90">
                    আপনার পছন্দের বই বা লেখক খুঁজুন
                </p>

                <!-- Mobile Search -->
                <div class="relative mt-6">
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="
                            activeTab === 'categories'
                                ? 'বিভাগ খুঁজুন...'
                                : 'লেখক খুঁজুন...'
                        "
                        class="w-full rounded-2xl border-none bg-white py-3.5 pr-4 pl-12 text-sm font-semibold text-slate-800 shadow-lg placeholder:text-slate-400 focus:ring-0"
                    />
                    <Search
                        class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400"
                    />
                </div>
            </div>
        </div>

        <!-- ======================= -->
        <!-- DESKTOP HERO HEADER     -->
        <!-- ======================= -->
        <div
            class="relative hidden overflow-hidden bg-blue pt-20 pb-32 text-white lg:block"
        >
            <div class="absolute inset-0 opacity-10">
                <div
                    class="absolute -top-20 -right-20 size-96 rounded-full bg-white blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-20 -left-20 size-96 rounded-full bg-white blur-3xl"
                ></div>
            </div>

            <div class="relative container mx-auto px-4 text-center">
                <h1 class="mb-4 text-5xl font-black">
                    পছন্দের বিষয় বা লেখক খুঁজুন
                </h1>
                <p class="mx-auto max-w-2xl text-lg font-medium text-blue-100">
                    হাজারো বইয়ের সংগ্রহ থেকে আপনার প্রিয় বিভাগ বা প্রিয় লেখকের
                    বই খুঁজে বের করুন।
                </p>

                <!-- Desktop Search -->
                <div class="mx-auto mt-10 max-w-xl">
                    <div class="group relative">
                        <Search
                            class="absolute top-1/2 left-5 size-5 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="
                                activeTab === 'categories'
                                    ? 'বিভাগ খুঁজুন...'
                                    : 'লেখক খুঁজুন...'
                            "
                            class="w-full rounded-2xl border-none bg-white py-4 pr-6 pl-14 text-slate-900 shadow-2xl transition-all focus:ring-4 focus:ring-blue/20"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="relative z-20 container mx-auto -mt-8 px-4 lg:-mt-16">
            <!-- Tabs Navigation -->
            <div
                class="mx-auto mb-8 max-w-fit rounded-full bg-white p-1.5 shadow-lg ring-1 ring-slate-100 lg:mb-12"
            >
                <div class="flex items-center">
                    <button
                        @click="activeTab = 'categories'"
                        class="flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold transition-all duration-300"
                        :class="
                            activeTab === 'categories'
                                ? 'bg-blue text-white shadow-md'
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                        "
                    >
                        <Layers class="size-4" />
                        বিভাগ ({{ categories.length }})
                    </button>
                    <button
                        @click="activeTab = 'authors'"
                        class="flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold transition-all duration-300"
                        :class="
                            activeTab === 'authors'
                                ? 'bg-blue text-white shadow-md'
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                        "
                    >
                        <Users class="size-4" />
                        লেখক ({{ authors.length }})
                    </button>
                </div>
            </div>

            <TransitionGroup
                name="fade"
                tag="div"
                class="grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4"
            >
                <!-- Category Grid -->
                <template v-if="activeTab === 'categories'">
                    <Link
                        v-for="cat in filteredCategories"
                        :key="cat.id"
                        :href="route('allbooks', { categories: [cat.slug] })"
                        class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 transition-all hover:-translate-y-1 hover:shadow-xl hover:ring-blue/30 active:scale-[0.98]"
                    >
                        <div class="flex items-start justify-between">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue/5 text-blue transition-colors group-hover:bg-blue group-hover:text-white"
                            >
                                <BookOpen class="h-7 w-7" v-if="!cat.image" />
                                <img
                                    v-else
                                    :src="'/storage/' + cat.image"
                                    class="h-full w-full rounded-2xl object-cover"
                                />
                            </div>
                            <span
                                class="rounded-full bg-slate-50 px-2.5 py-1 text-[10px] font-bold text-slate-500 group-hover:bg-blue/10 group-hover:text-blue"
                            >
                                {{ cat.books_count }} বই
                            </span>
                        </div>
                        <div class="mt-4">
                            <h3
                                class="text-base font-bold text-slate-800 transition-colors group-hover:text-blue"
                            >
                                {{ cat.name }}
                            </h3>
                            <div
                                class="mt-1 flex items-center gap-1 text-xs font-semibold text-slate-400 group-hover:text-blue/70"
                            >
                                <span>ব্রাউজ করুন</span>
                                <ArrowRight
                                    class="h-3 w-3 transition-transform group-hover:translate-x-1"
                                />
                            </div>
                        </div>
                    </Link>
                </template>

                <!-- Author Grid -->
                <template v-else>
                    <Link
                        v-for="author in filteredAuthors"
                        :key="author.id"
                        :href="route('allbooks', { authors: [author.id] })"
                        class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 transition-all hover:bg-slate-50 hover:shadow-lg hover:ring-blue/30 active:scale-[0.98]"
                    >
                        <div class="relative shrink-0">
                            <div
                                class="h-14 w-14 overflow-hidden rounded-full border-2 border-white shadow-sm ring-1 ring-slate-100 group-hover:ring-blue/30"
                            >
                                <img
                                    v-if="author.photo"
                                    :src="'/storage/' + author.photo"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-slate-50 text-sm font-bold text-slate-400 group-hover:bg-blue/5 group-hover:text-blue"
                                >
                                    {{ getAuthorInitials(author.name) }}
                                </div>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3
                                class="truncate text-sm font-bold text-slate-800 group-hover:text-blue"
                            >
                                {{ author.name }}
                            </h3>
                            <p class="text-[10px] font-medium text-slate-500">
                                {{ author.books_count }} টি বই প্রকাশিত
                            </p>
                        </div>
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-colors group-hover:bg-blue group-hover:text-white"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </Link>
                </template>
            </TransitionGroup>

            <!-- Empty State -->
            <div
                v-if="
                    (activeTab === 'categories' &&
                        filteredCategories.length === 0) ||
                    (activeTab === 'authors' && filteredAuthors.length === 0)
                "
                class="flex flex-col items-center justify-center py-20 text-center"
            >
                <div class="mb-4 rounded-full bg-slate-100 p-6 text-slate-400">
                    <Filter class="size-10" />
                </div>
                <h3 class="text-lg font-bold text-slate-800">
                    কিছু পাওয়া যায়নি
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    অন্য কোনো নাম দিয়ে সার্চ করে দেখুন।
                </p>
                <button
                    @click="searchQuery = ''"
                    class="mt-4 rounded-full bg-blue/10 px-5 py-2 text-xs font-bold text-blue hover:bg-blue/20"
                >
                    রিসেট করুন
                </button>
            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
