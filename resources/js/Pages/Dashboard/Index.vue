<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    upcomingFixtures: Array,
    recentPredictions: Array,
    userRank: Number,
    userPoints: Number,
    topLeaderboard: Array,
    totalUsers: Number,
});

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('nl-NL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getTimeUntilLock(fixture) {
    if (fixture.is_locked) return "Locked";
    
    if (fixture.time_until_prediction_locks > 24) {
        return `${Math.floor(fixture.time_until_prediction_locks / 24)}d ${Math.floor(fixture.time_until_prediction_locks % 24)}h left`;
    } else if (fixture.time_until_prediction_locks > 1) {
        return `${Math.floor(fixture.time_until_prediction_locks)}h left`;
    } else if (fixture.time_until_prediction_locks > 0) {
        return `${Math.floor(fixture.time_until_prediction_locks * 60)}min left`;
    }
    return "Locking soon";
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Upcoming Fixtures Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">🔷 Upcoming Fixtures</h3>
                            <Link :href="route('fixtures.index')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View All Fixtures →
                            </Link>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div 
                                v-for="fixture in upcomingFixtures" 
                                :key="fixture.id" 
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                                :class="{ 'bg-gray-50': fixture.is_locked, 'bg-yellow-50 border-yellow-200': !fixture.prediction && !fixture.is_locked }"
                            >
                                <!-- Match Info -->
                                <div class="text-center mb-3">
                                    <p class="text-xs text-gray-500 mb-1">{{ formatDateTime(fixture.match_datetime) }}</p>
                                    <p class="text-xs text-gray-400">{{ fixture.venue?.name }}</p>
                                </div>

                                <!-- Teams -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-right flex-1">
                                        <span class="font-medium text-sm">{{ fixture.home_team.name }}</span>
                                    </div>
                                    <div class="mx-4 text-gray-400 text-sm">vs</div>
                                    <div class="text-left flex-1">
                                        <span class="font-medium text-sm">{{ fixture.away_team.name }}</span>
                                    </div>
                                </div>

                                <!-- Prediction Status -->
                                <div class="flex justify-between items-center">
                                    <div class="text-xs">
                                        <span v-if="fixture.prediction" class="text-green-600 font-medium">
                                            Predicted: {{ fixture.prediction.home_score_predicted }}-{{ fixture.prediction.away_score_predicted }}
                                        </span>
                                        <span v-else-if="fixture.is_locked" class="text-red-500 font-medium">
                                            Missed
                                        </span>
                                        <span v-else class="text-yellow-600 font-medium">
                                            Not predicted
                                        </span>
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 mb-1">
                                            {{ getTimeUntilLock(fixture) }}
                                        </div>
                                        <Link 
                                            v-if="!fixture.is_locked"
                                            :href="route('fixtures.index', { matchweek: fixture.matchweek })"
                                            class="inline-block px-3 py-1 text-xs rounded-md transition-colors"
                                            :class="fixture.prediction 
                                                ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' 
                                                : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'"
                                        >
                                            {{ fixture.prediction ? 'Edit' : 'Predict' }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="upcomingFixtures.length === 0" class="text-center py-8 text-gray-500">
                            No upcoming fixtures found.
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout for Predictions and Leaderboard -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Your Recent Predictions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔷 Your Recent Predictions</h3>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="prediction in recentPredictions" 
                                    :key="prediction.id"
                                    class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-md"
                                >
                                    <div class="flex-1">
                                        <div class="text-sm font-medium">
                                            {{ prediction.fixture.home_team.name }} vs {{ prediction.fixture.away_team.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ formatDateTime(prediction.fixture.match_datetime) }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-bold text-blue-600">
                                        {{ prediction.home_score_predicted }}-{{ prediction.away_score_predicted }}
                                    </div>
                                    <div v-if="prediction.points_awarded !== null" class="ml-2 text-xs">
                                        <span class="px-2 py-1 rounded-full" 
                                              :class="prediction.points_awarded > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                            {{ prediction.points_awarded }}pts
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="recentPredictions.length === 0" class="text-center py-4 text-gray-500">
                                No predictions yet. Start predicting!
                            </div>
                        </div>
                    </div>

                    <!-- Leaderboard Preview -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">🔷 Leaderboard</h3>
                                <Link :href="route('leaderboard.index')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Full Leaderboard →
                                </Link>
                            </div>
                            
                            <!-- User's Rank -->
                            <div class="mb-4 p-3 bg-blue-50 rounded-md">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{userRank}}</div>
                                    <div class="text-sm text-gray-600">Your Current Rank</div>
                                    <div class="text-lg font-semibold text-blue-800">{{ userPoints }} Points</div>
                                    <div class="text-xs text-gray-500">out of {{ totalUsers }} players</div>
                                </div>
                            </div>

                            <!-- Top 5 -->
                            <div class="space-y-2">
                                <div 
                                    v-for="(user, index) in topLeaderboard" 
                                    :key="user.id"
                                    class="flex items-center justify-between py-2 px-3 rounded-md"
                                    :class="user.id === $page.props.auth.user.id ? 'bg-blue-100' : 'bg-gray-50'"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                             :class="index === 0 ? 'bg-yellow-400 text-yellow-900' : 
                                                    index === 1 ? 'bg-gray-300 text-gray-800' : 
                                                    index === 2 ? 'bg-orange-400 text-orange-900' : 
                                                    'bg-gray-200 text-gray-700'">
                                            {{ index + 1 }}
                                        </div>
                                        <span class="text-sm font-medium" 
                                              :class="user.id === $page.props.auth.user.id ? 'text-blue-800' : 'text-gray-900'">
                                            {{ user.name }}
                                            <span v-if="user.id === $page.props.auth.user.id" class="text-xs">(You)</span>
                                        </span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-600">{{ user.total_points }}pts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
