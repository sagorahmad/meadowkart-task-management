<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskBatch extends Model
{

    protected $fillable = [
        'user_id',
        'total_tasks',
        'completed_tasks',
        'failed_tasks',
        'status'
    ];


    public function tasks()
    {
        return $this->hasMany(Task::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}