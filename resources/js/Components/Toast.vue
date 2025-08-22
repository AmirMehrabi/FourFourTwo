<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  type: { type: String, default: 'info' }, // success | error | info
  message: { type: String, default: '' },
  closable: { type: Boolean, default: true },
  position: { type: String, default: 'bottom-right' } // bottom-right | top-right | bottom-left | top-left
});

const emit = defineEmits(['close']);

const positionClasses = computed(() => {
  switch (props.position) {
    case 'top-right': return 'top-5 right-5';
    case 'top-left': return 'top-5 left-5';
    case 'bottom-left': return 'bottom-5 left-5';
    default: return 'bottom-5 right-5';
  }
});

const baseColor = computed(() => {
  switch (props.type) {
    case 'success': return 'bg-emerald-50 border border-emerald-200 text-emerald-800';
    case 'error': return 'bg-rose-50 border border-rose-200 text-rose-700';
    case 'info': default: return 'bg-sky-50 border border-sky-200 text-sky-700';
  }
});

const iconPath = computed(() => {
  switch (props.type) {
    case 'success': return 'M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z';
    case 'error': return 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L10 10.586 8.707 9.293a1 1 0 10-1.414 1.414L8.586 12l-1.293 1.293a1 1 0 101.414 1.414L10 13.414l1.293 1.293a1 1 0 001.414-1.414L11.414 12l1.293-1.293z';
    case 'info': default: return 'M18 10A8 8 0 11.001 9.999 8 8 0 0118 10zM9 9a1 1 0 100-2 1 1 0 000 2zm-1 2a1 1 0 000 2h2v3a1 1 0 002 0v-4a1 1 0 00-1-1H8z';
  }
});
</script>

<template>
  <transition name="toast-fade" appear>
    <div v-if="show" :class="['fixed z-[1100] max-w-xs w-72 shadow-lg rounded-lg p-4 flex items-start gap-3 text-sm', positionClasses, baseColor]" role="status" aria-live="polite">
      <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" :d="iconPath" clip-rule="evenodd" />
      </svg>
      <div class="flex-1 leading-5" v-text="message" />
      <button v-if="closable" type="button" class="text-xs opacity-60 hover:opacity-90" @click="emit('close')">✕</button>
    </div>
  </transition>
</template>

<style scoped>
.toast-fade-enter-active, .toast-fade-leave-active { transition: opacity .25s, transform .25s; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translateY(6px); }
</style>
