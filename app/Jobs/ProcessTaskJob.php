<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskBatch;
use App\Services\TaskProcessorResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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
                $task->status !== 'pending'
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

        if($this->task->batch_id)
        {

            DB::transaction(function () {

                $batch = TaskBatch::where('id',$this->task->batch_id)
                    ->lockForUpdate()
                    ->first();


                $batch->increment('completed_tasks');

                $batch->refresh();


                if(
                    $batch->completed_tasks >= $batch->total_tasks
                )
                {
                    $batch->update([
                        'status'=>'completed'
                    ]);
                }

            });

        }
        TaskLog::create([
            'task_id'=>$this->task->id,
            'event'=>'completed',
            'message'=>'Task completed successfully'
        ]);

    }
    public function failed(\Throwable $exception): void
    {

        Log::error($exception);


        $task = Task::find($this->task->id);


        if($task)
        {
            $task->update([
                'status'=>'failed',
                'failed_at'=>now(),
                'error_message'=>'Task processing failed'
            ]);


            TaskLog::create([
                'task_id'=>$task->id,
                'event'=>'failed',
                'message'=>'Task processing failed'
            ]);
        }

    }

}