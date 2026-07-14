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
        Schema::table('invoices', function (Blueprint $table) {
            // 10%対象
            $table->decimal('subtotal_10_percent', 10, 0)->default(0)->after('amount'); // 10%対象の税抜合計
            $table->decimal('tax_amount_10_percent', 10, 0)->default(0)->after('subtotal_10_percent'); // 10%の消費税額

            // 8%対象（軽減税率）
            $table->decimal('subtotal_8_percent', 10, 0)->default(0)->after('tax_amount_10_percent'); // 8%対象の税抜合計
            $table->decimal('tax_amount_8_percent', 10, 0)->default(0)->after('subtotal_8_percent'); // 8%の消費税額
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_10_percent',
                'tax_amount_10_percent',
                'subtotal_8_percent',
                'tax_amount_8_percent',
            ]);
        });
    }
};
