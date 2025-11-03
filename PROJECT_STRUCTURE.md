# Struktur Project RSI - E-Learning Platform

## Backend (Laravel)

### Struktur OOP yang Diterapkan:

1. **Models** (`app/Models/`)
   - Semua model menggunakan Eloquent ORM
   - Relationships didefinisikan dengan jelas
   - Business logic dalam methods model

2. **Repositories** (`app/Repositories/`)
   - `RepositoryInterface` - Interface untuk semua repositories
   - `BaseRepository` - Abstract class dengan CRUD dasar
   - `UserRepository`, `CourseRepository` - Repository spesifik

3. **Services** (`app/Services/`)
   - `AuthService` - Logic untuk authentication
   - `CourseService` - Logic untuk manajemen kursus
   - `GamificationService` - Logic untuk sistem gamification

4. **Controllers** (`app/Http/Controllers/Api/`)
   - `AuthController` - Handle authentication
   - `CourseController` - Handle course operations

5. **Middleware** (`app/Http/Middleware/`)
   - `EnsureMentor` - Middleware untuk mentor
   - `EnsurePelajar` - Middleware untuk pelajar
   - `EnsureAdmin` - Middleware untuk admin

## Database Schema

### Tabel Utama:
- `users` - User utama dengan role
- `pelajar` - Data pelajar (jenjang, level, XP)
- `mentor` - Data mentor (CV, verifikasi)
- `courses` - Kursus
- `course_materials` - Materi kursus
- `course_enrollments` - Pendaftaran kursus
- `missions` - Misi untuk gamification
- `user_progress` - Progress user
- `badges` - Badge/achievement
- `user_badges` - Badge yang dimiliki user
- `forum_posts` - Postingan forum
- `forum_comments` - Komentar forum
- `forum_reports` - Laporan konten forum
- `messages` - Chat mentor-pelajar
- `assignments` - Tugas
- `quizzes` - Kuis
- `quiz_questions` - Soal kuis
- `assignment_submissions` - Pengumpulan tugas
- `quiz_submissions` - Pengumpulan kuis
- `activity_logs` - Log aktivitas

## API Endpoints

### Authentication
- `POST /api/register` - Registrasi
- `POST /api/login` - Login
- `POST /api/logout` - Logout (protected)
- `GET /api/me` - Get current user (protected)

### Courses
- `GET /api/courses` - List courses (public/published)
- `GET /api/courses/filter` - Filter courses
- `GET /api/courses/{id}` - Course detail
- `POST /api/courses/{id}/enroll` - Enroll to course (pelajar)
- `POST /api/courses` - Create course (mentor)
- `PUT /api/courses/{id}` - Update course (mentor)
- `DELETE /api/courses/{id}` - Delete course (mentor)

## Frontend (React)

Struktur akan dibuat dengan komponen OOP menggunakan class components atau functional components dengan hooks.

## Fitur yang Sudah Diimplementasi

✅ Database migrations
✅ Models dengan relationships
✅ Repository pattern
✅ Service layer
✅ Authentication (Register, Login, Logout)
✅ Course management (CRUD)
✅ Course enrollment
✅ Middleware untuk role-based access

## Fitur yang Perlu Ditambahkan

- [ ] Forum diskusi (Post, Comment)
- [ ] Chat mentor-pelajar
- [ ] Gamification (Missions, XP, Level, Badges)
- [ ] Tugas dan Kuis (CRUD, Submission, Grading)
- [ ] Admin features (Verifikasi mentor, Moderasi, Laporan)
- [ ] Profile management
- [ ] File upload handling

