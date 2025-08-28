<script setup>
import { ref, computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations.js';

const { translateTeamName } = useTranslations();

// Demo state
const currentStep = ref(0);
const demoScore = ref({ home: null, away: null });
const isAnimating = ref(false);
const showResult = ref(false);

// Demo match data
const demoMatch = {
  home_team: 'Liverpool',
  away_team: 'Arsenal',
  match_datetime: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString(), // 2 days from now
  actual_result: { home: 2, away: 1 }
};

// Demo steps
const steps = [
  {
    title: "انتخاب مسابقه",
    description: "از لیست مسابقات آتی، بازی مورد نظرت رو انتخاب کن",
    icon: "⚽"
  },
  {
    title: "پیش‌بینی نتیجه",
    description: "نتیجه‌ای که فکر می‌کنی درست باشه رو وارد کن",
    icon: "🎯"
  },
  {
    title: "ذخیره خودکار",
    description: "پیش‌بینی‌ت خودکار ذخیره میشه، نگران نباش!",
    icon: "💾"
  },
  {
    title: "کسب امتیاز",
    description: "بعد از تموم شدن بازی، امتیازت محاسبه میشه",
    icon: "🏆"
  }
];

const nextStep = () => {
  if (currentStep.value < steps.length - 1) {
    isAnimating.value = true;
    setTimeout(() => {
      currentStep.value++;
      isAnimating.value = false;
    }, 300);
  }
};

const prevStep = () => {
  if (currentStep.value > 0) {
    isAnimating.value = true;
    setTimeout(() => {
      currentStep.value--;
      isAnimating.value = false;
      if (currentStep.value < 2) {
        showResult.value = false;
      }
    }, 300);
  }
};

const resetDemo = () => {
  currentStep.value = 0;
  demoScore.value = { home: null, away: null };
  showResult.value = false;
};

const submitPrediction = () => {
  if (demoScore.value.home !== null && demoScore.value.away !== null) {
    isAnimating.value = true;
    setTimeout(() => {
      showResult.value = true;
      nextStep();
      isAnimating.value = false;
    }, 1000);
  }
};

const incrementScore = (team) => {
  if (demoScore.value[team] === null) {
    demoScore.value[team] = 1;
  } else {
    demoScore.value[team] = Math.min(10, demoScore.value[team] + 1);
  }
};

const decrementScore = (team) => {
  if (demoScore.value[team] !== null && demoScore.value[team] > 0) {
    demoScore.value[team]--;
  }
};

const getPointsEarned = computed(() => {
  if (!showResult.value) return 0;
  
  const predicted = demoScore.value;
  const actual = demoMatch.actual_result;
  
  // Exact score
  if (predicted.home === actual.home && predicted.away === actual.away) {
    return 5;
  }
  
  // Correct outcome
  const predictedOutcome = predicted.home > predicted.away ? 'home' : 
                          predicted.home < predicted.away ? 'away' : 'draw';
  const actualOutcome = actual.home > actual.away ? 'home' : 
                       actual.home < actual.away ? 'away' : 'draw';
  
  if (predictedOutcome === actualOutcome) {
    return 2;
  }
  
  return 0;
});

const getResultMessage = computed(() => {
  const points = getPointsEarned.value;
  if (points === 5) return "🔥 نتیجه دقیق! ۵ امتیاز کامل!";
  if (points === 2) return "✅ نتیجه درست! ۲ امتیاز گرفتی!";
  return "❌ این بار درست نبود، دفعه بعد بهتر!";
});

const formatFarsiDate = (date) => {
  return new Date(date).toLocaleDateString('fa-IR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<template>
  <section class="py-12 sm:py-16 md:py-20 bg-gradient-to-br from-slate-50 to-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-8 sm:mb-12">
        <h3 class="text-2xl sm:text-3xl md:text-4xl font-800 text-slate-900 mb-2 sm:mb-4">
          چطور کار می‌کنه؟
        </h3>
        <p class="text-base sm:text-lg text-slate-600 font-300">
          فقط ۳۰ ثانیه طول می‌کشه پیش‌بینی کنی
        </p>
      </div>

      <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
        <!-- Interactive Demo -->
        <div class="order-2 lg:order-1">
          <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <!-- Demo Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white p-4 sm:p-6">
              <div class="flex items-center justify-between">
                <h4 class="text-lg font-700">تست کن!</h4>
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                  <span class="text-sm">دمو زنده</span>
                </div>
              </div>
            </div>

            <!-- Demo Content -->
            <div class="p-6" :class="{ 'opacity-50 pointer-events-none': isAnimating }">
              <!-- Step 1: Match Selection -->
              <div v-if="currentStep === 0" class="space-y-4 transition-all duration-300">
                <div class="text-center mb-6">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-700">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ formatFarsiDate(demoMatch.match_datetime) }}
                  </span>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 border-2 border-blue-200 cursor-pointer hover:bg-blue-50 transition-colors" @click="nextStep">
                  <div class="flex items-center justify-between">
                    <!-- Home Team -->
                    <div class="flex items-center gap-3">
                      <img 
                        :src="`/assets/team-logos/${demoMatch.home_team}.png`"
                        :alt="demoMatch.home_team"
                        class="w-8 h-8 object-contain"
                        @error="$event.target.style.display = 'none'"
                      />
                      <span class="font-600 text-slate-900">{{ translateTeamName(demoMatch.home_team) }}</span>
                    </div>
                    
                    <div class="text-slate-400 font-medium">vs</div>
                    
                    <!-- Away Team -->
                    <div class="flex items-center gap-3">
                      <span class="font-600 text-slate-900">{{ translateTeamName(demoMatch.away_team) }}</span>
                      <img 
                        :src="`/assets/team-logos/${demoMatch.away_team}.png`"
                        :alt="demoMatch.away_team"
                        class="w-8 h-8 object-contain"
                        @error="$event.target.style.display = 'none'"
                      />
                    </div>
                  </div>
                  
                  <div class="text-center mt-3">
                    <span class="text-sm text-blue-600 font-600">👆 کلیک کن تا انتخاب بشه</span>
                  </div>
                </div>
              </div>

              <!-- Step 2: Score Prediction -->
              <div v-if="currentStep === 1" class="space-y-6 transition-all duration-300">
                <!-- Teams -->
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="`/assets/team-logos/${demoMatch.home_team}.png`"
                      :alt="demoMatch.home_team"
                      class="w-8 h-8 object-contain"
                      @error="$event.target.style.display = 'none'"
                    />
                    <span class="font-600 text-slate-900">{{ translateTeamName(demoMatch.home_team) }}</span>
                  </div>
                  
                  <div class="text-slate-400 font-medium">vs</div>
                  
                  <div class="flex items-center gap-3">
                    <span class="font-600 text-slate-900">{{ translateTeamName(demoMatch.away_team) }}</span>
                    <img 
                      :src="`/assets/team-logos/${demoMatch.away_team}.png`"
                      :alt="demoMatch.away_team"
                      class="w-8 h-8 object-contain"
                      @error="$event.target.style.display = 'none'"
                    />
                  </div>
                </div>

                <!-- Score Input -->
                <div class="flex items-center justify-center gap-4">
                  <!-- Home Score -->
                  <div class="flex flex-col items-center">
                    <button 
                      @click="incrementScore('home')" 
                      class="w-10 h-8 rounded-t-md bg-slate-100 hover:bg-green-200 text-sm border border-slate-200 border-b-0 transition-colors"
                    >+</button>
                    <input 
                      v-model.number="demoScore.home"
                      type="number" 
                      min="0" 
                      max="10" 
                      class="w-10 h-10 text-center text-lg font-bold border-x border-slate-200 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      placeholder="?"
                    />
                    <button 
                      @click="decrementScore('home')" 
                      class="w-10 h-8 rounded-b-md bg-slate-100 hover:bg-green-200 text-sm border border-slate-200 border-t-0 transition-colors"
                    >-</button>
                  </div>

                  <span class="text-xl font-bold text-slate-400">-</span>

                  <!-- Away Score -->
                  <div class="flex flex-col items-center">
                    <button 
                      @click="incrementScore('away')" 
                      class="w-10 h-8 rounded-t-md bg-slate-100 hover:bg-green-200 text-sm border border-slate-200 border-b-0 transition-colors"
                    >+</button>
                    <input 
                      v-model.number="demoScore.away"
                      type="number" 
                      min="0" 
                      max="10" 
                      class="w-10 h-10 text-center text-lg font-bold border-x border-slate-200 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      placeholder="?"
                    />
                    <button 
                      @click="decrementScore('away')" 
                      class="w-10 h-8 rounded-b-md bg-slate-100 hover:bg-green-200 text-sm border border-slate-200 border-t-0 transition-colors"
                    >-</button>
                  </div>
                </div>

                <div class="text-center">
                  <button 
                    @click="submitPrediction"
                    :disabled="demoScore.home === null || demoScore.away === null"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg font-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    ثبت پیش‌بینی
                  </button>
                  <p class="text-xs text-slate-500 mt-2">پیش‌بینی‌ت خودکار ذخیره میشه</p>
                </div>
              </div>

              <!-- Step 3: Auto Save -->
              <div v-if="currentStep === 2" class="space-y-6 text-center transition-all duration-300">
                <div class="flex items-center justify-center">
                  <svg class="animate-spin h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-lg font-700 text-slate-900 mb-2">در حال ذخیره...</h4>
                  <p class="text-slate-600">پیش‌بینی شما: {{ demoScore.home }}-{{ demoScore.away }}</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <p class="text-green-800 text-sm">✅ پیش‌بینی با موفقیت ذخیره شد!</p>
                </div>
              </div>

              <!-- Step 4: Results -->
              <div v-if="currentStep === 3" class="space-y-6 text-center transition-all duration-300">
                <div class="bg-slate-100 rounded-xl p-6">
                  <h4 class="text-lg font-700 text-slate-900 mb-4">نتیجه نهایی بازی</h4>
                  
                  <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                      <div class="text-sm text-slate-600 mb-1">پیش‌بینی شما</div>
                      <div class="text-xl font-bold text-blue-600">{{ demoScore.home }}-{{ demoScore.away }}</div>
                    </div>
                    <div class="flex items-center justify-center">
                      <div class="w-px h-8 bg-slate-300"></div>
                    </div>
                    <div class="text-center">
                      <div class="text-sm text-slate-600 mb-1">نتیجه واقعی</div>
                      <div class="text-xl font-bold text-slate-900">{{ demoMatch.actual_result.home }}-{{ demoMatch.actual_result.away }}</div>
                    </div>
                  </div>

                  <div class="bg-white rounded-lg p-4 border-2" :class="getPointsEarned > 0 ? 'border-green-200' : 'border-red-200'">
                    <p class="font-600" :class="getPointsEarned > 0 ? 'text-green-800' : 'text-red-800'">
                      {{ getResultMessage }}
                    </p>
                    <div v-if="getPointsEarned > 0" class="text-2xl font-800 text-green-600 mt-2">
                      +{{ getPointsEarned }} امتیاز
                    </div>
                  </div>
                </div>

                <button 
                  @click="resetDemo"
                  class="px-6 py-2 bg-slate-600 text-white rounded-lg font-600 hover:bg-slate-700 transition-colors"
                >
                  تست مجدد
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Steps Guide -->
        <div class="order-1 lg:order-2">
          <div class="space-y-6">
            <div 
              v-for="(step, index) in steps" 
              :key="index"
              class="flex items-start gap-4 p-4 rounded-xl transition-all duration-300"
              :class="currentStep === index ? 'bg-green-50 border-2 border-green-200' : 'bg-white border border-slate-200'"
            >
              <div 
                class="w-12 h-12 rounded-xl flex items-center justify-center text-xl flex-shrink-0 transition-colors"
                :class="currentStep === index ? 'bg-green-100' : 'bg-slate-100'"
              >
                {{ step.icon }}
              </div>
              <div>
                <h4 class="font-700 text-slate-900 mb-1">{{ step.title }}</h4>
                <p class="text-slate-600 text-sm leading-relaxed">{{ step.description }}</p>
              </div>
              <div 
                v-if="currentStep === index" 
                class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0 ml-auto"
              >
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <div class="flex items-center justify-between mt-8">
            <button 
              @click="prevStep"
              :disabled="currentStep === 0"
              class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              قبلی
            </button>
            
            <div class="flex items-center gap-2">
              <div 
                v-for="(_, index) in steps" 
                :key="index"
                class="w-2 h-2 rounded-full transition-colors"
                :class="currentStep >= index ? 'bg-green-500' : 'bg-slate-300'"
              ></div>
            </div>
            
            <button 
              @click="nextStep"
              :disabled="currentStep === steps.length - 1"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              بعدی
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Hide browser spinner buttons */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type=number] {
  appearance: textfield;
  -moz-appearance: textfield;
}

/* Smooth transitions */
.transition-all {
  transition: all 0.3s ease;
}

/* Animation for step changes */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.space-y-6 > * {
  animation: slideIn 0.5s ease-out;
}
</style>


