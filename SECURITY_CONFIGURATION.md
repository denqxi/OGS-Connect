# Security Configuration Guide

## Production Environment Setup

### 1. Environment Variables (.env file)
Create a `.env` file with the following security settings:

```env
APP_NAME="OGS Connect"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

# Session Security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Database Security
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ogsconnect_db
DB_USERNAME=your-secure-username
DB_PASSWORD=your-secure-password

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 2. Application Key Generation
Run the following command to generate a secure application key:
```bash
php artisan key:generate
```

### 3. Database Security
- Use strong database credentials
- Enable SSL connections if possible
- Restrict database access to application server only

### 4. Web Server Configuration
- Enable HTTPS/SSL
- Set secure headers
- Configure proper file permissions

### 5. File Permissions
```bash
# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env
```

## Security Features Implemented

### ✅ Route Protection
- All critical routes now require authentication
- Supervisor-only routes protected with `auth:supervisor` middleware
- API endpoints protected with `auth:supervisor,web` middleware

### ✅ Session Security
- Session encryption enabled
- Secure cookies enabled
- SameSite set to 'strict'
- HttpOnly cookies enabled

### ✅ Authentication
- Strong password hashing with bcrypt
- Proper user authentication guards
- Session-based authentication

### ✅ Data Protection
- Mass assignment protection on all models
- Sensitive attributes hidden from serialization
- XSS protection in Blade templates
- CSRF protection on forms

## Remaining Security Tasks

### High Priority
1. **Error Handling**: Implement generic error handling to prevent information leakage
2. **File Upload Security**: Add file extension validation
3. **Log Management**: Implement log rotation and secure logging
4. **Rate Limiting**: Add rate limiting to API endpoints

### Medium Priority
1. **HTTPS Configuration**: Ensure HTTPS is properly configured
2. **Security Headers**: Add security headers (HSTS, CSP, etc.)
3. **Input Validation**: Enhance input validation
4. **Audit Logging**: Implement security audit logging

## Testing Security

Run the following commands to test security:

```bash
# Test authentication
php artisan route:list --middleware=auth

# Test session security
php artisan config:cache
php artisan session:table

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Production Deployment Checklist

- [ ] Set APP_DEBUG=false
- [ ] Generate secure APP_KEY
- [ ] Configure HTTPS
- [ ] Set secure database credentials
- [ ] Enable session encryption
- [ ] Configure secure session cookies
- [ ] Set proper file permissions
- [ ] Configure web server security headers
- [ ] Test all authentication flows
- [ ] Verify route protection
- [ ] Test file upload security
- [ ] Implement error handling
- [ ] Configure log rotation
