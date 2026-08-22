<?php

namespace App\Services;

use App\Contracts\TaskProcessorInterface;
use App\Models\Task;

class NotificationTaskProcessor implements TaskProcessorInterface
{

    public function process(Task $task): void
    {
        \Log::info(
            "NotificationTaskProcessor executed for task ".$task->id
        );

        sleep(5);
    }

}