# Barangay Health Monitoring System - Standalone Desktop App Setup

This guide explains how to wrap the health monitoring system and run it as a standalone offline desktop application (`.exe`) on Windows using **PHP Desktop Chrome** and **SQLite**.

---

## Part 1: How the Desktop App Works

Instead of running Apache/MySQL through XAMPP, the desktop app packages:
1. **Chromium (Chrome)**: Renders the interface in a dedicated, custom desktop window (no URL bar, looks like a real desktop program).
2. **Mongoose Web Server**: Runs a lightweight, local web server in the background.
3. **PHP CGI**: Runs your PHP files.
4. **SQLite Database**: Uses a single-file database (`database/barangay_health.sqlite`) inside the folder—no database installation required.

---

## Part 2: Step-by-Step Desktop Integration

### Step 1: Initialize the SQLite Database
Open your PowerShell or Command Prompt, navigate to your project directory, and run the SQLite initializer:
```bash
php database/init_sqlite.php
```
This script will automatically generate the database file: `database/barangay_health.sqlite` and seed it with system accounts (e.g., login credentials like `admin` / `admin123`) so you can run the app offline immediately.

### Step 2: Download PHP Desktop Chrome
1. Download the pre-built **PHP Desktop Chrome (v57.0 with PHP 7.1 or higher)** for Windows:
   * [Official GitHub Releases](https://github.com/cztomczak/phpdesktop/releases) (Look for `phpdesktop-chrome-57.0-rc-php-7.1.3` or later zip files).
2. Extract the downloaded zip file anywhere on your computer.

### Step 3: Copy Your Project Files
1. Open the extracted PHP Desktop directory. You will see a folder named `www/`.
2. Delete everything inside the `www/` folder.
3. Copy **all files and folders** from your `barangay-health-system` project folder and paste them directly into that `www/` folder.

### Step 4: Configure PHP Desktop Settings
1. In the root directory of the extracted PHP Desktop folder, locate and open `settings.json` in a text editor.
2. Modify the configuration to match the following key parameters:
   ```json
   {
       "application": {
           "single_instance_guid": "",
           "dpi_aware": true
       },
       "debugging": {
           "show_console": true,
           "subprocess_show_console": false,
           "log_level": "DEBUG4",
           "log_file": "debug.log"
       },
       "main_window": {
           "title": "Barangay Lika Health Monitoring System",
           "icon": "",
           "default_size": [1280, 800],
           "minimum_size": [800, 600],
           "maximum_size": [0, 0],
           "disable_maximize_button": false,
           "center_on_screen": true,
           "start_maximized": false,
           "start_fullscreen": false,
           "always_on_top": false,
           "minimize_to_tray": false,
           "minimize_to_tray_message": "Minimized to tray",
           "start_html_in_shell": "",
           "start_page": "index.php"
       },
       "popup_window": {
           "icon": "",
           "fixed_title": "",
           "center_relative_to_parent": true,
           "default_size": [800, 600]
       },
       "web_server": {
           "listen_on": ["127.0.0.1", 0],
           "www_directory": "c:/xampp/htdocs/barangay-health-system",
           "index_files": ["index.php", "index.html"],
           "cgi_interpreter": "php/php-cgi.exe",
           "cgi_extensions": ["php"],
           "cgi_temp_dir": "",
           "404_handler": "/pretty-urls.php",
           "hide_files": []
       },
       "chrome": {
           "log_file": "debug.log",
           "log_severity": "default",
           "cache_path": "webcache",
           "external_drag": true,
           "external_navigation": true,
           "reload_page_F5": true,
           "devtools_F12": true,
           "remote_debugging_port": 0,
           "runtime_style": "chrome",
           "command_line_switches": {"disable-gpu": ""},
           "enable_downloads": true,
           "context_menu": {
               "enable_menu": true,
               "navigation": true,
               "print": true,
               "view_source": true,
               "open_in_external_browser": true,
               "devtools": true
           }
       }
   }
   ```
3. Save the file.

### Step 5: Enable SQLite in the Desktop App's PHP.ini
1. Inside the PHP Desktop directory, open the `php/` folder.
2. Locate `php.ini` and open it in a text editor.
3. Ensure the SQLite extension is enabled. Look for this line:
   ```ini
   extension=php_sqlite3.dll
   extension=php_pdo_sqlite.dll
   ```
   *(If there is a semicolon `;` in front of them, delete it to enable them. If the lines do not exist, add them under the `[ExtensionList]` section).*
4. Save the file.

### Step 6: Test the Desktop App
1. Go back to the root PHP Desktop folder.
2. Double-click **`phpdesktop-chrome.exe`**.
3. The Barangay Health Monitoring System will boot instantly in its own standalone window!
4. Log in using:
   * **Username**: `worker`
   * **Password**: `worker123`
   * Or **Username**: `admin` | **Password**: `admin123`

---

## Switching Database Modes
You can toggle between local XAMPP MySQL and SQLite modes easily by opening `app/config/config.php` and updating the `DB_DRIVER` constant:

* **MySQL Mode** (Web/Online/XAMPP):
  ```php
  define('DB_DRIVER', 'mysql');
  ```
* **SQLite Mode** (Standalone Desktop/Offline):
  ```php
  define('DB_DRIVER', 'sqlite');
  ```
