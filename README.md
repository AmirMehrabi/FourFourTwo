# چهار چهار دو (FourFourTwo) - Football Prediction Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/Vue.js-3.x-green.svg" alt="Vue.js Version">
  <img src="https://img.shields.io/badge/Inertia.js-2.x-purple.svg" alt="Inertia.js Version">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-blue.svg" alt="TailwindCSS Version">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
</p>

## 🚀 About The Project

**چهار چهار دو (FourFourTwo)** is a modern, data-driven football prediction platform designed for Persian-speaking football enthusiasts. Built with Laravel and Vue.js, it provides an intuitive interface for users to predict Premier League match results, compete with friends, and track their prediction accuracy over time.

### 🌟 Live Demo
Visit our platform at [https://fourfourtwo.ir](https://fourfourtwo.ir)

## ✨ Features

### 🎯 Core Prediction Features
- **Match Predictions**: Predict exact scores for Premier League fixtures
- **Auto-Save**: Intelligent auto-save system for partial predictions
- **Prediction Locking**: Predictions lock 1 hour before match kick-off
- **Real-time Updates**: Live match data and score updates

### 🏆 Competitive Elements
- **Points System**: 
  - 5 points for exact score predictions
  - 2 points for correct match outcomes
  - 0 points for incorrect predictions
- **Leaderboards**: Global and weekly ranking systems
- **User Statistics**: Detailed accuracy metrics and performance tracking
- **Gameweek Management**: Season-long competition across all matchweeks

### 📊 Analytics & Insights
- **Performance Dashboards**: Comprehensive user statistics
- **Accuracy Tracking**: Detailed breakdown of prediction success rates
- **Community Insights**: Platform-wide prediction trends and statistics
- **Visual Charts**: Interactive charts powered by Chart.js

### 🌐 Localization
- **Persian (Farsi) Interface**: Complete RTL support with Persian translations
- **Team Name Translation**: English team names translated to Persian
- **Persian Date/Time Formatting**: Localized date and time displays
- **Cultural Adaptation**: UI elements designed for Persian-speaking users

### 🎨 Modern UI/UX
- **Responsive Design**: Mobile-first approach with TailwindCSS
- **Dark/Light Themes**: Beautiful gradient designs and modern aesthetics
- **Team Logos**: Official team logos for better visual experience
- **Interactive Components**: Smooth animations and transitions

## 🛠️ Technology Stack

### Backend
- **Laravel 11.x**: PHP framework for robust backend development
- **SQLite Database**: Lightweight database for development (easily switchable to MySQL/PostgreSQL)
- **Inertia.js**: Modern SPA development without the complexity

### Frontend
- **Vue.js 3**: Progressive JavaScript framework with Composition API
- **TailwindCSS 3**: Utility-first CSS framework for rapid UI development
- **Chart.js**: Beautiful, responsive charts for data visualization
- **Vue-ChartJS**: Vue.js wrapper for Chart.js integration

### Development Tools
- **Vite**: Fast build tool and development server
- **Laravel Breeze**: Authentication scaffolding
- **Laravel Artisan**: Custom commands for data processing

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite (or MySQL/PostgreSQL for production)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/fourfourtwo.git
cd fourfourtwo
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Setup
```bash
# Create database and run migrations
php artisan migrate

# Seed the database with teams and sample data
php artisan db:seed
```

### 6. Build Assets
```bash
# For development
npm run dev

# For production
npm run build
```

### 7. Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` to see the application.

## 🗄️ Database Structure

### Core Models
- **Users**: User accounts and authentication
- **Teams**: Football teams with Persian translations
- **Fixtures**: Match fixtures with dates, venues, and results
- **Predictions**: User predictions linked to fixtures and users
- **Seasons**: Season management for different years

### Key Relationships
- Users can have many Predictions
- Fixtures belong to home/away Teams
- Predictions belong to Users and Fixtures
- Points are calculated automatically after matches

## 🎮 Usage

### For Users
1. **Register/Login**: Create an account to start predicting
2. **Browse Fixtures**: View upcoming matches by gameweek
3. **Make Predictions**: Enter your score predictions for matches
4. **Track Performance**: Monitor your accuracy and ranking
5. **Compete**: Compare your performance with other users

### For Administrators
1. **Manage Fixtures**: Add/update match fixtures and results
2. **Calculate Points**: Run automated point calculation after matches
3. **Monitor Platform**: View platform statistics and user engagement

## 🔧 Artisan Commands

### Calculate Prediction Points
```bash
php artisan app:calculate-points
```
Processes completed fixtures and awards points to users based on their prediction accuracy.

### Database Management
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Run specific seeders
php artisan db:seed --class=TeamSeeder
php artisan db:seed --class=FixtureSeeder
```

## 🏗️ Architecture

### Frontend Architecture
- **Pages**: Vue.js pages using Inertia.js for SPA-like experience
- **Components**: Reusable Vue components for UI elements
- **Composables**: Vue 3 composables for shared logic (translations, utilities)
- **Layouts**: Authenticated and guest layouts for different user states

### Backend Architecture
- **Controllers**: Handle HTTP requests and business logic
- **Models**: Eloquent models for database interactions
- **Migrations**: Database schema versioning
- **Seeders**: Sample data generation for development

### Key Features Implementation
- **Auto-save**: Debounced form submissions to prevent data loss
- **Prediction Locking**: Time-based validation to prevent late submissions
- **Points Calculation**: Automated scoring system with custom rules
- **Persian Localization**: Translation system with RTL support

## 🌍 Localization

The platform supports complete Persian localization:

### Translation Files
- `resources/js/translations/fa.js`: UI text and team name translations
- `resources/js/composables/useTranslations.js`: Translation helper functions

### RTL Support
- Full right-to-left layout support
- Persian number formatting
- Culturally appropriate design elements

## 🔒 Security Features

- **CSRF Protection**: Laravel's built-in CSRF protection
- **Input Validation**: Comprehensive form validation
- **Authentication**: Secure user authentication with Laravel Breeze
- **Authorization**: User-based permissions for predictions and data access

## 🚀 Deployment

### Production Setup
1. Set up your web server (Apache/Nginx)
2. Configure your production database
3. Set environment variables in `.env`
4. Run production builds:
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fourfourtwo.ir

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

## 🤝 Contributing

We welcome contributions to improve the platform! Here's how you can help:

### Getting Started
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Write tests for new functionality
5. Commit your changes (`git commit -m 'Add some amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards for PHP
- Use Vue 3 Composition API for new components
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

### Areas for Contribution
- Additional sports/leagues support
- Enhanced statistical analysis
- Mobile application development
- Performance optimizations
- UI/UX improvements
- Translation to other languages

## 📈 Roadmap

### Short Term
- [ ] Mobile application (React Native/Flutter)
- [ ] Push notifications for match reminders
- [ ] Social features (friends, private leagues)
- [ ] Enhanced statistical analysis

### Long Term
- [ ] Multiple league support (Champions League, La Liga, etc.)
- [ ] AI-powered prediction suggestions
- [ ] Fantasy football integration
- [ ] Live match commentary integration

## 📄 License

This project is open-source software licensed under the [MIT License](LICENSE).

## 👥 Team

- **Developer**: [Your Name]
- **Design**: [Designer Name]
- **Project Lead**: [Lead Name]

## 📞 Support

- **Website**: [https://fourfourtwo.ir](https://fourfourtwo.ir)
- **Email**: support@fourfourtwo.ir
- **Issues**: [GitHub Issues](https://github.com/yourusername/fourfourtwo/issues)

## 🙏 Acknowledgments

- Laravel team for the amazing framework
- Vue.js team for the reactive frontend framework
- TailwindCSS for the utility-first CSS framework
- Football API providers for match data
- The open-source community for invaluable tools and libraries

---

**Made with ❤️ for football fans by football fans**
