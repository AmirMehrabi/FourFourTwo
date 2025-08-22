<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'name_fa',
        'short_name',
        'short_name_fa',
        'logo_url',
        'slug',
        'primary_color',
        'secondary_color',
        'founded_year',
        'stadium_name',
        'stadium_name_fa',
        'stadium_capacity',
        'city',
        'city_fa',
        'manager',
        'manager_fa',
        'website_url',
        'api_id',
        'is_active',
    ];

    /**
     * Get home fixtures for this team
     */
    public function homeFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    /**
     * Get away fixtures for this team
     */
    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }

    /**
     * Get all fixtures for this team
     */
    public function fixtures()
    {
        return Fixture::where('home_team_id', $this->id)
            ->orWhere('away_team_id', $this->id);
    }

    /**
     * Get league table entries for this team
     */
    public function leagueTableEntries(): HasMany
    {
        return $this->hasMany(LeagueTable::class);
    }

    /**
     * Get the logo URL with fallback
     */
        public function getLogoUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Fallback to assets folder based on team name
        $logoPath = '/assets/team-logos/' . urlencode($this->name) . '.png';
        
        // Check if file exists, if not use default
        if (file_exists(public_path($logoPath))) {
            return $logoPath;
        }
        
        return '/assets/team-logos/default.png';
    }

    /**
     * Get the display name (Farsi if available, otherwise English)
     */
    public function getDisplayNameAttribute()
    {
        return $this->name_fa ?: $this->name;
    }

    /**
     * Get team name translations
     */
    public static function getTeamTranslations()
    {
        return [
            'Manchester City' => 'منچسترسیتی',
            'Arsenal' => 'آرسنال',
            'Liverpool' => 'لیورپول',
            'Chelsea' => 'چلسی',
            'Manchester United' => 'منچستریونایتد',
            'Tottenham Hotspur' => 'تاتنهام',
            'Newcastle United' => 'نیوکاسل',
            'West Ham United' => 'وست‌هم',
            'Brighton & Hove Albion' => 'برایتون',
            'Aston Villa' => 'استون‌ویلا',
            'Fulham' => 'فولام',
            'Brentford' => 'برنتفورد',
            'Crystal Palace' => 'کریستال‌پالاس',
            'Everton' => 'اورتون',
            'Wolverhampton Wanderers' => 'ولورهمپتون',
            'Nottingham Forest' => 'ناتینگهام‌فارست',
            'Bournemouth' => 'بورنموث',
            'Leeds United' => 'لیدز',
            'Burnley' => 'برنلی',
            'Sunderland' => 'ساندرلند',
        ];
    }}
