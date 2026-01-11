<script setup lang="ts">
import CardSkeleton from '@/components/CardSkeleton.vue';
import { Skeleton } from '@/components/ui/skeleton';
</script>

<template>
    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
        <!-- Breadcrumbs Skeleton -->
        <div class="mb-6 hidden items-center gap-2 sm:flex">
            <Skeleton class="h-4 w-12 bg-gray-200" />
            <span class="text-gray-300">&rsaquo;</span>
            <Skeleton class="h-4 w-16 bg-gray-200" />
            <span class="text-gray-300">&rsaquo;</span>
            <Skeleton class="h-4 w-32 bg-gray-200" />
        </div>

        <div
            class="mb-8 flex flex-col items-start gap-6 lg:mb-12 lg:flex-row lg:gap-12"
        >
            <!-- Thumbnails Column Skeleton -->
            <div
                class="order-2 flex w-full shrink-0 items-center gap-3 lg:order-1 lg:w-20 lg:flex-col"
            >
                <div
                    v-for="i in 3"
                    :key="i"
                    class="aspect-3/4 w-16 shrink-0 rounded-lg border border-gray-100 bg-white lg:w-20"
                >
                    <Skeleton class="h-full w-full bg-gray-100" />
                </div>
            </div>

            <!-- Main Content Skeleton -->
            <div class="order-1 w-full flex-1 lg:order-2">
                <div
                    class="flex flex-col gap-6 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:flex-row md:gap-8 md:p-6 lg:gap-10 lg:p-10"
                >
                    <!-- Book Cover Skeleton -->
                    <div
                        class="mx-auto w-full max-w-[200px] shrink-0 sm:max-w-[280px] md:mx-0 lg:max-w-[320px]"
                    >
                        <Skeleton
                            class="aspect-2/3 w-full rounded-l-md rounded-r-2xl bg-gray-200"
                        />
                    </div>

                    <!-- Book Details Skeleton -->
                    <div class="flex flex-1 flex-col">
                        <div
                            class="mb-3 flex flex-wrap items-center justify-between gap-4"
                        >
                            <Skeleton class="h-6 w-32 bg-gray-200" />
                            <!-- Writer name -->
                            <div class="flex items-center gap-4">
                                <Skeleton class="h-5 w-20 bg-gray-100" />
                                <!-- Reviews -->
                                <Skeleton class="h-5 w-12 bg-gray-100" />
                                <!-- Rating -->
                            </div>
                        </div>

                        <Skeleton
                            class="mb-3 h-10 w-3/4 bg-gray-200 sm:mb-5 sm:h-12"
                        />
                        <!-- Title -->

                        <div class="mb-5 flex flex-wrap gap-2">
                            <Skeleton
                                v-for="i in 3"
                                :key="i"
                                class="h-6 w-16 rounded-full bg-gray-100"
                            />
                            <!-- Tags -->
                        </div>

                        <div class="mb-6 flex items-end gap-3">
                            <Skeleton class="h-10 w-24 bg-gray-200 sm:h-12" />
                            <!-- Price -->
                            <Skeleton class="mb-1 h-6 w-20 bg-gray-100" />
                            <!-- Original Price / Discount -->
                        </div>

                        <div class="mb-8 space-y-2">
                            <Skeleton class="h-4 w-full bg-gray-100" />
                            <Skeleton class="h-4 w-full bg-gray-100" />
                            <Skeleton class="h-4 w-2/3 bg-gray-100" />
                        </div>

                        <div
                            class="mt-auto mb-6 hidden grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 md:grid"
                        >
                            <Skeleton
                                class="h-14 w-full rounded-xl bg-gray-200"
                            />
                            <!-- Add to cart -->
                            <Skeleton
                                class="h-14 w-full rounded-xl bg-gray-100"
                            />
                            <!-- Read a bit -->
                        </div>

                        <div
                            class="flex flex-col items-center justify-center gap-4 border-t border-gray-100 pt-5 sm:flex-row sm:justify-between"
                        >
                            <Skeleton class="h-5 w-40 bg-gray-100" />
                            <!-- Wishlist -->
                            <Skeleton class="h-5 w-24 bg-gray-100" />
                            <!-- Share -->
                        </div>
                    </div>
                </div>

                <!-- Footer icons/info skeleton -->
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Skeleton
                        class="h-16 w-full rounded-lg bg-white shadow-sm"
                    />
                    <Skeleton
                        class="h-16 w-full rounded-lg bg-white shadow-sm"
                    />
                </div>
            </div>
        </div>

        <!-- Tabs Skeleton -->
        <div class="mb-12 flex flex-col gap-8 lg:mb-16 lg:flex-row lg:gap-12">
            <div class="hidden w-20 shrink-0 lg:block"></div>
            <div
                class="w-full flex-1 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:p-8"
            >
                <div
                    class="flex items-center gap-6 border-b border-gray-100 pb-4"
                >
                    <Skeleton class="h-6 w-20 bg-gray-100" />
                    <Skeleton class="h-6 w-24 bg-gray-100" />
                    <Skeleton class="h-6 w-16 bg-gray-100" />
                </div>
                <div class="mt-8 space-y-4">
                    <Skeleton class="h-6 w-48 bg-gray-200" />
                    <div class="space-y-2">
                        <Skeleton class="h-4 w-full bg-gray-100" />
                        <Skeleton class="h-4 w-full bg-gray-100" />
                        <Skeleton class="h-4 w-3/4 bg-gray-100" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Books Skeleton -->
        <div class="mt-8 border-t border-gray-200 pt-8 sm:mt-16 sm:pt-10">
            <div class="mb-6 flex items-end justify-between gap-3">
                <Skeleton class="h-8 w-48 bg-gray-200" />
                <Skeleton class="h-6 w-20 rounded-full bg-gray-100" />
            </div>
            <div
                class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6"
            >
                <div
                    v-for="i in 6"
                    :key="i"
                    class="mx-auto w-full max-w-[170px]"
                >
                    <CardSkeleton />
                </div>
            </div>
        </div>
    </div>
</template>
