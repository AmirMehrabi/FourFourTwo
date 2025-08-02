<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    gameweeks: Array,
    currentMatchweek: Number,
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('nl-NL', {
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
            return 'Completed';
        case 'active':
            return 'Active';
        case 'upcoming':
            return 'Upcoming';
        default:
            return 'Unknown';
    }
}
</script>

<template>
    <Head title="Gameweek Timeline" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">
                🔷 Gameweek Timeline
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Quick Navigation -->
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Navigation</h3>
                        <div class="flex flex-wrap gap-2">
                            <Link 
                                v-for="gameweek in gameweeks" 
                                :key="gameweek.matchweek"
                                :href="route('fixtures.index', { matchweek: gameweek.matchweek })"
                                class="px-3 py-2 text-sm rounded-md border transition-colors"
                                :class="[
                                    getStatusColor(gameweek.status),
                                    gameweek.matchweek === currentMatchweek 
                                        ? 'ring-2 ring-blue-500 ring-opacity-50' 
                                        : 'hover:shadow-md'
                                ]"
                            >
                                GW{{ gameweek.matchweek }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <div 
                                v-for="gameweek in gameweeks" 
                                :key="gameweek.matchweek"
                                class="border rounded-lg p-4 transition-all duration-200 hover:shadow-lg"
                                :class="[
                                    getStatusColor(gameweek.status),
                                    gameweek.matchweek === currentMatchweek 
                                        ? 'ring-2 ring-blue-500 ring-opacity-50 transform scale-105' 
                                        : ''
                                ]"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg">{{ getStatusIcon(gameweek.status) }}</span>
                                        <h3 class="font-bold text-lg">GW{{ gameweek.matchweek }}</h3>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full bg-white bg-opacity-60">
                                        {{ getStatusText(gameweek.status) }}
                                    </span>
                                </div>

                                <!-- Date Range -->
                                <div class="mb-3">
                                    <p class="text-sm font-medium">
                                        {{ formatDateRange(gameweek.start_date, gameweek.end_date) }}
                                    </p>
                                    <p class="text-xs opacity-75">
                                        {{ gameweek.fixture_count }} fixtures
                                    </p>
                                </div>

                                <!-- Prediction Stats -->
                                <div class="mb-4">
                                    <div class="flex justify-between items-center text-sm mb-1">
                                        <span>Predictions:</span>
                                        <span class="font-medium">{{ gameweek.predictions_made }}/{{ gameweek.fixture_count }}</span>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-white bg-opacity-50 rounded-full h-2 mb-2">
                                        <div 
                                            class="h-2 rounded-full transition-all duration-300"
                                            :class="gameweek.predictions_made === gameweek.fixture_count ? 'bg-green-500' : 'bg-blue-500'"
                                            :style="{ width: (gameweek.predictions_made / gameweek.fixture_count * 100) + '%' }"
                                        ></div>
                                    </div>

                                    <div class="flex justify-between items-center text-xs">
                                        <span v-if="gameweek.predictions_completed > 0">
                                            Points: <span class="font-bold">{{ gameweek.points_earned }}</span>
                                        </span>
                                        <span v-else-if="gameweek.status === 'completed' && gameweek.predictions_made === 0" class="text-red-600">
                                            Missed
                                        </span>
                                        <span v-else-if="gameweek.status === 'upcoming'" class="text-blue-600">
                                            {{ gameweek.predictions_open ? 'Open' : 'Locked' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-2">
                                    <Link 
                                        :href="route('fixtures.index', { matchweek: gameweek.matchweek })"
                                        class="block w-full px-3 py-2 text-sm text-center rounded-md transition-colors"
                                        :class="gameweek.predictions_open && gameweek.predictions_made < gameweek.fixture_count
                                            ? 'bg-yellow-500 hover:bg-yellow-600 text-white font-medium'
                                            : 'bg-white bg-opacity-60 hover:bg-opacity-80 text-gray-700'"
                                    >
                                        <span v-if="gameweek.predictions_open && gameweek.predictions_made < gameweek.fixture_count">
                                            {{ gameweek.predictions_made > 0 ? 'Complete Predictions' : 'Make Predictions' }}
                                        </span>
                                        <span v-else>
                                            View Fixtures
                                        </span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Legend</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded border border-green-400 bg-green-100 flex items-center justify-center">
                                    ⚽
                                </div>
                                <div>
                                    <p class="font-medium text-green-800">Active</p>
                                    <p class="text-xs text-gray-600">Gameweek in progress</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded border border-blue-300 bg-blue-100 flex items-center justify-center">
                                    📅
                                </div>
                                <div>
                                    <p class="font-medium text-blue-700">Upcoming</p>
                                    <p class="text-xs text-gray-600">Future gameweek</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded border border-gray-300 bg-gray-100 flex items-center justify-center">
                                    ✅
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700">Completed</p>
                                    <p class="text-xs text-gray-600">Past gameweek</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
