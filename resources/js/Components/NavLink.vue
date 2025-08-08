<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-3 pt-1 border-b-2 border-[var(--brand-2)] text-sm font-medium leading-5 text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-2)] focus-visible:ring-offset-2 focus-visible:ring-offset-white transition duration-150 ease-in-out'
        : 'inline-flex items-center px-3 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-600 hover:text-[var(--brand-2)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-2)] focus-visible:ring-offset-2 focus-visible:ring-offset-white transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes" :aria-current="active ? 'page' : undefined">
        <slot />
    </Link>
</template>
