<?php

namespace App\Services;

use App\Contracts\TaskProcessorInterface;
use App\Models\Task;

class DataProcessingTaskProcessor implements TaskProcessorInterface
{

    public function process(Task $task): void
    {
        sleep(5);

        // simulate data processing
    }

}