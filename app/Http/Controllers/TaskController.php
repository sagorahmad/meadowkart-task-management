<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Http\Request;
use App\Jobs\ProcessTaskJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{

    public function index(Request $request)
    {
        $query = Task::where(
            'user_id',
            $request->user()->id
        );


        if($request->filled('status'))
        {
            $query->where(
                'status',
                $request->status
            );
        }


        if($request->filled('type'))
        {
            $query->where(
                'type',
                $request->type
            );
        }


        if($request->filled('priority'))
        {
            $query->where(
                'priority',
                $request->priority
            );
        }


        if($request->filled('search'))
        {
            $query->where(
                'title',
                'ILIKE',
                '%'.$request->search.'%'
            );
        }


        if($request->filled('from'))
        {
            $query->where(
                'created_at',
                '>=',
                Carbon::parse($request->from)->startOfDay()
            );
        }


        if($request->filled('to'))
        {
            $query->where(
                'created_at',
                '<=',
                Carbon::parse($request->to)->endOfDay()
            );
        }


       $allowedSorts = [
        'created_at',
        'updated_at',
        'priority',
        'status'
    ];


    $sort = $request->get('sort','created_at');

    if(!in_array($sort,$allowedSorts))
    {
        $sort = 'created_at';
    }


    $direction = $request->get('direction','desc');

    if(!in_array($direction,['asc','desc']))
    {
        $direction = 'desc';
    }


    return $query
    ->orderBy($sort,$direction)
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
            'type'=>[
                'required',
                'string',
                'in:report_generation,bulk_notification,data_processing'
            ],
            'title'=>'required|string',
            'payload'=>'nullable|array',
            'priority'=>'nullable|in:low,normal,high,critical'
        ]);


        DB::transaction(function () use ($request, $data, &$task) {

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


        TaskLog::create([
            'task_id'=>$task->id,
            'event'=>'queued',
            'message'=>'Task added to queue'
        ]);

    });


    ProcessTaskJob::dispatch($task)
    ->onQueue($task->priority);

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

        TaskLog::create([
            'task_id'=>$task->id,
            'event'=>'retry_attempt',
            'message'=>'Retry attempt #'.($task->attempts + 1)
        ]);
        $task->update([
            'status'=>'pending',
            'attempts'=>0,
            'error_message'=>null,
            'failed_at'=>null
        ]);


        ProcessTaskJob::dispatch($task)->onQueue($task->priority);

        return response()->json([
            'message'=>'Task queued for retry',
            'task'=>$task
        ]);
    }


}