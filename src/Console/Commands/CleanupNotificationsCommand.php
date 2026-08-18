<?php

namespace CaiqueBispo\NotificationBell\Console\Commands;

use Illuminate\Console\Command;
use CaiqueBispo\NotificationBell\Models\Notification;


class CleanupNotificationsCommand extends Command
{
    protected $signature = 'notifications:cleanup 
                          {--days=30 : Número de dias para manter notificações lidas}
                          {--unread-days=90 : Número de dias para manter notificações não lidas}
                          {--dry-run : Apenas mostrar o que seria excluído}';

    protected $description = 'Limpar notificações antigas do sistema';

    public function handle()
    {
        $days = $this->option('days');
        $unreadDays = $this->option('unread-days');
        $dryRun = $this->option('dry-run');

        $this->info("🔍 Iniciando limpeza de notificações...");

        if ($dryRun) {
            $this->warn("⚠️  Modo dry-run ativado - nada será excluído");
        }

        $guards = function ($query) {
            if (config('notifications.cleanup.keep_pinned', true)) {
                $query->whereNull('pinned_at');
            }

            if (config('notifications.cleanup.keep_archived', false)) {
                $query->whereNull('archived_at');
            }

            return $query;
        };

        $readCutoff = now()->subDays($days);
        $readQuery = $guards(Notification::whereNotNull('read_at')
            ->where('read_at', '<', $readCutoff));

        $readCount = $readQuery->count();

        $unreadCutoff = now()->subDays($unreadDays);
        $unreadQuery = $guards(Notification::whereNull('read_at')
            ->where('created_at', '<', $unreadCutoff));

        $unreadCount = $unreadQuery->count();

        // Notificações já na lixeira (soft delete) há mais de um dia são expurgadas.
        $trashedQuery = Notification::onlyTrashed()->where('deleted_at', '<', now()->subDay());
        $trashedCount = $trashedQuery->count();

        $this->table(
            ['Tipo', 'Quantidade', 'Critério'],
            [
                ['Lidas', $readCount, "Lidas há mais de {$days} dias"],
                ['Não lidas', $unreadCount, "Criadas há mais de {$unreadDays} dias"],
                ['Na lixeira', $trashedCount, 'Excluídas pelo usuário há mais de 1 dia'],
                ['Total', $readCount + $unreadCount + $trashedCount, 'A serem removidas']
            ]
        );

        $total = $readCount + $unreadCount + $trashedCount;

        if ($total === 0) {
            $this->info("✅ Nenhuma notificação antiga encontrada!");
            return 0;
        }

        if (!$dryRun) {
            if ($this->confirm("Confirma a exclusão de {$total} notificações?")) {
                $deletedRead = $readQuery->forceDelete();
                $deletedUnread = $unreadQuery->forceDelete();
                $deletedTrashed = $trashedQuery->forceDelete();

                $this->info("✅ Limpeza concluída!");
                $this->info("📊 Removidas {$deletedRead} notificações lidas");
                $this->info("📊 Removidas {$deletedUnread} notificações não lidas");
                $this->info("📊 Expurgadas {$deletedTrashed} notificações da lixeira");
                $this->info("📊 Total: " . ($deletedRead + $deletedUnread + $deletedTrashed) . " notificações removidas");
            } else {
                $this->info("❌ Operação cancelada pelo usuário");
            }
        }

        return 0;
    }
}
