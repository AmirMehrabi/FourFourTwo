<template>
    <div
        v-if="show && (users.length > 0 || loading)"
        class="bg-white border border-gray-200 rounded-lg shadow-lg max-w-xs z-50 overflow-hidden"
        style="min-width: 250px;"
    >
        <!-- Loading State -->
        <div v-if="loading" class="p-3 text-center">
            <div class="inline-flex items-center gap-2 text-gray-500 text-sm">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                جستجو...
            </div>
        </div>
        
        <!-- Users List -->
        <div v-else-if="users.length > 0" class="max-h-60 overflow-y-auto">
            <div
                v-for="(user, index) in users"
                :key="user.id"
                @click="selectUser(user)"
                @mouseenter="selectedIndex = index"
                class="flex items-center gap-3 p-3 cursor-pointer transition-colors duration-150"
                :class="{
                    'bg-blue-50 border-l-2 border-l-blue-500': selectedIndex === index,
                    'hover:bg-gray-50': selectedIndex !== index
                }"
            >
                <!-- User Avatar -->
                <div class="flex-shrink-0">
                    <div 
                        v-if="user.avatar" 
                        class="w-8 h-8 rounded-full overflow-hidden border border-gray-200"
                    >
                        <img 
                            :src="user.avatar" 
                            :alt="user.name"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div 
                        v-else 
                        class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-xs"
                    >
                        {{ getUserInitials(user.name) }}
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900 truncate">{{ user.name }}</span>
                    </div>
                    <div class="text-sm text-gray-500">@{{ user.username }}</div>
                </div>

                <!-- Selection Indicator -->
                <div v-if="selectedIndex === index" class="flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- No Results -->
        <div v-else class="p-4 text-center text-gray-500 text-sm">
            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p>کاربری یافت نشد</p>
            <p class="text-xs mt-1">فقط کاربران دنبال شده قابل منشن هستند</p>
        </div>

        <!-- Footer Hint -->
        <div v-if="users.length > 0" class="px-3 py-2 bg-gray-50 border-t border-gray-100">
            <div class="text-xs text-gray-500 flex items-center justify-between">
                <span>↑↓ انتخاب</span>
                <span>Enter تایید</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
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
const selectedIndex = ref(0);
let searchTimeout = null;

// Methods
const searchUsers = async (query) => {
    if (!query || query.length < 1) {
        users.value = [];
        return;
    }

    try {
        loading.value = true;
        const response = await axios.get('/comments/search-followed-users', {
            params: { query, limit: 10 }
        });
        users.value = response.data;
        selectedIndex.value = 0;
    } catch (error) {
        console.error('Error searching users:', error);
        users.value = [];
    } finally {
        loading.value = false;
    }
};

const selectUser = (user) => {
    emit('select-user', user);
    resetState();
};

const selectCurrentUser = () => {
    if (users.value.length > 0 && selectedIndex.value >= 0) {
        selectUser(users.value[selectedIndex.value]);
    }
};

const moveSelection = (direction) => {
    if (users.value.length === 0) return;
    
    if (direction === 'up') {
        selectedIndex.value = selectedIndex.value <= 0 
            ? users.value.length - 1 
            : selectedIndex.value - 1;
    } else if (direction === 'down') {
        selectedIndex.value = selectedIndex.value >= users.value.length - 1 
            ? 0 
            : selectedIndex.value + 1;
    }
};

const resetState = () => {
    users.value = [];
    selectedIndex.value = 0;
    loading.value = false;
};

const getUserInitials = (name) => {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2) || 'U';
};

const handleKeydown = (event) => {
    if (!props.show || users.value.length === 0) return;
    
    switch (event.key) {
        case 'ArrowUp':
            event.preventDefault();
            moveSelection('up');
            break;
        case 'ArrowDown':
            event.preventDefault();
            moveSelection('down');
            break;
        case 'Enter':
            event.preventDefault();
            selectCurrentUser();
            break;
        case 'Escape':
            event.preventDefault();
            resetState();
            break;
    }
};

// Watchers
watch(() => props.searchTerm, (newTerm) => {
    clearTimeout(searchTimeout);
    
    if (newTerm && newTerm.length >= 1) {
        searchTimeout = setTimeout(() => {
            searchUsers(newTerm);
        }, 300); // Debounce search
    } else {
        users.value = [];
    }
});

watch(() => props.show, (show) => {
    if (!show) {
        resetState();
    }
});

// Lifecycle
onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    clearTimeout(searchTimeout);
});

// Expose methods for parent component
defineExpose({
    moveSelection,
    selectCurrentUser,
    resetState
});
</script>

<style scoped>
/* Custom scrollbar */
.max-h-60::-webkit-scrollbar {
    width: 6px;
}

.max-h-60::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth animations */
.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Highlight matched text - could be enhanced later */
.highlight {
    background-color: #fef3c7;
    font-weight: 600;
}
</style>