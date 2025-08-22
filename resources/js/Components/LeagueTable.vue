<template>
    <section class="py-12 sm:py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12 md:mb-16">
                <h3 class="text-2xl sm:text-3xl md:text-4xl font-800 text-slate-900 mb-2 sm:mb-4">جدول لیگ</h3>
                <p class="text-base sm:text-lg md:text-xl text-slate-600 font-300">جدول زنده پریمیر لیگ انگلیس</p>
                <div v-if="lastUpdated" class="text-xs text-slate-500 mt-2">
                    آخرین بروزرسانی: {{ formatFarsiTime(lastUpdated) }}
                </div>
            </div>

            <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                <!-- Mobile Header -->
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white p-4 sm:p-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-base sm:text-lg font-700 flex items-center gap-2">
                            <span class="text-xl">🏆</span>
                            پریمیر لیگ انگلیس
                        </h4>
                        <div v-if="hasLiveMatches" class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-red-400 rounded-full animate-pulse"></div>
                            <span class="text-xs sm:text-sm text-red-300">بازی زنده</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-right">
                                <th class="px-4 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider">رتبه</th>
                                <th class="px-4 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-right">تیم</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">ب</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">برد</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">مساوی</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">باخت</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">گل زده</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">گل خورده</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">تفاضل</th>
                                <th class="px-2 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">امتیاز</th>
                                <th class="px-4 py-3 text-xs font-600 text-slate-700 uppercase tracking-wider text-center">فرم</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="entry in tableData" :key="entry.team.id" 
                                class="hover:bg-slate-50 transition-colors relative"
                                :class="qualificationBorder(entry.position)">
                                <!-- Position -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1">
                                        <span class="text-sm font-800 text-slate-900 w-6 text-center rounded"
                                              :class="getPositionColor(entry.position)"
                                              :title="qualificationLabel(entry.position)">
                                            {{ entry.position }}
                                        </span>
                                                                                                                        <span v-if="entry.position_change > 0"
                                                                                                                                    class="text-xs font-700 text-green-600"
                                                                                                                                    :title="'صعود ' + entry.position_change">
                                                                                                                                ▲
                                                                                                                        </span>
                                                                                                                        <span v-else-if="entry.position_change < 0"
                                                                                                                                    class="text-xs font-700 text-red-600"
                                                                                                                                    :title="'سقوط ' + Math.abs(entry.position_change)">
                                                                                                                                ▼
                                                                                                                        </span>
                                                                                                                        <span v-else class="text-xs text-slate-400" title="بدون تغییر">•</span>
                                    </div>
                                </td>
                                
                                <!-- Team -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <img :src="`/assets/team-logos/${entry.team.name}.png`" 
                                             :alt="entry.team.name_fa || entry.team.name"
                                             class="w-8 h-8 object-contain flex-shrink-0"
                                             @error="handleImageError">
                                        <div class="flex items-center gap-2">
                                            <div class="font-800 text-slate-900 text-lg flex items-center gap-2">
                                                {{ translateTeamName(entry.team.name) }}
                                                <span v-if="entry.team.is_live || entry.has_live_match" class="flex items-center gap-1 text-xs font-600 text-green-600">
                                                    <span class="w-2 h-2 bg-green-600 rounded-full animate-pulse inline-block"></span>
                                                    زنده
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Stats -->
                                <td class="px-2 py-4 text-center text-sm text-slate-700">{{ entry.played }}</td>
                                <td class="px-2 py-4 text-center text-sm font-600 text-green-700">{{ entry.won }}</td>
                                <td class="px-2 py-4 text-center text-sm font-600 text-yellow-700">{{ entry.drawn }}</td>
                                <td class="px-2 py-4 text-center text-sm font-600 text-red-700">{{ entry.lost }}</td>
                                <td class="px-2 py-4 text-center text-sm text-slate-700">{{ entry.goals_for }}</td>
                                <td class="px-2 py-4 text-center text-sm text-slate-700">{{ entry.goals_against }}</td>
                                <td class="px-2 py-4 text-center text-sm font-600"
                                    :class="entry.goal_difference >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ entry.goal_difference >= 0 ? '+' : '' }}{{ entry.goal_difference }}
                                </td>
                                <td class="px-2 py-4 text-center text-sm font-800 text-slate-900">{{ entry.points }}</td>
                                
                                <!-- Form -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-1">
                                        <span v-for="(result, index) in entry.form?.slice(0, 5) || []" 
                                              :key="index"
                                              class="w-5 h-5 rounded-full text-xs font-700 flex items-center justify-center text-white"
                                              :class="getFormColor(result)">
                                            {{ result }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile / Tablet Tabbed Table -->
                <div class="lg:hidden p-4">
                    <!-- Tabs -->
                    <div class="flex items-center gap-2 mb-4 text-sm">
                        <button
                            v-for="tab in mobileTabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            class="px-3 py-1.5 rounded-full border transition-colors"
                            :class="activeTab === tab.key ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-100'"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="min-w-full text-right text-sm">
                            <thead class="bg-slate-50 text-xs text-slate-600">
                                <tr>
                                    <th class="sticky right-0 bg-slate-50 px-3 py-2 z-10">رتبه / تیم</th>
                                    <th v-if="activeTab === 'short' || activeTab === 'full'" class="px-3 py-2">بازی</th>
                                    <th v-if="activeTab === 'short' || activeTab === 'full'" class="px-3 py-2">برد</th>
                                    <th v-if="activeTab === 'full'" class="px-3 py-2">مساوی</th>
                                    <th v-if="activeTab === 'full'" class="px-3 py-2">باخت</th>
                                    <th v-if="activeTab === 'full'" class="px-3 py-2">گل زده</th>
                                    <th v-if="activeTab === 'full'" class="px-3 py-2">گل خورده</th>
                                    <th v-if="activeTab !== 'form'" class="px-3 py-2">تفاضل</th>
                                    <th v-if="activeTab !== 'form'" class="px-3 py-2">امتیاز</th>
                                    <th v-if="activeTab === 'form'" class="px-3 py-2">فرم</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="entry in tableData" :key="entry.team.id" class="border-t hover:bg-slate-50 text-slate-800"
                                    :class="qualificationBorder(entry.position)">
                                    <!-- Team sticky column -->
                                    <td class="sticky right-0 bg-white px-3 py-2 max-w-[170px] z-10 shadow-[_-4px_0_4px_-2px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-800 w-5 text-center rounded" :class="getPositionColor(entry.position)" :title="qualificationLabel(entry.position)">
                                                {{ entry.position }}
                                            </span>
                                                                          <span v-if="entry.position_change > 0"
                                                                              class="text-[10px] font-700 text-green-600"
                                                                              :title="'صعود ' + entry.position_change">▲</span>
                                                                          <span v-else-if="entry.position_change < 0"
                                                                              class="text-[10px] font-700 text-red-600"
                                                                              :title="'سقوط ' + Math.abs(entry.position_change)">▼</span>
                                                                          <span v-else class="text-[10px] text-slate-400" title="بدون تغییر">•</span>
                                            <img :src="`/assets/team-logos/${entry.team.name}.png`" 
                                                 :alt="entry.team.name_fa || entry.team.name"
                                                 class="w-6 h-6 object-contain flex-shrink-0"
                                                 @error="handleImageError">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[13px] font-800 truncate max-w-[90px]">{{ translateTeamName(entry.team.name) }}</span>
                                                <span v-if="entry.team.is_live || entry.has_live_match" class="w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td v-if="activeTab === 'short' || activeTab === 'full'" class="px-3 py-2 text-center">{{ entry.played }}</td>
                                    <td v-if="activeTab === 'short' || activeTab === 'full'" class="px-3 py-2 text-center font-600 text-green-700">{{ entry.won }}</td>
                                    <td v-if="activeTab === 'full'" class="px-3 py-2 text-center font-600 text-yellow-700">{{ entry.drawn }}</td>
                                    <td v-if="activeTab === 'full'" class="px-3 py-2 text-center font-600 text-red-700">{{ entry.lost }}</td>
                                    <td v-if="activeTab === 'full'" class="px-3 py-2 text-center">{{ entry.goals_for }}</td>
                                    <td v-if="activeTab === 'full'" class="px-3 py-2 text-center">{{ entry.goals_against }}</td>
                                    <td v-if="activeTab !== 'form'" class="px-3 py-2 text-center" :class="entry.goal_difference >= 0 ? 'text-green-700' : 'text-red-700'">
                                        {{ entry.goal_difference >= 0 ? '+' : '' }}{{ entry.goal_difference }}
                                    </td>
                                    <td v-if="activeTab !== 'form'" class="px-3 py-2 text-center font-700">{{ entry.points }}</td>
                                    <td v-if="activeTab === 'form'" class="px-3 py-2">
                                        <div class="flex items-center gap-1 justify-center">
                                            <span v-for="(result, index) in entry.form?.slice(0,5) || []" :key="index"
                                                  class="w-5 h-5 rounded-full text-[10px] font-700 flex items-center justify-center text-white"
                                                  :class="getFormColor(result)">{{ result }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="p-8 text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900 mx-auto mb-4"></div>
                    <div class="text-slate-600">در حال بارگذاری جدول...</div>
                </div>

                <!-- Error State -->
                <div v-if="error" class="p-8 text-center text-red-600">
                    <div class="text-4xl mb-4">⚠️</div>
                    <div>خطا در بارگذاری جدول</div>
                    <button @click="fetchLeagueTable" 
                            class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        تلاش مجدد
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="!loading && !error && tableData.length === 0" class="p-8 text-center text-slate-500">
                    <div class="text-4xl mb-4">📊</div>
                    <div>جدول لیگ در دسترس نیست</div>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs text-slate-600">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-100 border border-green-400 rounded"></div>
                    <span>لیگ قهرمانان اروپا</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-blue-100 border border-blue-400 rounded"></div>
                    <span>لیگ اروپا</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-yellow-100 border border-yellow-400 rounded"></div>
                    <span>لیگ کنفرانس</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-red-100 border border-red-400 rounded"></div>
                    <span>سقوط</span>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useTranslations } from '@/composables/useTranslations.js';


// State
const tableData = ref([])
const loading = ref(true)
const error = ref(false)
const lastUpdated = ref(null)

const { translateTeamName, t } = useTranslations();


// Computed properties
const hasLiveMatches = computed(() => {
    return tableData.value.some(entry => entry.team?.is_live || entry.has_live_match)
})

// Mobile tab state
const mobileTabs = [
    { key: 'short', label: 'کوتاه' },
    { key: 'full', label: 'کامل' },
    { key: 'form', label: 'فرم' },
]
const activeTab = ref('short')

// Row qualification border (aggressive right border color) based on live position
const qualificationBorder = (position) => {
    if (position <= 4) return 'border-r-4 border-green-600'
    if (position === 5) return 'border-r-4 border-blue-600'
    if (position === 6) return 'border-r-4 border-yellow-500'
    if (position >= 18) return 'border-r-4 border-red-600'
    return 'border-r border-transparent'
}

const qualificationLabel = (position) => {
    if (position <= 4) return 'لیگ قهرمانان اروپا'
    if (position === 5) return 'لیگ اروپا'
    if (position === 6) return 'لیگ کنفرانس'
    if (position >= 18) return 'سقوط'
    return ''
}

// Methods
const fetchLeagueTable = async () => {
    try {
        loading.value = true
        error.value = false
        
        const response = await fetch('/api/league-table')
        const data = await response.json()
        
        if (response.ok) {
            tableData.value = data.table || []
            lastUpdated.value = data.last_updated
        } else {
            throw new Error(data.error || 'خطا در دریافت داده‌ها')
        }
    } catch (err) {
        console.error('Error fetching league table:', err)
        error.value = true
    } finally {
        loading.value = false
    }
}

const getPositionColor = (position) => {
    if (position <= 4) return 'text-green-700'
    if (position === 5) return 'text-blue-700'
    if (position === 6) return 'text-yellow-700'
    if (position >= 18) return 'text-red-700'
    return 'text-slate-700'
}

const getFormColor = (result) => {
    switch (result) {
        case 'W': return 'bg-green-500'
        case 'D': return 'bg-yellow-500'
        case 'L': return 'bg-red-500'
        default: return 'bg-slate-400'
    }
}

const handleImageError = (event) => {
    event.target.style.display = 'none'
}

const formatFarsiTime = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleString('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Auto-refresh every 30 seconds if there are live matches
let refreshInterval = null

const startAutoRefresh = () => {
    refreshInterval = setInterval(() => {
        if (hasLiveMatches.value) {
            fetchLeagueTable()
        }
    }, 30000) // 30 seconds
}

const stopAutoRefresh = () => {
    if (refreshInterval) {
        clearInterval(refreshInterval)
        refreshInterval = null
    }
}

// Lifecycle
onMounted(() => {
    fetchLeagueTable()
    startAutoRefresh()
})

onUnmounted(() => {
    stopAutoRefresh()
})
</script>

<style scoped>
.brand-card {
    background: white;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    padding: 1rem;
    transition: box-shadow 0.15s ease-in-out;
}

.brand-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.brand-section-dark {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}
</style>
