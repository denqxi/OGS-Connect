# OGS Connect - GLS Scheduling System

A Laravel-based scheduling and management system for Global Learning Solutions (GLS) tutoring services.

## Features

- **Employee Management**: Manage tutors across multiple platforms (GLS, Tutlo, Babilala, Talk915)
- **Scheduling System**: Create and manage class schedules with tutor assignments
- **Excel Export**: Export schedules in multiple formats
- **Authentication**: Supervisor and tutor login system
- **Profile Management**: Update personal information and change passwords
- **Payment Information**: Manage employee payment details
- **Dashboard**: View statistics and system overview

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ogsconnect
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start the application**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## Test Accounts

### Supervisors
- **Admin**: ID: `OGS-S1001`, Email: `admin@ogsconnect.com`, Password: `password`
- **Jane Smith**: ID: `OGS-S1002`, Email: `jane.smith@ogsconnect.com`, Password: `password`

## Technologies Used

- **Laravel 12** - PHP Framework
- **SQLite** - Database (default)
- **Tailwind CSS** - Styling
- **maatwebsite/excel** - Excel export functionality

## Project Structure

```
app/
├── Http/Controllers/     # Controllers
├── Models/              # Database models
└── Services/            # Business logic

database/
├── migrations/          # Database migrations
└── seeders/             # Sample data

resources/
├── views/               # Blade templates
└── css/                 # Stylesheets
```