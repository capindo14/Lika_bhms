# 🏥 Barangay Health Monitoring System (BHMS)

A web-based and desktop-compatible **Barangay Health Monitoring System** designed for local barangays (e.g., Barangay Lika) to manage, track, and report resident demographics, medical consultations, immunizations, and medicine distributions.
Built with a custom native PHP MVC architecture, the system provides high performance, offline usability, role-based access control, responsive design with dark mode support, and dynamic analytics.

---

## 🌟 Core Features

- **🔑 Secure Authentication & User Roles**:
  - **Admin**: Full access to dashboard, system configurations, user/staff accounts management, audit logs, and medical records.
  - **Health Worker & Staff**: Granular access to residents' profiling, consultations entry, immunization tracking, and medicine distribution.
- **👥 Resident Profiling & Demographics**:
  - CRUD operations (Create, Read, Update, Delete) for residents.
  - Interactive profile management with archive/restore features to keep records updated without permanent data loss.
- **🏡 Family & Household Registry**:
  - Houses grouping of residents into family units under designated "Family Heads".
  - Socio-environmental metrics tracking: water source, toilet types, child feeding methods, family planning status, and food production activities.
- **🩺 Clinical Consultations**:
  - Digital health checkups recording patient symptoms, diagnoses, and treatments.
  - Auto-linked medicine distribution directly from consultations.
- **💉 Immunization & Vaccination Tracking**:
  - Pediatric and general immunization recording (vaccine name, dose number, date given).
  - Next schedule calculation with automated indicators for upcoming, missed, or completed vaccinations.
- **📦 Medicine Inventory & Distribution**:
  - Real-time stock counts for medicines, vaccines, and supplies.
  - Alert system for low stock levels based on set reorder levels.
  - Dedicated distribution tracker log to document every dose dispensed.
- **📊 Dynamic Reports Generator**:
  - Generates summaries and exports reports for Residents, Consultations, Immunizations, and Medicine distributions.
- **📝 Audit Logging**:
  - Automatic system-wide logging of all user activities, capturing IP addresses, user agents, action details, and timestamps for data integrity.

---

## 🛠️ Technology Stack

### Backend

- **Core Language**: PHP (OOP-based, native custom MVC router & autoloader)
- **Database Engine**: MySQL (default) / SQLite (offline fallback support)
- **Database API**: PHP Data Objects (PDO) for secure, prepared statement execution
- **Config Loader**: Custom `.env` environment parser

### Frontend

- **CSS Framework**: Bootstrap 5 (with native light/dark mode toggling)
- **Icons**: Bootstrap Icons
- **Data Grids**: DataTables (with responsive extensions and search filters)
- **UI Components**: SweetAlert2 (modals & alerts), Flatpickr (advanced date-picking)
- **Fonts**: Google Fonts (Inter)

---

## 📁 Directory Structure

```text
barangay-health-system/
├── app/
│   ├── config/          # Configurations (autoloader, database, routing engine)
│   ├── controllers/     # Controller layer handling business logic
│   ├── helpers/         # Custom global helper functions (validation, XSS escaping, etc.)
│   ├── middleware/      # Authentication & route guards
│   ├── models/          # Database interaction models
│   └── services/        # Service classes (optional helper services)
├── database/
│   ├── schema.sql       # Database schema for MySQL
│   └── setup.php        # MySQL DB creator & seed data generator
├── logs/
│   └── error.log        # System error logs (git ignored)
├── public/
│   ├── css/             # Frontend stylesheets (style.css)
│   ├── js/              # Frontend scripting (app.js)
│   ├── index.php        # App entry point (front controller)
│   └── .htaccess        # Apache rewrite rules for clean URLs
├── routes/
│   └── web.php          # Route registers map to controller actions
├── views/               # PHP UI layout views (residents, family, reports, etc.)
│   └── layouts/         # Shared layouts (header, footer, navbar, sidebar)
├── .env                 # Environment secrets and database configuration (git ignored)
├── .gitignore           # Files and directories ignored by Git
├── index.php            # Root redirection to public/
└── README.md            # Project documentation
```

---

## 🚀 Setup & Installation (XAMPP / MySQL Web Mode)

### Prerequisites

- Install [XAMPP](https://www.apachefriends.org/) (PHP 7.4 or later recommended, Apache, and MySQL).
- Install Git on your system.

### Step 1: Clone the Repository

Clone this repository directly into your XAMPP web root directory (`C:\xampp\htdocs\`):

```bash
cd C:\xampp\htdocs\
git clone https://github.com/capindo14/Lika_bhms.git barangay-health-system
```

### Step 2: Configure Environment Settings

1. Inside the `barangay-health-system/` root directory, create a `.env` file (or verify if it exists) with the following database configurations:
   ```env
   DB_HOST=127.0.0.1
   DB_USER=root
   DB_PASS=""
   DB_NAME=barangay_health
   ```

### Step 3: Run Database Setup & Seeding

1. Start **Apache** and **MySQL** in your XAMPP Control Panel.
2. Open a terminal/command prompt, navigate to your project directory, and run the PHP setup script:
   ```bash
   php database/setup.php
   ```
   _This script will create the `barangay_health` database, set up the required table structures, and seed the database with mock records (residents, medicines, transactions)._

### Step 4: Access the System

Open your web browser and navigate to:

```text
http://localhost/barangay-health-system/
```

## The root page will automatically route you to the secure login screen.

## 🔑 Default Accounts (Seed Credentials)

Use these credentials to test the different user roles:
| Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` |
| **Health Worker** | `worker` | `worker123` |
| **Staff** | `staff` | `staff123` |

---

## 🖥️ Running as a Standalone Desktop Application (SQLite Mode)

If you want to package the system as an offline desktop app using **PHP Desktop Chrome** and **SQLite**, follow the steps outlined in the [DESKTOP_SETUP.md](file:///c:/xampp/htdocs/barangay-health-system/DESKTOP_SETUP.md) file.
