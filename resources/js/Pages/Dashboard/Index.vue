<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import ActivityFeed from '@/Components/ActivityFeed/ActivityFeed.vue';
import GamificationWidget from '@/Components/GamificationWidget.vue';
import SimpleLeagueTable from '@/Components/SimpleLeagueTable.vue';
import { useTranslations } from '@/composables/useTranslations.js';
import { computed, ref, watch, defineAsyncComponent } from 'vue';

const { translateTeamName, t } = useTranslations();

// Lazy-load LeagueTable (same component used on Welcome page)
const LeagueTable = defineAsyncComponent(() => import('@/Components/LeagueTable.vue'));

const props = defineProps({
    upcomingFixtures: Array,
    recentPredictions: Array,
    userRank: Number,
    userPoints: Number,
    topLeaderboard: Array,
    totalUsers: Number,
    activityFeed: Array,
    userStats: Object,
    leagueTable: Array,
});

// Auto-save functionality
const isAutoSaving = ref(false);
const lastSaved = ref(null);
const showToast = ref(false);
const toastMsg = ref('');
const toastType = ref('success');

// useForm will wrap our prediction data, making submission easy
const form = useForm({
    predictions: props.upcomingFixtures.map((f) => ({
        fixture_id: f.id,
        home_score: f.prediction?.home_score_predicted ?? null,
        away_score: f.prediction?.away_score_predicted ?? null,
    })),
});

// Auto-save predictions when they change (debounce ~2s)
let autoSaveTimeout = null;
watch(() => form.predictions, () => {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        autoSavePredictions();
    }, 2000);
}, { deep: true });

function autoSavePredictions() {
    // Only auto-save if there are complete predictions (both home and away scores filled)
    const completeValidPredictions = form.predictions.filter(p => 
        p.home_score !== null && p.home_score !== '' && 
        p.away_score !== null && p.away_score !== ''
    );
    
    if (completeValidPredictions.length === 0) return;

    isAutoSaving.value = true;
    form.post(route('predictions.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            lastSaved.value = new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
            isAutoSaving.value = false;
            toastMsg.value = 'ذخیره خودکار شد';
            toastType.value = 'success';
            showToast.value = true;
            setTimeout(() => (showToast.value = false), 2000);
        },
        onError: () => {
            isAutoSaving.value = false;
            toastMsg.value = 'خطا در ذخیره‌سازی خودکار';
            toastType.value = 'error';
            showToast.value = true;
            setTimeout(() => (showToast.value = false), 2500);
        },
    });
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('fa-IR', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

function formatMatchDateTime(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('fa-IR', options);
}

function getTimeUntilLock(fixture) {
    if (fixture.is_locked) return 'قفل شده';
    if (fixture.time_until_prediction_locks > 24) {
        return `${Math.floor(fixture.time_until_prediction_locks / 24)} روز ${Math.floor(fixture.time_until_prediction_locks % 24)} ساعت باقی‌مانده`;
    } else if (fixture.time_until_prediction_locks > 1) {
        return `${Math.floor(fixture.time_until_prediction_locks)} ساعت باقی‌مانده`;
    } else if (fixture.time_until_prediction_locks > 0) {
        return `${Math.floor(fixture.time_until_prediction_locks * 60)} دقیقه باقی‌مانده`;
    }
    return 'به زودی قفل می‌شود';
}

function isPredictionIncomplete(index) {
    const prediction = form.predictions[index];
    const hasHomeScore = prediction.home_score !== null && prediction.home_score !== '';
    const hasAwayScore = prediction.away_score !== null && prediction.away_score !== '';
    return (hasHomeScore && !hasAwayScore) || (!hasHomeScore && hasAwayScore);
}

// Statistics
const completedPredictions = computed(() => {
    return props.upcomingFixtures
        .filter((fixture, index) => !fixture.is_locked) // Only count unlocked fixtures
        .filter((fixture, index) => {
            const prediction = form.predictions[index];
            return prediction && 
                   prediction.home_score !== null && 
                   prediction.home_score !== '' && 
                   prediction.away_score !== null && 
                   prediction.away_score !== '';
        }).length;
});
const totalEditableFixtures = computed(() => props.upcomingFixtures.filter(f => !f.is_locked).length);
const progressPct = computed(() => totalEditableFixtures.value > 0 ? Math.round((completedPredictions.value / totalEditableFixtures.value) * 100) : 0);

function inc(index, field) { const v = Number(form.predictions[index][field] ?? 0); form.predictions[index][field] = Math.max(0, v + 1); }
function dec(index, field) { const v = Number(form.predictions[index][field] ?? 0); form.predictions[index][field] = Math.max(0, v - 1); }
</script>

<template>
    <Head title="داشبورد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-slate-800">داشبورد</h2>
                <div class="hidden sm:flex items-center gap-3">
                    <span class="badge-brand">به‌روزرسانی زنده</span>
                </div>
            </div>
        </template>

        <div class="py-4 lg:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Mobile-First Layout -->
                <div class="space-y-6 lg:space-y-8">
                    
                    <!-- Quick Stats Bar - Mobile Optimized -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                        <div class="stat-tile bg-white rounded-xl border border-gray-100 p-4 lg:p-5 shadow-sm hover:shadow-md transition-shadow">
                            <div class="text-xs text-slate-500 mb-1 lg:mb-2">امتیاز شما</div>
                            <div class="text-xl lg:text-2xl font-extrabold text-slate-900">{{ (userPoints ?? 0).toLocaleString('fa-IR') }}</div>
                        </div>
                        <div class="stat-tile bg-white rounded-xl border border-gray-100 p-4 lg:p-5 shadow-sm hover:shadow-md transition-shadow">
                            <div class="text-xs text-slate-500 mb-1 lg:mb-2">رتبه شما</div>
                            <div class="text-xl lg:text-2xl font-extrabold text-slate-900">#{{ (userRank ?? 0).toLocaleString('fa-IR') }}</div>
                    </div>
                        <div class="stat-tile bg-white rounded-xl border border-gray-100 p-4 lg:p-5 shadow-sm hover:shadow-md transition-shadow">
                            <div class="text-xs text-slate-500 mb-1 lg:mb-2">پیش‌بینی‌های کامل</div>
                            <div class="text-xl lg:text-2xl font-extrabold text-slate-900">{{ completedPredictions.toLocaleString('fa-IR') }}</div>
                        </div>
                        <div class="stat-tile bg-white rounded-xl border border-gray-100 p-4 lg:p-5 shadow-sm hover:shadow-md transition-shadow">
                            <div class="text-xs text-slate-500 mb-1 lg:mb-2">پیشرفت هفته</div>
                            <div class="text-xl lg:text-2xl font-extrabold text-slate-900">%{{ progressPct.toLocaleString('fa-IR') }}</div>
                        </div>
                    </div>

                    <!-- Main Content Grid - Two Column Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
                        
                        <!-- Main Content -->
                        <div class="lg:col-span-8 space-y-6">
                            <!-- Mobile Gamification Widget -->
                            <div class="lg:hidden">
                                <GamificationWidget :user-stats="userStats" />
                            </div>

                            <!-- Upcoming Fixtures Prediction Section -->
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-900">پیش‌بینی مسابقات آتی</h3>
                                        <Link :href="route('fixtures.index')" class="text-xs text-blue-600 hover:text-blue-700">
                                            مشاهده همه
                            </Link>
                        </div>
                                </div>
                                
                                <div class="p-4">
                                    <!-- Progress Indicator -->
                                    <div v-if="totalEditableFixtures > 0" class="mb-4 bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center gap-4">
                                                <span class="text-gray-600">پیشرفت پیش‌بینی:</span>
                                                <span class="font-semibold text-gray-900">{{ completedPredictions }} از {{ totalEditableFixtures }}</span>
                                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                                    <div class="progress-bar h-2 rounded-full transition-all duration-300" :style="{ width: (progressPct) + '%' }"></div>
                                                </div>
                                                <span class="text-gray-600">%{{ progressPct }}</span>
                                            </div>
                                            <div class="auto-save-indicator flex items-center gap-2 text-xs text-gray-500">
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
                                    
                                    <form @submit.prevent="autoSavePredictions" class="space-y-4">
                                        <div v-for="(fixture, index) in upcomingFixtures" :key="fixture.id" 
                                             class="border border-gray-200 rounded p-4 hover:bg-gray-50 transition-colors"
                                             :class="{ 'opacity-50': fixture.is_locked, 'border-red-300': isPredictionIncomplete(index) }">
                                            
                                            <!-- Match Info -->
                                            <div class="text-center mb-4">
                                                <div class="text-xs text-gray-500 mb-1">{{ formatMatchDateTime(fixture.match_datetime) }}</div>
                                                <div class="text-xs text-gray-400">{{ getTimeUntilLock(fixture) }}</div>
                                        </div>

                                            <!-- Teams and Prediction -->
                                            <div class="flex items-center justify-between">
                                                <!-- Home Team -->
                                                <div class="flex items-center gap-3 flex-1">
                                            <img 
                                                        :src="`/assets/team-logos/${fixture.home_team?.name}.png`"
                                                :alt="fixture.home_team?.name"
                                                        class="w-10 h-10 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ translateTeamName(fixture.home_team?.name) }}
                                                    </span>
                                        </div>
                                                
                                                <!-- Score Controls -->
                                                <div class="flex items-center justify-center gap-2 mx-4" :class="{ 'border-red-300': isPredictionIncomplete(index) }">
                                        <div class="score-control flex flex-col items-center">
                                            <button type="button" class="w-14 py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(index, 'home_score')">+</button>
                                                        <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input w-14 h-10 text-center appearance-none border-x border-slate-200 focus:z-10" :class="{ 'bg-red-50': isPredictionIncomplete(index) }" v-model.number="form.predictions[index].home_score" />
                                            <button type="button" class="w-14 py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(index, 'home_score')">-</button>
                                        </div>
                                        <span class="text-slate-500">-</span>
                                        <div class="score-control flex flex-col items-center">
                                            <button type="button" class="w-14 py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(index, 'away_score')">+</button>
                                                        <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input w-14 h-10 text-center appearance-none border-x border-slate-200 focus:z-10" :class="{ 'bg-red-50': isPredictionIncomplete(index) }" v-model.number="form.predictions[index].away_score" />
                                            <button type="button" class="w-14 py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(index, 'away_score')">-</button>
                                        </div>
                                    </div>
                                                
                                                <!-- Away Team -->
                                                <div class="flex items-center gap-3 flex-1 justify-end">
                                                    <span class="text-sm font-medium text-gray-900">
                                                {{ translateTeamName(fixture.away_team?.name) }}
                                                    </span>
                                            <img 
                                                :src="`/assets/team-logos/${fixture.away_team?.name}.png`"
                                                :alt="fixture.away_team?.name"
                                                        class="w-10 h-10 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                        </div>
                                    </div>
                                            
                                            <!-- Validation message for incomplete predictions -->
                                            <div v-if="isPredictionIncomplete(index)" class="text-center text-xs text-red-600 mt-2">
                                                لطفاً هر دو نتیجه را وارد کنید
                                            </div>
                                        </div>
                                        
                                        <div v-if="upcomingFixtures.length === 0" class="text-center py-8 text-gray-500">
                                            <div class="text-2xl mb-2">⚽</div>
                                            <p class="text-sm">هیچ مسابقه آتی پیدا نشد</p>
                                        </div>
                                    </form>
                                </div>
                                    </div>

                            <!-- Activity Feed -->
                            <ActivityFeed 
                                :activities="activityFeed" 
                                title="فعالیت‌های اخیر" 
                                max-height="max-h-96"
                            />
                                </div>

                        <!-- Right Sidebar -->
                        <div class="lg:col-span-4 space-y-6">
                            <!-- Desktop Gamification Widget -->
                            <div class="hidden lg:block">
                                <GamificationWidget :user-stats="userStats" />
                            </div>
                            
                            <!-- Simple League Table -->
                            <SimpleLeagueTable :teams="leagueTable" />
                            
                            <!-- User's Rank -->
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <h3 class="text-sm font-semibold text-gray-900">موقعیت شما</h3>
                                </div>
                                <div class="p-4 text-center">
                                    <div class="text-xs text-gray-600 mb-1">رتبه فعلی</div>
                                    <div class="text-lg font-semibold text-gray-900">#{{ (userRank ?? 0).toLocaleString('fa-IR') }}</div>
                                    <div class="text-sm text-gray-600 mt-1">{{ (userPoints ?? 0).toLocaleString('fa-IR') }} امتیاز</div>
                    </div>
                </div>

                            <!-- Recent Predictions -->
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-900">پیش‌بینی‌های اخیر</h3>
                                        <Link :href="route('my-predictions.index')" class="text-xs text-blue-600 hover:text-blue-700">
                                            مشاهده همه
                                        </Link>
                                    </div>
                                </div>
                                
                                <div class="p-4 space-y-3">
                                    <div v-for="p in recentPredictions.slice(0, 4)" :key="p.id" 
                                         class="flex items-center justify-between py-2 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-700">{{ translateTeamName(p.fixture?.home_team?.name) }}</span>
                                            <span class="text-gray-400">vs</span>
                                            <span class="text-gray-700">{{ translateTeamName(p.fixture?.away_team?.name) }}</span>
                            </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded text-xs">{{ p.home_score_predicted }}-{{ p.away_score_predicted }}</span>
                                            <span v-if="p.points_awarded !== null" 
                                                  class="text-xs font-medium"
                                                  :class="p.points_awarded > 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ p.points_awarded > 0 ? '+' : '' }}{{ p.points_awarded }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="recentPredictions.length === 0" class="text-center py-6 text-gray-500">
                                        <div class="text-2xl mb-2">⚽</div>
                                        <p class="text-sm">هنوز پیش‌بینی‌ای نداشته‌اید</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Toast for notifications -->
            <Toast :show="showToast" :message="toastMsg" :type="toastType" position="bottom-right" @close="showToast=false" />
            
            <!-- Error Display (system-level) -->
            <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" 
                 class="fixed top-4 right-4 z-50 p-4 bg-red-50 border border-red-200 rounded-lg shadow-lg max-w-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-red-800">
                        <div v-for="(error, key) in $page.props.errors" :key="key" class="text-sm">{{ Array.isArray(error) ? error[0] : error }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Clean, minimal design */
.stat-tile {
    transition: all 0.2s ease;
}

.stat-tile:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Mobile-first responsive design */
@media (max-width: 640px) {
    .stat-tile {
        padding: 1rem;
        border-radius: 0.5rem;
    }
    
    .grid {
        gap: 0.75rem;
    }
}

/* Clean scrollbar */
::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Score controls styling (matching Fixtures/Index.vue) */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    appearance: textfield;
    -moz-appearance: textfield;
}

.score-input {
    border-top: 0;
    border-bottom: 0;
    border-radius: 0;
    height: 2.5rem;
}

.score-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6;
}

.score-control {
    filter: drop-shadow(0 1px 1px rgb(0 0 0 / 0.05));
}

.score-control button:hover:not(:disabled) {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.progress-bar {
    background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
}
</style>