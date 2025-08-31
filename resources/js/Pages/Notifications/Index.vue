<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        اعلان‌ها
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        تمام اعلان‌های شما در یک مکان
                    </p>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        :disabled="markingAllAsRead"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="markingAllAsRead" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        علامت‌گذاری همه
                    </button>
                    
                    <button
                        @click="refreshNotifications"
                        :disabled="loading"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        بروزرسانی
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Type Filter -->
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    نوع اعلان
                                </label>
                                <select
                                    v-model="filters.type"
                                    @change="applyFilters"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                                    <option value="">همه انواع</option>
                                    <option value="comment_reply">پاسخ به نظر</option>
                                    <option value="comment_reaction">واکنش به نظر</option>
                                    <option value="mention">ذکر شدن</option>
                                    <option value="friend_request">درخواست دوستی</option>
                                    <option value="match_update">بروزرسانی مسابقه</option>
                                    <option value="badge_awarded">دریافت نشان</option>
                                    <option value="new_follower">دنبال‌کننده جدید</option>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    وضعیت
                                </label>
                                <select
                                    v-model="filters.status"
                                    @change="applyFilters"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                                    <option value="">همه</option>
                                    <option value="unread">خوانده نشده</option>
                                    <option value="read">خوانده شده</option>
                                </select>
                            </div>

                            <!-- Date Filter -->
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    بازه زمانی
                                </label>
                                <select
                                    v-model="filters.dateRange"
                                    @change="applyFilters"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                                    <option value="">همه زمان‌ها</option>
                                    <option value="today">امروز</option>
                                    <option value="week">هفته گذشته</option>
                                    <option value="month">ماه گذشته</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div class="divide-y divide-gray-200">
                        <!-- Loading State -->
                        <div v-if="loading" class="p-8 text-center">
                            <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-500">در حال بارگذاری اعلان‌ها...</p>
                        </div>

                        <!-- Empty State -->
                        <div v-else-if="filteredNotifications.length === 0" class="p-8 text-center">
                            <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a8.38 8.38 0 01-1.5-5V7a6 6 0 10-12 0v1.5c0 2.215-.553 4.315-1.5 5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                {{ hasActiveFilters ? 'هیچ اعلانی با فیلترهای انتخاب شده یافت نشد' : 'هیچ اعلانی وجود ندارد' }}
                            </h3>
                            <p class="text-gray-500 mb-4">
                                {{ hasActiveFilters ? 'فیلترهای خود را تغییر دهید یا همه فیلترها را پاک کنید' : 'زمانی که فعالیت جدیدی رخ دهد، اینجا نمایش داده می‌شود' }}
                            </p>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                پاک کردن فیلترها
                            </button>
                        </div>

                        <!-- Notifications -->
                        <div v-else>
                            <div
                                v-for="notification in paginatedNotifications"
                                :key="notification.id"
                                class="p-6 hover:bg-gray-50 transition-colors duration-200"
                                :class="{ 'bg-blue-50 border-l-4 border-blue-500': !notification.read_at }"
                            >
                                <NotificationItem
                                    :notification="notification"
                                    @click="handleNotificationClick(notification)"
                                    @mark-as-read="handleMarkAsRead(notification)"
                                />
                            </div>

                            <!-- Pagination -->
                            <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        نمایش {{ (currentPage - 1) * perPage + 1 }} تا {{ Math.min(currentPage * perPage, totalNotifications) }} از {{ totalNotifications }} اعلان
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <button
                                            @click="goToPage(currentPage - 1)"
                                            :disabled="currentPage === 1"
                                            class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            قبلی
                                        </button>
                                        
                                        <template v-for="page in visiblePages" :key="page">
                                            <button
                                                v-if="page !== '...'"
                                                @click="goToPage(page)"
                                                :class="[
                                                    'relative inline-flex items-center px-3 py-2 text-sm font-medium rounded-md',
                                                    page === currentPage
                                                        ? 'z-10 bg-blue-600 text-white'
                                                        : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
                                                ]"
                                            >
                                                {{ page }}
                                            </button>
                                            <span v-else class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white">
                                                ...
                                            </span>
                                        </template>
                                        
                                        <button
                                            @click="goToPage(currentPage + 1)"
                                            :disabled="currentPage === totalPages"
                                            class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            بعدی
                                        </button>
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
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NotificationItem from '@/Components/NotificationItem.vue';
import axios from 'axios';

// State
const notifications = ref([]);
const loading = ref(false);
const markingAllAsRead = ref(false);
const unreadCount = ref(0);

// Pagination
const currentPage = ref(1);
const perPage = ref(20);
const totalNotifications = ref(0);

// Filters
const filters = ref({
    type: '',
    status: '',
    dateRange: ''
});

// Computed
const totalPages = computed(() => Math.ceil(totalNotifications.value / perPage.value));

const filteredNotifications = computed(() => {
    let filtered = [...notifications.value];
    
    // Type filter
    if (filters.value.type) {
        filtered = filtered.filter(n => n.type === filters.value.type);
    }
    
    // Status filter
    if (filters.value.status === 'unread') {
        filtered = filtered.filter(n => !n.read_at);
    } else if (filters.value.status === 'read') {
        filtered = filtered.filter(n => n.read_at);
    }
    
    // Date filter
    if (filters.value.dateRange) {
        const now = new Date();
        const filterDate = new Date();
        
        switch (filters.value.dateRange) {
            case 'today':
                filterDate.setHours(0, 0, 0, 0);
                break;
            case 'week':
                filterDate.setDate(now.getDate() - 7);
                break;
            case 'month':
                filterDate.setMonth(now.getMonth() - 1);
                break;
        }
        
        filtered = filtered.filter(n => new Date(n.created_at) >= filterDate);
    }
    
    return filtered;
});

const paginatedNotifications = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredNotifications.value.slice(start, end);
});

const hasActiveFilters = computed(() => {
    return filters.value.type || filters.value.status || filters.value.dateRange;
});

const visiblePages = computed(() => {
    const pages = [];
    const maxVisible = 5;
    
    if (totalPages.value <= maxVisible) {
        for (let i = 1; i <= totalPages.value; i++) {
            pages.push(i);
        }
    } else {
        if (currentPage.value <= 3) {
            for (let i = 1; i <= 4; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(totalPages.value);
        } else if (currentPage.value >= totalPages.value - 2) {
            pages.push(1);
            pages.push('...');
            for (let i = totalPages.value - 3; i <= totalPages.value; i++) {
                pages.push(i);
            }
        } else {
            pages.push(1);
            pages.push('...');
            for (let i = currentPage.value - 1; i <= currentPage.value + 1; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(totalPages.value);
        }
    }
    
    return pages;
});

// Methods
const loadNotifications = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/notifications', {
            params: {
                limit: 1000, // Get all notifications for filtering
                page: 1
            }
        });
        
        notifications.value = response.data.data || [];
        totalNotifications.value = response.data.total || notifications.value.length;
        updateUnreadCount();
    } catch (error) {
        console.error('Error loading notifications:', error);
    } finally {
        loading.value = false;
    }
};

const loadUnreadCount = async () => {
    try {
        const response = await axios.get('/api/notifications/unread-count');
        unreadCount.value = response.data.count || 0;
    } catch (error) {
        console.error('Error loading unread count:', error);
    }
};

const updateUnreadCount = () => {
    unreadCount.value = notifications.value.filter(n => !n.read_at).length;
};

const markAllAsRead = async () => {
    try {
        markingAllAsRead.value = true;
        await axios.post('/api/notifications/mark-all-read');
        
        notifications.value.forEach(notification => {
            notification.read_at = new Date().toISOString();
        });
        
        unreadCount.value = 0;
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    } finally {
        markingAllAsRead.value = false;
    }
};

const handleMarkAsRead = async (notification) => {
    if (notification.read_at) return;
    
    try {
        await axios.post(`/api/notifications/${notification.id}/read`);
        notification.read_at = new Date().toISOString();
        updateUnreadCount();
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const handleNotificationClick = async (notification) => {
    // Mark as read if unread
    if (!notification.read_at) {
        await handleMarkAsRead(notification);
    }
    
    // Navigate based on notification type
    switch (notification.type) {
        case 'comment_reply':
        case 'comment_reaction':
        case 'mention':
            if (notification.data.fixture_id) {
                router.visit(route('fixtures.show', notification.data.fixture_id));
            }
            break;
        case 'badge_awarded':
            router.visit(route('profile.edit'));
            break;
        case 'new_follower':
            if (notification.data.follower_username) {
                router.visit(`/@${notification.data.follower_username}`);
            }
            break;
        default:
            // For other types, stay on the page
            break;
    }
};

const refreshNotifications = () => {
    loadNotifications();
    loadUnreadCount();
};

const applyFilters = () => {
    currentPage.value = 1; // Reset to first page when filters change
};

const clearFilters = () => {
    filters.value = {
        type: '',
        status: '',
        dateRange: ''
    };
    currentPage.value = 1;
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Watch for filter changes
watch(filters, () => {
    applyFilters();
}, { deep: true });

// Lifecycle
onMounted(() => {
    loadNotifications();
    loadUnreadCount();
});
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
