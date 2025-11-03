# Petunjuk Setup Project RSI

## Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js >= 16
- MySQL atau SQLite
- Laragon (sudah terinstall berdasarkan path project)

## Langkah-langkah Setup

### 1. Setup Backend (Laravel)

```bash
# Masuk ke direktori backend
cd backend

# Install dependencies
composer install

# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit file .env untuk konfigurasi database
# Buka .env dan ubah:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=rsi_db
# DB_USERNAME=root
# DB_PASSWORD=  (kosongkan jika menggunakan Laragon default)

# Buat database (jika belum ada)
# Buka phpMyAdmin atau MySQL client dan buat database: rsi_db

# Run migrations
php artisan migrate

# (Optional) Create admin user via tinker
php artisan tinker
# Lalu jalankan:
# $user = \App\Models\User::create(['username' => 'admin', 'name' => 'Admin', 'email' => 'admin@rsi.com', 'password' => bcrypt('password'), 'role' => 'admin']);

# Start development server
php artisan serve
```

Backend API akan tersedia di: `http://localhost:8000`

### 2. Setup Frontend (React)

```bash
# Masuk ke direktori frontend
cd frontend

# Install dependencies
npm install

# Start development server
npm start
```

Frontend akan tersedia di: `http://localhost:3000`

### 3. Konfigurasi CORS

File `.env` di backend perlu dikonfigurasi untuk mengizinkan request dari frontend:

```env
FRONTEND_URL=http://localhost:3000

# Atau tambahkan di config/cors.php jika ada
```

### 4. Test API Endpoints

Gunakan Postman atau curl untuk test API:

#### Register Pelajar
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "pelajar1",
    "name": "Pelajar Satu",
    "email": "pelajar1@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "pelajar",
    "jenjang_pendidikan": "SMA"
  }'
```

#### Register Mentor
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "mentor1",
    "name": "Mentor Satu",
    "email": "mentor1@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "mentor",
    "cv_path": "/path/to/cv.pdf"
  }'
```

#### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "pelajar1@test.com",
    "password": "password123"
  }'
```

Response akan berisi token yang digunakan untuk authenticated requests.

#### Get Current User (dengan token)
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Struktur File Penting

### Backend
- `routes/api.php` - API routes
- `app/Models/` - Eloquent models
- `app/Repositories/` - Repository pattern
- `app/Services/` - Business logic services
- `app/Http/Controllers/Api/` - API controllers
- `database/migrations/` - Database migrations

### Frontend
- `src/` - Source code React
- `public/` - Public assets

## Troubleshooting

### Error: SQLSTATE[HY000] [2002] Connection refused
- Pastikan MySQL server berjalan
- Cek konfigurasi DB_HOST dan DB_PORT di .env

### Error: Class not found
- Jalankan `composer dump-autoload`

### Error: Migration fails
- Pastikan database sudah dibuat
- Cek koneksi database
- Coba drop database dan buat ulang, lalu jalankan migrate lagi

### CORS Error di Frontend
- Pastikan FRONTEND_URL di .env sudah benar
- Atau install package CORS untuk Laravel (sudah include di Laravel 12)

## Development Tips

1. **Database Seeding**: Buat seeder untuk data dummy dengan `php artisan make:seeder`
2. **API Testing**: Gunakan Postman collection atau Laravel Telescope untuk debugging
3. **Logging**: File log ada di `storage/logs/laravel.log`
4. **Clear Cache**: `php artisan cache:clear` dan `php artisan config:clear`

## Production Deployment

Untuk production:
1. Set `APP_ENV=production` di .env
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Setup web server (Nginx/Apache)
5. Setup SSL certificate
6. Konfigurasi environment variables di hosting

