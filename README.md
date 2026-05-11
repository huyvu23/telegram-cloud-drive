# Telegram Cloud Drive

A full-featured cloud storage application powered by Telegram Bot API as the storage backend.

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Telegram Bot Token (from @BotFather)

### Installation

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start server
php artisan serve --host=0.0.0.0 --port=8000
```

### .env Configuration

```env
APP_KEY=              # Run: php artisan key:generate
JWT_SECRET=           # Secret key (min 32 chars)
TELEGRAM_BOT_TOKEN=   # From @BotFather
TELEGRAM_API_ID=      # From my.telegram.org
TELEGRAM_API_HASH=    # From my.telegram.org
```

## ✨ Features

- 📁 Upload, download, preview files
- 📂 Folder organization
- 🔗 Share via public links
- ⭐ Favorites & trash
- 🔐 JWT authentication
- 🌐 Vietnamese & English

## 🌍 Language Support

- 🇻🇳 Vietnamese (default) - Access `/lang/vi`
- 🇬🇧 English - Access `/lang/en`

## 📝 License

MIT License
