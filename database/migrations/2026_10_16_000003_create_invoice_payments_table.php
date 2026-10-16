<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 入金消込の履歴（1請求書に対して複数回の一部入金を記録できる）
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_setting_id')->nullable()->constrained('system_settings')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->date('payment_date')->comment('入金日');
            $table->integer('amount')->comment('入金額');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
