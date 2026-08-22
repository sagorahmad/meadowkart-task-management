<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Http\Request;
use App\Jobs\ProcessTaskJob;

class TaskController extends Controller
{

    public function index(Request $request)
    {
        return Task::where('user_id',$request->user()->id)
            ->latest()
            ->paginate(10);
    }


    public function show(Request $request, Task $task)
    {
        abort_if($task->user_id !== $request->user()->id,403);

        return $task;
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'type'=>'required|string',
            'title'=>'required|string',
            'payload'=>'nullable|array',
            'priority'=>'nullable|in:low,normal,high,critical'
        ]);


        $task = Task::create([

            'user_id'=>$request->user()->id,
            'type'=>$data['type'],
            'title'=>$data['title'],
            'payload'=>$data['payload'] ?? null,
            'priority'=>$data['priority'] ?? 'normal',
            'status'=>'pending'

        ]);

        TaskLog::create([
            'task_id'=>$task->id,
            'event'=>'created',
            'message'=>'Task created'
        ]);
        ProcessTaskJob::dispatch($task);


        return response()->json([
            'id'=>$task->id,
            'status'=>$task->status,
            'message'=>'Task queued successfully'
        ],201);
    }
    public function cancel(Request $request, Task $task)
    {
        abort_if($task->user_id !== $request->user()->id,403);


        if(in_array($task->status, [
            'completed',
            'failed',
            'cancelled'
        ]))
        {
            return response()->json([
                'message'=>'Task cannot be cancelled'
            ],400);
        }


        $task->update([
            'status'=>'cancelled'
        ]);
        TaskLog::create([
            'task_id'=>$task->id,
            'event'=>'cancelled',
            'message'=>'Task cancelled by user'
        ]);

        return response()->json([
            'message'=>'Task cancelled successfully',
            'task'=>$task
        ]);
    }


    public function retry(Request $request, Task $task)
    {
        abort_if($task->user_id !== $request->user()->id,403);

        if($task->status !== 'failed')
        {
            return response()->json([
                'message'=>'Only failed tasks can be retried'
            ],400);
        }


        $task->update([
            'status'=>'pending',
            'attempts'=>0,
            'error_message'=>null
        ]);

        ProcessTaskJob::dispatch($task);
        return response()->json([
            'message'=>'Task queued for retry',
            'task'=>$task
        ]);
    }


}