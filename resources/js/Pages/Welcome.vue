<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted, watch, nextTick, defineAsyncComponent } from 'vue';
import { useTranslations } from '@/composables/useTranslations.js';

// Code-split heavier sections
const LeaderboardDemo = defineAsyncComponent(() => import('@/Components/LeaderboardDemo.vue'));
const GamificationExplainer = defineAsyncComponent(() => import('@/Components/GamificationExplainer.vue'));
const LeagueTable = defineAsyncComponent(() => import('@/Components/LeagueTable.vue'));

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
    const meta = document.querySelector('meta[name="csrf-token"]');
    loginForm.transform(data => ({
        ...data,
        _token: meta ? meta.getAttribute('content') : undefined,
    })).post(route('login'), {
        onFinish: () => loginForm.reset('password'),
    });
};

const submitRegister = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    registerForm.transform(data => ({
        ...data,
        _token: meta ? meta.getAttribute('content') : undefined,
    })).post(route('register'), {
        onFinish: () => registerForm.reset('password', 'password_confirmation'),
    });
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
        
        <!-- Mobile-First Header -->
        <header class="relative border-b border-slate-200 bg-white/90 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4 md:py-6">
                    <!-- Logo - Always visible -->
                    <div class="flex items-center">
                        <img src="/assets/images/442-logo.png" class="h-12 md:h-16" alt="">
                        <div class="mr-2 md:mr-3 flex items-center">
                            <span class="text-xs text-brand-200 bg-brand-200/10 px-2 py-1 rounded-full font-600 border border-brand-200/30">BETA</span>
                        </div>
                    </div>
                    
                    <!-- Desktop Navigation -->
                    <!-- <nav class="hidden md:flex items-center gap-6 text-sm text-slate-700">
                        <a href="#features" class="hover:text-brand-300 transition-colors">ویژگی‌ها</a>
                        <a href="#live" class="hover:text-brand-300 transition-colors">زنده</a>
                    </nav> -->
                    
                    <!-- User Actions -->
                    <div class="flex items-center">
                        <div v-if="$page.props.auth.user" class="flex items-center">
                            <Link 
                                :href="route('dashboard')"
                                class="text-slate-900 hover:text-brand-300 transition-colors font-600 px-3 py-2 md:px-4 rounded-lg hover:bg-brand-50 text-sm md:text-base"
                            >
                                داشبورد
                            </Link>
                        </div>
                        <!-- Show login/register on desktop, hidden on mobile -->
                        <div v-else class="hidden md:flex items-center space-x-3 space-x-reverse">
                            <button @click="openAuthModal('login')" class="text-slate-700 hover:text-brand-300 transition-colors font-600 px-4 py-2 rounded-lg hover:bg-brand-50">
                                ورود
                            </button>
                            <button @click="openAuthModal('register')" class="btn-brand-primary text-sm">
                                شروع پیش‌بینی
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile-First Hero Section -->
        <section class="relative overflow-hidden text-white py-12 sm:py-16 md:py-20">
            <div class="absolute inset-0 brand-hero-gradient"></div>
            <div class="absolute inset-0 bg-black/10"></div>
            
            <div class="relative max-w-6xl mx-auto px-4 sm:px-6">
                <!-- Mobile-first badges -->
                <div class="flex flex-wrap gap-2 text-xs text-white/85 justify-center sm:justify-start">
                    <span class="badge-translucent">⚡ زنده</span>
                    <span class="badge-translucent">🎯 {{ stats?.accuracy_rate || 0 }}% دقت</span>
                    <span class="badge-translucent">👥 {{ stats?.total_users?.toLocaleString() || '0' }} کاربر</span>
                </div>
                
                <!-- Hero Content - Mobile Optimized -->
                <div class="mt-8 sm:mt-12 md:mt-14 text-center sm:text-right max-w-4xl sm:max-w-none">
                    <h1 class="text-2xl sm:text-4xl md:text-5xl font-800 leading-tight sm:leading-[1.25]">
                        {{ heroContent.headline }}
                    </h1>
                    <p class="mt-3 sm:mt-4 text-base sm:text-xl text-white/85 font-300 max-w-2xl mx-auto sm:mx-0">
                        {{ heroContent.sub }}
                    </p>
                    
                    <!-- Mobile-first stats -->
                    <div class="mt-4 sm:mt-6 flex flex-wrap items-center justify-center sm:justify-start gap-2 sm:gap-3 text-xs sm:text-sm">
                        <span class="flex items-center gap-1 bg-white/10 px-3 py-2 rounded-full border border-white/15">
                            <span class="hidden sm:inline">دقت</span>
                            <strong class="font-700">{{ heroContent.stats.accuracy }}</strong>
                        </span>
                        <span class="flex items-center gap-1 bg-white/10 px-3 py-2 rounded-full border border-white/15">
                            <span class="hidden sm:inline">کاربران</span>
                            <strong class="font-700">{{ heroContent.stats.users }}</strong>
                        </span>
                        <span class="flex items-center gap-1 bg-white/10 px-3 py-2 rounded-full border border-white/15">
                            <span class="hidden sm:inline">این هفته</span>
                            <strong class="font-700">{{ heroContent.stats.predictions }}</strong>
                        </span>
                    </div>
                    
                    <!-- CTA Buttons - Mobile Only, hidden on desktop -->
                    <div v-if="!$page.props.auth.user" class="mt-6 sm:mt-8 flex flex-col gap-3 max-w-sm mx-auto md:hidden">
                        <button @click="openAuthModal('register')" class="btn-brand-primary text-sm py-3 px-6 w-full order-1">
                            شروع پیش‌بینی
                        </button>
                        <button @click="openAuthModal('login')" class="btn-brand-ghost text-sm py-3 px-6 w-full order-2">
                            ورود به حساب
                        </button>
                    </div>
                    
                    <!-- Welcome message for logged in users -->
                    <div v-else class="mt-6 sm:mt-8">
                        <Link 
                            :href="route('dashboard')"
                            class="inline-flex items-center justify-center btn-brand-primary text-sm sm:text-base py-3 sm:py-4 px-6 sm:px-8"
                        >
                            رفتن به داشبورد
                        </Link>
                    </div>
                    
                    <!-- Live Stats Grid - Mobile Optimized -->
                    <div class="mt-8 sm:mt-10 grid grid-cols-3 gap-2 sm:gap-4 max-w-xs sm:max-w-lg mx-auto sm:mx-0 text-center">
                        <div class="bg-white/5 border border-white/10 rounded-lg sm:rounded-xl p-3 sm:p-4">
                            <div class="text-xs text-white/60 mb-1">پیش‌بینی امروز</div>
                            <div class="text-lg sm:text-xl font-800">{{ liveStats?.predictions_today || 0 }}</div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-lg sm:rounded-xl p-3 sm:p-4">
                            <div class="text-xs text-white/60 mb-1">کاربر فعال</div>
                            <div class="text-lg sm:text-xl font-800">{{ liveStats?.active_users_today || 0 }}</div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-lg sm:rounded-xl p-3 sm:p-4">
                            <div class="text-xs text-white/60 mb-1">بازی ۲۴ساعت</div>
                            <div class="text-lg sm:text-xl font-800">{{ liveStats?.upcoming_matches_24h || 0 }}</div>
                        </div>
                    </div>
                    
                    <!-- Features tagline - Mobile optimized -->
                    <div class="mt-4 sm:mt-6 text-xs text-white/70 flex flex-wrap justify-center sm:justify-start gap-2 sm:gap-3">
                        <span>داده محور</span><span class="opacity-40">•</span>
                        <span>رقابت دوستانه</span><span class="opacity-40">•</span>
                        <span>پیشرفت شخصی</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Value Proposition: Why FourFourTwo -->
        <section class="relative py-12 sm:py-16 md:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-12 md:mb-16">
                    <h3 class="text-2xl sm:text-3xl md:text-4xl font-800 text-slate-900 mb-2 sm:mb-4">⚡  چرا چهار چهار دو؟ </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
                    <div class="brand-card">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-brand-200/15 flex-shrink-0">
                                <span class="text-brand-200 text-lg sm:text-xl">⚽</span>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl font-800 text-slate-900">بازی‌های زنده و به‌روز</div>
                                <p class="mt-1 text-slate-600 text-sm sm:text-base">همیشه آخرین برنامه لیگ برتر رو اینجا داری.</p>
                            </div>
                        </div>
                    </div>
                    <div class="brand-card">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-brand-200/15 flex-shrink-0">
                                <span class="text-brand-200 text-lg sm:text-xl">🎯</span>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl font-800 text-slate-900">پیش‌بینی راحت و سریع</div>
                                <p class="mt-1 text-slate-600 text-sm sm:text-base">فقط با چند کلیک نتیجه رو ثبت کن.</p>
                            </div>
                        </div>
                    </div>
                    <div class="brand-card">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-brand-200/15 flex-shrink-0">
                                <span class="text-brand-200 text-lg sm:text-xl">🏆</span>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl font-800 text-slate-900">رقابت هیجان‌انگیز</div>
                                <p class="mt-1 text-slate-600 text-sm sm:text-base">توی جدول امتیازات بالا برو و قهرمان هفته بشو.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->

    <LeaderboardDemo :top-predictors="topPredictors" :trending-matches="trendingMatches" />

    <LeagueTable />

    <GamificationExplainer />

        <!-- Mobile Sticky Signup Bar - Only for unauthenticated users -->
        <div v-if="showStickySignup && !$page.props.auth.user" class="fixed bottom-0 inset-x-0 sm:hidden z-40">
            <div class="mx-2 mb-2 bg-slate-900 text-white rounded-xl px-4 py-3 flex items-center justify-between shadow-2xl border border-slate-700">
                <div class="flex-1 mr-3">
                    <div class="text-sm font-600">شروع پیش‌بینی</div>
                    <div class="text-xs text-white/70">به جامعه ما بپیوندید</div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="openAuthModal('login')" class="text-white/80 text-xs font-600 px-3 py-2 rounded-lg hover:bg-white/10 transition-colors">
                        ورود
                    </button>
                    <button @click="openAuthModal('register')" class="bg-white text-slate-900 text-xs font-700 px-4 py-2 rounded-lg hover:bg-white/90 transition-colors">
                        ثبت نام
                    </button>
                </div>
            </div>
        </div>

    <!-- how-it-works removed -->

        <!-- Mobile-First Stats Dashboard Preview -->
        <!-- <section id="features" class="relative py-12 sm:py-16 md:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-12 md:mb-16">
                    <h3 class="text-2xl sm:text-3xl md:text-4xl font-800 text-slate-900 mb-2 sm:mb-4">آمار پلتفرم</h3>
                    <p class="text-base sm:text-lg md:text-xl text-slate-600 font-300">نگاهی به عملکرد جامعه ما</p>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 md:gap-8 mb-8 sm:mb-12 md:mb-16">
                    <div class="brand-card col-span-2 lg:col-span-1">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-lg sm:text-xl">👥</span>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl sm:text-3xl font-800 text-slate-900">{{ stats?.total_users?.toLocaleString() || '0' }}</div>
                                <div class="text-xs sm:text-sm text-slate-600 font-600">کاربر فعال</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">+{{ liveStats?.active_users_today || 0 }} امروز</div>
                    </div>

                    <div class="brand-card col-span-2 lg:col-span-1">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-lg sm:text-xl">📊</span>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl sm:text-3xl font-800 text-slate-900">{{ stats?.total_predictions?.toLocaleString() || '0' }}</div>
                                <div class="text-xs sm:text-sm text-slate-600 font-600">پیش‌بینی کل</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">+{{ liveStats?.predictions_today || 0 }} امروز</div>
                    </div>

                    <div class="brand-card">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-lg sm:text-xl">🎯</span>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl sm:text-3xl font-800 text-slate-900">{{ stats?.accuracy_rate || 0 }}%</div>
                                <div class="text-xs sm:text-sm text-slate-600 font-600">دقت پیش‌بینی</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">{{ stats?.exact_matches || 0 }} تطبیق دقیق</div>
                    </div>

                    <div class="brand-card">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-brand-200/15">
                                <span class="text-brand-200 text-lg sm:text-xl">⚽</span>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl sm:text-3xl font-800 text-slate-900">{{ liveStats?.upcoming_matches_24h || 0 }}</div>
                                <div class="text-xs sm:text-sm text-slate-600 font-600">بازی ۲۴ ساعت آینده</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">{{ liveStats?.live_matches || 0 }} بازی زنده</div>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- Mobile-First Live Trending Matches -->
        <section id="live" class="py-12 sm:py-16 md:py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-6 sm:mb-8">
                    <div class="text-center sm:text-right">
                        <h3 class="text-xl sm:text-2xl font-800 text-slate-900">بازی‌های داغ امروز</h3>
                        <p class="text-slate-600 text-sm mt-1">احتمالات برد/مساوی/باخت و زمان شروع</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6" v-if="trendingMatches && trendingMatches.length">
                    <div v-for="(m, i) in trendingMatches" :key="i" class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 p-4 sm:p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between text-xs sm:text-sm text-slate-500 mb-3 sm:mb-4">
                            <span>🕒 {{ m?.match_datetime ? formatFarsiDate(m.match_datetime) : '—' }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ m?.league || '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <img loading="lazy" :src="`/assets/team-logos/${m?.home_team}.png`" :alt="translateTeamName(m?.home_team)" class="w-5 h-5 sm:w-6 sm:h-6 object-contain flex-shrink-0" @error="$event.target.style.display='none'"/>
                                <div class="font-600 text-slate-900 text-sm sm:text-base truncate">{{ translateTeamName(m?.home_team) }}</div>
                            </div>
                            <div class="text-slate-400 text-xs sm:text-sm mx-2">vs</div>
                            <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                                <div class="font-600 text-slate-900 text-sm sm:text-base truncate">{{ translateTeamName(m?.away_team) }}</div>
                                <img loading="lazy" :src="`/assets/team-logos/${m?.away_team}.png`" :alt="translateTeamName(m?.away_team)" class="w-5 h-5 sm:w-6 sm:h-6 object-contain flex-shrink-0" @error="$event.target.style.display='none'"/>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-1 sm:gap-2 mt-3 sm:mt-4 text-center text-xs">
                            <div class="px-2 py-1.5 sm:py-2 rounded-lg bg-brand-50 text-slate-900">
                                <div class="font-600">برد میزبان</div>
                                <div class="font-800 text-slate-900">{{ (m?.prob_home ?? 0) }}%</div>
                            </div>
                            <div class="px-2 py-1.5 sm:py-2 rounded-lg bg-slate-100">
                                <div class="font-600">مساوی</div>
                                <div class="font-800 text-slate-900">{{ (m?.prob_draw ?? 0) }}%</div>
                            </div>
                            <div class="px-2 py-1.5 sm:py-2 rounded-lg bg-brand-50 text-slate-900">
                                <div class="font-600">برد مهمان</div>
                                <div class="font-800 text-slate-900">{{ (m?.prob_away ?? 0) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-slate-500 py-8">
                    <div class="text-4xl mb-4">⚽</div>
                    <div>داده‌ای برای نمایش موجود نیست</div>
                </div>
            </div>
        </section>

        <!-- Mobile-First Recent Community Predictions -->
        <section class="py-12 sm:py-16 md:py-20 bg-slate-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-12">
                    <h3 class="text-2xl sm:text-3xl md:text-4xl font-800 text-slate-900 mb-2 sm:mb-4">جامعه چه پیش‌بینی می‌کند؟</h3>
                    <p class="text-base sm:text-lg md:text-xl text-slate-600 font-300">آخرین پیش‌بینی‌های کاربران</p>
                </div>

                <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="p-4 sm:p-6 brand-section-dark text-white">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base sm:text-lg font-700">🔴 پیش‌بینی‌های زنده</h4>
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-xs sm:text-sm">زنده</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-slate-100">
                        <div v-for="(prediction, index) in recentPredictions" :key="index" 
                             class="p-3 sm:p-4 hover:bg-slate-50 transition-colors">
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
                            
                            <!-- Mobile Layout - Improved -->
                            <div class="sm:hidden space-y-2">
                                <!-- Teams and Score Row -->
                                <div class="flex items-center justify-center space-x-2 space-x-reverse">
                                    <!-- Home Team -->
                                    <div class="flex items-center space-x-1 space-x-reverse flex-1 min-w-0">
                                        <img loading="lazy"
                                            :src="`/assets/team-logos/${prediction.home_team}.png`"
                                            :alt="prediction.home_team"
                                            class="w-4 h-4 object-contain flex-shrink-0"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                        <span class="font-600 text-slate-900 text-xs truncate">{{ translateTeamName(prediction.home_team) }}</span>
                                    </div>
                                    
                                    <!-- Score - More prominent -->
                                    <div class="flex items-center justify-center space-x-1 space-x-reverse bg-slate-50 rounded-lg px-3 py-1">
                                        <span class="text-base font-800 text-slate-900">{{ prediction.home_score }}</span>
                                        <span class="text-slate-400 text-sm">-</span>
                                        <span class="text-base font-800 text-slate-900">{{ prediction.away_score }}</span>
                                    </div>
                                    
                                    <!-- Away Team -->
                                    <div class="flex items-center space-x-1 space-x-reverse flex-1 min-w-0 justify-end">
                                        <span class="font-600 text-slate-900 text-xs truncate">{{ translateTeamName(prediction.away_team) }}</span>
                                        <img loading="lazy"
                                            :src="`/assets/team-logos/${prediction.away_team}.png`"
                                            :alt="prediction.away_team"
                                            class="w-4 h-4 object-contain flex-shrink-0"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                    </div>
                                </div>
                                
                                <!-- Date and Time Row -->
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
                        <div v-if="!$page.props.auth.user" class="mt-4">
                            <button @click="openAuthModal('register')" class="btn-brand-primary text-sm px-6 py-2">
                                شروع پیش‌بینی
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <!-- methodology removed -->

        <!-- Mobile-First Footer -->
        <footer class="bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
                <div class="border-t border-slate-200 mt-2 pt-6 sm:pt-8">
                    <!-- Mobile: Stack vertically, Desktop: Side by side -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0 text-center sm:text-right">
                        <div class="text-sm text-slate-500 order-2 sm:order-1">
                            © ۲۰۲۵ FourFourTwo. تمامی حقوق محفوظ است.
                        </div>
                        <div class="flex items-center justify-center sm:justify-end space-x-2 space-x-reverse text-sm text-slate-500 order-1 sm:order-2">
                            <span>ساخته شده با</span>
                            <span class="text-brand-200">❤️</span>
                            <span>برای علاقه‌مندان فوتبال</span>
                        </div>
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
  --brand-1: #005724;
  --brand-2: #009c42;
  --brand-3: #006a2d;

}

/* Brand helpers */
.text-brand-50 { color: rgba(255,255,255,0.95); }
.text-brand-100 { color: #e9ddff; }
.text-brand-200 { color: #009c42; }
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

/* Mobile-first button styles */
.btn-brand-primary {
  background-image: linear-gradient(90deg, var(--brand-1), var(--brand-2), var(--brand-3));
  color: #fff; font-weight: 700; padding: .875rem 1.5rem; border-radius: .75rem;
  transition: transform .15s ease, box-shadow .2s ease, opacity .2s ease;
  box-shadow: 0 8px 16px rgba(9, 14, 80, 0.2);
  text-align: center; display: inline-flex; align-items: center; justify-content: center;
}

.btn-brand-primary:hover { 
  transform: translateY(-1px); 
  box-shadow: 0 12px 24px rgba(9, 14, 80, 0.3); 
}

.btn-brand-ghost {
  border: 2px solid rgba(255,255,255,0.35); color: #fff; 
  padding: .875rem 1.5rem; border-radius: .75rem;
  transition: background-color .2s ease, border-color .2s ease; background-color: transparent;
  text-align: center; display: inline-flex; align-items: center; justify-content: center;
}

.btn-brand-ghost:hover { 
  background-color: rgba(255,255,255,0.08); 
  border-color: rgba(255,255,255,0.55); 
}

/* Desktop adjustments */
@media (min-width: 640px) {
  .btn-brand-primary {
    padding: 1rem 2rem;
    box-shadow: 0 10px 20px rgba(9, 14, 80, 0.2);
  }
  
  .btn-brand-primary:hover {
    box-shadow: 0 14px 28px rgba(9, 14, 80, 0.3);
  }
  
  .btn-brand-ghost {
    padding: 1rem 2rem;
  }
}

.brand-card { 
  background: white; border: 1px solid #e5e7eb; border-radius: 1rem; 
  padding: 1rem; 
}

@media (min-width: 640px) {
  .brand-card {
    padding: 1.25rem;
  }
}

.card-step { 
  background: white; border: 1px solid #e5e7eb; border-radius: 1rem; 
  padding: 1rem; text-align: center; 
}

@media (min-width: 640px) {
  .card-step {
    padding: 1.25rem;
  }
}

.step-icon { font-size: 1.25rem; }
@media (min-width: 640px) {
  .step-icon { font-size: 1.5rem; }
}

.step-title { margin-top: .5rem; font-weight: 800; color: #0f172a; }
.step-desc { margin-top: .25rem; color: #64748b; font-size: .875rem; }
@media (min-width: 640px) {
  .step-desc { font-size: .9rem; }
}

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

/* Mobile touch improvements */
@media (max-width: 639px) {
  /* Larger touch targets on mobile */
  button, .btn-brand-primary, .btn-brand-ghost {
    min-height: 44px;
  }
  
  /* Better text readability on mobile */
  body {
    -webkit-text-size-adjust: 100%;
  }
  
  /* Reduce motion for users who prefer it */
  @media (prefers-reduced-motion: reduce) {
    .btn-brand-primary:hover,
    .btn-brand-ghost:hover {
      transform: none;
    }
  }
}
</style>
