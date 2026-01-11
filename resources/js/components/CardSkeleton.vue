<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
</script>

<template>
    <div class="mx-auto flex h-full w-full max-w-[170px] flex-col">
        <!-- Book Cover Skeleton -->
        <Skeleton class="aspect-2/3 w-full rounded-r-sm bg-gray-200" />

        <!-- Book Info Skeleton -->
        <div class="mt-2 flex flex-1 flex-col justify-between px-0.5">
            <div>
                <div class="mb-1.5 h-10">
                    <Skeleton class="mb-1 h-5 w-3/4 bg-gray-200" />
                    <Skeleton class="h-4 w-1/2 bg-gray-200" />
                </div>

                <div
                    class="flex items-end justify-between border-t border-gray-100 pt-3"
                >
                    <Skeleton class="h-6 w-16 bg-gray-200" />
                    <Skeleton class="h-4 w-10 bg-gray-200" />
                </div>
            </div>

            <Skeleton
                class="mt-3 h-9 w-full rounded-md bg-gray-200 md:hidden"
            />
        </div>
    </div>
</template>
