<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    gameweeks: Array,
    currentMatchweek: Number,
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fa-IR', {
        day: 'numeric',
        month: 'short'
    });
}

function formatDateRange(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (start.toDateString() === end.toDateString()) {
        return formatDate(startDate);
    }
    
    return `${formatDate(startDate)} - ${formatDate(endDate)}`;
}

function getStatusColor(status) {
    switch (status) {
        case 'completed':
            return 'bg-gray-100 border-gray-300 text-gray-700';
        case 'active':
            return 'bg-green-100 border-green-400 text-green-800';
        case 'upcoming':
            return 'bg-blue-100 border-blue-300 text-blue-700';
        default:
            return 'bg-gray-100 border-gray-300 text-gray-700';
    }
}

function getStatusIcon(status) {
    switch (status) {
        case 'completed':
            return '✅';
        case 'active':
            return '⚽';
        case 'upcoming':
            return '📅';
        default:
            return '📅';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'completed':
            return 'تکمیل شده';
        case 'active':
            return 'فعال';
        case 'upcoming':
            return 'پیش رو';
        default:
            return 'نامشخص';
    }
}
</script>

<template>
    <Head title="هفته‌های لیگ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">
                هفته‌های لیگ
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Calendar Grid -->
                <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">تقویم هفته‌های بازی</h3>
                                <p class="text-sm text-gray-600 mt-1">نمای کلی از تمام هفته‌های فصل</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <div 
                                v-for="gameweek in gameweeks" 
                                :key="gameweek.matchweek"
                                class="gameweek-card p-5 border rounded-xl transition-all duration-200 hover:shadow-lg hover:-translate-y-1"
                                :class="[
                                    getStatusColor(gameweek.status),
                                    gameweek.matchweek === currentMatchweek 
                                        ? 'ring-2 ring-[var(--brand-2)] ring-opacity-60 transform scale-105' 
                                        : ''
                                ]"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl">{{ getStatusIcon(gameweek.status) }}</span>
                                        <h3 class="font-bold text-xl">هفته {{ gameweek.matchweek }}</h3>
                                    </div>
                                    <span class="status-badge text-xs px-2 py-1 rounded-full bg-white bg-opacity-60 font-medium">
                                        {{ getStatusText(gameweek.status) }}
                                    </span>
                                </div>

                                <!-- Date Range -->
                                <div class="mb-4">
                                    <p class="text-sm font-medium">
                                        {{ formatDateRange(gameweek.start_date, gameweek.end_date) }}
                                    </p>
                                    <p class="text-xs opacity-75">
                                        {{ gameweek.fixture_count }} بازی
                                    </p>
                                </div>

                                <!-- Prediction Stats -->
                                <div class="mb-6">
                                    <div class="flex justify-between items-center text-sm mb-2">
                                        <span>پیش‌بینی‌ها:</span>
                                        <span class="font-medium">{{ gameweek.predictions_made }}/{{ gameweek.fixture_count }}</span>
                                    </div>
                                    
                                    <!-- Progress Bar (brand) -->
                                    <div class="w-full bg-white bg-opacity-50 rounded-full h-3 mb-3">
                                        <div 
                                            class="progress-bar h-3 rounded-full transition-all duration-300"
                                            :style="{ width: (gameweek.predictions_made / gameweek.fixture_count * 100) + '%' }"
                                        ></div>
                                    </div>

                                    <div class="flex justify-between items-center text-xs">
                                        <span v-if="gameweek.predictions_completed > 0">
                                            امتیازات: <span class="font-bold">{{ gameweek.points_earned }}</span>
                                        </span>
                                        <span v-else-if="gameweek.status === 'completed' && gameweek.predictions_made === 0" class="status-badge px-2 py-1 rounded-full bg-red-100 text-red-600 font-medium">
                                            از دست‌رفته
                                        </span>
                                        <span v-else-if="gameweek.status === 'upcoming'" class="status-badge px-2 py-1 rounded-full bg-blue-100 text-blue-600 font-medium">
                                            {{ gameweek.predictions_open ? 'باز' : 'بسته' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div>
                                    <Link 
                                        :href="route('fixtures.index', { matchweek: gameweek.matchweek })"
                                        class="btn-primary block w-full px-4 py-3 text-sm text-center rounded-lg font-medium transition-all duration-200"
                                    >
                                        <span v-if="gameweek.predictions_open && gameweek.predictions_made < gameweek.fixture_count">
                                            {{ gameweek.predictions_made > 0 ? 'تکمیل پیش‌بینی‌ها' : 'شروع پیش‌بینی' }}
                                        </span>
                                        <span v-else>
                                            نمایش این هفته
                                        </span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-8 fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">راهنمای وضعیت هفته‌ها</h3>
                                <p class="text-sm text-gray-600 mt-1">توضیح رنگ‌ها و نمادها</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-lg border border-green-400 bg-green-100 flex items-center justify-center">
                                    <span class="text-xl">⚽</span>
                                </div>
                                <div>
                                    <p class="font-medium text-green-800">فعال</p>
                                    <p class="text-sm text-gray-600">هفته‌ی جاری</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-lg border border-blue-300 bg-blue-100 flex items-center justify-center">
                                    <span class="text-xl">📅</span>
                                </div>
                                <div>
                                    <p class="font-medium text-blue-700">پیش رو</p>
                                    <p class="text-sm text-gray-600">هفته‌ی آینده</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-lg border border-gray-300 bg-gray-100 flex items-center justify-center">
                                    <span class="text-xl">✅</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700">تکمیل شده</p>
                                    <p class="text-sm text-gray-600">هفته‌ی گذشته</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
