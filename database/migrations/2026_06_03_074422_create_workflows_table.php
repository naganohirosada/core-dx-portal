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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('type')->comment('申請種別(expense:経費精算, ringi:稟議申請)');
            $table->string('title')->comment('申請タイトル');
            $table->integer('amount')->nullable()->comment('金額（経費や購入稟議用）');
            $table->text('description')->nullable()->comment('詳細・理由');
            $table->string('status')->default('pending')->comment('状態(pending, approved, rejected)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
