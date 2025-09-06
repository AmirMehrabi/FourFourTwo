<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations.js';

const { translateTeamName } = useTranslations();

const props = defineProps({
    teams: {
        type: Array,
        default: () => []
    },
    showLoadButton: {
        type: Boolean,
        default: true
    }
});

// Transform league table data to display format
const leagueTableData = computed(() => {
    return props.teams.map((team) => ({
        id: team.team?.id || team.id,
        name: team.team?.name || team.name,
        team: team.team || team, // Keep full team object for translation
        position: team.position || 0,
        matches_played: team.played || 0,
        points: team.points || 0
    }));
});

const isExpanded = ref(false);
const maxVisibleTeams = 8;

const visibleTeams = computed(() => {
    if (isExpanded.value || leagueTableData.value.length <= maxVisibleTeams) {
        return leagueTableData.value;
    }
    return leagueTableData.value.slice(0, maxVisibleTeams);
});

const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value;
};
</script>

<template>
    <div class="simple-league-table bg-white border border-gray-200 rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">جدول لیگ</h3>
                <Link 
                    v-if="showLoadButton"
                    :href="route('league-table.index')" 
                    class="text-xs text-blue-600 hover:text-blue-700"
                >
                    مشاهده کامل
                </Link>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-hidden">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-right text-gray-600 font-medium">رتبه</th>
                        <th class="px-3 py-2 text-right text-gray-600 font-medium">تیم</th>
                        <th class="px-3 py-2 text-center text-gray-600 font-medium">بازی</th>
                        <th class="px-3 py-2 text-center text-gray-600 font-medium">امتیاز</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr 
                        v-for="(team, index) in visibleTeams" 
                        :key="team.id"
                        class="hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-3 py-2 text-center">
                            <span class="w-5 h-5 inline-flex items-center justify-center rounded text-xs font-medium"
                                  :class="index < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'">
                                {{ (index + 1).toLocaleString('fa-IR') }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <img 
                                    :src="`/assets/team-logos/${team.name}.png`"
                                    :alt="team.name"
                                    class="w-5 h-5 object-contain"
                                    @error="$event.target.style.display = 'none'"
                                    loading="lazy"
                                />
                                <span class="font-medium text-gray-900 truncate">{{ translateTeamName(team.team?.name || team.name) }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-center text-gray-600">{{ team.matches_played || 0 }}</td>
                        <td class="px-3 py-2 text-center font-semibold text-gray-900">{{ team.points || 0 }}</td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Load More Button -->
            <div v-if="!isExpanded && leagueTableData.length > maxVisibleTeams" class="p-3 border-t border-gray-200 bg-gray-50">
                <button 
                    @click="toggleExpanded"
                    class="w-full py-2 text-xs font-medium text-gray-600 hover:text-gray-900 transition-colors flex items-center justify-center gap-1"
                >
                    <span>مشاهده {{ leagueTableData.length - maxVisibleTeams }} تیم دیگر</span>
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            <!-- Collapse Button -->
            <div v-if="isExpanded && leagueTableData.length > maxVisibleTeams" class="p-3 border-t border-gray-200 bg-gray-50">
                <button 
                    @click="toggleExpanded"
                    class="w-full py-2 text-xs font-medium text-gray-600 hover:text-gray-900 transition-colors flex items-center justify-center gap-1"
                >
                    <span>کمتر نشان بده</span>
                    <svg class="w-3 h-3 rotate-180" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.simple-league-table {
    font-size: 0.75rem;
}

@media (max-width: 640px) {
    .simple-league-table {
        font-size: 0.7rem;
    }
}
</style>
