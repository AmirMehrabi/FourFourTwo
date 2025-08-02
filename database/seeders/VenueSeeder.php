<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Venue;
use Illuminate\Support\Str;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Venue::query()->delete(); // Clear the table before seeding

                $venues = [
            ['name' => 'Emirates Stadium', 'city' => 'London', 'capacity' => 60704],
            ['name' => 'Villa Park', 'city' => 'Birmingham', 'capacity' => 42918],
            ['name' => 'Dean Court', 'city' => 'Bournemouth', 'capacity' => 11307],
            ['name' => 'Brentford Community Stadium', 'city' => 'London', 'capacity' => 17250],
            ['name' => 'Falmer Stadium', 'city' => 'Falmer', 'capacity' => 31876],
            ['name' => 'Turf Moor', 'city' => 'Burnley', 'capacity' => 21944],
            ['name' => 'Stamford Bridge', 'city' => 'London', 'capacity' => 40173],
            ['name' => 'Selhurst Park', 'city' => 'London', 'capacity' => 25194],
            ['name' => 'Everton Stadium', 'city' => 'Liverpool', 'capacity' => 52769],
            ['name' => 'Craven Cottage', 'city' => 'London', 'capacity' => 29589],
            ['name' => 'Elland Road', 'city' => 'Leeds', 'capacity' => 37645],
            ['name' => 'Anfield', 'city' => 'Liverpool', 'capacity' => 61276],
            ['name' => 'Etihad Stadium', 'city' => 'Manchester', 'capacity' => 52900],
            ['name' => 'Old Trafford', 'city' => 'Manchester', 'capacity' => 74197],
            ['name' => 'St James\' Park', 'city' => 'Newcastle upon Tyne', 'capacity' => 52258],
            ['name' => 'City Ground', 'city' => 'West Bridgford', 'capacity' => 30404],
            ['name' => 'Stadium of Light', 'city' => 'Sunderland', 'capacity' => 49000],
            ['name' => 'Tottenham Hotspur Stadium', 'city' => 'London', 'capacity' => 62850],
            ['name' => 'London Stadium', 'city' => 'London', 'capacity' => 62500],
            ['name' => 'Molineux Stadium', 'city' => 'Wolverhampton', 'capacity' => 31750],
        ];

        foreach ($venues as $venue) {
            Venue::create([
                'name' => $venue['name'],
                'city' => $venue['city'],
                'capacity' => $venue['capacity'],
                'slug' => Str::slug($venue['name']), // <-- This line is now added
            ]);
        }
    }
}
