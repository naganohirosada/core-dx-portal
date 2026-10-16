<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class JournalDetail extends Model
{
	use BelongsToTenant;

	protected $fillable = ['journal_id', 'account_id', 'side',
	'amount'];
	public function account()
	{
	return $this->belongsTo(Account::class);
	}

	public function journal()
	{
		return $this->belongsTo(Journal::class);
	}
}