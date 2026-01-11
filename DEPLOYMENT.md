# Deployment Guide - এলাকার বাজার

This guide will help you deploy the এলাকার বাজার (Elakar Bazar) Laravel application to production.

## 📋 Pre-Deployment Checklist

### 1. Environment Configuration

Before deploying, ensure you have configured the following environment variables in your production `.env` file:

#### **Required Settings**

```env
APP_NAME="এলাকার বাজার"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql  # or your preferred database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_secure_password

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# File Storage
FILESYSTEM_DISK=public  # or 's3' for cloud storage
```

#### **Third-Party Services**

```env
# Google OAuth (for social login)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# Email Service (choose one)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Optional: Postmark
POSTMARK_API_KEY=your_postmark_key

# Optional: Resend
RESEND_API_KEY=your_resend_key
```

#### **Security Settings**

```env
SESSION_SECURE_COOKIE=true  # HTTPS only
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

#### **Optional: AWS S3 (for file storage)**

```env
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

### 2. Server Requirements

Ensure your server meets these requirements:

- **PHP**: >= 8.2
- **Composer**: Latest version
- **Node.js**: >= 18.x
- **NPM**: >= 9.x
- **Database**: MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+
- **Web Server**: Nginx or Apache
- **SSL Certificate**: Required for production

### 3. PHP Extensions

Verify these PHP extensions are installed:

- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL
- GD or Imagick (for image processing)

## 🚀 Deployment Steps

### Option 1: Using the Deploy Script (Recommended)

We've created a convenient deployment script in `composer.json`:

```bash
# SSH into your server
ssh user@your-server.com

# Navigate to your project directory
cd /path/to/your/project

# Pull latest changes from Git
git pull origin main

# Run the deployment script
composer deploy
```

This script will:

1. Put the application in maintenance mode
2. Install production dependencies
3. Run database migrations
4. Optimize the application
5. Create storage symlink
6. Build frontend assets
7. Bring the application back online

### Option 2: Manual Deployment

If you prefer manual control:

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Install PHP dependencies (production only)
composer install --no-dev --optimize-autoloader

# 4. Install Node dependencies
npm install

# 5. Build frontend assets
npm run build

# 6. Run database migrations
php artisan migrate --force

# 7. Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Create storage symlink (if not exists)
php artisan storage:link --force

# 9. Optimize application
php artisan optimize

# 10. Bring application back online
php artisan up
```

## 🔧 First-Time Deployment

For the first deployment to a new server:

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/elakarbazar.git
cd elakarbazar

# 2. Copy environment file
cp .env.example .env

# 3. Edit .env with your production settings
nano .env

# 4. Install dependencies
composer install --no-dev --optimize-autoloader
npm install

# 5. Generate application key
php artisan key:generate

# 6. Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force  # if you have seeders

# 7. Create storage symlink
php artisan storage:link

# 8. Build frontend assets
npm run build

# 9. Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 10. Optimize for production
php artisan optimize
```

## 📁 File Permissions

Set correct permissions for Laravel directories:

```bash
# Storage and cache directories
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# If using Nginx
sudo chown -R www-data:www-data .

# If using Apache
sudo chown -R apache:apache .
```

## 🌐 Web Server Configuration

### Nginx Configuration

Create a new site configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/elakarbazar/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
}
```

### Apache Configuration

If using Apache, ensure `.htaccess` is enabled and mod_rewrite is active:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 🔄 Queue Workers (Optional but Recommended)

If you're using queues, set up a supervisor configuration:

```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/elakarbazar-worker.conf
```

Add this configuration:

```ini
[program:elakarbazar-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/elakarbazar/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/elakarbazar/storage/logs/worker.log
stopwaitsecs=3600
```

Then start the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start elakarbazar-worker:*
```

## 📊 Database Backup

Set up automated database backups:

```bash
# Create backup script
nano /usr/local/bin/backup-elakarbazar.sh
```

Add:

```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/backups/elakarbazar"
DB_NAME="your_database_name"
DB_USER="your_database_user"
DB_PASS="your_database_password"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$TIMESTAMP.sql.gz

# Keep only last 7 days of backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

Make it executable and add to cron:

```bash
chmod +x /usr/local/bin/backup-elakarbazar.sh
crontab -e

# Add this line for daily backups at 2 AM
0 2 * * * /usr/local/bin/backup-elakarbazar.sh
```

## 🔍 Monitoring & Logs

### View Application Logs

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Using Laravel Pail (if installed)
php artisan pail
```

### Error Tracking

Consider integrating error tracking services:

- Sentry
- Bugsnag
- Flare

## 🧪 Post-Deployment Testing

After deployment, verify:

1. ✅ Homepage loads correctly
2. ✅ User authentication works (login/register)
3. ✅ Google OAuth login works
4. ✅ Book pages display correctly with images
5. ✅ Cart functionality works
6. ✅ Checkout process completes
7. ✅ Admin panel (Filament) is accessible
8. ✅ Sitemap.xml is accessible
9. ✅ SSL certificate is valid
10. ✅ All static assets load correctly

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials are secure
- [ ] `.env` file is not publicly accessible
- [ ] SSL certificate is installed and valid
- [ ] `SESSION_SECURE_COOKIE=true` for HTTPS
- [ ] File permissions are correctly set
- [ ] Unnecessary files removed from public directory
- [ ] CSRF protection is enabled
- [ ] Rate limiting is configured

## 🚨 Troubleshooting

### Common Issues

**500 Internal Server Error**

- Check storage and cache permissions
- Verify `.env` file exists and is readable
- Check error logs: `storage/logs/laravel.log`

**Images Not Loading**

- Ensure storage symlink exists: `php artisan storage:link`
- Check file permissions on `storage/app/public`
- Verify `FILESYSTEM_DISK` setting in `.env`

**Database Connection Failed**

- Verify database credentials in `.env`
- Ensure database server is running
- Check firewall rules

**Queue Jobs Not Processing**

- Verify queue worker is running
- Check supervisor logs
- Ensure `QUEUE_CONNECTION` is set correctly

## 📞 Support

For deployment issues, check:

- Laravel Documentation: https://laravel.com/docs
- Filament Documentation: https://filamentphp.com/docs
- Project Repository Issues

---

**Last Updated**: 2026-01-11
**Laravel Version**: 12.x
**PHP Version**: 8.2+
