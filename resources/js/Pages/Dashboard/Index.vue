<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations.js';
import { computed, ref, watch } from 'vue';

const { translateTeamName, t } = useTranslations();

const props = defineProps({
    upcomingFixtures: Array,
    recentPredictions: Array,
    userRank: Number,
    userPoints: Number,
    topLeaderboard: Array,
    totalUsers: Number,
});

// Auto-save functionality
const isAutoSaving = ref(false);
const lastSaved = ref(null);

// useForm will wrap our prediction data, making submission easy
const form = useForm({
    predictions: props.upcomingFixtures.map((f) => ({
        fixture_id: f.id,
        home_score: f.prediction?.home_score_predicted ?? null,
        away_score: f.prediction?.away_score_predicted ?? null,
    })),
});

// Auto-save predictions when they change
let autoSaveTimeout = null;
watch(() => form.predictions, () => {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        autoSavePredictions();
    }, 2000); // Auto-save after 2 seconds of inactivity
}, { deep: true });

function autoSavePredictions() {
    // Only auto-save if there are complete predictions (both home and away scores filled)
    const completeValidPredictions = form.predictions.filter(p => 
        p.home_score !== null && p.home_score !== '' && 
        p.away_score !== null && p.away_score !== ''
    );
    
    if (completeValidPredictions.length === 0) return;

    isAutoSaving.value = true;
    form.post(route("predictions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            lastSaved.value = new Date().toLocaleTimeString('fa-IR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            isAutoSaving.value = false;
        },
        onError: () => {
            isAutoSaving.value = false;
        },
    });
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('fa-IR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Format match date and time for mobile view in a more readable Farsi format
function formatMatchDateTime(dateString) {
    const date = new Date(dateString);
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('fa-IR', options);
}

function getTimeUntilLock(fixture) {
    if (fixture.is_locked) return "قفل شده";
    
    if (fixture.time_until_prediction_locks > 24) {
        return `${Math.floor(fixture.time_until_prediction_locks / 24)} روز ${Math.floor(fixture.time_until_prediction_locks % 24)} ساعت باقی‌مانده`;
    } else if (fixture.time_until_prediction_locks > 1) {
        return `${Math.floor(fixture.time_until_prediction_locks)} ساعت باقی‌مانده`;
    } else if (fixture.time_until_prediction_locks > 0) {
        return `${Math.floor(fixture.time_until_prediction_locks * 60)} دقیقه باقی‌مانده`;
    }
    return "به زودی قفل می‌شود";
}

// Helper function to check if a prediction is incomplete
function isPredictionIncomplete(index) {
    const prediction = form.predictions[index];
    const hasHomeScore = prediction.home_score !== null && prediction.home_score !== '';
    const hasAwayScore = prediction.away_score !== null && prediction.away_score !== '';
    
    // Incomplete if one score is filled but not the other
    return (hasHomeScore && !hasAwayScore) || (!hasHomeScore && hasAwayScore);
}

// Statistics
const completedPredictions = computed(() => {
    return form.predictions.filter(p => 
        p.home_score !== null && p.home_score !== '' && 
        p.away_score !== null && p.away_score !== ''
    ).length;
});

const totalEditableFixtures = computed(() => {
    return props.upcomingFixtures.filter(f => !f.is_locked).length;
});
</script>

<template>
    <Head title="داشبورد" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">
                داشبورد
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Error Display -->
                <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="text-red-800">
                            <div v-for="(error, key) in $page.props.errors" :key="key" class="text-sm">
                                {{ Array.isArray(error) ? error[0] : error }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="text-green-800 text-sm">
                            {{ $page.props.flash.success }}
                        </div>
                    </div>
                </div>

                <!-- Progress Indicator for Predictions -->
                <div v-if="totalEditableFixtures > 0" class="bg-white rounded-lg border border-gray-100 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-4 space-x-reverse">
                            <span class="text-gray-600">پیشرفت پیش‌بینی:</span>
                            <span class="font-semibold text-gray-900">{{ completedPredictions }} از {{ totalEditableFixtures }}</span>
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: totalEditableFixtures > 0 ? (completedPredictions / totalEditableFixtures * 100) + '%' : '0%' }"
                                ></div>
                            </div>
                        </div>
                        <div class="auto-save-indicator flex items-center space-x-2 space-x-reverse text-xs text-gray-500">
                            <svg v-if="isAutoSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="isAutoSaving">در حال ذخیره...</span>
                            <span v-else-if="lastSaved">آخرین ذخیره: {{ lastSaved }}</span>
                            <span v-else>پیش‌بینی‌های کامل خودکار ذخیره می‌شوند</span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Fixtures Section -->
                <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">پیش‌بینی شما از مسابقات آتی</h3>
                                <p class="text-sm text-gray-600 mt-1">مسابقات نزدیک که نیاز به پیش‌بینی دارند</p>
                            </div>
                            <Link :href="route('fixtures.index')" class="nav-btn inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-all duration-200">
                                مشاهده همه مسابقات
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                        
                        <form @submit.prevent="autoSavePredictions" class="space-y-4">
                            <div
                                v-for="(fixture, index) in upcomingFixtures"
                                :key="fixture.id"
                                class="fixture-card p-5 border border-gray-100 rounded-lg hover:shadow-md transition-all duration-200"
                                :class="{
                                    'bg-gray-50 opacity-75': fixture.is_locked,
                                    'bg-white': !fixture.is_locked,
                                    'border-red-300': isPredictionIncomplete(index)
                                }"
                            >
                                <!-- Time display -->
                                <div class="text-center mb-4">
                                    <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-700">
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{
                                            new Date(
                                                fixture.match_datetime
                                            ).toLocaleString("fa-IR")
                                        }}
                                    </span>
                                </div>

                                <!-- Desktop Layout -->
                                <div class="hidden sm:grid sm:grid-cols-5 sm:items-center sm:gap-4">
                                    <!-- Home Team -->
                                    <div class="col-span-2 flex items-center justify-end gap-3">
                                        <div class="text-right">
                                            <h4 class="team-name text-gray-900">{{
                                                translateTeamName(fixture.home_team.name)
                                            }}</h4>
                                        </div>
                                        <img 
                                            :src="`/assets/team-logos/${fixture.home_team.name}.png`"
                                            :alt="fixture.home_team.name"
                                            class="team-logo w-12 h-12 object-contain"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                    </div>

                                    <!-- Score Input/Display -->
                                    <div class="flex items-center justify-center gap-3">
                                        <div v-if="!fixture.is_locked" class="flex items-center gap-3">
                                            <input
                                                v-model.number="form.predictions[index].home_score"
                                                type="number"
                                                min="0"
                                                max="20"
                                                placeholder="?"
                                                class="prediction-input w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                :class="{
                                                    'border-red-300 bg-red-50': isPredictionIncomplete(index),
                                                    'border-green-300 bg-green-50': form.predictions[index].home_score !== null && form.predictions[index].away_score !== null
                                                }"
                                            />
                                            <span class="text-xl font-bold text-gray-400">×</span>
                                            <input
                                                v-model.number="form.predictions[index].away_score"
                                                type="number"
                                                min="0"
                                                max="20"
                                                placeholder="?"
                                                class="prediction-input w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                :class="{
                                                    'border-red-300 bg-red-50': isPredictionIncomplete(index),
                                                    'border-green-300 bg-green-50': form.predictions[index].home_score !== null && form.predictions[index].away_score !== null
                                                }"
                                            />
                                        </div>
                                        <div v-else class="flex items-center gap-3">
                                            <span class="prediction-score w-12 h-12 flex items-center justify-center text-lg font-bold text-blue-600 bg-blue-50 rounded-lg">
                                                {{ fixture.prediction?.home_score_predicted ?? '?' }}
                                            </span>
                                            <span class="text-xl font-bold text-gray-400">×</span>
                                            <span class="prediction-score w-12 h-12 flex items-center justify-center text-lg font-bold text-blue-600 bg-blue-50 rounded-lg">
                                                {{ fixture.prediction?.away_score_predicted ?? '?' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Away Team -->
                                    <div class="col-span-2 flex items-center justify-start gap-3">
                                        <img 
                                            :src="`/assets/team-logos/${fixture.away_team.name}.png`"
                                            :alt="fixture.away_team.name"
                                            class="team-logo w-12 h-12 object-contain"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                        <div class="text-left">
                                            <h4 class="team-name text-gray-900">{{
                                                translateTeamName(fixture.away_team.name)
                                            }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Layout -->
                                <div class="sm:hidden space-y-4">
                                    <!-- Teams -->
                                    <div class="flex items-center justify-between">
                                        <!-- Home Team -->
                                        <div class="flex items-center gap-3 flex-1">
                                            <img 
                                                :src="`/assets/team-logos/${fixture.home_team.name}.png`"
                                                :alt="fixture.home_team.name"
                                                class="team-logo w-8 h-8 object-contain flex-shrink-0"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                            <span class="team-name text-gray-900">{{
                                                translateTeamName(fixture.home_team.name)
                                            }}</span>
                                        </div>
                                        
                                        <span class="text-gray-400 font-bold mx-3">VS</span>
                                        
                                        <!-- Away Team -->
                                        <div class="flex items-center gap-3 flex-1 justify-end">
                                            <span class="team-name text-gray-900">{{
                                                translateTeamName(fixture.away_team.name)
                                            }}</span>
                                            <img 
                                                :src="`/assets/team-logos/${fixture.away_team.name}.png`"
                                                :alt="fixture.away_team.name"
                                                class="team-logo w-8 h-8 object-contain flex-shrink-0"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                        </div>
                                    </div>

                                    <!-- Prediction Input/Display Mobile -->
                                    <div class="flex items-center justify-center gap-4">
                                        <div v-if="!fixture.is_locked" class="flex items-center gap-4">
                                            <div class="text-center">
                                                <label class="block text-xs font-medium text-gray-600 mb-2">خانه</label>
                                                <input
                                                    v-model.number="form.predictions[index].home_score"
                                                    type="number"
                                                    min="0"
                                                    max="20"
                                                    placeholder="?"
                                                    class="prediction-input w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    :class="{
                                                        'border-red-300 bg-red-50': isPredictionIncomplete(index),
                                                        'border-green-300 bg-green-50': form.predictions[index].home_score !== null && form.predictions[index].away_score !== null
                                                    }"
                                                />
                                            </div>
                                            <span class="text-xl font-bold text-gray-400 mt-6">×</span>
                                            <div class="text-center">
                                                <label class="block text-xs font-medium text-gray-600 mb-2">مهمان</label>
                                                <input
                                                    v-model.number="form.predictions[index].away_score"
                                                    type="number"
                                                    min="0"
                                                    max="20"
                                                    placeholder="?"
                                                    class="prediction-input w-12 h-12 text-center text-lg font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    :class="{
                                                        'border-red-300 bg-red-50': isPredictionIncomplete(index),
                                                        'border-green-300 bg-green-50': form.predictions[index].home_score !== null && form.predictions[index].away_score !== null
                                                    }"
                                                />
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center gap-4">
                                            <div class="text-center">
                                                <label class="block text-xs font-medium text-gray-600 mb-2">خانه</label>
                                                <span class="prediction-score w-12 h-12 flex items-center justify-center text-lg font-bold text-blue-600 bg-blue-50 rounded-lg">
                                                    {{ fixture.prediction?.home_score_predicted ?? '?' }}
                                                </span>
                                            </div>
                                            <span class="text-xl font-bold text-gray-400 mt-6">×</span>
                                            <div class="text-center">
                                                <label class="block text-xs font-medium text-gray-600 mb-2">مهمان</label>
                                                <span class="prediction-score w-12 h-12 flex items-center justify-center text-lg font-bold text-blue-600 bg-blue-50 rounded-lg">
                                                    {{ fixture.prediction?.away_score_predicted ?? '?' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Status and Action -->
                                <div class="mt-6 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span v-if="form.predictions[index].home_score !== null && form.predictions[index].away_score !== null" class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            پیش‌بینی شده
                                        </span>
                                        <span v-else-if="fixture.is_locked" class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                            </svg>
                                            قفل شده
                                        </span>
                                        <span v-else-if="isPredictionIncomplete(index)" class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                            </svg>
                                            ناکامل
                                        </span>
                                        <span v-else class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                            </svg>
                                            پیش‌بینی نشده
                                        </span>
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">
                                            {{ getTimeUntilLock(fixture) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div v-if="upcomingFixtures.length === 0" class="text-center py-8 text-gray-500">
                            هیچ مسابقه آتی پیدا نشد.
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout for Predictions and Leaderboard -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Your Recent Predictions -->
                    <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ t('your_recent_predictions') }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">آخرین پیش‌بینی‌های شما</p>
                                </div>
                                <Link :href="route('my-predictions.index')" class="nav-btn inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-all duration-200">
                                    همه پیش‌بینی‌ها
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="prediction in recentPredictions" 
                                    :key="prediction.id"
                                    class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg border border-gray-100 hover:shadow-sm transition-all duration-200"
                                >
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ translateTeamName(prediction.fixture.home_team.name) }} vs {{ translateTeamName(prediction.fixture.away_team.name) }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ formatDateTime(prediction.fixture.match_datetime) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">
                                            {{ prediction.home_score_predicted }}-{{ prediction.away_score_predicted }}
                                        </div>
                                        <div v-if="prediction.points_awarded !== null" class="text-xs">
                                            <span class="px-2 py-1 rounded-full font-medium" 
                                                  :class="prediction.points_awarded > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                {{ prediction.points_awarded }}{{ t('pts') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="recentPredictions.length === 0" class="text-center py-8 text-gray-500">
                                <div class="text-gray-400 text-lg mb-2">⚽</div>
                                <p class="text-sm">هنوز پیش‌بینی‌ای نداشته‌اید. شروع کنید!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leaderboard Preview -->
                    <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ t('leaderboard') }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">برترین بازیکنان</p>
                                </div>
                                <Link :href="route('leaderboard.index')" class="nav-btn inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-all duration-200">
                                    جدول کامل
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                            
                            <!-- User's Rank -->
                            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{userRank}}</div>
                                    <div class="text-sm text-gray-600 mb-1">{{ t('your_current_rank') }}</div>
                                    <div class="text-lg font-semibold text-blue-800">{{ userPoints }} {{ t('points') }}</div>
                                    <div class="text-xs text-gray-500">از {{ totalUsers }} {{ t('of_players') }}</div>
                                </div>
                            </div>

                            <!-- Top 5 -->
                            <div class="space-y-2">
                                <div 
                                    v-for="(user, index) in topLeaderboard" 
                                    :key="user.id"
                                    class="flex items-center justify-between py-3 px-4 rounded-lg transition-all duration-200"
                                    :class="user.id === $page.props.auth.user.id ? 'bg-blue-100 border border-blue-200' : 'bg-gray-50 hover:bg-gray-100'"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                                             :class="index === 0 ? 'bg-yellow-400 text-yellow-900' : 
                                                    index === 1 ? 'bg-gray-300 text-gray-800' : 
                                                    index === 2 ? 'bg-orange-400 text-orange-900' : 
                                                    'bg-gray-200 text-gray-700'">
                                            {{ index + 1 }}
                                        </div>
                                        <span class="text-sm font-medium" 
                                              :class="user.id === $page.props.auth.user.id ? 'text-blue-800' : 'text-gray-900'">
                                            {{ user.name }}
                                            <span v-if="user.id === $page.props.auth.user.id" class="text-xs text-blue-600">(شما)</span>
                                        </span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-600">{{ user.total_points }}{{ t('pts') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
