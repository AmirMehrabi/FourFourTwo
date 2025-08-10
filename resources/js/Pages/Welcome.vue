<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted, watch, nextTick, defineAsyncComponent } from 'vue';
import { useTranslations } from '@/composables/useTranslations.js';

// Code-split heavier sections
const LeaderboardDemo = defineAsyncComponent(() => import('@/Components/LeaderboardDemo.vue'));
const GamificationExplainer = defineAsyncComponent(() => import('@/Components/GamificationExplainer.vue'));

const { translateTeamName, t } = useTranslations();

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    stats: Object,
    topPredictors: Array,
    predictionOfTheDay: Object,
    recentPredictions: Array,
    trendingMatches: Array,
    weeklyInsight: String,
    platformInsights: Array,
    accuracyBreakdown: Object,
    weeklyChallenge: Object,
    liveStats: Object,
    confidenceMetrics: Object,
});

// State
const activeTab = ref('login');
const showAuthModal = ref(false);
// Focused hero (no rotation)
const heroContent = {
    headline: 'پیش‌بینی فوتبال با قدرت داده و جامعه',
    sub: 'پیش‌بینی کنید، عملکرد خود را بسنجید، پیشرفت را دنبال کنید.',
    stats: {
        accuracy: `${props.stats?.accuracy_rate || 0}% دقت اخیر`,
        users: `${props.stats?.total_users?.toLocaleString() || '0'} کاربر`,
        predictions: `${props.stats?.weekly_predictions || 0} پیش‌بینی این هفته`
    }
};

// Modal / auth enhancements
const firstInputRef = ref(null);
const passwordVisible = ref(false);
const registerPasswordVisible = ref(false);
const passwordStrength = ref(null); // {score,label}

// (Moved demo logic into async component)

// Sticky signup (mobile)
const showStickySignup = ref(false);
if (typeof window !== 'undefined') {
    window.addEventListener('scroll', () => {
        const trigger = window.innerHeight * 1.5;
        showStickySignup.value = window.scrollY > trigger;
    });
}

// (Removed chart datasets & options – methodology section removed)

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submitLogin = () => {
    // Add explicit CSRF token for Safari
    loginForm.transform(data => ({
        ...data,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }));
    loginForm.post('/login');
};

const submitRegister = () => {
    // Add explicit CSRF token for Safari
    registerForm.transform(data => ({
        ...data,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }));
    registerForm.post('/register');
};

const formatTimeAgo = (date) => {
    const now = new Date();
    const past = new Date(date);
    const diffMs = now - past;
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffMins < 60) return `${diffMins} دقیقه پیش`;
    if (diffHours < 24) return `${diffHours} ساعت پیش`;
    return `${diffDays} روز پیش`;
};

const formatFarsiDate = (date) => {
    const matchDate = new Date(date);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    
    if (matchDate.toDateString() === today.toDateString()) {
        return 'امروز ' + matchDate.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
    } else if (matchDate.toDateString() === tomorrow.toDateString()) {
        return 'فردا ' + matchDate.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
    } else if (matchDate.toDateString() === yesterday.toDateString()) {
        return 'دیروز ' + matchDate.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
    } else {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return matchDate.toLocaleDateString('fa-IR', options);
    }
};

const getConfidenceColor = (confidence) => {
    if (confidence >= 80) return 'bg-green-500';
    if (confidence >= 60) return 'bg-yellow-500';
    return 'bg-red-500';
};

// (Slider removed) touch handlers no longer needed

const openAuthModal = (tab = 'login') => {
    activeTab.value = tab;
    showAuthModal.value = true;
    nextTick(()=>{ if (firstInputRef.value) firstInputRef.value.focus(); });
};

const closeAuthModal = () => {
    showAuthModal.value = false;
};

const handleEscClose = (e) => { if (e.key === 'Escape' && showAuthModal.value) closeAuthModal(); };
const evaluatePassword = (pwd) => {
    if (!pwd) { passwordStrength.value = null; return; }
    let score = 0;
    if (pwd.length >= 8) score++;
    if (/[A-Zآ-ی]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9آ-ی]/.test(pwd)) score++;
    const labels = ['ضعیف','متوسط','خوب','قوی'];
    passwordStrength.value = { score, label: labels[Math.min(score-1, labels.length-1)] };
};
watch(() => registerForm.password, evaluatePassword);

onMounted(() => { document.addEventListener('keydown', handleEscClose); });
onUnmounted(() => { document.removeEventListener('keydown', handleEscClose); });
</script>

<template>
    <Head title="FourFourTwo - پیش‌بینی مبتنی بر داده">
        <link rel="preload" href="/css/fonts/Tanha/Tanha.woff2" as="font" type="font/woff2" crossorigin>
    </Head>
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 font-tanha" dir="rtl">
        <!-- Subtle grid background pattern -->
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(15,23,42,0.08) 1px, transparent 0); background-size: 20px 20px;"></div>
        </div>
        
        <!-- Header -->
        <header class="relative border-b border-slate-200 bg-white/90 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <img src="/assets/images/442-logo.png" class="h-16" alt="">
                        <div class="mr-3 flex items-center space-x-2 space-x-reverse">
                            <span class="text-xs text-brand-200 bg-brand-200/10 px-2 py-1 rounded-full font-600 border border-brand-200/30">BETA</span>
                        </div>
                    </div>
                    <nav class="hidden md:flex items-center gap-6 text-sm text-slate-700">
                        <a href="#features" class="hover:text-brand-300">ویژگی‌ها</a>
                        <a href="#live" class="hover:text-brand-300">زنده</a>
                    </nav>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div v-if="$page.props.auth.user">
                            <Link 
                                :href="route('dashboard')"
                                class="text-slate-900 hover:text-brand-300 transition-colors font-600 px-4 py-2 rounded-lg hover:bg-brand-50"
                            >
                                داشبورد
                            </Link>
                        </div>
                        <div v-else class="flex items-center space-x-3 space-x-reverse">
                            <button @click="openAuthModal('login')" class="text-slate-700 hover:text-brand-300 transition-colors font-600 px-4 py-2 rounded-lg hover:bg-brand-50">ورود</button>
                            <button @click="openAuthModal('register')" class="btn-brand-primary">همین حالا پیش‌بینی را شروع کنید</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Simplified Hero -->
        <section class="relative overflow-hidden text-white py-10 md:py-16">
            <div class="absolute inset-0 brand-hero-gradient"></div>
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative max-w-6xl mx-auto px-4 sm:px-6">
                <div class="pt-2 flex flex-wrap gap-2 text-[11px] text-white/85">
                    <span class="badge-translucent">⚡ زنده</span>
                    <span class="badge-translucent">🎯 {{ stats?.accuracy_rate || 0 }}% دقت</span>
                    <span class="badge-translucent">👥 {{ stats?.total_users?.toLocaleString() || '0' }} کاربر</span>
                </div>
                <div class="mt-8 md:mt-14 max-w-3xl">
                    <h1 class="text-3xl sm:text-5xl font-800 leading-[1.25] sm:leading-tight">{{ heroContent.headline }}</h1>
                    <p class="mt-4 text-base sm:text-xl text-white/85 font-300">{{ heroContent.sub }}</p>
                    <div class="mt-6 flex items-center gap-3 flex-wrap text-sm">
                        <span class="flex items-center gap-1 bg-white/10 px-3 py-2 rounded-full border border-white/15">دقت <strong class="font-700">{{ heroContent.stats.accuracy }}</strong></span>
                        <span class="flex items-center gap-1 bg-white/10 px-3 py-2 rounded-full border border-white/15">کاربران <strong class="font-700">{{ heroContent.stats.users }}</strong></span>
                        <span class="flex items-center gap-1 bg-white/10 px-3 py-2 rounded-full border border-white/15">این هفته <strong class="font-700">{{ heroContent.stats.predictions }}</strong></span>
                    </div>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 max-w-md">
                        <button @click="openAuthModal('register')" class="btn-brand-primary w-full sm:w-auto">همین حالا پیش‌بینی را شروع کنید</button>
                        <button @click="openAuthModal('login')" class="btn-brand-ghost w-full sm:w-auto">ورود</button>
                    </div>
                    <div class="mt-10 grid grid-cols-3 gap-3 sm:gap-4 max-w-lg text-center">
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <div class="text-[11px] text-white/60 mb-1">پیش‌بینی امروز</div>
                            <div class="text-xl font-800">{{ liveStats?.predictions_today || 0 }}</div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <div class="text-[11px] text-white/60 mb-1">کاربر فعال</div>
                            <div class="text-xl font-800">{{ liveStats?.active_users_today || 0 }}</div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <div class="text-[11px] text-white/60 mb-1">بازی ۲۴ساعت</div>
                            <div class="text-xl font-800">{{ liveStats?.upcoming_matches_24h || 0 }}</div>
                        </div>
                    </div>
                    <div class="mt-6 text-[11px] text-white/70 flex flex-wrap gap-3">
                        <span>داده محور</span><span class="opacity-40">•</span><span>رقابت دوستانه</span><span class="opacity-40">•</span><span>پیشرفت شخصی</span>
                    </div>
                </div>
            </div>
        </section>

    <LeaderboardDemo :top-predictors="topPredictors" :trending-matches="trendingMatches" />

    <GamificationExplainer />

        <!-- Sticky Mobile Signup Bar -->
        <div v-if="showStickySignup" class="fixed bottom-0 inset-x-0 sm:hidden z-40">
            <div class="mx-3 mb-3 bg-slate-900 text-white rounded-2xl px-5 py-4 flex items-center justify-between shadow-lg">
                <div class="text-sm font-600">به ما بپیوندید و پیش‌بینی کنید</div>
                <button @click="openAuthModal('register')" class="bg-white text-slate-900 text-xs font-700 px-4 py-2 rounded-xl">شروع</button>
            </div>
        </div>

    <!-- how-it-works removed -->

        <!-- Stats Dashboard Preview -->
        <section id="features" class="relative py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h3 class="text-4xl font-800 text-slate-900 mb-4">آمار پلتفرم</h3>
                    <p class="text-xl text-slate-600 font-300">نگاهی به عملکرد جامعه ما</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                    <div class="brand-card">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-xl">👥</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-slate-900">{{ stats?.total_users?.toLocaleString() || '0' }}</div>
                                <div class="text-sm text-slate-600 font-600">کاربر فعال</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">+{{ liveStats?.active_users_today || 0 }} امروز</div>
                    </div>

                    <div class="brand-card">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-xl">📊</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-slate-900">{{ stats?.total_predictions?.toLocaleString() || '0' }}</div>
                                <div class="text-sm text-slate-600 font-600">پیش‌بینی کل</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">+{{ liveStats?.predictions_today || 0 }} امروز</div>
                    </div>

                    <div class="brand-card">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-xl">🎯</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-slate-900">{{ stats?.accuracy_rate || 0 }}%</div>
                                <div class="text-sm text-slate-600 font-600">دقت پیش‌بینی</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">{{ stats?.exact_matches || 0 }} تطبیق دقیق</div>
                    </div>

                    <div class="brand-card">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-xl">⚽</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-slate-900">{{ liveStats?.upcoming_matches_24h || 0 }}</div>
                                <div class="text-sm text-slate-600 font-600">بازی ۲۴ ساعت آینده</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">{{ liveStats?.live_matches || 0 }} بازی زنده</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Trending Matches (if any) -->
        <section id="live" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <h3 class="text-2xl font-800 text-slate-900">بازی‌های داغ امروز</h3>
                        <p class="text-slate-600 text-sm">احتمالات برد/مساوی/باخت و زمان شروع</p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" v-if="trendingMatches && trendingMatches.length">
                    <div v-for="(m, i) in trendingMatches" :key="i" class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>🕒 {{ m?.match_datetime ? formatFarsiDate(m.match_datetime) : '—' }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ m?.league || '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center gap-2">
                                <img loading="lazy" :src="`/assets/team-logos/${m?.home_team}.png`" :alt="translateTeamName(m?.home_team)" class="w-6 h-6 object-contain" @error="$event.target.style.display='none'"/>
                                <div class="font-600 text-slate-900">{{ translateTeamName(m?.home_team) }}</div>
                            </div>
                            <div class="text-slate-400">vs</div>
                            <div class="flex items-center gap-2">
                                <div class="font-600 text-slate-900">{{ translateTeamName(m?.away_team) }}</div>
                                <img loading="lazy" :src="`/assets/team-logos/${m?.away_team}.png`" :alt="translateTeamName(m?.away_team)" class="w-6 h-6 object-contain" @error="$event.target.style.display='none'"/>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
                            <div class="px-2 py-2 rounded-lg bg-brand-50 text-slate-900">برد میزبان<br><span class="font-800 text-slate-900">{{ (m?.prob_home ?? 0) }}%</span></div>
                            <div class="px-2 py-2 rounded-lg bg-slate-100">مساوی<br><span class="font-800 text-slate-900">{{ (m?.prob_draw ?? 0) }}%</span></div>
                            <div class="px-2 py-2 rounded-lg bg-brand-50 text-slate-900">برد مهمان<br><span class="font-800 text-slate-900">{{ (m?.prob_away ?? 0) }}%</span></div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-slate-500">داده‌ای برای نمایش موجود نیست</div>
            </div>
        </section>

        <!-- Recent Community Predictions -->
        <section class="py-20 bg-slate-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h3 class="text-4xl font-800 text-slate-900 mb-4">جامعه چه پیش‌بینی می‌کند؟</h3>
                    <p class="text-xl text-slate-600 font-300">آخرین پیش‌بینی‌های کاربران</p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="p-6 brand-section-dark text-white">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-700">🔴 پیش‌بینی‌های زنده</h4>
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-sm">زنده</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-slate-100">
                        <div v-for="(prediction, index) in recentPredictions" :key="index" 
                             class="p-4 hover:bg-slate-50 transition-colors">
                            <!-- Desktop Layout -->
                            <div class="hidden sm:flex sm:items-center sm:justify-between gap-3">
                                <div class="flex items-center justify-start">
                                    <div class="text-sm text-slate-500 mr-4">
                                        <div class="flex items-center space-x-1 space-x-reverse">
                                            <span>🕒</span>
                                            <span>{{ formatTimeAgo(prediction.created_at) }}</span>
                                        </div>
                                    </div>

                                </div>
                                <div class="flex items-center space-x-3 space-x-reverse">
                                    <!-- Home Team -->
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <img loading="lazy"
                                            :src="`/assets/team-logos/${prediction.home_team}.png`"
                                            :alt="prediction.home_team"
                                            class="w-6 h-6 object-contain flex-shrink-0"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                        <span class="font-600 text-slate-900 text-base">{{ translateTeamName(prediction.home_team) }}</span>
                                    </div>
                                    
                                    <!-- Score -->
                                    <div class="flex items-center justify-center space-x-1 space-x-reverse mx-3">
                                        <span class="text-lg font-800 text-slate-900">{{ prediction.home_score }}</span>
                                        <span class="text-slate-400">-</span>
                                        <span class="text-lg font-800 text-slate-900">{{ prediction.away_score }}</span>
                                    </div>
                                    
                                    <!-- Away Team -->
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <span class="font-600 text-slate-900 text-base">{{ translateTeamName(prediction.away_team) }}</span>
                                        <img loading="lazy"
                                            :src="`/assets/team-logos/${prediction.away_team}.png`"
                                            :alt="prediction.away_team"
                                            class="w-6 h-6 object-contain flex-shrink-0"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                    </div>
                                </div>
                                <div class="text-sm text-slate-500 text-right">
                                    <div class="flex items-center space-x-1 space-x-reverse">
                                        <span>⚽</span>
                                        <span>{{ formatFarsiDate(prediction.match_datetime) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Layout -->
                            <div class="sm:hidden space-y-3">
                                <!-- First Row: Teams and Score -->
                                <div class="flex items-center justify-center space-x-3 space-x-reverse">
                                    <!-- Home Team -->
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <img loading="lazy"
                                            :src="`/assets/team-logos/${prediction.home_team}.png`"
                                            :alt="prediction.home_team"
                                            class="w-5 h-5 object-contain flex-shrink-0"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                        <span class="font-600 text-slate-900 text-sm truncate max-w-[70px]">{{ translateTeamName(prediction.home_team) }}</span>
                                    </div>
                                    
                                    <!-- Score -->
                                    <div class="flex items-center justify-center space-x-1 space-x-reverse mx-2">
                                        <span class="text-lg font-800 text-slate-900">{{ prediction.home_score }}</span>
                                        <span class="text-slate-400">-</span>
                                        <span class="text-lg font-800 text-slate-900">{{ prediction.away_score }}</span>
                                    </div>
                                    
                                    <!-- Away Team -->
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <span class="font-600 text-slate-900 text-sm truncate max-w-[70px]">{{ translateTeamName(prediction.away_team) }}</span>
                                        <img loading="lazy"
                                            :src="`/assets/team-logos/${prediction.away_team}.png`"
                                            :alt="prediction.away_team"
                                            class="w-5 h-5 object-contain flex-shrink-0"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                    </div>
                                </div>
                                
                                <!-- Second Row: Match Date and Prediction Time -->
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <div class="flex items-center space-x-1 space-x-reverse">
                                        <span>⚽</span>
                                        <span>{{ formatFarsiDate(prediction.match_datetime) }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1 space-x-reverse">
                                        <span>🕒</span>
                                        <span>{{ formatTimeAgo(prediction.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="recentPredictions && recentPredictions.length === 0" class="p-8 text-center">
                        <div class="text-4xl mb-4">⚽</div>
                        <div class="text-lg font-600 text-slate-700 mb-2">هنوز پیش‌بینی‌ای ثبت نشده</div>
                        <div class="text-sm text-slate-500">اولین نفری باشید که پیش‌بینی می‌کند</div>
                    </div>
                </div>
            </div>
        </section>

    <!-- methodology removed -->

        <!-- Footer -->
        <footer class=" bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="border-t border-slate-200 mt-2 pt-8 flex justify-between items-center">
                    <div class="hidden sm:block text-sm text-slate-500">
                        © ۲۰۲۵ FourFourTwo. تمامی حقوق محفوظ است.
                    </div>
                    <div class="flex items-center space-x-2 space-x-reverse text-sm text-slate-500">
                        <span>ساخته شده با</span>
                        <span class="text-brand-200">❤️</span>
                        <span>برای علاقه‌مندان فوتبال</span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Auth Modal -->
        <div v-if="showAuthModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             @click.self="closeAuthModal">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-800 text-slate-900">{{ activeTab === 'login' ? 'ورود به حساب کاربری' : 'ایجاد حساب کاربری جدید' }}</h2>
                        <div class="text-xs text-slate-500 mt-2">۱ از ۳: ایجاد حساب</div>
                    </div>
                    <button @click="closeAuthModal" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors" aria-label="بستن">
                        <span class="text-slate-400 text-xl">×</span>
                    </button>
                </div>

                <!-- Tab Navigation -->
                <div class="flex mb-8 bg-slate-100 rounded-lg p-1">
                    <button 
                        @click="activeTab = 'login'"
                        :class="[
                            'flex-1 py-3 px-4 text-base font-600 transition-all duration-200 rounded-md',
                            activeTab === 'login' 
                                ? 'text-white bg-slate-900 shadow-sm' 
                                : 'text-slate-600 hover:text-slate-900'
                        ]"
                    >
                        ورود
                    </button>
                    <button 
                        @click="activeTab = 'register'"
                        :class="[
                            'flex-1 py-3 px-4 text-base font-600 transition-all duration-200 rounded-md',
                            activeTab === 'register' 
                                ? 'text-white bg-slate-900 shadow-sm' 
                                : 'text-slate-600 hover:text-slate-900'
                        ]"
                    >
                        ثبت‌نام
                    </button>
                </div>
                
                <!-- Login Form -->
                <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="space-y-6" novalidate>
                    <div>
                        <label class="block text-sm font-600 text-slate-900 mb-2">ایمیل</label>
                        <input 
                            v-model="loginForm.email"
                            type="email" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900"
                            placeholder="example@email.com"
                        >
                        <div v-if="loginForm.errors.email" class="text-red-600 text-sm mt-1">{{ loginForm.errors.email }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-600 text-slate-900 mb-2">رمز عبور</label>
                        <div class="relative">
                            <input 
                                v-model="loginForm.password"
                                :type="passwordVisible ? 'text' : 'password'" 
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900 pr-12"
                                placeholder="••••••••"
                            >
                            <button type="button" @click="passwordVisible = !passwordVisible" class="absolute top-1/2 -translate-y-1/2 left-3 text-xs text-slate-500 hover:text-slate-700">{{ passwordVisible ? 'مخفی' : 'نمایش' }}</button>
                        </div>
                        <div v-if="loginForm.errors.password" class="text-red-600 text-sm mt-1">{{ loginForm.errors.password }}</div>
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            v-model="loginForm.remember"
                            type="checkbox" 
                            id="remember-modal"
                            class="w-4 h-4 text-brand-200 border-slate-300 rounded focus:ring-brand-200"
                        >
                        <label for="remember-modal" class="mr-2 text-sm text-slate-700">مرا به خاطر بسپار</label>
                    </div>
                    
                    <button 
                        type="submit"
                        :disabled="loginForm.processing"
                        class="w-full btn-brand-primary disabled:opacity-50"
                    >
                        <span v-if="loginForm.processing">در حال ورود...</span>
                        <span v-else>ورود</span>
                    </button>
                </form>
                
                <!-- Register Form -->
                <form v-if="activeTab === 'register'" @submit.prevent="submitRegister" class="space-y-6" novalidate>
                    <div>
                        <label class="block text-sm font-600 text-slate-900 mb-2">نام</label>
                        <input 
                            v-model="registerForm.name"
                            type="text" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900"
                            placeholder="نام شما"
                        >
                        <div v-if="registerForm.errors.name" class="text-red-600 text-sm mt-1">{{ registerForm.errors.name }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-600 text-slate-900 mb-2">ایمیل</label>
                        <input 
                            v-model="registerForm.email"
                            type="email" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900"
                            placeholder="example@email.com"
                        >
                        <div v-if="registerForm.errors.email" class="text-red-600 text-sm mt-1">{{ registerForm.errors.email }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-600 text-slate-900 mb-2">رمز عبور</label>
                        <div class="relative">
                            <input 
                                v-model="registerForm.password"
                                :type="registerPasswordVisible ? 'text' : 'password'" 
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900 pr-12"
                                placeholder="••••••••"
                            >
                            <button type="button" @click="registerPasswordVisible = !registerPasswordVisible" class="absolute top-1/2 -translate-y-1/2 left-3 text-xs text-slate-500 hover:text-slate-700">{{ registerPasswordVisible ? 'مخفی' : 'نمایش' }}</button>
                        </div>
                        <div class="mt-2 h-2 bg-slate-200 rounded overflow-hidden" v-if="passwordStrength">
                            <div :class="['h-full transition-all', passwordStrength.score <=1 ? 'bg-red-500' : passwordStrength.score===2 ? 'bg-yellow-500' : passwordStrength.score===3 ? 'bg-blue-500' : 'bg-green-500']" :style="{width: ((passwordStrength.score/4)*100)+'%'}"></div>
                        </div>
                        <div class="text-xs mt-1" v-if="passwordStrength" :class="passwordStrength.score<=1 ? 'text-red-600' : passwordStrength.score===2 ? 'text-yellow-600' : passwordStrength.score===3 ? 'text-blue-600' : 'text-green-600'">قدرت: {{ passwordStrength.label }}</div>
                        <div v-if="registerForm.errors.password" class="text-red-600 text-sm mt-1">{{ registerForm.errors.password }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-600 text-slate-900 mb-2">تکرار رمز عبور</label>
                        <input 
                            v-model="registerForm.password_confirmation"
                            type="password" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900"
                            placeholder="••••••••"
                        >
                    </div>
                    
                    <button 
                        type="submit"
                        :disabled="registerForm.processing"
                        class="w-full btn-brand-primary disabled:opacity-50"
                    >
                        <span v-if="registerForm.processing">در حال ثبت‌نام...</span>
                        <span v-else>ثبت‌نام</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style>
:root {
  --brand-1: #411085;
  --brand-2: #7b0681;
  --brand-3: #071a8a;
}

/* Brand helpers */
.text-brand-50 { color: rgba(255,255,255,0.95); }
.text-brand-100 { color: #e9ddff; }
.text-brand-200 { color: #7b0681; }
.bg-brand-50 { background: rgba(127, 92, 200, 0.08); }
.brand-section-dark { background: linear-gradient(120deg, var(--brand-1), var(--brand-2)); }
.brand-hero-gradient {
  background: radial-gradient(80% 80% at 20% 10%, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 60%),
              linear-gradient(120deg, var(--brand-1) 0%, var(--brand-2) 50%, var(--brand-3) 100%);
}
.badge-translucent {
  display: inline-flex; align-items: center; gap: .5rem;
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
  padding: .35rem .6rem; border-radius: 999px;
}
.btn-brand-primary {
  background-image: linear-gradient(90deg, var(--brand-1), var(--brand-2), var(--brand-3));
  color: #fff; font-weight: 700; padding: 1rem 2rem; border-radius: .75rem;
  transition: transform .15s ease, box-shadow .2s ease, opacity .2s ease;
  box-shadow: 0 10px 20px rgba(9, 14, 80, 0.2);
}
.btn-brand-primary:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(9, 14, 80, 0.3); }
.btn-brand-ghost {
  border: 2px solid rgba(255,255,255,0.35); color: #fff; padding: 1rem 2rem; border-radius: .75rem;
  transition: background-color .2s ease, border-color .2s ease; background-color: transparent;
}
.btn-brand-ghost:hover { background-color: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.55); }

.brand-card { background: white; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1.25rem; }
.card-step { background: white; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1.25rem; text-align: center; }
.step-icon { font-size: 1.5rem; }
.step-title { margin-top: .5rem; font-weight: 800; color: #0f172a; }
.step-desc { margin-top: .25rem; color: #64748b; font-size: .9rem; }

/* Existing slider thumbs (kept) */
.slider::-webkit-slider-thumb {
    appearance: none;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: var(--brand-2);
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.slider::-moz-range-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: var(--brand-2);
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>
