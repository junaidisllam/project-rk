@echo off
REM এলাকার বাজার - Production Optimization Script (Windows)
REM This script prepares the application for production deployment on Windows

echo.
echo ========================================
echo   Production Optimization Script
echo   এলাকার বাজার
echo ========================================
echo.

REM Check if .env file exists
if not exist .env (
    echo [ERROR] .env file not found!
    echo Please copy .env.example to .env and configure it first.
    pause
    exit /b 1
)

REM Check if APP_DEBUG is false
findstr /C:"APP_DEBUG=true" .env >nul
if %errorlevel% equ 0 (
    echo [ERROR] APP_DEBUG is set to 'true' in .env file
    echo For production, APP_DEBUG must be set to 'false'
    pause
    exit /b 1
)

echo [1/10] Clearing all caches...
php artisan optimize:clear
if %errorlevel% neq 0 goto :error
echo [OK] Caches cleared
echo.

echo [2/10] Installing production dependencies...
composer install --no-dev --optimize-autoloader --no-interaction
if %errorlevel% neq 0 goto :error
echo [OK] Composer dependencies installed
echo.

echo [3/10] Installing Node dependencies...
call npm ci --production=false
if %errorlevel% neq 0 goto :error
echo [OK] Node dependencies installed
echo.

echo [4/10] Building frontend assets...
call npm run build
if %errorlevel% neq 0 goto :error
echo [OK] Frontend assets built
echo.

echo [5/10] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 goto :error
echo [OK] Database migrations completed
echo.

echo [6/10] Creating storage symlink...
php artisan storage:link --force
if %errorlevel% neq 0 goto :error
echo [OK] Storage symlink created
echo.

echo [7/10] Optimizing application...
php artisan config:cache
if %errorlevel% neq 0 goto :error
echo [OK] Configuration cached

php artisan route:cache
if %errorlevel% neq 0 goto :error
echo [OK] Routes cached

php artisan view:cache
if %errorlevel% neq 0 goto :error
echo [OK] Views cached

php artisan event:cache
if %errorlevel% neq 0 goto :error
echo [OK] Events cached
echo.

echo [8/10] Setting file permissions...
REM Windows doesn't need chmod, but we can verify directories exist
if exist storage (
    echo [OK] Storage directory exists
) else (
    echo [WARNING] Storage directory not found
)

if exist bootstrap\cache (
    echo [OK] Bootstrap cache directory exists
) else (
    echo [WARNING] Bootstrap cache directory not found
)
echo.

echo [9/10] Running code formatter...
if exist vendor\bin\pint (
    vendor\bin\pint --quiet
    echo [OK] Code formatted with Pint
) else (
    echo [WARNING] Pint not found, skipping code formatting
)
echo.

echo [10/10] Verifying production readiness...

REM Check if APP_KEY is set
findstr /C:"APP_KEY=base64:" .env >nul
if %errorlevel% neq 0 (
    echo [ERROR] APP_KEY is not set in .env file
    echo Run: php artisan key:generate
    pause
    exit /b 1
)
echo [OK] APP_KEY is set

REM Check if storage directory exists
if not exist storage (
    echo [ERROR] Storage directory not found
    pause
    exit /b 1
)
echo [OK] Storage directory exists

REM Check if public\storage symlink exists
if not exist public\storage (
    echo [WARNING] Storage symlink not found in public directory
)
echo.

echo ========================================
echo   Production optimization complete!
echo ========================================
echo.
echo Next steps:
echo   1. Review your .env file for production settings
echo   2. Ensure SSL certificate is installed
echo   3. Configure your web server (IIS/Apache)
echo   4. Set up queue workers if using queues
echo   5. Configure automated backups
echo   6. Set up monitoring and error tracking
echo.
echo See DEPLOYMENT.md for detailed deployment instructions
echo.
pause
exit /b 0

:error
echo.
echo [ERROR] An error occurred during optimization
echo Please check the error message above and try again
echo.
pause
exit /b 1
