# Production Deployment Checklist

Use this checklist to ensure your এলাকার বাজার application is ready for production deployment.

## ✅ Pre-Deployment

### Environment Configuration

- [ ] Copy `.env.example` to `.env`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate `APP_KEY` with `php artisan key:generate`
- [ ] Set correct `APP_URL` (with https://)
- [ ] Configure database credentials
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Configure Google OAuth credentials
- [ ] Configure email service (SMTP/Postmark/Resend)
- [ ] Set `FILESYSTEM_DISK=public` or `s3`

### Code Quality

- [ ] Run `vendor/bin/pint --dirty` to format code
- [ ] Run `php artisan test` to ensure all tests pass
- [ ] Remove any debug code or `dd()` statements
- [ ] Review and remove unused dependencies
- [ ] Update `composer.lock` and `package-lock.json`

### Database

- [ ] Review all migrations
- [ ] Test migrations on staging environment
- [ ] Prepare database seeders if needed
- [ ] Plan for data migration if upgrading

### Assets & Frontend

- [ ] Run `npm run build` to compile production assets
- [ ] Verify all images are optimized
- [ ] Check that all fonts are loaded correctly
- [ ] Test responsive design on multiple devices
- [ ] Verify dark mode works correctly

## 🚀 Deployment

### Server Setup

- [ ] Verify PHP 8.2+ is installed
- [ ] Install required PHP extensions
- [ ] Install Composer
- [ ] Install Node.js and NPM
- [ ] Configure web server (Nginx/Apache)
- [ ] Install and configure SSL certificate
- [ ] Set up firewall rules

### Application Deployment

- [ ] Clone repository or upload files
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm install && npm run build`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan storage:link`
- [ ] Run `php artisan optimize`
- [ ] Set correct file permissions (775 for storage, bootstrap/cache)
- [ ] Set correct ownership (www-data or apache)

### Optimization

- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `php artisan event:cache`
- [ ] Enable OPcache in PHP
- [ ] Configure Redis for caching (optional)

## 🔒 Security

### Application Security

- [ ] Ensure `APP_DEBUG=false`
- [ ] Verify `.env` is not publicly accessible
- [ ] Check `.gitignore` includes sensitive files
- [ ] Enable HTTPS only
- [ ] Configure CORS if using API
- [ ] Set up rate limiting
- [ ] Review and update security headers

### Server Security

- [ ] Keep server OS updated
- [ ] Configure firewall (UFW/iptables)
- [ ] Disable unnecessary services
- [ ] Set up fail2ban for SSH protection
- [ ] Regular security audits
- [ ] Monitor for vulnerabilities

## 📊 Monitoring & Maintenance

### Logging

- [ ] Configure log rotation
- [ ] Set up error tracking (Sentry/Bugsnag)
- [ ] Monitor application logs regularly
- [ ] Set up alerts for critical errors

### Backups

- [ ] Set up automated database backups
- [ ] Set up file storage backups
- [ ] Test backup restoration process
- [ ] Store backups in secure location
- [ ] Define backup retention policy

### Performance

- [ ] Set up application monitoring (New Relic/DataDog)
- [ ] Monitor server resources (CPU, RAM, Disk)
- [ ] Set up uptime monitoring
- [ ] Configure CDN for static assets (optional)
- [ ] Enable Gzip compression

### Queue Workers (if using queues)

- [ ] Set up Supervisor for queue workers
- [ ] Configure queue worker processes
- [ ] Monitor queue worker status
- [ ] Set up failed job handling

## 🧪 Post-Deployment Testing

### Functional Testing

- [ ] Test homepage loads correctly
- [ ] Test user registration
- [ ] Test user login
- [ ] Test Google OAuth login
- [ ] Test password reset
- [ ] Test book browsing and filtering
- [ ] Test book detail pages
- [ ] Test cart functionality
- [ ] Test checkout process
- [ ] Test order creation
- [ ] Test admin panel access (Filament)
- [ ] Test file uploads in admin panel

### Performance Testing

- [ ] Check page load times
- [ ] Test with multiple concurrent users
- [ ] Verify database query performance
- [ ] Check asset loading times
- [ ] Test on slow network connections

### SEO & Accessibility

- [ ] Verify sitemap.xml is accessible
- [ ] Check robots.txt
- [ ] Test meta tags on all pages
- [ ] Verify Open Graph tags
- [ ] Test with Google PageSpeed Insights
- [ ] Check mobile responsiveness
- [ ] Verify accessibility standards

## 🔄 Ongoing Maintenance

### Regular Tasks

- [ ] Monitor error logs daily
- [ ] Review and optimize slow queries
- [ ] Update dependencies monthly
- [ ] Review and update security patches
- [ ] Monitor disk space usage
- [ ] Review and clean old logs
- [ ] Test backup restoration quarterly

### Updates

- [ ] Keep Laravel framework updated
- [ ] Keep PHP version updated
- [ ] Keep Node.js and NPM updated
- [ ] Update Composer dependencies
- [ ] Update NPM dependencies
- [ ] Review and update Filament

## 📝 Documentation

- [ ] Document deployment process
- [ ] Document environment variables
- [ ] Document server configuration
- [ ] Document backup procedures
- [ ] Document troubleshooting steps
- [ ] Keep changelog updated

## 🎯 Quick Deployment Commands

### Initial Deployment

```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

### Update Deployment

```bash
git pull origin main
composer deploy
```

### Manual Update

```bash
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan optimize
php artisan up
```

### Clear All Caches

```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
```

### Rebuild All Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

**Note**: Always test deployment procedures on a staging environment before deploying to production.

**Last Updated**: 2026-01-11
