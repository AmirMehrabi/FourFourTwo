<?php

namespace App\Services;

use App\Models\Fixture;
use App\Services\Support\FixtureFinalizationException;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service encapsulating lifecycle transitions for fixtures.
 */
class FixtureUpdateService
{
	/**
	 * Start scheduled fixtures whose kickoff time has passed.
	 * @return int number of fixtures moved to in_play
	 */
	public function startDueFixtures(?int $fixtureId = null): int
	{
		$query = Fixture::query()
			->where('status', 'scheduled')
			->where('match_datetime', '<=', Carbon::now());
		if ($fixtureId) { $query->where('id', $fixtureId); }
		return $query->update(['status' => 'in_play']);
	}

	/**
	 * Get fixtures currently in_play (optionally one fixture) for live score updating.
	 */
	public function getLiveCandidates(?int $fixtureId = null): Collection
	{
		$query = Fixture::with(['homeTeam','awayTeam','season'])
			->where('status', 'in_play');
		if ($fixtureId) { $query->where('id', $fixtureId); }
		return $query->orderBy('match_datetime')->get();
	}

	/**
	 * Get fixtures that are candidates for finalization: either in_play or scheduled (overdue) older than cutoff.
	 * @param int $minutes Minutes elapsed since kickoff to consider finalize.
	 */
	public function getFinalizeCandidates(int $minutes, ?int $fixtureId = null): Collection
	{
		$cutoff = Carbon::now()->subMinutes($minutes);
		$query = Fixture::with(['homeTeam','awayTeam','season'])
			->whereIn('status', ['in_play','scheduled'])
			->where('match_datetime', '<', $cutoff);
		if ($fixtureId) { $query->where('id', $fixtureId); }
		return $query->orderBy('match_datetime')->get();
	}

	/**
	 * Update live (in-play) score without finalizing.
	 */
	public function updateLiveScore(Fixture $fixture, ?int $homeScore, ?int $awayScore): void
	{
		if ($fixture->status !== 'in_play') {
			// If it was scheduled but we are updating live score, mark in_play
			if ($fixture->status === 'scheduled') {
				$fixture->status = 'in_play';
			} else {
				// Do not update scores for finished matches
				return;
			}
		}
		$fixture->home_score = $homeScore;
		$fixture->away_score = $awayScore;
		$fixture->save();
	}

	/**
	 * Finalize a fixture, setting final scores and status finished.
	 */
	public function finalizeFixture(Fixture $fixture, int $homeScore, int $awayScore): void
	{
		// Safety checks
		if ($fixture->status === 'finished') {
			return; // idempotent
		}
		if ($homeScore < 0 || $awayScore < 0) {
			throw new FixtureFinalizationException('Scores must be non-negative.');
		}
		if ($fixture->match_datetime && Carbon::now()->lt($fixture->match_datetime)) {
			throw new FixtureFinalizationException('Cannot finalize a fixture before kickoff.');
		}
		// Heuristic: at least 60 minutes should have elapsed since kickoff unless explicitly already in_play more than 30 minutes with scores set.
		if ($fixture->match_datetime) {
			$elapsed = $fixture->match_datetime->diffInMinutes(Carbon::now(), false);
			if ($elapsed < 60) {
				throw new FixtureFinalizationException('Fixture appears not to have completed (elapsed '.$elapsed.'m).');
			}
		}
		DB::transaction(function () use ($fixture, $homeScore, $awayScore) {
			$fixture->update([
				'home_score' => $homeScore,
				'away_score' => $awayScore,
				'status' => 'finished',
			]);
		});
	}
}

