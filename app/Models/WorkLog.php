<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class WorkLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'task_id',
        'date',
        'hours',
    ];
}