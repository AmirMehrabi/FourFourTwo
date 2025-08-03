<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    upcomingFixtures: Array,
    recentPredictions: Array,
    userRank: Number,
    userPoints: Number,
    topLeaderboard: Array,
    totalUsers: Number,
});

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('fa-IR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
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
</script>

<template>
    <Head title="داشبورد" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">
                داشبورد
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Upcoming Fixtures Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">پیش‌بینی شما از مسابقات آتی</h3>
                            <Link :href="route('fixtures.index')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                مشاهده همه مسابقات ←
                            </Link>
                        </div>
                        
                        <div class="space-y-4">
                            <div
                                v-for="fixture in upcomingFixtures"
                                :key="fixture.id"
                                class="p-4 border rounded-md"
                                :class="{
                                    'bg-gray-100 opacity-75': fixture.is_locked,
                                }"
                            >
                                <p class="text-sm text-center text-gray-500">
                                    {{
                                        new Date(
                                            fixture.match_datetime
                                        ).toLocaleString("fa-IR")
                                    }}
                                </p>

                                <div
                                    class="grid grid-cols-3 items-center mt-2 text-center"
                                >
                                    <span class="font-bold text-right">{{
                                        fixture.home_team.name
                                    }}</span>
                                    <div
                                        class="flex items-center justify-center gap-2 mx-4"
                                    >
                                        <div v-if="fixture.prediction" class="flex items-center gap-2">
                                            <span class="w-16 text-center font-bold text-blue-600">
                                                {{ fixture.prediction.home_score_predicted }}
                                            </span>
                                            <span class="m-0 px-2">-</span>
                                            <span class="w-16 text-center font-bold text-blue-600">
                                                {{ fixture.prediction.away_score_predicted }}
                                            </span>
                                        </div>
                                        <div v-else class="flex items-center gap-2">
                                            <span class="w-16 text-center text-gray-400">
                                                ?
                                            </span>
                                            <span class="m-0 px-2 text-gray-400">-</span>
                                            <span class="w-16 text-center text-gray-400">
                                                ?
                                            </span>
                                        </div>
                                    </div>
                                    <span class="font-bold text-left">{{
                                        fixture.away_team.name
                                    }}</span>
                                </div>
                                
                                <!-- Status and Action -->
                                <div class="mt-3 flex justify-between items-center">
                                    <div class="text-xs">
                                        <span v-if="fixture.prediction" class="text-green-600 font-medium">
                                            پیش‌بینی شده
                                        </span>
                                        <span v-else-if="fixture.is_locked" class="text-red-500 font-medium">
                                            قفل شده
                                        </span>
                                        <span v-else class="text-yellow-600 font-medium">
                                            پیش‌بینی نشده
                                        </span>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="text-xs text-gray-500 mb-1">
                                            {{ getTimeUntilLock(fixture) }}
                                        </div>
                                        <!-- <Link 
                                            v-if="!fixture.is_locked"
                                            :href="route('fixtures.index', { matchweek: fixture.matchweek })"
                                            class="inline-block px-3 py-1 text-xs rounded-md transition-colors"
                                            :class="fixture.prediction 
                                                ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' 
                                                : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'"
                                        >
                                            {{ fixture.prediction ? 'ویرایش' : 'پیش‌بینی' }}
                                        </Link> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="upcomingFixtures.length === 0" class="text-center py-8 text-gray-500">
                            هیچ مسابقه آتی پیدا نشد.
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout for Predictions and Leaderboard -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Your Recent Predictions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔷 پیش‌بینی‌های اخیر شما</h3>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="prediction in recentPredictions" 
                                    :key="prediction.id"
                                    class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-md"
                                >
                                    <div class="flex-1">
                                        <div class="text-sm font-medium">
                                            {{ prediction.fixture.home_team.name }} vs {{ prediction.fixture.away_team.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ formatDateTime(prediction.fixture.match_datetime) }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-bold text-blue-600">
                                        {{ prediction.home_score_predicted }}-{{ prediction.away_score_predicted }}
                                    </div>
                                    <div v-if="prediction.points_awarded !== null" class="ml-2 text-xs">
                                        <span class="px-2 py-1 rounded-full" 
                                              :class="prediction.points_awarded > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                            {{ prediction.points_awarded }}pts
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="recentPredictions.length === 0" class="text-center py-4 text-gray-500">
                                هنوز پیش‌بینی‌ای نداشته‌اید. شروع کنید!
                            </div>
                        </div>
                    </div>

                    <!-- Leaderboard Preview -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">🔷 جدول امتیازات</h3>
                                <Link :href="route('leaderboard.index')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    جدول کامل امتیازات ←
                                </Link>
                            </div>
                            
                            <!-- User's Rank -->
                            <div class="mb-4 p-3 bg-blue-50 rounded-md">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{userRank}}</div>
                                    <div class="text-sm text-gray-600">رتبه فعلی شما</div>
                                    <div class="text-lg font-semibold text-blue-800">{{ userPoints }} امتیاز</div>
                                    <div class="text-xs text-gray-500">از {{ totalUsers }} بازیکن</div>
                                </div>
                            </div>

                            <!-- Top 5 -->
                            <div class="space-y-2">
                                <div 
                                    v-for="(user, index) in topLeaderboard" 
                                    :key="user.id"
                                    class="flex items-center justify-between py-2 px-3 rounded-md"
                                    :class="user.id === $page.props.auth.user.id ? 'bg-blue-100' : 'bg-gray-50'"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                             :class="index === 0 ? 'bg-yellow-400 text-yellow-900' : 
                                                    index === 1 ? 'bg-gray-300 text-gray-800' : 
                                                    index === 2 ? 'bg-orange-400 text-orange-900' : 
                                                    'bg-gray-200 text-gray-700'">
                                            {{ index + 1 }}
                                        </div>
                                        <span class="text-sm font-medium" 
                                              :class="user.id === $page.props.auth.user.id ? 'text-blue-800' : 'text-gray-900'">
                                            {{ user.name }}
                                            <span v-if="user.id === $page.props.auth.user.id" class="text-xs">(You)</span>
                                        </span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-600">{{ user.total_points }}pts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
