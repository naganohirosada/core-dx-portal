<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalDetail extends Model
{
	protected $fillable = ['journal_id', 'account_id', 'side',
	'amount'];
	public function account()
	{
	return $this->belongsTo(Account::class);
	}
}