<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations.js';

const { translateTeamName, t } = useTranslations();

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
    return new Date(dateString).toLocaleString('fa-IR', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fa-IR', {
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
        case 'exact': return t('exact_score');
        case 'correct_outcome': return t('correct_winner');
        case 'wrong': return t('wrong');
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
    if (timeUntilLock <= 0) return t('locked');
    
    // Helper function to convert numbers to Persian
    const toPersianNumbers = (num) => {
        const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return num.toString().replace(/\d/g, (digit) => persianDigits[digit]);
    };
    
    if (timeUntilLock > 24) {
        const days = Math.floor(timeUntilLock / 24);
        const hours = Math.floor(timeUntilLock % 24);
        return `${toPersianNumbers(days)} روز ${toPersianNumbers(hours)} ساعت باقی‌مانده`;
    } else if (timeUntilLock > 1) {
        return `${toPersianNumbers(Math.floor(timeUntilLock))} ساعت باقی‌مانده`;
    } else {
        return `${toPersianNumbers(Math.floor(timeUntilLock * 60))} دقیقه باقی‌مانده`;
    }
}

const currentGameweekData = computed(() => {
    return props.gameweeks.find(gw => gw.matchweek === props.selectedGameweek);
});
</script>

<template>
    <Head :title="t('my_predictions')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 mb-1">
                        {{ t('my_predictions') }}
                    </h2>
                    <p class="text-sm text-gray-600">
                        شما {{ overallStats.total_predictions }} بازی را پیش‌بینی کرده‌اید. • 
                        میانگین {{ overallStats.avg_points_per_match }} امتیاز در هر بازی
                    </p>
                </div>
                <button 
                    @click="showMobileStats = !showMobileStats"
                    class="mt-2 sm:hidden text-sm text-blue-600 font-medium"
                >
                    {{ showMobileStats ? t('hide_stats') : t('show_stats') }}
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Overall Stats (Desktop) -->
                <div class="hidden sm:block mb-8">
                    <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600 mb-2">{{ overallStats.total_points }}</div>
                                <div class="text-sm text-gray-600">{{ t('total_points') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 mb-2">{{ overallStats.outcome_accuracy }}%</div>
                                <div class="text-sm text-gray-600">{{ t('prediction_accuracy') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-orange-600 mb-2">{{ overallStats.exact_accuracy }}%</div>
                                <div class="text-sm text-gray-600">{{ t('exact_score_rate') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600 mb-2">{{ overallStats.completed_predictions }}</div>
                                <div class="text-sm text-gray-600">{{ t('completed_predictions') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Stats (Mobile) -->
                <div class="sm:hidden mb-6" v-show="showMobileStats">
                    <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-xl font-bold text-blue-600">{{ overallStats.total_points }}</div>
                                <div class="text-xs text-gray-600">{{ t('total_points') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-green-600">{{ overallStats.outcome_accuracy }}%</div>
                                <div class="text-xs text-gray-600">{{ t('prediction_accuracy') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-orange-600">{{ overallStats.exact_accuracy }}%</div>
                                <div class="text-xs text-gray-600">{{ t('exact_score_rate') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-purple-600">{{ overallStats.completed_predictions }}</div>
                                <div class="text-xs text-gray-600">{{ t('completed_predictions') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gameweek Selector -->
                <div class="mb-8">
                    <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ t('gameweek') }}</h3>
                                <p class="text-sm text-gray-600 mt-1">انتخاب هفته برای مشاهده پیش‌بینی‌ها</p>
                            </div>
                            <div v-if="currentGameweekData" class="text-sm text-gray-600">
                                {{ currentGameweekData.predictions_made }}/{{ currentGameweekData.total_fixtures }} {{ t('predictions_made') }}
                                <span v-if="currentGameweekData.points_earned > 0">
                                    • {{ currentGameweekData.points_earned }} {{ t('points') }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Horizontal scroll gameweek selector -->
                        <div class="overflow-x-auto">
                            <div class="flex gap-3 pb-2" style="min-width: max-content;">
                                <button
                                    v-for="gameweek in gameweeks"
                                    :key="gameweek.matchweek"
                                    @click="selectGameweek(gameweek.matchweek)"
                                    class="flex-shrink-0 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 border"
                                    :class="gameweek.matchweek === selectedGameweek 
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
                                        : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300'"
                                >
                                    <div class="flex flex-col items-center">
                                        <span class="font-semibold">GW{{ gameweek.matchweek }}</span>
                                        <div class="flex items-center gap-1 mt-1">
                                            <div class="w-2 h-2 rounded-full"
                                                 :class="gameweek.predictions_made > 0 ? 'bg-green-400' : 'bg-gray-300'">
                                            </div>
                                            <span class="text-xs">{{ gameweek.predictions_made }}/{{ gameweek.total_fixtures }}</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Predictions List -->
                <div class="space-y-8">
                    <div 
                        v-for="(dayPredictions, date) in gameweekData.predictions" 
                        :key="date"
                        class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                    >
                        <!-- Date Header -->
                        <div class="date-header px-6 py-4 border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">{{ formatDate(date) }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ dayPredictions.length }} مسابقه</p>
                        </div>

                        <!-- Matches for this date -->
                        <div class="divide-y divide-gray-100">
                            <div 
                                v-for="prediction in dayPredictions" 
                                :key="prediction.fixture.id"
                                class="p-6 hover:bg-gray-50 transition-all duration-200"
                            >
                                <!-- Desktop Layout -->
                                <div class="hidden sm:grid sm:grid-cols-6 sm:items-center sm:gap-6">
                                    <!-- Teams -->
                                    <div class="col-span-2 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <img 
                                                :src="`/assets/team-logos/${prediction.fixture.home_team.name}.png`"
                                                :alt="prediction.fixture.home_team.name"
                                                class="team-logo w-8 h-8 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                            <span class="team-name text-gray-900">{{ translateTeamName(prediction.fixture.home_team.name) }}</span>
                                        </div>
                                        <span class="text-gray-400 font-medium">vs</span>
                                        <div class="flex items-center gap-3">
                                            <span class="team-name text-gray-900">{{ translateTeamName(prediction.fixture.away_team.name) }}</span>
                                            <img 
                                                :src="`/assets/team-logos/${prediction.fixture.away_team.name}.png`"
                                                :alt="prediction.fixture.away_team.name"
                                                class="team-logo w-8 h-8 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                        </div>
                                    </div>

                                    <!-- Prediction -->
                                    <div class="text-center">
                                        <div v-if="prediction.prediction" class="flex items-center justify-center gap-2">
                                            <span class="prediction-score w-10 h-10 flex items-center justify-center text-lg font-bold text-blue-600 bg-blue-50 rounded-lg">
                                                {{ prediction.prediction.home_score_predicted }}
                                            </span>
                                            <span class="text-lg font-bold text-gray-400">×</span>
                                            <span class="prediction-score w-10 h-10 flex items-center justify-center text-lg font-bold text-blue-600 bg-blue-50 rounded-lg">
                                                {{ prediction.prediction.away_score_predicted }}
                                            </span>
                                        </div>
                                        <div v-else class="text-sm text-gray-400">{{ t('no_prediction') }}</div>
                                        <div class="text-xs text-gray-500 mt-1">پیش‌بینی شما</div>
                                    </div>

                                    <!-- Actual Result -->
                                    <div class="text-center">
                                        <div v-if="prediction.is_completed" class="flex items-center justify-center gap-2">
                                            <span class="w-10 h-10 flex items-center justify-center text-lg font-bold text-gray-700 bg-gray-100 rounded-lg">
                                                {{ prediction.fixture.home_score }}
                                            </span>
                                            <span class="text-lg font-bold text-gray-400">×</span>
                                            <span class="w-10 h-10 flex items-center justify-center text-lg font-bold text-gray-700 bg-gray-100 rounded-lg">
                                                {{ prediction.fixture.away_score }}
                                            </span>
                                        </div>
                                        <div v-else class="text-sm text-gray-400">-</div>
                                        <div class="text-xs text-gray-500 mt-1">نتیجه نهایی</div>
                                    </div>

                                    <!-- Outcome -->
                                    <div class="text-center">
                                        <div v-if="prediction.outcome">
                                            <span class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                  :class="getOutcomeColor(prediction.outcome)">
                                                {{ getOutcomeIcon(prediction.outcome) }} {{ getOutcomeText(prediction.outcome) }}
                                            </span>
                                            <div v-if="prediction.prediction" class="text-xs text-gray-500 mt-1">
                                                {{ prediction.prediction.points_awarded }} {{ t('points') }}
                                            </div>
                                        </div>
                                        <div v-else class="text-sm text-gray-400">-</div>
                                    </div>

                                    <!-- Time -->
                                    <div class="text-right text-sm text-gray-500">
                                        {{ formatDateTime(prediction.fixture.match_datetime) }}
                                        <div v-if="!prediction.is_completed" class="text-xs mt-1">
                                            {{ getTimeUntilLock(prediction.time_until_lock) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Layout -->
                                <div class="sm:hidden space-y-4">
                                    <!-- Teams -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3 flex-1">
                                            <img 
                                                :src="`/assets/team-logos/${prediction.fixture.home_team.name}.png`"
                                                :alt="prediction.fixture.home_team.name"
                                                class="team-logo w-6 h-6 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                            <span class="team-name text-gray-900 text-sm">{{ translateTeamName(prediction.fixture.home_team.name) }}</span>
                                        </div>
                                        <span class="text-gray-400 font-medium mx-3">vs</span>
                                        <div class="flex items-center gap-3 flex-1 justify-end">
                                            <span class="team-name text-gray-900 text-sm">{{ translateTeamName(prediction.fixture.away_team.name) }}</span>
                                            <img 
                                                :src="`/assets/team-logos/${prediction.fixture.away_team.name}.png`"
                                                :alt="prediction.fixture.away_team.name"
                                                class="team-logo w-6 h-6 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                        </div>
                                    </div>

                                    <!-- Scores and Results -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Prediction -->
                                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                                            <div v-if="prediction.prediction" class="flex items-center justify-center gap-2">
                                                <span class="w-8 h-8 flex items-center justify-center text-lg font-bold text-blue-600 bg-white rounded">
                                                    {{ prediction.prediction.home_score_predicted }}
                                                </span>
                                                <span class="text-blue-600">×</span>
                                                <span class="w-8 h-8 flex items-center justify-center text-lg font-bold text-blue-600 bg-white rounded">
                                                    {{ prediction.prediction.away_score_predicted }}
                                                </span>
                                            </div>
                                            <div v-else class="text-sm text-gray-400">{{ t('no_prediction') }}</div>
                                            <div class="text-xs text-blue-600 mt-1 font-medium">پیش‌بینی شما</div>
                                        </div>

                                        <!-- Result -->
                                        <div class="text-center p-3 bg-gray-100 rounded-lg">
                                            <div v-if="prediction.is_completed" class="flex items-center justify-center gap-2">
                                                <span class="w-8 h-8 flex items-center justify-center text-lg font-bold text-gray-700 bg-white rounded">
                                                    {{ prediction.fixture.home_score }}
                                                </span>
                                                <span class="text-gray-700">×</span>
                                                <span class="w-8 h-8 flex items-center justify-center text-lg font-bold text-gray-700 bg-white rounded">
                                                    {{ prediction.fixture.away_score }}
                                                </span>
                                            </div>
                                            <div v-else class="text-sm text-gray-400">-</div>
                                            <div class="text-xs text-gray-600 mt-1 font-medium">نتیجه نهایی</div>
                                        </div>
                                    </div>

                                    <!-- Outcome and Time -->
                                    <div class="flex items-center justify-between">
                                        <div v-if="prediction.outcome">
                                            <span class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                  :class="getOutcomeColor(prediction.outcome)">
                                                {{ getOutcomeIcon(prediction.outcome) }} {{ getOutcomeText(prediction.outcome) }}
                                            </span>
                                            <div v-if="prediction.prediction" class="text-xs text-gray-500 mt-1">
                                                {{ prediction.prediction.points_awarded }} {{ t('points') }}
                                            </div>
                                        </div>
                                        
                                        <div class="text-right text-xs text-gray-500">
                                            {{ formatDateTime(prediction.fixture.match_datetime) }}
                                            <div v-if="!prediction.is_completed" class="mt-1">
                                                {{ getTimeUntilLock(prediction.time_until_lock) }}
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
                     class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="text-gray-400 text-lg mb-2">⚽</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ t('no_fixtures_found') }}</h3>
                    <p class="text-gray-600">{{ t('no_fixtures_gameweek') }}</p>
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
