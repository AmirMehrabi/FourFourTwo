<template>
    <Head title="فعالیت‌ها" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800">فعالیت‌ها و اعلان‌ها</h2>
                    <p class="text-sm text-gray-600 mt-1">همه اعلان‌ها و فعالیت‌های شما</p>
                </div>
                <div class="hidden sm:flex items-center gap-3">
                    <div class="text-xs text-gray-500">
                        {{ stats.total_notifications }} اعلان کل
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-blue-600 mb-1">{{ stats.total_notifications }}</div>
                        <div class="text-xs text-gray-600">کل اعلان‌ها</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-green-600 mb-1">{{ stats.comments_count }}</div>
                        <div class="text-xs text-gray-600">نظرات شما</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-purple-600 mb-1">{{ stats.predictions_count }}</div>
                        <div class="text-xs text-gray-600">پیش‌بینی‌ها</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-orange-600 mb-1">{{ unreadCount }}</div>
                        <div class="text-xs text-gray-600">خوانده نشده</div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="bg-white rounded-xl border border-gray-100 mb-6 overflow-hidden">
                    <div class="flex border-b border-gray-100">
                        <button
                            v-for="filter in filters"
                            :key="filter.key"
                            @click="activeFilter = filter.key"
                            class="flex-1 px-4 py-3 text-sm font-medium transition-colors duration-200"
                            :class="activeFilter === filter.key 
                                ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-500' 
                                : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50'"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <component :is="filter.icon" class="w-4 h-4" />
                                <span class="hidden sm:inline">{{ filter.label }}</span>
                                <span class="sm:hidden">{{ filter.shortLabel }}</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Activity Feed -->
                <div class="space-y-4">
                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-8">
                        <svg class="w-8 h-8 animate-spin mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-500">در حال بارگذاری...</p>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="filteredNotifications.length === 0" class="text-center py-12">
                        <div class="bg-white rounded-xl border border-gray-100 p-8">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a8.38 8.38 0 01-1.5-5V7a6 6 0 10-12 0v1.5c0 2.215-.553 4.315-1.5 5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                {{ activeFilter === 'all' ? 'هیچ اعلانی وجود ندارد' : 'هیچ اعلانی در این دسته وجود ندارد' }}
                            </h3>
                            <p class="text-gray-600 mb-6">
                                {{ activeFilter === 'all' 
                                    ? 'زمانی که کسی به نظرات شما پاسخ دهد یا واکنش نشان دهد، اینجا نمایش داده می‌شود.' 
                                    : 'اعلان‌های این نوع اینجا نمایش داده خواهند شد.' 
                                }}
                            </p>
                            <Link 
                                :href="route('fixtures.index')" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                شروع نظردهی
                            </Link>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div v-else class="space-y-3">
                        <ActivityFeedItem
                            v-for="notification in filteredNotifications"
                            :key="notification.id"
                            :notification="notification"
                            @click="handleNotificationClick(notification)"
                            class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200"
                        />
                    </div>

                    <!-- Load More Button -->
                    <div v-if="hasMorePages" class="text-center py-6">
                        <button
                            @click="loadMore"
                            :disabled="loadingMore"
                            class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200 disabled:opacity-50"
                        >
                            <span v-if="loadingMore" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                در حال بارگذاری...
                            </span>
                            <span v-else>نمایش بیشتر</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ActivityFeedItem from '@/Components/ActivityFeedItem.vue';
import axios from 'axios';

const props = defineProps({
    notifications: Object,
    stats: Object,
});

// State
const loading = ref(false);
const loadingMore = ref(false);
const activeFilter = ref('all');
const allNotifications = ref(props.notifications.data || []);
const currentPage = ref(props.notifications.current_page || 1);
const lastPage = ref(props.notifications.last_page || 1);

// Filter options
const filters = [
    {
        key: 'all',
        label: 'همه اعلان‌ها',
        shortLabel: 'همه',
        icon: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M15 17h5l-3.5-3.5a8.38 8.38 0 01-1.5-5V7a6 6 0 10-12 0v1.5c0 2.215-.553 4.315-1.5 5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9'
        }))
    },
    {
        key: 'comment_reply',
        label: 'پاسخ‌ها',
        shortLabel: 'پاسخ',
        icon: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'
        }))
    },
    {
        key: 'comment_reaction',
        label: 'واکنش‌ها',
        shortLabel: 'واکنش',
        icon: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'
        }))
    },
    {
        key: 'match_update',
        label: 'نتایج مسابقات',
        shortLabel: 'نتایج',
        icon: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
        }))
    }
];

// Computed
const filteredNotifications = computed(() => {
    if (activeFilter.value === 'all') {
        return allNotifications.value;
    }
    return allNotifications.value.filter(notification => 
        notification.type === activeFilter.value
    );
});

const hasMorePages = computed(() => currentPage.value < lastPage.value);

const unreadCount = computed(() => {
    return allNotifications.value.filter(n => !n.read_at).length;
});

// Methods
const loadMore = async () => {
    if (hasMorePages.value && !loadingMore.value) {
        try {
            loadingMore.value = true;
            const response = await axios.get('/api/notifications', {
                params: { page: currentPage.value + 1 }
            });
            
            allNotifications.value.push(...response.data.data);
            currentPage.value = response.data.current_page;
            lastPage.value = response.data.last_page;
        } catch (error) {
            console.error('Error loading more notifications:', error);
        } finally {
            loadingMore.value = false;
        }
    }
};

const handleNotificationClick = (notification) => {
    // Navigate based on notification type
    if (notification.type === 'comment_reply' && notification.data.fixture_id) {
        window.location.href = `/fixtures/${notification.data.fixture_id}`;
    }
    // Add more navigation logic for other notification types
};
</script>

<style scoped>
/* Custom animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.activity-item {
    animation: fadeIn 0.3s ease-out;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .filter-tabs {
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .filter-tabs::-webkit-scrollbar {
        display: none;
    }
}
</style>
