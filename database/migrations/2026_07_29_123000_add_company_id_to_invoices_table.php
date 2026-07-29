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
        Schema::table('invoices', function (Blueprint $table) {
            // まずは nullable で追加して既存データのバッチ更新を安全に行う
            $table->foreignId('company_id')
                  ->nullable()
                  ->after('project_id')
                  ->constrained('companies')
                  ->onDelete('cascade');
        });

        // 既存の invoices が project_id を持っていれば projects から company_id をコピー
        DB::statement("UPDATE invoices i JOIN projects p ON i.project_id = p.id SET i.company_id = p.company_id WHERE i.company_id IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
