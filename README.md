# Laravel Notification Bell

An elegant, framework-agnostic Laravel package for managing notifications with Livewire, featuring per-user preferences, full dark mode support, zero-build self-contained assets, and a robust delivery pipeline.

<p align="center">
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/v" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/v/unstable" alt="Latest Unstable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/license" alt="License"></a>
  <a href="https://packagist.org/packages/caiquebispo/notification-bell"><img src="http://poser.pugx.org/caiquebispo/notification-bell/require/php" alt="PHP Version Require"></a>
</p>

## Highlights

### Zero build, zero publish
The package ships its own self-contained CSS (prefixed `nb-`), served directly from `vendor/` through a package route. **No Tailwind, no Bootstrap, no `npm run build`, no `vendor:publish` needed** — install via Composer, drop the component in your layout, done. It looks right in any project: Tailwind, Bootstrap, or plain CSS.

### End-user control
Each user manages their own experience through a preferences panel inside the bell:
- Toggle popup toasts and sounds (with volume control)
- Mute notification categories individually
- "Do not disturb" snooze (1h / 4h / 8h / 24h)
- Daily quiet hours (e.g. 22:00–08:00, midnight-crossing supported)
- Pin important notifications to the top
- Archive instead of delete
- Undo accidental deletions (soft delete + undo toast)
- Clear all notifications in the current tab, with an elegant inline confirmation (no native browser dialogs anywhere)

### Robust delivery pipeline
- **Deduplication** — repeated `dedup_key` within a time window is dropped
- **Rate limiting** — per-user per-minute cap against notification floods
- **Scheduling & expiry** — `scheduled_at` / `expires_at` respected automatically
- **Smart grouping** — N notifications sharing a `group_key` collapse into one expandable item
- **Laravel events** — `NotificationCreated`, `NotificationRead`, `NotificationArchived`, `NotificationDeleted`
- **Broadcasting** — real-time via Echo/Reverb/Pusher with automatic polling fallback
- **REST API** — optional JSON endpoints for mobile apps and SPAs
- **Soft deletes** — nothing is lost by accident; cleanup purges for real
- **Test suite** — PHPUnit + Testbench covering models, dispatcher, preferences, Livewire component and API

## Installation

```bash
composer require caiquebispo/notification-bell
php artisan migrate
```

Add the trait to your User model:

```php
use CaiqueBispo\NotificationBell\Traits\HasNotifications;

class User extends Authenticatable
{
    use HasNotifications;
}
```

Include the component in your layout (Livewire 3 bundles Alpine.js — nothing else to install):

```blade
@auth
    <livewire:notification-bell />
@endauth

@livewireScripts
```

That's it. The component injects its own stylesheet automatically. If you prefer loading it in the `<head>`:

```blade
<head>
    @notificationBellStyles
</head>
```

### Optional publishing

Publishing is **optional** — only for deep customization:

```bash
php artisan vendor:publish --tag="notification-bell-config"      # config/notifications.php
php artisan vendor:publish --tag="notification-bell-views"       # Blade views
php artisan vendor:publish --tag="notification-bell-lang"        # translations
php artisan vendor:publish --tag="notification-bell-migrations"  # migrations
```

## Theming

Customize the look through config — applied via CSS variables, no build step:

```php
'theme' => [
    'mode' => 'auto',                  // auto | system | dark | light (see below)
    'primary' => '#8b5cf6',            // any CSS color, or names like 'violet'
    'badge_background' => '#ef4444',
    'badge_text' => '#ffffff',
    'badge_style' => 'count',          // count | dot | pulse
    'badge_position' => 'top-right',   // top-right | top-left | bottom-right | bottom-left
    'radius' => '0.75rem',
    'dropdown_width' => '22rem',
    'toast_position' => 'bottom-right',// + top-left, top-center, bottom-center...
    'bell_icon' => null,               // Blade view with your own SVG
    'item_view' => null,               // Blade view to render each list item
],
```

Type colors come from the `types` section, so custom types get proper colors automatically:

```php
'types' => [
    'info'    => ['color' => 'blue',   'icon' => 'info-circle'],
    'success' => ['color' => 'green',  'icon' => 'check-circle'],
    'warning' => ['color' => 'yellow', 'icon' => 'exclamation-triangle'],
    'error'   => ['color' => 'red',    'icon' => 'x-circle'],
    'billing' => ['color' => '#8b5cf6', 'icon' => 'bell'], // custom type
],
```

**Dark mode** (`theme.mode`):
- `auto` (default) — follows the host page's explicit markers only: `.dark`, `[data-theme="dark"]`, or `[data-bs-theme="dark"]` (Bootstrap). A light site stays light even for users whose OS is in dark mode.
- `system` — additionally follows the OS `prefers-color-scheme` when no marker is present.
- `dark` / `light` — force one look regardless of the host.

### Per-instance overrides

```blade
<livewire:notification-bell :limit="5" :polling="false" />
```

## User preferences

Enabled by default. Users open the gear icon inside the bell to control toasts, sound, volume, muted categories, snooze and quiet hours. Persisted in the `notification_preferences` table.

```php
'preferences' => [
    'enabled' => true,
    'allow_sound_control' => true,
    'allow_toast_control' => true,
    'allow_category_control' => true,
    'allow_snooze' => true,
    'allow_quiet_hours' => true,
    'snooze_options' => ['1h' => 60, '4h' => 240, '8h' => 480, '24h' => 1440],
],
```

Programmatic access:

```php
$prefs = $user->bellPreferences();
$prefs->muteCategory('marketing');
$prefs->snoozeFor(120);
$prefs->isSnoozed(); // true
```

## Categories

Group notifications above the type level. Users can mute categories they don't care about:

```php
'categories' => [
    'orders' => ['label' => 'Pedidos'],
    'system' => ['label' => 'Sistema'],
    'marketing' => ['label' => 'Novidades'],
],
```

```php
NotificationHelper::info($userId, 'Sale!', '50% off today', null, null, [
    'category' => 'marketing',
]);
```

A notification sent to a user who muted its category is silently skipped.

## Creating notifications

```php
use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;

// Simple
NotificationHelper::info($userId, 'Title', 'Message');
NotificationHelper::success($userId, 'Order confirmed', 'Order #123 confirmed!', ['order_id' => 123], route('orders.show', 123));

// Multiple users
NotificationHelper::create([1, 2, 3], 'Maintenance', 'Scheduled for tonight', 'warning');

// Full options
NotificationHelper::create($userId, 'New comment', 'Someone replied', 'info', null, '/posts/1', [
    'category' => 'social',
    'group_key' => 'post-1-comments',   // groups with similar notifications
    'dedup_key' => 'comment-42',        // duplicate within window is dropped
    'image_url' => '/avatars/maria.png',
    'scheduled_at' => now()->addHour(), // deliver later
    'expires_at' => now()->addDays(3),  // disappears after
    'queue' => false,                   // bypass the queue, create synchronously
]);

// Via the User trait
auth()->user()->success($userId, 'Done!', 'Operation completed.');
```

### Action buttons

```php
NotificationHelper::info($userId, 'Approval needed', 'A document awaits review', [
    'actions' => [
        ['label' => 'Approve', 'url' => '/docs/1/approve', 'style' => 'primary'],
        ['label' => 'Reject', 'url' => '/docs/1/reject', 'style' => 'danger'],
    ],
]);
```

## Delivery guards

```php
'deduplication' => [
    'enabled' => true,
    'window' => 300, // seconds
],
'rate_limit' => [
    'enabled' => true,
    'max_per_minute' => 30, // per user
],
```

## Events

React in your app without coupling to the package:

```php
use CaiqueBispo\NotificationBell\Events\NotificationCreated;
use CaiqueBispo\NotificationBell\Events\NotificationRead;
use CaiqueBispo\NotificationBell\Events\NotificationArchived;
use CaiqueBispo\NotificationBell\Events\NotificationDeleted;

Event::listen(NotificationCreated::class, function ($event) {
    // e.g. mirror to e-mail or Slack
    $event->notification;
});
```

## Broadcasting (real-time)

```php
'broadcasting' => [
    'enabled' => true,
    'channel' => 'notifications.{user_id}',
    'event' => 'NotificationCreated',
    'private' => true,
    'fallback_to_polling' => true,
],
```

With Laravel Echo (Reverb/Pusher) configured in the host app, the bell listens over websockets. Register the channel authorization in your `routes/channels.php`:

```php
Broadcast::channel('notifications.{userId}', fn ($user, $userId) => (int) $user->id === (int) $userId);
```

If Echo is absent, the component keeps working via polling.

## REST API

For mobile apps and SPAs:

```php
'api' => [
    'enabled' => true,
    'prefix' => 'api/notifications',
    'middleware' => ['api', 'auth:sanctum'],
],
```

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/notifications` | Paginated list (`?status=unread\|read\|archived\|pinned`, `?type=`, `?category=`, `?search=`) |
| GET | `/api/notifications/unread-count` | Unread counter |
| GET | `/api/notifications/stats` | Totals by state and type |
| POST | `/api/notifications/{id}/read` | Mark as read |
| POST | `/api/notifications/read-all` | Mark all as read |
| POST | `/api/notifications/{id}/pin` | Toggle pin |
| POST | `/api/notifications/{id}/archive` | Archive |
| POST | `/api/notifications/{id}/unarchive` | Unarchive |
| DELETE | `/api/notifications/{id}` | Soft delete |
| POST | `/api/notifications/{id}/restore` | Undo delete |
| GET | `/api/notifications/preferences` | Get user preferences |
| PUT | `/api/notifications/preferences` | Update user preferences |

All endpoints operate strictly on the authenticated user's own notifications.

## History page

A full-page notification center at `/notifications/history` with search, filters (status, type, category, period), pin/archive actions and incremental loading. Linked from the bell's "View all" footer. Disable with `'features' => ['history_page' => false]`.

## Admin panel

Comprehensive management panel at `/notifications` (configurable): create/edit/delete, filters, send to one or all users, bulk actions, stats — fully redesigned with self-contained CSS.

```php
'route' => [
    'prefix' => 'admin/notifications',
    'middleware' => ['web', 'auth', 'role:admin'],
    'name' => 'admin.notifications.',
],
```

## Localization

Ships with English (default) and Brazilian Portuguese. By default the component follows `app()->getLocale()`, falling back to English. To force a language regardless of the app locale:

```php
// config/notifications.php
'locale' => 'pt_BR', // null = follow the app locale
```

Or per component instance:

```blade
<livewire:notification-bell locale="pt_BR" />
```

This applies to every text in the bell, history page and admin panel — including relative dates ("há 4 meses" instead of "4 months ago"). To customize the strings:

```bash
php artisan vendor:publish --tag="notification-bell-lang"
```

## Sounds

Generated with the Web Audio API (no audio files) — unique tones per type — or bring your own file:

```php
'features' => [
    'sound' => [
        'enabled' => true,
        'volume' => 0.5,
        'file' => '/sounds/notify.mp3', // optional custom sound
    ],
],
```

> Browsers may block audio until the user interacts with the page (standard autoplay policy).

## Artisan commands

```bash
# Cleanup (respects keep_pinned / keep_archived, purges soft-deleted trash)
php artisan notifications:cleanup
php artisan notifications:cleanup --days=60 --unread-days=120
php artisan notifications:cleanup --dry-run

# Bulk send
php artisan notifications:send-bulk "Title" "Message" --all-users
php artisan notifications:send-bulk "Maintenance" "Tonight 22h" --users=1,2,3 --type=warning
```

Schedule cleanup in `routes/console.php` (or `Kernel.php` on older versions):

```php
Schedule::command('notifications:cleanup')->daily();
```

## Queues

Notifications are processed through Laravel queues by default. Run a worker:

```bash
php artisan queue:work
```

Or bypass per call with `'queue' => false` in the options array.

## Testing

```bash
composer install
composer test
```

The suite uses Orchestra Testbench with in-memory SQLite — no app scaffolding required.

## Deploy safety

The package is designed to never take a production page down:

- **Migrations are idempotent and reconciling** — partial previous runs, pre-existing tables or re-runs never fail the deploy; missing columns are added individually.
- **Schema-readiness guard** — between `composer update` and `php artisan migrate`, the bell detects the outdated schema and renders empty (logging a warning) instead of throwing a `QueryException` on every page. It recovers automatically on the first request after the migration runs.
- **Queued deliveries** hitting a stale schema fail and retry through the queue — no notification is silently lost.

## Feature toggles

Every user-facing capability can be turned off individually:

```php
'features' => [
    'pin' => true,
    'archive' => true,
    'clear_all' => true,
    'undo_delete' => ['enabled' => true, 'window' => 8000],
    'grouping' => ['enabled' => true, 'min_size' => 3],
    'history_page' => true,
    // toasts and sound: see above
],
```

## Upgrading from 1.x

- If you had published the config before, re-publish it with `php artisan vendor:publish --tag="notification-bell-config" --force` to see the new keys (`locale`, `theme`, `preferences`, `categories`, `deduplication`, `rate_limit`, `api`...). An old published config keeps working — missing keys fall back to the package defaults.
- New migrations add columns (`category`, `group_key`, `dedup_key`, `image_url`, `pinned_at`, `archived_at`, `scheduled_at`, `expires_at`, `deleted_at`) and the `notification_preferences` table — just run `php artisan migrate`.
- `Notification::delete()` is now a **soft delete**. Cleanup routines purge trashed rows.
- The helper signature is backward compatible; new capabilities live in the trailing `$options` array.
- The old Tailwind-dependent views were replaced by self-contained CSS. If you had published the views, re-publish or port your customizations to `theme.item_view` / `theme.bell_icon`.

## License

MIT License - see the LICENSE file for details.
