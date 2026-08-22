<?php

namespace App\Services;

use App\Models\Task;
use App\Contracts\TaskProcessorInterface;

class TaskProcessorResolver
{

    public function resolve(Task $task): TaskProcessorInterface
    {

        return match($task->type)
        {

        'report_generation'
        => app(ReportTaskProcessor::class),

        'bulk_notification'
        => app(NotificationTaskProcessor::class),

        'data_processing'
        => app(DataProcessingTaskProcessor::class),


        default =>
        throw new \Exception(
        'Unsupported task type'
        )

        };

    }

    }