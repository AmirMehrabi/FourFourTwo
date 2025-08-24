<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Toast from '@/Components/Toast.vue';
import { useTranslations } from '@/composables/useTranslations.js';
import { computed, ref, watch } from 'vue';

const { translateTeamName, t } = useTranslations();

const props = defineProps({
    fixtures: Array,
    matchweek: Number,
});

// Auto-save functionality
const isAutoSaving = ref(false);
const lastSaved = ref(null);
// Toast state (consistent with Dashboard)
const showToast = ref(false);
const toastMsg = ref('');
const toastType = ref('info'); // 'info' | 'success' | 'error'

function pushToast(message, type = 'info') {
    toastMsg.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 2500);
}

// useForm will wrap our prediction data, making submission easy
const form = useForm({
    predictions: props.fixtures.map((f) => ({
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
            lastSaved.value = new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
            isAutoSaving.value = false;
            pushToast('ذخیره خودکار شد', 'success');
        },
        onError: () => {
            isAutoSaving.value = false;
            pushToast('خطا در ذخیره‌سازی خودکار', 'error');
        },
    });
}

function submitPredictions() {
    // Validate that all non-empty predictions have both scores
    const incompleteIndexes = [];
    form.predictions.forEach((prediction, index) => {
        const hasHomeScore = prediction.home_score !== null && prediction.home_score !== '';
        const hasAwayScore = prediction.away_score !== null && prediction.away_score !== '';
        
        // If one score is filled but not the other, it's incomplete
        if ((hasHomeScore && !hasAwayScore) || (!hasHomeScore && hasAwayScore)) {
            incompleteIndexes.push(index);
        }
    });
    
    if (incompleteIndexes.length > 0) {
        // Show error for incomplete predictions
        pushToast('لطفاً برای همه پیش‌بینی‌ها هر دو نتیجه (خانه و مهمان) را وارد کنید یا خالی بگذارید.', 'error');
        return;
    }

    form.post(route("predictions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            lastSaved.value = new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
            pushToast('پیش‌بینی‌ها ذخیره شد', 'success');
        },
        onError: () => { pushToast('خطا در ذخیره‌سازی دستی', 'error'); }
    });
}

// Group fixtures by date
const fixturesByDate = computed(() => {
    const grouped = {};
    
    props.fixtures.forEach((fixture, index) => {
        // Get the date part only (without time)
        const date = new Date(fixture.match_datetime).toDateString();
        
        if (!grouped[date]) {
            grouped[date] = [];
        }
        
        grouped[date].push({
            ...fixture,
            originalIndex: index // Keep track of original index for form mapping
        });
    });
    
    return grouped;
});

// Enhanced date formatting
function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (date.toDateString() === today.toDateString()) {
        return 'امروز';
    } else if (date.toDateString() === tomorrow.toDateString()) {
        return 'فردا';
    }
    
    return date.toLocaleDateString('fa-IR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('fa-IR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Full date/time for footer like dashboard
function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('fa-IR', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

// Time until lock (if backend supplies time_until_prediction_locks)
function getTimeUntilLock(fixture) {
    if (fixture.is_locked) return 'قفل شده';
    if (fixture.time_until_prediction_locks == null) return '';
    const hrs = fixture.time_until_prediction_locks;
    if (hrs > 24) {
        return `${Math.floor(hrs / 24)} روز ${Math.floor(hrs % 24)} ساعت باقی‌مانده`;
    } else if (hrs > 1) {
        return `${Math.floor(hrs)} ساعت باقی‌مانده`;
    } else if (hrs > 0) {
        return `${Math.floor(hrs * 60)} دقیقه باقی‌مانده`;
    }
    return 'به زودی قفل می‌شود';
}

// Increment / decrement helpers like dashboard
function inc(index, field) {
    const v = Number(form.predictions[index][field] ?? 0);
    form.predictions[index][field] = Math.max(0, v + 1);
}
function dec(index, field) {
    const v = Number(form.predictions[index][field] ?? 0);
    form.predictions[index][field] = Math.max(0, v - 1);
}

// Statistics
const completedPredictions = computed(() => {
    return form.predictions.filter((p, index) => {
        // Only count predictions for unlocked fixtures
        const fixture = props.fixtures[index];
        return !fixture.is_locked && 
               p.home_score !== null && p.home_score !== '' && 
               p.away_score !== null && p.away_score !== '';
    }).length;
});

const totalFixtures = computed(() => {
    return props.fixtures.filter(f => !f.is_locked).length;
});

// Helper function to check if a prediction is incomplete
function isPredictionIncomplete(index) {
    const prediction = form.predictions[index];
    const hasHomeScore = prediction.home_score !== null && prediction.home_score !== '';
    const hasAwayScore = prediction.away_score !== null && prediction.away_score !== '';
    
    // Incomplete if one score is filled but not the other
    return (hasHomeScore && !hasAwayScore) || (!hasHomeScore && hasAwayScore);
}

// Disable submit when any prediction is incomplete
const hasIncompletePredictions = computed(() => {
    return form.predictions.some((_, idx) => isPredictionIncomplete(idx));
});
</script>

<template>
    <!-- Localize head title -->
    <Head :title="'پیش‌بینی‌های هفته ' + matchweek" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        هفته‌ی {{ matchweek }}
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        پیش‌بینی نتایج مسابقات
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="matchweek > 1"
                        :href="
                            route('fixtures.index', {
                                matchweek: matchweek - 1,
                            })
                        "
                        class="nav-btn inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200"
                    >
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        هفته‌ی قبل
                    </Link>
                    <span class="text-sm text-gray-500 px-2">{{ matchweek }} از 38</span>
                    <Link
                        v-if="matchweek < 38"
                        :href="
                            route('fixtures.index', {
                                matchweek: matchweek + 1,
                            })
                        "
                        class="nav-btn inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200"
                    >
                        هفته‌ی بعد
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Error Display -->
                <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
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


                <!-- Progress Indicator -->
                <div class="mb-8 bg-white rounded-lg border border-gray-100 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-4 space-x-reverse">
                            <span class="text-gray-600">پیشرفت:</span>
                            <span class="font-semibold text-gray-900">{{ completedPredictions }} از {{ totalFixtures }}</span>
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div 
                                    class="progress-bar h-2 rounded-full transition-all duration-300"
                                    :style="{ width: totalFixtures > 0 ? (completedPredictions / totalFixtures * 100) + '%' : '0%' }"
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
                            <span v-else>فقط پیش‌بینی‌های کامل خودکار ذخیره می‌شوند</span>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitPredictions">
                    <!-- Fixtures grouped by date -->
                    <div class="space-y-8">
                        <div 
                            v-for="(dayFixtures, date) in fixturesByDate" 
                            :key="date"
                            class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                        >
                            <!-- Date Header -->
                            <div class="date-header px-6 py-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900">{{ formatDate(date) }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ dayFixtures.length }} مسابقه</p>
                            </div>

                            <!-- Fixtures for this date -->
                            <div class="divide-y divide-gray-100">
                                <div
                                    v-for="fixture in dayFixtures"
                                    :key="fixture.id"
                                    class="relative"
                                    :class="{
                                        'fixture-card locked bg-gray-50': fixture.is_locked,
                                        'fixture-card': !fixture.is_locked
                                    }"
                                >
                                    <!-- Locked overlay -->
                                    <div
                                        v-if="fixture.is_locked"
                                        class="absolute top-2 left-2 z-10"
                                    >
                                        <span class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <svg class="lock-icon w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                            </svg>
                                            قفل شده
                                        </span>
                                    </div>

                                    <div class="p-6">
                                        <!-- Time display -->
                                        <div class="text-center mb-6">
                                            <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-700">
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ formatTime(fixture.match_datetime) }}
                                            </span>
                                        </div>

                                        <!-- Desktop Layout -->
                                        <div class="hidden sm:grid sm:grid-cols-5 sm:items-center sm:gap-4">
                                            <!-- Home Team -->
                                            <div class="col-span-2 flex items-center justify-end gap-3">
                                                <div class="text-right">
                                                    <h4 class="team-name text-gray-900">{{ translateTeamName(fixture.home_team.name) }}</h4>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <img :src="`/assets/team-logos/${fixture.home_team.name}.png`" :alt="fixture.home_team.name" class="team-logo w-12 h-12 object-contain" @error="$event.target.style.display = 'none'" />
                                                </div>
                                            </div>
                                            <!-- Score Controls -->
                                            <div class="col-span-1 flex items-center justify-center gap-2" :class="{ 'border-red-300': isPredictionIncomplete(fixture.originalIndex) }">
                                                <div class="score-control flex flex-col items-center">
                                                    <button type="button" class="w-14 py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(fixture.originalIndex, 'home_score')">+</button>
                                                    <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input w-14 h-10 text-center appearance-none border-x border-slate-200 focus:z-10" :class="{ 'bg-red-50': isPredictionIncomplete(fixture.originalIndex) }" v-model.number="form.predictions[fixture.originalIndex].home_score" />
                                                    <button type="button" class="w-14 py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(fixture.originalIndex, 'home_score')">-</button>
                                                </div>
                                                <span class="text-slate-500">-</span>
                                                <div class="score-control flex flex-col items-center">
                                                    <button type="button" class="w-14 py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(fixture.originalIndex, 'away_score')">+</button>
                                                    <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input w-14 h-10 text-center appearance-none border-x border-slate-200 focus:z-10" :class="{ 'bg-red-50': isPredictionIncomplete(fixture.originalIndex) }" v-model.number="form.predictions[fixture.originalIndex].away_score" />
                                                    <button type="button" class="w-14 py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(fixture.originalIndex, 'away_score')">-</button>
                                                </div>
                                            </div>
                                            <!-- Away Team -->
                                            <div class="col-span-2 flex items-center justify-start gap-3">
                                                <div class="flex-shrink-0">
                                                    <img :src="`/assets/team-logos/${fixture.away_team.name}.png`" :alt="fixture.away_team.name" class="team-logo w-12 h-12 object-contain" @error="$event.target.style.display = 'none'" />
                                                </div>
                                                <div class="text-left">
                                                    <h4 class="team-name text-gray-900">{{ translateTeamName(fixture.away_team.name) }}</h4>
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

                                            <!-- Score Inputs Mobile (with + / -) -->
                                            <div class="flex items-center justify-center gap-4">
                                                <div class="score-control flex flex-col items-center">
                                                    <button type="button" class="w-full py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-xs border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(fixture.originalIndex, 'home_score')">+</button>
                                                    <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input score-input-mobile text-center appearance-none border-x border-slate-200 focus:z-10" :class="{ 'bg-red-50': isPredictionIncomplete(fixture.originalIndex) }" v-model.number="form.predictions[fixture.originalIndex].home_score" />
                                                    <button type="button" class="w-full py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-xs border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(fixture.originalIndex, 'home_score')">-</button>
                                                </div>
                                                <span class="text-slate-500">-</span>
                                                <div class="score-control flex flex-col items-center">
                                                    <button type="button" class="w-full py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-xs border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(fixture.originalIndex, 'away_score')">+</button>
                                                    <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input score-input-mobile text-center appearance-none border-x border-slate-200 focus:z-10" :class="{ 'bg-red-50': isPredictionIncomplete(fixture.originalIndex) }" v-model.number="form.predictions[fixture.originalIndex].away_score" />
                                                    <button type="button" class="w-full py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-xs border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(fixture.originalIndex, 'away_score')">-</button>
                                                </div>
                                            </div>
                                            
                                            <!-- Mobile validation message -->
                                            <div v-if="isPredictionIncomplete(fixture.originalIndex)" class="text-center text-xs text-red-600 mt-2">
                                                لطفاً هر دو نتیجه را وارد کنید
                                            </div>
                                        </div>
                                        <!-- Status/Footer -->
                                        <div class="mt-6 flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span v-if="fixture.is_locked" class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs bg-slate-200 text-slate-700 border border-slate-300">قفل شده</span>
                                                <span v-else class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">{{ getTimeUntilLock(fixture) }}</span>
                                            </div>
                                            <div class="text-xs text-slate-500">{{ formatDateTime(fixture.match_datetime) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-center">
                        <button
                            type="submit"
                            :disabled="form.processing || hasIncompletePredictions"
                            class="btn-primary inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ form.processing ? 'در حال ذخیره...' : (hasIncompletePredictions ? 'تکمیل نمرات ناقص' : t('save_changes')) }}
                        </button>
                    </div>
                </form>

                <Toast :show="showToast" :message="toastMsg" :type="toastType" position="bottom-right" @close="showToast=false" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Score controls styling (toast now shared component) */
/* existing below */
</style>
<style>
/* Added score controls styling to align with dashboard */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
input[type=number] { appearance: textfield; -moz-appearance: textfield; }
.score-input { border-top: 0; border-bottom: 0; border-radius: 0; height: 2.5rem; }
.score-input:focus { outline: none; border-color: var(--brand-2); box-shadow: 0 0 0 1px var(--brand-2); }
.score-control { filter: drop-shadow(0 1px 1px rgb(0 0 0 / 0.05)); }
.score-control button:hover:not(:disabled) { background-color: var(--brand-2); color: white; border-color: var(--brand-2); }
.score-input-mobile { width: 3rem; }
.team-logo { background-color: white; border-radius: 0.25rem; filter: drop-shadow(0 1px 1px rgb(0 0 0 / 0.05)); }
</style>
