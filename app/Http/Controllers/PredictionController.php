<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prediction;
use App\Models\Fixture;
use Carbon\Carbon;

class PredictionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'predictions' => 'required|array',
            'predictions.*.fixture_id' => 'required|exists:fixtures,id',
            'predictions.*.home_score' => 'nullable|integer|min:0',
            'predictions.*.away_score' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['predictions'] as $predictionData) {
            // Ensure fixture is not locked before saving
            $fixture = Fixture::find($predictionData['fixture_id']);
            if (Carbon::parse($fixture->match_datetime)->isBefore(now()->addHour())) {
                continue; // Skip locked fixtures
            }

            // If both scores are null, skip saving
            if (is_null($predictionData['home_score']) && is_null($predictionData['away_score'])) {
                continue;
            }

            Prediction::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'fixture_id' => $predictionData['fixture_id'],
                ],
                [
                    'home_score_predicted' => $predictionData['home_score'],
                    'away_score_predicted' => $predictionData['away_score'],
                ]
            );
        }

        return back()->with('success', 'Predictions saved!');
    }
}
