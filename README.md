# OwlLib - Admin Portal

A comprehensive web-based library management system built with Laravel, designed specifically for School Library's administrators to efficiently manage library operations, track user activities, and generate insightful analytics. This system serves as the administrative backbone for the school's library services.

**Live Demo**: [https://library-admin.bps.edu.ph](https://library-admin.bps.edu.ph)

---

## Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Automated Deployment (CI/CD)](#-automated-deployment-cicd)
- [System Architecture](#-system-architecture)
- [Tech Stack](#️-tech-stack)
- [Usage Guide](#-usage-guide)
- [License](#-license)

---

## Overview

The OwlLib Admin Portal is a capstone project developed to modernize and streamline library operations at School's library. The system provides a centralized platform for:

- **Library Staff**: To manage daily operations including book lending, returns, and user management.
- **Administrators**: To monitor library usage, generate reports, and configure system settings.
- **Data Analysis**: To track trends in book borrowing, user visits, and resource utilization.

The system supports three primary user types:
1. **Students** (Grade 7-12) - Primary library users with borrowing privileges.
2. **Employees** (Faculty & Staff) - School personnel with extended borrowing privileges.
3. **Visitors** - External guests with limited library access.

---

## Features

### Analytics Dashboard
- **Real-time User Monitoring**: Track currently timed-in users with one-click timeout functionality.
- **Monthly/Yearly Logs**: Visualize library visits over time with interactive line charts.
- **Transaction History**: Monitor borrowed, returned, and reserved books with bar charts.
- **User Registration Growth**: Track cumulative user growth with toggleable monthly/yearly views.
- **Top Performers**: View most visited students and top borrowers per grade level.
- **Book Analytics**: Identify most borrowed books and popular categories.
- **Yearly Book Acquisition**: Track library collection growth over time.

### Book & Category Management
- **CRUD Operations**: Complete control over book records with rich attributes.
- **Category Autocomplete**: Category organization supported by autocomplete search in the [CategoryMaintenanceController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Maintenance/CategoryMaintenanceController.php).
- **Inventory & Barcodes**: Real-time quantity tracking, plus dynamic exporting of barcodes and call numbers handled by the [BookMaintenanceController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Maintenance/BookMaintenanceController.php).
- **Physical to Digital**: Upload digital versions of library materials alongside physical inventory.

### Admin & Privilege Management
- **Admin Promotions**: Promote existing database users into administrators using [AdminMaintenanceController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Maintenance/AdminMaintenanceController.php) with checks that prevent promoting students to Super Admin.
- **Roles & Permissions Management**: Manage customizable administrative access roles using Spatie Permissions and the roles view logic.
- **Seeder Automation**: Keep permissions synchronized in deployment automatically using [SyncPermissionsSeeder](file:///c:/Academics/Capstone/BPS-LMS-Admin/database/seeders/SyncPermissionsSeeder.php).
- **Automated Notifications**: Promoted or updated administrators receive immediate email alerts when roles change.

### Library Website & Media Gallery
- **Announcements Engine**: Post updates directly to the school library portal.
- **Multi-Level Media Gallery**: Manage library visual media using the [GalleryMaintenanceController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Maintenance/GalleryMaintenanceController.php). It implements:
  - **Photo Albums**: Creating and ordering photo categories.
  - **Video Albums & Folders**: Creating nested folders of video items.
  - **Video Items**: Hosting and embedding external video URLs with customized providers, thumbnails, and descriptions.
- **Binary Thumbnail Storage**: Image thumbnails are read, base64-encoded, and saved locally inside database `LONGBLOB` columns for cached rendering.

### Transaction & Circulation Management
- **Borrow & Return System**: Checkout flow with due dates, dynamic overdue monitoring, and return condition assessments.
- **Circulation Records**: Complete history of circulations and audits.
- **Reservation Extensions**: Approving or rejecting book reservation extension requests with dashboard tracking alerts.

### Penalty System
- **Dynamic Calculation Rules**: Custom rules based on overdue duration.
- **Tracking & History**: Record payments and check penalty balances.
- **Penalty Reports**: Compile financial audits of paid and outstanding library fees.

### Automated Database Backup System
- **Queue-Based Backups**: Runs asynchronously via [CreateBackupJob](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Jobs/CreateBackupJob.php) to avoid locking UI requests during large SQL dumps.
- **High-Security Downloads**: Downloading a backup triggers an on-demand workflow in [BackupController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Backup/BackupController.php):
  1. Compiles backup files into a temporary ZIP folder.
  2. Encrypts the ZIP archive with high-grade AES-256 encryption.
  3. Automatically generates a unique 12-character password.
  4. Mails the password to the logged-in administrator's email for secure delivery.
  5. Streams the download and immediately deletes the temporary encrypted file to save disk space.
- **UI Progress Status**: Front-end polls the `status` endpoint to track if a backup job is in progress.

### High-Performance Bulk Imports
- **Background Jobs**: Uses Laravel's queue driver to process import files in the background, keeping web requests extremely fast.
- **Queued Import Controllers**:
  - [StudentImportController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Import/StudentImportController.php) dispatching [ProcessStudentImport](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Jobs/ProcessStudentImport.php).
  - [FacultyStaffImportController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Import/FacultyStaffImportController.php) dispatching [ProcessEmployeeImport](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Jobs/ProcessEmployeeImport.php).
  - [MaterialImportController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Import/MaterialImportController.php) dispatching [ProcessMaterialImport](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Jobs/ProcessMaterialImport.php).
  - [UserImageImportController](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Controllers/Import/UserImageImportController.php) dispatching [ProcessUserImageImport](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Jobs/ProcessUserImageImport.php).
- **Import Validation & Cleanup**: Validates header rows, handles size limits (e.g., maximum 5MB for images), performs database transaction-based chunking to ensure stability, triggers garbage collection, and automatically cleans up temporary directories.
- **Progress Tracking API**: Provides live progress tracking metrics (`processed_rows`, `updated_count`, `new_count`, and errors) dynamically fetched by the UI.

### Security & Access Control
- **Role-Based Access Control (RBAC)**: Fine-grained permissions using Spatie Permissions.
- **Two-Factor Authentication (2FA)**: Email verification code required upon logging in.
- **No-Cache Middleware**: The global [PreventBackHistory](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Http/Middleware/PreventBackHistory.php) middleware strips browser history caching for all authenticated web requests. This prevents back-button session hijacking after logout.
- **Audit Trail Logging**: Database logging for administrative actions.

---

## Automated Deployment (CI/CD)

The project includes pre-configured GitHub Actions workflows located in [.github/workflows/](file:///c:/Academics/Capstone/BPS-LMS-Admin/.github/workflows):
- [sncs-deploy-prod.yml](file:///c:/Academics/Capstone/BPS-LMS-Admin/.github/workflows/sncs-deploy-prod.yml): Deploys the production branch to the Sto. Niño Catholic School server.
- [sncs-deply-test.yml](file:///c:/Academics/Capstone/BPS-LMS-Admin/.github/workflows/sncs-deply-test.yml): Deploys the test branch to the SNCS test server.

### Deployment Workflow Details
1. **PHP Setup**: Deploys on PHP 8.5 with optimized, production-level Composer dependencies.
2. **Frontend Compilation**: Builds and bundles assets using Node.js and Vite (`npm run build`).
3. **Rsync Deployment**: Safely syncs compiled repository files via SSH, ignoring local configurations, caches, and storage.
4. **Artisan Optimization**: Re-creates frameworks folders, clears configuration caches, performs database migrations (`php artisan migrate --force`), and executes [SyncPermissionsSeeder](file:///c:/Academics/Capstone/BPS-LMS-Admin/database/seeders/SyncPermissionsSeeder.php) to sync fresh permissions.

---

## System Architecture

```
BPS-LMS-Admin/
├── .github/
│   └── workflows/              # GitHub Actions deployment scripts
├── app/
│   ├── Console/Commands/       # Custom Artisan commands
│   ├── Enum/                   # Permission & Role enumerations
│   ├── Helpers/                # Utility helper classes
│   ├── Http/
│   │   ├── Controllers/        # HTTP controllers
│   │   │   ├── Admin/          # Auth controllers for admins
│   │   │   ├── Analytics/      # Dashboard and reporting data
│   │   │   ├── Backup/         # Automated database backups
│   │   │   ├── Import/         # Background bulk CSV and zip imports
│   │   │   ├── Maintenance/    # CRUD controllers for system configurations
│   │   │   └── Report/         # PDF and Excel report compilation
│   │   ├── Middleware/         # Custom request filters
│   │   └── Requests/           # Form validation classes
│   ├── Jobs/                   # Asynchronous background tasks
│   ├── Listeners/              # Event listeners
│   ├── Mail/                   # Mail notifications
│   ├── Models/                 # Eloquent database models
│   ├── Notifications/          # Notification scripts
│   └── Providers/              # Core Service Providers
├── bootstrap/
│   └── app.php                 # Middleware registration and routing
├── config/                     # Configuration files
├── database/
│   ├── factories/              # Model data factories
│   ├── migrations/             # Database table structures
│   └── seeders/                # Default database seeding files
├── public/                     # Public assets
├── resources/
│   ├── css/                    # Custom stylesheets
│   ├── js/                     # Client scripts
│   ├── sass/                   # SASS configurations
│   └── views/                  # Blade templates
├── routes/                     # Web routes mapping
├── storage/                    # Output logs, backup zip folders, and uploads
└── tests/                      # Testing directory
```

### Key Models

| Model | Description | Link |
| :--- | :--- | :--- |
| `User` | Core user model (RFID, profile images, personal details) | [User.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/User.php) |
| `PhotoAlbum` | Storage for library photo album categorizations | [PhotoAlbum.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/PhotoAlbum.php) |
| `VideoAlbum` | Categorizes and groups media video folders | [VideoAlbum.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/VideoAlbum.php) |
| `VideoFolder` | Directory hierarchy under video albums | [VideoFolder.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/VideoFolder.php) |
| `VideoItem` | Video content urls (YouTube, Vimeo, etc.) and tags | [VideoItem.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/VideoItem.php) |
| `Transaction` | Circulation logs for books borrowed/returned/reserved | [Transaction.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/Transaction.php) |
| `Penalty` | Outstanding fines and payment history records | [Penalty.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/Penalty.php) |
| `Inventory` | Book inventories | [Inventory.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/Inventory.php) |
| `Log` | RFID time-in / time-out logs | [Log.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/Log.php) |
| `AuditTrail` | User action tracking database entries | [AuditTrail.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/AuditTrail.php) |
| `StudentDetail` | Student metadata | [StudentDetail.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/StudentDetail.php) |
| `EmployeeDetail`| Employee metadata | [EmployeeDetail.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/EmployeeDetail.php) |
| `VisitorDetail` | Visitor metadata | [VisitorDetail.php](file:///c:/Academics/Capstone/BPS-LMS-Admin/app/Models/VisitorDetail.php) |

---

## Tech Stack

### Frameworks & Base Tech
- **Laravel 11**: Upgraded to Laravel 11.31 utilizing lightweight middleware setups inside `bootstrap/app.php`.
- **PHP 8.2+**: Built to support modern PHP properties and functions.
- **MySQL**: Relational database storage.
- **Livewire 3.6**: Interactive frontend components with zero page reloads.

### Frontend
- **Blade Templates**: Traditional Laravel rendering.
- **Tailwind CSS**: Utility-first styling framework.
- **Flowbite**: Pre-built dashboard components.
- **Chart.js**: Client-side data analytics graphs.
- **Alpine.js**: Ultra-lightweight reactive client interactions.

### Key Packages
- **spatie/laravel-permission (v6.13)**: Backend RBAC system.
- **spatie/laravel-backup (v9.3)**: Automating local and remote SQL file backups.
- **dompdf/dompdf (v3.1)**: Compiles HTML layouts into downloadable PDFs.
- **phpoffice/phpspreadsheet (v4.2)**: Handles parsing bulk Excel imports and exports.
- **milon/barcode (v12.0)**: Automatic barcode generation.

---

## Usage Guide

### Managing Admins & Permissions
1. Go to **Maintenance** > **Admin Management** > **Admins**.
2. Click **Add Admin** to search existing users. Promotion is done by entering their RFID card.
3. Select an administrative role (e.g., Administrator, Library Staff). *Note: Students cannot be assigned the Super Admin role.*
4. The user will automatically receive a role notification email.

### Running & Downloading Backups
1. Go to **Backup** > **Backups**.
2. Click **Create Backup**. The job runs in the background. The list updates once processing finishes.
3. To download, click **Download**.
4. Check your administrator email inbox for a secure password.
5. Extract the downloaded ZIP archive using the mailed password.

### Updating the Media Gallery
1. Navigate to **Maintenance** > **Library Website** > **Gallery**.
2. **Photo Tab**: Create photo albums and upload high-res JPEG/PNG thumbnails.
3. **Video Tab**:
   - Add a new **Video Album**.
   - Open the album and create **Video Folders** to group items.
   - Inside folders, click **Create Video Item** to paste links from providers like YouTube or Vimeo.

### Running Bulk Imports
1. Navigate to the **Import** sidebar section.
2. Select the import category: **Students**, **Faculty & Staff**, **Library Materials**, or **User Images**.
3. Download the specific Excel template.
4. Fill in data fields, select the file, and click **Upload**.
5. Keep the browser page open. The UI will show a real-time progress bar powered by background queue updates.

---

## License

This project is developed as a capstone project for educational purposes.

---

## Authors

- **OwlQuery Team** - Capstone Project

---

## Acknowledgments

- Polytechnic University of the Philippines for the opportunity of this capstone.
- Bicutan Parochial School, Inc. and Sto. Niño Catholic School, Inc. for the support in this project.
- Laravel community for excellent documentation.
