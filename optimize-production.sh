#!/bin/bash

# এলাকার বাজার - Production Optimization Script
# This script prepares the application for production deployment

set -e  # Exit on any error

echo "🚀 Starting production optimization for এলাকার বাজার..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print success messages
success() {
    echo -e "${GREEN}✓${NC} $1"
}

# Function to print warning messages
warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Function to print error messages
error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if .env file exists
if [ ! -f .env ]; then
    error ".env file not found!"
    echo "Please copy .env.example to .env and configure it first."
    exit 1
fi

# Check if APP_ENV is set to production
if ! grep -q "APP_ENV=production" .env; then
    warning "APP_ENV is not set to 'production' in .env file"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check if APP_DEBUG is false
if grep -q "APP_DEBUG=true" .env; then
    error "APP_DEBUG is set to 'true' in .env file"
    echo "For production, APP_DEBUG must be set to 'false'"
    exit 1
fi

echo "1️⃣  Clearing all caches..."
php artisan optimize:clear
success "Caches cleared"

echo ""
echo "2️⃣  Installing production dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
success "Composer dependencies installed"

echo ""
echo "3️⃣  Installing Node dependencies..."
npm ci --production=false
success "Node dependencies installed"

echo ""
echo "4️⃣  Building frontend assets..."
npm run build
success "Frontend assets built"

echo ""
echo "5️⃣  Running database migrations..."
php artisan migrate --force
success "Database migrations completed"

echo ""
echo "6️⃣  Creating storage symlink..."
php artisan storage:link --force
success "Storage symlink created"

echo ""
echo "7️⃣  Optimizing application..."
php artisan config:cache
success "Configuration cached"

php artisan route:cache
success "Routes cached"

php artisan view:cache
success "Views cached"

php artisan event:cache
success "Events cached"

echo ""
echo "8️⃣  Setting file permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage
    success "Storage permissions set"
fi

if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    success "Bootstrap cache permissions set"
fi

echo ""
echo "9️⃣  Running code formatter..."
if [ -f "vendor/bin/pint" ]; then
    vendor/bin/pint --quiet
    success "Code formatted with Pint"
else
    warning "Pint not found, skipping code formatting"
fi

echo ""
echo "🔟  Verifying production readiness..."

# Check if APP_KEY is set
if ! grep -q "APP_KEY=base64:" .env; then
    error "APP_KEY is not set in .env file"
    echo "Run: php artisan key:generate"
    exit 1
fi
success "APP_KEY is set"

# Check if database is configured
if grep -q "DB_DATABASE=laravel" .env; then
    warning "Default database name detected. Make sure to configure your database."
fi

# Check if storage directory is writable
if [ ! -w "storage" ]; then
    error "Storage directory is not writable"
    exit 1
fi
success "Storage directory is writable"

# Check if public/storage symlink exists
if [ ! -L "public/storage" ]; then
    warning "Storage symlink not found in public directory"
fi

echo ""
echo "✨ Production optimization complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Review your .env file for production settings"
echo "   2. Ensure SSL certificate is installed"
echo "   3. Configure your web server (Nginx/Apache)"
echo "   4. Set up queue workers if using queues"
echo "   5. Configure automated backups"
echo "   6. Set up monitoring and error tracking"
echo ""
echo "📚 See DEPLOYMENT.md for detailed deployment instructions"
echo ""
