<?php

namespace App\Services;

use App\Contracts\TaskProcessorInterface;
use App\Models\Task;

class DataProcessingTaskProcessor implements TaskProcessorInterface
{

    public function process(Task $task): void
    {
        \Log::info(
            "DataProcessingTaskProcessor executed for task ".$task->id
        );

        sleep(5);
    }

}