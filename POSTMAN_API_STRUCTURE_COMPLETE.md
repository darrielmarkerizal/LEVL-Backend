# STRUKTUR LENGKAP API - LEVL POSTMAN COLLECTION

**Versi**: 1.0  
**Tanggal**: 2026-03-14  
**Base URL**: `{{base_url}}/v1`

---

## 📱 [MOBILE] STUDENT APP

### 🔐 1. Authentication
```
POST   [Mobile] Auth - Login
POST   [Mobile] Auth - Register Student
POST   [Mobile] Auth - Logout
POST   [Mobile] Auth - Refresh Token
POST   [Mobile] Auth - Forgot Password
POST   [Mobile] Auth - Reset Password
POST   [Mobile] Auth - Verify Email
GET    [Mobile] Auth - Get Current User
```

### 📚 2. Learning

#### 2.1 Courses
```
GET    [Mobile] Courses - List Enrolled Courses
GET    [Mobile] Courses - Get Course Detail
GET    [Mobile] Courses - Get Course Progress
GET    [Mobile] Courses - Get Course Content Tree
```

#### 2.2 Units
```
GET    [Mobile] Units - List Course Units
GET    [Mobile] Units - Get Unit Detail
GET    [Mobile] Units - Get Unit Progress
```

#### 2.3 Lessons
```
GET    [Mobile] Lessons - List Unit Lessons
GET    [Mobile] Lessons - Get Lesson Detail
GET    [Mobile] Lessons - Get Lesson Content
POST   [Mobile] Lessons - Mark as Complete
POST   [Mobile] Lessons - Track Progress
GET    [Mobile] Lessons - Get Next Lesson
```

#### 2.4 Assignments
```
GET    [Mobile] Assignments - List My Assignments
GET    [Mobile] Assignments - Get Assignment Detail
POST   [Mobile] Assignments - Submit Assignment
GET    [Mobile] Assignments - Get My Submissions
GET    [Mobile] Assignments - Get Submission Detail
POST   [Mobile] Assignments - Upload File
```

#### 2.5 Quizzes
```
GET    [Mobile] Quizzes - List My Quizzes
GET    [Mobile] Quizzes - Get Quiz Detail
POST   [Mobile] Quizzes - Start Quiz Attempt
POST   [Mobile] Quizzes - Submit Answer
POST   [Mobile] Quizzes - Submit Quiz
GET    [Mobile] Quizzes - Get Quiz Result
GET    [Mobile] Quizzes - Get My Attempts
```

### 🎮 3. Gamification

#### 3.1 Stats & Progress
```
GET    [Mobile] Gamification - My Stats
GET    [Mobile] Gamification - My Level
GET    [Mobile] Gamification - Daily XP Stats
GET    [Mobile] Gamification - Summary
GET    [Mobile] Gamification - Milestones
```

#### 3.2 Badges
```
GET    [Mobile] Gamification - My Badges
GET    [Mobile] Gamification - Badge Detail
GET    [Mobile] Gamification - Available Badges
```

#### 3.3 Leaderboard
```
GET    [Mobile] Gamification - Global Leaderboard
GET    [Mobile] Gamification - Course Leaderboard
GET    [Mobile] Gamification - My Rank
GET    [Mobile] Gamification - Surrounding Users
```

#### 3.4 XP & Points
```
GET    [Mobile] Gamification - XP History
GET    [Mobile] Gamification - Points History
GET    [Mobile] Gamification - XP Sources
```

### 💬 4. Forums

#### 4.1 Threads
```
GET    [Mobile] Forums - List Threads
POST   [Mobile] Forums - Create Thread
GET    [Mobile] Forums - Thread Detail
PUT    [Mobile] Forums - Update Thread
DELETE [Mobile] Forums - Delete Thread
GET    [Mobile] Forums - My Threads
```

#### 4.2 Replies
```
GET    [Mobile] Forums - List Replies
POST   [Mobile] Forums - Reply to Thread
PUT    [Mobile] Forums - Update Reply
DELETE [Mobile] Forums - Delete Reply
```

#### 4.3 Reactions
```
POST   [Mobile] Forums - React to Thread
POST   [Mobile] Forums - React to Reply
DELETE [Mobile] Forums - Remove Reaction
```

### 📊 5. Dashboard
```
GET    [Mobile] Dashboard - Overview
GET    [Mobile] Dashboard - Recent Activities
GET    [Mobile] Dashboard - Progress Summary
GET    [Mobile] Dashboard - Upcoming Deadlines
GET    [Mobile] Dashboard - Recent Achievements
```

### 👤 6. Profile
```
GET    [Mobile] Profile - Get My Profile
PUT    [Mobile] Profile - Update Profile
POST   [Mobile] Profile - Upload Avatar
PUT    [Mobile] Profile - Change Password
GET    [Mobile] Profile - My Enrollments
GET    [Mobile] Profile - My Certificates
```

---

## 💻 [WEB] ADMIN DASHBOARD

### 🔐 1. Authentication
```
POST   [Admin] Auth - Login
POST   [Admin] Auth - Logout
POST   [Admin] Auth - Refresh Token
GET    [Admin] Auth - Get Current User
```

### 👥 2. User Management

#### 2.1 Users List & CRUD
```
GET    [Admin] Users - List All Users
GET    [Admin] Users - Get User Detail
POST   [Admin] Users - Create User
PUT    [Admin] Users - Update User
DELETE [Admin] Users - Delete User
POST   [Admin] Users - Bulk Import
POST   [Admin] Users - Export Users
```

#### 2.2 Students
```
GET    [Admin] Users - List Students
POST   [Admin] Users - Create Student
GET    [Admin] Users - Student Detail
PUT    [Admin] Users - Update Student
DELETE [Admin] Users - Delete Student
GET    [Admin] Users - Student Progress
GET    [Admin] Users - Student Enrollments
```

#### 2.3 Instructors
```
GET    [Admin] Users - List Instructors
POST   [Admin] Users - Create Instructor
GET    [Admin] Users - Instructor Detail
PUT    [Admin] Users - Update Instructor
DELETE [Admin] Users - Delete Instructor
GET    [Admin] Users - Instructor Courses
GET    [Admin] Users - Instructor Statistics
```

#### 2.4 Admins
```
GET    [Admin] Users - List Admins
POST   [Admin] Users - Create Admin
GET    [Admin] Users - Admin Detail
PUT    [Admin] Users - Update Admin
DELETE [Admin] Users - Delete Admin
```

#### 2.5 Roles & Permissions
```
GET    [Admin] Roles - List Roles
GET    [Admin] Roles - Get Role Detail
POST   [Admin] Roles - Create Role
PUT    [Admin] Roles - Update Role
DELETE [Admin] Roles - Delete Role
GET    [Admin] Permissions - List Permissions
POST   [Admin] Users - Assign Role
POST   [Admin] Users - Assign Permission
```

### 📖 3. Course Management

#### 3.1 Courses
```
GET    [Admin] Courses - List All Courses
GET    [Admin] Courses - Get Course Detail
POST   [Admin] Courses - Create Course
PUT    [Admin] Courses - Update Course
DELETE [Admin] Courses - Delete Course
POST   [Admin] Courses - Publish Course
POST   [Admin] Courses - Unpublish Course
POST   [Admin] Courses - Duplicate Course
GET    [Admin] Courses - Course Statistics
```

#### 3.2 Course Settings
```
GET    [Admin] Courses - Get Settings
PUT    [Admin] Courses - Update Settings
PUT    [Admin] Courses - Update Prerequisites
PUT    [Admin] Courses - Update Instructors
```

### 📝 4. Content Management

#### 4.1 Units
```
GET    [Admin] Content - List Units
GET    [Admin] Content - Get Unit Detail
POST   [Admin] Content - Create Unit
PUT    [Admin] Content - Update Unit
DELETE [Admin] Content - Delete Unit
POST   [Admin] Content - Reorder Units
POST   [Admin] Content - Duplicate Unit
```

#### 4.2 Lessons
```
GET    [Admin] Content - List Lessons
GET    [Admin] Content - Get Lesson Detail
POST   [Admin] Content - Create Lesson
PUT    [Admin] Content - Update Lesson
DELETE [Admin] Content - Delete Lesson
POST   [Admin] Content - Reorder Lessons
POST   [Admin] Content - Duplicate Lesson
```

#### 4.3 Assignments
```
GET    [Admin] Content - List Assignments
GET    [Admin] Content - Get Assignment Detail
POST   [Admin] Content - Create Assignment
PUT    [Admin] Content - Update Assignment
DELETE [Admin] Content - Delete Assignment
GET    [Admin] Content - Assignment Submissions
```

#### 4.4 Quizzes
```
GET    [Admin] Content - List Quizzes
GET    [Admin] Content - Get Quiz Detail
POST   [Admin] Content - Create Quiz
PUT    [Admin] Content - Update Quiz
DELETE [Admin] Content - Delete Quiz
POST   [Admin] Content - Add Question
PUT    [Admin] Content - Update Question
DELETE [Admin] Content - Delete Question
POST   [Admin] Content - Reorder Questions
```

#### 4.5 Media & Files
```
POST   [Admin] Media - Upload Image
POST   [Admin] Media - Upload Document
POST   [Admin] Media - Upload Video
DELETE [Admin] Media - Delete Media
GET    [Admin] Media - List Media
```

### 📊 5. Reports & Analytics

#### 5.1 User Reports
```
GET    [Admin] Reports - User Statistics
GET    [Admin] Reports - User Growth
GET    [Admin] Reports - Active Users
GET    [Admin] Reports - User Engagement
POST   [Admin] Reports - Export User Report
```

#### 5.2 Course Reports
```
GET    [Admin] Reports - Course Statistics
GET    [Admin] Reports - Course Enrollment
GET    [Admin] Reports - Course Completion
GET    [Admin] Reports - Popular Courses
POST   [Admin] Reports - Export Course Report
```

#### 5.3 Learning Reports
```
GET    [Admin] Reports - Completion Rates
GET    [Admin] Reports - Average Scores
GET    [Admin] Reports - Time Spent
GET    [Admin] Reports - Progress Overview
POST   [Admin] Reports - Export Learning Report
```

#### 5.4 Gamification Reports
```
GET    [Admin] Reports - XP Distribution
GET    [Admin] Reports - Badge Awards
GET    [Admin] Reports - Leaderboard Stats
GET    [Admin] Reports - Engagement Metrics
```

### 🎯 6. Enrollment Management

#### 6.1 Enrollments
```
GET    [Admin] Enrollments - List All Enrollments
GET    [Admin] Enrollments - Get Enrollment Detail
POST   [Admin] Enrollments - Enroll Student
PUT    [Admin] Enrollments - Update Enrollment
DELETE [Admin] Enrollments - Remove Enrollment
POST   [Admin] Enrollments - Bulk Enroll
POST   [Admin] Enrollments - Export Enrollments
```

#### 6.2 Enrollment Status
```
PUT    [Admin] Enrollments - Activate Enrollment
PUT    [Admin] Enrollments - Suspend Enrollment
PUT    [Admin] Enrollments - Complete Enrollment
PUT    [Admin] Enrollments - Cancel Enrollment
```

#### 6.3 Enrollment Keys
```
GET    [Admin] Enrollment Keys - List Keys
POST   [Admin] Enrollment Keys - Generate Key
DELETE [Admin] Enrollment Keys - Revoke Key
GET    [Admin] Enrollment Keys - Key Usage
```

### 🎮 7. Gamification Management

#### 7.1 Badges
```
GET    [Admin] Badges - List All Badges
GET    [Admin] Badges - Get Badge Detail
POST   [Admin] Badges - Create Badge
PUT    [Admin] Badges - Update Badge
DELETE [Admin] Badges - Delete Badge
POST   [Admin] Badges - Upload Icon
GET    [Admin] Badges - Badge Statistics
```

#### 7.2 Badge Rules
```
GET    [Admin] Badge Rules - List Rules
GET    [Admin] Badge Rules - Get Rule Detail
POST   [Admin] Badge Rules - Create Rule
PUT    [Admin] Badge Rules - Update Rule
DELETE [Admin] Badge Rules - Delete Rule
POST   [Admin] Badge Rules - Test Rule
```

#### 7.3 Levels
```
GET    [Admin] Levels - List Level Configs
GET    [Admin] Levels - Get Level Detail
POST   [Admin] Levels - Create Level
PUT    [Admin] Levels - Update Level
DELETE [Admin] Levels - Delete Level
POST   [Admin] Levels - Sync Levels
GET    [Admin] Levels - Level Statistics
```

#### 7.4 XP Sources
```
GET    [Admin] XP Sources - List Sources
GET    [Admin] XP Sources - Get Source Detail
POST   [Admin] XP Sources - Create Source
PUT    [Admin] XP Sources - Update Source
DELETE [Admin] XP Sources - Delete Source
```

#### 7.5 Leaderboard
```
GET    [Admin] Leaderboard - Global Rankings
GET    [Admin] Leaderboard - Course Rankings
POST   [Admin] Leaderboard - Update Rankings
POST   [Admin] Leaderboard - Reset Rankings
```

### 🗑️ 8. Trash Management
```
GET    [Admin] Trash - List Deleted Items
GET    [Admin] Trash - Get Item Detail
POST   [Admin] Trash - Restore Item
DELETE [Admin] Trash - Permanent Delete
POST   [Admin] Trash - Bulk Restore
DELETE [Admin] Trash - Empty Trash
GET    [Admin] Trash - Source Types
```

### ⚙️ 9. System Settings

#### 9.1 General Settings
```
GET    [Admin] Settings - Get All Settings
PUT    [Admin] Settings - Update Settings
POST   [Admin] Settings - Reset to Default
```

#### 9.2 Categories
```
GET    [Admin] Categories - List Categories
POST   [Admin] Categories - Create Category
PUT    [Admin] Categories - Update Category
DELETE [Admin] Categories - Delete Category
```

#### 9.3 Tags
```
GET    [Admin] Tags - List Tags
POST   [Admin] Tags - Create Tag
PUT    [Admin] Tags - Update Tag
DELETE [Admin] Tags - Delete Tag
```

#### 9.4 Master Data
```
GET    [Admin] Master Data - List Types
GET    [Admin] Master Data - Get Type Data
POST   [Admin] Master Data - Create Item
PUT    [Admin] Master Data - Update Item
DELETE [Admin] Master Data - Delete Item
```

### 📋 10. Activity & Audit Logs

#### 10.1 Activity Logs
```
GET    [Admin] Activity Logs - List Logs
GET    [Admin] Activity Logs - Get Log Detail
POST   [Admin] Activity Logs - Export Logs
```

#### 10.2 Audit Logs
```
GET    [Admin] Audit Logs - List Logs
GET    [Admin] Audit Logs - Get Log Detail
GET    [Admin] Audit Logs - List Actions
POST   [Admin] Audit Logs - Export Logs
```

---

## 🎓 [WEB] INSTRUCTOR DASHBOARD

### 🔐 1. Authentication
```
POST   [Instructor] Auth - Login
POST   [Instructor] Auth - Logout
POST   [Instructor] Auth - Refresh Token
GET    [Instructor] Auth - Get Current User
```

### 📖 2. My Courses

#### 2.1 Course List & Detail
```
GET    [Instructor] Courses - List My Courses
GET    [Instructor] Courses - Get Course Detail
GET    [Instructor] Courses - Course Statistics
GET    [Instructor] Courses - Enrolled Students
GET    [Instructor] Courses - Course Progress
```

#### 2.2 Course Settings
```
GET    [Instructor] Courses - Get Settings
PUT    [Instructor] Courses - Update Settings
```

### 📝 3. Content Creation

#### 3.1 Units
```
GET    [Instructor] Content - List Units
GET    [Instructor] Content - Get Unit Detail
POST   [Instructor] Content - Create Unit
PUT    [Instructor] Content - Update Unit
DELETE [Instructor] Content - Delete Unit
POST   [Instructor] Content - Reorder Units
```

#### 3.2 Lessons
```
GET    [Instructor] Content - List Lessons
GET    [Instructor] Content - Get Lesson Detail
POST   [Instructor] Content - Create Lesson
PUT    [Instructor] Content - Update Lesson
DELETE [Instructor] Content - Delete Lesson
POST   [Instructor] Content - Reorder Lessons
POST   [Instructor] Content - Upload Content
```

#### 3.3 Assignments
```
GET    [Instructor] Content - List Assignments
GET    [Instructor] Content - Get Assignment Detail
POST   [Instructor] Content - Create Assignment
PUT    [Instructor] Content - Update Assignment
DELETE [Instructor] Content - Delete Assignment
GET    [Instructor] Content - Assignment Submissions
```

#### 3.4 Quizzes
```
GET    [Instructor] Content - List Quizzes
GET    [Instructor] Content - Get Quiz Detail
POST   [Instructor] Content - Create Quiz
PUT    [Instructor] Content - Update Quiz
DELETE [Instructor] Content - Delete Quiz
POST   [Instructor] Content - Add Question
PUT    [Instructor] Content - Update Question
DELETE [Instructor] Content - Delete Question
```

### ✅ 4. Grading

#### 4.1 Submissions
```
GET    [Instructor] Grading - List Submissions
GET    [Instructor] Grading - Get Submission Detail
GET    [Instructor] Grading - Pending Submissions
GET    [Instructor] Grading - Graded Submissions
```

#### 4.2 Grade Management
```
POST   [Instructor] Grading - Grade Submission
PUT    [Instructor] Grading - Update Grade
POST   [Instructor] Grading - Release Grades
POST   [Instructor] Grading - Bulk Release
POST   [Instructor] Grading - Add Feedback
PUT    [Instructor] Grading - Update Feedback
```

#### 4.3 Grade Reports
```
GET    [Instructor] Grading - Grade Distribution
GET    [Instructor] Grading - Student Grades
POST   [Instructor] Grading - Export Grades
```

### 💬 5. Forums

#### 5.1 Forum Management
```
GET    [Instructor] Forums - List Course Forums
GET    [Instructor] Forums - Thread Detail
POST   [Instructor] Forums - Reply to Thread
POST   [Instructor] Forums - Pin Thread
POST   [Instructor] Forums - Unpin Thread
DELETE [Instructor] Forums - Delete Thread
DELETE [Instructor] Forums - Delete Reply
```

#### 5.2 Forum Moderation
```
POST   [Instructor] Forums - Lock Thread
POST   [Instructor] Forums - Unlock Thread
POST   [Instructor] Forums - Mark as Resolved
GET    [Instructor] Forums - Reported Content
POST   [Instructor] Forums - Moderate Content
```

### 📊 6. Course Analytics

#### 6.1 Overview
```
GET    [Instructor] Analytics - Course Overview
GET    [Instructor] Analytics - Enrollment Trends
GET    [Instructor] Analytics - Completion Rates
GET    [Instructor] Analytics - Engagement Metrics
```

#### 6.2 Student Analytics
```
GET    [Instructor] Analytics - Student Progress
GET    [Instructor] Analytics - Student Performance
GET    [Instructor] Analytics - At-Risk Students
GET    [Instructor] Analytics - Top Performers
```

#### 6.3 Content Analytics
```
GET    [Instructor] Analytics - Lesson Completion
GET    [Instructor] Analytics - Assignment Statistics
GET    [Instructor] Analytics - Quiz Statistics
GET    [Instructor] Analytics - Time Spent
```

#### 6.4 Export Reports
```
POST   [Instructor] Analytics - Export Overview
POST   [Instructor] Analytics - Export Student Data
POST   [Instructor] Analytics - Export Grades
```

### 👤 7. Profile
```
GET    [Instructor] Profile - Get My Profile
PUT    [Instructor] Profile - Update Profile
POST   [Instructor] Profile - Upload Avatar
PUT    [Instructor] Profile - Change Password
GET    [Instructor] Profile - My Statistics
```

---

## 🌐 [SHARED] COMMON APIs

### 🔐 1. Authentication
```
POST   [Shared] Auth - Login
POST   [Shared] Auth - Register
POST   [Shared] Auth - Logout
POST   [Shared] Auth - Refresh Token
POST   [Shared] Auth - Forgot Password
POST   [Shared] Auth - Reset Password
POST   [Shared] Auth - Verify Email
POST   [Shared] Auth - Resend Verification
GET    [Shared] Auth - Get Current User
PUT    [Shared] Auth - Update Profile
```

### 👤 2. Profile Management
```
GET    [Shared] Profile - Get My Profile
PUT    [Shared] Profile - Update Profile
POST   [Shared] Profile - Upload Avatar
DELETE [Shared] Profile - Delete Avatar
PUT    [Shared] Profile - Change Password
PUT    [Shared] Profile - Update Email
PUT    [Shared] Profile - Update Preferences
GET    [Shared] Profile - Get Preferences
```

### 🔔 3. Notifications

#### 3.1 Notification List
```
GET    [Shared] Notifications - List Notifications
GET    [Shared] Notifications - Unread Count
GET    [Shared] Notifications - Get Notification Detail
```

#### 3.2 Notification Actions
```
PUT    [Shared] Notifications - Mark as Read
PUT    [Shared] Notifications - Mark All as Read
DELETE [Shared] Notifications - Delete Notification
DELETE [Shared] Notifications - Delete All
```

#### 3.3 Notification Preferences
```
GET    [Shared] Notifications - Get Preferences
PUT    [Shared] Notifications - Update Preferences
POST   [Shared] Notifications - Reset Preferences
```

### 🔍 4. Search

#### 4.1 Global Search
```
GET    [Shared] Search - Global Search
GET    [Shared] Search - Autocomplete
GET    [Shared] Search - Search Courses
GET    [Shared] Search - Search Users
GET    [Shared] Search - Search Content
```

#### 4.2 Search History
```
GET    [Shared] Search - Get Search History
DELETE [Shared] Search - Clear Search History
DELETE [Shared] Search - Delete Search Item
```

### 📁 5. Media Upload

#### 5.1 Upload
```
POST   [Shared] Media - Upload Image
POST   [Shared] Media - Upload Document
POST   [Shared] Media - Upload Video
POST   [Shared] Media - Upload Audio
POST   [Shared] Media - Bulk Upload
```

#### 5.2 Media Management
```
GET    [Shared] Media - List My Media
GET    [Shared] Media - Get Media Detail
DELETE [Shared] Media - Delete Media
GET    [Shared] Media - Get Media URL
POST   [Shared] Media - Generate Signed URL
```

### ⚙️ 6. System Settings
```
GET    [Shared] Settings - Get App Settings
GET    [Shared] Settings - Get Level Configs
GET    [Shared] Settings - Get XP Sources
GET    [Shared] Settings - Get Categories
GET    [Shared] Settings - Get Tags
```

### 📊 7. Master Data
```
GET    [Shared] Master Data - List Types
GET    [Shared] Master Data - Get Type Data
GET    [Shared] Master Data - Get Courses
GET    [Shared] Master Data - Get Students
GET    [Shared] Master Data - Get Instructors
```

---

## 📚 [REFERENCE] DOCUMENTATION

### 📖 API Overview
- Base URL & Versioning
- Rate Limiting
- Pagination
- Filtering & Sorting
- Including Relations

### 🔑 Authentication Guide
- Login Flow
- Token Management
- Token Refresh
- Logout Process
- Password Reset

### 📝 Request/Response Format
- Standard Request Format
- Standard Response Format
- Success Response Structure
- Error Response Structure
- Pagination Format
- Meta Information

### ⚠️ Error Codes
- HTTP Status Codes
- Custom Error Codes
- Error Messages
- Validation Errors
- Troubleshooting Guide

### 🚀 Quick Start Guide
- Setup Environment
- First API Call
- Common Workflows
- Best Practices
- Testing Tips

---

## 📊 SUMMARY STATISTICS

### Total Endpoints by Platform
- **Mobile Student App**: ~80 endpoints
- **Admin Dashboard**: ~200 endpointsperiksa #Auth apakah sudah ada logika login untuk per status misal pending, aktif, banned, inactive

Dan aktivitas lainnya berdasarkan user statusnya #Modules 
- **Shared Common APIs**: ~50 endpoints
- **Total**: ~420 endpoints

### Endpoints by Method
- **GET**: ~250 endpoints (60%)
- **POST**: ~100 endpoints (24%)
- **PUT/PATCH**: ~50 endpoints (12%)
- **DELETE**: ~20 endpoints (4%)

### Endpoints by Module
- **Learning & Content**: ~120 endpoints
- **User Management**: ~60 endpoints
- **Gamification**: ~50 endpoints
- **Grading**: ~30 endpoints
- **Forums**: ~25 endpoints
- **Reports & Analytics**: ~40 endpoints
- **System & Settings**: ~45 endpoints
- **Others**: ~50 endpoints

---

**Catatan**: Struktur ini mencakup semua endpoint yang ada di backend. Setiap endpoint akan didokumentasikan lengkap dengan parameters, request body, dan response examples di collection Postman.
