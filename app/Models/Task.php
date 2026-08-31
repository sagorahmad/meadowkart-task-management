<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'title',
        'payload',
        'status',
        'priority',
        'attempts',
        'started_at',
        'completed_at',
        'failed_at',
        'error_message',
        'user_id',
        'batch_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }
    public function batch(): BelongsTo
    {
        return $this->belongsTo(TaskBatch::class);
    }
}