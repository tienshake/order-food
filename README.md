# Laravel Filament Application

A web application built with Laravel v11.36.1 and Filament v3.2.

## Requirements
- PHP >= 8.2.12
- Composer
- MySQL/MariaDB
- Node.js & NPM

## Installation Steps

1. **Clone the repository**
```bash
git clone https://github.com/tienshake/inventory-management-system
cd inventory-management-system
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Set up environment file**
```bash
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

4. **Configure your database in .env file**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

5. **Run database migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Start the development server**
```bash
php artisan serve
```

The application will be available at: http://127.0.0.1:8000

Admin panel can be accessed at: http://127.0.0.1:8000/admin
## Common Issues

If you encounter permission issues:
```bash
chmod -R 777 storage bootstrap/cache
```

For composer memory limit errors:
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```
