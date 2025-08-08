<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-4 pe-5 py-3 border-e-4 border-[var(--brand-2)] text-start text-base font-medium text-[var(--brand-2)] bg-white/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-2)] focus-visible:ring-offset-2 focus-visible:ring-offset-white transition duration-150 ease-in-out'
        : 'block w-full ps-4 pe-5 py-3 border-e-4 border-transparent text-start text-base font-medium text-slate-700 hover:text-[var(--brand-2)] hover:bg-slate-50 hover:border-[var(--brand-2)]/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-2)] focus-visible:ring-offset-2 focus-visible:ring-offset-white transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes" :aria-current="active ? 'page' : undefined">
        <slot />
    </Link>
</template>
