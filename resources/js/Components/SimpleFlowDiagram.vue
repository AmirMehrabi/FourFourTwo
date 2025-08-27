<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);

onMounted(() => {
  // Trigger animation after component mounts
  setTimeout(() => {
    isVisible.value = true;
  }, 200);
});

const flowSteps = [
  {
    number: '1',
    icon: '👤',
    title: 'ثبت نام رایگان',
    description: 'فقط ۳۰ ثانیه طول می‌کشه',
    color: 'from-blue-500 to-blue-600'
  },
  {
    number: '2',
    icon: '⚽',
    title: 'انتخاب مسابقه',
    description: 'از بازی‌های هفته انتخاب کن',
    color: 'from-green-500 to-green-600'
  },
  {
    number: '3',
    icon: '🎯',
    title: 'پیش‌بینی نتیجه',
    description: 'نتیجه‌ای که فکر می‌کنی رو بنویس',
    color: 'from-purple-500 to-purple-600'
  },
  {
    number: '4',
    icon: '🏆',
    title: 'کسب امتیاز',
    description: 'هر پیش‌بینی درست = امتیاز بیشتر',
    color: 'from-orange-500 to-orange-600'
  },
  {
    number: '5',
    icon: '👑',
    title: 'صعود در رتبه',
    description: 'بهترین پیش‌بین هفته بشو!',
    color: 'from-red-500 to-red-600'
  }
];
</script>

<template>
  <section class="py-12 sm:py-16 md:py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-12">
        <h3 class="text-2xl sm:text-3xl md:text-4xl font-800 text-slate-900 mb-4">
          ⚡ فقط ۵ قدم تا قهرمانی
        </h3>
        <p class="text-base sm:text-lg text-slate-600 font-300 max-w-2xl mx-auto">
          از ثبت نام تا صدر جدول، مسیر ساده‌ای که همه می‌تونن طی کنن
        </p>
      </div>

      <!-- Flow Steps -->
      <div class="relative">
        <!-- Connection Lines (Desktop) -->
        <div class="hidden lg:block absolute top-20 left-1/2 transform -translate-x-1/2 w-full max-w-5xl">
          <div class="relative h-1">
            <div 
              class="absolute inset-0 bg-gradient-to-r from-blue-200 to-red-200 rounded-full transition-all duration-1000 ease-out"
              :class="isVisible ? 'opacity-100 scale-x-100' : 'opacity-0 scale-x-0'"
            ></div>
            <!-- Animated dots -->
            <div class="absolute inset-0 flex items-center justify-between px-8">
              <div 
                v-for="(step, index) in flowSteps" 
                :key="index"
                class="w-3 h-3 bg-white rounded-full border-2 transition-all duration-500 ease-out"
                :class="[
                  `border-${step.color.split('-')[1]}-500`,
                  isVisible ? 'scale-100 opacity-100' : 'scale-0 opacity-0'
                ]"
                :style="{ transitionDelay: `${index * 200}ms` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Steps Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 lg:gap-4">
          <div 
            v-for="(step, index) in flowSteps" 
            :key="index"
            class="relative group transition-all duration-500 ease-out"
            :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            :style="{ transitionDelay: `${index * 150}ms` }"
          >
            <!-- Step Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group-hover:border-slate-300">
              <!-- Step Number -->
              <div class="flex items-center justify-center mb-4">
                <div 
                  class="w-16 h-16 rounded-full bg-gradient-to-br flex items-center justify-center text-white font-800 text-xl shadow-lg"
                  :class="step.color"
                >
                  {{ step.number }}
                </div>
              </div>

              <!-- Step Icon -->
              <div class="text-center mb-3">
                <span class="text-3xl">{{ step.icon }}</span>
              </div>

              <!-- Step Content -->
              <div class="text-center">
                <h4 class="text-lg font-700 text-slate-900 mb-2">{{ step.title }}</h4>
                <p class="text-sm text-slate-600 leading-relaxed">{{ step.description }}</p>
              </div>

              <!-- Hover Effect -->
              <div class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-5 transition-opacity duration-300 rounded-2xl pointer-events-none"
                   :class="step.color">
              </div>
            </div>

            <!-- Mobile Connection Arrow -->
            <div v-if="index < flowSteps.length - 1" class="lg:hidden flex justify-center mt-4 mb-2">
              <svg class="w-6 h-6 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="text-center mt-12">
        <div class="bg-gradient-to-r from-slate-50 to-blue-50 rounded-2xl p-8 border border-slate-200">
          <h4 class="text-xl font-700 text-slate-900 mb-3">آماده‌ای شروع کنی؟</h4>
          <p class="text-slate-600 mb-6">همین الان بپیوند و اولین پیش‌بینی‌ت رو ثبت کن</p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <button class="btn-brand-primary px-8 py-3 text-lg font-700 hover:scale-105 transition-transform">
              شروع پیش‌بینی - رایگان
            </button>
            <div class="text-sm text-slate-500">
              ✨ بدون نیاز به کارت بانکی
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
        <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
          <div class="text-2xl font-800 text-blue-600 mb-1">۳۰ ثانیه</div>
          <div class="text-sm text-slate-600">زمان ثبت نام</div>
        </div>
        <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
          <div class="text-2xl font-800 text-green-600 mb-1">۱۰ مسابقه</div>
          <div class="text-sm text-slate-600">هر هفته</div>
        </div>
        <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
          <div class="text-2xl font-800 text-purple-600 mb-1">۵ امتیاز</div>
          <div class="text-sm text-slate-600">برای هر پیش‌بینی دقیق</div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Custom animations */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Hover effects */
.group:hover .absolute {
  transform: scale(1.05);
}

/* Gradient text effect */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Smooth transitions for all interactive elements */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .grid-cols-5 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .transition-all,
  .group:hover,
  .hover\:scale-105:hover {
    transition: none;
    transform: none;
  }
  
  .animate-pulse {
    animation: none;
  }
}
</style>
