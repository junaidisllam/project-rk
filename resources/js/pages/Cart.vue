<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import { useCartStore } from '@/Stores/cart';
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    Minus,
    Plus,
    ShoppingBag,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';

// --- Cart Store ---
const cartStore = useCartStore();

// --- Computeds ---
const selectedItems = computed(() => cartStore.selectedItems);
const subtotal = computed(() => cartStore.subtotal);
const totalSavings = computed(() => cartStore.totalSavings);

// Total is now just the subtotal since shipping/gift costs are removed
const totalAmount = computed(() => subtotal.value);

const allSelected = computed({
    get: () => cartStore.allSelected,
    set: (val: boolean) => cartStore.toggleAllSelected(val),
});

// --- Methods ---
const increment = (id: number) => {
    cartStore.incrementQuantity(id);
};
const decrement = (id: number) => {
    cartStore.decrementQuantity(id);
};
const removeItem = (id: number) => {
    cartStore.remove(id);
};
const toggleSelectedItem = (id: number) => {
    cartStore.toggleSelection(id);
};

const formatPrice = (value: number) => '৳' + value.toLocaleString('en-BD');
</script>

<template>
    <!-- Desktop Header (Hidden on Mobile) -->
    <div class="hidden lg:block">
        <Heading />
    </div>

    <!-- MOBILE APP HEADER (Sticky Top) -->
    <div
        class="fixed top-0 right-0 left-0 z-40 flex h-16 items-center border-b border-slate-100 bg-white/90 px-4 shadow-sm backdrop-blur-md lg:hidden"
    >
        <Link
            href="/"
            class="mr-4 rounded-full p-2 text-slate-700 active:bg-slate-100"
        >
            <ArrowLeft class="size-6" />
        </Link>
        <h1 class="text-lg font-bold text-slate-900">
            শপিং ব্যাগ ({{ cartStore.items.length }})
        </h1>
    </div>

    <!-- MAIN CONTENT -->
    <div
        class="min-h-screen bg-[#F5F7FA] pt-20 pb-48 font-sans text-slate-900 lg:pt-0 lg:pb-20"
    >
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Desktop Title -->
            <div class="mb-8 hidden items-center justify-between lg:flex">
                <div>
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-slate-900"
                    >
                        আপনার শপিং ব্যাগ
                    </h1>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        আপনার ব্যাগে
                        <span class="text-blue"
                            >{{ cartStore.items?.length ?? 0 }} টি</span
                        >
                        আইটেম রয়েছে
                    </p>
                </div>
                <Link
                    href="/"
                    class="flex items-center gap-2 text-sm font-bold text-blue hover:underline"
                >
                    <ArrowLeft class="size-4" />
                    কেনাকাটা চালিয়ে যান
                </Link>
            </div>

            <Transition name="fade-slide" mode="out-in">
                <!-- EMPTY STATE -->
                <div
                    v-if="cartStore.items.length === 0"
                    class="flex min-h-[60vh] flex-col items-center justify-center text-center"
                >
                    <div class="relative mb-6">
                        <div
                            class="absolute inset-0 animate-ping rounded-full bg-blue-100 opacity-20"
                        ></div>
                        <div
                            class="relative flex size-24 items-center justify-center rounded-full bg-white shadow-xl shadow-blue-100"
                        >
                            <ShoppingBag class="size-10 text-blue" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        ব্যাগটি খালি!
                    </h2>
                    <p class="mt-2 max-w-xs text-sm text-slate-500">
                        মনে হচ্ছে আপনি এখনও কোনো বই পছন্দ করেননি।
                    </p>
                    <Link
                        href="/"
                        class="mt-8 flex items-center gap-2 rounded-full bg-blue px-8 py-3.5 font-bold text-white shadow-lg shadow-blue/30 transition hover:scale-105 active:scale-95"
                    >
                        বই খুঁজতে যান <ArrowRight class="size-4" />
                    </Link>
                </div>

                <!-- FILLED STATE -->
                <div
                    v-else
                    class="flex flex-col gap-8 lg:flex-row lg:items-start"
                >
                    <!-- Left Column: Items List -->
                    <div class="flex-1 space-y-4">
                        <!-- Select All Header (App Style) -->
                        <div
                            class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm"
                        >
                            <label
                                class="flex cursor-pointer items-center gap-3"
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
                                <span class="text-sm font-bold text-slate-700"
                                    >সবগুলো সিলেক্ট করুন</span
                                >
                            </label>
                            <button
                                @click="cartStore.clearCart"
                                class="text-xs font-medium text-red-500 hover:text-red-700"
                            >
                                সব মুছুন
                            </button>
                        </div>

                        <!-- Items Animation Group -->
                        <TransitionGroup
                            name="list"
                            tag="div"
                            class="space-y-3"
                        >
                            <div
                                v-for="item in cartStore.items"
                                :key="item.id"
                                class="group relative flex gap-3 overflow-hidden rounded-2xl bg-white p-3 shadow-sm ring-1 transition-all duration-300 hover:shadow-md lg:p-4"
                                :class="
                                    item.selected
                                        ? 'bg-blue-50/10 ring-blue-200'
                                        : 'ring-transparent'
                                "
                            >
                                <!-- Checkbox -->
                                <div class="flex items-center">
                                    <label
                                        class="relative flex cursor-pointer items-center p-1"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="item.selected"
                                            @change="
                                                toggleSelectedItem(item.id)
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
                                    class="relative h-24 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100 shadow-sm sm:h-28 sm:w-20"
                                >
                                    <img
                                        :src="
                                            item.cover_photo
                                                ? `/storage/${item.cover_photo}`
                                                : undefined
                                        "
                                        :alt="item.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>

                                <!-- Content -->
                                <div
                                    class="flex flex-1 flex-col justify-between py-0.5"
                                >
                                    <div class="flex justify-between gap-2">
                                        <div>
                                            <h3
                                                class="line-clamp-2 text-sm font-bold text-slate-800 lg:text-base"
                                            >
                                                {{ item.name }}
                                            </h3>
                                            <p
                                                class="line-clamp-1 text-xs text-slate-500"
                                            >
                                                {{ item.writer.name }}
                                            </p>
                                        </div>
                                        <button
                                            @click="removeItem(item.id)"
                                            class="h-8 w-8 shrink-0 rounded-full p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                        >
                                            <Trash2 class="h-full w-full" />
                                        </button>
                                    </div>

                                    <!-- Price & Controls -->
                                    <div
                                        class="mt-2 flex items-end justify-between"
                                    >
                                        <div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="text-base font-extrabold text-blue"
                                                    >{{
                                                        formatPrice(item.price)
                                                    }}</span
                                                >
                                                <span
                                                    v-if="item.original_price"
                                                    class="text-xs text-slate-400 line-through"
                                                    >{{
                                                        formatPrice(
                                                            item.original_price,
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                            <p
                                                v-if="item.original_price"
                                                class="mt-0.5 text-[10px] font-bold text-green-600"
                                            >
                                                {{
                                                    Math.round(
                                                        ((item.original_price -
                                                            item.price) /
                                                            item.original_price) *
                                                            100,
                                                    )
                                                }}% ছাড়
                                            </p>
                                        </div>

                                        <!-- Quantity Control -->
                                        <div
                                            class="flex items-center rounded-full border border-slate-200 bg-white shadow-sm"
                                        >
                                            <button
                                                @click="decrement(item.id)"
                                                :disabled="item.quantity <= 1"
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-slate-600 active:bg-slate-100 disabled:opacity-30"
                                            >
                                                <Minus class="size-3.5" />
                                            </button>
                                            <span
                                                class="w-6 text-center text-sm font-bold text-slate-900"
                                                >{{ item.quantity }}</span
                                            >
                                            <button
                                                @click="increment(item.id)"
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-slate-600 active:bg-slate-100"
                                            >
                                                <Plus class="size-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TransitionGroup>
                    </div>

                    <!-- Right Column: Sidebar (Desktop Only) -->
                    <aside class="sticky top-4 hidden w-96 lg:block">
                        <div
                            class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100"
                        >
                            <h2
                                class="mb-6 text-xl font-extrabold text-slate-900"
                            >
                                অর্ডার সামারি
                            </h2>

                            <div
                                class="space-y-4 border-b border-slate-50 pb-6 text-sm"
                            >
                                <div
                                    class="flex justify-between text-slate-600"
                                >
                                    <span
                                        >সাবটোটাল ({{
                                            selectedItems?.length ?? 0
                                        }})</span
                                    >
                                    <span class="font-bold text-slate-900">{{
                                        formatPrice(subtotal)
                                    }}</span>
                                </div>
                                <div
                                    v-if="totalSavings > 0"
                                    class="flex justify-between font-bold text-green-600"
                                >
                                    <span>মোট সাশ্রয়</span>
                                    <span
                                        >- {{ formatPrice(totalSavings) }}</span
                                    >
                                </div>
                            </div>

                            <div
                                class="mt-2 mb-6 flex items-end justify-between pt-4"
                            >
                                <span class="text-base font-bold text-slate-700"
                                    >সর্বমোট</span
                                >
                                <span class="text-3xl font-black text-blue">{{
                                    formatPrice(totalAmount)
                                }}</span>
                            </div>

                            <Link
                                href="/checkout"
                                class="flex w-full items-center justify-center gap-3 rounded-xl bg-blue py-4 font-bold text-white shadow-xl shadow-blue-200 transition hover:-translate-y-1 hover:bg-blue-700 active:scale-95"
                            >
                                চেকআউট করুন <ArrowRight class="size-5" />
                            </Link>
                        </div>
                    </aside>
                </div>
            </Transition>
        </div>
    </div>

    <!-- MOBILE STICKY BOTTOM ACTION BAR (App Style) -->
    <div
        class="fixed right-0 bottom-0 left-0 z-50 rounded-t-2xl border-t border-slate-100 bg-white px-5 pt-4 pb-6 shadow-[0_-5px_20px_rgba(0,0,0,0.08)] lg:hidden"
    >
        <div class="mx-auto max-w-md">
            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500"
                        >সর্বমোট ({{ selectedItems.length }} আইটেম)</span
                    >
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-blue">{{
                            formatPrice(totalAmount)
                        }}</span>
                        <span
                            v-if="totalSavings > 0"
                            class="text-xs font-bold text-green-600 line-through decoration-slate-300"
                            >{{ formatPrice(totalAmount + totalSavings) }}</span
                        >
                    </div>
                </div>

                <Link
                    href="/checkout"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-blue py-3.5 text-base font-bold text-white shadow-lg shadow-blue/30 transition active:scale-95"
                >
                    চেকআউট <ArrowRight class="size-5" />
                </Link>
            </div>
        </div>
    </div>

    <div class="hidden lg:block">
        <Footer />
    </div>
</template>

<style scoped>
/* List Transitions */
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
    position: absolute;
    width: 100%;
}

/* Page Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
