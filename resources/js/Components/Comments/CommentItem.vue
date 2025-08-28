<template>
    <div class="p-4 hover:bg-gray-50/50 transition-colors duration-200">
        <!-- Main Comment -->
        <div class="flex gap-3">
            <!-- User Avatar -->
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-xs">
                    {{ getUserInitials(comment.user.name) }}
                </div>
            </div>

            <!-- Comment Content -->
            <div class="flex-1 min-w-0">
                <!-- Header -->
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-sm text-gray-900">{{ comment.user.name }}</span>
                    <span class="text-xs text-gray-500">@{{ comment.user.username || 'user' + comment.user.id }}</span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500">{{ formatTimeAgo(comment.created_at) }}</span>
                    <span v-if="comment.is_edited" class="text-xs text-gray-400">(ویرایش شده)</span>
                </div>

                <!-- Comment Text -->
                <div v-if="!isEditing" class="text-sm text-gray-800 leading-relaxed mb-3 whitespace-pre-wrap">
                    {{ comment.content }}
                </div>

                <!-- Edit Form -->
                <div v-else class="mb-3">
                    <textarea
                        v-model="editContent"
                        class="w-full p-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                        rows="3"
                        maxlength="1000"
                    ></textarea>
                    <div class="flex justify-between items-center mt-2">
                        <div class="text-xs text-gray-400">{{ editContent.length }}/1000</div>
                        <div class="flex gap-2">
                            <button
                                @click="cancelEdit"
                                class="px-3 py-1 text-xs text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
                            >
                                لغو
                            </button>
                            <button
                                @click="saveEdit"
                                :disabled="editContent.trim() === comment.content || editContent.trim().length === 0"
                                class="px-3 py-1 text-xs text-white bg-blue-500 rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                ذخیره
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reactions -->
                <div v-if="hasReactions" class="flex items-center gap-1 mb-3">
                    <div 
                        v-for="(count, type) in comment.reaction_counts" 
                        :key="type"
                        class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded-full text-xs"
                        :class="{ 'bg-blue-100 text-blue-700': comment.user_reaction === type }"
                    >
                        <span>{{ getReactionEmoji(type) }}</span>
                        <span>{{ count }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-4 text-xs">
                    <!-- Reaction Buttons -->
                    <div class="flex items-center gap-1">
                        <button
                            v-for="reactionType in ['like', 'love', 'laugh']"
                            :key="reactionType"
                            @click="toggleReaction(reactionType)"
                            class="p-1.5 rounded-full hover:bg-gray-100 transition-colors duration-200"
                            :class="{ 'bg-blue-100 text-blue-600': comment.user_reaction === reactionType }"
                        >
                            {{ getReactionEmoji(reactionType) }}
                        </button>
                    </div>

                    <!-- Reply Button (only show for top-level comments) -->
                    <button
                        v-if="!isReply"
                        @click="toggleReply"
                        class="text-gray-500 hover:text-blue-600 font-medium transition-colors duration-200"
                    >
                        پاسخ
                    </button>

                    <!-- Edit/Delete Buttons (for own comments) -->
                    <div v-if="isOwnComment" class="flex items-center gap-2">
                        <button
                            @click="startEdit"
                            class="text-gray-500 hover:text-blue-600 font-medium transition-colors duration-200"
                        >
                            ویرایش
                        </button>
                        <button
                            @click="deleteComment"
                            class="text-gray-500 hover:text-red-600 font-medium transition-colors duration-200"
                        >
                            حذف
                        </button>
                    </div>

                    <!-- Replies Count -->
                    <div v-if="comment.replies_count > 0" class="text-gray-500">
                        {{ comment.replies_count }} پاسخ
                    </div>
                </div>

                <!-- Reply Input (only for top-level comments) -->
                <div v-if="showReplyInput && !isReply" class="mt-3 bg-gray-50 rounded-lg p-3">
                    <CommentInput
                        :fixture-id="fixtureId"
                        :parent-comment="comment"
                        @reply-posted="handleReplyPosted"
                        @cancel-reply="showReplyInput = false"
                        ref="replyInputRef"
                    />
                </div>

                <!-- Replies -->
                <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 space-y-3">
                    <div class="border-l-2 border-gray-200 pl-4">
                        <CommentItem
                            v-for="reply in comment.replies"
                            :key="reply.id"
                            :comment="reply"
                            :fixture-id="fixtureId"
                            :is-reply="true"
                            @comment-updated="$emit('comment-updated', $event)"
                            @comment-deleted="handleReplyDeleted"
                            class="border-none p-0 hover:bg-transparent"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import axios from 'axios';
import CommentInput from './CommentInput.vue';

const props = defineProps({
    comment: {
        type: Object,
        required: true
    },
    fixtureId: {
        type: Number,
        required: true
    },
    isReply: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['comment-updated', 'comment-deleted', 'reply-posted']);

// State
const showReplyInput = ref(false);
const isEditing = ref(false);
const editContent = ref('');
const replyInputRef = ref(null);

// Computed
const isOwnComment = computed(() => {
    return props.comment.user.id === window.Laravel?.user?.id;
});

const hasReactions = computed(() => {
    return props.comment.reaction_counts && Object.keys(props.comment.reaction_counts).length > 0;
});

// Methods
const getUserInitials = (name) => {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2) || 'U';
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

const toggleReaction = async (type) => {
    try {
        const response = await axios.post(`/comments/${props.comment.id}/react`, {
            type: type
        });

        // Update local comment data
        props.comment.user_reaction = response.data.user_reaction;
        props.comment.reaction_counts = response.data.reaction_counts;

    } catch (error) {
        console.error('Error toggling reaction:', error);
        // TODO: Show error toast
    }
};

const toggleReply = () => {
    showReplyInput.value = !showReplyInput.value;
    if (showReplyInput.value) {
        nextTick(() => {
            replyInputRef.value?.focus();
        });
    }
};

const handleReplyPosted = (reply) => {
    showReplyInput.value = false;
    
    // Only emit the event, let the parent (CommentSection) handle adding the reply
    emit('reply-posted', reply, props.comment.id);
};

const handleReplyDeleted = (replyId) => {
    if (props.comment.replies) {
        const index = props.comment.replies.findIndex(r => r.id === replyId);
        if (index !== -1) {
            props.comment.replies.splice(index, 1);
            props.comment.replies_count = Math.max(0, (props.comment.replies_count || 1) - 1);
        }
    }
};

const startEdit = () => {
    isEditing.value = true;
    editContent.value = props.comment.content;
};

const cancelEdit = () => {
    isEditing.value = false;
    editContent.value = '';
};

const saveEdit = async () => {
    try {
        const response = await axios.put(`/comments/${props.comment.id}`, {
            content: editContent.value.trim()
        });

        // Update local comment
        props.comment.content = editContent.value.trim();
        props.comment.is_edited = true;
        props.comment.edited_at = new Date().toISOString();

        isEditing.value = false;
        emit('comment-updated', props.comment);

    } catch (error) {
        console.error('Error updating comment:', error);
        // TODO: Show error toast
    }
};

const deleteComment = async () => {
    if (!confirm('آیا از حذف این نظر اطمینان دارید؟')) {
        return;
    }

    try {
        await axios.delete(`/comments/${props.comment.id}`);
        emit('comment-deleted', props.comment.id);

    } catch (error) {
        console.error('Error deleting comment:', error);
        // TODO: Show error toast
    }
};
</script>

<style scoped>
/* Smooth transitions */
.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Mobile touch targets */
@media (max-width: 768px) {
    button {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Nested replies indentation */
.nested-reply {
    border-left: 2px solid #e5e7eb;
    padding-left: 1rem;
}
</style>
