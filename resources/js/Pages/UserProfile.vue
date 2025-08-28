<template>
    <Head title="پروفایل کاربر" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-slate-800">پروفایل کاربر</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Profile Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 text-center">
                        <!-- Avatar -->
                        <div class="relative inline-block mb-6">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg ring-4 ring-white/20">
                                {{ profileUser.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>

                        <!-- Username and Name -->
                        <div class="mb-4">
                            <h1 class="text-3xl font-bold text-slate-900 mb-2">
                                <Link 
                                    :href="`/@${profileUser.username}`" 
                                    class="hover:text-green-600 transition-colors duration-200"
                                >
                                    @{{ profileUser.username }}
                                </Link>
                            </h1>
                            <p class="text-xl text-slate-600">{{ profileUser.name }}</p>
                        </div>
                        
                        <!-- Bio -->
                        <p v-if="profileUser.bio" class="text-slate-600 mb-4 max-w-2xl mx-auto">{{ profileUser.bio }}</p>
                        
                        <!-- Member Since -->
                        <p class="text-sm text-slate-500 mb-8">عضو از {{ formatPersianDate(profileUser.created_at) }}</p>

                        <!-- Action Buttons -->
                        <div class="flex justify-center space-x-4 rtl:space-x-reverse">
                            <div v-if="!isOwnProfile" class="flex space-x-3">
                                <button
                                    v-if="!isFollowing"
                                    @click="followUser"
                                    class=" inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-[--brand-2] rounded-lg hover:bg-[--brand-2]/90 transition-all duration-200"
                                >
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    دنبال کردن
                                </button>
                                <button
                                    v-else
                                    @click="unfollowUser"
                                    class=" inline-flex items-center px-6 py-3 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-all duration-200"
                                >
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    دنبال شده
                                </button>
                            </div>
                            <button
                                v-else
                                @click="editProfile"
                                class=" inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-[--brand-1] rounded-lg hover:bg-[--brand-1]/90 transition-all duration-200"
                            >
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                ویرایش پروفایل
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Points -->
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">کل امتیازات</div>
                        <div class="text-2xl font-extrabold text-slate-900">{{ profileUser.stats.total_points.toLocaleString('fa-IR') }}</div>
                        <div class="mt-2">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-[--brand-2] to-[--brand-3] h-2 rounded-full transition-all duration-500" 
                                     :style="{ width: Math.min((profileUser.stats.total_points / 1000) * 100, 100) + '%' }"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">هدف: 1000 امتیاز</p>
                        </div>
                    </div>

                    <!-- Current Season Points -->
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">امتیازات فصل</div>
                        <div class="text-2xl font-extrabold text-slate-900">{{ profileUser.stats.current_season_points.toLocaleString('fa-IR') }}</div>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[--brand-2]/10 text-[--brand-2]">
                                رتبه #{{ profileUser.stats.current_season_rank || 'نامشخص' }}
                            </span>
                        </div>
                    </div>

                    <!-- Accuracy -->
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">دقت پیش‌بینی</div>
                        <div class="text-2xl font-extrabold text-slate-900">{{ profileUser.stats.accuracy }}%</div>
                        <div class="mt-2">
                            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                <span class="text-xs text-slate-600">نرخ موفقیت:</span>
                                <span class="text-xs font-semibold text-emerald-600">{{ profileUser.stats.correct_predictions }}/{{ profileUser.stats.total_predictions }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Predictions -->
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="text-xs text-slate-500 mb-2">کل پیش‌بینی‌ها</div>
                        <div class="text-2xl font-extrabold text-slate-900">{{ profileUser.stats.total_predictions.toLocaleString('fa-IR') }}</div>
                        <div class="mt-2">
                            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                <span class="text-xs text-slate-600">نظرات:</span>
                                <span class="text-xs font-semibold text-[--brand-1]">{{ profileUser.stats.total_comments }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Best Streak -->
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">بهترین رکورد</h3>
                                <p class="text-2xl font-bold text-orange-600">{{ profileUser.stats.best_streak }}</p>
                                <p class="text-xs text-slate-500">پیش‌بینی صحیح متوالی</p>
                            </div>
                        </div>
                    </div>

                    <!-- Current Streak -->
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">رکورد فعلی</h3>
                                <p class="text-2xl font-bold text-emerald-600">{{ profileUser.stats.current_streak }}</p>
                                <p class="text-xs text-slate-500">پیش‌بینی صحیح فعلی</p>
                            </div>
                        </div>
                    </div>

                    <!-- Community Stats -->
                    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">جامعه</h3>
                                <div class="space-y-1">
                                    <p class="text-xs text-slate-600">دنبال‌کنندگان: <span class="font-semibold text-purple-600">{{ profileUser.stats.followers_count }}</span></p>
                                    <p class="text-xs text-slate-600">دنبال‌شوندگان: <span class="font-semibold text-indigo-600">{{ profileUser.stats.following_count }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Tabs -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-slate-50 border-b border-gray-200">
                        <nav class="flex space-x-8 rtl:space-x-reverse px-6">
                            <button
                                @click="activeTab = 'predictions'"
                                :class="[
                                    activeTab === 'predictions'
                                        ? 'border-[--brand-2] text-[--brand-2] bg-white shadow-sm'
                                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100',
                                    'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg'
                                ]"
                            >
                                <span class="flex items-center space-x-2 rtl:space-x-reverse">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <span>پیش‌بینی‌های اخیر</span>
                                </span>
                            </button>
                            <button
                                @click="activeTab = 'comments'"
                                :class="[
                                    activeTab === 'comments'
                                        ? 'border-[--brand-2] text-[--brand-2] bg-white shadow-sm'
                                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100',
                                    'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg'
                                ]"
                            >
                                <span class="flex items-center space-x-2 rtl:space-x-reverse">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span>نظرات اخیر</span>
                                </span>
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Recent Predictions Tab -->
                        <div v-if="activeTab === 'predictions'">
                            <div v-if="profileUser.recent_predictions.length === 0" class="text-center py-12">
                                <div class="w-24 h-24 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <p class="text-slate-500 text-lg">هنوز پیش‌بینی‌ای نداشته‌اید</p>
                                <p class="text-slate-400 text-sm mt-2">شروع کنید و امتیاز کسب کنید!</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="prediction in profileUser.recent_predictions"
                                    :key="prediction.id"
                                    class="fixture-card p-5 border border-gray-100 rounded-lg hover:shadow-md transition-all duration-200"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-3">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-lg font-bold text-slate-900">{{ translateTeamName(prediction.home_team) }}</span>
                                                    <span class="text-slate-400">vs</span>
                                                    <span class="text-lg font-bold text-slate-900">{{ translateTeamName(prediction.away_team) }}</span>
                                                </div>
                                                <span class="text-sm text-slate-500 bg-slate-100 px-3 py-1 rounded-full">{{ formatPersianDate(prediction.fixture_date) }}</span>
                                            </div>
                                            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm text-slate-600">پیش‌بینی:</span>
                                                    <span class="font-bold text-lg bg-blue-100 text-blue-800 px-3 py-1 rounded-lg">{{ prediction.home_score_predicted }} - {{ prediction.away_score_predicted }}</span>
                                                </div>
                                                <div v-if="prediction.status === 'finished'" class="flex items-center space-x-2">
                                                    <span class="text-sm text-slate-600">نتیجه:</span>
                                                    <span class="font-bold text-lg bg-slate-100 text-slate-800 px-3 py-1 rounded-lg">{{ prediction.home_score_actual }} - {{ prediction.away_score_actual }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end space-y-3">
                                            <div v-if="prediction.status === 'finished'" class="flex items-center">
                                                <span
                                                    :class="[
                                                        prediction.is_correct
                                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                            : 'bg-red-50 text-red-700 border-red-200',
                                                        'px-4 py-2 rounded-full text-sm font-medium border'
                                                    ]"
                                                >
                                                    <span class="flex items-center space-x-2 rtl:space-x-reverse">
                                                        <svg v-if="prediction.is_correct" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        <span>{{ prediction.is_correct ? 'صحیح' : 'نادرست' }}</span>
                                                    </span>
                                                </span>
                                            </div>
                                            <div v-if="prediction.points_awarded !== null" class="text-center">
                                                <span class="text-sm text-slate-500">امتیاز کسب شده</span>
                                                <div class="text-2xl font-bold" :class="prediction.points_awarded > 0 ? 'text-emerald-600' : 'text-slate-600'">
                                                    {{ prediction.points_awarded > 0 ? '+' : '' }}{{ prediction.points_awarded }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Comments Tab -->
                        <div v-if="activeTab === 'comments'">
                            <div v-if="profileUser.recent_comments.length === 0" class="text-center py-12">
                                <div class="w-24 h-24 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <p class="text-slate-500 text-lg">هنوز نظری نداده‌اید</p>
                                <p class="text-slate-400 text-sm mt-2">با جامعه تعامل کنید!</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="comment in profileUser.recent_comments"
                                    :key="comment.id"
                                    class="fixture-card p-5 border border-gray-100 rounded-lg hover:shadow-md transition-all duration-200"
                                >
                                    <div class="mb-4">
                                        <p class="text-slate-900 text-lg leading-relaxed">{{ comment.content }}</p>
                                    </div>
                                    <div class="flex items-center justify-between text-sm text-slate-500">
                                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium">
                                                {{ translateTeamName(comment.fixture.home_team) }} vs {{ translateTeamName(comment.fixture.away_team) }}
                                            </span>
                                            <span class="text-slate-400">{{ formatPersianDate(comment.created_at) }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                            <span class="font-medium text-slate-700">{{ comment.reactions_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useTranslations } from '@/composables/useTranslations.js'

const props = defineProps({
    profileUser: Object,
    isFollowing: Boolean,
    isOwnProfile: Boolean,
})

const activeTab = ref('predictions')
const { translateTeamName } = useTranslations()

// Persian date formatting - same as Dashboard/Index.vue
const formatPersianDate = (dateString) => {
    const date = new Date(dateString)
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    return date.toLocaleDateString('fa-IR', options)
}

const followUser = async () => {
    try {
        const response = await fetch(`/@${props.profileUser.username}/follow`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        
        if (response.ok) {
            window.location.reload()
        }
    } catch (error) {
        console.error('Error following user:', error)
    }
}

const unfollowUser = async () => {
    try {
        const response = await fetch(`/@${props.profileUser.username}/unfollow`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        
        if (response.ok) {
            window.location.reload()
        }
    } catch (error) {
        console.error('Error unfollowing user:', error)
    }
}

const editProfile = () => {
    router.visit('/profile')
}
</script>

<style scoped>

/* Same styling patterns as other pages */
.fixture-card {
    background-color: white;
    transition: all 0.2s ease-in-out;
}

.fixture-card:hover {
    transform: translateY(-1px);
}

.nav-btn {
    transition: all 0.2s ease-in-out;
}

.nav-btn:hover {
    transform: translateY(-1px);
}
</style>
