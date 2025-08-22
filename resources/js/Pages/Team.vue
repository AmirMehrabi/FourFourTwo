<template>
  <section class="min-h-screen bg-gradient-to-br from-slate-50 to-white pb-16">
    <!-- Colorful Hero -->
    <div class="w-full bg-gradient-to-r from-[var(--team-primary,#7b0681)] to-[var(--team-secondary,#16a34a)] py-10 px-4 flex flex-col md:flex-row items-center gap-8 shadow-lg">
      <img :src="team.logoUrl" :alt="team.displayName" class="w-32 h-32 rounded-full border-4 border-white shadow-xl object-contain" />
      <div class="flex-1 text-white">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-2 flex items-center gap-3">
          <span>{{ team.displayName }}</span>
          <span v-if="team.name !== team.displayName" class="text-2xl text-white/70 font-normal">({{ team.name }})</span>
        </h1>
        <div class="flex flex-wrap gap-3 mb-3">
          <span class="inline-block px-4 py-2 rounded-full text-base font-bold bg-white/20">رتبه: {{ league.position }}</span>
          <span class="inline-block px-4 py-2 rounded-full text-base font-bold bg-white/20">امتیاز: {{ league.points }}</span>
          <span class="inline-block px-4 py-2 rounded-full text-base font-bold bg-white/20">تفاضل: {{ league.goal_difference }}</span>
          <span class="inline-block px-4 py-2 rounded-full text-base font-bold bg-white/20">بازی: {{ league.played }}</span>
        </div>
        <div class="flex gap-2">
          <span v-for="(result, idx) in league.formArray" :key="idx" class="w-8 h-8 rounded-full flex items-center justify-center text-lg font-bold text-white shadow-lg" :class="formColor(result)">{{ result }}</span>
        </div>
        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
          <div><span class="font-bold">تأسیس:</span> {{ team.founded_year || '-' }}</div>
          <div><span class="font-bold">شهر:</span> {{ team.city_fa || team.city || '-' }}</div>
          <div><span class="font-bold">ورزشگاه:</span> {{ team.stadium_name_fa || team.stadium_name || '-' }}</div>
          <div><span class="font-bold">ظرفیت:</span> {{ team.stadium_capacity || '-' }}</div>
          <div><span class="font-bold">مدیر:</span> {{ team.manager_fa || team.manager || '-' }}</div>
          <div><span class="font-bold">وبسایت:</span> <a v-if="team.website_url" :href="team.website_url" target="_blank" class="underline">مشاهده</a><span v-else>-</span></div>
        </div>
      </div>
    </div>

    <!-- Quick Facts -->
    <div class="max-w-5xl mx-auto px-4 mt-8 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 text-sm">
      <div class="bg-white rounded-lg shadow p-3 flex flex-col items-center">
        <span class="font-bold text-slate-700">تأسیس</span>
        <span class="text-slate-500">{{ team.founded_year || '-' }}</span>
      </div>
      <div class="bg-white rounded-lg shadow p-3 flex flex-col items-center">
        <span class="font-bold text-slate-700">شهر</span>
        <span class="text-slate-500">{{ team.city_fa || team.city || '-' }}</span>
      </div>
      <div class="bg-white rounded-lg shadow p-3 flex flex-col items-center">
        <span class="font-bold text-slate-700">ورزشگاه</span>
        <span class="text-slate-500">{{ team.stadium_name_fa || team.stadium_name || '-' }}</span>
      </div>
      <div class="bg-white rounded-lg shadow p-3 flex flex-col items-center">
        <span class="font-bold text-slate-700">ظرفیت</span>
        <span class="text-slate-500">{{ team.stadium_capacity || '-' }}</span>
      </div>
      <div class="bg-white rounded-lg shadow p-3 flex flex-col items-center">
        <span class="font-bold text-slate-700">مدیر</span>
        <span class="text-slate-500">{{ team.manager_fa || team.manager || '-' }}</span>
      </div>
      <div class="bg-white rounded-lg shadow p-3 flex flex-col items-center">
        <span class="font-bold text-slate-700">وبسایت</span>
        <a v-if="team.website_url" :href="team.website_url" target="_blank" class="text-blue-600 underline">مشاهده</a>
        <span v-else class="text-slate-400">-</span>
      </div>
    </div>


    <!-- Next Match Card -->
    <div class="max-w-3xl mx-auto mt-10 px-4">
      <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col md:flex-row items-center gap-6">
        <div class="flex items-center gap-4">
          <img :src="nextMatch.value.opponent_logo || '/assets/team-logos/default.png'" :alt="nextMatch.value.opponent_name || 'حریف'" class="w-16 h-16 rounded-full object-contain border-2 border-slate-200" />
          <div>
            <div class="text-xl font-bold text-slate-900">هفته {{ nextMatch.value.matchweek }}: {{ nextMatch.value.opponent_name }}</div>
            <div class="text-slate-500">{{ nextMatch.value.date }} | {{ nextMatch.value.time }} | {{ nextMatch.value.venue }}</div>
          </div>
        </div>
        <div class="flex-1"></div>
        <div v-if="!nextMatch.value.locked" class="flex flex-col items-center">
          <form @submit.prevent="submitPrediction" class="flex gap-2 items-center">
            <input type="number" v-model="userPrediction.home" min="0" max="10" class="w-16 px-2 py-1 rounded border border-slate-300" placeholder="گل تیم شما" />
            <span class="font-bold text-slate-700">-</span>
            <input type="number" v-model="userPrediction.away" min="0" max="10" class="w-16 px-2 py-1 rounded border border-slate-300" placeholder="گل حریف" />
            <button type="submit" class="px-4 py-1 rounded bg-slate-900 text-white font-bold">ثبت</button>
          </form>
          <div v-if="predictionSubmitted" class="text-green-600 mt-2">پیش‌بینی شما ثبت شد!</div>
        </div>
        <div v-else class="text-slate-400 font-bold">پیش‌بینی بسته شده است</div>
      </div>
    </div>


    <!-- Team Forms Timeline -->
    <div class="max-w-5xl mx-auto px-4 mt-10">
      <h2 class="text-xl font-bold text-slate-800 mb-4">Team Forms</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border-separate border-spacing-y-2">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-3 py-2">MW</th>
              <th class="px-3 py-2">Avatar</th>
              <th class="px-3 py-2">Team Name</th>
              <th class="px-3 py-2">Home/Away</th>
              <th class="px-3 py-2">Score/Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="form in teamForms" :key="form.matchweek" class="bg-white rounded shadow">
              <td class="px-3 py-2 font-bold">{{ form.matchweek }}</td>
              <td class="px-3 py-2"><img :src="form.avatar" :alt="form.team_name" class="w-8 h-8 rounded-full object-contain" /></td>
              <td class="px-3 py-2 font-bold">{{ form.team_name }}</td>
              <td class="px-3 py-2">{{ form.home_or_away }}</td>
              <td class="px-3 py-2">
                <span v-if="form.is_past" class="font-bold text-lg" :class="resultColor(form.result)">{{ form.score }}</span>
                <span v-else class="text-slate-500">{{ form.date }} {{ form.time }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>


    <!-- League Table with Permanent Hover -->
    <div class="max-w-5xl mx-auto px-4 mt-10">
      <h2 class="text-xl font-bold text-slate-800 mb-4">جدول لیگ</h2>
      <div class="bg-white rounded-lg shadow p-3">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-right text-slate-500">
              <th>رتبه</th>
              <th>تیم</th>
              <th>امتیاز</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in leagueContext" :key="row.team_id" :class="row.team_id === team.id ? 'bg-gradient-to-r from-green-100 to-blue-100 font-bold shadow-lg' : 'hover:bg-slate-50'">
              <td>{{ row.position }}</td>
              <td>{{ row.team_name }}</td>
              <td>{{ row.points }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Home vs Away Comparison -->
    <div class="max-w-5xl mx-auto px-4 mt-10">
      <h2 class="text-lg font-bold text-slate-800 mb-3">مقایسه خانه و خارج</h2>
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
          <h3 class="font-bold text-green-700 mb-2">خانه</h3>
          <div v-for="stat in homeStats" :key="stat.label" class="flex justify-between text-sm mb-1">
            <span class="text-slate-600">{{ stat.label }}</span>
            <span class="font-bold text-slate-900">{{ stat.value }}</span>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <h3 class="font-bold text-blue-700 mb-2">خارج</h3>
          <div v-for="stat in awayStats" :key="stat.label" class="flex justify-between text-sm mb-1">
            <span class="text-slate-600">{{ stat.label }}</span>
            <span class="font-bold text-slate-900">{{ stat.value }}</span>
          </div>
        </div>
      </div>
    </div>


    <!-- Modern Trend & Goals Charts -->
    <div class="max-w-5xl mx-auto px-4 mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
      <div>
        <h2 class="text-xl font-bold text-slate-800 mb-4">روند موقعیت تیم</h2>
        <div class="bg-white rounded-lg shadow p-6 flex items-center justify-center">
          <!-- Modern dummy sparkline -->
          <svg width="220" height="60">
            <defs>
              <linearGradient id="trendGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#7b0681" />
                <stop offset="100%" stop-color="#16a34a" />
              </linearGradient>
            </defs>
            <polyline :points="trendPoints" fill="none" stroke="url(#trendGrad)" stroke-width="4" />
            <circle v-for="(pt, i) in trendCirclePoints" :key="i" :cx="pt.x" :cy="pt.y" r="4" fill="#7b0681" />
          </svg>
        </div>
      </div>
      <div>
        <h2 class="text-xl font-bold text-slate-800 mb-4">گل‌های زده و خورده</h2>
        <div class="bg-white rounded-lg shadow p-6 flex items-center justify-center">
          <!-- Modern dummy bar chart -->
          <svg width="220" height="60">
            <defs>
              <linearGradient id="gfGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#16a34a" />
                <stop offset="100%" stop-color="#bbf7d0" />
              </linearGradient>
              <linearGradient id="gaGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#dc2626" />
                <stop offset="100%" stop-color="#fee2e2" />
              </linearGradient>
            </defs>
            <rect v-for="(g, i) in goalsFor" :key="'gf'+i" :x="i*24+10" :y="60-g" width="10" :height="g" fill="url(#gfGrad)" rx="3" />
            <rect v-for="(g, i) in goalsAgainst" :key="'ga'+i" :x="i*24+22" :y="60-g" width="10" :height="g" fill="url(#gaGrad)" rx="3" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Clean Sheets & Scoring Reliability -->
    <div class="max-w-5xl mx-auto px-4 mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">کلین شیت</h2>
        <div class="text-2xl font-extrabold text-green-700">{{ cleanSheets }}</div>
        <div class="text-slate-500">در {{ league.played }} بازی</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">عدم گلزنی</h2>
        <div class="text-2xl font-extrabold text-red-700">{{ failedToScore }}</div>
        <div class="text-slate-500">در {{ league.played }} بازی</div>
      </div>
    </div>

    <!-- Head-to-Head vs Next Opponent -->
    <div class="max-w-5xl mx-auto px-4 mt-10">
      <h2 class="text-lg font-bold text-slate-800 mb-3">تقابل با حریف بعدی</h2>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-3 mb-2">
          <img :src="nextOpponent.logo" :alt="nextOpponent.name" class="w-10 h-10 rounded-full object-contain" />
          <span class="font-bold text-slate-700">{{ nextOpponent.name }}</span>
        </div>
        <div class="text-slate-500 mb-2">آخرین ۵ بازی: {{ headToHeadSummary }}</div>
        <div class="flex gap-2">
          <span v-for="(result, idx) in headToHeadResults" :key="idx" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" :class="formColor(result)">{{ result }}</span>
        </div>
      </div>
    </div>

    <!-- Predictions Widget & Form -->
    <div class="max-w-5xl mx-auto px-4 mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">پیش‌بینی کاربران</h2>
        <div class="flex gap-2 mb-2">
          <div v-for="(pred, idx) in predictionsSummary" :key="idx" class="flex flex-col items-center">
            <span class="text-xs text-slate-500">{{ pred.score }}</span>
            <span class="w-8 h-8 rounded-full flex items-center justify-center text-lg font-bold text-white bg-slate-700">{{ pred.count }}</span>
          </div>
        </div>
        <div class="text-slate-500">میانگین پیش‌بینی: {{ predictionsAverage }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">پیش‌بینی شما</h2>
        <form @submit.prevent="submitPrediction" class="flex gap-2 items-center">
          <input type="number" v-model="userPrediction.home" min="0" max="10" class="w-16 px-2 py-1 rounded border border-slate-300" placeholder="گل تیم شما" />
          <span class="font-bold text-slate-700">-</span>
          <input type="number" v-model="userPrediction.away" min="0" max="10" class="w-16 px-2 py-1 rounded border border-slate-300" placeholder="گل حریف" />
          <button type="submit" class="px-4 py-1 rounded bg-slate-900 text-white font-bold">ثبت</button>
        </form>
        <div v-if="predictionSubmitted" class="text-green-600 mt-2">پیش‌بینی شما ثبت شد!</div>
      </div>
    </div>

    <!-- Seasonal Progress Bar -->
    <div class="max-w-5xl mx-auto px-4 mt-10">
      <h2 class="text-lg font-bold text-slate-800 mb-3">پیشرفت فصل</h2>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="w-full h-6 bg-slate-100 rounded-full overflow-hidden">
          <div class="h-6 bg-gradient-to-r from-green-400 to-blue-400" :style="{ width: seasonProgress + '%' }"></div>
        </div>
        <div class="text-xs text-slate-500 mt-2">{{ league.played }} از 38 بازی انجام شده</div>
      </div>
    </div>

    <!-- Advanced Analytics & Venue Profile & Player Stats & Tiers & Projection -->
    <div class="max-w-5xl mx-auto px-4 mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">آنالیز پیشرفته (دمی)</h2>
        <div class="text-slate-500">xG: {{ advancedAnalytics.xg }}, xGA: {{ advancedAnalytics.xga }}, Possession: {{ advancedAnalytics.possession }}%</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">پروفایل ورزشگاه (دمی)</h2>
        <div class="flex items-center gap-3 mb-2">
          <img :src="venue.photo_url" :alt="venue.name" class="w-10 h-10 rounded object-cover" />
          <span class="font-bold text-slate-700">{{ venue.name }}</span>
        </div>
        <div class="text-slate-500">شهر: {{ venue.city }} | ظرفیت: {{ venue.capacity }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">آمار بازیکنان (دمی)</h2>
        <div class="text-slate-500">گلزن برتر: {{ playerStats.topScorer }} ({{ playerStats.goals }})</div>
        <div class="text-slate-500">پاس گل: {{ playerStats.topAssist }} ({{ playerStats.assists }})</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">عملکرد مقابل رقبا (دمی)</h2>
        <div class="text-slate-500">نتایج مقابل تیم‌های بالای جدول: {{ performanceVsTiers.top }}</div>
        <div class="text-slate-500">نتایج مقابل تیم‌های پایین جدول: {{ performanceVsTiers.bottom }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-slate-800 mb-3">پیش‌بینی امتیاز نهایی (دمی)</h2>
        <div class="text-slate-500">پیش‌بینی: {{ pointsProjection }}</div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';

const defaultNextMatch = {
  opponent_logo: '/assets/team-logos/default.png',
  opponent_name: 'حریف',
  matchweek: '-',
  date: '-',
  time: '-',
  venue: '-',
  locked: true,
};

const props = defineProps({
  team: Object,
  nextMatch: Object,
});

const nextMatch = computed(() => props.nextMatch ?? defaultNextMatch);
// Dummy data for widgets not yet implemented
const team = ref({
  id: 1,
  name: 'Manchester City',
  displayName: 'منچسترسیتی',
  logoUrl: '/assets/team-logos/Manchester City.png',
  founded_year: 1880,
  city: 'Manchester',
  city_fa: 'منچستر',
  stadium_name: 'Etihad Stadium',
  stadium_name_fa: 'اتحاد',
  stadium_capacity: 55000,
  manager: 'Pep Guardiola',
  manager_fa: 'پپ گواردیولا',
  website_url: 'https://www.mancity.com',
});
const league = ref({
  position: 1,
  points: 85,
  goal_difference: 45,
  played: 36,
  won: 27,
  drawn: 4,
  lost: 5,
  goals_for: 90,
  goals_against: 45,
  form: 'WWDWL',
  formArray: ['W','W','D','W','L'],
});
const kpis = [
  { label: 'برد', value: league.value.won },
  { label: 'مساوی', value: league.value.drawn },
  { label: 'باخت', value: league.value.lost },
  { label: 'گل زده', value: league.value.goals_for },
  { label: 'گل خورده', value: league.value.goals_against },
  { label: 'درصد برد', value: Math.round((league.value.won/league.value.played)*100)+'%' },
];
const recentResults = [
  { id: 1, opponent_logo: '/assets/team-logos/Arsenal.png', opponent_name: 'آرسنال', home_or_away: 'home', home_score: 2, away_score: 1, result: 'W', date: '2025-08-10' },
  { id: 2, opponent_logo: '/assets/team-logos/Liverpool.png', opponent_name: 'لیورپول', home_or_away: 'away', home_score: 1, away_score: 1, result: 'D', date: '2025-08-03' },
  { id: 3, opponent_logo: '/assets/team-logos/Chelsea.png', opponent_name: 'چلسی', home_or_away: 'home', home_score: 3, away_score: 0, result: 'W', date: '2025-07-27' },
  { id: 4, opponent_logo: '/assets/team-logos/Manchester United.png', opponent_name: 'منچستریونایتد', home_or_away: 'away', home_score: 0, away_score: 2, result: 'L', date: '2025-07-20' },
  { id: 5, opponent_logo: '/assets/team-logos/Tottenham Hotspur.png', opponent_name: 'تاتنهام', home_or_away: 'home', home_score: 2, away_score: 0, result: 'W', date: '2025-07-13' },
];
const upcomingFixtures = [
  { id: 6, opponent_logo: '/assets/team-logos/Newcastle United.png', opponent_name: 'نیوکاسل', home_or_away: 'away', matchweek: 37, status: 'scheduled', date: '2025-08-17' },
  { id: 7, opponent_logo: '/assets/team-logos/West Ham United.png', opponent_name: 'وست‌هم', home_or_away: 'home', matchweek: 38, status: 'scheduled', date: '2025-08-24' },
];
const streakText = '3 بازی بدون شکست';
const leagueContext = [
  { team_id: 2, position: 2, team_name: 'آرسنال', points: 80 },
  { team_id: 3, position: 3, team_name: 'لیورپول', points: 78 },
  { team_id: 1, position: 1, team_name: 'منچسترسیتی', points: 85 },
  { team_id: 4, position: 4, team_name: 'چلسی', points: 75 },
  { team_id: 5, position: 5, team_name: 'تاتنهام', points: 72 },
];
const homeStats = [
  { label: 'بازی', value: 18 },
  { label: 'برد', value: 15 },
  { label: 'مساوی', value: 2 },
  { label: 'باخت', value: 1 },
  { label: 'گل زده', value: 50 },
  { label: 'گل خورده', value: 15 },
  { label: 'امتیاز', value: 47 },
];
const awayStats = [
  { label: 'بازی', value: 18 },
  { label: 'برد', value: 12 },
  { label: 'مساوی', value: 2 },
  { label: 'باخت', value: 4 },
  { label: 'گل زده', value: 40 },
  { label: 'گل خورده', value: 30 },
  { label: 'امتیاز', value: 38 },
];
const trendPoints = '0,30 30,20 60,25 90,10 120,15 150,5 180,10';
const goalsFor = [30, 25, 20, 15, 10, 5, 10];
const goalsAgainst = [10, 15, 20, 25, 30, 35, 30];
const cleanSheets = 15;
const failedToScore = 3;
const nextOpponent = { logo: '/assets/team-logos/Arsenal.png', name: 'آرسنال' };
const headToHeadSummary = '3 برد، 1 مساوی، 1 باخت';
const headToHeadResults = ['W','W','D','L','W'];
const predictionsSummary = [
  { score: '2-1', count: 12 },
  { score: '1-1', count: 8 },
  { score: '3-0', count: 5 },
];
const predictionsAverage = '2.1 - 1.2';
const userPrediction = ref({ home: '', away: '' });
const predictionSubmitted = ref(false);
function submitPrediction() {
  predictionSubmitted.value = true;
  setTimeout(() => predictionSubmitted.value = false, 2000);
}
const seasonProgress = computed(() => Math.round((league.value.played/38)*100));
const advancedAnalytics = { xg: 2.3, xga: 1.1, possession: 62 };
const venue = { photo_url: '/assets/venue.png', name: 'اتحاد', city: 'منچستر', capacity: 55000 };
const playerStats = { topScorer: 'هالند', goals: 32, topAssist: 'دی بروینه', assists: 18 };
const performanceVsTiers = { top: '3 برد، 2 باخت', bottom: '5 برد' };
const pointsProjection = 89;
function formColor(result) {
  if (result === 'W') return 'bg-green-500';
  if (result === 'D') return 'bg-yellow-500';
  if (result === 'L') return 'bg-red-500';
  return 'bg-slate-400';
}
function resultColor(result) {
  if (result === 'W') return 'bg-green-100 text-green-700';
  if (result === 'D') return 'bg-yellow-100 text-yellow-700';
  if (result === 'L') return 'bg-red-100 text-red-700';
  return 'bg-slate-100 text-slate-700';
}
</script>

<style scoped>
/* Custom styles for sparkline, etc. */
</style>
