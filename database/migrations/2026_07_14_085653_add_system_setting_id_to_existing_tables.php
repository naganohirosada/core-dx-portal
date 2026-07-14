<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 影響を受ける主要テーブルのリスト
        $tables = [
            'users', 
            'companies', 
            'projects', 
            'tasks', 
            'invoices', 
            'attendances', 
            'paid_leaves', 
            'work_logs', 
            'workflows', 
            'events'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // system_settingsのIDと紐づく外部キーを追加。削除時は連動して消える（cascade）
                $table->foreignId('system_setting_id')
                      ->nullable() // 開発初期やシステム管理者のために一旦nullableを推奨
                      ->after('id')
                      ->constrained('system_settings')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'events', 'workflows', 'work_logs', 'paid_leaves', 
            'attendances', 'invoices', 'tasks', 'projects', 'companies', 'users'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['system_setting_id']);
                $table->dropColumn('system_setting_id');
            });
        }
    }
};