# 🎓 OGS Connect - GLS Scheduling System

A comprehensive Laravel-based scheduling and management system for Global Learning Solutions (GLS) tutoring services.

## ✨ Features

### 🔐 Authentication & User Management
- **Multi-guard Authentication**: Support for supervisors and tutors
- **Flexible Login**: Login with email, ID, or username
- **Password Reset**: Secure password reset with security questions
- **Role-based Access**: Different permissions for supervisors and tutors

### 📅 Scheduling System
- **Class Scheduling**: Create and manage tutoring sessions
- **Tutor Assignment**: Manual and automatic tutor assignment
- **Availability Management**: Track tutor availability and time slots
- **Schedule History**: Complete audit trail of all changes
- **Status Management**: Draft, tentative, and finalized schedule states

### 📊 Dashboard & Reporting
- **Real-time Dashboard**: Comprehensive statistics and metrics
- **Excel Export**: Multiple export formats (tentative, final, selected)
- **Analytics**: Class statistics, tutor utilization, and trends
- **Recent Activity**: Track all system activities

### 👥 Employee Management
- **Tutor Profiles**: Complete tutor information and availability
- **Status Management**: Activate/deactivate tutors
- **Account Management**: GLS account integration
- **Search & Filter**: Advanced filtering capabilities

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- SQLite (default) or MySQL/MariaDB
- Node.js and npm (for frontend assets)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ogsconnect
   ```

2. **Install dependencies**
   ```bash
   composer install  # This automatically installs maatwebsite/excel and all other packages
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env  # Create environment file
   php artisan key:generate  # Generate application key
   ```

4. **Database setup (SQLite - Default)**
   The system uses SQLite by default. The database file will be created automatically:
   ```bash
   php artisan migrate  # Run consolidated migrations
   php artisan db:seed  # Seed with sample data
   ```

   **For MySQL/MariaDB (Optional):**
   Update your `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ogsconnect_db
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
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

### 🎯 Quick Setup (One Command)
For the fastest setup, you can use the built-in composer script:
```bash
composer run-script post-create-project-cmd
```
This will automatically:
- Generate application key
- Create SQLite database
- Run migrations
- Set up the basic environment

## 🧪 Test Accounts

### Supervisors
- **Admin**: ID: `OGS-S1001`, Email: `admin@ogsconnect.com`, Password: `password`
- **Jane Smith**: ID: `OGS-S1002`, Email: `jane.smith@ogsconnect.com`, Password: `password`

### Sample Tutors
- Active tutors with various availability patterns
- GLS account integration
- Test data for scheduling scenarios

## 🏗️ Project Structure

```
ogsconnect/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   └── Imports/             # Data import functionality
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── views/               # Blade templates
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript files
├── routes/
│   ├── web.php              # Web routes
│   ├── auth.php             # Authentication routes
│   └── api.php              # API routes
└── public/                  # Public assets
```

## 🔧 Key Technologies

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade templates, Tailwind CSS, Alpine.js
- **Database**: SQLite (default) or MySQL with Eloquent ORM
- **Authentication**: Laravel Breeze, Multi-guard auth
- **Export**: maatwebsite/excel (PHPSpreadsheet) for Excel generation
- **UI/UX**: Responsive design with smooth animations
- **Security**: CSRF protection, SQL injection prevention, XSS protection

## 📋 Main Features

### Dashboard
- Real-time statistics and metrics
- Class scheduling overview
- Tutor utilization rates
- Recent activity feed

### Scheduling
- Create and manage class schedules
- Assign tutors manually or automatically
- Track schedule status (draft/tentative/finalized)
- Export schedules in multiple formats

### Employee Management
- Tutor profile management
- Availability tracking
- Status management (active/inactive)
- Search and filtering

### Reports & Analytics
- Excel export functionality
- Schedule history tracking
- Performance metrics
- Data visualization

## 🔒 Security Features

- CSRF protection
- SQL injection prevention
- XSS protection
- Secure password hashing
- Role-based access control
- Session management

## 📱 Responsive Design

- Mobile-friendly interface
- Smooth sidebar transitions
- Touch-optimized interactions
- Adaptive layouts

## 🚀 Deployment

1. Configure production environment variables
2. Set up database and run migrations
3. Build frontend assets: `npm run build`
4. Configure web server (Apache/Nginx)
5. Set proper file permissions

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions, please contact the development team or create an issue in the repository.

---

**OGS Connect** - Streamlining GLS scheduling and management 🎓✨