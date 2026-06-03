<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'start',
        'end',
        'is_all_day',
        'description',
    ];

    // 💡 キャスト設定（文字列をCarbon/日付オブジェクトとして扱えるようにする）
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'is_all_day' => 'boolean',
    ];
}