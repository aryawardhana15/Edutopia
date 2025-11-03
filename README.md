# RSI - Platform E-Learning

Platform e-learning dengan fitur gamification, forum diskusi, dan chat mentor-pelajar.

## Tech Stack
- **Backend**: Laravel 12 (PHP OOP dengan Repository Pattern & Service Layer)
- **Frontend**: React (JavaScript/TypeScript)
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Sanctum

## Fitur Utama

### ✅ Sudah Diimplementasi
1. **Authentication & Authorization**
   - ✅ Registrasi (Pelajar/Mentor)
   - ✅ Login/Logout
   - ✅ Role-based access control (Pelajar, Mentor, Admin)

2. **Kursus**
   - ✅ Buat, Edit, Hapus Kursus (Mentor)
   - ✅ Cari & Filter Kursus
   - ✅ Bergabung dengan Kursus
   - ✅ Database schema untuk Materi Kursus

3. **Gamification** (Database & Service layer ready)
   - ✅ Database schema untuk Level & XP
   - ✅ Database schema untuk Misi dan Badge
   - ✅ Service layer untuk gamification logic

### 🚧 Perlu Diimplementasi
- Forum Diskusi (Database ready)
- Chat Mentor-Pelajar (Database ready)
- Tugas/Kuis & Penilaian (Database ready)
- Admin features (Verifikasi Mentor, Moderasi, Laporan)
- Update Profil
- File upload handling

## Struktur Project OOP

### Backend Architecture (Laravel)
```
backend/
├── app/
│   ├── Models/              # Eloquent Models dengan relationships
│   ├── Repositories/        # Repository Pattern
│   │   ├── RepositoryInterface.php
│   │   ├── BaseRepository.php
│   │   ├── UserRepository.php
│   │   └── CourseRepository.php
│   ├── Services/            # Business Logic Layer
│   │   ├── AuthService.php
│   │   ├── CourseService.php
│   │   └── GamificationService.php
│   ├── Http/
│   │   ├── Controllers/Api/ # API Controllers
│   │   └── Middleware/      # Role-based middleware
│   └── ...
├── database/
│   └── migrations/          # 20+ migrations untuk semua entitas
└── routes/
    └── api.php             # API routes
```

### Prinsip OOP yang Diterapkan:
1. **Encapsulation** - Data dan methods di-enkapsulasi dalam class
2. **Inheritance** - BaseRepository sebagai parent class
3. **Polymorphism** - RepositoryInterface untuk abstraction
4. **Separation of Concerns** - Repository, Service, Controller terpisah

## Installation

### Backend (Laravel)
```bash
cd backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database (edit .env untuk konfigurasi database)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=rsi_db
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Start server
php artisan serve
# API akan tersedia di http://localhost:8000/api
```

### Frontend (React)
```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm start
# Frontend akan tersedia di http://localhost:3000
```

## API Endpoints

Base URL: `http://localhost:8000/api`

### Authentication
- `POST /register` - Registrasi user baru
- `POST /login` - Login user
- `POST /logout` - Logout (protected)
- `GET /me` - Get current user (protected)

### Courses
- `GET /courses` - List semua kursus (published)
- `GET /courses/filter` - Filter kursus
- `GET /courses/{id}` - Detail kursus
- `POST /courses/{id}/enroll` - Bergabung kursus (Pelajar only)
- `POST /courses` - Buat kursus (Mentor only)
- `PUT /courses/{id}` - Update kursus (Mentor only)
- `DELETE /courses/{id}` - Hapus kursus (Mentor only)

## Database Schema

Lihat `PROJECT_STRUCTURE.md` untuk detail lengkap schema database.

## Next Steps

1. **Setup Database** - Konfigurasi database di `.env` dan jalankan migrations
2. **Test API** - Gunakan Postman atau tools lain untuk test endpoints
3. **Frontend Integration** - Connect React app dengan Laravel API
4. **Implementasi Fitur** - Lanjutkan implementasi fitur yang belum selesai
5. **File Upload** - Setup file storage untuk CV, materi, dll

## Dokumentasi Lengkap

Lihat `PROJECT_STRUCTURE.md` untuk dokumentasi lengkap tentang struktur project dan arsitektur OOP.

