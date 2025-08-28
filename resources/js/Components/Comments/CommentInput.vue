<template>
    <div class="p-4 bg-white">
        <!-- User Avatar & Input -->
        <div class="flex gap-3">
            <!-- User Avatar -->
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                    {{ userInitials }}
                </div>
            </div>

            <!-- Input Area -->
            <div class="flex-1">
                <div class="relative">
                    <!-- Textarea -->
                    <textarea
                        v-model="content"
                        :placeholder="placeholder"
                        :disabled="posting"
                        @keydown.enter.prevent="handleEnterKey"
                        @keydown="handleKeydown"
                        @input="handleInput"
                        ref="textareaRef"
                        class="w-full resize-none bg-gray-50 border-0 rounded-xl px-4 py-3 text-sm placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
                        :class="{ 
                            'pr-20': content.length > 0,
                            'opacity-50': posting 
                        }"
                        rows="1"
                    ></textarea>

                    <!-- Mention Dropdown -->
                    <MentionDropdown
                        :show="showMentionDropdown"
                        :search-term="mentionSearchTerm"
                        :style="{
                            position: 'fixed',
                            left: mentionDropdownPosition.x + 'px',
                            top: mentionDropdownPosition.y + 'px'
                        }"
                        @select-user="handleMentionSelect"
                    />

                    <!-- Character Counter & Send Button -->
                    <div v-if="content.length > 0" class="absolute left-2 top-1/2 transform -translate-y-1/2 flex items-center gap-2">
                        <!-- Character Counter -->
                        <div class="text-xs text-gray-400" :class="{ 'text-red-500': content.length > maxLength }">
                            {{ content.length }}/{{ maxLength }}
                        </div>

                        <!-- Send Button -->
                        <button
                            @click="submitComment"
                            :disabled="!canSubmit"
                            class="p-2 rounded-full bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 active:scale-95"
                        >
                            <svg v-if="posting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ error }}
                </div>

                <!-- Reply Context -->
                <div v-if="parentComment" class="mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <div class="text-xs text-blue-600 font-medium mb-1">
                                در پاسخ به {{ parentComment.user.name }}
                            </div>
                            <div class="text-sm text-gray-700 line-clamp-2">
                                {{ parentComment.content }}
                            </div>
                        </div>
                        <button
                            @click="cancelReply"
                            class="flex-shrink-0 p-1 text-blue-400 hover:text-blue-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue';
import axios from 'axios';
import MentionDropdown from './MentionDropdown.vue';

const props = defineProps({
    fixtureId: {
        type: Number,
        required: true
    },
    parentComment: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['comment-posted', 'reply-posted', 'cancel-reply']);

// State
const content = ref('');
const posting = ref(false);
const error = ref('');
const textareaRef = ref(null);
const maxLength = 1000;

// Mention functionality
const showMentionDropdown = ref(false);
const mentionSearchTerm = ref('');
const mentionDropdownPosition = ref({ x: 0, y: 0 });

// Computed
const userInitials = computed(() => {
    const name = window.Laravel?.user?.name || '';
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2) || 'U';
});

const placeholder = computed(() => {
    return props.parentComment 
        ? `پاسخ به ${props.parentComment.user.name}...`
        : 'نظر خود را بنویسید...';
});

const canSubmit = computed(() => {
    return content.value.trim().length > 0 && 
           content.value.length <= maxLength && 
           !posting.value;
});

// Methods
const handleInput = () => {
    error.value = '';
    autoResize();
    checkForMentions();
};

const autoResize = () => {
    nextTick(() => {
        const textarea = textareaRef.value;
        if (textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }
    });
};

const handleEnterKey = (event) => {
    // Submit on Enter (without Shift)
    if (!event.shiftKey) {
        event.preventDefault();
        submitComment();
    }
};

const submitComment = async () => {
    if (!canSubmit.value) return;

    try {
        posting.value = true;
        error.value = '';

        const response = await axios.post('/comments', {
            fixture_id: props.fixtureId,
            parent_id: props.parentComment?.id || null,
            content: content.value.trim()
        });

        const newComment = response.data.comment;

        // Emit appropriate event
        if (props.parentComment) {
            emit('reply-posted', newComment, props.parentComment.id);
        } else {
            emit('comment-posted', newComment);
        }

        // Reset form
        content.value = '';
        autoResize();

        // Show success feedback (subtle animation)
        showSuccessFeedback();

    } catch (err) {
        console.error('Error posting comment:', err);
        
        if (err.response?.data?.errors?.content) {
            error.value = err.response.data.errors.content[0];
        } else if (err.response?.data?.message) {
            error.value = err.response.data.message;
        } else {
            error.value = 'خطا در ارسال نظر. لطفا دوباره تلاش کنید.';
        }
    } finally {
        posting.value = false;
    }
};

const cancelReply = () => {
    emit('cancel-reply');
};

const showSuccessFeedback = () => {
    // Add a subtle success animation
    const textarea = textareaRef.value;
    if (textarea) {
        textarea.style.transform = 'scale(0.98)';
        setTimeout(() => {
            textarea.style.transform = 'scale(1)';
        }, 150);
    }
};

// Mention methods
const checkForMentions = () => {
    const text = content.value;
    const lastAtSymbol = text.lastIndexOf('@');
    
    if (lastAtSymbol !== -1) {
        const afterAt = text.substring(lastAtSymbol + 1);
        const spaceIndex = afterAt.indexOf(' ');
        
        if (spaceIndex === -1 || spaceIndex > 0) {
            // Show dropdown if we have @ and some text after it
            mentionSearchTerm.value = afterAt.substring(0, spaceIndex > 0 ? spaceIndex : afterAt.length);
            showMentionDropdown.value = true;
            updateMentionDropdownPosition();
        } else {
            showMentionDropdown.value = false;
        }
    } else {
        showMentionDropdown.value = false;
    }
};

const updateMentionDropdownPosition = () => {
    const textarea = textareaRef.value;
    if (textarea) {
        const rect = textarea.getBoundingClientRect();
        const text = content.value;
        const lastAtSymbol = text.lastIndexOf('@');
        
        // Calculate cursor position for @ symbol
        const beforeAt = text.substring(0, lastAtSymbol);
        const tempTextarea = document.createElement('textarea');
        tempTextarea.style.position = 'absolute';
        tempTextarea.style.visibility = 'hidden';
        tempTextarea.style.whiteSpace = 'pre-wrap';
        tempTextarea.style.wordWrap = 'break-word';
        tempTextarea.style.width = textarea.offsetWidth + 'px';
        tempTextarea.style.font = window.getComputedStyle(textarea).font;
        tempTextarea.value = beforeAt;
        
        document.body.appendChild(tempTextarea);
        const height = tempTextarea.scrollHeight;
        document.body.removeChild(tempTextarea);
        
        mentionDropdownPosition.value = {
            x: rect.left,
            y: rect.top + height - 20
        };
    }
};

const handleMentionSelect = (user) => {
    const text = content.value;
    const lastAtSymbol = text.lastIndexOf('@');
    
    if (lastAtSymbol !== -1) {
        const beforeAt = text.substring(0, lastAtSymbol);
        const afterAt = text.substring(lastAtSymbol + 1);
        const spaceIndex = afterAt.indexOf(' ');
        
        if (spaceIndex > 0) {
            const afterSpace = afterAt.substring(spaceIndex);
            content.value = beforeAt + '@' + user.username + ' ' + afterSpace;
        } else {
            content.value = beforeAt + '@' + user.username + ' ';
        }
    }
    
    showMentionDropdown.value = false;
    nextTick(() => {
        textareaRef.value?.focus();
        autoResize();
    });
};

const handleKeydown = (event) => {
    if (showMentionDropdown.value) {
        if (event.key === 'Escape') {
            showMentionDropdown.value = false;
            event.preventDefault();
        }
    }
};

const focus = () => {
    nextTick(() => {
        textareaRef.value?.focus();
    });
};

// Watch for parent comment changes to auto-focus
watch(() => props.parentComment, (newParent) => {
    if (newParent) {
        focus();
    }
});

// Expose focus method
defineExpose({ focus });
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

textarea {
    transition: all 0.2s ease;
}

textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    textarea {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
</style>
