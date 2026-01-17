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

- ✅ Modern and responsive interface with Tailwind CSS  
- ✅ Full dark mode support  
- ✅ Multiple notification types (success, error, warning, info)  
- ✅ Dropdown panel with actions (mark as read, delete)  
- ✅ Seamless Livewire integration  
- ✅ Trait for the User model  
- ✅ Helper class for simplified usage  
- ✅ **Queue system for better performance**
- ✅ **Authentication protection for notification routes**
- ✅ **Admin panel for notification management**

# Queue Configuration
## Important: Queue Worker Required
All notifications are now processed through Laravel queues for better performance and scalability. You must run the queue worker for notifications to be delivered:

## For access the admin panel use the route:
```
/notifications
```

## Installation

### 1. Install via Composer

```bash
composer require caiquebispo/notification-bell
```

### 2. Publish the package assets

```bash
# Publish migrations
php artisan vendor:publish --tag="notification-bell-migrations"

# Publish views (optional, for customization)
php artisan vendor:publish --tag="notification-bell-views"

# Publish config
php artisan vendor:publish --tag="notification-bell-config"
```

### 3. Run the migrations

```bash
php artisan migrate
```

### 4. Add the trait to the User model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use YourVendor\LaravelNotifications\Traits\HasNotifications;

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

## Dark Mode Customization

The package automatically detects and adapts to your system's dark mode. Ensure your project uses Tailwind's dark mode classes:

```html
<html class="dark">
```

If you use a dark mode toggle, the component will adapt automatically.

## Authentication Protection

All notification management routes are protected with Laravel's authentication middleware. Only authenticated users can access the notification panel and perform CRUD operations.

```php
// The routes are automatically protected with the 'auth' middleware
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [PanelNotificationController::class, 'index'])->name('notifications.index');
    // Other notification routes...
});
```

## Testing

This package includes a comprehensive test suite built with PestPHP. To run the tests:

```bash
# Run all tests
php artisan test

# Run specific notification tests
php artisan test --filter=NotificationTest
```

The test suite covers:
- Authentication protection
- CRUD operations for notifications
- Filtering functionality
- User-specific notifications

## Admin Panel

The package includes a comprehensive admin panel for managing notifications. To access it, navigate to `/notifications` in your browser (requires authentication).

Features of the admin panel:
- Create, edit, and delete notifications
- Filter notifications by title, user, and type
- Send notifications to specific users or all users
- Responsive design with Tailwind CSS
- Dark mode support

![Admin Panel Screenshot](https://via.placeholder.com/800x450.png?text=Notification+Admin+Panel)

## Usage

### Creating Notifications

#### Using the Helper Class

```php
use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;

NotificationHelper::info($userId, 'Title', 'Notification message');
NotificationHelper::success($userId, 'Order Confirmed', 'Your order was successfully confirmed!', ['order_id' => 123], route('orders.show', 123));
NotificationHelper::error($userId, 'Error', 'Something went wrong!');
NotificationHelper::warning($userId, 'Warning', 'Please check your information.');

NotificationHelper::create(
    [1, 2, 3],
    'Mass Notification',
    'This is a message for all users'
);
```

#### Using the User Trait

```php
$user = auth()->user();

$user->notify('Title', 'Message');
$user->success('Success!', 'Operation completed.');
$user->error('Error!', 'Something went wrong.');
$user-warning('Warning!', 'Check your data.');
$user->info('Info', 'Important information');

```

### In Controllers

```php
auth()->user()->success(
    'Order Created!',
    'Your order #' . $order->id . ' was successfully created',
    ['order_id' => $order->id],
    route('orders.show', $order)
);
```

### In Jobs/Events

```php
NotificationHelper::success(
    $this->userId,
    'Payment Processed',
    'Your payment was successfully processed'
);
```

## Configuration

Edit `config/notifications.php` to customize limits, cleanup, and more.

## Visual Customization

- **Info**: Blue  
- **Success**: Green  
- **Warning**: Yellow  
- **Error**: Red  

Responsive design adapts to mobile and desktop.

## Artisan Commands

Example cleanup command:

```php
php artisan notifications:cleanup
```

Example send notification all users

```php
php artisan notifications:send "Título" "Mensagem" --all-users
```

Example send notification specific users

```php
php artisan notifications:send "Manutenção" "Sistema em manutenção" --users=1,2,3 --type=warning
```

Schedule it in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('notifications:cleanup')->daily();
}
```

## License

MIT License – see the LICENSE file for details.
