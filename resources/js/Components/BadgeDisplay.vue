<template>
  <div class="badge-display">
    <!-- Badge Stats Summary -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          🏆 نشان‌ها
        </h3>
        <span class="text-sm text-gray-500 dark:text-gray-400">
          {{ totalBadges }} نشان کسب شده
        </span>
      </div>
      
      <!-- Tier Summary -->
      <div class="grid grid-cols-4 gap-2 mb-4">
        <div v-for="(count, tier) in tierCounts" :key="tier" 
             class="text-center p-2 rounded-lg border border-gray-200 dark:border-gray-700">
          <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
            {{ getTierName(tier) }}
          </div>
          <div class="font-bold text-sm" :style="{ color: getTierColor(tier) }">
            {{ count }}
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Badges -->
    <div v-if="recentBadges.length > 0" class="mb-6">
      <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">
        📅 آخرین نشان‌ها
      </h4>
      <div class="flex flex-wrap gap-2">
        <div v-for="badge in recentBadges" :key="badge.id"
             class="badge-item recent-badge"
             :title="`${badge.name} - ${badge.description}`">
          <div class="badge-icon" :style="{ backgroundColor: badge.tier_color }">
            <i :class="getBadgeIcon(badge.icon)"></i>
          </div>
          <div class="badge-info">
            <div class="badge-name">{{ badge.name }}</div>
            <div class="badge-date">{{ formatDate(badge.awarded_at) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Badges by Tier -->
    <div class="badges-by-tier">
      <div v-for="tier in ['diamond', 'gold', 'silver', 'bronze']" :key="tier"
           v-show="badgesByTier[tier].length > 0"
           class="tier-section mb-6">
        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3 flex items-center">
          <span class="tier-icon mr-2" :style="{ color: getTierColor(tier) }">
            {{ getTierIcon(tier) }}
          </span>
          {{ getTierName(tier) }}
          <span class="ml-2 text-sm text-gray-500">
            ({{ badgesByTier[tier].length }})
          </span>
        </h4>
        
        <div class="badge-grid">
          <div v-for="badge in badgesByTier[tier]" :key="badge.id"
               class="badge-card"
               :class="{ 'rare-badge': badge.rarity < 10 }"
               :title="`${badge.name} - ${badge.description} (${badge.rarity}% کاربران دارند)`">
            <div class="badge-card-icon" :style="{ backgroundColor: badge.tier_color }">
              <i :class="getBadgeIcon(badge.icon)"></i>
            </div>
            <div class="badge-card-content">
              <div class="badge-card-name">{{ badge.name }}</div>
              <div class="badge-card-description">{{ badge.description }}</div>
              <div class="badge-card-meta">
                <span class="badge-rarity">{{ badge.rarity }}% کاربران</span>
                <span class="badge-date">{{ formatDate(badge.awarded_at) }}</span>
              </div>
            </div>
            <div v-if="badge.rarity < 10" class="rare-indicator">
              ⭐ نادر
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="totalBadges === 0" class="empty-state text-center py-8">
      <div class="text-gray-400 dark:text-gray-600 mb-4">
        <i class="fas fa-medal text-4xl"></i>
      </div>
      <p class="text-gray-500 dark:text-gray-400">
        هنوز هیچ نشانی کسب نشده است
      </p>
      <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
        با شرکت در پیش‌بینی‌ها نشان‌های خود را کسب کنید!
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  badgesByTier: {
    type: Object,
    required: true,
    default: () => ({
      diamond: [],
      gold: [],
      silver: [],
      bronze: []
    })
  },
  recentBadges: {
    type: Array,
    default: () => []
  }
})

// Computed properties
const totalBadges = computed(() => {
  return Object.values(props.badgesByTier).reduce((total, badges) => total + badges.length, 0)
})

const tierCounts = computed(() => {
  return {
    diamond: props.badgesByTier.diamond.length,
    gold: props.badgesByTier.gold.length,
    silver: props.badgesByTier.silver.length,
    bronze: props.badgesByTier.bronze.length
  }
})

// Helper functions
const getTierName = (tier) => {
  const names = {
    diamond: 'الماس',
    gold: 'طلا',
    silver: 'نقره',
    bronze: 'برنز'
  }
  return names[tier] || tier
}

const getTierColor = (tier) => {
  const colors = {
    diamond: '#B9F2FF',
    gold: '#FFD700',
    silver: '#C0C0C0',
    bronze: '#CD7F32'
  }
  return colors[tier] || '#6B7280'
}

const getTierIcon = (tier) => {
  const icons = {
    diamond: '💎',
    gold: '🥇',
    silver: '🥈',
    bronze: '🥉'
  }
  return icons[tier] || '🏅'
}

const getBadgeIcon = (icon) => {
  // Map icon names to Font Awesome classes
  const iconMap = {
    'play-circle': 'fas fa-play-circle',
    'chat-bubble': 'fas fa-comment',
    'user-check': 'fas fa-user-check',
    'user-plus': 'fas fa-user-plus',
    'calendar-check': 'fas fa-calendar-check',
    'sunrise': 'fas fa-sun',
    'target': 'fas fa-bullseye',
    'fire': 'fas fa-fire',
    'chart-bar': 'fas fa-chart-bar',
    'heart': 'fas fa-heart',
    'calendar': 'fas fa-calendar',
    'cog': 'fas fa-cog',
    'crown': 'fas fa-crown',
    'bullseye': 'fas fa-bullseye',
    'lightning': 'fas fa-bolt',
    'trophy': 'fas fa-trophy',
    'star': 'fas fa-star',
    'gem': 'fas fa-gem',
    'medal': 'fas fa-medal',
    'brain': 'fas fa-brain',
    'sword': 'fas fa-sword',
    'surprise': 'fas fa-surprise'
  }
  return iconMap[icon] || 'fas fa-medal'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('fa-IR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
.badge-display {
  @apply bg-white dark:bg-gray-800 rounded-lg p-6;
}

.badge-item {
  @apply flex items-center space-x-3 space-x-reverse bg-gray-50 dark:bg-gray-700 rounded-lg p-3;
}

.recent-badge {
  @apply min-w-0 flex-1;
}

.badge-icon {
  @apply w-8 h-8 rounded-full flex items-center justify-center text-white text-sm;
}

.badge-info {
  @apply min-w-0 flex-1;
}

.badge-name {
  @apply font-medium text-gray-900 dark:text-white text-sm truncate;
}

.badge-date {
  @apply text-xs text-gray-500 dark:text-gray-400;
}

.tier-section {
  @apply border-t border-gray-200 dark:border-gray-700 pt-4 first:border-t-0 first:pt-0;
}

.tier-icon {
  @apply text-xl;
}

.badge-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4;
}

.badge-card {
  @apply bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 relative;
}

.rare-badge {
  @apply ring-2 ring-yellow-400 ring-opacity-50;
}

.badge-card-icon {
  @apply w-10 h-10 rounded-full flex items-center justify-center text-white text-lg mb-3;
}

.badge-card-name {
  @apply font-semibold text-gray-900 dark:text-white text-sm mb-1;
}

.badge-card-description {
  @apply text-xs text-gray-600 dark:text-gray-300 mb-2 leading-tight;
}

.badge-card-meta {
  @apply flex justify-between items-center text-xs text-gray-500 dark:text-gray-400;
}

.badge-rarity {
  @apply font-medium;
}

.rare-indicator {
  @apply absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded-full font-bold;
}

.empty-state {
  @apply border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg;
}

/* RTL Support */
[dir="rtl"] .badge-item {
  @apply space-x-reverse;
}

[dir="rtl"] .badge-info {
  @apply text-right;
}

[dir="rtl"] .badge-card-meta {
  @apply flex-row-reverse;
}

[dir="rtl"] .rare-indicator {
  @apply right-auto left-2;
}
</style>
