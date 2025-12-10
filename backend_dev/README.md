# CRM System - Complete Code Files

## Installation Instructions

### Step 1: Copy Files to Laravel Project

Copy files from `dev_code/` to your Laravel project:

- `migrations/` → `backend_dev/database/migrations/`
- `models/` → `backend_dev/app/Models/`
- `controllers/` → `backend_dev/app/Http/Controllers/`
- `routes/web.php` → `backend_dev/routes/web.php` (replace)
- `providers/AppServiceProvider.php` → `backend_dev/app/Providers/AppServiceProvider.php` (replace)
- `views/contacts/` → `backend_dev/resources/views/contacts/`

### Step 2: Copy Frontend Assets

Copy `frontend_dev/assets/` to `backend_dev/public/assets/`

### Step 3: Run Commands
sh
cd backend_dev
php artisan migrate
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve### Step 4: Access Application

Visit: http://localhost:8000