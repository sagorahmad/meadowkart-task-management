<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskLog;
use App\Services\TaskProcessorResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;


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

        $claimed = DB::transaction(function () {

            $task = Task::where('id', $this->task->id)
                ->lockForUpdate()
                ->first();


            if(
                !$task ||
                $task->status === 'cancelled' ||
                $task->status === 'completed' 
            ){
                return false;
            }


            $task->update([
                'status'=>'processing',
                'started_at'=>now(),
                'attempts'=>$task->attempts + 1
            ]);


            return true;

        });



        if(!$claimed)
        {
            return;
        }

        $this->task = Task::find($this->task->id);

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
            TaskLog::create([
                'task_id'=>$this->task->id,
                'event'=>'processing_cancelled',
                'message'=>'Task was cancelled during processing'
            ]);

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