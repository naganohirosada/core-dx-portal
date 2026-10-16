<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            // 経費精算の経費区分（承認時の自動仕訳で借方科目を決めるために使う）
            $table->string('expense_category')->nullable()->after('type')->comment('経費区分(travel, supplies, communication, meeting, misc)');
        });
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn('expense_category');
        });
    }
};
