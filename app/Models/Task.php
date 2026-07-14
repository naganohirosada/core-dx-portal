<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Task extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'project_id',
        'title',
        'status',
        'due_date',
    ];
}
