<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('player_profiles', function (Blueprint $table) {
            // Индекс для запросов активных пользователей
            if (!$this->indexExists('player_profiles', 'player_profiles_last_login_index')) {
                $table->index('last_login', 'player_profiles_last_login_index');
            }
            
            // Индекс для запросов по уровню
            if (!$this->indexExists('player_profiles', 'player_profiles_level_index')) {
                $table->index('level', 'player_profiles_level_index');
            }
            
            // Составной индекс для retention запросов
            if (!$this->indexExists('player_profiles', 'player_profiles_created_login_index')) {
                $table->index(['created_at', 'last_login'], 'player_profiles_created_login_index');
            }
        });

        Schema::table('player_situations', function (Blueprint $table) {
            // Индекс для запросов завершенных ситуаций
            if (!$this->indexExists('player_situations', 'player_situations_completed_at_index')) {
                $table->index('completed_at', 'player_situations_completed_at_index');
            }
            
            // Составной индекс для запросов по игроку и времени
            if (!$this->indexExists('player_situations', 'player_situations_player_created_index')) {
                $table->index(['player_id', 'created_at'], 'player_situations_player_created_index');
            }
            
            // Индекс для запросов по времени создания
            if (!$this->indexExists('player_situations', 'player_situations_created_at_index')) {
                $table->index('created_at', 'player_situations_created_at_index');
            }
        });

        Schema::table('player_micro_actions', function (Blueprint $table) {
            // Составной индекс для запросов по игроку и времени
            if (!$this->indexExists('player_micro_actions', 'player_micro_actions_player_created_index')) {
                $table->index(['player_id', 'created_at'], 'player_micro_actions_player_created_index');
            }
            
            // Индекс для запросов по времени создания
            if (!$this->indexExists('player_micro_actions', 'player_micro_actions_created_at_index')) {
                $table->index('created_at', 'player_micro_actions_created_at_index');
            }
            
            // Индекс для топ действий
            if (!$this->indexExists('player_micro_actions', 'player_micro_actions_action_created_index')) {
                $table->index(['micro_action_id', 'created_at'], 'player_micro_actions_action_created_index');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Индекс для запросов новых регистраций
            if (!$this->indexExists('users', 'users_created_at_index')) {
                $table->index('created_at', 'users_created_at_index');
            }
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            // Составной индекс для запросов активности пользователей
            if (!$this->indexExists('activity_logs', 'activity_logs_user_created_index')) {
                $table->index(['user_id', 'created_at'], 'activity_logs_user_created_index');
            }
            
            // Индекс для запросов по времени
            if (!$this->indexExists('activity_logs', 'activity_logs_created_at_index')) {
                $table->index('created_at', 'activity_logs_created_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_profiles', function (Blueprint $table) {
            $table->dropIndex('player_profiles_last_login_index');
            $table->dropIndex('player_profiles_level_index');
            $table->dropIndex('player_profiles_created_login_index');
        });

        Schema::table('player_situations', function (Blueprint $table) {
            $table->dropIndex('player_situations_completed_at_index');
            $table->dropIndex('player_situations_player_created_index');
            $table->dropIndex('player_situations_created_at_index');
        });

        Schema::table('player_micro_actions', function (Blueprint $table) {
            $table->dropIndex('player_micro_actions_player_created_index');
            $table->dropIndex('player_micro_actions_created_at_index');
            $table->dropIndex('player_micro_actions_action_created_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_at_index');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_user_created_index');
            $table->dropIndex('activity_logs_created_at_index');
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $indexes = $connection->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return array_key_exists($index, $indexes);
    }
};

