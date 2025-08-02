<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    fixtures: Array,
    matchweek: Number,
});

// useForm will wrap our prediction data, making submission easy
const form = useForm({
    predictions: props.fixtures.map(f => ({
        fixture_id: f.id,
        home_score: f.prediction?.home_score_predicted ?? null,
        away_score: f.prediction?.away_score_predicted ?? null,
    }))
});

function submitPredictions() {
    form.post(route('predictions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success message, maybe show a toast notification
        }
    });
}
</script>

<template>
    <Head :title="'Matchweek ' + matchweek" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Matchweek {{ matchweek }} Predictions</h2>
                <div class="space-x-2">
                    <Link
                        v-if="matchweek > 1"
                        :href="route('fixtures.index', { matchweek: matchweek - 1 })"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md text-sm"
                    >
                        &larr; Previous Week
                    </Link>
                    <Link
                        v-if="matchweek < 38"
                        :href="route('fixtures.index', { matchweek: matchweek + 1 })"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md text-sm"
                    >
                        Next Week &rarr;
                    </Link>
                </div>
            </div>

        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submitPredictions">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div v-for="(fixture, index) in fixtures" :key="fixture.id" class="p-4 border rounded-md" :class="{ 'bg-gray-100 opacity-75': fixture.is_locked }">
                            <p class="text-sm text-center text-gray-500">{{ new Date(fixture.match_datetime).toLocaleString('nl-NL') }}</p>

                            <div class="grid grid-cols-3 items-center mt-2 text-center">
                                <span class="font-bold text-right">{{ fixture.home_team.name }}</span>
                                <div class="flex items-center justify-center space-x-2 mx-4">
                                    <input
                                        type="number"
                                        min="0"
                                        class="w-16 text-center font-bold"
                                        v-model="form.predictions[index].home_score"
                                        :disabled="fixture.is_locked"
                                    />
                                    <span>-</span>
                                    <input
                                        type="number"
                                        min="0"
                                        class="w-16 text-center font-bold"
                                        v-model="form.predictions[index].away_score"
                                        :disabled="fixture.is_locked"
                                    />
                                </div>
                                <span class="font-bold text-left">{{ fixture.away_team.name }}</span>
                            </div>
                            <p v-if="fixture.is_locked" class="text-xs text-center text-red-500 mt-2">Prediction locked</p>
                        </div>

                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-300">
                            Save Predictions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>