<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    selectedGameweek: Number,
    overallStats: Object,
    gameweeks: Array,
    gameweekData: Object,
});

const editingPrediction = ref(null);
const showMobileStats = ref(false);

const editForm = useForm({
    home_score_predicted: 0,
    away_score_predicted: 0,
});

function selectGameweek(gameweek) {
    router.get(route('my-predictions.index'), { gameweek }, { 
        preserveState: true, 
        preserveScroll: true 
    });
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('en-US', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
}

function startEditing(prediction) {
    editingPrediction.value = prediction.prediction.id;
    editForm.home_score_predicted = prediction.prediction.home_score_predicted;
    editForm.away_score_predicted = prediction.prediction.away_score_predicted;
}

function cancelEditing() {
    editingPrediction.value = null;
    editForm.reset();
}

function saveEdit(predictionId) {
    editForm.put(route('my-predictions.update', predictionId), {
        onSuccess: () => {
            editingPrediction.value = null;
            editForm.reset();
        }
    });
}

function getOutcomeIcon(outcome) {
    switch (outcome) {
        case 'exact': return '🔥';
        case 'correct_outcome': return '✅';
        case 'wrong': return '❌';
        default: return '';
    }
}

function getOutcomeText(outcome) {
    switch (outcome) {
        case 'exact': return 'Exact Score';
        case 'correct_outcome': return 'Correct Winner';
        case 'wrong': return 'Wrong';
        default: return '';
    }
}

function getOutcomeColor(outcome) {
    switch (outcome) {
        case 'exact': return 'text-orange-600 bg-orange-50';
        case 'correct_outcome': return 'text-green-600 bg-green-50';
        case 'wrong': return 'text-red-600 bg-red-50';
        default: return 'text-gray-600 bg-gray-50';
    }
}

function getTimeUntilLock(timeUntilLock) {
    if (timeUntilLock <= 0) return "Locked";
    
    if (timeUntilLock > 24) {
        const days = Math.floor(timeUntilLock / 24);
        const hours = Math.floor(timeUntilLock % 24);
        return `${days}d ${hours}h left`;
    } else if (timeUntilLock > 1) {
        return `${Math.floor(timeUntilLock)}h left`;
    } else {
        return `${Math.floor(timeUntilLock * 60)}min left`;
    }
}

const currentGameweekData = computed(() => {
    return props.gameweeks.find(gw => gw.matchweek === props.selectedGameweek);
});
</script>

<template>
    <Head title="My Predictions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 mb-1">
                        My Predictions
                    </h2>
                    <p class="text-sm text-gray-600">
                        You've predicted {{ overallStats.total_predictions }} matches • 
                        Average {{ overallStats.avg_points_per_match }} points per match
                    </p>
                </div>
                <button 
                    @click="showMobileStats = !showMobileStats"
                    class="mt-2 sm:hidden text-sm text-blue-600 font-medium"
                >
                    {{ showMobileStats ? 'Hide' : 'Show' }} Stats
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Overall Stats (Desktop) -->
                <div class="hidden sm:block mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ overallStats.total_points }}</div>
                                <div class="text-sm text-gray-600">Total Points</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ overallStats.outcome_accuracy }}%</div>
                                <div class="text-sm text-gray-600">Outcome Accuracy</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ overallStats.exact_accuracy }}%</div>
                                <div class="text-sm text-gray-600">Exact Score Rate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ overallStats.completed_predictions }}</div>
                                <div class="text-sm text-gray-600">Completed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Stats (Mobile) -->
                <div class="sm:hidden mb-6" v-show="showMobileStats">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-xl font-bold text-blue-600">{{ overallStats.total_points }}</div>
                                <div class="text-xs text-gray-600">Total Points</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-green-600">{{ overallStats.outcome_accuracy }}%</div>
                                <div class="text-xs text-gray-600">Accuracy</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-orange-600">{{ overallStats.exact_accuracy }}%</div>
                                <div class="text-xs text-gray-600">Exact Scores</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-purple-600">{{ overallStats.completed_predictions }}</div>
                                <div class="text-xs text-gray-600">Completed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gameweek Selector -->
                <div class="mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-gray-900">Gameweek</h3>
                            <div v-if="currentGameweekData" class="text-sm text-gray-600">
                                {{ currentGameweekData.predictions_made }}/{{ currentGameweekData.total_fixtures }} predicted
                                <span v-if="currentGameweekData.points_earned > 0">
                                    • {{ currentGameweekData.points_earned }} pts
                                </span>
                            </div>
                        </div>
                        
                        <!-- Horizontal scroll gameweek selector -->
                        <div class="overflow-x-auto">
                            <div class="flex space-x-2 pb-2" style="min-width: max-content;">
                                <button
                                    v-for="gameweek in gameweeks"
                                    :key="gameweek.matchweek"
                                    @click="selectGameweek(gameweek.matchweek)"
                                    class="flex-shrink-0 px-3 py-2 rounded-md text-sm font-medium transition-colors"
                                    :class="gameweek.matchweek === selectedGameweek 
                                        ? 'bg-blue-600 text-white' 
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    <div class="flex flex-col items-center">
                                        <span>GW{{ gameweek.matchweek }}</span>
                                        <div class="flex items-center space-x-1 mt-1">
                                            <div 
                                                class="w-1.5 h-1.5 rounded-full"
                                                :class="{
                                                    'bg-green-400': gameweek.status === 'completed',
                                                    'bg-yellow-400': gameweek.status === 'active',
                                                    'bg-gray-300': gameweek.status === 'upcoming'
                                                }"
                                            ></div>
                                            <span class="text-xs">
                                                {{ gameweek.predictions_made }}/{{ gameweek.total_fixtures }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Predictions List -->
                <div class="space-y-6">
                    <div 
                        v-for="(dayPredictions, date) in gameweekData.predictions" 
                        :key="date"
                        class="bg-white rounded-lg shadow-sm border border-gray-200"
                    >
                        <!-- Date Header -->
                        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                            <h4 class="font-medium text-gray-900">{{ formatDate(date) }}</h4>
                        </div>

                        <!-- Matches for this date -->
                        <div class="divide-y divide-gray-200">
                            <div 
                                v-for="prediction in dayPredictions" 
                                :key="prediction.fixture.id"
                                class="p-6"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    
                                    <!-- Match Info -->
                                    <div class="flex-1 mb-4 sm:mb-0">
                                        <div class="flex items-center justify-between sm:justify-start sm:space-x-8">
                                            <!-- Teams -->
                                            <div class="flex items-center space-x-4">
                                                <div class="text-right">
                                                    <div class="font-medium text-gray-900">{{ prediction.fixture.home_team.name }}</div>
                                                </div>
                                                <div class="text-gray-400 font-medium">vs</div>
                                                <div class="text-left">
                                                    <div class="font-medium text-gray-900">{{ prediction.fixture.away_team.name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Match Details -->
                                        <div class="mt-2 text-sm text-gray-600">
                                            {{ formatDateTime(prediction.fixture.match_datetime) }}
                                            <span v-if="prediction.fixture.venue" class="ml-2">
                                                • {{ prediction.fixture.venue.name }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Prediction & Status -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-6">
                                        
                                        <!-- User Prediction -->
                                        <div class="mb-3 sm:mb-0">
                                            <div v-if="prediction.prediction && editingPrediction !== prediction.prediction.id" 
                                                 class="text-center">
                                                <div class="text-lg font-bold text-blue-600">
                                                    {{ prediction.prediction.home_score_predicted }} - {{ prediction.prediction.away_score_predicted }}
                                                </div>
                                                <div class="text-xs text-gray-500">Your Prediction</div>
                                            </div>
                                            
                                            <!-- Edit Form -->
                                            <!-- <div v-else-if="editingPrediction === prediction.prediction.id" 
                                                 class="flex items-center space-x-2">
                                                <select v-model="editForm.home_score_predicted" 
                                                        class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                                    <option v-for="n in 11" :key="n-1" :value="n-1">{{ n-1 }}</option>
                                                </select>
                                                <span class="text-gray-400">-</span>
                                                <select v-model="editForm.away_score_predicted" 
                                                        class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                                    <option v-for="n in 11" :key="n-1" :value="n-1">{{ n-1 }}</option>
                                                </select>
                                                <button @click="saveEdit(prediction.prediction.id)" 
                                                        :disabled="editForm.processing"
                                                        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 disabled:opacity-50">
                                                    Save
                                                </button>
                                                <button @click="cancelEditing" 
                                                        class="px-3 py-1 bg-gray-300 text-gray-700 text-xs rounded hover:bg-gray-400">
                                                    Cancel
                                                </button>
                                            </div> -->
                                            
                                            <div v-else class="text-center text-gray-400">
                                                <div class="text-sm">No Prediction</div>
                                            </div>
                                        </div>

                                        <!-- Actual Result (if completed) -->
                                        <div v-if="prediction.is_completed" class="mb-3 sm:mb-0 text-center">
                                            <div class="text-lg font-bold text-gray-800">
                                                {{ prediction.fixture.home_score }} - {{ prediction.fixture.away_score }}
                                            </div>
                                            <div class="text-xs text-gray-500">Actual Result</div>
                                        </div>

                                        <!-- Status & Actions -->
                                        <div class="flex items-center justify-between sm:flex-col sm:items-end">
                                            
                                            <!-- Outcome Badge -->
                                            <div v-if="prediction.outcome" class="mb-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                      :class="getOutcomeColor(prediction.outcome)">
                                                    {{ getOutcomeIcon(prediction.outcome) }} {{ getOutcomeText(prediction.outcome) }}
                                                    <span v-if="prediction.prediction" class="ml-1">
                                                        ({{ prediction.prediction.points_awarded }}pts)
                                                    </span>
                                                </span>
                                            </div>

                                            <!-- Time & Actions -->
                                            <div class="text-right">
                                                <div v-if="!prediction.is_completed" class="text-xs text-gray-500 mb-1">
                                                    {{ getTimeUntilLock(prediction.time_until_lock) }}
                                                </div>
                                                
                                                <div class="flex space-x-2">
                                                    <button v-if="prediction.can_edit && prediction.prediction && editingPrediction !== prediction.prediction.id"
                                                            @click="startEditing(prediction)"
                                                            class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                                        Edit
                                                    </button>
                                                    
                                                    <Link v-if="prediction.can_edit && !prediction.prediction"
                                                          :href="route('fixtures.index', { matchweek: selectedGameweek })"
                                                          class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
                                                        Predict Now
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="Object.keys(gameweekData.predictions).length === 0" 
                     class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <div class="text-gray-400 text-lg mb-2">⚽</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No fixtures found</h3>
                    <p class="text-gray-600">There are no fixtures for this gameweek yet.</p>
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar for horizontal gameweek selector */
.overflow-x-auto::-webkit-scrollbar {
    height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
