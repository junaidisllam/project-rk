<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/components/Card.vue';
import { Heart } from 'lucide-vue-next';

defineProps<{
    books: any[];
}>();
</script>

<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <h1
                class="mb-6 flex items-center gap-2 text-2xl font-bold text-slate-800"
            >
                <Heart class="fill-red-500 text-red-500" />
                My Wishlist
            </h1>

            <div
                v-if="books.length > 0"
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6"
            >
                <Card v-for="book in books" :key="book.id" v-bind="book" />
            </div>

            <div
                v-else
                class="flex flex-col items-center justify-center rounded-2xl bg-slate-50 py-20 text-center"
            >
                <div class="mb-4 rounded-full bg-white p-4 shadow-sm">
                    <Heart class="h-10 w-10 text-slate-300" />
                </div>
                <h3 class="text-lg font-bold text-slate-700">
                    Your wishlist is empty
                </h3>
                <p class="mt-2 text-slate-500">
                    Save items you want to buy later.
                </p>
                <a
                    href="/allbooks"
                    class="mt-6 rounded-lg bg-blue px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-600"
                >
                    Browse Books
                </a>
            </div>
        </div>
    </AppLayout>
</template>
