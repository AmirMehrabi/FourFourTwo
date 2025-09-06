<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    upcomingMatches: {
        type: Number,
        default: 0
    },
    suggestedUsers: {
        type: Number,
        default: 0
    }
});

const actions = [
    {
        id: 'predict',
        title: 'پیش‌بینی سریع',
        subtitle: `${props.upcomingMatches} بازی در انتظار`,
        icon: '⚽',
        color: 'from-blue-500 to-blue-600',
        href: '/fixtures',
        pulse: props.upcomingMatches > 0
    },
    {
        id: 'discover',
        title: 'کشف کاربران',
        subtitle: `${props.suggestedUsers} پیشنهاد جدید`,
        icon: '👥',
        color: 'from-purple-500 to-pink-500',
        href: '/users/discover',
        pulse: props.suggestedUsers > 0
    },
    {
        id: 'leaderboard',
        title: 'رقابت',
        subtitle: 'مقایسه با دیگران',
        icon: '🏆',
        color: 'from-amber-500 to-orange-500',
        href: '/leaderboard',
        pulse: false
    },
    {
        id: 'activity',
        title: 'فعالیت‌ها',
        subtitle: 'اعلان‌های جدید',
        icon: '🔔',
        color: 'from-green-500 to-emerald-500',
        href: '/activity',
        pulse: false
    }
];
</script>

<template>
    <div class="quick-actions bg-white border border-gray-200 rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">اقدامات سریع</h3>
        </div>

        <!-- Actions Grid -->
        <div class="p-4 space-y-2">
            <Link
                v-for="action in actions"
                :key="action.id"
                :href="action.href"
                class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded transition-colors duration-200"
            >
                <div class="flex items-center gap-3">
                    <span class="text-gray-600">{{ action.icon }}</span>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ action.title }}</div>
                        <div class="text-xs text-gray-500">{{ action.subtitle }}</div>
                    </div>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </Link>
        </div>
    </div>
</template>

<style scoped>
/* Mobile optimizations */
@media (max-width: 640px) {
    .quick-actions {
        border-radius: 0.5rem;
    }
}
</style>
