<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { useTranslations } from '@/composables/useTranslations.js';
import { computed } from 'vue';

const { translateTeamName, t } = useTranslations();

const props = defineProps({
    fixtures: Array,
    matchweek: Number,
});

// useForm will wrap our prediction data, making submission easy
const form = useForm({
    predictions: props.fixtures.map((f) => ({
        fixture_id: f.id,
        home_score: f.prediction?.home_score_predicted ?? null,
        away_score: f.prediction?.away_score_predicted ?? null,
    })),
});

function submitPredictions() {
    form.post(route("predictions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success message, maybe show a toast notification
        },
    });
}

// Group fixtures by date
const fixturesByDate = computed(() => {
    const grouped = {};
    
    props.fixtures.forEach((fixture, index) => {
        // Get the date part only (without time)
        const date = new Date(fixture.match_datetime).toDateString();
        
        if (!grouped[date]) {
            grouped[date] = [];
        }
        
        grouped[date].push({
            ...fixture,
            originalIndex: index // Keep track of original index for form mapping
        });
    });
    
    return grouped;
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fa-IR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('fa-IR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<template>
    <Head :title="'Matchweek ' + matchweek" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">
                    پیش‌بینی هفته‌ی {{ matchweek }}
                </h2>
                <div class="gap-2">
                    <Link
                        v-if="matchweek > 1"
                        :href="
                            route('fixtures.index', {
                                matchweek: matchweek - 1,
                            })
                        "
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md text-sm"
                    >
                        &rarr; هفته‌ی قبل
                    </Link>
                    <Link
                        v-if="matchweek < 38"
                        :href="
                            route('fixtures.index', {
                                matchweek: matchweek + 1,
                            })
                        "
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md text-sm"
                    >
                        هفته‌ی بعد &larr;
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submitPredictions">
                    <!-- Fixtures grouped by date -->
                    <div class="space-y-6">
                        <div 
                            v-for="(dayFixtures, date) in fixturesByDate" 
                            :key="date"
                            class="bg-white rounded-lg shadow-sm border border-gray-200"
                        >
                            <!-- Date Header -->
                            <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                                <h4 class="font-medium text-gray-900">{{ formatDate(date) }}</h4>
                            </div>

                            <!-- Fixtures for this date -->
                            <div class="divide-y divide-gray-200">
                                <div
                                    v-for="fixture in dayFixtures"
                                    :key="fixture.id"
                                    class="p-6"
                                    :class="{
                                        'bg-gray-100 opacity-75': fixture.is_locked,
                                    }"
                                >
                                    <!-- Time display -->
                                    <p class="text-sm text-center text-gray-500 mb-4">
                                        {{ formatTime(fixture.match_datetime) }}
                                    </p>

                                    <div class="grid grid-cols-3 items-center text-center">
                                        <!-- Home Team -->
                                        <div class="flex items-center justify-end gap-3">
                                            <span class="font-bold">{{
                                                translateTeamName(fixture.home_team.name)
                                            }}</span>
                                            <img 
                                                :src="`/assets/team-logos/${fixture.home_team.name}.png`"
                                                :alt="fixture.home_team.name"
                                                class="w-8 lg:w-20 h-8 lg:h-20 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                        </div>

                                        <!-- Score Inputs -->
                                        <div class="flex items-center justify-center gap-2 mx-4">
                                            <input
                                                type="number"
                                                min="0"
                                                class="w-16 text-center font-bold"
                                                v-model="form.predictions[fixture.originalIndex].home_score"
                                                :disabled="fixture.is_locked"
                                            />
                                            <span class="m-0 px-2">-</span>
                                            <input
                                                type="number"
                                                min="0"
                                                class="w-16 text-center font-bold"
                                                v-model="form.predictions[fixture.originalIndex].away_score"
                                                :disabled="fixture.is_locked"
                                            />
                                        </div>

                                        <!-- Away Team -->
                                        <div class="flex items-center justify-start gap-3">
                                            <img 
                                                :src="`/assets/team-logos/${fixture.away_team.name}.png`"
                                                :alt="fixture.away_team.name"
                                                class="w-8 lg:w-20 h-8 lg:h-20 object-contain"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                            <span class="font-bold">{{
                                                translateTeamName(fixture.away_team.name)
                                            }}</span>
                                        </div>
                                    </div>

                                    <!-- Locked message -->
                                    <p
                                        v-if="fixture.is_locked"
                                        class="text-xs text-center text-red-500 mt-2"
                                    >
                                        {{ t('locked') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-300"
                        >
                            {{ t('save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
