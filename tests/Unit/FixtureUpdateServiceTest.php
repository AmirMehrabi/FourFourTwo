<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\FixtureUpdateService;
use App\Services\Support\FixtureFinalizationException;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Carbon\Carbon;

class FixtureUpdateServiceTest extends TestCase
{
    use RefreshDatabase;

    private FixtureUpdateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FixtureUpdateService::class);
    }

    public function test_start_due_fixtures_moves_scheduled_to_in_play()
    {
        // Fixed base time for determinism
        $baseNow = Carbon::create(2025,1,1,12,0,0);
        Carbon::setTestNow($baseNow);
        $kickoff = $baseNow->copy()->addMinutes(5);

        $fixture = Fixture::factory()->create([
            'status' => 'scheduled',
            'match_datetime' => $kickoff,
        ]);

        // Advance time to after kickoff
        Carbon::setTestNow($kickoff->copy()->addMinute());
        $count = $this->service->startDueFixtures($fixture->id);
        $this->assertEquals(1, $count, 'Expected exactly one fixture to transition to in_play');
        $fixture->refresh();
        $this->assertEquals('in_play', $fixture->status);
        Carbon::setTestNow(); // clear
    }

    public function test_update_live_score_sets_scores_and_keeps_in_play()
    {
        $fixture = Fixture::factory()->create([
            'status' => 'in_play',
            'home_score' => null,
            'away_score' => null,
        ]);

        $this->service->updateLiveScore($fixture, 2, 1);
        $fixture->refresh();
        $this->assertEquals(2, $fixture->home_score);
        $this->assertEquals(1, $fixture->away_score);
        $this->assertEquals('in_play', $fixture->status);
    }

    public function test_finalize_fixture_changes_status_and_scores()
    {
        $fixture = Fixture::factory()->create([
            'status' => 'in_play',
            'match_datetime' => Carbon::now()->subMinutes(95),
        ]);

        $this->service->finalizeFixture($fixture, 3, 0);
        $fixture->refresh();
        $this->assertEquals('finished', $fixture->status);
        $this->assertEquals(3, $fixture->home_score);
        $this->assertEquals(0, $fixture->away_score);
    }

    public function test_finalize_fixture_before_elapsed_time_throws_exception()
    {
        $fixture = Fixture::factory()->create([
            'status' => 'in_play',
            'match_datetime' => Carbon::now()->subMinutes(30),
        ]);
        $this->expectException(FixtureFinalizationException::class);
        $this->service->finalizeFixture($fixture, 1, 1);
    }

    public function test_finalize_fixture_future_kickoff_throws_exception()
    {
        $fixture = Fixture::factory()->create([
            'status' => 'scheduled',
            'match_datetime' => Carbon::now()->addMinutes(10),
        ]);
        $this->expectException(FixtureFinalizationException::class);
        $this->service->finalizeFixture($fixture, 0, 0);
    }
}
