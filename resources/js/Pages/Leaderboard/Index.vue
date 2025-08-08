<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    leaderboard: Array,
});
</script>

<template>
    <Head title="جدول امتیازات" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">جدول امتیازات</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        رتبه‌بندی بازیکنان بر اساس مجموع امتیازات فصل
                    </p>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span class="status-badge inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">
                        {{ leaderboard?.length || 0 }} بازیکن
                    </span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Summary card -->
                <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" v-if="leaderboard && leaderboard.length">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-extrabold text-blue-600 mb-1">#1</div>
                            <div class="text-sm text-gray-600">رتبه برتر</div>
                            <div class="mt-2 text-base font-semibold text-gray-900 truncate">{{ leaderboard[0]?.name }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-extrabold text-green-600 mb-1">{{ leaderboard[0]?.total_points ?? 0 }}</div>
                            <div class="text-sm text-gray-600">امتیاز برتر</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-extrabold text-purple-600 mb-1">{{ leaderboard.length }}</div>
                            <div class="text-sm text-gray-600">تعداد بازیکنان</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-extrabold text-orange-600 mb-1">🏆</div>
                            <div class="text-sm text-gray-600">سه نفر برتر مشخص</div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="!leaderboard || leaderboard.length === 0" class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 p-10 text-center">
                    <div class="text-3xl mb-2">📭</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">هنوز رکوردی وجود ندارد</h3>
                    <p class="text-sm text-gray-600">به محض کسب امتیاز توسط بازیکنان، جدول به‌روزرسانی می‌شود.</p>
                </div>

                <!-- Leaderboard list (mobile) -->
                <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sm:hidden" v-else>
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="text-sm text-gray-600">نمای کلی رتبه‌ها</div>
                        <div class="text-xs text-gray-500">به‌روزرسانی لحظه‌ای</div>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        <li v-for="(player, index) in leaderboard" :key="player.id" class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 font-semibold">
                                    {{ (player.name || '?').slice(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ player.name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span v-if="index === 0" class="status-badge inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-yellow-100 text-yellow-800">🥇 اول</span>
                                        <span v-else-if="index === 1" class="status-badge inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-200 text-gray-800">🥈 دوم</span>
                                        <span v-else-if="index === 2" class="status-badge inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-orange-100 text-orange-700">🥉 سوم</span>
                                        <span v-else class="text-gray-500">#{{ index + 1 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-semibold text-gray-900">{{ player.total_points }}</div>
                                <div class="text-xs text-gray-500">امتیاز</div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Leaderboard table (desktop) -->
                <div class="fixture-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden sm:block" v-if="leaderboard && leaderboard.length">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="text-sm text-gray-600">نمای کلی رتبه‌ها</div>
                        <div class="text-xs text-gray-500">به‌روزرسانی لحظه‌ای</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">رتبه</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">بازیکن</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">امتیاز</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr 
                                    v-for="(player, index) in leaderboard" 
                                    :key="player.id"
                                    class="hover:bg-gray-50 transition-colors duration-150"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        <span v-if="index === 0" class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">🥇 اول</span>
                                        <span v-else-if="index === 1" class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-800">🥈 دوم</span>
                                        <span v-else-if="index === 2" class="status-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">🥉 سوم</span>
                                        <span v-else>#{{ index + 1 }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 font-semibold">
                                                {{ (player.name || '?').slice(0, 2).toUpperCase() }}
                                            </div>
                                            <span class="font-medium">{{ player.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ player.total_points }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>