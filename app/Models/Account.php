<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
	protected $fillable = ['code', 'name', 'type'];
	
	public function journalDetails()
	{
		return $this->hasMany(JournalDetail::class);
	}
}