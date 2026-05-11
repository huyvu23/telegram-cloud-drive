# 📦 Telegram Cloud Drive

> **Cloud Storage powered by Telegram Bot API** - Lưu trữ đám mây miễn phí không giới hạn!

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-10-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com/)
[![Telegram](https://img.shields.io/badge/Telegram-Bot-26A5E4?style=flat&logo=telegram&logoColor=white)](https://core.telegram.org/bots/api)

---

## 🇻🇳 Giới thiệu

**Telegram Cloud Drive** là ứng dụng lưu trữ đám mây sử dụng Telegram Bot API làm backend lưu trữ. 
Thay vì lưu trữ file trên server, tất cả file được upload trực tiếp lên Telegram Bot của bạn.

### ✨ Tính năng chính

| Tính năng | Mô tả |
|-----------|-------|
| 📁 **Upload/Download** | Upload file không giới hạn dung lượng |
| 📂 **Thư mục** | Tạo, tổ chức thư mục theo ý muốn |
| 🔗 **Chia sẻ** | Tạo link chia sẻ với mật khẩu & hết hạn |
| ⭐ **Yêu thích** | Đánh dấu file quan trọng |
| 🗑️ **Thùng rác** | Xóa mềm, khôi phục file |
| 🔍 **Tìm kiếm** | Tìm file nhanh chóng |

### 👥 Hệ thống đa người dùng

- 📝 **Đăng ký/Đăng nhập** - Tài khoản cá nhân
- 👤 **Phân quyền** - USER, ADMIN
- 💾 **Quota** - Giới hạn dung lượng mỗi user
- 📊 **Dashboard** - Quản lý người dùng

### 🔐 Bảo mật

- 🔐 **JWT Authentication** - Đăng nhập an toàn
- 🔒 **Password Protected Shares** - Link chia sẻ có mật khẩu
- ⏱️ **Link Expiration** - Link tự hết hạn
- 🛡️ **Rate Limiting** - Chống spam

### 🌐 Giao diện

- 🎨 **Thiết kế hiện đại** - Giao diện đẹp mắt
- 📱 **Responsive** - Hoạt động trên mọi thiết bị
- 🇻🇳🇬🇧 **Đa ngôn ngữ** - Tiếng Việt & Tiếng Anh

---

## 🇬🇧 Introduction

**Telegram Cloud Drive** is a cloud storage application powered by Telegram Bot API as storage backend.
Instead of storing files on server, all files are uploaded directly to your Telegram Bot.

### ✨ Key Features

| Feature | Description |
|---------|-------------|
| 📁 **Upload/Download** | Unlimited file storage |
| 📂 **Folders** | Create & organize folders |
| 🔗 **Sharing** | Share links with password & expiration |
| ⭐ **Favorites** | Star important files |
| 🗑️ **Trash** | Soft delete with restore |
| 🔍 **Search** | Quick file search |

### 👥 Multi-User System

- 📝 **Register/Login** - Personal accounts
- 👤 **Roles** - USER, ADMIN
- 💾 **Quota** - Per-user storage limits
- 📊 **Dashboard** - User management

### 🔐 Security

- 🔐 **JWT Authentication** - Secure login
- 🔒 **Password Protected Shares** - Password-protected links
- ⏱️ **Link Expiration** - Auto-expire links
- 🛡️ **Rate Limiting** - Anti-spam protection

### 🌐 UI/UX

- 🎨 **Modern Design** - Beautiful interface
- 📱 **Responsive** - Works on all devices
- 🇻🇳🇬🇧 **Multi-language** - Vietnamese & English

---

## 🚀 Quick Start / Cài đặt nhanh

### Yêu cầu / Requirements

- PHP 8.1+
- Composer
- Telegram Bot Token (từ @BotFather)

### Setup

```bash
# Clone repository
git clone https://github.com/huydatvn/telegram-cloud-drive.git
cd telegram-cloud-drive

# Install dependencies
composer install

# Copy & edit .env
cp .env.example .env
nano .env  # Điền Telegram Bot Token

# Generate app key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start server
php artisan serve --host=0.0.0.0 --port=8000
```

### Cấu hình Telegram / Telegram Configuration

```env
# .env file
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
```

**Cách lấy Telegram Token:**
1. Mở Telegram, chat với [@BotFather](https://t.me/BotFather)
2. Gửi `/newbot`
3. Làm theo hướng dẫn, copy token

---

## 📱 Cách sử dụng / Usage

1. **Đăng ký tài khoản** / Register account
2. **Cấu hình Telegram** trong Settings / Configure Telegram in Settings
3. **Upload file** / Upload files
4. **Chia sẻ** với bạn bè / Share with friends!

---

## 📁 Cấu trúc dự án / Project Structure

```
telegram-cloud-drive/
├── app/
│   ├── Http/Controllers/    # API Controllers
│   │   ├── AuthController.php
│   │   ├── FileController.php
│   │   ├── FolderController.php
│   │   └── ShareController.php
│   ├── Models/             # Database Models
│   └── Helpers/            # Helper Functions
├── database/migrations/    # Database Migrations
├── resources/
│   ├── lang/              # Language Files (vi/en)
│   └── views/             # Blade Templates
├── routes/web.php          # Routes
└── config/                # Configuration
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-------------|
| Backend | PHP 8.1+, Laravel 10 |
| Database | SQLite |
| Frontend | Blade, TailwindCSS, Alpine.js |
| Auth | JWT (tymon/jwt-auth) |
| Storage | Telegram Bot API |

---

## 🌍 Ngôn ngữ / Languages

Chuyển đổi ngôn ngữ / Switch language:
- 🇻🇳 `/lang/vi` - Tiếng Việt (mặc định)
- 🇬🇧 `/lang/en` - English

---

## 📝 License

MIT License - Sử dụng tự do!

---

## 🤝 Đóng góp / Contributing

Fork, pull request, feedback - mọi đóng góp đều được chào đón!

---

**Made with ❤️ by [Vũ Huy Đạt](https://github.com/huydatvn)**
