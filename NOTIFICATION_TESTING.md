# 🔔 Notification System Testing Guide

This guide helps you test the new notification features for badge awards and user follows.

## 🎯 What's Been Implemented

### **1. Badge Award Notifications**
- Users receive notifications when they earn new badges
- Includes badge details: name, description, tier, icon, rarity
- Includes context about what triggered the badge

### **2. Follow Notifications**
- Users receive notifications when someone follows them
- Includes follower's profile info and stats
- Triggers achievement checking for both users

## 🧪 Testing Instructions

### **Test Badge Notifications**

#### Method 1: Manual Badge Award (via Tinker)
```bash
php artisan tinker

# Get a user
$user = \App\Models\User::first();

# Award a badge manually (this will create a notification)
$user->awardBadge('first_prediction', ['trigger' => 'manual_test']);

# Check if notification was created
$user->notifications()->where('type', 'badge_awarded')->count();

# View the notification data
$notification = $user->notifications()->where('type', 'badge_awarded')->latest()->first();
echo json_encode($notification->data, JSON_PRETTY_PRINT);
```

#### Method 2: Trigger via Achievement System
```bash
# Run the achievements check command
php artisan achievements:check-all

# This will award badges and create notifications automatically
```

#### Method 3: Create a Prediction (for first_prediction badge)
```bash
php artisan tinker

# Get a user who hasn't made predictions yet
$user = \App\Models\User::factory()->create(['username' => 'testuser2']);

# Get a fixture
$fixture = \App\Models\Fixture::first();

# Create a prediction (this should trigger first_prediction badge + notification)
$prediction = $user->predictions()->create([
    'fixture_id' => $fixture->id,
    'home_score_predicted' => 2,
    'away_score_predicted' => 1
]);

# Check for badge notification
$user->notifications()->where('type', 'badge_awarded')->count();
```

### **Test Follow Notifications**

#### Method 1: Via Web Interface
1. Login as User A
2. Visit another user's profile: `/@username`
3. Click the "Follow" button
4. Login as User B (the followed user)
5. Check notifications - should see a "new_follower" notification

#### Method 2: Via API/Tinker
```bash
php artisan tinker

# Get two users
$follower = \App\Models\User::find(1);
$followed = \App\Models\User::find(2);

# Simulate the follow action
$follower->following()->attach($followed->id);
\App\Models\Notification::createFollowerNotification($followed->id, $follower);
\App\Events\UserFollowed::dispatch($follower, $followed);

# Check if notification was created
$followed->notifications()->where('type', 'new_follower')->count();

# View the notification
$notification = $followed->notifications()->where('type', 'new_follower')->latest()->first();
echo json_encode($notification->data, JSON_PRETTY_PRINT);
```

#### Method 3: Via HTTP Request
```bash
# Follow a user (replace with actual usernames)
curl -X POST http://localhost:8000/@testuser/follow \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "your-session-cookie"
```

## 🔍 Verification Steps

### **Check Notification Data Structure**

#### Badge Notification Structure:
```json
{
  "badge_id": 1,
  "badge_key": "first_prediction",
  "badge_name": "پیش‌بین تازه‌کار",
  "badge_description": "اولین پیش‌بینی خود را ثبت کنید",
  "badge_icon": "play-circle",
  "badge_tier": "bronze",
  "badge_category": "milestone",
  "badge_tier_color": "#CD7F32",
  "badge_rarity": 85.5,
  "context": {
    "trigger": "prediction_created",
    "prediction_id": 123
  }
}
```

#### Follow Notification Structure:
```json
{
  "follower_id": 1,
  "follower_name": "John Doe",
  "follower_username": "johndoe",
  "follower_avatar": "avatar.jpg",
  "follower_stats": {
    "predictions_count": 25,
    "accuracy": 75.5,
    "badges_count": 3
  }
}
```

### **Check Notification API Endpoints**

```bash
# Get all notifications
curl -X GET http://localhost:8000/api/notifications \
  -H "Authorization: Bearer your-token"

# Get unread count
curl -X GET http://localhost:8000/api/notifications/unread-count \
  -H "Authorization: Bearer your-token"

# Mark notification as read
curl -X POST http://localhost:8000/api/notifications/1/read \
  -H "Authorization: Bearer your-token"
```

## 🎨 Frontend Integration

The notifications should appear in your existing notification dropdown/panel. The notification types to handle in frontend:

### **Badge Notification Display**
```javascript
if (notification.type === 'badge_awarded') {
  const badge = notification.data;
  return `🏆 شما نشان "${badge.badge_name}" را دریافت کردید!`;
}
```

### **Follow Notification Display**
```javascript
if (notification.type === 'new_follower') {
  const follower = notification.data;
  return `👤 ${follower.follower_name} شما را دنبال کرد`;
}
```

## 🔧 Debugging

### **Check if Events are Firing**
```bash
# Add this to your .env for debugging
LOG_LEVEL=debug

# Check logs
tail -f storage/logs/laravel.log
```

### **Check Database**
```sql
-- Check notifications table
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;

-- Check user_badges table
SELECT * FROM user_badges ORDER BY created_at DESC LIMIT 10;

-- Check specific notification types
SELECT type, COUNT(*) FROM notifications GROUP BY type;
```

### **Common Issues**

1. **No notifications created**: Check if events are firing and listeners are registered
2. **Badge notifications missing**: Ensure `awardBadge` method is being called
3. **Follow notifications missing**: Check if UserFollowed event is being dispatched
4. **Frontend not showing**: Verify notification API endpoints are working

## ✅ Success Criteria

- ✅ Badge notifications created when users earn badges
- ✅ Follow notifications created when users gain followers  
- ✅ Notifications appear in API responses
- ✅ Notification data includes all required fields
- ✅ Events trigger achievement checking
- ✅ No duplicate notifications for same action

## 🚀 Next Steps

Once testing is complete, you can enhance the system with:
- Push notifications (browser/mobile)
- Email notifications for important badges
- Real-time notifications via WebSockets
- Notification preferences/settings
- Notification grouping (multiple follows, etc.)

The notification system is now fully integrated with your badge and social features! 🎉
