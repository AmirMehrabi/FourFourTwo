<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    userStats: {
        type: Object,
        required: true
    }
});

const streakEmoji = computed(() => {
    const streak = props.userStats.current_streak || 0;
    if (streak >= 10) return '🔥🔥🔥';
    if (streak >= 5) return '🔥🔥';
    if (streak >= 3) return '🔥';
    return '⚡';
});

const accuracyColor = computed(() => {
    const accuracy = props.userStats.accuracy || 0;
    if (accuracy >= 80) return 'from-green-400 to-emerald-500';
    if (accuracy >= 60) return 'from-yellow-400 to-orange-500';
    if (accuracy >= 40) return 'from-orange-400 to-red-500';
    return 'from-gray-400 to-gray-500';
});

const badgesByTier = computed(() => {
    const badges = props.userStats.recent_badges || [];
    return badges.reduce((acc, badge) => {
        acc[badge.tier] = (acc[badge.tier] || 0) + 1;
        return acc;
    }, {});
});
</script>

<template>
    <div class="gamification-widget bg-white border border-gray-200 rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">آمار شما</h3>
                <span class="text-xs text-gray-500">{{ streakEmoji }}</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="p-4 space-y-4">
            <!-- Streak & Accuracy Row -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Current Streak -->
                <div class="text-center p-3 bg-gray-50 rounded">
                    <div class="text-xs text-gray-600 mb-1">سری فعلی</div>
                    <div class="text-lg font-semibold text-gray-900">{{ userStats.current_streak || 0 }}</div>
                    <div class="text-xs text-gray-500">بهترین: {{ userStats.best_streak || 0 }}</div>
                </div>

                <!-- Accuracy -->
                <div class="text-center p-3 bg-gray-50 rounded">
                    <div class="text-xs text-gray-600 mb-1">دقت</div>
                    <div class="text-lg font-semibold text-gray-900">{{ userStats.accuracy || 0 }}%</div>
                    <div class="text-xs text-gray-500">پیش‌بینی‌ها</div>
                </div>
            </div>

            <!-- Badges & Social -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Badges -->
                <div class="text-center p-3 bg-gray-50 rounded">
                    <div class="text-xs text-gray-600 mb-1">مدال‌ها</div>
                    <div class="text-lg font-semibold text-gray-900">{{ userStats.badges_count || 0 }}</div>
                </div>

                <!-- Social -->
                <div class="text-center p-3 bg-gray-50 rounded">
                    <div class="text-xs text-gray-600 mb-1">دنبال‌کنندگان</div>
                    <div class="text-lg font-semibold text-gray-900">{{ userStats.followers_count || 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Mobile optimizations */
@media (max-width: 640px) {
    .gamification-widget {
        border-radius: 0.5rem;
    }
    
    .grid-cols-2 {
        gap: 0.75rem;
    }
}
</style>
