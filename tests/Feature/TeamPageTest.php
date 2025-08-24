<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that team page loads without TypeError
     */
    public function test_team_page_loads_successfully(): void
    {
        // Create a test team
        $team = Team::factory()->create([
            'name' => 'Manchester City',
            'slug' => 'manchester-city',
            'name_fa' => 'منچسترسیتی',
        ]);

        // Visit the team page
        $response = $this->get('/teams/' . $team->slug);

        // Assert the page loads successfully
        $response->assertStatus(200);
        
        // Assert the Inertia component is rendered
        $response->assertInertia(fn ($page) => 
            $page->component('Team')
                ->has('team')
                ->has('nextMatch')
        );
    }
}
