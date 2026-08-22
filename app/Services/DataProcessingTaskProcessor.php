<?php

namespace App\Services;

use App\Contracts\TaskProcessorInterface;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class DataProcessingTaskProcessor implements TaskProcessorInterface
{

    public function process(Task $task): void
    {
        $records = $task->payload['records'] ?? 0;

        $chunkSize = 1000;

        Log::info([
            'task_id' => $task->id,
            'records' => $records,
            'message' => 'Data processing started'
        ]);


        for ($processed = 0; $processed < $records; $processed += $chunkSize) {

            $currentChunk = min($chunkSize, $records - $processed);


            // simulate processing this chunk
            sleep(1);


            Log::info([
                'task_id' => $task->id,
                'processed' => $processed + $currentChunk,
                'message' => 'Chunk processed'
            ]);
        }


        Log::info([
            'task_id' => $task->id,
            'message' => 'Data processing completed'
        ]);
    }

}