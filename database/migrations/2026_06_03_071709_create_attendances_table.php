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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // 💡 誰の勤怠か（Userテーブルと紐付け）
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->date('date')->comment('出勤日(YYYY-MM-DD)');
            $table->time('clock_in')->nullable()->comment('出勤時刻');
            $table->time('clock_out')->nullable()->comment('退勤時刻');
            $table->time('break_start')->nullable()->comment('休憩開始時刻');
            $table->time('break_end')->nullable()->comment('休憩終了時刻');
            $table->string('status')->default('未出勤')->comment('状態(未出勤, 勤務中, 休憩中, 退勤済)');
            $table->timestamps();
            
            // 💡 同じ人が同じ日に2つレコードを作らないようにユニーク制約
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
