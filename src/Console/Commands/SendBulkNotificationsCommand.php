<?php

namespace CaiqueBispo\NotificationBell\Console\Commands;

use Illuminate\Console\Command;
use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;


class SendBulkNotificationsCommand extends Command
{
    protected $signature = 'notifications:send-bulk
                          {title : Título da notificação}
                          {message : Mensagem da notificação}
                          {--type=info : Tipo da notificação (info, success, warning, error)}
                          {--users= : IDs dos usuários separados por vírgula}
                          {--all-users : Enviar para todos os usuários}
                          {--url= : URL de ação}
                          {--dry-run : Apenas simular o envio}';

    protected $description = 'Enviar notificações em massa para usuários';

    public function handle()
    {
        $title = $this->argument('title');
        $message = $this->argument('message');
        $type = $this->option('type');
        $userIds = $this->option('users');
        $allUsers = $this->option('all-users');
        $url = $this->option('url');
        $dryRun = $this->option('dry-run');

        if (!in_array($type, ['info', 'success', 'warning', 'error'])) {
            $this->error("❌ Tipo inválido. Use: info, success, warning, error");
            return 1;
        }

        if ($allUsers) {
            $userModel = config('notifications.user_model');
            $users = $userModel::pluck('id')->toArray();
        } elseif ($userIds) {
            $users = array_map('intval', explode(',', $userIds));
        } else {
            $this->error("❌ Especifique --users ou --all-users");
            return 1;
        }

        $this->info("📧 Preparando envio de notificação em massa...");
        $this->line('');

        $this->table(
            ['Campo', 'Valor'],
            [
                ['Título', $title],
                ['Mensagem', substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '')],
                ['Tipo', ucfirst($type)],
                ['Destinatários', count($users) . ' usuário(s)'],
                ['URL de Ação', $url ?: 'Nenhuma'],
                ['Modo', $dryRun ? 'Simulação (Dry Run)' : 'Envio Real']
            ]
        );

        $this->line('');
        $countUser = count($users);

        if ($dryRun) {
            $this->warn("⚠️  Modo simulação - nenhuma notificação será enviada");
            $this->info("✅ {$countUser} notificações seriam enviadas");
            return 0;
        }

        if (!$this->confirm("Confirma o envio para " . count($users) . " usuário(s)?")) {
            $this->info("❌ Operação cancelada");
            return 0;
        }

        $this->info("📤 Enviando notificações...");

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        $sent = 0;
        $errors = 0;

        foreach ($users as $userId) {
            try {
                NotificationHelper::create($userId, $title, $message, $type, null, $url);
                $sent++;
            } catch (\Exception $e) {
                $errors++;
                $this->line('');
                $this->error("❌ Erro ao enviar para usuário {$userId}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('');

        $this->info("✅ Envio concluído!");
        $this->info("📊 Enviadas: {$sent}");
        if ($errors > 0) {
            $this->warn("⚠️  Erros: {$errors}");
        }

        return 0;
    }
}
