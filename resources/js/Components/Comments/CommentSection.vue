<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Comments Header -->
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    نظرات
                    <span v-if="totalComments > 0" class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        {{ totalComments }}
                    </span>
                </h3>
                <button 
                    @click="refreshComments" 
                    :disabled="loading"
                    class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    :class="{ 'animate-spin': loading }"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Comment Input -->
        <CommentInput 
            :fixture-id="fixtureId"
            @comment-posted="handleCommentPosted"
            class="border-b border-gray-100"
        />

        <!-- Comments List -->
        <div class="divide-y divide-gray-100">
            <div v-if="loading && comments.length === 0" class="p-6 text-center">
                <div class="inline-flex items-center gap-2 text-gray-500">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    در حال بارگذاری نظرات...
                </div>
            </div>

            <div v-else-if="comments.length === 0" class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-lg font-medium mb-1">هنوز نظری ثبت نشده</p>
                <p class="text-sm">اولین نفری باشید که نظر می‌دهد!</p>
            </div>

            <CommentItem
                v-for="comment in comments"
                :key="comment.id"
                :comment="comment"
                :fixture-id="fixtureId"
                @comment-updated="handleCommentUpdated"
                @comment-deleted="handleCommentDeleted"
                @reply-posted="handleReplyPosted"
            />
        </div>

        <!-- Load More Button -->
        <div v-if="hasMoreComments" class="p-4 border-t border-gray-100 bg-gray-50/30">
            <button
                @click="loadMoreComments"
                :disabled="loadingMore"
                class="w-full py-3 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 disabled:opacity-50"
            >
                <span v-if="loadingMore" class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    در حال بارگذاری...
                </span>
                <span v-else>نمایش نظرات بیشتر</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import CommentInput from './CommentInput.vue';
import CommentItem from './CommentItem.vue';

const props = defineProps({
    fixtureId: {
        type: Number,
        required: true
    }
});

// State
const comments = ref([]);
const loading = ref(false);
const loadingMore = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

// Computed
const hasMoreComments = computed(() => currentPage.value < lastPage.value);
const totalComments = computed(() => total.value);

// Methods
const loadComments = async (page = 1, append = false) => {
    try {
        if (page === 1) {
            loading.value = true;
        } else {
            loadingMore.value = true;
        }

        const response = await axios.get(`/fixtures/${props.fixtureId}/comments`, {
            params: { page }
        });

        const data = response.data;
        
        if (append) {
            comments.value.push(...data.data);
        } else {
            comments.value = data.data;
        }
        
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;

    } catch (error) {
        console.error('Error loading comments:', error);
        // TODO: Show error toast
    } finally {
        loading.value = false;
        loadingMore.value = false;
    }
};

const loadMoreComments = () => {
    if (hasMoreComments.value && !loadingMore.value) {
        loadComments(currentPage.value + 1, true);
    }
};

const refreshComments = () => {
    loadComments(1, false);
};

const handleCommentPosted = (newComment) => {
    comments.value.unshift(newComment);
    total.value += 1;
};

const handleCommentUpdated = (updatedComment) => {
    const index = comments.value.findIndex(c => c.id === updatedComment.id);
    if (index !== -1) {
        comments.value[index] = { ...comments.value[index], ...updatedComment };
    }
};

const handleCommentDeleted = (commentId) => {
    const index = comments.value.findIndex(c => c.id === commentId);
    if (index !== -1) {
        comments.value.splice(index, 1);
        total.value -= 1;
    }
};

const handleReplyPosted = (reply, parentCommentId) => {
    const parentComment = comments.value.find(c => c.id === parentCommentId);
    if (parentComment) {
        if (!parentComment.replies) {
            parentComment.replies = [];
        }
        parentComment.replies.push(reply);
        parentComment.replies_count = (parentComment.replies_count || 0) + 1;
        total.value += 1;
    }
};

// Lifecycle
onMounted(() => {
    loadComments();
});
</script>

<style scoped>
/* Custom scrollbar for mobile */
@media (max-width: 768px) {
    ::-webkit-scrollbar {
        width: 2px;
    }
    
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 1px;
    }
}
</style>
