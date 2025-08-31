<template>
    <div class="relative notification-bell">
        <!-- Notification Bell Button -->
        <button
            @click="toggleNotifications"
            class="relative p-2 text-gray-600 hover:text-gray-800 transition-colors duration-200"
            :class="{ 'text-blue-600': hasUnreadNotifications }"
        >
            <!-- Bell Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a8.38 8.38 0 01-1.5-5V7a6 6 0 10-12 0v1.5c0 2.215-.553 4.315-1.5 5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            
            <!-- Unread Count Badge -->
            <div
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 min-w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </div>
        </button>

        <!-- Notifications Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="showNotifications"
                class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50"
                style="transform: translateX(-75%);"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">اعلان‌ها</h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        علامت‌گذاری همه
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="loading" class="p-4 text-center text-gray-500">
                        <svg class="w-5 h-5 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال بارگذاری...
                    </div>

                    <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a8.38 8.38 0 01-1.5-5V7a6 6 0 10-12 0v1.5c0 2.215-.553 4.315-1.5 5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="font-medium">اعلانی وجود ندارد</p>
                        <p class="text-sm mt-1">زمانی که کسی به نظرات شما پاسخ دهد، اینجا نمایش داده می‌شود</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <NotificationItem
                            v-for="notification in notifications"
                            :key="notification.id"
                            :notification="notification"
                            @click="handleNotificationClick(notification)"
                            @mark-as-read="handleMarkAsRead(notification)"
                        />
                    </div>
                </div>

                <!-- View All Link -->
                <div v-if="notifications.length > 0" class="p-3 border-t border-gray-100 text-center">
                    <Link
                        :href="route('notifications.index')"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        @click="showNotifications = false"
                    >
                        مشاهده همه اعلان‌ها
                    </Link>
                </div>
            </div>
        </Transition>

        <!-- Backdrop -->
        <div
            v-if="showNotifications"
            @click="showNotifications = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import NotificationItem from './NotificationItem.vue';

// State
const showNotifications = ref(false);
const notifications = ref([]);
const loading = ref(false);
const unreadCount = ref(0);

// Computed
const hasUnreadNotifications = computed(() => unreadCount.value > 0);

// Methods
const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value && notifications.value.length === 0) {
        loadNotifications();
    }
};

const loadNotifications = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/notifications?limit=10');
        notifications.value = response.data.data || [];
        updateUnreadCount();
    } catch (error) {
        console.error('Error loading notifications:', error);
    } finally {
        loading.value = false;
    }
};

const updateUnreadCount = () => {
    unreadCount.value = notifications.value.filter(n => !n.read_at).length;
};

const markAllAsRead = async () => {
    try {
        await axios.post('/api/notifications/mark-all-read');
        notifications.value.forEach(notification => {
            notification.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
    } catch (error) {
        console.error('Error marking notifications as read:', error);
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
            if (notification.data.fixture_id) {
                window.location.href = `/fixtures/${notification.data.fixture_id}`;
            }
            break;
        case 'badge_awarded':
            // Navigate to profile or achievements page
            window.location.href = route('profile.edit');
            break;
        case 'new_follower':
            // Navigate to user's profile
            if (notification.data.follower_username) {
                window.location.href = `/@${notification.data.follower_username}`;
            }
            break;
        default:
            // For other types, let the NotificationItem handle navigation
            break;
    }
    
    showNotifications.value = false;
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

// Load initial unread count
const loadUnreadCount = async () => {
    try {
        const response = await axios.get('/api/notifications/unread-count');
        unreadCount.value = response.data.count || 0;
    } catch (error) {
        console.error('Error loading unread count:', error);
    }
};

// Lifecycle
onMounted(() => {
    loadUnreadCount();
    
    // Set up periodic refresh of unread count
    const interval = setInterval(loadUnreadCount, 30000); // Every 30 seconds
    
    // Close dropdown when clicking outside
    const handleClickOutside = (event) => {
        if (!event.target.closest('.notification-bell')) {
            showNotifications.value = false;
        }
    };
    
    document.addEventListener('click', handleClickOutside);
    
    onUnmounted(() => {
        clearInterval(interval);
        document.removeEventListener('click', handleClickOutside);
    });
});
</script>

<style scoped>
/* Custom scrollbar for notifications list */
.max-h-96::-webkit-scrollbar {
    width: 4px;
}

.max-h-96::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.max-h-96::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animation for unread badge */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.animate-pulse-custom {
    animation: pulse 2s infinite;
}
</style>
