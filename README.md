# 🎓 College Management System

A web-based **College Management System** developed to simplify and organize the academic and administrative activities of a college.

The system provides separate dashboards for **Admin, Faculty, and Student**, allowing each user to access features according to their role.

---

## 📌 About the Project

The College Management System is designed to manage important college information through a centralized web application.

It helps manage students, faculty members, departments, subjects, attendance, examination results, fees, timetables, notices, and college events.

The main goal of the project is to reduce manual work and provide an organized and user-friendly system for college administration.

---

## 👥 User Roles

The system contains three main user roles:

### 👨‍💼 Admin

Admin has overall control of the system.

- Dashboard with statistics
- Manage Students
- Manage Faculty
- Manage Departments
- Manage Subjects
- Manage Attendance
- Manage Results
- Manage Fees
- Manage Timetable
- Manage Notices
- Manage Events
- View and manage profile

### 👨‍🏫 Faculty

Faculty members can manage academic activities.

- Faculty Dashboard
- View My Timetable
- Manage Student Attendance
- Enter and Update Results
- View Notices
- View Profile
- Change Password

### 🎓 Student

Students can access their academic information.

- Student Dashboard
- View Timetable
- View Attendance
- View Results
- View Fee Details
- View Fee History
- View Notices
- View Upcoming Classes
- View Profile
- Change Password

---

## 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| HTML5 | Web page structure |
| CSS3 | Custom styling |
| Bootstrap 5 | Responsive user interface |
| Bootstrap Icons | Interface icons |
| JavaScript | Frontend interactions |
| PHP | Server-side processing |
| MySQL | Database management |
| phpMyAdmin | Database administration |
| XAMPP | Local development environment |
| Apache | Local web server |
| Git | Version control |
| GitHub | Remote repository |

---

## 🗂️ Main Modules

The project includes the following modules:

- Authentication
- Admin Management
- Student Management
- Faculty Management
- Department Management
- Subject Management
- Attendance Management
- Result Management
- Fee Management
- Timetable Management
- Notice Management
- Event Management
- Profile Management

---

## 🏗️ System Architecture

The application follows a web-based client-server architecture.

```text
Admin / Faculty / Student
          ↓
      Web Browser
          ↓
HTML + CSS + Bootstrap + JavaScript
          ↓
         PHP
          ↓
    MySQL Database
```

---

## 🗄️ Database

The project uses a MySQL database named:

```text
college_management
```

### Main Tables

```text
users
students
faculty
departments
subjects
attendance
results
fees
timetable
notices
events
```

The database stores user accounts, academic records, attendance, examination results, fee information, class schedules, notices, and events.

---

## 📁 Project Structure

```text
college-management/
│
├── admin/
│   ├── dashboard.php
│   ├── students.php
│   ├── faculty.php
│   ├── departments.php
│   ├── subjects.php
│   ├── attendance.php
│   ├── results.php
│   ├── fees.php
│   ├── timetable.php
│   ├── notices.php
│   └── events.php
│
├── faculty/
│   ├── dashboard.php
│   ├── timetable.php
│   ├── attendance.php
│   ├── results.php
│   ├── notices.php
│   └── profile.php
│
├── student/
│   ├── dashboard.php
│   ├── timetable.php
│   ├── attendance.php
│   ├── results.php
│   ├── fees.php
│   ├── notices.php
│   └── profile.php
│
├── assets/
│   ├── css/
│   └── js/
│
├── config/
│   └── database.php
│
├── includes/
│
├── index.php
├── login.php
├── logout.php
└── README.md
```

---

## ⚙️ Installation & Setup

### 1. Install XAMPP

Install XAMPP and start:

- Apache
- MySQL

### 2. Clone the Repository

```bash
git clone <your-github-repository-url>
```

Move the project inside:

```text
C:\xampp\htdocs\
```

The final folder should look like:

```text
C:\xampp\htdocs\college-management\
```

### 3. Create Database

Open phpMyAdmin and create a database:

```sql
CREATE DATABASE college_management;
```

Import the project's SQL database file if available.

### 4. Configure Database Connection

Create/configure:

```text
config/database.php
```

Example:

```php
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "college_management";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
```

> Database credentials may need to be changed according to the local XAMPP/MySQL configuration.

### 5. Run the Project

Open the browser and visit:

```text
http://localhost/college-management/
```

---

## 🔐 Role-Based Access

After successful login, users are redirected according to their role.

```text
Login
  ↓
Authentication
  ↓
Role Verification
  ↓
┌─────────┬─────────┬─────────┐
│  Admin  │ Faculty │ Student │
└─────────┴─────────┴─────────┘
     ↓         ↓         ↓
 Respective Role Dashboard
```

---

## ✨ Key Features

- Modern responsive dashboard
- Role-based login system
- Separate Admin, Faculty and Student panels
- Student and Faculty record management
- Attendance tracking
- Examination result management
- Fee tracking
- Timetable management
- Notice management
- College event management
- User profile pages
- Search and filtering
- Responsive Bootstrap interface
- MySQL database integration

---

## 🧪 Testing

The project has been tested locally using **XAMPP, Apache, PHP, MySQL, phpMyAdmin, and a web browser**.

Major functional areas checked during development include:

- Login and role redirection
- Student management
- Faculty management
- Department and subject management
- Attendance
- Results
- Fees
- Timetable
- Notices
- Events
- Profile pages
- Logout and session handling

---

## 🚀 Future Enhancements

Future versions of the project may include:

- Online payment gateway integration
- QR or biometric attendance
- Email and SMS notifications
- Advanced reports and analytics
- PDF/Excel report export
- Cloud deployment
- Mobile application
- Automated database backup
- More detailed role permissions

---

## 🔄 Version Control

This project uses **Git** for version control and **GitHub** for remote source-code management.

Basic development workflow:

```bash
git status
git add .
git commit -m "Update project"
git push origin main
```

---

## 📸 Project Screenshots

Screenshots of the following interfaces can be added here:

- Login Page
- Admin Dashboard
- Faculty Dashboard
- Student Dashboard
- Attendance Management
- Results Management
- Fees Management
- Timetable
- Notices & Events

---

## 📚 Project Purpose

This project was developed for **academic and educational purposes** to demonstrate the implementation of a web-based college administration system using PHP and MySQL.

---

## 👨‍💻 Developer

**College Management System**

Developed as an academic web development project.

**Technologies:** PHP • MySQL • HTML • CSS • Bootstrap • JavaScript

---

## 📄 License

This project is intended primarily for educational and academic use.

---

⭐ **If you find this project useful, consider giving the repository a star!**
