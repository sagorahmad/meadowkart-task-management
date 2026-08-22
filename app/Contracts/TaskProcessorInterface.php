<?php

namespace App\Contracts;

use App\Models\Task;

interface TaskProcessorInterface
{
    public function process(Task $task): void;
}