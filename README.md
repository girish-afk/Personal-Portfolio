# Girish Pawar — Premium Developer Portfolio & CMS

> **Tagline:** Building Digital Experiences. Solving Real Problems.  
> **Author:** Girish Pawar  
> **Tech Stack:** PHP 8.2+, MySQL 8.0 / MariaDB, HTML5, CSS3 (Modern Editorial / Swiss Design System), JavaScript ES6+

---

## 🌟 Overview

A bespoke, production-ready developer portfolio and private Content Management System (CMS) engineered specifically for **Girish Pawar**. Designed with modern editorial typography, restrained cyan accents, interactive case study drawers, automated spam-protected contact pipelines, and an owner-only private administration suite.

---

## 🚀 Key Highlights & Architecture

### 1. Dual Independent Experiences
- **Public Visitor Portfolio (`index.php`)**:
  - High-performance, read-only presentation.
  - Zero admin controls, zero edit buttons, zero leakage of administrative APIs or secrets.
  - Interactive typewriter headlines, dynamic skills filter, real-time project search, interactive case study drawers, certificate preview modal, dark/light theme persistence, downloadable resume, and anti-spam contact form.
- **Private Admin Dashboard (`/admin/login.php`)**:
  - Strictly protected by server-side PHP session verification, `password_hash` (Bcrypt), CSRF validation on all state-changing endpoints, and brute-force lockout protection (5 failed attempts = 15-minute freeze).
  - Complete owner-only CRUD for Profile, Projects, Case Studies, Skills, Categories, Experience claims, Internships, Education, Certifications, Social links, Inbound Inquiries, and Resume PDF.
  - Instant One-Click Database Backup (JSON snapshot export) and Disaster Recovery Restore.

### 2. Multi-Tier Resilient Persistence
- Connects to MySQL / MariaDB via **PHP Data Objects (PDO)** with parameterized prepared statements (100% SQL injection immune).
- Automatically initializes the database `girish_portfolio`, executes `core/schema.sql`, and seeds initial data.
- Built-in **Server-Side Resilient Persistence** (`data/portfolio_store.json`): If MySQL is temporarily offline (e.g. before launching XAMPP services), the portfolio remains completely operable and editable. When MySQL is online, a single click in Admin Settings syncs records directly to MySQL.

---

## 🔐 Initial Owner Credentials

| Field | Value | Notes |
|---|---|---|
| **Login Route** | `/admin/login.php` | Strictly private; public visitors are redirected here if unauthorized |
| **Default Username** | `admin` | Editable in Admin Settings |
| **Default Password** | `GirishPawar@2026!` | **Change this inside Admin Settings upon initial login** |
| **Email** | `girish.pawar.dev@gmail.com` | Receives contact inquiry notifications |

---

## 💻 Local Development & Setup

### Option A: Using PHP CLI Built-in Server (Fastest)
From the project directory:
```bash
php -S localhost:8000
```
Then open:
- **Public Website:** `http://localhost:8000`
- **Admin Login:** `http://localhost:8000/admin/login.php`

### Option B: Using XAMPP on Windows
1. Copy or symlink this folder into your XAMPP web root:
   ```
   C:\xampp\htdocs\craft-portfolio
   ```
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open in your browser:
   - **Public Website:** `http://localhost/craft-portfolio/`
   - **Admin Login:** `http://localhost/craft-portfolio/admin/login.php`
4. In Admin Dashboard -> **Settings & Security**, click **"Connect & Sync to MySQL"** to ensure all tables are seeded in MySQL.

---

## 📁 Directory Structure

```
craft-portfolio/
│
├── .htaccess                     # URL protection, security headers, MIME security
├── index.php                     # Public Portfolio (Hero, About, Skills, Projects, Experience, Education, Contact)
├── contact.php                   # Contact endpoint (Honeypot, CSRF, Rate Limiting, DB Storage, Email notification)
├── download-resume.php           # Secure Resume PDF streamer
├── robots.txt                    # Search crawler instructions (Blocks /admin/, /core/, /data/)
├── sitemap.xml                   # XML sitemap for SEO
├── 404.php                       # Custom 404 page
│
├── core/
│   ├── config.php                # Global environment constants, paths, session security
│   ├── database.php              # PDO Database abstraction layer with auto-installer & sync
│   ├── schema.sql                # Complete MySQL 8.0 schema (12 tables with indexes)
│   └── seed.php                  # Authoritative initial data seed
│
├── includes/
│   ├── auth.php                  # Session management, lockout, password verification
│   ├── functions.php             # XSS escaping, CSRF generators, sanitization, flash alerts
│   ├── upload.php                # MIME & extension whitelisting, random filename generator
│   ├── header.php                # Public header, navigation, SEO & OpenGraph tags
│   └── footer.php                # Public footer, modal overlays, toast system
│
├── admin/
│   ├── index.php                 # Dashboard Overview & Operational Summary
│   ├── login.php                 # Secure login with CSRF & brute-force mitigation
│   ├── logout.php                # Safe session destruction
│   ├── profile.php               # Edit personal info, bio, typing headlines, avatar upload
│   ├── projects.php              # Projects table, reordering, visibility toggle, deletion
│   ├── project-edit.php          # Case study editor (features, challenges, solutions, results)
│   ├── skills.php                # Skills & Categories management with icons & proficiency
│   ├── experience.php            # Experience claims & Internships timeline editor
│   ├── education.php             # Education history & percentage manager
│   ├── certifications.php        # Certifications, IEEE Coding Challenge, image uploader
│   ├── social.php                # Social links, icons, and visibility switches
│   ├── inquiries.php             # Inbound messages inbox, read/unread status, direct reply
│   ├── resume.php                # Resume PDF uploader & status checker
│   ├── settings.php              # Change owner password, SEO metadata, MySQL sync
│   ├── backup.php                # Download JSON snapshot & Disaster recovery restore
│   └── includes/
│       ├── admin-auth-check.php  # Strictly enforces session authentication
│       ├── admin-header.php      # Admin navigation bar & tab bar
│       └── admin-footer.php      # Admin scripts & confirm dialogs
│
├── assets/
│   ├── css/
│   │   ├── style.css             # Public Design System, Dark/Light Themes, Typography
│   │   └── admin.css             # Admin Control Center styling
│   ├── js/
│   │   └── main.js               # Typing animation, theme toggle, filters, modal drawers
│   └── img/
│       ├── logo.svg              # Custom geometric "G" monogram with circuit details
│       ├── favicon.svg           # Favicon variant
│       ├── girish-avatar.svg     # Geometric developer avatar
│       ├── ieee-certificate.svg  # IEEE Coding Challenge 2026 Certificate vector
│       └── projects/
│           ├── agrishield.svg    # AgriShield cover illustration
│           ├── aura.svg          # AURA AI cover illustration
│           └── astro.svg         # PROJECT ASTRO NASA Space Apps cover illustration
│
├── uploads/                      # Uploaded files directory
│   ├── .htaccess                 # Strictly disables PHP script execution
│   ├── profile/
│   ├── projects/
│   ├── certificates/
│   └── resume/
│       └── Girish_Pawar_Resume.pdf
│
└── tests/
    └── test_portfolio.php        # Automated verification test suite (34 test cases)
```

---

## 🛡️ Security Verification & Best Practices

1. **Zero Public Edit Access**: Public templates load zero administrative scripts or buttons.
2. **SQL Injection Immune**: All database queries utilize PDO prepared statements with parameterized input bindings.
3. **Cross-Site Scripting (XSS) Prevention**: All user and database output is escaped using `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
4. **CSRF Protection**: All state-changing POST requests require a cryptographically randomized 32-byte session token verified with `hash_equals()`.
5. **Anti-Spam Contact Form**: Includes invisible honeypot field (`website_hp`), IP rate limiting (max 5 submissions per hour), and input sanitization.
6. **File Upload Restrictions**: MIME-type validated via `finfo`, strict extension whitelisting (`.jpg`, `.jpeg`, `.png`, `.webp`, `.svg`, `.pdf`), randomized filename generation, and execution blocked via `uploads/.htaccess`.
7. **Session Security**: Cookies set with `HttpOnly`, `SameSite=Lax`, and `Secure` (when on HTTPS), with session ID regenerated on successful login.

---

## 🧪 Automated Test Suite

To run the automated verification suite:
```bash
php tests/test_portfolio.php
```
Result: **34/34 Passing Unit & Integration Checks**.
