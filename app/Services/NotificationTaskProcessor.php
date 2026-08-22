<?php

namespace App\Services;

use App\Contracts\TaskProcessorInterface;
use App\Models\Task;

class NotificationTaskProcessor implements TaskProcessorInterface
{

    public function process(Task $task): void
    {
        sleep(5);

        // simulate sending notifications
    }

}