<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import Slider from '@/components/Slider.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, MoveLeft, MoveRight } from 'lucide-vue-next';
import { ref } from 'vue';

// Splide Imports
import Card from '@/components/Card.vue';
import Footer from '@/components/Footer.vue';
import { Splide, SplideSlide } from '@splidejs/vue-splide';
import '@splidejs/vue-splide/css';

// --- Interfaces ---
interface Book {
    id: number;
    name: string;
    writer: { name: string };
    cover_photo: string | null;
    price: number;
    originalPrice: number | null;
    slug: string;
}

interface SliderData {
    id: number;
    image_path: string;
    title: string | null;
    link: string | null;
    is_active: boolean;
    order_column: number;
}

interface Section {
    id: number;
    title: string;
    subtitle: string;
    slug: string;
    order_column: number;
    books: Book[];
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Author {
    id: number;
    name: string;
    slug: string;
}

const props = withDefaults(
    defineProps<{
        canRegister?: boolean;
        sections?: Section[];
        sliders?: SliderData[];
        title?: string;
        description?: string;
    }>(),
    {
        canRegister: true,
        sections: () => [],
        sliders: () => [],
        title: 'Home',
    },
);

// --- Splide Configuration ---
const splideOptions = {
    type: 'slide',
    perPage: 6,
    perMove: 1,
    gap: '1.5rem',
    pagination: false,
    arrows: false,
    padding: { left: 0, right: 0 },
    breakpoints: {
        1536: { perPage: 5, gap: '1.5rem' },
        1280: { perPage: 4, gap: '1.5rem' },
        1024: { perPage: 3, gap: '1.25rem' },
        768: { perPage: 2.5, gap: '1rem', focus: 'center' },
        // --- MOBILE: DESTROY SPLIDE JS ---
        640: {
            destroy: true, // Native Scroll via CSS
        },
    },
};

const sliderRefs = ref<InstanceType<typeof Splide>[]>([]);

const moveSlider = (index: number, direction: '>' | '<') => {
    const slider = sliderRefs.value[index];
    if (slider?.splide && !slider.splide.state.is(1)) {
        slider.go(direction);
    }
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
    <Slider :sliders="sliders" />

    <div class="container mx-auto space-y-2 px-4 py-12">
        <div
            v-for="(section, index) in sections"
            :key="section.id"
            class="slider-section"
        >
            <!--
               SECTION HEADER (UPDATED)
               - Added 'gap-3' for spacing between text and button
               - Made 'View All' visible on mobile with improved styling
            -->
            <div class="mb-5 flex items-end justify-between gap-3">
                <!-- Left: Text -->
                <div class="min-w-0 flex-1">
                    <h2
                        class="truncate text-xl font-bold tracking-tight text-gray-900 sm:text-2xl"
                    >
                        {{ section.title }}
                    </h2>
                    <p
                        class="mt-1 line-clamp-1 text-xs text-gray-500 sm:text-sm"
                    >
                        {{ section.subtitle }}
                    </p>
                </div>

                <!-- Right: Controls & Link -->
                <div class="flex shrink-0 items-center gap-3">
                    <!-- View All Button (Mobile & Desktop) -->
                    <Link
                        href="/allbooks"
                        class="group flex items-center gap-1 rounded-full bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-blue transition-all hover:text-blue-600 sm:bg-transparent sm:px-0 sm:py-0 sm:text-sm"
                    >
                        <span class="whitespace-nowrap">সব দেখুন</span>
                        <ChevronRight
                            class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1"
                        />
                    </Link>

                    <!-- Arrows (Hidden on Mobile) -->
                    <div
                        class="hidden items-center gap-2.5 border-l border-gray-200 pl-4 sm:flex lg:pl-5"
                    >
                        <button
                            @click="moveSlider(index, '<')"
                            class="group flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition-all hover:border-gray-900 hover:bg-gray-900 hover:text-white focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 focus:outline-none active:scale-95"
                        >
                            <MoveLeft class="h-4 w-4" />
                        </button>
                        <button
                            @click="moveSlider(index, '>')"
                            class="group flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition-all hover:border-gray-900 hover:bg-gray-900 hover:text-white focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 focus:outline-none active:scale-95"
                        >
                            <MoveRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Splide / Native Scroll Container -->
            <Splide
                :options="splideOptions"
                :ref="(el) => (sliderRefs[index] = el as any)"
                :aria-label="section.title"
                class="mobile-native-scroll pb-6"
            >
                <SplideSlide
                    v-for="book in section.books"
                    :key="book.id"
                    class="mobile-slide-item"
                >
                    <Card
                        :id="book.id"
                        :name="book.name"
                        :writer="book.writer"
                        :category="{ name: '' }"
                        :cover_photo="book.cover_photo"
                        :price="book.price"
                        :original_price="book.originalPrice"
                        :slug="book.slug"
                    />
                </SplideSlide>
            </Splide>
        </div>
    </div>
    <Footer />
</template>

<style scoped>
/*
   --------------------------------------------------
   MOBILE NATIVE SCROLL FIX
   --------------------------------------------------
*/
@media (max-width: 640px) {
    /* Force List to Flex Row */
    :deep(.splide__list) {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 0.75rem !important; /* 12px gap */
        width: auto !important;
    }

    /* Track properties */
    :deep(.splide__track) {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        display: block !important;
        width: 100% !important;
        padding-left: 0.5rem !important;
        padding-right: 1rem !important;
        scrollbar-width: none;
    }

    :deep(.splide__track::-webkit-scrollbar) {
        display: none;
    }

    /*
       Card Size: (100% - 12px gap) / 2
    */
    :deep(.splide__slide) {
        width: calc(50% - 0.5rem) !important;
        flex: 0 0 auto !important;
        margin: 0 !important;
    }
}
</style>
