<script setup>
import { computed, ref } from 'vue';
import { useTranslations } from '@/composables/useTranslations.js';

const props = defineProps({
  topPredictors: { type: Array, default: () => [] },
  trendingMatches: { type: Array, default: () => [] }
});

const { translateTeamName } = useTranslations();

// Local demo interaction (model vs community probabilities)
const selectedMatchId = ref(0);
const demoMatches = computed(() => (props.trendingMatches || []).map((m, idx) => ({
    id: idx,
    label: `${translateTeamName(m.home_team)} vs ${translateTeamName(m.away_team)}`,
    community: [m.prob_home, m.prob_draw, m.prob_away],
    model: [m.model_prob_home ?? m.prob_home, m.model_prob_draw ?? m.prob_draw, m.model_prob_away ?? m.prob_away]
})));
const currentDemo = computed(() => demoMatches.value.find(m => m.id === selectedMatchId.value) || demoMatches.value[0]);
const demoView = computed(() => {
  if (!currentDemo.value) return [];
  const labels = ['برد میزبان','مساوی','برد مهمان'];
  return labels.map((l,i)=> ({ label:l, model: currentDemo.value.model[i], community: currentDemo.value.community[i] }));
});
</script>

<template>
  <section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-1 gap-12">
        <!-- Leaderboard -->
        <div>
          <h3 class="text-2xl font-800 text-slate-900 mb-6">برترین پیش‌بین‌های این هفته</h3>
          <ul class="space-y-4" v-if="topPredictors && topPredictors.length">
            <li v-for="(user,i) in topPredictors" :key="i" class="flex items-center justify-between bg-slate-50 rounded-xl p-4 border border-slate-200">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-200/20 to-slate-200 flex items-center justify-center font-700 text-slate-700">
                  {{ (user.name||'?').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="font-700 text-slate-900">{{ user.name }}</div>
                  <div class="text-xs text-slate-500">رتبه {{ i+1 }}</div>
                </div>
              </div>
              <div class="text-sm font-700 text-brand-200">{{ user.total_points || 0 }} امتیاز</div>
            </li>
          </ul>
          <div v-else class="text-sm text-slate-500">داده‌ای برای نمایش وجود ندارد.</div>
          <div class="mt-6 text-xs text-slate-500">امتیاز بر اساس امتیازات کسب شده از پیش‌بینی‌های موفق محاسبه می‌شود.</div>
        </div>
        <!-- Demo Interaction -->
        <!-- <div>
          <h3 class="text-2xl font-800 text-slate-900 mb-6">مقایسه مدل با جامعه</h3>
          <div v-if="demoMatches.length" class="space-y-6">
            <select v-model="selectedMatchId" class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-brand-200">
              <option v-for="m in demoMatches" :key="m.id" :value="m.id">{{ m.label }}</option>
            </select>
            <div class="grid grid-cols-3 gap-4">
              <div v-for="row in demoView" :key="row.label" class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <div class="text-xs text-slate-500 mb-2">{{ row.label }}</div>
                <div class="h-24 flex items-end gap-1">
                  <div class="flex-1 flex flex-col justify-end">
                    <div class="w-full bg-brand-200/50 rounded" :style="{height: row.model + '%'}" title="مدل"></div>
                    <div class="mt-1 text-[10px] text-slate-600 text-center">مدل {{ row.model }}%</div>
                  </div>
                  <div class="flex-1 flex flex-col justify-end">
                    <div class="w-full bg-slate-300 rounded" :style="{height: row.community + '%'}" title="جامعه"></div>
                    <div class="mt-1 text-[10px] text-slate-600 text-center">جامعه {{ row.community }}%</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="text-xs text-slate-500">این تفاوت‌ها نمایشی است و برای درک سریع ساختار احتمالات ارائه شده است.</div>
          </div>
          <div v-else class="text-sm text-slate-500">داده‌ای برای نمایش موجود نیست.</div>
        </div> -->
      </div>
    </div>
  </section>
</template>
