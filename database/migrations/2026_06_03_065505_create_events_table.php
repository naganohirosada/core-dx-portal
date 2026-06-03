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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            // 💡 案件に紐づく予定（任意）
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title')->comment('予定タイトル');
            $table->dateTime('start')->comment('開始日時');
            $table->dateTime('end')->nullable()->comment('終了日時');
            $table->boolean('is_all_day')->default(false)->comment('終日フラグ');
            $table->text('description')->nullable()->comment('概要・メモ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
