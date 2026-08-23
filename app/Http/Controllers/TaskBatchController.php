<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskBatch;
use App\Jobs\ProcessTaskJob;
use Illuminate\Http\Request;

class TaskBatchController extends Controller
{


    public function store(Request $request)
    {

        $data=$request->validate([

            'tasks'=>'required|array',

        ]);


        $batch=TaskBatch::create([

        'user_id'=>$request->user()->id,

        'total_tasks'=>count($data['tasks'])

        ]);


        foreach($data['tasks'] as $taskData)
        {


            $task = Task::create([

                'user_id'=>$request->user()->id,

                'batch_id'=>$batch->id,

                'type'=>$taskData['type'],

                'title'=>$taskData['title'],

                'payload'=>$taskData['payload'] ?? null,

                'priority'=>$taskData['priority'] ?? 'normal',

                'status'=>'pending'

            ]);

            ProcessTaskJob::dispatch($task)->onQueue($task->priority);


        }


        return response()->json([

            'batch_id'=>$batch->id,

            'message'=>'Batch created successfully'

        ],201);


    }



    public function show(TaskBatch $batch)
    {

        abort_if(
            $batch->user_id !== request()->user()->id,
            403
        );


        return response()->json([

            'id'=>$batch->id,

            'total_tasks'=>$batch->total_tasks,

            'completed_tasks'=>$batch->completed_tasks,

            'progress'=>

            round(
                ($batch->completed_tasks / $batch->total_tasks)*100
            ),

            'status'=>$batch->status

        ]);

    }


}