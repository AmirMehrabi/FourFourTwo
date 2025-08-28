<template>
    <!-- Instagram-inspired Mobile Bottom Navigation -->
    <div class="fixed bottom-0 left-0 z-50 w-full bg-white/95 backdrop-blur-md border-t border-gray-100 sm:hidden">
        <div class="safe-area-padding-bottom">
            <div class="grid h-16 max-w-lg grid-cols-5 mx-auto">
                <!-- Home/Dashboard -->
            <Link
                :href="route('dashboard')"
                    class="flex flex-col items-center justify-center py-2 px-1 group relative overflow-hidden"
                    :class="{ 'text-gray-900': route().current('dashboard'), 'text-gray-500': !route().current('dashboard') }"
                >
                    <div class="relative mb-1">
                        <!-- Home Icon -->
                        <svg 
                            v-if="!route().current('dashboard')"
                            class="w-6 h-6 transition-all duration-300 group-active:scale-90"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <!-- Home Icon Filled -->
                        <svg 
                            v-else
                            class="w-6 h-6 transition-all duration-300"
                    fill="currentColor" 
                            viewBox="0 0 24 24"
                >
                            <path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 1-1.06 1.06L12 5.43 3.84 13.59a.75.75 0 0 1-1.06-1.06l8.69-8.69Z"/>
                            <path d="m12 5.43 8.25 8.25V19.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75v4.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-5.82L12 5.43Z"/>
                </svg>
                        <!-- Active indicator -->
                        <div v-if="route().current('dashboard')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-gray-900 rounded-full"></div>
                    </div>
                    <span class="text-xs font-medium" :class="{ 'text-gray-900': route().current('dashboard'), 'text-gray-500': !route().current('dashboard') }">
                        خانه
                </span>
            </Link>
            
                <!-- Fixtures/Matches -->
            <Link
                :href="route('fixtures.index')"
                    class="flex flex-col items-center justify-center py-2 px-1 group relative overflow-hidden"
                    :class="{ 'text-gray-900': route().current('fixtures.index'), 'text-gray-500': !route().current('fixtures.index') }"
                >
                    <div class="relative mb-1">
                        <!-- Calendar Icon -->
                        <svg 
                            v-if="!route().current('fixtures.index')"
                            class="w-6 h-6 transition-all duration-300 group-active:scale-90"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <!-- Calendar Icon Filled -->
                        <svg 
                            v-else
                            class="w-6 h-6 transition-all duration-300"
                    fill="currentColor" 
                            viewBox="0 0 24 24"
                >
                            <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd"/>
                </svg>
                        <!-- Active indicator -->
                        <div v-if="route().current('fixtures.index')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-gray-900 rounded-full"></div>
                    </div>
                    <span class="text-xs font-medium" :class="{ 'text-gray-900': route().current('fixtures.index'), 'text-gray-500': !route().current('fixtures.index') }">
                    مسابقات
                </span>
            </Link>
            

                <!-- My Predictions -->
            <Link
                :href="route('my-predictions.index')"
                    class="flex flex-col items-center justify-center py-2 px-1 group relative overflow-hidden"
                    :class="{ 'text-gray-900': route().current('my-predictions.index'), 'text-gray-500': !route().current('my-predictions.index') }"
                >
                    <div class="relative mb-1">
                        <!-- Heart Icon -->
                        <svg 
                            v-if="!route().current('my-predictions.index')"
                            class="w-6 h-6 transition-all duration-300 group-active:scale-90"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <!-- Heart Icon Filled -->
                        <svg 
                            v-else
                            class="w-6 h-6 transition-all duration-300 text-red-500"
                    fill="currentColor" 
                            viewBox="0 0 24 24"
                >
                            <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z"/>
                </svg>
                        <!-- Active indicator -->
                        <div v-if="route().current('my-predictions.index')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-red-500 rounded-full"></div>
                    </div>
                    <span class="text-xs font-medium" :class="{ 'text-red-500': route().current('my-predictions.index'), 'text-gray-500': !route().current('my-predictions.index') }">
                    پیش‌بینی‌ها
                </span>
            </Link>
                            <!-- Activities -->
                            <Link
                    :href="route('activity.index')"
                    class="flex flex-col items-center justify-center py-2 px-1 group relative overflow-hidden"
                    :class="{ 'text-gray-900': route().current('activity.index'), 'text-gray-500': !route().current('activity.index') }"
                >
                    <div class="relative mb-1">
                        <!-- Bell Icon -->
                        <svg 
                            v-if="!route().current('activity.index')"
                            class="w-6 h-6 transition-all duration-300 group-active:scale-90"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a8.38 8.38 0 01-1.5-5V7a6 6 0 10-12 0v1.5c0 2.215-.553 4.315-1.5 5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <!-- Bell Icon Filled -->
                        <svg 
                            v-else
                            class="w-6 h-6 transition-all duration-300 text-blue-500"
                            fill="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path d="M5.25 9a6.75 6.75 0 0112.5 0v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h9.19c1.73 0 2.813-1.874 1.948-3.374L13.949 9.75c-.866-1.5-3.032-1.5-3.898 0zM6.75 9.75v.01V9.75zm10.5-3.01v.01V6.74zm-13.5.01v.01V6.74z"/>
                        </svg>
                        <!-- Active indicator -->
                        <div v-if="route().current('activity.index')" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-blue-500 rounded-full"></div>
                        
                        <!-- Notification badge -->
                        <div v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border border-white"></div>
                    </div>
                    <span class="text-xs font-medium" :class="{ 'text-blue-600': route().current('activity.index'), 'text-gray-500': !route().current('activity.index') }">
                        فعالیت‌ها
                    </span>
                </Link>
                <!-- Profile with Logout -->
                <button
                    @click="toggleProfileMenu"
                    class="flex flex-col items-center justify-center py-2 px-1 group relative overflow-hidden"
                    :class="{ 'text-gray-900': showProfileMenu, 'text-gray-500': !showProfileMenu }"
                >
                    <div class="relative mb-1">
                        <!-- Profile Avatar -->
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold transition-all duration-300 group-active:scale-90"
                             :class="{ 'ring-2 ring-gray-900 ring-offset-2': showProfileMenu }">
                            {{ userInitials }}
                        </div>
                        <!-- Active indicator -->
                        <div v-if="showProfileMenu" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-gray-900 rounded-full"></div>
                        
                        <!-- Notification badge on profile -->
                        <div v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border border-white"></div>
                    </div>
                    <span class="text-xs font-medium" :class="{ 'text-gray-900': showProfileMenu, 'text-gray-500': !showProfileMenu }">
                        پروفایل
                    </span>
                </button>
            

            </div>
        </div>



        <!-- Profile Menu Overlay -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-full"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-full"
        >
            <div v-if="showProfileMenu" class="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm" @click="showProfileMenu = false">
                <div class="absolute bottom-16 right-0 left-0 bg-white rounded-t-3xl shadow-2xl border-t border-gray-100" @click.stop>
                    <div class="p-6">
                        <!-- Profile Header -->
                        <div class="flex items-center space-x-4 space-x-reverse mb-6">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xl font-bold">
                                {{ userInitials }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $page.props.auth.user.name }}</h3>
                                <p class="text-sm text-gray-500">{{ $page.props.auth.user.email }}</p>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="space-y-2">
                            <!-- Activity Feed -->


                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center space-x-3 space-x-reverse p-3 rounded-xl hover:bg-gray-50 transition-colors duration-200"
                                @click="showProfileMenu = false"
                            >
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-gray-900 font-medium">ویرایش پروفایل</span>
                            </Link>

                            <Link
                                :href="route('gameweek.index')"
                                class="flex items-center space-x-3 space-x-reverse p-3 rounded-xl hover:bg-gray-50 transition-colors duration-200"
                                @click="showProfileMenu = false"
                            >
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-gray-900 font-medium">هفته‌های بازی</span>
                            </Link>

                            <button
                                @click="logout"
                                class="w-full flex items-center space-x-3 space-x-reverse p-3 rounded-xl hover:bg-red-50 transition-colors duration-200 text-red-600"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="font-medium">خروج از حساب</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const showProfileMenu = ref(false);
const unreadCount = ref(0);

const userInitials = computed(() => {
    const name = window.Laravel?.user?.name || '';
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2) || 'U';
});

const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

const logout = () => {
    router.post(route('logout'));
};

// Load unread notifications count
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
    
    onUnmounted(() => {
        clearInterval(interval);
    });
});
</script>

<style scoped>
.safe-area-padding-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>