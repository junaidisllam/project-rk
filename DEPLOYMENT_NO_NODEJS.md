# Server Deployment Guide (Without Node.js)

This guide is for deploying to servers that **don't have Node.js/NPM installed** (common with shared hosting).

## 📋 Prerequisites

Your server needs:

- ✅ PHP 8.2+
- ✅ Composer
- ✅ MySQL/MariaDB
- ❌ Node.js/NPM (not required!)

## 🚀 Deployment Steps

### On Your Server

#### 1. Pull Latest Code

```bash
cd /home/jnmboilf/elakarbazar.com
git pull origin main
```

#### 2. Fix Composer Install Issue

The composer install failed due to a concurrent process error. Let's fix it:

```bash
# Remove vendor directory
rm -rf vendor

# Clear composer cache
composer clear-cache

# Install dependencies (without dev packages)
composer install --no-dev --optimize-autoloader --no-interaction
```

If you still get errors, try:

```bash
# Install with different flags
composer install --no-dev --no-scripts --optimize-autoloader
```

#### 3. Configure Environment

Make sure your `.env` file has correct database credentials:

```bash
nano .env
```

**Important settings:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://elakarbazar.com

# Database - GET THESE FROM YOUR HOSTING PROVIDER
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=jnmboilf_books
DB_USERNAME=jnmboilf_books
DB_PASSWORD=YOUR_CORRECT_PASSWORD

# Use file-based cache (no database needed)
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# File storage
FILESYSTEM_DISK=public
```

#### 4. Generate Application Key (if not set)

```bash
php artisan key:generate --force
```

#### 5. Run Migrations

```bash
php artisan migrate --force
```

#### 6. Create Storage Link

```bash
php artisan storage:link
```

#### 7. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

If you have issues with permissions, contact your hosting provider.

#### 8. Clear and Cache Configuration

```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 9. Verify Deployment

```bash
# Check if the application is working
php artisan about
```

## 🔧 Troubleshooting

### Composer Install Fails

**Error:** `Concurrent process failed with exit code [255]`

**Solution:**

```bash
# Option 1: Remove vendor and reinstall
rm -rf vendor
composer install --no-dev --optimize-autoloader --no-scripts

# Option 2: Update composer first
composer self-update
composer install --no-dev --optimize-autoloader
```

### Database Connection Errors

**Error:** `Access denied for user`

**Solution:**

1. Contact your hosting provider for correct database credentials
2. Or create a new database user in cPanel:
    - Go to MySQL Databases
    - Create a new user
    - Add user to database with ALL PRIVILEGES
    - Update `.env` with new credentials

### File Permission Errors

**Error:** `Permission denied` when writing to storage

**Solution:**

```bash
# Set correct permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If still having issues, try 777 (less secure but works)
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Frontend Assets Not Loading

**Don't worry!** The assets are already built and included in the repository.

Just make sure:

```bash
# Check if public/build exists
ls -la public/build

# If missing, pull from git
git pull origin main
```

## 📝 Quick Deployment Script

Save this as `deploy.sh` on your server:

```bash
#!/bin/bash
echo "🚀 Deploying এলাকার বাজার..."

# Pull latest code
git pull origin main

# Install dependencies
rm -rf vendor
composer install --no-dev --optimize-autoloader --no-scripts

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment complete!"
```

Make it executable:

```bash
chmod +x deploy.sh
```

Run it:

```bash
./deploy.sh
```

## 🔄 Future Updates

When you make changes:

### On Your Local Machine:

```bash
# Make your changes
# Build assets
npm run build

# Commit everything
git add .
git commit -m "Your changes"
git push origin main
```

### On Your Server:

```bash
# Pull changes
git pull origin main

# Update dependencies if needed
composer install --no-dev --optimize-autoloader --no-scripts

# Run migrations if you added any
php artisan migrate --force

# Clear and rebuild caches
php artisan optimize:clear
php artisan optimize
```

## 📞 Need Help?

Common issues:

1. **Composer fails** → Remove vendor directory and try again
2. **Database errors** → Check credentials with hosting provider
3. **Permission errors** → Contact hosting support for correct permissions
4. **Assets not loading** → Make sure `public/build` directory exists

---

**Note:** Since your server doesn't have Node.js, always build assets locally before pushing to GitHub!
