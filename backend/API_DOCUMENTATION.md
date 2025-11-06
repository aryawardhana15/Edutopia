# API Documentation - RSI E-Learning Platform

Base URL: `http://localhost:8000/api`

## Authentication

Semua endpoint (kecuali register dan login) memerlukan token authentication di header:
```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### Register
- **POST** `/register`
- **Body:**
```json
{
  "username": "pelajar1",
  "name": "Pelajar Satu",
  "email": "pelajar1@test.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "pelajar",
  "jenjang_pendidikan": "SMA"
}
```

#### Login
- **POST** `/login`
- **Body:**
```json
{
  "email": "pelajar1@test.com",
  "password": "password123"
}
```

#### Logout
- **POST** `/logout`
- **Auth:** Required

#### Get Current User
- **GET** `/me`
- **Auth:** Required

### Profile

#### Get Profile
- **GET** `/profile`
- **Auth:** Required

#### Update Profile
- **PUT** `/profile`
- **Auth:** Required
- **Body:**
```json
{
  "name": "New Name",
  "email": "newemail@test.com",
  "bio": "Bio text"
}
```

#### Update Password
- **PUT** `/profile/password`
- **Auth:** Required
- **Body:**
```json
{
  "current_password": "oldpassword",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

#### Update Photo
- **PUT** `/profile/photo`
- **Auth:** Required
- **Body:**
```json
{
  "photo": "path/to/photo.jpg"
}
```

### Courses

#### List Courses
- **GET** `/courses?search=keyword`
- **Auth:** Required

#### Filter Courses
- **GET** `/courses/filter?tingkat_kesulitan=pemula&jenjang_pendidikan=SMA`
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
  "title": "Course Title",
  "description": "Course description",
  "category": "Programming",
  "tingkat_kesulitan": "pemula",
  "jenjang_pendidikan": "SMA",
  "harga": 0,
  "status": "published"
}
```

#### Update Course (Mentor)
- **PUT** `/courses/{id}`
- **Auth:** Required (Mentor only)

#### Delete Course (Mentor)
- **DELETE** `/courses/{id}`
- **Auth:** Required (Mentor only)

### Materials

#### List Materials
- **GET** `/courses/{courseId}/materials`
- **Auth:** Required

#### Get Material Detail
- **GET** `/materials/{id}`
- **Auth:** Required

#### Create Material (Mentor)
- **POST** `/courses/{courseId}/materials`
- **Auth:** Required (Mentor only)
- **Body:**
```json
{
  "title": "Material Title",
  "description": "Description",
  "type": "video",
  "content_path": "https://youtube.com/watch?v=...",
  "order": 1
}
```

#### Update Material (Mentor)
- **PUT** `/materials/{id}`
- **Auth:** Required (Mentor only)

#### Delete Material (Mentor)
- **DELETE** `/materials/{id}`
- **Auth:** Required (Mentor only)

#### Complete Material (Pelajar)
- **POST** `/materials/{id}/complete`
- **Auth:** Required (Pelajar only)

### Forum

#### List Forum Posts
- **GET** `/courses/{courseId}/forums`
- **Auth:** Required

#### Get Forum Post Detail
- **GET** `/forums/{id}`
- **Auth:** Required

#### Create Forum Post
- **POST** `/courses/{courseId}/forums`
- **Auth:** Required
- **Body:**
```json
{
  "title": "Post Title",
  "content": "Post content",
  "category": "Question",
  "tags": ["tag1", "tag2"]
}
```

#### Reply to Post
- **POST** `/forums/{id}/replies`
- **Auth:** Required
- **Body:**
```json
{
  "content": "Reply content",
  "parent_id": null
}
```

#### Like Post
- **POST** `/forums/{id}/like`
- **Auth:** Required

#### Report Post
- **POST** `/forums/{id}/report`
- **Auth:** Required
- **Body:**
```json
{
  "reason": "Inappropriate content"
}
```

#### Search Forums
- **GET** `/forums/search?q=keyword`
- **Auth:** Required

#### Pin Post (Mentor/Admin)
- **POST** `/forums/{id}/pin`
- **Auth:** Required (Mentor/Admin only)

#### Lock Post (Mentor/Admin)
- **POST** `/forums/{id}/lock`
- **Auth:** Required (Mentor/Admin only)

### Chat

#### Get Chat Rooms
- **GET** `/chats`
- **Auth:** Required

#### Get Messages
- **GET** `/chats/{userId}/messages?limit=50`
- **Auth:** Required

#### Send Message
- **POST** `/chats/{userId}/send`
- **Auth:** Required
- **Body:**
```json
{
  "message": "Hello, I need help"
}
```

#### Mark Messages as Read
- **POST** `/chats/{userId}/read`
- **Auth:** Required

### Assignments

#### List Assignments
- **GET** `/courses/{courseId}/assignments`
- **Auth:** Required

#### Get Assignment Detail
- **GET** `/assignments/{id}`
- **Auth:** Required

#### Create Assignment (Mentor)
- **POST** `/courses/{courseId}/assignments`
- **Auth:** Required (Mentor only)
- **Body:**
```json
{
  "title": "Assignment Title",
  "instruction": "Instructions",
  "deadline": "2025-12-31 23:59:59",
  "max_score": 100,
  "weight": 20,
  "status": "published"
}
```

#### Submit Assignment (Pelajar)
- **POST** `/assignments/{id}/submit`
- **Auth:** Required (Pelajar only)
- **Body:**
```json
{
  "submission_content": "Answer text",
  "file_attachment": "path/to/file.pdf"
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
  "feedback": "Good work!"
}
```

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

### File Upload

#### Upload Profile Photo
- **POST** `/files/profile-photo`
- **Auth:** Required
- **Content-Type:** multipart/form-data
- **Body:** `photo` (file, max 2MB, jpeg/png/jpg)

#### Upload CV
- **POST** `/files/cv`
- **Auth:** Required
- **Content-Type:** multipart/form-data
- **Body:** `cv` (file, max 5MB, pdf/doc/docx)

#### Upload Material File
- **POST** `/files/material`
- **Auth:** Required
- **Content-Type:** multipart/form-data
- **Body:** `file` (file, max 10MB, pdf/doc/docx/pptx)

#### Upload Submission File
- **POST** `/files/submission`
- **Auth:** Required
- **Content-Type:** multipart/form-data
- **Body:** `file` (file, max 10MB)

#### Get File
- **GET** `/files/{filename}`
- **Auth:** Required

### Admin

#### Get Pending Mentors
- **GET** `/admin/mentors/pending`
- **Auth:** Required (Admin only)

#### Verify Mentor
- **POST** `/admin/mentors/{id}/verify`
- **Auth:** Required (Admin only)

#### Reject Mentor
- **POST** `/admin/mentors/{id}/reject`
- **Auth:** Required (Admin only)
- **Body:**
```json
{
  "rejection_reason": "CV tidak memenuhi syarat"
}
```

#### Get All Users
- **GET** `/admin/users?role=pelajar&search=keyword`
- **Auth:** Required (Admin only)

#### Suspend User
- **POST** `/admin/users/{id}/suspend`
- **Auth:** Required (Admin only)

#### Get Forum Reports
- **GET** `/admin/reports`
- **Auth:** Required (Admin only)

#### Delete Forum Post
- **DELETE** `/admin/forums/{id}`
- **Auth:** Required (Admin only)

#### Get Dashboard Stats
- **GET** `/admin/stats`
- **Auth:** Required (Admin only)

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... } // For validation errors
}
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

