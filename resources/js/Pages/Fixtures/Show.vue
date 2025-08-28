<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Toast from '@/Components/Toast.vue';
import { useTranslations } from '@/composables/useTranslations.js';
import { computed, ref, watch } from 'vue';

const { translateTeamName, t } = useTranslations();

const props = defineProps({
    fixture: Object,
    predictionStats: Object,
    popularScores: Array,
});

// Toast state
const showToast = ref(false);
const toastMsg = ref('');
const toastType = ref('info');

function pushToast(message, type = 'info') {
    toastMsg.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
}

// Prediction form
const form = useForm({
    predictions: [{
        fixture_id: props.fixture.id,
        home_score: props.fixture.prediction?.home_score_predicted ?? null,
        away_score: props.fixture.prediction?.away_score_predicted ?? null,
    }],
});

// Auto-save functionality
const isAutoSaving = ref(false);
const lastSaved = ref(null);
let autoSaveTimeout = null;

watch(() => form.predictions[0], () => {
    if (props.fixture.is_locked) return;
    
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        autoSavePrediction();
    }, 2000);
}, { deep: true });

function autoSavePrediction() {
    const prediction = form.predictions[0];
    
    // Only auto-save if both scores are filled
    if (prediction.home_score === null || prediction.home_score === '' || 
        prediction.away_score === null || prediction.away_score === '') {
        return;
    }

    isAutoSaving.value = true;
    form.post(route("predictions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            lastSaved.value = new Date().toLocaleTimeString('fa-IR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            isAutoSaving.value = false;
            pushToast('پیش‌بینی ذخیره شد', 'success');
        },
        onError: () => {
            isAutoSaving.value = false;
            pushToast('خطا در ذخیره‌سازی', 'error');
        },
    });
}

function submitPrediction() {
    const prediction = form.predictions[0];
    
    // Validate that both scores are provided
    if (prediction.home_score === null || prediction.home_score === '' || 
        prediction.away_score === null || prediction.away_score === '') {
        pushToast('لطفاً هر دو نتیجه را وارد کنید', 'error');
        return;
    }

    form.post(route("predictions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            lastSaved.value = new Date().toLocaleTimeString('fa-IR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            pushToast('پیش‌بینی با موفقیت ذخیره شد', 'success');
        },
        onError: () => {
            pushToast('خطا در ذخیره‌سازی پیش‌بینی', 'error');
        }
    });
}

// Helper functions
function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('fa-IR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('fa-IR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getTimeUntilLock() {
    if (props.fixture.is_locked) return 'قفل شده';
    if (props.fixture.time_until_prediction_locks == null) return '';
    
    const hrs = props.fixture.time_until_prediction_locks;
    if (hrs > 24) {
        return `${Math.floor(hrs / 24)} روز ${Math.floor(hrs % 24)} ساعت باقی‌مانده`;
    } else if (hrs > 1) {
        return `${Math.floor(hrs)} ساعت باقی‌مانده`;
    } else if (hrs > 0) {
        return `${Math.floor(hrs * 60)} دقیقه باقی‌مانده`;
    }
    return 'به زودی قفل می‌شود';
}

// Score increment/decrement
function inc(field) {
    if (props.fixture.is_locked) return;
    const v = Number(form.predictions[0][field] ?? 0);
    form.predictions[0][field] = Math.max(0, v + 1);
}

function dec(field) {
    if (props.fixture.is_locked) return;
    const v = Number(form.predictions[0][field] ?? 0);
    form.predictions[0][field] = Math.max(0, v - 1);
}

// Check if prediction is incomplete
const isPredictionIncomplete = computed(() => {
    const prediction = form.predictions[0];
    const hasHomeScore = prediction.home_score !== null && prediction.home_score !== '';
    const hasAwayScore = prediction.away_score !== null && prediction.away_score !== '';
    
    return (hasHomeScore && !hasAwayScore) || (!hasHomeScore && hasAwayScore);
});

// Match status
const matchStatus = computed(() => {
    if (props.fixture.status === 'finished') {
        return 'تمام شده';
    } else if (props.fixture.status === 'in_play') {
        return 'در حال برگزاری';
    } else if (props.fixture.status === 'scheduled') {
        return 'برنامه‌ریزی شده';
    }
    return props.fixture.status;
});

const hasUserPrediction = computed(() => {
    return props.fixture.prediction !== null;
});
</script>

<template>
    <Head :title="`${translateTeamName(fixture.home_team.name)} در برابر ${translateTeamName(fixture.away_team.name)}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        جزئیات مسابقه
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        هفته {{ fixture.matchweek }} - {{ formatDateTime(fixture.match_datetime) }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('fixtures.index', { matchweek: fixture.matchweek })"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200"
                    >
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        بازگشت به هفته {{ fixture.matchweek }}
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Main Fixture Card -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-8">
                    <!-- Match Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center px-4 py-2 bg-white/20 rounded-full text-sm font-medium mb-4">
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ formatTime(fixture.match_datetime) }}
                            </div>
                            <h2 class="text-lg font-semibold mb-2">هفته {{ fixture.matchweek }}</h2>
                            <div class="text-sm opacity-90">{{ fixture.venue?.name || 'ورزشگاه نامشخص' }}</div>
                        </div>

                        <!-- Teams Display -->
                        <div class="grid grid-cols-3 items-center gap-4">
                            <!-- Home Team -->
                            <div class="text-center">
                                <div class="mb-4">
                                    <img 
                                        :src="`/assets/team-logos/${fixture.home_team.name}.png`" 
                                        :alt="fixture.home_team.name"
                                        class="w-20 h-20 mx-auto object-contain bg-white rounded-full p-2 shadow-lg"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                </div>
                                <h3 class="text-xl font-bold">{{ translateTeamName(fixture.home_team.name) }}</h3>
                                <div class="text-sm opacity-75 mt-1">میزبان</div>
                            </div>

                            <!-- Score/VS -->
                            <div class="text-center">
                                <div v-if="fixture.status === 'finished'" class="text-4xl font-bold mb-2">
                                    {{ fixture.home_score }} - {{ fixture.away_score }}
                                </div>
                                <div v-else class="text-2xl font-bold mb-2 opacity-75">
                                    VS
                                </div>
                                <div class="inline-flex items-center px-3 py-1 bg-white/20 rounded-full text-sm">
                                    {{ matchStatus }}
                                </div>
                            </div>

                            <!-- Away Team -->
                            <div class="text-center">
                                <div class="mb-4">
                                    <img 
                                        :src="`/assets/team-logos/${fixture.away_team.name}.png`" 
                                        :alt="fixture.away_team.name"
                                        class="w-20 h-20 mx-auto object-contain bg-white rounded-full p-2 shadow-lg"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                </div>
                                <h3 class="text-xl font-bold">{{ translateTeamName(fixture.away_team.name) }}</h3>
                                <div class="text-sm opacity-75 mt-1">مهمان</div>
                            </div>
                        </div>
                    </div>

                    <!-- Prediction Section -->
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">پیش‌بینی شما</h3>
                        
                        <div v-if="fixture.is_locked && !hasUserPrediction" class="text-center py-8">
                            <div class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-800 rounded-full text-sm font-medium mb-2">
                                <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                پیش‌بینی قفل شده
                            </div>
                            <p class="text-gray-600">زمان پیش‌بینی برای این مسابقه به پایان رسیده است</p>
                        </div>

                        <div v-else-if="fixture.is_locked && hasUserPrediction" class="text-center py-6">
                            <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium mb-4">
                                <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                پیش‌بینی ثبت شده
                            </div>
                            <div class="text-3xl font-bold text-gray-900 mb-2">
                                {{ fixture.prediction.home_score_predicted }} - {{ fixture.prediction.away_score_predicted }}
                            </div>
                            <p class="text-gray-600">پیش‌بینی شما برای این مسابقه</p>
                        </div>

                        <div v-else>
                            <!-- Active Prediction Form -->
                            <form @submit.prevent="submitPrediction" class="space-y-6">
                                <div class="flex items-center justify-center gap-6">
                                    <!-- Home Score Control -->
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-700 mb-2">{{ translateTeamName(fixture.home_team.name) }}</div>
                                        <div class="score-control flex flex-col items-center">
                                            <button 
                                                type="button" 
                                                class="w-16 py-2 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0"
                                                @click="inc('home_score')"
                                            >
                                                +
                                            </button>
                                            <input 
                                                type="number" 
                                                min="0" 
                                                max="20" 
                                                class="w-16 h-12 text-center text-lg font-bold appearance-none border-x border-slate-200 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                :class="{ 'bg-red-50': isPredictionIncomplete }"
                                                v-model.number="form.predictions[0].home_score"
                                            />
                                            <button 
                                                type="button" 
                                                class="w-16 py-2 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0"
                                                @click="dec('home_score')"
                                            >
                                                -
                                            </button>
                                        </div>
                                    </div>

                                    <div class="text-2xl font-bold text-gray-400">-</div>

                                    <!-- Away Score Control -->
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-700 mb-2">{{ translateTeamName(fixture.away_team.name) }}</div>
                                        <div class="score-control flex flex-col items-center">
                                            <button 
                                                type="button" 
                                                class="w-16 py-2 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0"
                                                @click="inc('away_score')"
                                            >
                                                +
                                            </button>
                                            <input 
                                                type="number" 
                                                min="0" 
                                                max="20" 
                                                class="w-16 h-12 text-center text-lg font-bold appearance-none border-x border-slate-200 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                :class="{ 'bg-red-50': isPredictionIncomplete }"
                                                v-model.number="form.predictions[0].away_score"
                                            />
                                            <button 
                                                type="button" 
                                                class="w-16 py-2 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0"
                                                @click="dec('away_score')"
                                            >
                                                -
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Validation Message -->
                                <div v-if="isPredictionIncomplete" class="text-center text-sm text-red-600">
                                    لطفاً هر دو نتیجه را وارد کنید
                                </div>

                                <!-- Submit Button -->
                                <div class="text-center">
                                    <button
                                        type="submit"
                                        :disabled="form.processing || isPredictionIncomplete"
                                        class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                    >
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ form.processing ? 'در حال ذخیره...' : 'ثبت پیش‌بینی' }}
                                    </button>
                                </div>

                                <!-- Auto-save indicator -->
                                <div class="text-center text-xs text-gray-500">
                                    <div v-if="isAutoSaving" class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        در حال ذخیره خودکار...
                                    </div>
                                    <div v-else-if="lastSaved">آخرین ذخیره: {{ lastSaved }}</div>
                                    <div v-else>{{ getTimeUntilLock() }}</div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Prediction Statistics -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">آمار پیش‌بینی‌ها</h3>
                        
                        <div v-if="predictionStats.total === 0" class="text-center py-8 text-gray-500">
                            هنوز پیش‌بینی‌ای ثبت نشده است
                        </div>
                        
                        <div v-else class="space-y-6">
                            <div class="text-center mb-6">
                                <div class="text-2xl font-bold text-gray-900">{{ predictionStats.total }}</div>
                                <div class="text-sm text-gray-600">پیش‌بینی ثبت شده</div>
                            </div>

                            <!-- Win/Draw/Loss Percentages -->
                            <div class="space-y-4">
                                <!-- Home Win -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                                        <span class="text-sm font-medium">برد {{ translateTeamName(fixture.home_team.name) }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-900">{{ predictionStats.home_win_percentage }}%</div>
                                        <div class="text-xs text-gray-500">{{ predictionStats.home_wins }} نفر</div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-300" :style="{ width: predictionStats.home_win_percentage + '%' }"></div>
                                </div>

                                <!-- Draw -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                                        <span class="text-sm font-medium">تساوی</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-900">{{ predictionStats.draw_percentage }}%</div>
                                        <div class="text-xs text-gray-500">{{ predictionStats.draws }} نفر</div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" :style="{ width: predictionStats.draw_percentage + '%' }"></div>
                                </div>

                                <!-- Away Win -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                                        <span class="text-sm font-medium">برد {{ translateTeamName(fixture.away_team.name) }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-900">{{ predictionStats.away_win_percentage }}%</div>
                                        <div class="text-xs text-gray-500">{{ predictionStats.away_wins }} نفر</div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full transition-all duration-300" :style="{ width: predictionStats.away_win_percentage + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Score Predictions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">محبوب‌ترین نتایج</h3>
                        
                        <div v-if="popularScores.length === 0" class="text-center py-8 text-gray-500">
                            هنوز پیش‌بینی‌ای ثبت نشده است
                        </div>
                        
                        <div v-else class="space-y-4">
                            <div 
                                v-for="(score, index) in popularScores" 
                                :key="score.score"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                        {{ index + 1 }}
                                    </div>
                                    <div class="font-mono text-lg font-bold text-gray-900">
                                        {{ score.score }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900">{{ score.percentage }}%</div>
                                    <div class="text-xs text-gray-500">{{ score.count }} نفر</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Match Information -->
                <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">اطلاعات مسابقه</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">تاریخ و ساعت</div>
                            <div class="font-semibold text-gray-900">{{ formatDateTime(fixture.match_datetime) }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">ورزشگاه</div>
                            <div class="font-semibold text-gray-900">{{ fixture.venue?.name || 'نامشخص' }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">وضعیت</div>
                            <div class="font-semibold text-gray-900">{{ matchStatus }}</div>
                        </div>
                    </div>
                </div>

                <Toast :show="showToast" :message="toastMsg" :type="toastType" position="bottom-right" @close="showToast=false" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Score controls styling */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}

input[type=number] { 
    appearance: textfield; 
    -moz-appearance: textfield; 
}

.score-control { 
    filter: drop-shadow(0 1px 2px rgb(0 0 0 / 0.1)); 
}

.score-control button:hover:not(:disabled) { 
    background-color: #3b82f6; 
    color: white; 
    border-color: #3b82f6; 
}
</style>
