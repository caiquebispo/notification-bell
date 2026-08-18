<?php

namespace CaiqueBispo\NotificationBell\Http\Controllers;

use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;
use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API JSON do usuário autenticado, pensada para apps mobile e SPAs.
 * Todas as rotas operam apenas sobre as notificações do próprio usuário.
 */
class NotificationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Notification::forUser($request->user()->id)
            ->deliverable()
            ->search($request->query('search'))
            ->latest();

        if ($request->filled('type')) {
            $query->ofType($request->query('type'));
        }

        if ($request->filled('category')) {
            $query->ofCategory($request->query('category'));
        }

        match ($request->query('status')) {
            'unread' => $query->unread(),
            'read' => $query->read(),
            'archived' => $query->archived(),
            'pinned' => $query->pinned(),
            default => $query->notArchived(),
        };

        return response()->json(
            $query->paginate(min((int) $request->query('per_page', 15), 50))
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => NotificationHelper::getUnreadCount($request->user()->id),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        return response()->json(NotificationHelper::getStats($request->user()->id));
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = $this->findOwn($request, $id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = NotificationHelper::markAllAsRead($request->user()->id);

        return response()->json(['success' => true, 'updated' => $updated]);
    }

    public function archive(Request $request, int $id): JsonResponse
    {
        $this->findOwn($request, $id)->archive();

        return response()->json(['success' => true]);
    }

    public function unarchive(Request $request, int $id): JsonResponse
    {
        $this->findOwn($request, $id)->unarchive();

        return response()->json(['success' => true]);
    }

    public function togglePin(Request $request, int $id): JsonResponse
    {
        $notification = $this->findOwn($request, $id)->togglePin();

        return response()->json(['success' => true, 'pinned' => $notification->isPinned()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findOwn($request, $id)->delete();

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, int $id): JsonResponse
    {
        $notification = Notification::withTrashed()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->restore();

        return response()->json(['success' => true]);
    }

    public function preferences(Request $request): JsonResponse
    {
        return response()->json(NotificationPreference::forUser($request->user()->id));
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'toasts_enabled' => 'sometimes|boolean',
            'sound_enabled' => 'sometimes|boolean',
            'sound_volume' => 'sometimes|integer|min:0|max:100',
            'muted_categories' => 'sometimes|array',
            'muted_categories.*' => 'string',
            'snoozed_until' => 'sometimes|nullable|date',
            'quiet_hours_start' => ['sometimes', 'nullable', 'regex:/^\d{2}:\d{2}$/'],
            'quiet_hours_end' => ['sometimes', 'nullable', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        $preference = NotificationPreference::forUser($request->user()->id);
        $preference->update($validated);

        return response()->json($preference->fresh());
    }

    private function findOwn(Request $request, int $id): Notification
    {
        return Notification::forUser($request->user()->id)->findOrFail($id);
    }
}
