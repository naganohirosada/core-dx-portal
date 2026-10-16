<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Journal extends Model
{
	use BelongsToTenant;

	protected $fillable = ['date', 'description'];
	public function details()
	{
		return $this->hasMany(JournalDetail::class);
	}
}