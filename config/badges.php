<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Badge System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all badge definitions for the FourFourTwo platform.
    | Badges are automatically awarded based on the criteria defined here.
    |
    */

    'enabled' => env('BADGES_ENABLED', true),
    
    'cache_ttl' => env('BADGES_CACHE_TTL', 3600), // Cache for 1 hour
    
    /*
    |--------------------------------------------------------------------------
    | Badge Definitions
    |--------------------------------------------------------------------------
    */
    
    'definitions' => [
        
        // ========================================
        // BRONZE TIER - Welcome Achievements
        // ========================================
        
        'first_prediction' => [
            'name' => 'پیش‌بین تازه‌کار',
            'description' => 'اولین پیش‌بینی خود را ثبت کنید',
            'icon' => 'play-circle',
            'tier' => 'bronze',
            'category' => 'milestone',
            'criteria' => [
                'type' => 'count',
                'field' => 'predictions_count',
                'operator' => '>=',
                'value' => 1,
            ],
            'sort_order' => 1,
        ],
        
        'first_comment' => [
            'name' => 'صدای اول',
            'description' => 'اولین نظر خود را بنویسید',
            'icon' => 'chat-bubble',
            'tier' => 'bronze',
            'category' => 'social',
            'criteria' => [
                'type' => 'count',
                'field' => 'comments_count',
                'operator' => '>=',
                'value' => 1,
            ],
            'sort_order' => 2,
        ],
        
        'profile_complete' => [
            'name' => 'آماده بازی',
            'description' => 'پروفایل خود را کامل کنید (بیو و تصویر پروفایل)',
            'icon' => 'user-check',
            'tier' => 'bronze',
            'category' => 'profile',
            'criteria' => [
                'type' => 'multiple',
                'conditions' => [
                    ['field' => 'has_bio', 'operator' => '=', 'value' => true],
                    ['field' => 'has_avatar', 'operator' => '=', 'value' => true],
                ],
                'logic' => 'and',
            ],
            'sort_order' => 3,
        ],
        
        'first_follow' => [
            'name' => 'دوست‌یاب',
            'description' => 'اولین کاربر را دنبال کنید',
            'icon' => 'user-plus',
            'tier' => 'bronze',
            'category' => 'social',
            'criteria' => [
                'type' => 'count',
                'field' => 'following_count',
                'operator' => '>=',
                'value' => 1,
            ],
            'sort_order' => 4,
        ],
        
        'gameweek_participant' => [
            'name' => 'شرکت‌کننده هفته',
            'description' => 'در یک هفته کامل شرکت کنید (حداقل ۸ پیش‌بینی)',
            'icon' => 'calendar-check',
            'tier' => 'bronze',
            'category' => 'participation',
            'criteria' => [
                'type' => 'custom',
                'method' => 'hasCompletedGameweek',
            ],
            'sort_order' => 5,
        ],
        
        'early_bird' => [
            'name' => 'پرنده سحرخیز',
            'description' => 'پیش‌بینی را ۲۴ ساعت قبل از بازی ثبت کنید',
            'icon' => 'sunrise',
            'tier' => 'bronze',
            'category' => 'timing',
            'criteria' => [
                'type' => 'custom',
                'method' => 'hasEarlyPrediction',
                'params' => ['hours' => 24],
            ],
            'sort_order' => 6,
        ],
        
        // ========================================
        // SILVER TIER - Progress Achievements
        // ========================================
        
        'first_exact_score' => [
            'name' => 'نتیجه دقیق',
            'description' => 'اولین نتیجه دقیق خود را بزنید',
            'icon' => 'target',
            'tier' => 'silver',
            'category' => 'accuracy',
            'criteria' => [
                'type' => 'count',
                'field' => 'exact_scores',
                'operator' => '>=',
                'value' => 1,
            ],
            'sort_order' => 10,
        ],
        
        'winning_streak_5' => [
            'name' => 'پنج‌تایی طلایی',
            'description' => '۵ پیش‌بینی درست پشت سر هم',
            'icon' => 'fire',
            'tier' => 'silver',
            'category' => 'streak',
            'criteria' => [
                'type' => 'count',
                'field' => 'best_streak',
                'operator' => '>=',
                'value' => 5,
            ],
            'sort_order' => 11,
        ],
        
        'accuracy_60' => [
            'name' => 'پیش‌بین خوب',
            'description' => 'دقت ۶۰٪ در پیش‌بینی‌ها (حداقل ۲۰ پیش‌بینی)',
            'icon' => 'chart-bar',
            'tier' => 'silver',
            'category' => 'accuracy',
            'criteria' => [
                'type' => 'multiple',
                'conditions' => [
                    ['field' => 'accuracy_percentage', 'operator' => '>=', 'value' => 60],
                    ['field' => 'predictions_count', 'operator' => '>=', 'value' => 20],
                ],
                'logic' => 'and',
            ],
            'sort_order' => 12,
        ],
        
        'social_butterfly' => [
            'name' => 'محبوب جامعه',
            'description' => '۱۰ دنبال‌کننده پیدا کنید',
            'icon' => 'heart',
            'tier' => 'silver',
            'category' => 'social',
            'criteria' => [
                'type' => 'count',
                'field' => 'followers_count',
                'operator' => '>=',
                'value' => 10,
            ],
            'sort_order' => 13,
        ],
        
        'month_regular' => [
            'name' => 'بازیکن منظم',
            'description' => 'یک ماه مداوم بازی کنید',
            'icon' => 'calendar',
            'tier' => 'silver',
            'category' => 'consistency',
            'criteria' => [
                'type' => 'custom',
                'method' => 'hasActiveDays',
                'params' => ['days' => 30],
            ],
            'sort_order' => 14,
        ],
        
        'prediction_machine' => [
            'name' => 'ماشین پیش‌بینی',
            'description' => '۵۰ پیش‌بینی ثبت کنید',
            'icon' => 'cog',
            'tier' => 'silver',
            'category' => 'milestone',
            'criteria' => [
                'type' => 'count',
                'field' => 'predictions_count',
                'operator' => '>=',
                'value' => 50,
            ],
            'sort_order' => 15,
        ],
        
        // ========================================
        // GOLD TIER - Excellence Achievements
        // ========================================
        
        'accuracy_80' => [
            'name' => 'استاد پیش‌بینی',
            'description' => 'دقت ۸۰٪ در پیش‌بینی‌ها (حداقل ۵۰ پیش‌بینی)',
            'icon' => 'crown',
            'tier' => 'gold',
            'category' => 'accuracy',
            'criteria' => [
                'type' => 'multiple',
                'conditions' => [
                    ['field' => 'accuracy_percentage', 'operator' => '>=', 'value' => 80],
                    ['field' => 'predictions_count', 'operator' => '>=', 'value' => 50],
                ],
                'logic' => 'and',
            ],
            'sort_order' => 20,
        ],
        
        'exact_score_master' => [
            'name' => 'شاه نتیجه دقیق',
            'description' => '۱۰ نتیجه دقیق بزنید',
            'icon' => 'bullseye',
            'tier' => 'gold',
            'category' => 'accuracy',
            'criteria' => [
                'type' => 'count',
                'field' => 'exact_scores',
                'operator' => '>=',
                'value' => 10,
            ],
            'sort_order' => 21,
        ],
        
        'winning_streak_10' => [
            'name' => 'ده‌گانه افسانه‌ای',
            'description' => '۱۰ پیش‌بینی درست پشت سر هم',
            'icon' => 'lightning',
            'tier' => 'gold',
            'category' => 'streak',
            'criteria' => [
                'type' => 'count',
                'field' => 'best_streak',
                'operator' => '>=',
                'value' => 10,
            ],
            'sort_order' => 22,
        ],
        
        'top_10_season' => [
            'name' => 'ده نفر برتر',
            'description' => 'در فصل جاری جزو ۱۰ نفر اول باشید',
            'icon' => 'trophy',
            'tier' => 'gold',
            'category' => 'ranking',
            'criteria' => [
                'type' => 'multiple',
                'conditions' => [
                    ['field' => 'season_rank', 'operator' => '<=', 'value' => 10],
                    ['field' => 'season_rank', 'operator' => '>', 'value' => 0],
                ],
                'logic' => 'and',
            ],
            'sort_order' => 23,
        ],
        
        'influencer' => [
            'name' => 'تأثیرگذار',
            'description' => '۵۰ دنبال‌کننده پیدا کنید',
            'icon' => 'star',
            'tier' => 'gold',
            'category' => 'social',
            'criteria' => [
                'type' => 'count',
                'field' => 'followers_count',
                'operator' => '>=',
                'value' => 50,
            ],
            'sort_order' => 24,
        ],
        
        // ========================================
        // DIAMOND TIER - Legendary Achievements
        // ========================================
        
        'perfect_gameweek' => [
            'name' => 'هفته کامل',
            'description' => 'تمام بازی‌های یک هفته را درست پیش‌بینی کنید',
            'icon' => 'gem',
            'tier' => 'diamond',
            'category' => 'perfection',
            'criteria' => [
                'type' => 'custom',
                'method' => 'hasPerfectGameweek',
            ],
            'sort_order' => 30,
        ],
        
        'season_champion' => [
            'name' => 'قهرمان فصل',
            'description' => 'قهرمان فصل شوید',
            'icon' => 'medal',
            'tier' => 'diamond',
            'category' => 'championship',
            'criteria' => [
                'type' => 'count',
                'field' => 'season_rank',
                'operator' => '=',
                'value' => 1,
            ],
            'sort_order' => 31,
        ],
        
        'accuracy_90' => [
            'name' => 'نابغه پیش‌بینی',
            'description' => 'دقت ۹۰٪ با حداقل ۱۰۰ پیش‌بینی',
            'icon' => 'brain',
            'tier' => 'diamond',
            'category' => 'mastery',
            'criteria' => [
                'type' => 'multiple',
                'conditions' => [
                    ['field' => 'accuracy_percentage', 'operator' => '>=', 'value' => 90],
                    ['field' => 'predictions_count', 'operator' => '>=', 'value' => 100],
                ],
                'logic' => 'and',
            ],
            'sort_order' => 32,
        ],
        
        // ========================================
        // SPECIAL EVENT ACHIEVEMENTS
        // ========================================
        
        'derby_specialist' => [
            'name' => 'متخصص دربی',
            'description' => '۵ دربی بزرگ را درست پیش‌بینی کنید',
            'icon' => 'sword',
            'tier' => 'gold',
            'category' => 'special',
            'criteria' => [
                'type' => 'custom',
                'method' => 'hasDerbyPredictions',
                'params' => ['count' => 5],
            ],
            'sort_order' => 25,
        ],
        
        'upset_predictor' => [
            'name' => 'پیش‌بین غافلگیری',
            'description' => '۳ غافلگیری بزرگ را پیش‌بینی کنید',
            'icon' => 'surprise',
            'tier' => 'gold',
            'category' => 'special',
            'criteria' => [
                'type' => 'custom',
                'method' => 'hasUpsetPredictions',
                'params' => ['count' => 3],
            ],
            'sort_order' => 26,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Badge Tier Colors (for frontend)
    |--------------------------------------------------------------------------
    */
    
    'tier_colors' => [
        'bronze' => '#CD7F32',
        'silver' => '#C0C0C0', 
        'gold' => '#FFD700',
        'diamond' => '#B9F2FF',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Derby Match Team IDs (for derby specialist badge)
    |--------------------------------------------------------------------------
    */
    
    'derby_matches' => [
        // Manchester Derby
        ['home' => 1, 'away' => 2], // Man City vs Man United
        ['home' => 2, 'away' => 1], // Man United vs Man City
        
        // North London Derby  
        ['home' => 3, 'away' => 4], // Arsenal vs Tottenham
        ['home' => 4, 'away' => 3], // Tottenham vs Arsenal
        
        // Merseyside Derby
        ['home' => 5, 'away' => 6], // Liverpool vs Everton
        ['home' => 6, 'away' => 5], // Everton vs Liverpool
        
        // London Derby (Chelsea vs Arsenal)
        ['home' => 7, 'away' => 3], // Chelsea vs Arsenal
        ['home' => 3, 'away' => 7], // Arsenal vs Chelsea
        
        // Add more derby combinations as needed
    ],
];
