<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Account extends Model
{
	use BelongsToTenant;

	protected $fillable = ['code', 'name', 'type'];
	
	public function journalDetails()
	{
		return $this->hasMany(JournalDetail::class);
	}
}