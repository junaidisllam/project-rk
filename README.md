# এলাকার বাজার (Elakar Bazar)

বাংলাদেশের সবচেয়ে সাশ্রয়ী অনলাইন বইয়ের দোকান

## 📚 About

এলাকার বাজার is a modern e-commerce platform for buying books online in Bangladesh. Built with Laravel 12, Vue 3, and Inertia.js, it offers a seamless shopping experience with features like:

- 📖 Extensive book catalog with advanced filtering
- 🛒 Shopping cart and checkout system
- 👤 User authentication (email & Google OAuth)
- 💳 Order management
- 🎨 Beautiful, responsive UI with dark mode support
- ⚡ Fast, optimized performance
- 📱 Mobile-friendly design
- 🔍 SEO optimized

## 🛠️ Tech Stack

### Backend

- **Laravel 12** - PHP Framework
- **Filament 4** - Admin Panel
- **Fortify** - Authentication
- **Socialite** - OAuth (Google)
- **Inertia.js** - Server-side routing

### Frontend

- **Vue 3** - JavaScript Framework
- **Tailwind CSS 4** - Styling
- **Vite** - Build Tool
- **Pinia** - State Management
- **Wayfinder** - Type-safe routing

### Database

- SQLite (development)
- MySQL/PostgreSQL (production)

## 📋 Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM >= 9.x
- MySQL 8.0+ or PostgreSQL 13+ (for production)

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/junaidisllam/project-rk.git elakarbazar
cd elakarbazar
```

### 2. Install dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Environment setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure your `.env` file

Edit `.env` and set your database credentials and other settings:

```env
APP_NAME="এলাকার বাজার"
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# For MySQL, use:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=elakarbazar
# DB_USERNAME=root
# DB_PASSWORD=
```

### 5. Database setup

```bash
# Run migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed
```

### 6. Create storage symlink

```bash
php artisan storage:link
```

### 7. Build frontend assets

```bash
npm run build
```

## 🏃 Running the Application

### Development Mode

```bash
# Option 1: Using Laravel's built-in server
php artisan serve

# Option 2: Using the dev script (recommended)
composer run dev
```

This will start:

- Laravel development server on `http://localhost:8000`
- Vite dev server for hot module replacement
- Queue worker for background jobs

Visit `http://localhost:8000` in your browser.

### Admin Panel

Access the Filament admin panel at `http://localhost:8000/admin`

Create an admin user:

```bash
php artisan make:filament-user
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BookTest.php

# Run with coverage
php artisan test --coverage
```

## 📦 Building for Production

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions.

Quick production build:

```bash
# Build assets
npm run build

# Optimize Laravel
php artisan optimize
```

## 🔧 Available Commands

### Composer Scripts

```bash
composer run dev          # Start development servers
composer run test         # Run tests
composer run deploy       # Deploy to production
composer run setup        # Initial setup
```

### Artisan Commands

```bash
php artisan migrate              # Run migrations
php artisan db:seed             # Seed database
php artisan storage:link        # Create storage symlink
php artisan optimize            # Optimize for production
php artisan optimize:clear      # Clear all caches
php artisan queue:work          # Start queue worker
php artisan pail                # View logs in real-time
```

### NPM Scripts

```bash
npm run dev           # Start Vite dev server
npm run build         # Build for production
npm run lint          # Run ESLint
npm run format        # Format code with Prettier
```

## 📁 Project Structure

```
elakarbazar/
├── app/
│   ├── Console/         # Artisan commands
│   ├── Filament/        # Admin panel resources
│   ├── Http/
│   │   ├── Controllers/ # Route controllers
│   │   └── Middleware/  # HTTP middleware
│   ├── Models/          # Eloquent models
│   └── Policies/        # Authorization policies
├── bootstrap/           # Framework bootstrap
├── config/              # Configuration files
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── public/              # Public assets
├── resources/
│   ├── css/            # CSS files
│   ├── js/
│   │   ├── components/ # Vue components
│   │   ├── layouts/    # Layout components
│   │   ├── pages/      # Inertia pages
│   │   └── Stores/     # Pinia stores
│   └── views/          # Blade templates
├── routes/
│   ├── web.php         # Web routes
│   └── console.php     # Console routes
├── storage/            # Application storage
└── tests/              # Test files
```

## 🎨 Features

### For Customers

- Browse books by category, author, publisher
- Advanced search and filtering
- Add books to cart
- Secure checkout process
- Order tracking
- Wishlist functionality
- User profile management
- Google OAuth login

### For Administrators

- Manage books, authors, publishers
- Order management
- User management
- Category management
- Slider/banner management
- Homepage section customization
- Analytics and reports

## 🔐 Security

- CSRF protection
- XSS protection
- SQL injection prevention
- Secure password hashing
- HTTPS enforcement (production)
- Rate limiting
- Input validation

## 🌐 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## 📄 License

This project is proprietary software. All rights reserved.

## 👥 Team

- **Developer**: Junaid Islam
- **Project**: এলাকার বাজার

## 📞 Support

For issues and questions:

- GitHub Issues: [project-rk/issues](https://github.com/junaidisllam/project-rk/issues)
- Email: support@elakarbazar.com

## 🙏 Acknowledgments

- Laravel Framework
- Filament Admin Panel
- Vue.js
- Tailwind CSS
- Inertia.js

---

Made with ❤️ in Bangladesh
