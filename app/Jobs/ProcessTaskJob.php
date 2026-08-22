<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskLog;
use App\Services\TaskProcessorResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;


class ProcessTaskJob implements ShouldQueue
{

    use Queueable;


    public $tries = 3;

    public $backoff = [10,30,60];


    public function __construct(
        public Task $task
    ) {}


    public function handle(TaskProcessorResolver $resolver)
    {

        $this->task->refresh();


        if($this->task->status === 'cancelled')
        {
            return;
        }


        if($this->task->status === 'completed')
        {
            return;
        }


        $this->task->update([
            'status'=>'processing',
            'started_at'=>now(),
            'attempts'=>$this->task->attempts + 1
        ]);


        TaskLog::create([
            'task_id'=>$this->task->id,
            'event'=>'processing_started',
            'message'=>'Task processing started'
        ]);



        $processor = $resolver->resolve($this->task);

        $processor->process($this->task);


        $this->task->refresh();


        if($this->task->status === 'cancelled')
        {
            return;
        }


        $this->task->update([
            'status'=>'completed',
            'completed_at'=>now()
        ]);


        TaskLog::create([
            'task_id'=>$this->task->id,
            'event'=>'completed',
            'message'=>'Task completed successfully'
        ]);

    }

    public function failed(\Throwable $exception): void
    {

        $task = Task::find($this->task->id);


        if($task)
        {
            $task->update([
                'status'=>'failed',
                'failed_at'=>now(),
                'error_message'=>$exception->getMessage()
            ]);


            TaskLog::create([
                'task_id'=>$task->id,
                'event'=>'failed',
                'message'=>$exception->getMessage()
            ]);
        }

    }

}