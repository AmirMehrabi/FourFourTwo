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
            'predictions.*.home_score' => 'nullable|integer|min:0|max:20',
            'predictions.*.away_score' => 'nullable|integer|min:0|max:20',
        ]);

        $savedCount = 0;
        $errors = [];

        foreach ($validated['predictions'] as $index => $predictionData) {
            // Ensure fixture is not locked before saving
            $fixture = Fixture::find($predictionData['fixture_id']);
            if (Carbon::parse($fixture->match_datetime)->isBefore(now()->addHour())) {
                continue; // Skip locked fixtures
            }

            // Skip if both scores are null/empty
            if (is_null($predictionData['home_score']) && is_null($predictionData['away_score'])) {
                continue;
            }

            // Validate that both scores are provided for saving
            if (is_null($predictionData['home_score']) || is_null($predictionData['away_score'])) {
                $errors[] = "برای ثبت پیش‌بینی، باید هر دو نتیجه تیم خانه و مهمان را وارد کنید.";
                continue;
            }

            try {
                $prediction = Prediction::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'fixture_id' => $predictionData['fixture_id'],
                    ],
                    [
                        'home_score_predicted' => $predictionData['home_score'],
                        'away_score_predicted' => $predictionData['away_score'],
                    ]
                );
                
                // Create activity feed entry for new predictions
                if ($prediction->wasRecentlyCreated) {
                    $prediction->load('fixture.homeTeam', 'fixture.awayTeam');
                    \App\Models\ActivityFeed::createPredictionActivity(Auth::user(), $prediction);
                }
                
                $savedCount++;
            } catch (\Exception $e) {
                $errors[] = "خطا در ذخیره پیش‌بینی.";
            }
        }

        if (!empty($errors)) {
            return back()->withErrors(['validation' => $errors[0]]);
        }

        $message = $savedCount > 0 ? "پیش‌بینی‌های شما با موفقیت ذخیره شد!" : "هیچ پیش‌بینی جدیدی برای ذخیره وجود نداشت.";
        return back()->with('success', $message);
    }
}
