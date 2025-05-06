# CRUD Generator Installation Guide

## Installing from Forked Repository

### 1. Configure Composer

Add the following to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/YOUR-USERNAME/swatcrud"
    }
  ],
  "require": {
    "ibex/crud-generator": "dev-development"
  }
}
```

### 2. Install Package

```bash
composer update
```

### 3. Publish Configuration

```bash
php artisan vendor:publish --provider="Ibex\CrudGenerator\CrudServiceProvider"
```

### 4. Set Up Module Structure

1. Create Modules directory:

```bash
mkdir Modules
```

2. Update composer.json autoload section:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "Modules/"
    }
  }
}
```

### 5. Register Service Provider

Add the following to `config/app.php`:

```php
'providers' => [
    // ...existing providers...
    Ibex\CrudGenerator\CrudServiceProvider::class,
]
```

### 6. Rebuild Autoloader

```bash
composer dump-autoload
```

### 7. Clear Laravel Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Usage

### Generate CRUD with Module

```bash
# Test with dry-run first
php artisan make:crud users bootstrap Admin --dry-run

# Generate CRUD
php artisan make:crud users bootstrap Admin
```

### Available Stacks

- bootstrap
- tailwind
- livewire
- api

### Module Structure

```
Modules/
└── Admin/
    ├── Config/
    ├── Database/
    │   ├── Migrations/
    │   └── Seeders/
    ├── Http/
    │   ├── Controllers/
    │   ├── Middleware/
    │   ├── Requests/
    │   └── Resources/
    ├── Models/
    ├── Providers/
    ├── Resources/
    │   ├── views/
    │   └── assets/
    └── Routes/
        ├── web.php
        └── api.php
```

### Access Routes

- Web Interface: `http://your-app.test/admin/users`
- API Endpoints: `http://your-app.test/api/users`

## Development Setup

### Local Development

1. Clone your fork:

```bash
git clone https://github.com/YOUR-USERNAME/swatcrud.git
```

2. Link package locally in composer.json:

```json
{
  "repositories": {
    "local": {
      "type": "path",
      "url": "../path/to/swatcrud"
    }
  }
}
```

3. Update composer:

```bash
composer update
```

### Troubleshooting

If you encounter any issues:

1. Clear Laravel cache:

```bash
php artisan config:clear
php artisan cache:clear
```

2. Rebuild autoloader:

```bash
composer dump-autoload
```

3. Check module permissions:

```bash
chmod -R 755 Modules
```
