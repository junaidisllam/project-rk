<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Heading from '@/components/Heading.vue';
import { useCartStore } from '@/Stores/cart';
import { Link } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import {
    Check,
    PackageCheck,
    Printer,
    ShoppingBag,
    Truck,
} from 'lucide-vue-next';
import { onMounted } from 'vue';
import { route } from 'ziggy-js';

defineProps<{
    orderId: number;
    invoiceNo: string;
    totalPrice: number;
}>();

const useRoute = route;
const formatPrice = (value: number = 0) =>
    '৳ ' + (value ?? 0).toLocaleString('en-BD');

// --- Professional Fireworks ---
const triggerFireworks = () => {
    const end = Date.now() + 3000;
    const colors = ['#10B981', '#3B82F6', '#F59E0B']; // Green, Blue, Gold (Professional colors)

    (function frame() {
        confetti({
            particleCount: 3,
            angle: 60,
            spread: 55,
            origin: { x: 0 },
            colors: colors,
        });
        confetti({
            particleCount: 3,
            angle: 120,
            spread: 55,
            origin: { x: 1 },
            colors: colors,
        });

        if (Date.now() < end) {
            requestAnimationFrame(frame);
        }
    })();
};

// --- Print Functionality ---
const printReceipt = () => {
    window.print();
};

onMounted(() => {
    triggerFireworks();
    useCartStore().clearCart();
});
</script>

<template>
    <Heading class="print:hidden" />

    <div
        class="relative flex min-h-screen flex-col items-center justify-center bg-slate-50 p-4 md:p-8"
    >
        <!-- Subtle Background Pattern -->
        <div
            class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-size-[24px_24px]"
        ></div>

        <div
            class="animate-fade-in-up relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl print:w-full print:border-none print:shadow-none"
        >
            <!-- Status Bar (Top) -->
            <div
                class="bg-emerald-500 p-6 text-center text-white print:border-b print:bg-white print:text-black"
            >
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm print:border print:border-black print:bg-transparent"
                >
                    <Check
                        class="size-10 text-white print:text-black"
                        stroke-width="3"
                    />
                </div>
                <h1 class="mb-2 text-2xl font-bold md:text-3xl">
                    অর্ডার সফল হয়েছে!
                </h1>
                <p class="text-emerald-50 opacity-90 print:text-slate-600">
                    আপনার অর্ডারের জন্য ধন্যবাদ। একটি কনফার্মেশন ইমেইল পাঠানো
                    হয়েছে।
                </p>
            </div>

            <!-- Visual Timeline (UX Improvement) -->
            <div
                class="border-b border-slate-100 bg-slate-50/50 px-8 py-8 print:hidden"
            >
                <div
                    class="relative mx-auto flex max-w-md items-center justify-between"
                >
                    <!-- Line -->
                    <div
                        class="absolute top-1/2 left-0 z-0 h-1 w-full -translate-y-1/2 bg-slate-200"
                    ></div>
                    <div
                        class="absolute top-1/2 left-0 z-0 h-1 w-1/3 -translate-y-1/2 bg-emerald-500"
                    ></div>

                    <!-- Step 1 -->
                    <div class="relative z-10 flex flex-col items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 ring-4 ring-white"
                        >
                            <Check class="size-4 text-white" />
                        </div>
                        <span class="text-xs font-bold text-emerald-700"
                            >অর্ডার প্লেসড</span
                        >
                    </div>
                    <!-- Step 2 -->
                    <div class="relative z-10 flex flex-col items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-300 bg-white ring-4 ring-white"
                        >
                            <PackageCheck class="size-4 text-slate-400" />
                        </div>
                        <span class="text-xs font-medium text-slate-500"
                            >প্রসেসিং</span
                        >
                    </div>
                    <!-- Step 3 -->
                    <div class="relative z-10 flex flex-col items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-300 bg-white ring-4 ring-white"
                        >
                            <Truck class="size-4 text-slate-400" />
                        </div>
                        <span class="text-xs font-medium text-slate-500"
                            >ডেলিভারি</span
                        >
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="p-6 md:p-8">
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <h3
                            class="mb-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                        >
                            অর্ডার নং
                        </h3>
                        <p class="font-mono text-lg font-bold text-slate-800">
                            #{{ orderId }}
                        </p>
                    </div>
                    <div>
                        <h3
                            class="mb-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                        >
                            ইনভয়েস
                        </h3>
                        <p class="font-mono text-lg font-medium text-slate-800">
                            {{ invoiceNo }}
                        </p>
                    </div>
                    <div>
                        <h3
                            class="mb-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                        >
                            তারিখ
                        </h3>
                        <p class="font-medium text-slate-800">
                            {{ new Date().toLocaleDateString('bn-BD') }}
                        </p>
                    </div>
                    <div>
                        <h3
                            class="mb-1 text-xs font-bold tracking-wider text-slate-400 uppercase"
                        >
                            মোট বিল
                        </h3>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ formatPrice(totalPrice) }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-4 sm:flex-row print:hidden">
                    <Link
                        :href="useRoute('home')"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3.5 font-bold text-white shadow-lg transition-all hover:bg-slate-800 hover:shadow-xl"
                    >
                        <ShoppingBag class="size-5" />
                        কেনাকাটা করুন
                    </Link>

                    <button
                        @click="printReceipt"
                        class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 font-bold text-slate-700 transition-colors hover:bg-slate-50"
                    >
                        <Printer class="size-5" />
                        রসিদ ডাউনলোড
                    </button>

                    <!-- Optional: Link to Order History -->
                    <!-- <Link :href="useRoute('orders.show', orderId)" class="flex items-center justify-center gap-2 bg-blue-50 text-blue-700 py-3.5 px-6 rounded-xl font-bold hover:bg-blue-100 transition-colors">
                        <ArrowRight class="size-5" />
                    </Link> -->
                </div>
            </div>
        </div>

        <!-- Support Text -->
        <p class="mt-8 text-sm text-slate-500 print:hidden">
            কোন সমস্যা?
            <span
                class="cursor-pointer font-bold text-emerald-600 hover:underline"
                >আমাদের সাথে যোগাযোগ করুন</span
            >
        </p>
    </div>
    <Footer class="print:hidden" />
</template>

<style scoped>
/* Smooth entry animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}

/* Print Styles specific for Invoice look */
@media print {
    body {
        background: white;
    }
    .print\:hidden {
        display: none !important;
    }
    .print\:shadow-none {
        box-shadow: none !important;
    }
    .print\:border-none {
        border: none !important;
    }
}
</style>
