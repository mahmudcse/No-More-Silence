<script setup lang="ts">
import { formatNumberByLocale } from '@/utils/bangla';
import { computed, nextTick, onMounted, ref } from 'vue';

export interface CommentUser {
    id?: number;
    name: string;
    avatar?: string | null;
}

export interface CommentItem {
    id: number;
    parent_id?: number | null;
    content: string;
    likes_count: number;
    is_liked: boolean;
    created_at: string;
    user: CommentUser;
    can_delete: boolean;
    replies?: CommentItem[];
}

const props = defineProps<{
    reportUuid: string;
    initialCount?: number;
    isBn?: boolean;
    currentUser?: {
        id?: number;
        name?: string;
        email?: string;
        avatar?: string | null;
    } | null;
}>();

const emit = defineEmits<{
    (e: 'updateCount', count: number): void;
}>();

const comments = ref<CommentItem[]>([]);
const totalCount = ref(props.initialCount ?? 0);
const isLoading = ref(false);
const isLoaded = ref(false);
const isSubmitting = ref(false);
const newCommentText = ref('');
const replyingToId = ref<number | null>(null);
const replyText = ref('');
const isSubmittingReply = ref(false);
const mainInputRef = ref<HTMLTextAreaElement | null>(null);
const replyInputRef = ref<HTMLTextAreaElement | null>(null);

function getXsrfToken(): string {
    if (typeof document === 'undefined') return '';
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function fetchComments() {
    isLoading.value = true;
    try {
        const res = await fetch(`/reports/${props.reportUuid}/comments`, {
            headers: {
                Accept: 'application/json',
            },
        });
        if (res.ok) {
            const data = await res.json();
            comments.value = data.data || [];
            totalCount.value = data.total ?? comments.value.length;
            emit('updateCount', totalCount.value);
            isLoaded.value = true;
        }
    } catch (e) {
        console.error('Failed to fetch comments', e);
    } finally {
        isLoading.value = false;
    }
}

async function submitComment() {
    const text = newCommentText.value.trim();
    if (!text || isSubmitting.value || !props.currentUser) return;

    isSubmitting.value = true;
    try {
        const res = await fetch(`/reports/${props.reportUuid}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ content: text }),
        });

        if (res.ok) {
            const newComment = await res.json();
            comments.value.unshift(newComment);
            newCommentText.value = '';
            totalCount.value += 1;
            emit('updateCount', totalCount.value);
        } else if (res.status === 401) {
            window.location.href = '/login';
        }
    } catch (e) {
        console.error('Failed to submit comment', e);
    } finally {
        isSubmitting.value = false;
    }
}

async function startReply(commentId: number) {
    if (!props.currentUser) {
        window.location.href = '/login';
        return;
    }
    replyingToId.value = commentId;
    replyText.value = '';
    await nextTick();
    replyInputRef.value?.focus();
}

function cancelReply() {
    replyingToId.value = null;
    replyText.value = '';
}

async function submitReply(parentComment: CommentItem) {
    const text = replyText.value.trim();
    if (!text || isSubmittingReply.value || !props.currentUser) return;

    isSubmittingReply.value = true;
    try {
        const res = await fetch(`/reports/${props.reportUuid}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                content: text,
                parent_id: parentComment.id,
            }),
        });

        if (res.ok) {
            const newReply = await res.json();
            if (!parentComment.replies) {
                parentComment.replies = [];
            }
            parentComment.replies.push(newReply);
            replyText.value = '';
            replyingToId.value = null;
            totalCount.value += 1;
            emit('updateCount', totalCount.value);
        } else if (res.status === 401) {
            window.location.href = '/login';
        }
    } catch (e) {
        console.error('Failed to submit reply', e);
    } finally {
        isSubmittingReply.value = false;
    }
}

async function toggleLike(item: CommentItem) {
    if (!props.currentUser) {
        window.location.href = '/login';
        return;
    }

    // Optimistic update
    const prevLiked = item.is_liked;
    const prevCount = item.likes_count;
    item.is_liked = !prevLiked;
    item.likes_count = prevLiked ? Math.max(0, prevCount - 1) : prevCount + 1;

    try {
        const res = await fetch(`/comments/${item.id}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        if (res.ok) {
            const data = await res.json();
            item.is_liked = data.liked;
            item.likes_count = data.likes_count;
        } else {
            // Revert on error
            item.is_liked = prevLiked;
            item.likes_count = prevCount;
        }
    } catch {
        item.is_liked = prevLiked;
        item.likes_count = prevCount;
    }
}

async function deleteComment(targetId: number, parent?: CommentItem) {
    const confirmMsg = props.isBn
        ? 'আপনি কি নিশ্চিত যে এই মন্তব্যটি মুছে ফেলতে চান?'
        : 'Are you sure you want to delete this comment?';
    if (!confirm(confirmMsg)) return;

    try {
        const res = await fetch(`/comments/${targetId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        if (res.ok) {
            if (parent && parent.replies) {
                parent.replies = parent.replies.filter((r) => r.id !== targetId);
            } else {
                comments.value = comments.value.filter((c) => c.id !== targetId);
            }
            totalCount.value = Math.max(0, totalCount.value - 1);
            emit('updateCount', totalCount.value);
        }
    } catch (e) {
        console.error('Failed to delete comment', e);
    }
}

function formatTimeAgo(isoString: string): string {
    if (!isoString) return '';
    const date = new Date(isoString);
    const now = new Date();
    const diffSecs = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffSecs < 60) {
        return props.isBn ? 'এইমাত্র' : 'Just now';
    }
    const diffMins = Math.floor(diffSecs / 60);
    if (diffMins < 60) {
        return props.isBn
            ? `${formatNumberByLocale(diffMins, 'bn')} মিনিট আগে`
            : `${diffMins}m ago`;
    }
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) {
        return props.isBn
            ? `${formatNumberByLocale(diffHours, 'bn')} ঘণ্টা আগে`
            : `${diffHours}h ago`;
    }
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) {
        return props.isBn
            ? `${formatNumberByLocale(diffDays, 'bn')} দিন আগে`
            : `${diffDays}d ago`;
    }
    return date.toLocaleDateString(props.isBn ? 'bn-BD' : 'en-US', {
        month: 'short',
        day: 'numeric',
    });
}

function getInitial(name?: string): string {
    return (name || 'U').charAt(0).toUpperCase();
}

function handleFacebookLogin() {
    const entered = window.prompt(
        props.isBn
            ? 'আপনার ফেসবুক প্রদর্শনী নাম লিখুন:'
            : 'Enter your Facebook display name to continue:',
        'Mahmudul Hasan',
    );
    if (entered !== null) {
        const query = entered.trim() ? `?name=${encodeURIComponent(entered.trim())}` : '';
        window.location.href = `/auth/facebook${query}`;
    }
}

onMounted(() => {
    fetchComments();
});
</script>

<template>
    <div class="border-t border-black/[0.08] bg-slate-50/70 p-4 dark:border-white/[0.08] dark:bg-slate-950/40 sm:p-5">
        <!-- New Comment Box -->
        <div class="mb-4">
            <!-- If Logged In -->
            <div v-if="currentUser" class="flex items-start gap-2.5 sm:gap-3">
                <!-- Avatar -->
                <div class="shrink-0">
                    <img
                        v-if="currentUser.avatar"
                        :src="currentUser.avatar"
                        :alt="currentUser.name || 'User'"
                        class="h-8 w-8 rounded-full object-cover ring-1 ring-black/10 dark:ring-white/10 sm:h-9 sm:w-9"
                    />
                    <div
                        v-else
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-tr from-rose-500 to-indigo-600 font-bold text-xs text-white shadow-xs sm:h-9 sm:w-9"
                    >
                        {{ getInitial(currentUser.name) }}
                    </div>
                </div>

                <!-- Input Field -->
                <div class="flex-1">
                    <div class="relative rounded-2xl border border-black/[0.1] bg-white shadow-xs focus-within:border-rose-500 focus-within:ring-2 focus-within:ring-rose-500/20 dark:border-white/[0.12] dark:bg-slate-900">
                        <textarea
                            ref="mainInputRef"
                            v-model="newCommentText"
                            :placeholder="isBn ? 'একটি মন্তব্য লিখুন...' : 'Write a comment...'"
                            rows="2"
                            @keydown.enter.exact.prevent="submitComment"
                            class="w-full resize-none border-0 bg-transparent px-3.5 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white dark:placeholder:text-slate-500 sm:text-sm"
                        ></textarea>

                        <div class="flex items-center justify-between border-t border-black/[0.04] px-3 py-1.5 dark:border-white/[0.04]">
                            <span class="text-[10px] text-slate-400">
                                {{ isBn ? 'Enter চাপুন পোস্ট করতে' : 'Press Enter to post' }}
                            </span>

                            <button
                                type="button"
                                @click="submitComment"
                                :disabled="!newCommentText.trim() || isSubmitting"
                                class="inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-3 py-1 text-xs font-semibold text-white shadow-xs transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <svg
                                    v-if="isSubmitting"
                                    class="h-3.5 w-3.5 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ isBn ? 'পোস্ট' : 'Post' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- If Guest / Not Logged In -->
            <div
                v-else
                class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-blue-500/20 bg-blue-500/[0.04] p-3.5 dark:border-blue-500/30 dark:bg-blue-500/[0.08] sm:flex-row"
            >
                <div class="flex items-center gap-2.5 text-center sm:text-left">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#1877F2]/10 text-[#1877F2] dark:bg-[#1877F2]/20">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                            {{ isBn ? 'আলোচনায় অংশ নিতে ফেসবুক আইডি দিয়ে লগইন করুন' : 'Sign in with Facebook to join the discussion' }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ isBn ? 'ফেসবুক বা ইমেইল অ্যাকাউন্ট ব্যবহার করে সরাসরি মন্তব্য করুন।' : 'Post comments using your Facebook or email account.' }}
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a
                        href="/auth/facebook"
                        @click.prevent="handleFacebookLogin"
                        class="inline-flex items-center gap-1.5 rounded-full bg-[#1877F2] px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-[#166fe5] active:scale-95"
                    >
                        <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>{{ isBn ? 'ফেসবুক লগইন' : 'Facebook Login' }}</span>
                    </a>

                    <a
                        href="/login"
                        class="inline-flex items-center rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-xs transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        {{ isBn ? 'ইমেইল লগইন' : 'Email Login' }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Comments List -->
        <div class="space-y-3">
            <!-- Loading Skeleton -->
            <div v-if="isLoading && !isLoaded" class="space-y-2 py-2">
                <div v-for="i in 2" :key="i" class="flex animate-pulse items-start gap-2.5">
                    <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                    <div class="flex-1 space-y-1.5">
                        <div class="h-10 w-3/4 rounded-2xl bg-slate-200 dark:bg-slate-800"></div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="comments.length === 0"
                class="py-3 text-center text-xs text-slate-500 dark:text-slate-400"
            >
                {{ isBn ? 'এখনও কোনো মন্তব্য নেই। প্রথম মন্তব্যটি করুন!' : 'No comments yet. Be the first to share your thoughts!' }}
            </div>

            <!-- Comment Items -->
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="space-y-2"
            >
                <!-- Main Comment -->
                <div class="flex items-start gap-2.5 sm:gap-3 group">
                    <!-- Avatar -->
                    <div class="shrink-0 pt-0.5">
                        <img
                            v-if="comment.user?.avatar"
                            :src="comment.user.avatar"
                            :alt="comment.user.name"
                            class="h-8 w-8 rounded-full object-cover ring-1 ring-black/10 dark:ring-white/10"
                        />
                        <div
                            v-else
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-tr from-slate-400 to-slate-600 font-bold text-xs text-white shadow-xs dark:from-slate-700 dark:to-slate-800"
                        >
                            {{ getInitial(comment.user?.name) }}
                        </div>
                    </div>

                    <!-- Bubble + Actions -->
                    <div class="flex-1 min-w-0">
                        <div class="relative inline-block max-w-[95%] rounded-2xl bg-slate-200/70 px-3.5 py-2 text-xs text-slate-800 dark:bg-slate-800/90 dark:text-slate-100 sm:text-sm">
                            <div class="font-bold text-[12px] text-slate-900 dark:text-white">
                                {{ comment.user?.name || 'User' }}
                            </div>
                            <div class="whitespace-pre-wrap break-words mt-0.5">
                                {{ comment.content }}
                            </div>

                            <!-- Floating Like Reaction Badge on bottom-right of bubble -->
                            <div
                                v-if="comment.likes_count > 0"
                                class="absolute -bottom-2 right-2 flex items-center gap-1 rounded-full border border-black/[0.06] bg-white px-1.5 py-0.5 text-[10px] font-semibold text-rose-600 shadow-xs dark:border-white/[0.08] dark:bg-slate-900 dark:text-rose-400"
                            >
                                <svg class="h-3 w-3 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                <span>{{ formatNumberByLocale(comment.likes_count, isBn ? 'bn' : 'en') }}</span>
                            </div>
                        </div>

                        <!-- Action Bar below bubble -->
                        <div class="mt-1 flex items-center gap-3 pl-2 text-[11px] text-slate-500 dark:text-slate-400">
                            <span>{{ formatTimeAgo(comment.created_at) }}</span>

                            <button
                                type="button"
                                @click="toggleLike(comment)"
                                :class="[
                                    'font-semibold transition-colors hover:underline',
                                    comment.is_liked
                                        ? 'text-rose-600 dark:text-rose-400'
                                        : 'hover:text-slate-800 dark:hover:text-slate-200',
                                ]"
                            >
                                {{ isBn ? (comment.is_liked ? 'পছন্দ হয়েছে' : 'পছন্দ') : (comment.is_liked ? 'Liked' : 'Like') }}
                            </button>

                            <button
                                type="button"
                                @click="startReply(comment.id)"
                                class="font-semibold transition-colors hover:text-slate-800 hover:underline dark:hover:text-slate-200"
                            >
                                {{ isBn ? 'উত্তর দিন' : 'Reply' }}
                            </button>

                            <button
                                v-if="comment.can_delete"
                                type="button"
                                @click="deleteComment(comment.id)"
                                class="text-slate-400 transition-colors hover:text-rose-600 hover:underline dark:hover:text-rose-400"
                            >
                                {{ isBn ? 'মুছুন' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Nested Replies -->
                <div v-if="comment.replies && comment.replies.length > 0" class="ml-10 space-y-2 border-l-2 border-slate-200 pl-3 dark:border-slate-800">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="flex items-start gap-2 group"
                    >
                        <!-- Mini Avatar -->
                        <div class="shrink-0 pt-0.5">
                            <img
                                v-if="reply.user?.avatar"
                                :src="reply.user.avatar"
                                :alt="reply.user.name"
                                class="h-6 w-6 rounded-full object-cover ring-1 ring-black/10 dark:ring-white/10"
                            />
                            <div
                                v-else
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-400 font-bold text-[10px] text-white dark:bg-slate-700"
                            >
                                {{ getInitial(reply.user?.name) }}
                            </div>
                        </div>

                        <!-- Bubble + Actions -->
                        <div class="flex-1 min-w-0">
                            <div class="relative inline-block max-w-[95%] rounded-2xl bg-slate-200/60 px-3 py-1.5 text-xs text-slate-800 dark:bg-slate-800/80 dark:text-slate-100">
                                <div class="font-bold text-[11px] text-slate-900 dark:text-white">
                                    {{ reply.user?.name || 'User' }}
                                </div>
                                <div class="whitespace-pre-wrap break-words mt-0.5">
                                    {{ reply.content }}
                                </div>

                                <div
                                    v-if="reply.likes_count > 0"
                                    class="absolute -bottom-2 right-2 flex items-center gap-1 rounded-full border border-black/[0.06] bg-white px-1 py-0.2 text-[9px] font-semibold text-rose-600 shadow-xs dark:border-white/[0.08] dark:bg-slate-900 dark:text-rose-400"
                                >
                                    <svg class="h-2.5 w-2.5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    <span>{{ formatNumberByLocale(reply.likes_count, isBn ? 'bn' : 'en') }}</span>
                                </div>
                            </div>

                            <div class="mt-0.5 flex items-center gap-2.5 pl-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                <span>{{ formatTimeAgo(reply.created_at) }}</span>

                                <button
                                    type="button"
                                    @click="toggleLike(reply)"
                                    :class="[
                                        'font-semibold hover:underline',
                                        reply.is_liked ? 'text-rose-600 dark:text-rose-400' : 'hover:text-slate-800 dark:hover:text-slate-200',
                                    ]"
                                >
                                    {{ isBn ? (reply.is_liked ? 'পছন্দ' : 'পছন্দ') : (reply.is_liked ? 'Liked' : 'Like') }}
                                </button>

                                <button
                                    v-if="reply.can_delete"
                                    type="button"
                                    @click="deleteComment(reply.id, comment)"
                                    class="text-slate-400 hover:text-rose-600 hover:underline dark:hover:text-rose-400"
                                >
                                    {{ isBn ? 'মুছুন' : 'Delete' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inline Reply Box -->
                <div v-if="replyingToId === comment.id" class="ml-10 mt-2 flex items-start gap-2">
                    <div class="shrink-0 pt-1">
                        <img
                            v-if="currentUser?.avatar"
                            :src="currentUser.avatar"
                            class="h-6 w-6 rounded-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-500 font-bold text-[10px] text-white"
                        >
                            {{ getInitial(currentUser?.name) }}
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="relative rounded-2xl border border-black/[0.1] bg-white shadow-xs dark:border-white/[0.12] dark:bg-slate-900">
                            <textarea
                                ref="replyInputRef"
                                v-model="replyText"
                                :placeholder="isBn ? 'উত্তর লিখুন...' : 'Write a reply...'"
                                rows="1"
                                @keydown.enter.exact.prevent="submitReply(comment)"
                                class="w-full resize-none border-0 bg-transparent px-3 py-1.5 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white dark:placeholder:text-slate-500"
                            ></textarea>

                            <div class="flex items-center justify-end gap-1.5 border-t border-black/[0.04] px-2 py-1 dark:border-white/[0.04]">
                                <button
                                    type="button"
                                    @click="cancelReply"
                                    class="rounded-full px-2 py-0.5 text-[10px] text-slate-500 hover:text-slate-800 dark:hover:text-slate-200"
                                >
                                    {{ isBn ? 'বাতিল' : 'Cancel' }}
                                </button>
                                <button
                                    type="button"
                                    @click="submitReply(comment)"
                                    :disabled="!replyText.trim() || isSubmittingReply"
                                    class="rounded-full bg-rose-600 px-2.5 py-0.5 text-[10px] font-semibold text-white shadow-xs hover:bg-rose-700 disabled:opacity-40"
                                >
                                    {{ isBn ? 'উত্তর' : 'Reply' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
