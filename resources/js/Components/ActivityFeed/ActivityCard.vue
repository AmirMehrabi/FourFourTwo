<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations.js';

const { translateTeamName, t } = useTranslations();

const props = defineProps({
    activity: {
        type: Object,
        required: true
    }
});

const activityIcon = computed(() => {
    switch (props.activity.activity_type) {
        case 'prediction':
            return '⚽';
        case 'badge_earned':
            return '🏅';
        case 'follow':
            return '👥';
        default:
            return '📱';
    }
});

const activityColor = computed(() => {
    switch (props.activity.activity_type) {
        case 'prediction':
            return 'from-blue-500 to-blue-600';
        case 'badge_earned':
            return 'from-amber-500 to-orange-500';
        case 'follow':
            return 'from-purple-500 to-pink-500';
        default:
            return 'from-gray-500 to-gray-600';
    }
});

const badgeTierColor = computed(() => {
    if (props.activity.activity_type !== 'badge_earned') return '';
    
    const tier = props.activity.data?.badge_tier;
    switch (tier) {
        case 'diamond':
            return 'from-cyan-400 to-blue-500';
        case 'gold':
            return 'from-yellow-400 to-orange-500';
        case 'silver':
            return 'from-gray-300 to-gray-500';
        case 'bronze':
            return 'from-orange-400 to-red-500';
        default:
            return 'from-gray-400 to-gray-600';
    }
});

const timeAgo = computed(() => {
    const date = new Date(props.activity.activity_date);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'الان';
    if (diffInMinutes < 60) return `${diffInMinutes} دقیقه پیش`;
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} ساعت پیش`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} روز پیش`;
    
    return date.toLocaleDateString('fa-IR');
});

const userInitials = computed(() => {
    if (!props.activity.user?.name) return '?';
    return props.activity.user.name.split(' ').map(n => n[0]).join('').slice(0, 2);
});
</script>

<template>
    <div class="activity-card bg-white border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-200">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-3">
            <!-- User Avatar -->
            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 text-sm font-medium">
                {{ userInitials }}
            </div>
            
            <!-- User info and time -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-900 truncate">
                        {{ activity.user?.name || 'کاربر ناشناس' }}
                    </span>
                    <span class="text-xs text-gray-500 shrink-0">{{ timeAgo }}</span>
                </div>
            </div>
            
            <!-- Activity type indicator -->
            <div class="text-gray-400 text-sm">
                {{ activityIcon }}
            </div>
        </div>

        <!-- Activity Content -->
        <div class="text-sm text-gray-700">
            <!-- Prediction Activity -->
            <div v-if="activity.activity_type === 'prediction'">
                <span class="text-gray-600">پیش‌بینی کرد:</span>
                <span class="font-medium text-gray-900 mr-1">
                    {{ translateTeamName(activity.data.fixture?.home_team) }}
                </span>
                <span class="text-gray-500">vs</span>
                <span class="font-medium text-gray-900 mr-1">
                    {{ translateTeamName(activity.data.fixture?.away_team) }}
                </span>
                <span class="font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded text-xs">
                    {{ activity.data.home_score_predicted }}-{{ activity.data.away_score_predicted }}
                </span>
            </div>

            <!-- Badge Activity -->
            <div v-else-if="activity.activity_type === 'badge_earned'">
                <span class="text-gray-600">مدال جدید کسب کرد:</span>
                <span class="font-medium text-gray-900 mr-1">{{ activity.data.badge_name }}</span>
                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                    {{ activity.data.badge_tier }}
                </span>
            </div>

            <!-- Follow Activity -->
            <div v-else-if="activity.activity_type === 'follow'">
                <span class="text-gray-600">دنبال کرد:</span>
                <span class="font-medium text-gray-900">{{ activity.data.following_user_name }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.activity-card {
    transition: background-color 0.2s ease;
}

@media (max-width: 640px) {
    .activity-card {
        padding: 0.875rem;
    }
}
</style>
