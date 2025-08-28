# User Profile System

This document describes the comprehensive user profile system implemented for the FourFourTwo application.

## Features Implemented

### Phase 1: Core Profile
- **Profile Header**: Username, display name, bio, avatar, join date
- **Stats Overview**: Total predictions, accuracy, points, current rank
- **Performance Analytics**: Season performance, all-time rank
- **Recent Activity**: Latest predictions and comments with results
- **Prediction History**: Detailed view of recent predictions with outcomes

### Phase 3: Social Features
- **Follow/Unfollow System**: Users can follow other users
- **Followers/Following Counts**: Display social connections
- **Profile Links**: Usernames throughout the app link to profiles
- **Community Engagement**: Show user's community contributions

## URL Structure

User profiles are accessible at:
```
https://baseurl.com/@username
```

Example: `https://baseurl.com/@ammir`

## Database Changes

### New Tables
- `user_follows`: Manages following relationships between users

### Modified Tables
- `users`: Added `bio` and `avatar` fields

## API Endpoints

### Profile Display
- `GET /@{username}` - Show user profile
- `GET /@{username}/followers` - Get user's followers
- `GET /@{username}/following` - Get users user is following

### Social Actions
- `POST /@{username}/follow` - Follow a user
- `POST /@{username}/unfollow` - Unfollow a user

## User Statistics

The profile displays comprehensive user statistics:

### Core Stats
- Total Predictions Made
- Correct Predictions Count
- Prediction Accuracy Percentage
- Total Points Earned
- Current Season Points
- Current Season Rank
- All-Time Rank

### Advanced Stats
- Best Prediction Streak
- Current Prediction Streak
- Total Comments Made
- Followers Count
- Following Count

### Recent Activity
- Last 10 Predictions with results
- Last 10 Comments with context
- Points earned per prediction
- Match details and outcomes

## Navigation Integration

### Profile Links Added To:
- **Comments**: Usernames in comments link to profiles
- **Leaderboard**: User names in rankings link to profiles
- **Dashboard**: Leaderboard preview usernames link to profiles
- **Navigation Dropdown**: "پروفایل من" (My Profile) link

### Profile Actions
- **Follow/Unfollow**: For other users' profiles
- **Edit Profile**: For own profile (redirects to settings)

## User Experience Features

### Engagement Elements
- **Visual Stats Cards**: Color-coded statistics with icons
- **Tabbed Activity View**: Switch between predictions and comments
- **Hover Effects**: Interactive elements with smooth transitions
- **Responsive Design**: Works on all device sizes

### Social Proof
- **Follower Counts**: Show community recognition
- **Ranking Display**: Competitive positioning
- **Achievement Metrics**: Streaks and accuracy percentages

## Technical Implementation

### Backend
- **User Model**: Enhanced with relationship methods and statistics calculations
- **UserProfileController**: Handles profile display and social actions
- **Database Queries**: Optimized for performance with eager loading

### Frontend
- **UserProfile.vue**: Main profile page component
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **State Management**: Vue 3 Composition API with reactive data

## Usage Examples

### Viewing a Profile
1. Navigate to `https://baseurl.com/@username`
2. View comprehensive user statistics
3. Browse recent activity and predictions
4. Follow/unfollow the user (if not own profile)

### Following a User
1. Visit any user's profile
2. Click the "Follow" button
3. Button changes to "Following" state
4. User is added to your following list

### Profile Navigation
1. Click on any username throughout the app
2. Username links automatically redirect to `/@username`
3. Use navigation dropdown "پروفایل من" for own profile

## Future Enhancements

### Phase 2 (Not Implemented)
- Performance analytics charts
- Historical data visualization
- Achievement system with badges
- Advanced filtering and search

### Additional Social Features
- Direct messaging between users
- Profile customization options
- Activity sharing and notifications
- Team-based communities

## Testing

To test the profile system:

1. **Create a user account** with a username
2. **Make some predictions** to generate statistics
3. **Visit your profile** at `/@yourusername`
4. **Test following** another user
5. **Verify profile links** work throughout the app

## Troubleshooting

### Common Issues
- **Profile not found**: Ensure username exists and is correct
- **Stats not showing**: Check if user has made predictions
- **Follow button not working**: Verify user is authenticated
- **Links not working**: Check route registration and component imports

### Debug Commands
```bash
# Check routes
php artisan route:list | grep user

# Verify database
php artisan migrate:status

# Test user model
php artisan tinker --execute="App\Models\User::first()"
```

## Performance Considerations

- **Eager Loading**: Relationships are loaded efficiently
- **Caching**: Consider implementing Redis for frequently accessed stats
- **Database Indexes**: Ensure proper indexing on username and user_id fields
- **Lazy Loading**: Recent activity is limited to prevent performance issues

This profile system provides a comprehensive view of user activity and engagement, encouraging community interaction and competitive gameplay through detailed statistics and social features.
