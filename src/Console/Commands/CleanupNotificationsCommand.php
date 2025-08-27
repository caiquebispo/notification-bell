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

        $readCutoff = now()->subDays($days);
        $readQuery = Notification::whereNotNull('read_at')
            ->where('read_at', '<', $readCutoff);

        $readCount = $readQuery->count();

        $unreadCutoff = now()->subDays($unreadDays);
        $unreadQuery = Notification::whereNull('read_at')
            ->where('created_at', '<', $unreadCutoff);

        $unreadCount = $unreadQuery->count();

        $this->table(
            ['Tipo', 'Quantidade', 'Critério'],
            [
                ['Lidas', $readCount, "Lidas há mais de {$days} dias"],
                ['Não lidas', $unreadCount, "Criadas há mais de {$unreadDays} dias"],
                ['Total', $readCount + $unreadCount, 'A serem removidas']
            ]
        );

        if ($readCount + $unreadCount === 0) {
            $this->info("✅ Nenhuma notificação antiga encontrada!");
            return 0;
        }

        if (!$dryRun) {
            if ($this->confirm("Confirma a exclusão de " . ($readCount + $unreadCount) . " notificações?")) {
                $deletedRead = $readQuery->delete();
                $deletedUnread = $unreadQuery->delete();

                $this->info("✅ Limpeza concluída!");
                $this->info("📊 Removidas {$deletedRead} notificações lidas");
                $this->info("📊 Removidas {$deletedUnread} notificações não lidas");
                $this->info("📊 Total: " . ($deletedRead + $deletedUnread) . " notificações removidas");
            } else {
                $this->info("❌ Operação cancelada pelo usuário");
            }
        }

        return 0;
    }
}
