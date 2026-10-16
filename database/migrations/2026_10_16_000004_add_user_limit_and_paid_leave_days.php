<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            // 契約上の社内ユーザー数上限（基本10ID。追加契約に応じて運営側で変更、最大50）
            $table->unsignedSmallInteger('max_users')->default(10)->after('phone')->comment('社内ユーザー数上限');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('paid_leave_days')->default(10)->after('role')->comment('有給休暇の付与日数');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('paid_leave_days');
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn('max_users');
        });
    }
};
