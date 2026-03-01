# Laravel Notifications Package

An elegant and responsive Laravel package for managing notifications with Livewire and Tailwind CSS, featuring full dark mode support, authentication protection, and queue system.
<p align="center">
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/v" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/v/unstable" alt="Latest Unstable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/license" alt="License"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/require/php" alt="PHP Version Require"></a>
</p>

## Features

- Modern and responsive interface with Tailwind CSS
- Full dark mode support
- Multiple notification types (success, error, warning, info)
- Dropdown panel with actions (mark as read, delete)
- Seamless Livewire integration
- Trait for the User model
- Helper class for simplified usage
- Queue system for better performance
- Authentication protection for notification routes
- Admin panel for notification management
- Configurable user model, table, and column mapping
- Configurable route prefix, middleware, and naming

## Queue Configuration

All notifications are processed through Laravel queues for better performance and scalability. You must run the queue worker for notifications to be delivered.

## Installation

### 1. Install via Composer

```bash
composer require caiquebispo/notification-bell
```

### 2. Publish the package assets

```bash
# Publish config
php artisan vendor:publish --tag="notification-bell-config"

# Publish migrations (optional - migrations run automatically)
php artisan vendor:publish --tag="notification-bell-migrations"

# Publish views (optional, for customization)
php artisan vendor:publish --tag="notification-bell-views"

```

### 3. Run the migrations

Migrations are loaded automatically by the package. Just run:

```bash
php artisan migrate
```

### 4. Add the trait to the User model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use CaiqueBispo\NotificationBell\Traits\HasNotifications;

class User extends Authenticatable
{
    use HasNotifications;

    // ...
}
```

### 5. Include Alpine.js (if not already in use)

Add this to your main layout:

```html
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

### 6. Include the component in your layout

```blade
<div class="flex items-center space-x-4">
    {{-- Other header elements --}}

    @auth
        <livewire:notification-bell />
    @endauth
</div>

{{-- Livewire scripts --}}
@livewireScripts
```

## Configuration

Publish the config file and edit `config/notifications.php` to customize the package behavior.

```bash
php artisan vendor:publish --tag="notification-bell-config"
```

### User Model & Table

Configure a custom user model and table name:

```php
'user_model' => App\Models\User::class,
'user_table' => 'users',
```

For systems that use a different model (e.g. `Usuario`):

```php
'user_model' => App\Models\Usuario::class,
'user_table' => 'usuarios',
```

### User Column Mapping

Map user model columns used by the notification panel. Useful for systems with different column names:

```php
'user_columns' => [
    'name' => 'name',
],
```

For example, Brazilian systems that use `nome` instead of `name`:

```php
'user_columns' => [
    'name' => 'nome',
],
```

The panel will automatically use the mapped column to display user names, avatars, and dropdowns.

### Route Configuration

Customize the route prefix, middleware, and naming:

```php
'route' => [
    'prefix' => 'notifications',
    'middleware' => ['web', 'auth'],
    'name' => 'notifications.',
],
```

Example with a custom prefix and additional middleware:

```php
'route' => [
    'prefix' => 'admin/notifications',
    'middleware' => ['web', 'auth', 'role:admin'],
    'name' => 'admin.notifications.',
],
```

### Polling

Configure real-time polling for new notifications:

```php
'polling' => [
    'enabled' => true,
    'interval' => '10s',
],
```

### Toast Notifications & Sound

```php
'features' => [
    'toasts' => [
        'enabled' => true,
        'duration' => 5000, // 5 seconds
    ],
    'sound' => [
        'enabled' => false, // Enable notification sounds
        'volume' => 0.5,    // Volume level: 0.0 (silent) to 1.0 (max)
    ],
],
```

Notification sounds are generated using the Web Audio API -- no external audio files are required. Each notification type has a unique sound:

| Type | Sound |
|------|-------|
| **success** | Ascending two-note chime (C5 to E5) |
| **error** | Descending two-note tone (E4 to C4) |
| **warning** | Two short attention pulses (A4) |
| **info** | Soft single ping (G5) |

> **Note:** Browsers may block audio playback until the user has interacted with the page (clicked, tapped, or pressed a key). This is standard browser autoplay policy.

### Notification Types

```php
'types' => [
    'info'    => ['color' => 'blue',   'icon' => 'info-circle'],
    'success' => ['color' => 'green',  'icon' => 'check-circle'],
    'warning' => ['color' => 'yellow', 'icon' => 'exclamation-triangle'],
    'error'   => ['color' => 'red',    'icon' => 'x-circle'],
],
```

### Broadcasting

```php
'broadcasting' => [
    'enabled' => false,
    'channel' => 'notifications.{user_id}',
    'event' => 'NotificationCreated',
],
```

### Cleanup

```php
'cleanup' => [
    'enabled' => true,
    'days_to_keep' => 30,
    'schedule' => 'daily',
],
```

## Admin Panel

The package includes a comprehensive admin panel for managing notifications. Access it at `/notifications` (or your configured prefix) in your browser (requires authentication).

Features of the admin panel:
- Create, edit, and delete notifications
- Filter notifications by title, user, and type
- Send notifications to specific users or all users
- Bulk actions (select and delete multiple)
- Responsive design with Tailwind CSS
- Dark mode support

## Usage

### Creating Notifications

#### Using the Helper Class

```php
use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;

// Simple notifications
NotificationHelper::info($userId, 'Title', 'Notification message');
NotificationHelper::success($userId, 'Order Confirmed', 'Your order was successfully confirmed!', ['order_id' => 123], route('orders.show', 123));
NotificationHelper::error($userId, 'Error', 'Something went wrong!');
NotificationHelper::warning($userId, 'Warning', 'Please check your information.');

// Mass notification (multiple users)
NotificationHelper::create(
    [1, 2, 3],
    'Mass Notification',
    'This is a message for all users'
);
```

#### Using the User Trait

```php
$user = auth()->user();

$user->bellNotify($userId, 'Title', 'Message');
$user->success($userId, 'Success!', 'Operation completed.');
$user->error($userId, 'Error!', 'Something went wrong.');
$user->warning($userId, 'Warning!', 'Check your data.');
$user->info($userId, 'Info', 'Important information');
```

### In Controllers

```php
auth()->user()->success(
    $userId,
    'Order Created!',
    'Your order #' . $order->id . ' was successfully created',
    ['order_id' => $order->id],
    route('orders.show', $order)
);
```

### In Jobs/Events

```php
NotificationHelper::success(
    $userId,
    'Payment Processed',
    'Your payment was successfully processed'
);
```

### Helper Utilities

```php
// Get unread count
NotificationHelper::getUnreadCount($userId);

// Mark all as read
NotificationHelper::markAllAsRead($userId);

// Get stats
NotificationHelper::getStats($userId);
// Returns: ['total' => 10, 'unread' => 3, 'read' => 7, 'by_type' => ['info' => 5, 'success' => 3, ...]]

// Cleanup old notifications
NotificationHelper::cleanup(30); // Remove notifications older than 30 days
```

## Dark Mode

The package automatically detects and adapts to your system's dark mode. Ensure your project uses Tailwind's dark mode classes:

```html
<html class="dark">
```

If you use a dark mode toggle, the component will adapt automatically.

## Artisan Commands

### Cleanup notifications

```bash
# Remove old notifications (default: 30 days)
php artisan notifications:cleanup

# Custom retention period
php artisan notifications:cleanup --days=60 --unread-days=120

# Preview without deleting
php artisan notifications:cleanup --dry-run
```

### Send bulk notifications

```bash
# Send to all users
php artisan notifications:send-bulk "Title" "Message" --all-users

# Send to specific users
php artisan notifications:send-bulk "Maintenance" "System maintenance" --users=1,2,3 --type=warning

# With action URL
php artisan notifications:send-bulk "Update" "New version available" --all-users --url=https://example.com

# Preview without sending
php artisan notifications:send-bulk "Title" "Message" --all-users --dry-run
```

### Schedule cleanup

In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('notifications:cleanup')->daily();
}
```

## License

MIT License - see the LICENSE file for details.
