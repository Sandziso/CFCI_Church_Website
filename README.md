# CFCI Church Website

**Christian Family Centre International** – A modern, feature-rich church management and content management system built with PHP/MySQL.

This website serves as the online presence for CFCI, a vibrant Christian community in Manzini, Eswatini. It provides members and visitors with sermons, events, prayer requests, giving options, a photo gallery, ministry information, and a full administrative backend.

<img width="1900" height="906" alt="image" src="https://github.com/user-attachments/assets/aaec5d0f-4a57-424c-bbac-0a2379fae834" />

---

## ✨ Features

- **Public Pages**
  - Home, About, Beliefs, Vision & Mission
  - Sermons (audio/video, series, downloads)
  - Events & Calendar (registration, waitlist, categories)
  - Blog with categories, tags, search
  - Photo Gallery (filterable, lightbox)
  - Ministries & Leadership profiles
  - Prayer Requests (public/private, categories, “I prayed”)
  - Online Giving (MTN Mobile Money, bank transfer)
  - Live Stream integration (YouTube/Facebook)
  - Contact forms with department selection
  - Visitor info & “Plan Your Visit” form
  - Testimonials

- **Member Features**
  - User registration / login (role-based: member, pastor, admin)
  - Personal dashboard
  - Event registration & check-in
  - Ministry involvement
  - Donation history & receipts
  - Notification center

- **Administration**
  - Full content management (events, sermons, blog, gallery)
  - User management (roles, permissions)
  - Financial reporting & donation tracking
  - Security settings (2FA, IP whitelist, password policies)
  - Database backup & audit logs
  - System settings (site name, colours, social links)

- **Technical Highlights**
  - PDO prepared statements – secure database access
  - CSRF protection on all forms
  - Password hashing with `password_hash()`
  - Input sanitization & XSS prevention (`htmlspecialchars`)
  - Responsive design (Bootstrap 5, Font Awesome 6)
  - SEO friendly URLs (slugs)
  - Modular includes (`header.php`, `footer.php`)
  - MySQL views, triggers, stored procedures for analytics
  - Multi‑language ready (currently English / siSwati)

---

## 🛠️ Technology Stack

| Component        | Technology                          |
|------------------|-------------------------------------|
| Backend          | PHP 7.4+ (PDO MySQL)               |
| Database         | MySQL 5.7+ / MariaDB 10.2+         |
| Frontend         | HTML5, CSS3, JavaScript (ES6+)     |
| CSS Framework    | Bootstrap 5.3                      |
| Icons            | Font Awesome 6                     |
| Maps             | Google Maps (iframe embed)         |
| Live Streaming   | YouTube / Facebook embed           |
| Development      | XAMPP / WAMP / LAMP stack          |

---

## 📦 Installation

### 1. Prerequisites
- Web server with PHP 7.4 or higher (Apache / Nginx)
- MySQL 5.7+ or MariaDB 10.2+
- `mod_rewrite` enabled (for clean URLs)

### 2. Download / Clone
```bash
git clone https://github.com/Sandziso/cfci-church-website.git
cd cfci-church-website
```

### 3. Database Setup
1. Create a new MySQL database (e.g. `cfci_church_db`).
2. Import the provided SQL dump:
   ```bash
   mysql -u root -p cfci_church_db < cfci_church_db.sql
   ```
   *(You can use phpMyAdmin or any MySQL client)*
3. The dump includes all tables, sample data, views, triggers, and stored procedures.

### 4. Configuration
1. **Database credentials** – edit `config/db_connect.php`:
   ```php
   $host = 'localhost';
   $dbname = 'cfci_church_db';
   $username = 'your_db_user';
   $password = 'your_db_password';
   ```
2. **Application settings** – edit `config/app_config.php`:
   ```php
   define('SITE_NAME', 'Christian Family Centre International');
   define('BASE_URL', 'http://localhost/cfci-church-website');
   define('SITE_EMAIL', 'info@yourdomain.org');
   ```
3. **Set folder permissions** – ensure the following directories are writable:
   - `uploads/` and all its subdirectories
   - `logs/` (if you enable logging)

### 5. Web Server Configuration
**Apache** – the included `.htaccess` should handle URL rewriting.  
**Nginx** – add a rule like:
```
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

### 6. Run the Website
Navigate to your base URL. The homepage should load with sample data.

**Default admin login** (if you imported the sample data):
- **Email:** `bishop.zakes@cfci.org`
- **Password:** `password`  
*(Change this immediately after first login!)*

---

## 📁 Project Structure

```
cfci-church-website/
├── admin/                  # Administrative dashboard
├── assets/                 # CSS, JS, images
│   ├── css/
│   ├── js/
│   └── images/
├── config/                 # Configuration files
│   ├── app_config.php
│   └── db_connect.php
├── includes/               # Reusable components
│   ├── header.php
│   ├── footer.php
│   ├── auth.php
│   └── functions.php
├── member/                 # Member area
├── uploads/                # User‑uploaded files (gallery, events, avatars)
├── .htaccess               # Apache URL rewriting
├── index.php
├── about.php
├── beliefs.php
├── blog.php
├── contact.php
├── events.php
├── event-details.php
├── event-register.php
├── gallery.php
├── give.php
├── leadership.php
├── livestream.php
├── login.php
├── register.php
├── sermons.php
├── sermon-details.php
├── prayer-request.php
├── resources.php
├── testimonials.php
├── vision.php
├── visitor-info.php
├── plan-your-visit.php
└── ... (other public pages)
```

---

## 🔒 Security Considerations

- **Credentials:** Never commit `config/db_connect.php` with real passwords – use the provided `.gitignore`.
- **SQL Injection:** All queries use prepared statements with PDO.
- **XSS:** Output is escaped with `htmlspecialchars()`.
- **CSRF:** Tokens are validated on all POST forms.
- **File Uploads:** Restricted to images, size limited, and stored outside the webroot (or inside `uploads/` with proper `.htaccess` restrictions).
- **Passwords:** Hashed with `password_hash()` (bcrypt). Password history and expiry are enforced.
- **HTTPS:** Always run the site over HTTPS in production.
- **File Permissions:** Set `uploads/` to 755 and config files to 644 / 600.

---

## 🧪 Development & Contribution

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/amazing-feature`).
3. Commit your changes (`git commit -m 'Add some amazing feature'`).
4. Push to the branch (`git push origin feature/amazing-feature`).
5. Open a Pull Request.

Please ensure your code follows the existing style (PSR-12 inspired) and includes proper escaping and prepared statements.

---

## 📄 License

This project is open‑source software licensed under the **MIT License**.  
See the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgements

- [Bootstrap](https://getbootstrap.com)
- [Font Awesome](https://fontawesome.com)
- [Google Fonts](https://fonts.google.com)
- Our dedicated church volunteers and developers

---

**Soli Deo Gloria**  
*Christian Family Centre International – Manzini, Eswatini*

# .gitignore

```gitignore
# OS generated files
.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db
Thumbs.db
*.swp
*.swo
*~

# IDE files
.idea/
.vscode/
*.sublime-*
.project
.classpath
.settings/
*.code-workspace

# PHP / Composer
vendor/
composer.lock
composer.phar
.php_cs.cache
.phpunit.result.cache

# Environment & configuration files
.env
.env.local
.env.*.local
config/db_connect.php
config/app_config.php
config/*.local.php
config/*.secret.php

# Logs
logs/
*.log
error_log
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# Cache / Temp
tmp/
temp/
cache/
*.cache
*.tmp

# Uploaded files (do NOT commit user content)
uploads/*
!uploads/.htaccess
!uploads/README.md

# Database dumps (except maybe schema-only)
*.sql
!schema.sql
!structure.sql

# Deployment scripts
deploy/
*.sh
!.env.example
!.gitkeep

# Node / NPM
node_modules/
package-lock.json
yarn.lock

# Other sensitive files
*.key
*.pem
*.crt
*.csr
*.bak
*.old
*~
```

---

**Note:** The `.gitignore` excludes all SQL dumps, uploads, and configuration files with credentials. A sample configuration file (`db_connect.example.php`) should be provided and committed instead – you can create one from `db_connect.php` by replacing the real credentials with placeholders.
