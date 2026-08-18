<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'category')) {
                $table->string('category')->nullable()->after('type');
            }

            if (!Schema::hasColumn('notifications', 'group_key')) {
                $table->string('group_key')->nullable()->after('category');
            }

            if (!Schema::hasColumn('notifications', 'dedup_key')) {
                $table->string('dedup_key')->nullable()->after('group_key');
            }

            if (!Schema::hasColumn('notifications', 'image_url')) {
                $table->string('image_url')->nullable()->after('action_url');
            }

            if (!Schema::hasColumn('notifications', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('read_at');
            }

            if (!Schema::hasColumn('notifications', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('pinned_at');
            }

            if (!Schema::hasColumn('notifications', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('archived_at');
            }

            if (!Schema::hasColumn('notifications', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('scheduled_at');
            }

            if (!Schema::hasColumn('notifications', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Índices criados um a um e tolerantes a re-execução: uma tentativa
        // anterior parcial não pode quebrar o deploy seguinte.
        $indexes = [
            ['user_id', 'archived_at'],
            ['user_id', 'category'],
            ['user_id', 'group_key'],
            ['dedup_key'],
            ['scheduled_at'],
            ['expires_at'],
        ];

        foreach ($indexes as $columns) {
            try {
                Schema::table('notifications', function (Blueprint $table) use ($columns) {
                    $table->index($columns);
                });
            } catch (\Throwable $e) {
                // Índice já existe (execução anterior parcial) — seguir em frente.
            }
        }
    }

    public function down(): void
    {
        foreach ([
            ['user_id', 'archived_at'],
            ['user_id', 'category'],
            ['user_id', 'group_key'],
            ['dedup_key'],
            ['scheduled_at'],
            ['expires_at'],
        ] as $columns) {
            try {
                Schema::table('notifications', function (Blueprint $table) use ($columns) {
                    $table->dropIndex($columns);
                });
            } catch (\Throwable $e) {
                // Índice não existe — nada a fazer.
            }
        }

        Schema::table('notifications', function (Blueprint $table) {

            $table->dropColumn([
                'category',
                'group_key',
                'dedup_key',
                'image_url',
                'pinned_at',
                'archived_at',
                'scheduled_at',
                'expires_at',
                'deleted_at',
            ]);
        });
    }
};
