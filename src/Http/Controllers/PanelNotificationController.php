<?php

namespace CaiqueBispo\NotificationBell\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;

class PanelNotificationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $users = User::where('id', '!=', Auth::id())->get(['id', 'name']);
        
        $notifications = $this->buildNotificationsQuery($request)->paginate(10);

        if ($request->ajax()) {
            return $this->ajaxResponse($notifications);
        }

        // Calculate Stats
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::whereNull('read_at')->count(),
            'success' => Notification::where('type', 'success')->count(),
            'error' => Notification::where('type', 'error')->count(),
        ];

        return view('notification-bell::notification-panel', compact('notifications', 'users', 'stats'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateNotificationRequest($request);
            
            if (empty($validated['recipientUser'])) {
                return $this->handleBroadcastNotification($validated);
            }
            
            return $this->handleSingleNotification($validated);
            
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e);
        }
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        try {
            $validated = $this->validateNotificationRequest($request);
            
            if ($validated['processing_type'] === 'queue') {
                return $this->handleQueuedUpdate($validated, $notification);
            }
            
            return $this->handleImmediateUpdate($validated, $notification);
            
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e);
        }
    }

    public function destroy(Notification $notification): JsonResponse
    {
        try {
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificação excluída com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notificação'
            ], 500);
        }
    }
    
    public function destroyAll(Request $request): JsonResponse
    {
        try {
            Notification::query()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Todas as notificações foram excluídas com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir todas as notificações'
            ], 500);
        }
    }

    public function destroySelected(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma notificação selecionada.'
                ], 422);
            }

            Notification::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificações selecionadas excluídas com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notificações selecionadas'
            ], 500);
        }
    }
    
    public function show(int $id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'notification' => $notification
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notificação não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter detalhes da notificação'
            ], 500);
        }
    }

    /**
     * Métodos auxiliares para melhorar a legibilidade
     */
    private function buildNotificationsQuery(Request $request)
    {
        $query = Notification::with('user:id,name')->orderBy('created_at', 'desc');

        if ($request->filled('search_title')) {
            $query->where('title', 'LIKE', '%' . $request->search_title . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return $query;
    }

    private function ajaxResponse($notifications): JsonResponse
    {
        return response()->json([
            'success' => true,
            'html' => view('notification-bell::partials.notifications-table', compact('notifications'))->render(),
            'pagination' => (string) $notifications->links(),
            'total' => $notifications->total()
        ]);
    }

    private function validateNotificationRequest(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'recipientUser' => 'nullable|exists:users,id',
            'url' => 'nullable|url|max:500',
            'processing_type' => 'required|in:immediate,queue',
        ]);
    }

    private function handleBroadcastNotification(array $validated): JsonResponse
    {
        $users = User::all();
        $isQueue = $validated['processing_type'] === 'queue';

        if ($isQueue) {
            NotificationHelper::create(
                $users->pluck('id')->toArray(),
                $validated['title'],
                $validated['message'],
                $validated['type'],
                null,
                $validated['url'] ?? null
            );
            
            return $this->broadcastSuccessResponse($users->count(), true);
        }

        $notifications = $this->createNotificationsForUsers($users, $validated);
        return $this->broadcastSuccessResponse(count($notifications), false);
    }

    private function handleSingleNotification(array $validated): JsonResponse
    {
        $isQueue = $validated['processing_type'] === 'queue';
        $userId = $validated['recipientUser'];

        if ($isQueue) {
            NotificationHelper::create(
                $userId,
                $validated['title'],
                $validated['message'],
                $validated['type'],
                null,
                $validated['url'] ?? null
            );
            
            return $this->singleSuccessResponse(true);
        }

        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'action_url' => $validated['url'] ?? null,
            'read_at' => null,
        ]);

        return $this->singleSuccessResponse(false, $notification);
    }

    private function handleQueuedUpdate(array $validated, Notification $notification): JsonResponse
    {
        $notificationId = $notification->id;
        $notification->delete();
        
        NotificationHelper::create(
            $validated['recipientUser'] ?? $notification->user_id,
            $validated['title'],
            $validated['message'],
            $validated['type'],
            null,
            $validated['url'] ?? null
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Notificação atualizada e será processada pela fila!',
            'deleted_id' => $notificationId
        ]);
    }

    private function handleImmediateUpdate(array $validated, Notification $notification): JsonResponse
    {
        $notification->update([
            'user_id' => $validated['recipientUser'] ?? $notification->user_id,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'action_url' => $validated['url'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notificação atualizada com sucesso!',
            'notification' => $notification
        ]);
    }

    private function createNotificationsForUsers($users, array $data): array
    {
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = Notification::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'],
                'action_url' => $data['url'] ?? null,
                'read_at' => null,
            ]);
        }
        
        return $notifications;
    }

    private function broadcastSuccessResponse(int $count, bool $isQueued): JsonResponse
    {
        $message = $isQueued 
            ? 'Notificação enviada para todos os usuários e será processada pela fila!'
            : 'Notificação enviada para todos os usuários com sucesso!';

        return response()->json([
            'success' => true,
            'message' => $message,
            'notification_count' => $count
        ]);
    }

    private function singleSuccessResponse(bool $isQueued, ?Notification $notification = null): JsonResponse
    {
        $message = $isQueued 
            ? 'Notificação criada e será processada pela fila!'
            : 'Notificação criada com sucesso!';

        $response = [
            'success' => true,
            'message' => $message
        ];

        if (!$isQueued && $notification) {
            $response['notification'] = $notification;
        }

        return response()->json($response);
    }

    private function validationErrorResponse(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos',
            'errors' => $e->errors()
        ], 422);
    }

    private function serverErrorResponse(\Exception $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Erro interno do servidor: ' . $e->getMessage()
        ], 500);
    }
}