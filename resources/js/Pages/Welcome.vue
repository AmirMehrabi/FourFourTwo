<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
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
const heroSlide = ref(0);
const demoConfidence = ref(75);
const demoHomeScore = ref(2);
const demoAwayScore = ref(1);

const heroSlides = [
    {
        title: 'پیش‌بینی مبتنی بر داده',
        subtitle: 'بر اساس ۱۰,۰۰۰+ پیش‌بینی جامعه',
        stat: `${props.stats?.accuracy_rate || 78}% دقت`,
        description: 'کاربران ما عملکرد بهتری از متوسط بازار دارند'
    },
    {
        title: 'جامعه‌ای از تحلیلگران',
        subtitle: `${props.stats?.total_users || 2000}+ پیش‌بین فعال`,
        stat: `${props.stats?.weekly_predictions || 450} پیش‌بینی این هفته`,
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
        backgroundColor: ['#FF6B35', '#4ECDC4', '#95A5A6'],
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
        backgroundColor: ['#FF6B35', '#F7931E', '#FFD93D'],
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
        backgroundColor: ['#4ECDC4', '#E8F4F8'],
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
    loginForm.post('/login');
};

const submitRegister = () => {
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

const getConfidenceColor = (confidence) => {
    if (confidence >= 80) return 'bg-green-500';
    if (confidence >= 60) return 'bg-yellow-500';
    return 'bg-red-500';
};

onMounted(() => {
    // Auto-rotate hero slides
    setInterval(() => {
        heroSlide.value = (heroSlide.value + 1) % heroSlides.length;
    }, 5000);
});
</script>

<template>
    <Head title="FourFourTwo - پیش‌بینی مبتنی بر داده">
        <link href="https://fonts.googleapis.com/css2?family=Tanha:wght@300;400;600;800&display=swap" rel="stylesheet">
    </Head>
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 font-tanha" dir="rtl">
        <!-- Subtle grid background pattern -->
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(15,23,42,0.15) 1px, transparent 0); background-size: 20px 20px;"></div>
        </div>
        
        <!-- Header -->
        <header class="relative border-b border-slate-200 bg-white/90 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-3xl font-800 text-slate-900">FourFourTwo</h1>
                        <div class="mr-3 flex items-center">
                            <span class="text-sm text-slate-600 bg-orange-100 text-orange-800 px-3 py-1 rounded-full font-600">پیش‌بینی</span>
                            <div class="mr-2 w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                    <div v-if="$page.props.auth.user" class="flex items-center space-x-4 space-x-reverse">
                        <Link 
                            :href="route('dashboard')"
                            class="text-slate-900 hover:text-orange-600 transition-colors font-600 px-4 py-2 rounded-lg hover:bg-orange-50"
                        >
                            داشبورد
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section with Dynamic Content -->
        <section class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white">
            <div class="absolute inset-0 bg-gradient-to-r from-orange-500/10 to-blue-500/10"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Hero Content -->
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <div class="text-orange-400 font-600 text-lg tracking-wide">
                                {{ heroSlides[heroSlide].subtitle }}
                            </div>
                            <h2 class="text-5xl lg:text-6xl font-800 leading-tight">
                                {{ heroSlides[heroSlide].title }}
                            </h2>
                            <p class="text-xl text-slate-300 font-300 leading-relaxed">
                                {{ heroSlides[heroSlide].description }}
                            </p>
                        </div>
                        
                        <!-- Live Stats Banner -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <div class="text-3xl font-800 text-orange-400">{{ heroSlides[heroSlide].stat }}</div>
                                    <div class="text-sm text-slate-300 mt-1">عملکرد جامعه</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-800 text-blue-400">{{ liveStats?.active_users_today || 0 }}</div>
                                    <div class="text-sm text-slate-300 mt-1">کاربر فعال امروز</div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide Indicators -->
                        <div class="flex space-x-2 space-x-reverse">
                            <button 
                                v-for="(slide, index) in heroSlides" 
                                :key="index"
                                @click="heroSlide = index"
                                :class="['w-3 h-3 rounded-full transition-all duration-300', 
                                        heroSlide === index ? 'bg-orange-400 w-8' : 'bg-white/30']"
                            ></button>
                        </div>
                    </div>

                    <!-- Auth Form -->
                    <div class="bg-white rounded-2xl shadow-2xl p-8">
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
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all bg-white text-slate-900"
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
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all bg-white text-slate-900"
                                    placeholder="••••••••"
                                >
                                <div v-if="loginForm.errors.password" class="text-red-600 text-sm mt-1">{{ loginForm.errors.password }}</div>
                            </div>
                            
                            <div class="flex items-center">
                                <input 
                                    v-model="loginForm.remember"
                                    type="checkbox" 
                                    id="remember"
                                    class="w-4 h-4 text-orange-600 border-slate-300 rounded focus:ring-orange-500"
                                >
                                <label for="remember" class="mr-2 text-sm text-slate-700">مرا به خاطر بسپار</label>
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="loginForm.processing"
                                class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-600 py-3 px-4 rounded-xl transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl"
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
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all bg-white text-slate-900"
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
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all bg-white text-slate-900"
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
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all bg-white text-slate-900"
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
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all bg-white text-slate-900"
                                    placeholder="••••••••"
                                >
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="registerForm.processing"
                                class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-600 py-3 px-4 rounded-xl transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl"
                            >
                                <span v-if="registerForm.processing">در حال ثبت‌نام...</span>
                                <span v-else>ثبت‌نام</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Dashboard Preview -->
        <section class="relative py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h3 class="text-4xl font-800 text-slate-900 mb-4">آمار پلتفرم</h3>
                    <p class="text-xl text-slate-600 font-300">نگاهی به عملکرد جامعه ما</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 border border-orange-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center">
                                <span class="text-white text-xl">👥</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-orange-600">{{ stats?.total_users?.toLocaleString() || '0' }}</div>
                                <div class="text-sm text-orange-700 font-600">کاربر فعال</div>
                            </div>
                        </div>
                        <div class="text-xs text-orange-600">+{{ liveStats?.active_users_today || 0 }} امروز</div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                                <span class="text-white text-xl">📊</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-blue-600">{{ stats?.total_predictions?.toLocaleString() || '0' }}</div>
                                <div class="text-sm text-blue-700 font-600">پیش‌بینی کل</div>
                            </div>
                        </div>
                        <div class="text-xs text-blue-600">+{{ liveStats?.predictions_today || 0 }} امروز</div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                                <span class="text-white text-xl">🎯</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-green-600">{{ stats?.accuracy_rate || 0 }}%</div>
                                <div class="text-sm text-green-700 font-600">دقت پیش‌بینی</div>
                            </div>
                        </div>
                        <div class="text-xs text-green-600">{{ stats?.exact_matches || 0 }} تطبیق دقیق</div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                                <span class="text-white text-xl">⚽</span>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-800 text-purple-600">{{ liveStats?.upcoming_matches_24h || 0 }}</div>
                                <div class="text-sm text-purple-700 font-600">بازی ۲۴ ساعت آینده</div>
                            </div>
                        </div>
                        <div class="text-xs text-purple-600">{{ liveStats?.live_matches || 0 }} بازی زنده</div>
                    </div>
                </div>

                <!-- Data Visualization Section -->
                <!-- <div class="grid lg:grid-cols-3 gap-8">
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-200">
                        <h4 class="text-lg font-700 text-slate-900 mb-4">توزیع دقت پیش‌بینی‌ها</h4>
                        <div class="h-64">
                            <Doughnut :data="accuracyChartData" :options="accuracyChartOptions" />
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-200">
                        <h4 class="text-lg font-700 text-slate-900 mb-4">اعتماد در مقابل دقت</h4>
                        <div class="h-64">
                            <Bar :data="confidenceChartData" :options="{ responsive: true, maintainAspectRatio: false }" />
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-200">
                        <h4 class="text-lg font-700 text-slate-900 mb-4">پیشرفت چالش هفتگی</h4>
                        <div class="h-64">
                            <Doughnut :data="weeklyProgressData" :options="accuracyChartOptions" />
                        </div>
                        <div class="mt-4 text-center">
                            <div class="text-2xl font-800 text-slate-900">{{ weeklyChallenge?.progress_percentage || 0 }}%</div>
                            <div class="text-sm text-slate-600">هفته {{ weeklyChallenge?.week_number || 1 }} تکمیل شده</div>
                        </div>
                    </div>
                </div> -->
            </div>
        </section>

        <!-- Live Mini-Leaderboard & Featured Content -->
        <!-- <section class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-xl font-700 text-slate-900">🏆 برترین پیش‌بین‌ها</h4>
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        </div>
                        <div class="space-y-4">
                            <div v-for="(predictor, index) in topPredictors" :key="index" 
                                 class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div class="flex items-center space-x-3 space-x-reverse">
                                    <div :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-800 text-white',
                                        index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-slate-400' : 'bg-orange-600'
                                    ]">
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-600 text-slate-900">{{ predictor.name }}</div>
                                        <div class="text-xs text-slate-500">{{ predictor.total_points }} امتیاز</div>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <div class="text-lg font-700 text-orange-600">{{ predictor.total_points }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <div class="text-sm text-slate-600">به {{ topPredictors?.length || 0 }}+ پیش‌بین موفق بپیوندید</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-200">
                        <h4 class="text-xl font-700 text-slate-900 mb-6">⚽ پیش‌بینی روز</h4>
                        <div v-if="predictionOfTheDay" class="space-y-4">
                            <div class="text-center text-sm text-slate-500 mb-2">
                                {{ new Date(predictionOfTheDay.match_datetime).toLocaleDateString('fa-IR') }}
                            </div>
                            <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div class="text-center">
                                        <div class="font-600 text-slate-900 mb-2">{{ predictionOfTheDay.homeTeam?.name }}</div>
                                        <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto text-xs font-700">
                                            {{ predictionOfTheDay.homeTeam?.name?.substring(0, 3).toUpperCase() }}
                                        </div>
                                    </div>
                                    
                                    <div class="text-center px-4">
                                        <div class="text-2xl font-800 text-slate-900 mb-2">VS</div>
                                        <div class="text-xs text-slate-500">پیش‌بینی کنید</div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="font-600 text-slate-900 mb-2">{{ predictionOfTheDay.awayTeam?.name }}</div>
                                        <div class="w-12 h-12 bg-red-600 text-white rounded-full flex items-center justify-center mx-auto text-xs font-700">
                                            {{ predictionOfTheDay.awayTeam?.name?.substring(0, 3).toUpperCase() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-2 rounded-xl font-600 hover:from-orange-600 hover:to-orange-700 transition-all">
                                    ثبت‌نام و پیش‌بینی
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center text-slate-500 py-8">
                            در حال بارگذاری...
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-200">
                        <h4 class="text-xl font-700 text-slate-900 mb-6">📅 چالش هفتگی</h4>
                        <div class="space-y-4">
                            <div class="text-center">
                                <div class="text-3xl font-800 text-orange-600">هفته {{ weeklyChallenge?.week_number }}</div>
                                <div class="text-sm text-slate-600">چالش جامعه</div>
                            </div>
                            
                            <div class="bg-slate-50 rounded-xl p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-600 text-slate-700">پیشرفت</span>
                                    <span class="text-sm font-600 text-orange-600">{{ weeklyChallenge?.progress_percentage || 0 }}%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 h-3 rounded-full transition-all duration-500" 
                                         :style="{ width: (weeklyChallenge?.progress_percentage || 0) + '%' }"></div>
                                </div>
                                <div class="flex justify-between text-xs text-slate-500 mt-2">
                                    <span>{{ weeklyChallenge?.completed_fixtures || 0 }} تکمیل شده</span>
                                    <span>{{ weeklyChallenge?.total_fixtures || 0 }} کل</span>
                                </div>
                            </div>
                            
                            <div class="text-center text-sm text-slate-600">
                                {{ weeklyChallenge?.community_predictions || 0 }} پیش‌بینی جامعه
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->


        <!-- Recent Community Predictions -->
        <section class="py-20 bg-slate-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h3 class="text-4xl font-800 text-slate-900 mb-4">فعالیت زنده جامعه</h3>
                    <p class="text-xl text-slate-600 font-300">آخرین پیش‌بینی‌های کاربران</p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="p-6 bg-gradient-to-r from-slate-900 to-slate-800 text-white">
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
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 space-x-reverse">
                                    <div class="text-sm text-slate-500">
                                        {{ formatTimeAgo(prediction.created_at) }}
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
                                            <span class="font-600 text-slate-900">{{ translateTeamName(prediction.home_team) }}</span>
                                        </div>
                                        
                                        <!-- Score -->
                                        <div class="flex items-center space-x-1 space-x-reverse mx-3">
                                            <span class="text-lg font-800 text-orange-600">{{ prediction.home_score }}</span>
                                            <span class="text-slate-400">-</span>
                                            <span class="text-lg font-800 text-orange-600">{{ prediction.away_score }}</span>
                                        </div>
                                        
                                        <!-- Away Team -->
                                        <div class="flex items-center space-x-2 space-x-reverse">
                                            <span class="font-600 text-slate-900">{{ translateTeamName(prediction.away_team) }}</span>
                                            <img 
                                                :src="`/assets/team-logos/${prediction.away_team}.png`"
                                                :alt="prediction.away_team"
                                                class="w-6 h-6 object-contain flex-shrink-0"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="text-sm text-slate-500">
                                    {{ new Date(prediction.match_datetime).toLocaleDateString('fa-IR') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="recentPredictions && recentPredictions.length === 0" class="p-8 text-center">
                        <div class="text-4xl mb-4">⚽</div>
                        <div class="text-lg font-600 text-slate-700 mb-2">هنوز پیش‌بینی‌ای ثبت نشده</div>
                        <div class="text-sm text-slate-500">اولین نفری باشید که پیش‌بینی می‌کند</div>
                    </div>
                    
                    <!-- <div class="p-4 bg-slate-50 text-center">
                        <div class="text-sm text-slate-600">
                            همین الان {{ recentPredictions?.length || 0 }} پیش‌بینی جدید ثبت شده است
                        </div>
                    </div> -->
                </div>
            </div>
        </section>


        <!-- Weekly Insight Banner -->
        <!-- <section class="py-16 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="space-y-6">
                    <div class="text-orange-400 font-600 text-lg">بینش این هفته</div>
                    <h3 class="text-4xl font-800 leading-tight">{{ weeklyInsight }}</h3>
                    <p class="text-xl text-slate-300 font-300">تحلیل‌های مبتنی بر داده از عملکرد جامعه</p>
                    <div class="pt-4">
                        <button @click="activeTab = 'register'" 
                                class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-700 px-8 py-4 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl text-lg">
                            همین حالا شروع کنید
                        </button>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- <div class="grid md:grid-cols-4 gap-8">
                    <div class="md:col-span-2">
                        <div class="flex items-center mb-4">
                            <h3 class="text-2xl font-800 text-slate-900">FourFourTwo</h3>
                            <span class="mr-2 text-sm text-orange-600 bg-orange-100 px-2 py-1 rounded-full font-600">پیش‌بینی</span>
                        </div>
                        <p class="text-slate-600 font-300 mb-6 leading-relaxed">
                            پلتفرم پیش‌بینی لیگ برتر انگلیس با رویکرد مبتنی بر داده. 
                            به جامعه‌ای از هزاران تحلیلگر فوتبال بپیوندید.
                        </p>
                        <div class="flex space-x-4 space-x-reverse">
                            <div class="bg-white rounded-lg p-3 border border-slate-200">
                                <div class="text-lg font-800 text-orange-600">{{ stats?.accuracy_rate || 78 }}%</div>
                                <div class="text-xs text-slate-500">دقت</div>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-slate-200">
                                <div class="text-lg font-800 text-blue-600">{{ stats?.total_users?.toLocaleString() || '2K' }}+</div>
                                <div class="text-xs text-slate-500">کاربر</div>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-slate-200">
                                <div class="text-lg font-800 text-green-600">{{ stats?.total_predictions?.toLocaleString() || '10K' }}+</div>
                                <div class="text-xs text-slate-500">پیش‌بینی</div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-700 text-slate-900 mb-4">درباره پلتفرم</h4>
                        <ul class="space-y-2 text-slate-600 font-300">
                            <li><a href="#" class="hover:text-orange-600 transition-colors">چگونه کار می‌کند</a></li>
                            <li><a href="#" class="hover:text-orange-600 transition-colors">سیستم امتیازدهی</a></li>
                            <li><a href="#" class="hover:text-orange-600 transition-colors">قوانین مسابقه</a></li>
                            <li><a href="#" class="hover:text-orange-600 transition-colors">سوالات متداول</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-700 text-slate-900 mb-4">ارتباط با ما</h4>
                        <ul class="space-y-2 text-slate-600 font-300">
                            <li><a href="#" class="hover:text-orange-600 transition-colors">پشتیبانی</a></li>
                            <li><a href="#" class="hover:text-orange-600 transition-colors">تماس با ما</a></li>
                            <li><a href="#" class="hover:text-orange-600 transition-colors">شرایط استفاده</a></li>
                            <li><a href="#" class="hover:text-orange-600 transition-colors">حریم خصوصی</a></li>
                        </ul>
                    </div>
                </div> -->
                
                <div class="border-t border-slate-200 mt-8 pt-8 flex justify-between items-center">
                    <div class="text-sm text-slate-500">
                        © ۲۰۲۵ FourFourTwo. تمامی حقوق محفوظ است.
                    </div>
                    <div class="flex items-center space-x-2 space-x-reverse text-sm text-slate-500">
                        <span>ساخته شده با</span>
                        <span class="text-orange-500">❤️</span>
                        <span>برای علاقه‌مندان فوتبال</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.slider::-webkit-slider-thumb {
    appearance: none;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #f97316;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.slider::-moz-range-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #f97316;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>
