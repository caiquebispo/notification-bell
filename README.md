# Laravel Notifications Package

An elegant and responsive Laravel package for managing notifications with Livewire, featuring full dark mode support and queue system.

## Features

- ✅ Modern and responsive interface  
- ✅ Full dark mode support  
- ✅ Multiple notification types (success, error, warning, info)  
- ✅ Dropdown panel with actions (mark as read, delete)  
- ✅ Seamless Livewire integration  
- ✅ Trait for the User model  
- ✅ Helper class for simplified usage  
- ✅ **Queue system for better performance**

# Queue Configuration
## Important: Queue Worker Required
All notifications are now processed through Laravel queues for better performance and scalability. You must run the queue worker for notifications to be delivered:

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
