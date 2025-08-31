<template>
    <div
        @click="handleNotificationClick"
        class="p-4 hover:bg-gray-50 cursor-pointer transition-colors duration-200"
        :class="{ 'bg-blue-50 border-l-4 border-blue-500': !notification.read_at }"
    >
        <div class="flex items-start gap-3">
            <!-- Notification Icon -->
            <div class="flex-shrink-0 mt-1">
                <div 
                    class="w-8 h-8 rounded-full flex items-center justify-center"
                    :class="getNotificationIconClass(notification.type)"
                >
                    <component :is="getNotificationIcon(notification.type)" class="w-4 h-4" />
                </div>
            </div>

            <!-- Notification Content -->
            <div class="flex-1 min-w-0">
                <!-- Message -->
                <div class="text-sm text-gray-900 mb-1">
                    <span class="font-semibold">{{ getNotificationMessage(notification) }}</span>
                </div>

                <!-- Context -->
                <div v-if="getNotificationContext(notification)" class="text-xs text-gray-600 mb-2 line-clamp-2">
                    {{ getNotificationContext(notification) }}
                </div>

                <!-- Meta Information -->
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        {{ formatTimeAgo(notification.created_at) }}
                    </div>
                    
                    <!-- Mark as Read Button -->
                    <button
                        v-if="!notification.read_at"
                        @click.stop="$emit('mark-as-read')"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                    >
                        علامت‌گذاری
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { h } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    notification: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['click', 'mark-as-read']);

// Methods
const handleNotificationClick = () => {
    const { type, data } = props.notification;
    
    // Navigate based on notification type
    switch (type) {
        case 'comment_reply':
        case 'comment_reaction':
        case 'mention':
            if (data.fixture_id) {
                router.visit(route('fixtures.show', data.fixture_id) + (data.comment_id ? `#comment-${data.comment_id}` : ''));
            }
            break;
        case 'badge_awarded':
            // Navigate to profile or achievements page
            router.visit(route('profile.edit'));
            break;
        case 'new_follower':
            // Navigate to user's profile
            if (data.follower_username) {
                router.visit(`/@${data.follower_username}`);
            }
            break;
        default:
            // Emit click event for other notification types
            emit('click');
    }
};

const getNotificationIcon = (type) => {
    const icons = {
        comment_reply: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'
        })),
        
        comment_reaction: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'
        })),
        
        mention: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'
        })),
        
        friend_request: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        })),
        
        match_update: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
        })),
        
        badge_awarded: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'
        })),
        
        new_follower: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        }))
    };
    
    return icons[type] || icons.comment_reply;
};

const getNotificationIconClass = (type) => {
    const classes = {
        comment_reply: 'bg-blue-100 text-blue-600',
        comment_reaction: 'bg-red-100 text-red-600',
        mention: 'bg-purple-100 text-purple-600',
        friend_request: 'bg-green-100 text-green-600',
        match_update: 'bg-yellow-100 text-yellow-600',
        badge_awarded: 'bg-orange-100 text-orange-600',
        new_follower: 'bg-indigo-100 text-indigo-600'
    };
    
    return classes[type] || classes.comment_reply;
};

const getNotificationMessage = (notification) => {
    const { type, data } = notification;
    
    switch (type) {
        case 'comment_reply':
            return `${data.replier_name} به نظر شما پاسخ داد`;
        case 'comment_reaction':
            return `${data.reactor_name} به نظر شما واکنش نشان داد`;
        case 'mention':
            return `${data.commenter_name} شما را در نظر خود ذکر کرد`;
        case 'friend_request':
            return `${data.requester_name} درخواست دوستی فرستاد`;
        case 'match_update':
            return `نتیجه مسابقه ${data.home_team} در برابر ${data.away_team} اعلام شد`;
        case 'badge_awarded':
            return `شما نشان "${data.badge_name}" را دریافت کردید!`;
        case 'new_follower':
            return `${data.follower_name} شما را دنبال کرد`;
        default:
            return 'اعلان جدید';
    }
};

const getNotificationContext = (notification) => {
    const { type, data } = notification;
    
    switch (type) {
        case 'comment_reply':
            return `در مسابقه ${data.fixture_teams?.home} در برابر ${data.fixture_teams?.away}: "${data.content}"`;
        case 'comment_reaction':
            return `"${data.comment_content}"`;
        case 'mention':
            const truncatedContent = data.comment_content?.length > 100 
                ? data.comment_content.substring(0, 100) + '...' 
                : data.comment_content;
            return `در مسابقه ${data.fixture_teams?.home} در برابر ${data.fixture_teams?.away}: "${truncatedContent}"`;
        case 'friend_request':
            return 'می‌توانید درخواست را از بخش دوستان مدیریت کنید';
        case 'match_update':
            return `نتیجه نهایی: ${data.final_score}`;
        case 'badge_awarded':
            return data.badge_description || 'تبریک! شما یک موفقیت جدید کسب کردید.';
        case 'new_follower':
            return `اکنون ${data.follower_count || 1} نفر شما را دنبال می‌کنند`;
        default:
            return '';
    }
};

const formatTimeAgo = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'همین الان';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} دقیقه پیش`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} ساعت پیش`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} روز پیش`;
    
    return date.toLocaleDateString('fa-IR');
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
