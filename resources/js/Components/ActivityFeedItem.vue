<template>
    <div
        @click="$emit('click')"
        class="p-4 cursor-pointer transition-all duration-200 hover:bg-gray-50"
        :class="{ 'border-l-4 border-blue-500': !notification.read_at }"
    >
        <div class="flex items-start gap-4">
            <!-- Notification Icon & Avatar -->
            <div class="flex-shrink-0 relative">
                <!-- User Avatar (if available) -->
                <div v-if="shouldShowUserAvatar && hasUserAvatar" class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                    {{ getUserInitials() }}
                </div>
                
                <!-- Notification Type Icon -->
                <div 
                    v-if="!shouldShowUserAvatar"
                    class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center text-white font-semibold text-sm"
                >
                    <component :is="getNotificationIcon()" class="w-6 h-6" />
                </div>
                <div 
                    v-else
                    class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center"
                    :class="getNotificationIconClass()"
                >
                    <component :is="getNotificationIcon()" class="w-3 h-3" />
                </div>
            </div>

            <!-- Notification Content -->
            <div class="flex-1 min-w-0">
                <!-- Header -->
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">
                            {{ getNotificationTitle() }}
                        </h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            {{ getNotificationMessage() }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <div class="text-xs text-gray-500">
                            {{ formatTimeAgo(notification.created_at) }}
                        </div>
                        <div v-if="!notification.read_at" class="mt-1">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Context Card (for comment replies) -->
                <div v-if="showContextCard" class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-start gap-2">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-gray-600 mb-1">
                                {{ getContextLabel() }}
                            </div>
                            <div class="text-sm text-gray-800 line-clamp-2">
                                "{{ getContextContent() }}"
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Match Context (for match updates) -->
                <div v-if="notification.type === 'match_update'" class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-green-800">نتیجه نهایی</span>
                        </div>
                        <div class="text-sm font-bold text-green-900">
                            {{ notification.data.final_score || 'نتیجه اعلام شد' }}
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-3 flex items-center gap-3">
                    <button
                        v-if="notification.type === 'comment_reply'"
                        @click.stop="$emit('click')"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        مشاهده و پاسخ
                    </button>
                    
                    <button
                        v-if="notification.type === 'mention'"
                        @click.stop="$emit('click')"
                        class="text-xs text-purple-600 hover:text-purple-700 font-medium flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        مشاهده منشن
                    </button>
                    
                    <button
                        v-if="notification.type === 'friend_request'"
                        @click.stop="$emit('click')"
                        class="text-xs text-green-600 hover:text-green-700 font-medium flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        مشاهده درخواست
                    </button>
                    
                    <button
                        v-if="notification.type === 'match_update'"
                        @click.stop="$emit('click')"
                        class="text-xs text-green-600 hover:text-green-700 font-medium flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        مشاهده آمار
                    </button>
                    
                    <button
                        v-if="notification.type === 'new_follower'"
                        @click.stop="viewFollowerProfile"
                        class="text-xs text-purple-600 hover:text-purple-700 font-medium flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        مشاهده پروفایل
                    </button>
                    
                    <button
                        v-if="notification.type === 'achievement_unlocked'"
                        @click.stop="viewAchievements"
                        class="text-xs text-yellow-600 hover:text-yellow-700 font-medium flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.706 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.706 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.706 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.706 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.706-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.706-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        مشاهده دستاوردها
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, h } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    notification: {
        type: Object,
        required: true
    }
});

const page = usePage();

defineEmits(['click']);

// Computed
const hasUserAvatar = computed(() => {
    return props.notification.data.replier_name || 
           props.notification.data.reactor_name || 
           props.notification.data.requester_name ||
           props.notification.data.follower_name ||
           props.notification.data.commenter_name ||
           props.notification.data.user_name;
});

const showContextCard = computed(() => {
    return (props.notification.type === 'comment_reply' || props.notification.type === 'mention') && props.notification.data.content;
});

const shouldShowUserAvatar = computed(() => {
    return ['comment_reply', 'comment_reaction', 'mention', 'friend_request', 'new_follower'].includes(props.notification.type);
});

// Methods
const getUserInitials = () => {
    const name = props.notification.data.replier_name || 
                 props.notification.data.reactor_name || 
                 props.notification.data.requester_name || 
                 props.notification.data.follower_name ||
                 props.notification.data.commenter_name ||
                 props.notification.data.user_name ||
                 'کاربر';
    
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2) || 'ک';
};

const getNotificationIcon = () => {
    const { type } = props.notification;
    
    const icons = {
        comment_reply: () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 20 20'
        }, h('path', {
            'fill-rule': 'evenodd',
            d: 'M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z',
            'clip-rule': 'evenodd'
        })),
        
        comment_reaction: () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 20 20'
        }, h('path', {
            'fill-rule': 'evenodd',
            d: 'M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z',
            'clip-rule': 'evenodd'
        })),
        
        friend_request: () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 20 20'
        }, h('path', {
            'fill-rule': 'evenodd',
            d: 'M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z',
            'clip-rule': 'evenodd'
        })),
        
        match_update: () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 20 20'
        }, h('path', {
            'fill-rule': 'evenodd',
            d: 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z',
            'clip-rule': 'evenodd'
        })),
        
        new_follower: () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 20 20'
        }, h('path', {
            'fill-rule': 'evenodd',
            d: 'M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z',
            'clip-rule': 'evenodd'
        })),
        
        mention: () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 20 20'
        }, h('path', {
            'fill-rule': 'evenodd',
            d: 'M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z',
            'clip-rule': 'evenodd'
        })),
        
        achievement_unlocked: () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 20 20'
        }, h('path', {
            'fill-rule': 'evenodd',
            d: 'M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
            'clip-rule': 'evenodd'
        }))
    };
    
    return icons[type] || icons.comment_reply;
};

const getNotificationIconClass = () => {
    const { type } = props.notification;
    
    const classes = {
        comment_reply: 'bg-blue-500 text-white',
        comment_reaction: 'bg-red-500 text-white',
        mention: 'bg-purple-500 text-white',
        friend_request: 'bg-green-500 text-white',
        new_follower: 'bg-purple-500 text-white',
        achievement_unlocked: 'bg-yellow-500 text-white',
        match_update: 'bg-orange-500 text-white'
    };
    
    return classes[type] || classes.comment_reply;
};

const getNotificationTitle = () => {
    const { type, data } = props.notification;
    
    switch (type) {
        case 'comment_reply':
            return 'پاسخ جدید به نظر شما';
        case 'comment_reaction':
            return 'واکنش به نظر شما';
        case 'mention':
            return 'منشن شدن در نظر';
        case 'friend_request':
            return 'درخواست دوستی جدید';
        case 'new_follower':
            return 'دنبال‌کننده جدید';
        case 'achievement_unlocked':
            return 'دستاورد جدید';
        case 'match_update':
            return 'به‌روزرسانی نتیجه مسابقه';
        default:
            return 'اعلان جدید';
    }
};

const getNotificationMessage = () => {
    const { type, data } = props.notification;
    
    switch (type) {
        case 'comment_reply':
            return `${data.replier_name} به نظر شما در مسابقه ${data.fixture_teams?.home} در برابر ${data.fixture_teams?.away} پاسخ داد.`;
        case 'comment_reaction':
            return `${data.reactor_name} به نظر شما واکنش ${getReactionEmoji(data.reaction_type)} نشان داد.`;
        case 'mention':
            return `${data.commenter_name} شما را در نظری در مسابقه ${data.fixture_teams?.home} در برابر ${data.fixture_teams?.away} منشن کرد.`;
        case 'friend_request':
            return `${data.requester_name} درخواست دوستی برای شما فرستاده است.`;
        case 'new_follower':
            return `${data.follower_name} شما را دنبال کرد.`;
        case 'achievement_unlocked':
            return `شما دستاورد "${data.achievement_name}" را کسب کردید!`;
        case 'match_update':
            return `نتیجه مسابقه ${data.home_team} در برابر ${data.away_team} اعلام شد.`;
        default:
            return 'شما اعلان جدیدی دارید.';
    }
};

const getContextLabel = () => {
    const { type } = props.notification;
    
    switch (type) {
        case 'comment_reply':
            return 'نظر اصلی شما:';
        case 'comment_reaction':
            return 'نظر شما:';
        case 'mention':
            return 'نظر منشن شده:';
        default:
            return 'محتوا:';
    }
};

const getContextContent = () => {
    const { data } = props.notification;
    return data.content || data.comment_content || '';
};

const getReactionEmoji = (type) => {
    const emojis = {
        like: '👍',
        love: '❤️',
        laugh: '😂',
        angry: '😠',
        sad: '😢'
    };
    return emojis[type] || '👍';
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

// Navigation functions
const viewFollowerProfile = () => {
    const username = props.notification.data.follower_username;
    if (username) {
        router.visit(`/@${username}`);
    }
};

const viewAchievements = () => {
    const username = page.props.auth.user.username;
    if (username) {
        router.visit(`/@${username}`);
    }
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth hover animations */
.cursor-pointer:hover {
    transform: translateY(-1px);
}

/* Mobile touch optimization */
@media (max-width: 768px) {
    .cursor-pointer {
        min-height: 44px;
    }
}
</style>
