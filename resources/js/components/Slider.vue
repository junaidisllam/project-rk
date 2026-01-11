<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
// Import Splide for Desktop only
import '@splidejs/splide/dist/css/splide.min.css';
import { Splide, SplideSlide } from '@splidejs/vue-splide';
import { onMounted, onUnmounted, ref } from 'vue';

interface SliderData {
    id: number;
    image_path: string;
    mobile_image_path: string;
    title: string | null;
    link: string | null;
}

const props = defineProps<{
    sliders: SliderData[];
}>();

// ==========================================
// DESKTOP CONFIGURATION (Splide)
// ==========================================
const desktopOptions = {
    type: 'loop',
    perPage: 1,
    autoplay: true,
    interval: 5000,
    arrows: true,
    pagination: true,
    gap: '1rem',
};

// ==========================================
// MOBILE CUSTOM SLIDER LOGIC
// ==========================================
const mobileIndex = ref(0);
let mobileInterval: any = null;
const touchStartX = ref(0);
const touchEndX = ref(0);

const nextMobileSlide = () => {
    mobileIndex.value = (mobileIndex.value + 1) % props.sliders.length;
};

const startMobileAutoplay = () => {
    stopMobileAutoplay(); // Clear existing
    if (props.sliders.length > 1) {
        mobileInterval = setInterval(nextMobileSlide, 5000);
    }
};

const stopMobileAutoplay = () => {
    if (mobileInterval) clearInterval(mobileInterval);
};

// Swipe Logic for Mobile
const handleTouchStart = (e: TouchEvent) => {
    touchStartX.value = e.changedTouches[0].screenX;
    stopMobileAutoplay(); // Pause on touch
};

const handleTouchEnd = (e: TouchEvent) => {
    touchEndX.value = e.changedTouches[0].screenX;
    handleSwipe();
    startMobileAutoplay(); // Resume
};

const handleSwipe = () => {
    if (touchEndX.value < touchStartX.value - 50) {
        // Swipe Left (Next)
        nextMobileSlide();
    }
    if (touchEndX.value > touchStartX.value + 50) {
        // Swipe Right (Prev)
        mobileIndex.value =
            mobileIndex.value === 0
                ? props.sliders.length - 1
                : mobileIndex.value - 1;
    }
};

const goToSlide = (index: number) => {
    mobileIndex.value = index;
    stopMobileAutoplay();
    startMobileAutoplay();
};

onMounted(() => {
    startMobileAutoplay();
});

onUnmounted(() => {
    stopMobileAutoplay();
});
</script>

<template>
    <div v-if="sliders.length > 0" class="mt-4 w-full max-sm:mt-0">
        <!-- ============================================= -->
        <!-- 1. DESKTOP SLIDER (Hidden on Mobile)          -->
        <!-- ============================================= -->
        <div class="container mx-auto hidden px-4 sm:block">
            <Splide :options="desktopOptions" class="custom-slider">
                <SplideSlide v-for="slider in sliders" :key="slider.id">
                    <Link
                        v-if="slider.link"
                        :href="slider.link"
                        class="block h-full w-full"
                        target="_blank"
                    >
                        <img
                            :src="
                                slider.image_path
                                    ? '/storage/' + slider.image_path
                                    : '/placeholder.png'
                            "
                            :alt="slider.title ?? 'Slider Image'"
                            class="block aspect-[16/5] w-full rounded-xl object-cover"
                        />
                    </Link>
                    <div v-else class="block h-full w-full">
                        <img
                            :src="
                                slider.image_path
                                    ? '/storage/' + slider.image_path
                                    : '/placeholder.png'
                            "
                            class="block aspect-[16/5] w-full rounded-xl object-cover"
                        />
                    </div>
                </SplideSlide>
            </Splide>
        </div>

        <!-- ============================================= -->
        <!-- 2. CUSTOM MOBILE SLIDER (Visible on Mobile)   -->
        <!-- ============================================= -->
        <div
            class="group relative block w-full overflow-hidden sm:hidden"
            @touchstart="handleTouchStart"
            @touchend="handleTouchEnd"
        >
            <!-- Track: Flexbox that moves left/right -->
            <div
                class="flex w-full transition-transform duration-500 ease-out"
                :style="{ transform: `translateX(-${mobileIndex * 100}%)` }"
            >
                <div
                    v-for="slider in sliders"
                    :key="slider.id"
                    class="w-full flex-shrink-0"
                >
                    <Link :href="slider.link ?? '#'" class="block w-full">
                        <!-- Mobile Image Logic -->
                        <img
                            :src="
                                slider.mobile_image_path
                                    ? '/storage/' + slider.mobile_image_path
                                    : '/storage/' + slider.image_path
                            "
                            :alt="slider.title ?? 'Slider Image'"
                            class="block h-auto w-full object-cover"
                            style="width: 100%; height: auto"
                        />
                    </Link>
                </div>
            </div>

            <!-- Custom Dots for Mobile -->
            <div
                class="absolute right-0 bottom-2 left-0 z-10 flex justify-center gap-2"
            >
                <button
                    v-for="(slider, index) in sliders"
                    :key="index"
                    @click="goToSlide(index)"
                    class="h-2.5 w-2.5 rounded-full transition-all duration-300"
                    :class="
                        mobileIndex === index
                            ? 'w-6 bg-blue-600'
                            : 'bg-gray-300/80'
                    "
                ></button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Desktop specific styles */
:deep(.splide__track) {
    border-radius: 0.75rem;
}
:deep(.splide__pagination) {
    bottom: -1.5rem;
}
</style>
