<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Jobs\ProcessTaskJob;
use App\Models\TaskLog;
use Illuminate\Console\Command;

class CleanupStaleTasks extends Command
{
    protected $signature = 'tasks:cleanup-stale';

    protected $description = 'Mark stale processing tasks as failed and retry them';

    public function handle(): int
    {

        $tasks = Task::where('status', 'processing')
            ->where('started_at', '<', now()->subMinutes(30))
            ->get();


        foreach($tasks as $task)
        {

            $task->update([
                'status'=>'failed',
                'failed_at'=>now(),
                'error_message'=>'Task timeout after 30 minutes'
            ]);


            TaskLog::create([
                'task_id'=>$task->id,
                'event'=>'stale_cleanup',
                'message'=>'Task marked failed due to timeout'
            ]);


            $task->update([
                'status'=>'pending',
                'attempts'=>0,
                'error_message'=>null,
                'failed_at'=>null
            ]);


            TaskLog::create([
                'task_id'=>$task->id,
                'event'=>'automatic_retry',
                'message'=>'Automatic retry after stale cleanup'
            ]);


            ProcessTaskJob::dispatch($task)
                ->onQueue($task->priority);


            $this->info(
                "Retried task ID: ".$task->id
            );
        }


        return Command::SUCCESS;
    }
}