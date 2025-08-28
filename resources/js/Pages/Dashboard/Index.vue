<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
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

// Auto-save predictions when they change (debounce ~1.2s)
let autoSaveTimeout = null;
watch(() => form.predictions, () => {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        autoSavePredictions();
    }, 1200);
}, { deep: true });

function autoSavePredictions() {
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
            toastMsg.value = 'پیش‌بینی ذخیره شد';
            toastType.value = 'success';
            showToast.value = true;
            setTimeout(() => (showToast.value = false), 2000);
        },
        onError: () => {
            isAutoSaving.value = false;
            toastMsg.value = 'خطا در ذخیره';
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
const completedPredictions = computed(() => form.predictions.filter(p => p.home_score !== null && p.home_score !== '' && p.away_score !== null && p.away_score !== '').length);
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

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Stat Tiles -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">امتیاز شما</div>
                        <div class="text-2xl font-extrabold text-slate-900">{{ (userPoints ?? 0).toLocaleString('fa-IR') }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">رتبه شما</div>
                        <div class="text-2xl font-extrabold text-slate-900">#{{ (userRank ?? 0).toLocaleString('fa-IR') }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">پیش‌بینی‌های کامل</div>
                        <div class="text-2xl font-extrabold text-slate-900">{{ completedPredictions.toLocaleString('fa-IR') }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">پیشرفت هفته</div>
                        <div class="text-2xl font-extrabold text-slate-900">%{{ progressPct.toLocaleString('fa-IR') }}</div>
                    </div>
                </div>

                <!-- Error Display (system-level) -->
                <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <div class="text-red-800">
                            <div v-for="(error, key) in $page.props.errors" :key="key" class="text-sm">{{ Array.isArray(error) ? error[0] : error }}</div>
                        </div>
                    </div>
                </div>

                <!-- Progress Indicator for Predictions -->
                <div v-if="totalEditableFixtures > 0" class="bg-white rounded-lg border border-gray-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-sm">
                        <div class="flex items-center gap-4">
                            <span class="text-slate-600">پیشرفت پیش‌بینی:</span>
                            <span class="font-semibold text-slate-900">{{ completedPredictions }} از {{ totalEditableFixtures }}</span>
                            <div class="w-40 bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="progress-bar h-2 rounded-full transition-all duration-300" :style="{ width: (progressPct) + '%' }"></div>
                            </div>
                            <span class="text-slate-600">%{{ progressPct }}</span>
                        </div>
                        <div class="auto-save-indicator flex items-center gap-2 text-xs text-slate-500" aria-live="polite">
                            <svg v-if="isAutoSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <template v-else>
                                <svg class="h-4 w-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span v-if="lastSaved">ذخیره شد: {{ lastSaved }}</span>
                                <span v-else>پیش‌بینی‌های کامل خودکار ذخیره می‌شوند</span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Fixtures Section -->
                <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">پیش‌بینی شما از مسابقات آتی</h3>
                                <p class="text-sm text-slate-600 mt-1">مسابقات نزدیک که نیاز به پیش‌بینی دارند</p>
                            </div>
                            <Link :href="route('fixtures.index')" class="nav-btn inline-flex items-center px-4 py-2 text-sm font-medium text-[--brand-2] bg-slate-50 rounded-lg hover:bg-slate-100 transition-all duration-200">
                                مشاهده همه مسابقات
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>
                        </div>
                        <form @submit.prevent="autoSavePredictions" class="space-y-4">
                            <div v-for="(fixture, index) in upcomingFixtures" :key="fixture.id" class="fixture-card p-5 border border-gray-100 rounded-lg hover:shadow-md transition-all duration-200" :class="{ 'bg-gray-50 opacity-75 locked': fixture.is_locked, 'border-red-300': isPredictionIncomplete(index) }">
                                <!-- Time display -->
                                <div class="text-center mb-4">
                                    <span class="text-xs text-slate-600">{{ formatMatchDateTime(
                                        fixture.match_datetime) }}</span>
                                </div>
                                <!-- Desktop Layout -->
                                <div class="hidden sm:grid sm:grid-cols-5 sm:items-center sm:gap-4">
                                    <div class="col-span-2 flex items-center justify-end gap-3">
                                        <div class="text-right">
                                            <Link 
                                                :href="route('fixtures.show', fixture.id)" 
                                                class="team-name text-end text-slate-900 font-medium hover:text-blue-600 transition-colors duration-200"
                                            >
                                                {{ translateTeamName(fixture.home_team?.name) }}
                                            </Link>
                                        </div>
                                        <div class="flex-shrink-0">

                                            <img 
                                                :src="`/assets/team-logos/${fixture.home_team.name}.png`"
                                                :alt="fixture.home_team?.name"
                                                class="team-logo w-10 h-10 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-center gap-2">
                                        <div class="score-control flex flex-col items-center">
                                            <button type="button" class="w-14 py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(index, 'home_score')">+</button>
                                            <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input w-14 h-10 text-center appearance-none border-x border-slate-200 focus:z-10" v-model.number="form.predictions[index].home_score" />
                                            <button type="button" class="w-14 py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(index, 'home_score')">-</button>
                                        </div>
                                        <span class="text-slate-500">-</span>
                                        <div class="score-control flex flex-col items-center">
                                            <button type="button" class="w-14 py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(index, 'away_score')">+</button>
                                            <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input w-14 h-10 text-center appearance-none border-x border-slate-200 focus:z-10" v-model.number="form.predictions[index].away_score" />
                                            <button type="button" class="w-14 py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(index, 'away_score')">-</button>
                                        </div>
                                    </div>
                                    <div class="col-span-2 flex items-center justify-start gap-3">
                                        <div class="flex-shrink-0">
                                            <img 
                                                :src="`/assets/team-logos/${fixture.away_team.name}.png`"
                                                :alt="fixture.away_team?.name"
                                                class="team-logo w-10 h-10 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                        </div>
                                        <div class="text-left">
                                            <Link 
                                                :href="route('fixtures.show', fixture.id)" 
                                                class="team-name text-start text-slate-900 font-medium hover:text-blue-600 transition-colors duration-200"
                                            >
                                                {{ translateTeamName(fixture.away_team?.name) }}
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mobile Layout -->
                                <div class="sm:hidden space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <img 
                                                :src="`/assets/team-logos/${fixture.home_team?.name}.png`"
                                                :alt="fixture.home_team?.name"
                                                class="team-logo w-8 h-8 object-contain flex-shrink-0"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                            <Link 
                                                :href="route('fixtures.show', fixture.id)" 
                                                class="team-name text-slate-900 text-sm font-medium hover:text-blue-600 transition-colors duration-200"
                                            >
                                                {{ translateTeamName(fixture.home_team?.name) }}
                                            </Link>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Link 
                                                :href="route('fixtures.show', fixture.id)" 
                                                class="team-name text-slate-900 text-sm font-medium hover:text-blue-600 transition-colors duration-200"
                                            >
                                                {{ translateTeamName(fixture.away_team?.name) }}
                                            </Link>
                                            <img 
                                                :src="`/assets/team-logos/${fixture.away_team?.name}.png`"
                                                :alt="fixture.away_team?.name"
                                                class="team-logo w-8 h-8 object-contain flex-shrink-0"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="score-control flex flex-col items-center">
                                            <button type="button" class="w-full py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(index, 'home_score')">+</button>
                                            <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input score-input-mobile text-center appearance-none border-x border-slate-200 focus:z-10" v-model.number="form.predictions[index].home_score" />
                                            <button type="button" class="w-full py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(index, 'home_score')">-</button>
                                        </div>
                                        <span class="text-slate-500">-</span>
                                        <div class="score-control flex flex-col items-center">
                                            <button type="button" class="w-full py-1 rounded-t-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-b-0" :disabled="fixture.is_locked" @click="inc(index, 'away_score')">+</button>
                                            <input :disabled="fixture.is_locked" type="number" min="0" max="20" class="score-input score-input-mobile text-center appearance-none border-x border-slate-200 focus:z-10" v-model.number="form.predictions[index].away_score" />
                                            <button type="button" class="w-full py-1 rounded-b-md bg-slate-100 hover:bg-slate-200 text-sm border border-slate-200 border-t-0" :disabled="fixture.is_locked" @click="dec(index, 'away_score')">-</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Status and Action -->
                                <div class="mt-6 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span v-if="fixture.is_locked" class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs bg-slate-200 text-slate-700 border border-slate-300">قفل شده</span>
                                        <span v-else class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">{{ getTimeUntilLock(fixture) }}</span>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ formatDateTime(fixture.match_datetime) }}</div>
                                </div>
                            </div>
                        </form>
                        <div v-if="upcomingFixtures.length === 0" class="text-center py-8 text-slate-500">هیچ مسابقه آتی پیدا نشد.</div>
                    </div>
                </div>

                <!-- Two Column Layout for Predictions and Leaderboard -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Your Recent Predictions -->
                    <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">پیش‌بینی‌های اخیر شما</h3>
                                    <p class="text-sm text-slate-600 mt-1">مروری سریع بر آخرین فعالیت شما</p>
                                </div>
                                <Link :href="route('my-predictions.index')" class="nav-btn inline-flex items-center px-3 py-2 text-sm font-medium text-[--brand-2] bg-slate-50 rounded-lg hover:bg-slate-100 transition-all duration-200">همه پیش‌بینی‌ها</Link>
                            </div>
                            <div class="space-y-3">
                                <div v-for="p in recentPredictions" :key="p.id" class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg border border-gray-100 hover:shadow-sm transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-2">
                                            <img 
                                                :src="`/assets/team-logos/${p.fixture?.home_team?.name}.png`"
                                                :alt="p.fixture?.home_team?.name"
                                                class="team-logo w-6 h-6 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                            <span class="text-slate-700 text-sm">{{ translateTeamName(p.fixture?.home_team?.name) }}</span>
                                            <span class="text-slate-400">vs</span>
                                            <span class="text-slate-700 text-sm">{{ translateTeamName(p.fixture?.away_team?.name) }}</span>
                                            <img 
                                                :src="`/assets/team-logos/${p.fixture?.away_team?.name}.png`"
                                                :alt="p.fixture?.away_team?.name"
                                                class="team-logo w-6 h-6 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                                loading="lazy"
                                            />
                                        </div>
                                        <div class="text-xs text-slate-500">{{ formatDateTime(p.fixture?.match_datetime) }}</div>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="px-2 py-1 rounded bg-white border text-slate-700">{{ p.home_score_predicted }}-{{ p.away_score_predicted }}</span>
                                        <span v-if="p.points_awarded !== null" class="px-2 py-1 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">+{{ p.points_awarded }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="recentPredictions.length === 0" class="text-center py-8 text-slate-500"><div class="text-slate-400 text-lg mb-2">⚽</div><p class="text-sm">هنوز پیش‌بینی‌ای نداشته‌اید. شروع کنید!</p></div>
                        </div>
                    </div>

                    <!-- Leaderboard Preview -->
                    <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">جدول امتیازات</h3>
                                    <p class="text-sm text-slate-600 mt-1">برترین کاربران این هفته</p>
                                </div>
                                <Link :href="route('leaderboard.index')" class="nav-btn inline-flex items-center px-3 py-2 text-sm font-medium text-[--brand-2] bg-slate-50 rounded-lg hover:bg-slate-100 transition-all duration-200">مشاهده کامل</Link>
                            </div>
                            <!-- User's Rank -->
                            <div class="mb-6 p-4 bg-gradient-to-r from-[var(--brand-1)]/5 via-[var(--brand-2)]/5 to-[var(--brand-3)]/5 rounded-lg border border-[color-mix(in_oklab,white,rgba(9,14,80,0.2))]">
                                <div class="text-center">
                                    <div class="text-xs text-slate-600 mb-1">موقعیت شما</div>
                                    <div class="text-xl font-extrabold text-slate-900">رتبه #{{ (userRank ?? 0).toLocaleString('fa-IR') }} • {{ (userPoints ?? 0).toLocaleString('fa-IR') }} امتیاز</div>
                                </div>
                            </div>
                            <!-- Top 5 -->
                            <div class="space-y-2">
                                <div v-for="(user, i) in topLeaderboard" :key="user.id" :class="['flex items-center justify-between px-4 py-3 rounded-lg border', user.id === $page.props.auth.user.id ? 'bg-[var(--brand-2)]/10 border-[var(--brand-2)]/30' : 'bg-gray-50 hover:bg-gray-100 border-gray-100']">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 inline-flex items-center justify-center rounded-full text-xs" :class="i===0 ? 'bg-amber-100 text-amber-700' : i===1 ? 'bg-slate-100 text-slate-700' : i===2 ? 'bg-orange-100 text-orange-700' : 'bg-white border'">{{ (i+1).toLocaleString('fa-IR') }}</span>
                                        <span class="text-slate-800 text-sm">{{ user.name }}</span>
                                    </div>
                                    <div class="text-slate-700 text-sm">{{ (user.total_points ?? 0).toLocaleString('fa-IR') }} امتیاز</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- League Table (lazy-loaded) -->
                <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="">
                        <Suspense>
                            <LeagueTable />
                            <template #fallback>
                                <div class="text-center text-slate-500 py-8">در حال بارگذاری جدول...</div>
                            </template>
                        </Suspense>
                    </div>
                </div>
            </div>
            <Toast :show="showToast" :message="toastMsg" :type="toastType" position="bottom-right" @close="showToast=false" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Hide browser spinner buttons */
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

.score-input:focus { outline: none; border-color: var(--brand-2); box-shadow: 0 0 0 1px var(--brand-2); }

.score-control { filter: drop-shadow(0 1px 1px rgb(0 0 0 / 0.05)); }

.score-control button:hover:not(:disabled) {
    background-color: var(--brand-2);
    color: white;
    border-color: var(--brand-2);
}

.score-input-mobile {
    width: 3rem;
}

.team-logo {
    background-color: white;
    border-radius: 0.25rem;
    filter: drop-shadow(0 1px 1px rgb(0 0 0 / 0.05));
}
</style>
