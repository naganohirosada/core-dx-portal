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
        Schema::create('paid_leaves', function (Blueprint $table) {
            $table->id();
            // 💡 誰の申請か
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->date('start_date')->comment('取得開始日');
            $table->date('end_date')->comment('取得終了日');
            $table->text('reason')->nullable()->comment('取得理由');
            $table->string('status')->default('pending')->comment('状態(pending:承認待ち, approved:承認済, rejected:却下)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_leaves');
    }
};
