<template>
    <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600 sm:hidden">
        <div class="grid h-full max-w-lg grid-cols-5 mx-auto font-medium">
            <Link
                :href="route('dashboard')"
                type="button"
                class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors duration-200"
                :class="{ 'text-blue-600 dark:text-blue-500': route().current('dashboard'), 'text-gray-500 dark:text-gray-400': !route().current('dashboard') }"
            >
                <svg 
                    class="w-5 h-5 mb-2 transition-colors duration-200"
                    :class="{ 'text-blue-600 dark:text-blue-500': route().current('dashboard'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('dashboard') }"
                    aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                </svg>
                <span class="text-xs transition-colors duration-200" :class="{ 'text-blue-600 dark:text-blue-500': route().current('dashboard'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('dashboard') }">
                    داشبورد
                </span>
            </Link>
            
            <Link
                :href="route('fixtures.index')"
                type="button"
                class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors duration-200"
                :class="{ 'text-blue-600 dark:text-blue-500': route().current('fixtures.index'), 'text-gray-500 dark:text-gray-400': !route().current('fixtures.index') }"
            >
                <svg 
                    class="w-5 h-5 mb-2 transition-colors duration-200"
                    :class="{ 'text-blue-600 dark:text-blue-500': route().current('fixtures.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('fixtures.index') }"
                    aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.98 2.98 0 0 0 .13 5H5Z"/>
                    <path d="M14.066 0H7v5a2 2 0 0 1-2 2H0v11a1.97 1.97 0 0 0 1.934 2h12.132A1.97 1.97 0 0 0 16 18V2a1.97 1.97 0 0 0-1.934-2Z"/>
                </svg>
                <span class="text-xs transition-colors duration-200" :class="{ 'text-blue-600 dark:text-blue-500': route().current('fixtures.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('fixtures.index') }">
                    مسابقات
                </span>
            </Link>
            
            <Link
                :href="route('my-predictions.index')"
                type="button"
                class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors duration-200"
                :class="{ 'text-blue-600 dark:text-blue-500': route().current('my-predictions.index'), 'text-gray-500 dark:text-gray-400': !route().current('my-predictions.index') }"
            >
                <svg 
                    class="w-5 h-5 mb-2 transition-colors duration-200"
                    :class="{ 'text-blue-600 dark:text-blue-500': route().current('my-predictions.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('my-predictions.index') }"
                    aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="text-xs transition-colors duration-200" :class="{ 'text-blue-600 dark:text-blue-500': route().current('my-predictions.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('my-predictions.index') }">
                    پیش‌بینی‌ها
                </span>
            </Link>
            
            <Link
                :href="route('gameweek.index')"
                type="button"
                class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors duration-200"
                :class="{ 'text-blue-600 dark:text-blue-500': route().current('gameweek.index'), 'text-gray-500 dark:text-gray-400': !route().current('gameweek.index') }"
            >
                <svg 
                    class="w-5 h-5 mb-2 transition-colors duration-200"
                    :class="{ 'text-blue-600 dark:text-blue-500': route().current('gameweek.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('gameweek.index') }"
                    aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path d="M6 1a1 1 0 0 0-2 0h-.5A1.5 1.5 0 0 0 2 2.5v15A1.5 1.5 0 0 0 3.5 19h13a1.5 1.5 0 0 0 1.5-1.5v-15A1.5 1.5 0 0 0 16.5 1H16a1 1 0 1 0-2 0H6Z"/>
                    <path d="M4 5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm0 6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2Zm6 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-2Z"/>
                </svg>
                <span class="text-xs transition-colors duration-200" :class="{ 'text-blue-600 dark:text-blue-500': route().current('gameweek.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('gameweek.index') }">
                    هفته‌ها
                </span>
            </Link>
            
            <Link
                :href="route('leaderboard.index')"
                type="button"
                class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors duration-200"
                :class="{ 'text-blue-600 dark:text-blue-500': route().current('leaderboard.index'), 'text-gray-500 dark:text-gray-400': !route().current('leaderboard.index') }"
            >
                <svg 
                    class="w-5 h-5 mb-2 transition-colors duration-200"
                    :class="{ 'text-blue-600 dark:text-blue-500': route().current('leaderboard.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('leaderboard.index') }"
                    aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path d="M10 0C4.612 0 0 4.336 0 9.7 0 17 10 20 10 20s10-3 10-10.3C20 4.336 15.388 0 10 0ZM10 14a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                </svg>
                <span class="text-xs transition-colors duration-200" :class="{ 'text-blue-600 dark:text-blue-500': route().current('leaderboard.index'), 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !route().current('leaderboard.index') }">
                    امتیازات
                </span>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
