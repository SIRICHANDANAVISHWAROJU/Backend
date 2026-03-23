# Slim 4 — User Management REST API

A REST API built with Slim Framework 4 that demonstrates CRUD operations on a User resource with password hashing.

---

## Requirements

| Tool | Version |
|---|---|
| PHP | 8.1+ |
| Composer | 2.x |
| XAMPP | Any (for PHP + MySQL on Windows) |
| Postman | Any (for API testing) |

---

## Installation

### Step 1 — Install Composer Globally

Download and run the installer: https://getcomposer.org/Composer-Setup.exe

When prompted, point it to your PHP executable:

```
C:\xampp\php\php.exe
```

Verify:

```bash
composer --version
```

---

### Step 2 — Create the Project Folder

```bash
cd C:\xampp\htdocs
mkdir siri_crud_assign
cd siri_crud_assign
```

Install Slim Framework via Composer:

```bash
composer require slim/slim slim/psr7 php-di/php-di
```

This creates the vendor/ folder automatically with all required packages.

---

### Step 3 — Set Up the Database

Open phpMyAdmin at http://localhost/phpmyadmin and run:

```sql
CREATE DATABASE slim_crud_db;

USE slim_crud_db;

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(255) NOT NULL UNIQUE,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### Step 4 — Create Project Files

Create the following folder and file structure inside siri_crud_assign/:

```
siri_crud_assign/
├── public/
│   ├── index.php
│   └── .htaccess
├── vendor/
├── .htaccess
├── composer.json
└── README.md
```

---

### Step 5 — Configure Database Credentials

Open `public/index.php` and update the $pdo connection block:

```php
$pdo = new PDO(
    'mysql:host=localhost;dbname=slim_crud_db;charset=utf8',
    'root',   // your MySQL username
    '',       // your MySQL password (blank by default in XAMPP)
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

---

### Step 6 — Configure .htaccess Files

Root .htaccess (`siri_crud_assign/.htaccess`):

```apache
RewriteEngine On
RewriteBase /siri_crud_assign/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ public/index.php [QSA,L]
```

Public .htaccess (`siri_crud_assign/public/.htaccess`):

```apache
RewriteEngine On
RewriteBase /siri_crud_assign/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

---

### Step 7 — Set Base Path and Start the Server

In `public/index.php`, add the base path after creating the app:

```php
$app = AppFactory::create();
$app->setBasePath('/siri_crud_assign');
```

Open XAMPP Control Panel and start Apache and MySQL.

Your API is live at: **http://localhost/siri_crud_assign/users**

---

## API Endpoints

| Method | Endpoint | Action |
|---|---|---|
| GET | `/users` | List all users |
| POST | `/users` | Create a new user |
| GET | `/users/{id}` | Get one user |
| PUT | `/users/{id}` | Full update |
| DELETE | `/users/{id}` | Delete a user |

---

## Password Hashing

Passwords are hashed using bcrypt via PHP's built-in `password_hash()` function. Plain text passwords are never stored in the database.

The password column is also deliberately excluded from all GET responses so it is never exposed through the API.

---