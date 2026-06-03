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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // 💡 顧客テーブルへの外部キー（親が消えたら消えるcascade設定）
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            $table->string('name')->comment('案件名');
            $table->date('start_date')->nullable()->comment('開始日');
            $table->date('end_date')->nullable()->comment('終了日');
            $table->unsignedInteger('budget')->nullable()->comment('案件予算');
            $table->string('status')->default('ongoing')->comment('ステータス(lead, ongoing, completed, suspended)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
