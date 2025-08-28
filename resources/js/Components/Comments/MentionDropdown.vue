<template>
    <div v-if="show" class="absolute bottom-full left-0 mb-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
        <div class="p-2 border-b border-gray-100">
            <div class="text-xs text-gray-500 font-medium">افرادی که دنبال می‌کنید:</div>
        </div>
        
        <div v-if="loading" class="p-4 text-center">
            <div class="inline-flex items-center gap-2 text-gray-500">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                در حال بارگذاری...
            </div>
        </div>
        
        <div v-else-if="filteredUsers.length === 0" class="p-4 text-center text-gray-500 text-sm">
            هیچ کاربری برای نمایش وجود ندارد
        </div>
        
        <div v-else class="divide-y divide-gray-100">
            <button
                v-for="user in filteredUsers"
                :key="user.id"
                @click="selectUser(user)"
                class="w-full p-3 text-right hover:bg-gray-50 transition-colors duration-150 flex items-center gap-3"
            >
                <!-- User Avatar -->
                <div class="flex-shrink-0">
                    <div v-if="user.avatar" class="w-8 h-8 rounded-full overflow-hidden">
                        <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover">
                    </div>
                    <div v-else class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        {{ getUserInitials(user.name) }}
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="flex-1 text-right">
                    <div class="font-medium text-gray-900 text-sm">{{ user.name }}</div>
                    <div class="text-gray-500 text-xs">@{{ user.username }}</div>
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    searchTerm: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['select-user']);

// State
const users = ref([]);
const loading = ref(false);

// Computed
const filteredUsers = computed(() => {
    if (!props.searchTerm) return users.value;
    
    const term = props.searchTerm.toLowerCase();
    return users.value.filter(user => 
        user.name.toLowerCase().includes(term) || 
        user.username.toLowerCase().includes(term)
    );
});

// Methods
async function loadFollowingUsers() {
    loading.value = true;
    try {
        const response = await axios.get('/api/user/following');
        users.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading following users:', error);
        users.value = [];
    } finally {
        loading.value = false;
    }
}

function selectUser(user) {
    emit('select-user', user);
}

function getUserInitials(name) {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

// Lifecycle
onMounted(() => {
    if (props.show) {
        loadFollowingUsers();
    }
});

watch(() => props.show, (newShow) => {
    if (newShow) {
        loadFollowingUsers();
    }
});
</script>
