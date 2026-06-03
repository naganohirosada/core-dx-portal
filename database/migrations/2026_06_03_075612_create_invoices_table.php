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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            // 💡 どの案件に対する請求か
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            
            $table->string('invoice_number')->unique()->comment('請求書番号');
            $table->date('issue_date')->comment('発行日');
            $table->date('due_date')->comment('支払期日');
            $table->integer('amount')->comment('請求金額');
            $table->string('status')->default('draft')->comment('状態(draft:下書き, sent:送付済, paid:入金済)');
            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
