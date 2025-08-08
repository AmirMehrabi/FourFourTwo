<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import { useTranslations } from '@/composables/useTranslations.js';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    PointElement,
    LineElement,
    Filler
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    PointElement,
    LineElement,
    Filler
);

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

const activeTab = ref('login');
const showAuthModal = ref(false);
const heroSlide = ref(0);
const demoConfidence = ref(75);
const demoHomeScore = ref(2);
const demoAwayScore = ref(1);
const touchStartX = ref(0);
const touchEndX = ref(0);
const heroIntervalId = ref(null);

const heroSlides = [
    {
        title: 'پیش‌بینی مبتنی بر داده',
        subtitle: 'بر اساس پیش‌بینی‌های جامعه‌ی کاربری',
        stat: `${props.stats?.accuracy_rate || 0}% دقت`,
        description: 'کاربران ما عملکرد بهتری از متوسط بازار دارند'
    },
    {
        title: 'جامعه‌ای از تحلیلگران',
        subtitle: `${props.stats?.total_users || 0}+ پیش‌بین فعال`,
        stat: `${props.stats?.weekly_predictions || 0} پیش‌بینی این هفته`,
        description: 'به جامعه‌ای از علاقه‌مندان تحلیل فوتبال بپیوندید'
    },
    {
        title: 'نتایج زنده و آمار',
        subtitle: props.weeklyInsight,
        stat: `${props.liveStats?.predictions_today || 0} پیش‌بینی امروز`,
        description: 'دنبال کردن عملکرد و پیشرفت در زمان واقعی'
    }
];

const accuracyChartData = computed(() => ({
    labels: ['دقیق', 'نزدیک', 'اشتباه'],
    datasets: [{
        data: [
            props.accuracyBreakdown?.perfect || 0,
            props.accuracyBreakdown?.close || 0,
            props.accuracyBreakdown?.wrong || 0
        ],
        backgroundColor: ['#6e4bb3', '#9a3aa0', '#3b4db3'],
        borderWidth: 0
    }]
}));

const accuracyChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 20,
                usePointStyle: true,
                font: { family: 'Tanha', size: 12 }
            }
        }
    }
};

const confidenceChartData = computed(() => ({
    labels: ['اعتماد بالا', 'اعتماد متوسط', 'اعتماد پایین'],
    datasets: [{
        label: 'درصد دقت',
        data: [
            props.confidenceMetrics?.high_confidence_accuracy || 0,
            props.confidenceMetrics?.medium_confidence_accuracy || 0,
            props.confidenceMetrics?.low_confidence_accuracy || 0
        ],
        backgroundColor: ['#411085', '#7b0681', '#071a8a'],
        borderWidth: 1,
        borderColor: '#fff'
    }]
}));

const weeklyProgressData = computed(() => ({
    labels: ['پیش‌بینی شده', 'باقی‌مانده'],
    datasets: [{
        data: [
            props.weeklyChallenge?.completed_fixtures || 0,
            (props.weeklyChallenge?.total_fixtures || 0) - (props.weeklyChallenge?.completed_fixtures || 0)
        ],
        backgroundColor: ['#7b0681', '#E8F4F8'],
        borderWidth: 0
    }]
}));

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

const handleTouchStart = (e) => {
    touchStartX.value = e.touches[0].clientX;
};

const handleTouchEnd = (e) => {
    touchEndX.value = e.changedTouches[0].clientX;
    const diff = touchStartX.value - touchEndX.value;
    
    if (Math.abs(diff) > 50) { // Minimum swipe distance
        if (diff > 0) {
            // Swipe left - next slide
            heroSlide.value = (heroSlide.value + 1) % heroSlides.length;
        } else {
            // Swipe right - previous slide
            heroSlide.value = heroSlide.value === 0 ? heroSlides.length - 1 : heroSlide.value - 1;
        }
    }
};

const openAuthModal = (tab = 'login') => {
    activeTab.value = tab;
    showAuthModal.value = true;
};

const closeAuthModal = () => {
    showAuthModal.value = false;
};

onMounted(() => {
    // Auto-rotate hero slides
    heroIntervalId.value = setInterval(() => {
        heroSlide.value = (heroSlide.value + 1) % heroSlides.length;
    }, 5000);
});

onUnmounted(() => {
    if (heroIntervalId.value) clearInterval(heroIntervalId.value);
});
</script>

<template>
    <Head title="FourFourTwo - پیش‌بینی مبتنی بر داده">
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
                        <a href="#how" class="hover:text-brand-300">چگونه کار می‌کند</a>
                        <a href="#live" class="hover:text-brand-300">زنده</a>
                        <a href="#method" class="hover:text-brand-300">روش‌شناسی</a>
                        <a href="#pricing" class="hover:text-brand-300">قیمت‌گذاری</a>
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
                            <button 
                                @click="openAuthModal('login')"
                                class="text-slate-700 hover:text-brand-300 transition-colors font-600 px-4 py-2 rounded-lg hover:bg-brand-50"
                            >
                                ورود
                            </button>
                            <button 
                                @click="openAuthModal('register')"
                                class="btn-brand-primary"
                            >
                                ثبت‌نام
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section with Dynamic Content -->
        <section class="relative overflow-hidden text-white h-screen min-h-[600px] max-h-[800px]">
            <div class="absolute inset-0 brand-hero-gradient"></div>
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 h-full"
                 @touchstart="handleTouchStart" 
                 @touchend="handleTouchEnd">
                <!-- Social proof (top) -->
                <div class="flex justify-center lg:justify-end gap-3 text-white/90 text-xs mb-4">
                    <span class="badge-translucent">⚡ به‌روزرسانی زنده</span>
                    <span class="badge-translucent hidden sm:inline">🎯 {{ stats?.accuracy_rate || 0 }}% دقت ۷ روز اخیر</span>
                    <span class="badge-translucent hidden sm:inline">👥 {{ stats?.total_users?.toLocaleString() || '0' }} کاربر</span>
                </div>
                <div class="grid lg:grid-cols-2 gap-12 items-center h-[calc(100%-2rem)]">
                    <!-- Hero Content -->
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <div class="text-brand-100 font-600 text-lg tracking-wide">
                                {{ heroSlides[heroSlide].subtitle }}
                            </div>
                            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-800 leading-tight">
                                {{ heroSlides[heroSlide].title }}
                            </h2>
                            <p class="text-lg sm:text-xl text-white/80 font-300 leading-relaxed">
                                {{ heroSlides[heroSlide].description }}
                            </p>
                        </div>
                        
                        <!-- Live Stats Banner -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/15">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <div class="text-2xl sm:text-3xl font-800 text-brand-50">{{ heroSlides[heroSlide].stat }}</div>
                                    <div class="text-sm text-white/80 mt-1">عملکرد جامعه</div>
                                </div>
                                <div>
                                    <div class="text-2xl sm:text-3xl font-800 text-brand-50">{{ liveStats?.active_users_today || 0 }}</div>
                                    <div class="text-sm text-white/80 mt-1">کاربر فعال امروز</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button 
                                @click="openAuthModal('register')"
                                class="btn-brand-primary"
                            >
                                همین حالا شروع کنید
                            </button>
                            <button 
                                @click="openAuthModal('login')"
                                class="btn-brand-ghost"
                            >
                                ورود به حساب کاربری
                            </button>
                        </div>

                        <!-- Slide Indicators -->
                        <div class="flex space-x-2 space-x-reverse justify-center lg:justify-start">
                            <button 
                                v-for="(slide, index) in heroSlides" 
                                :key="index"
                                @click="heroSlide = index"
                                :class="['h-2 rounded-full transition-all duration-300', heroSlide === index ? 'bg-white w-8' : 'bg-white/40 w-2']"
                            ></button>
                        </div>
                    </div>

                    <!-- CTA Section for smaller screens instead of form -->
                    <div class="lg:hidden text-center">
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/15">
                            <h3 class="text-2xl font-800 mb-4">به جامعه ما بپیوندید</h3>
                            <p class="text-white/80 mb-6">پیش‌بینی کنید، امتیاز کسب کنید و با دیگران رقابت کنید</p>
                            <button 
                                @click="openAuthModal('register')"
                                class="w-full btn-brand-primary"
                            >
                                شروع رایگان
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Trusted by strip -->
                <div class="mt-8">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl border border-white/15 px-4 py-3">
                        <div class="flex items-center justify-center gap-6 text-white/80 text-xs">
                            <span>مورد اعتماد تحلیل‌گران فوتبال</span>
                            <span class="w-1 h-1 bg-white/40 rounded-full"></span>
                            <span>پوشش بازی‌های امروز</span>
                            <span class="w-1 h-1 bg-white/40 rounded-full"></span>
                            <span>کالیبراسیون تایید شده</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <h3 class="text-3xl sm:text-4xl font-800 text-slate-900 mb-3">چطور کار می‌کنیم</h3>
                    <p class="text-slate-600">داده‌ها → مدل → پیش‌بینی → بازخورد جامعه</p>
                </div>
                <div class="grid md:grid-cols-4 gap-6">
                    <div class="card-step">
                        <div class="step-icon">📡</div>
                        <div class="step-title">جمع‌آوری داده</div>
                        <div class="step-desc">نتایج، فرم تیم‌ها، پیش‌بینی‌های جامعه</div>
                    </div>
                    <div class="card-step">
                        <div class="step-icon">🧠</div>
                        <div class="step-title">مدل‌سازی</div>
                        <div class="step-desc">احتمال برد/مساوی/باخت و اعتماد</div>
                    </div>
                    <div class="card-step">
                        <div class="step-icon">📈</div>
                        <div class="step-title">نمایش</div>
                        <div class="step-desc">کارت بازی با احتمال و فرم اخیر</div>
                    </div>
                    <div class="card-step">
                        <div class="step-icon">🔁</div>
                        <div class="step-title">کالیبراسیون</div>
                        <div class="step-desc">بازبینی دقت و بهبود مستمر</div>
                    </div>
                </div>
            </div>
        </section>

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
                                <img :src="`/assets/team-logos/${m?.home_team}.png`" class="w-6 h-6 object-contain" @error="$event.target.style.display='none'"/>
                                <div class="font-600 text-slate-900">{{ translateTeamName(m?.home_team) }}</div>
                            </div>
                            <div class="text-slate-400">vs</div>
                            <div class="flex items-center gap-2">
                                <div class="font-600 text-slate-900">{{ translateTeamName(m?.away_team) }}</div>
                                <img :src="`/assets/team-logos/${m?.away_team}.png`" class="w-6 h-6 object-contain" @error="$event.target.style.display='none'"/>
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
                    <h3 class="text-4xl font-800 text-slate-900 mb-4">فعالیت زنده جامعه</h3>
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
                                        <img 
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
                                        <img 
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
                                        <img 
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
                                        <img 
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

        <!-- Methodology -->
        <section id="method" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h3 class="text-3xl sm:text-4xl font-800 text-slate-900 mb-3">روش‌شناسی ما</h3>
                    <p class="text-slate-600">شفافیت در دقت و کالیبراسیون</p>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="brand-card">
                        <div class="text-sm text-slate-500 mb-2">Brier Score (۷ روز)</div>
                        <div class="text-3xl font-800 text-slate-900">{{ (props.confidenceMetrics?.brier_7d ?? '—') }}</div>
                    </div>
                    <div class="brand-card">
                        <div class="text-sm text-slate-500 mb-2">LogLoss (۷ روز)</div>
                        <div class="text-3xl font-800 text-slate-900">{{ (props.confidenceMetrics?.logloss_7d ?? '—') }}</div>
                    </div>
                    <div class="brand-card">
                        <div class="text-sm text-slate-500 mb-2">اندازه نمونه (۳۰ روز)</div>
                        <div class="text-3xl font-800 text-slate-900">{{ (props.stats?.sample_size_30d ?? '—') }}</div>
                    </div>
                </div>
                <div class="mt-10 grid md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h4 class="text-lg font-700 text-slate-900 mb-4">توزیع دقت پیش‌بینی‌ها</h4>
                        <div class="h-64">
                            <Doughnut :data="accuracyChartData" :options="accuracyChartOptions" />
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h4 class="text-lg font-700 text-slate-900 mb-4">اعتماد در مقابل دقت</h4>
                        <div class="h-64">
                            <Bar :data="confidenceChartData" :options="{ responsive: true, maintainAspectRatio: false }" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
                    <h2 class="text-2xl font-800 text-slate-900">
                        {{ activeTab === 'login' ? 'ورود به حساب کاربری' : 'ایجاد حساب کاربری جدید' }}
                    </h2>
                    <button @click="closeAuthModal" 
                            class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
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
                <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="space-y-6">
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
                        <input 
                            v-model="loginForm.password"
                            type="password" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900"
                            placeholder="••••••••"
                        >
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
                <form v-if="activeTab === 'register'" @submit.prevent="submitRegister" class="space-y-6">
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
                        <input 
                            v-model="registerForm.password"
                            type="password" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-200 focus:border-brand-200 transition-all bg-white text-slate-900"
                            placeholder="••••••••"
                        >
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
