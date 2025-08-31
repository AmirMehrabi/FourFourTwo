<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Badge extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'tier',
        'category',
        'criteria',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users who have earned this badge.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
                    ->withPivot('awarded_at', 'awarded_for')
                    ->withTimestamps();
    }

    /**
     * Get the count of users who have this badge.
     */
    public function getUsersCountAttribute(): int
    {
        return Cache::remember("badge.{$this->id}.users_count", 3600, function () {
            return $this->users()->count();
        });
    }

    /**
     * Get the rarity percentage of this badge.
     */
    public function getRarityAttribute(): float
    {
        $totalUsers = User::count();
        if ($totalUsers === 0) return 0;
        
        return Cache::remember("badge.{$this->id}.rarity", 3600, function () use ($totalUsers) {
            return round(($this->users_count / $totalUsers) * 100, 2);
        });
    }

    /**
     * Check if this badge is rare (less than 10% of users have it).
     */
    public function getIsRareAttribute(): bool
    {
        return $this->rarity < 10;
    }

    /**
     * Get the tier color for frontend display.
     */
    public function getTierColorAttribute(): string
    {
        $colors = config('badges.tier_colors', []);
        return $colors[$this->tier] ?? '#6B7280';
    }

    /**
     * Scope to get active badges only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get badges by tier.
     */
    public function scopeByTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Scope to get badges by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order badges by tier hierarchy.
     */
    public function scopeOrderByTierHierarchy($query)
    {
        return $query->orderByRaw("
            CASE tier 
                WHEN 'diamond' THEN 1 
                WHEN 'gold' THEN 2 
                WHEN 'silver' THEN 3 
                WHEN 'bronze' THEN 4 
                ELSE 5 
            END
        ")->orderBy('sort_order');
    }

    /**
     * Get all badge definitions from config and sync with database.
     */
    public static function syncFromConfig(): void
    {
        $definitions = config('badges.definitions', []);
        
        foreach ($definitions as $key => $definition) {
            static::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'],
                    'tier' => $definition['tier'],
                    'category' => $definition['category'],
                    'criteria' => $definition['criteria'],
                    'sort_order' => $definition['sort_order'] ?? 0,
                    'is_active' => true,
                ]
            );
        }
        
        // Deactivate badges not in config
        $configKeys = array_keys($definitions);
        static::whereNotIn('key', $configKeys)->update(['is_active' => false]);
    }

    /**
     * Get badge by key.
     */
    public static function findByKey(string $key): ?self
    {
        return Cache::remember("badge.key.{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });
    }
}
