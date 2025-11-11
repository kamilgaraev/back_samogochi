<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL поддерживает CREATE INDEX IF NOT EXISTS
        $this->createIndexIfNotExists('player_profiles', 'last_login', 'player_profiles_last_login_index');
        $this->createIndexIfNotExists('player_profiles', 'level', 'player_profiles_level_index');
        $this->createIndexIfNotExists('player_profiles', ['created_at', 'last_login'], 'player_profiles_created_login_index');

        $this->createIndexIfNotExists('player_situations', 'completed_at', 'player_situations_completed_at_index');
        $this->createIndexIfNotExists('player_situations', ['player_id', 'created_at'], 'player_situations_player_created_index');
        $this->createIndexIfNotExists('player_situations', 'created_at', 'player_situations_created_at_index');

        $this->createIndexIfNotExists('player_micro_actions', ['player_id', 'created_at'], 'player_micro_actions_player_created_index');
        $this->createIndexIfNotExists('player_micro_actions', 'created_at', 'player_micro_actions_created_at_index');
        $this->createIndexIfNotExists('player_micro_actions', ['micro_action_id', 'created_at'], 'player_micro_actions_action_created_index');

        $this->createIndexIfNotExists('users', 'created_at', 'users_created_at_index');

        $this->createIndexIfNotExists('activity_logs', ['user_id', 'created_at'], 'activity_logs_user_created_index');
        $this->createIndexIfNotExists('activity_logs', 'created_at', 'activity_logs_created_at_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('player_profiles', 'player_profiles_last_login_index');
        $this->dropIndexIfExists('player_profiles', 'player_profiles_level_index');
        $this->dropIndexIfExists('player_profiles', 'player_profiles_created_login_index');

        $this->dropIndexIfExists('player_situations', 'player_situations_completed_at_index');
        $this->dropIndexIfExists('player_situations', 'player_situations_player_created_index');
        $this->dropIndexIfExists('player_situations', 'player_situations_created_at_index');

        $this->dropIndexIfExists('player_micro_actions', 'player_micro_actions_player_created_index');
        $this->dropIndexIfExists('player_micro_actions', 'player_micro_actions_created_at_index');
        $this->dropIndexIfExists('player_micro_actions', 'player_micro_actions_action_created_index');

        $this->dropIndexIfExists('users', 'users_created_at_index');

        $this->dropIndexIfExists('activity_logs', 'activity_logs_user_created_index');
        $this->dropIndexIfExists('activity_logs', 'activity_logs_created_at_index');
    }

    /**
     * Create index if it doesn't exist
     */
    private function createIndexIfNotExists(string $table, string|array $columns, string $indexName): void
    {
        try {
            if (is_array($columns)) {
                $columnsList = implode(', ', $columns);
            } else {
                $columnsList = $columns;
            }
            
            DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} ({$columnsList})");
        } catch (\Exception $e) {
            // Индекс уже существует или другая ошибка - игнорируем
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            DB::statement("DROP INDEX IF EXISTS {$indexName}");
        } catch (\Exception $e) {
            // Индекс не существует - игнорируем
        }
    }
};

