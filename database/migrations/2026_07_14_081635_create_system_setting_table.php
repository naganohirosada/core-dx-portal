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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');               // システムを使う企業の名前
            $table->string('registration_number', 14);     // ★インボイス登録番号 (T1234567890123)
            $table->string('postal_code')->nullable();     // 郵便番号
            $table->string('address')->nullable();         // 住所
            $table->string('phone')->nullable();           // 電話番号
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
