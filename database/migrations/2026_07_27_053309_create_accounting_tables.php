<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void
{
// 勘定科目マスタ
Schema::create('accounts', function (Blueprint $table) {
	$table->id();
	$table->string('code')->unique(); // 例: 1111
	$table->string('name'); // 例: 現金
	$table->enum('type', ['asset', 'liability', 'equity',

	'revenue', 'expense']); // 5大区分
	$table->timestamps();
});
// 仕訳帳(伝票ヘッダー)
Schema::create('journals', function (Blueprint $table) {
	$table->id();
	$table->date('date'); // 取引日
	$table->string('description'); // 摘要
	$table->timestamps();
});
// 仕訳明細(貸借の一致を記録)
Schema::create('journal_details', function (Blueprint $table)

{

	$table->id();

	$table->foreignId('journal_id')->constrained()->cascadeOnDelete();
	$table->foreignId('account_id')->constrained();
	$table->enum('side', ['debit', 'credit']); // 借方(debit) /貸方(credit)

	$table->decimal('amount', 15, 2);
	$table->timestamps();

});
}
public function down(): void
{
	Schema::dropIfExists('journal_details');
	Schema::dropIfExists('journals');
	Schema::dropIfExists('accounts');
}
};