# G3 URL Search Engine

A modern, lightweight URL search engine with a complete admin panel for managing URLs and users. Built with PHP and JSON-based storage for simplicity and portability.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## ✨ Features

### 🔍 Search Engine
- **Real-time URL Search** - Search through URLs by title, description, or URL
- **Clean, Modern UI** - Beautiful glassmorphism design with animated gradient background
- **Responsive Design** - Works seamlessly on desktop, tablet, and mobile devices
- **Fast Performance** - Client-side filtering for instant results

### 👤 User Authentication
- **User Registration** - Register with name, email, password (with confirmation)
- **Secure Login** - Session-based authentication
- **Password Hashing** - Bcrypt encryption for secure password storage
- **Role-based Access** - Two roles: `admin` and `user`

### 🛠️ Admin Panel

#### Dashboard
- **Statistics Overview** - View total URLs, users, and admin count at a glance
- **Modern Sidebar Navigation** - Easy navigation between sections

#### URL Management
- **Add URLs** - Add new URLs with title, URL, and description
- **Edit URLs** - Edit existing URL entries via modal dialog
- **Delete URLs** - Remove URLs with confirmation prompt
- **Search URLs** - Filter URLs by title, URL, or description
- **Pagination** - 50 URLs per page with full pagination controls

#### User Management (Admin Only)
- **View All Users** - See all registered users in a table
- **Change User Roles** - Promote users to admin or demote to regular user
- **Reset Passwords** - Change any user's password via modal dialog
- **Delete Users** - Remove users (cannot delete self)

## 📁 Project Structure

```
search/
├── data/
│   ├── urls.json          # URL database
│   └── users.json         # User database
├── admin_panel.php        # Admin dashboard
├── auth.php               # Authentication functions
├── index.php              # Main search page
├── login.php              # User login page
├── logout.php             # Logout handler
├── register.php           # User registration page
└── README.md              # This file
```

## 🚀 Installation

### Requirements
- PHP 7.4 or higher
- Web server (Apache, Nginx, or XAMPP/WAMP/MAMP)

### Steps

1. **Clone or download** the project to your web server directory:
   ```bash
   # For XAMPP
   cd C:\xampp\htdocs
   git clone <repository-url> search
   
   # Or simply copy the files to:
   # C:\xampp\htdocs\search\
   ```

2. **Ensure the `data` directory is writable**:
   ```bash
   chmod 755 data/
   chmod 644 data/*.json
   ```

3. **Access the application**:
   - Search Page: `http://localhost/search/`
   - Login: `http://localhost/search/login.php`
   - Register: `http://localhost/search/register.php`
   - Admin Panel: `http://localhost/search/admin_panel.php`

## 🔐 Default Admin Credentials

| Field    | Value              |
|----------|-------------------|
| Email    | admin@example.com |
| Password | password          |

> ⚠️ **Important**: Change the default admin password after first login!

## 💻 Usage

### For Users
1. Visit the main search page
2. Enter keywords in the search box
3. Click **Search** or press **Enter**
4. Click on any result to visit the URL

### For Administrators
1. Log in with admin credentials
2. Access the **Admin Panel**
3. Manage URLs:
   - Add new URLs using the form
   - Edit existing URLs by clicking **Edit**
   - Delete URLs by clicking **Delete**
   - Search and paginate through URLs
4. Manage Users:
   - Change user roles with the dropdown
   - Reset passwords by clicking **Reset Password**
   - Delete users by clicking **Delete**

## 🎨 Design Features

- **Glassmorphism UI** - Modern frosted glass effect
- **Gradient Backgrounds** - Beautiful animated gradient backgrounds
- **Smooth Animations** - Fade and scale transitions on modals
- **Responsive Layout** - Adapts to all screen sizes
- **Tailwind CSS** - Utility-first CSS framework via CDN
- **Inter Font** - Clean, modern typography

## 🔧 Configuration

### Changing Items Per Page
In `admin_panel.php`, modify the `$urlsPerPage` variable:
```php
$urlsPerPage = 50; // Change to desired number
```

### Adding Custom Roles
Extend the role system in `auth.php`:
```php
function isEditor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'editor';
}
```

## 📝 JSON Data Structure

### users.json
```json
[
    {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "password": "$2y$10$...",
        "role": "admin",
        "created_at": "2026-01-09T12:00:00+01:00"
    }
]
```

### urls.json
```json
[
    {
        "id": 1,
        "title": "Example Site",
        "url": "https://example.com",
        "description": "An example website"
    }
]
```

## 🛡️ Security Features

- **Password Hashing** - Uses PHP's `password_hash()` with BCRYPT
- **Session Management** - Secure session-based authentication
- **Input Sanitization** - `htmlspecialchars()` for XSS prevention
- **CSRF Protection** - Form-based actions use POST method
- **Role Verification** - Admin actions require admin role check

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework
- [Inter Font](https://fonts.google.com/specimen/Inter) - Beautiful open-source typeface
- [Heroicons](https://heroicons.com/) - SVG icons used in the UI

---

Made with ❤️ by G3 Team
