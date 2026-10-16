<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 会計データ（勘定科目・仕訳・仕訳明細）を会社（テナント）単位に分離する。
 * あわせて、会社ごとに重複してよい値（科目コード・請求書番号）の一意制約を会社単位に変更する。
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['accounts', 'journals', 'journal_details'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('system_setting_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('system_settings')
                    ->onDelete('cascade');
            });
        }

        // 科目コードは会社ごとに同じコード（例: 4111 売上高）を持てるようにする
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique('accounts_code_unique');
            $table->unique(['system_setting_id', 'code']);
        });

        // 請求書番号（INV-日付-連番）も会社ごとの採番のため、会社単位で一意にする
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_invoice_number_unique');
            $table->unique(['system_setting_id', 'invoice_number']);
        });

        // 既存データの移行：会社が1社しか存在しない場合のみ、その会社に紐づける。
        // 複数社ある場合は帰属先を判定できないため、NULLのまま残す（手動での割り当てが必要）。
        if (DB::table('system_settings')->count() === 1) {
            $settingId = DB::table('system_settings')->value('id');
            foreach (['accounts', 'journals', 'journal_details'] as $tableName) {
                DB::table($tableName)->whereNull('system_setting_id')->update(['system_setting_id' => $settingId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['system_setting_id', 'invoice_number']);
            $table->unique('invoice_number');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['system_setting_id', 'code']);
            $table->unique('code');
        });

        foreach (['journal_details', 'journals', 'accounts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['system_setting_id']);
                $table->dropColumn('system_setting_id');
            });
        }
    }
};
