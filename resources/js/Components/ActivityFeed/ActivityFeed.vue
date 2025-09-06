<script setup>
import { ref, computed } from 'vue';
import ActivityCard from './ActivityCard.vue';
import { useTranslations } from '@/composables/useTranslations.js';

const { t } = useTranslations();

const props = defineProps({
    activities: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: 'فعالیت‌های اخیر'
    },
    showHeader: {
        type: Boolean,
        default: true
    },
    maxHeight: {
        type: String,
        default: 'max-h-96'
    }
});

const isLoading = ref(false);

const hasActivities = computed(() => {
    return props.activities && props.activities.length > 0;
});

const activityTypes = computed(() => {
    if (!hasActivities.value) return {};
    
    return props.activities.reduce((acc, activity) => {
        acc[activity.activity_type] = (acc[activity.activity_type] || 0) + 1;
        return acc;
    }, {});
});
</script>

<template>
    <div class="activity-feed bg-white border border-gray-200 rounded-lg overflow-hidden">
        <!-- Header -->
        <div v-if="showHeader" class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">{{ title }}</h3>
                <span class="text-xs text-gray-500">{{ activities.length }} فعالیت</span>
            </div>
        </div>

        <!-- Feed Content -->
        <div class="relative">
            <!-- Loading State -->
            <div v-if="isLoading" class="p-6">
                <div class="space-y-4">
                    <div v-for="i in 3" :key="i" class="animate-pulse">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                            <div class="flex-1">
                                <div class="h-4 bg-gray-200 rounded w-1/3 mb-1"></div>
                                <div class="h-3 bg-gray-100 rounded w-1/4"></div>
                            </div>
                        </div>
                        <div class="h-20 bg-gray-100 rounded-xl"></div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!hasActivities" class="p-6 text-center">
                <div class="text-gray-400 text-2xl mb-2">📱</div>
                <h4 class="text-sm font-medium text-gray-900 mb-1">هنوز فعالیتی نیست</h4>
                <p class="text-xs text-gray-500">دوستان خود را دنبال کنید تا فعالیت‌هایشان را ببینید</p>
            </div>

            <!-- Activities List -->
            <div v-else class="overflow-y-auto" :class="maxHeight">
                <ActivityCard
                    v-for="activity in activities"
                    :key="`${activity.id}-${activity.activity_date}`"
                    :activity="activity"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Clean transitions */
.activity-enter-active,
.activity-leave-active {
    transition: all 0.2s ease;
}

.activity-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.activity-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.activity-move {
    transition: transform 0.2s ease;
}

/* Clean scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

@media (max-width: 640px) {
    .activity-feed {
        border-radius: 0.5rem;
    }
}
</style>
