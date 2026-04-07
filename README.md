# 📚 Library Management System - Admin Portal

A comprehensive web-based library management system built with Laravel, designed specifically for School Library's administrators to efficiently manage library operations, track user activities, and generate insightful analytics. This system serves as the administrative backbone for the school's library services.

🌐 **Live Demo**: [https://library-admin.bps.edu.ph](https://library-admin.bps.edu.ph)

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#️-tech-stack)
- [Usage Guide](#-usage-guide)

---

## 🔍 Overview

The Library Management System (LMS) Admin Portal is a capstone project developed to modernize and streamline library operations at Bicutan Parochial School, Inc. The system provides a centralized platform for:

- **Library Staff**: To manage daily operations including book lending, returns, and user management
- **Administrators**: To monitor library usage, generate reports, and configure system settings
- **Data Analysis**: To track trends in book borrowing, user visits, and resource utilization

The system supports three primary user types:
1. **Students** (Grade 7-12) - Primary library users with borrowing privileges
2. **Employees** (Faculty & Staff) - School personnel with extended borrowing privileges
3. **Visitors** - External guests with limited library access

---

## 🚀 Features

### 📊 Analytics Dashboard
- **Real-time User Monitoring**: Track currently timed-in users with one-click timeout functionality
- **Monthly/Yearly Logs**: Visualize library visits over time with interactive line charts
- **Transaction History**: Monitor borrowed, returned, and reserved books with bar charts
- **User Registration Growth**: Track cumulative user growth with toggleable monthly/yearly views
- **Top Performers**: View most visited students and top borrowers per grade level
- **Book Analytics**: Identify most borrowed books and popular categories
- **Yearly Book Acquisition**: Track library collection growth over time

### 📖 Book Management
- Complete CRUD operations for book records
- Category-based organization
- Inventory tracking with quantity management
- Barcode generation and scanning support
- Details lookup and validation
- Digital resource uploads
- Bulk import via Excel

### 👥 User Management
- **Student Management**: Track grade level, section, and academic details
- **Employee Management**: Manage faculty and staff records
- **Visitor Registration**: Handle external guest registrations
- **Staging Users**: Review and approve pending registrations
- **User Groups**: Organize users into custom groups

### 📅 Transaction Management
- **Borrowing System**: Process book checkouts with due date tracking
- **Return Processing**: Handle book returns with condition assessment
- **Reservation System**: Allow users to reserve books in advance
- **Overdue Tracking**: Automatic detection of overdue items

### 💰 Penalty System
- Configurable penalty rules based on overdue duration
- Automatic penalty calculation
- Payment tracking and history
- Penalty reports generation

### 🔐 Security & Access Control
- Role-based access control using Spatie Permissions
- Granular permission management
- Two-factor authentication (2FA) via email
- Session management
- Audit trail logging for all actions

### 📧 Notification System
- Email notifications for:
  - Account registration
  - Password changes
  - Reservation confirmations
  - Backup completion alerts
  - Role assignment notifications

### 📥 Import and Reports
- Excel import for bulk book additions
- Excel import for user registrations
- PDF & Excel report generation

### ⚙️ System Configuration
- Customizable UI settings
- System-wide settings management
- Backup scheduling and management
- Database maintenance tools

---

## 🏗️ System Architecture

```
BPS-LMS-Admin/
├── app/
│   ├── Console/Commands/       # Custom Artisan commands
│   ├── Enum/                   # Permission & Role enumerations
│   ├── Helpers/                # Utility helper classes
│   ├── Http/
│   │   ├── Controllers/        # Request handlers
│   │   │   ├── Analytics/      # Dashboard analytics controllers
│   │   │   └── ...
│   │   ├── Middleware/         # Request middleware
│   │   └── Requests/           # Form request validation
│   ├── Listeners/              # Event listeners
│   ├── Mail/                   # Mailable classes
│   ├── Models/                 # Eloquent models
│   ├── Notifications/          # Notification classes
│   ├── Providers/              # Service providers
│   └── View/Components/        # Blade components
├── config/                     # Configuration files
├── database/
│   ├── factories/              # Model factories
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── public/                     # Publicly accessible files
├── resources/
│   ├── css/                    # Stylesheets
│   ├── js/                     # JavaScript files
│   ├── sass/                   # SASS files
│   └── views/                  # Blade templates
├── routes/                     # Route definitions
├── storage/                    # File storage
└── tests/                      # Test files
```

### Key Models

| Model | Description |
|-------|-------------|
| `User` | Core user model with authentication |
| `Admin` | Administrator accounts |
| `Book` | Library book records |
| `Category` | Book categories |
| `Inventory` | Book inventory tracking |
| `Transaction` | Borrowing/returning records |
| `Penalty` | User penalties |
| `PenaltyRule` | Penalty configuration |
| `Log` | Library visit logs (time-in/out) |
| `AuditTrail` | System activity logs |
| `Notification` | User notifications |
| `StudentDetail` | Student-specific information |
| `EmployeeDetail` | Employee-specific information |
| `VisitorDetail` | Visitor-specific information |

---

## 🛠️ Tech Stack

### Backend
| Technology | Purpose |
|------------|---------|
| **Laravel 10+** | PHP Framework |
| **MySQL** | Relational Database |
| **Spatie Permission** | Role & Permission Management |
| **Laravel Breeze** | Authentication Scaffolding |

### Frontend
| Technology | Purpose |
|------------|---------|
| **Blade** | Templating Engine |
| **Tailwind CSS** | Utility-first CSS Framework |
| **Flowbite** | Tailwind UI Components |
| **Chart.js** | Data Visualization |
| **jQuery** | DOM Manipulation |
| **Alpine.js** | Lightweight JS Framework |

### Additional Packages
| Package | Purpose |
|---------|---------|
| **DOMPDF** | PDF Generation |
| **PhpSpreadsheet** | Excel Import/Export |
| **Livewire** | Dynamic Components |
| **Spatie Backup** | Database Backups |
| **Milon Barcode** | Barcode Generation |

---

## 📖 Usage Guide

### Dashboard Overview

1. **Current Users Card**: Displays real-time count of users currently in the library
   - Click "Timeout All Users" to automatically log out all users (useful at closing time)

2. **Monthly Logs Chart**: Line graph showing library visits over the past 12 months

3. **Transaction History**: Bar chart displaying borrowed, returned, and reserved books

4. **User Registration Growth**: Toggle between monthly and yearly views to track user growth

5. **Top Students**: View most active students per grade level with date range filtering

### Managing Books

1. Navigate to **Books** in the sidebar
2. Click **Add Book** to create a new record
3. Fill in book details (title, author, category, quantity)
4. Use **Import** for bulk additions via Excel

### Processing Transactions

1. Go to **Transactions** > **Borrow**
2. Scan or enter user barcode
3. Scan or enter book barcode
4. Confirm the transaction

### Generating Reports

1. Navigate to **Reports**
2. Select report type (Books, Users, Transactions)
3. Choose date range and filters
4. Export as PDF or Excel

---

## 📄 License

This project is developed as a capstone project for educational purposes.

---

## 👨‍💻 Authors

- **OwlQuery Team** - Capstone Project

---

## 🙏 Acknowledgments

- Polytechnic University of the Philippines for the opportunity of this capstone
- Bicutan Parochial School, Inc. for the support in this project
- Laravel community for excellent documentation
