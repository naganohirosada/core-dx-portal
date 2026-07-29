<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('unit_price')->default(0)->comment('税抜きの単価（円）');
            $table->tinyInteger('tax_rate')->default(10)->comment('税率（8または10）');
            $table->integer('subtotal')->default(0)->comment('税抜き小計（quantity * unit_price）');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
