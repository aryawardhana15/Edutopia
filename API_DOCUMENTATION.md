# API Documentation - RSI E-Learning Platform

Base URL: `http://localhost:8000/api`

## Authentication

Semua endpoint yang memerlukan authentication menggunakan Bearer Token dari Laravel Sanctum.

Header: `Authorization: Bearer {token}`

---

## Endpoints

### Authentication

#### Register
- **POST** `/register`
- **Body:**
  ```json
  {
    "username": "string",
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string",
    "role": "pelajar|mentor",
    "jenjang_pendidikan": "string (if pelajar)",
    "peminatan": "string (optional)",
    "cv_path": "string (if mentor)",
    "bidang_keahlian": "string (optional)",
    "pengalaman": "string (optional)"
  }
  ```

#### Login
- **POST** `/login`
- **Body:**
  ```json
  {
    "email": "string",
    "password": "string"
  }
  ```

#### Logout
- **POST** `/logout`
- **Auth:** Required

#### Get Current User
- **GET** `/me`
- **Auth:** Required

---

### Courses

#### List Courses
- **GET** `/courses?search=keyword`
- **Auth:** Required

#### Filter Courses
- **GET** `/courses/filter?tingkat_kesulitan=pemula&jenjang_pendidikan=SMA&category=Programming`
- **Auth:** Required

#### Get Course Detail
- **GET** `/courses/{id}`
- **Auth:** Required

#### Enroll Course
- **POST** `/courses/{id}/enroll`
- **Auth:** Required (Pelajar only)

#### Create Course (Mentor)
- **POST** `/courses`
- **Auth:** Required (Mentor only)
- **Body:**
  ```json
  {
    "title": "string",
    "description": "string",
    "category": "string",
    "tingkat_kesulitan": "pemula|menengah|lanjutan",
    "jenjang_pendidikan": "string",
    "harga": 0,
    "thumbnail": "string",
    "status": "draft|published"
  }
  ```

#### Update Course (Mentor)
- **PUT** `/courses/{id}`
- **Auth:** Required (Mentor only)

#### Delete Course (Mentor)
- **DELETE** `/courses/{id}`
- **Auth:** Required (Mentor only)

---

### Materials

#### List Materials
- **GET** `/courses/{courseId}/materials`
- **Auth:** Required

#### Get Material Detail
- **GET** `/materials/{id}`
- **Auth:** Required

#### Get Course Progress
- **GET** `/courses/{courseId}/progress`
- **Auth:** Required (Pelajar only)

#### Complete Material
- **POST** `/materials/{id}/complete`
- **Auth:** Required (Pelajar only)

#### Create Material (Mentor)
- **POST** `/courses/{courseId}/materials`
- **Auth:** Required (Mentor only)
- **Body:**
  ```json
  {
    "title": "string",
    "description": "string",
    "type": "text|video|document|link",
    "content_path": "string",
    "order": 1
  }
  ```

#### Update Material (Mentor)
- **PUT** `/materials/{id}`
- **Auth:** Required (Mentor only)

#### Delete Material (Mentor)
- **DELETE** `/materials/{id}`
- **Auth:** Required (Mentor only)

---

### Assignments

#### List Assignments
- **GET** `/courses/{courseId}/assignments`
- **Auth:** Required

#### Get Assignment Detail
- **GET** `/assignments/{id}`
- **Auth:** Required

#### Submit Assignment
- **POST** `/assignments/{id}/submit`
- **Auth:** Required (Pelajar only)
- **Body:**
  ```json
  {
    "submission_content": "string",
    "file_attachment": "string"
  }
  ```

#### Create Assignment (Mentor)
- **POST** `/courses/{courseId}/assignments`
- **Auth:** Required (Mentor only)
- **Body:**
  ```json
  {
    "title": "string",
    "instruction": "string",
    "file_attachment": "string",
    "deadline": "datetime",
    "weight": 0,
    "max_score": 100
  }
  ```

#### Get Submissions (Mentor)
- **GET** `/assignments/{id}/submissions`
- **Auth:** Required (Mentor only)

#### Grade Submission (Mentor)
- **POST** `/submissions/{id}/grade`
- **Auth:** Required (Mentor only)
- **Body:**
  ```json
  {
    "score": 85,
    "feedback": "string"
  }
  ```

---

### Quizzes

#### List Quizzes
- **GET** `/courses/{courseId}/quizzes`
- **Auth:** Required

#### Get Quiz Detail
- **GET** `/quizzes/{id}`
- **Auth:** Required

#### Submit Quiz
- **POST** `/quizzes/{id}/submit`
- **Auth:** Required (Pelajar only)
- **Body:**
  ```json
  {
    "answers": {
      "question_id": "answer"
    }
  }
  ```

#### Create Quiz (Mentor)
- **POST** `/courses/{courseId}/quizzes`
- **Auth:** Required (Mentor only)
- **Body:**
  ```json
  {
    "title": "string",
    "instruction": "string",
    "duration": 60,
    "max_score": 100,
    "weight": 0,
    "deadline": "datetime",
    "questions": [
      {
        "question": "string",
        "type": "multiple_choice|true_false|essay",
        "options": ["A", "B", "C", "D"],
        "correct_answer": "A",
        "score": 10,
        "order": 0
      }
    ]
  }
  ```

---

### Gamification

#### Get User Stats
- **GET** `/gamification/stats`
- **Auth:** Required

#### Get Leaderboard
- **GET** `/gamification/leaderboard?limit=10`
- **Auth:** Required

#### Get Missions
- **GET** `/gamification/missions`
- **Auth:** Required

#### Get Badges
- **GET** `/gamification/badges`
- **Auth:** Required

---

### File Upload

#### Upload Profile Photo
- **POST** `/files/profile-photo`
- **Auth:** Required
- **Content-Type:** `multipart/form-data`
- **Body:** `photo` (file)

#### Upload CV
- **POST** `/files/cv`
- **Auth:** Required (Mentor only)
- **Content-Type:** `multipart/form-data`
- **Body:** `cv` (file)

#### Upload Material File
- **POST** `/files/material`
- **Auth:** Required
- **Content-Type:** `multipart/form-data`
- **Body:** `file` (file)

#### Upload Submission File
- **POST** `/files/submission`
- **Auth:** Required
- **Content-Type:** `multipart/form-data`
- **Body:** `file` (file)

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "string",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "string",
  "errors": {}
}
```

---

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

