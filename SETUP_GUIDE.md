# 🚀 OGS Connect - Setup Guide for Team Members

## Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL/MariaDB
- Node.js and npm (for frontend assets)

## Installation Steps

### 1. Clone and Install Dependencies
```bash
git clone <repository-url>
cd ogsconnect
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration
Update your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ogsconnect_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Database Setup
```bash
# Create database tables
php artisan migrate

# Seed the database with test data
php artisan db:seed
```

### 5. Build Frontend Assets
```bash
npm run build
# or for development
npm run dev
```

### 6. Start the Application
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## 🧪 Test Data Included

The seeder will create:

### 👥 **Supervisor Accounts**
- **Supervisor 1**: ID: `OGS-S1001`, Email: `admin@ogsconnect.com`, Password: `password`
- **Supervisor 2**: ID: `OGS-S1002`, Email: `jane.smith@ogsconnect.com`, Password: `password`

### 👨‍🏫 **Tutor Accounts (10 tutors)**
- Active tutors: `alicewong`, `bobsmith`, `caroljohnson`, `davidlee`, `emilychen`, `gracewilson`, `henrydavis`, `irisgarcia`, `jackmartinez`
- Inactive tutor: `frankbrown`

### ⏰ **Time Slots**
- Monday-Friday: 9:00, 10:00, 11:00, 14:00, 15:00, 16:00 (30 total slots)

### 📅 **Availability Data**
- Each active tutor has 3-5 random available time slots
- Perfect for testing auto-assignment functionality

### 📚 **Sample Schedules**
- 5 sample classes across different schools and time slots
- All in "tentative" status for testing assignment features

## 🎯 Testing Features

### 1. **Login Testing**
- Try logging in with both supervisor accounts
- Test both ID and email login methods

### 2. **Schedule Ownership**
- Login as Supervisor 1, assign tutors to a schedule
- Logout and login as Supervisor 2 - you should see the schedule is "owned" by Supervisor 1
- Modify buttons should be disabled for Supervisor 2

### 3. **Auto-Assignment**
- Use the "Auto Assign All" button to test automatic tutor assignment
- Check that tutors are assigned based on availability

### 4. **Manual Assignment**
- Manually assign tutors to classes
- Test removing assignments
- Test cancelling classes

### 5. **Excel Export**
- Test tentative Excel export
- Test final Excel export
- Test selected schedule export from history

### 6. **Schedule History**
- View schedule history
- Export selected schedules

## 🔧 Troubleshooting

### Database Issues
```bash
# Reset database completely
php artisan migrate:fresh --seed
```

### Cache Issues
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Permission Issues
```bash
# On Linux/Mac
chmod -R 755 storage bootstrap/cache
```

## 📝 Development Notes

### Key Features to Test
1. **Authentication System** - Supervisor login with ID/email
2. **Schedule Ownership** - One supervisor per schedule
3. **Tutor Assignment** - Manual and automatic assignment
4. **Excel Export** - All three export types
5. **Schedule History** - Tracking changes and exports

### Database Tables
- `daily_data` - Main schedule data
- `supervisors` - Supervisor accounts
- `tutors` - Tutor information
- `tutor_accounts` - Tutor login accounts
- `tutor_assignments` - Tutor assignments
- `schedule_history` - Change tracking
- `availabilities` - Tutor availability
- `time_slots` - Time slot definitions

## 🎉 Ready to Test!

Your team members can now:
1. **Login** with the provided supervisor accounts
2. **Create schedules** and assign tutors
3. **Test ownership** by switching between supervisors
4. **Export data** in various Excel formats
5. **View history** of all schedule changes

Happy testing! 🚀
