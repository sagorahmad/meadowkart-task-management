<?php

namespace App\Services;

use App\Contracts\TaskProcessorInterface;
use App\Models\Task;

class ReportTaskProcessor implements TaskProcessorInterface
{

    public function process(Task $task): void
    {
        \Log::info(
            "ReportTaskProcessor executed for task ".$task->id
        );

        sleep(60);
    }
}