<?php

namespace App\Services;

use App\Contracts\TaskProcessorInterface;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class BulkNotificationTaskProcessor implements TaskProcessorInterface
{

    public function process(Task $task): void
    {
        $recipients = $task->payload['recipients'] ?? [];

        $message = $task->payload['message'] ?? '';


        Log::info([
            'task_id' => $task->id,
            'total_recipients' => count($recipients),
            'message' => 'Bulk notification started'
        ]);


        foreach($recipients as $recipient)
        {
            // simulate sending notification

            Log::info([
                'task_id' => $task->id,
                'recipient' => $recipient,
                'notification_message' => $message,
                'message' => 'Notification sent'
            ]);

            sleep(1);
        }


        Log::info([
            'task_id' => $task->id,
            'message' => 'Bulk notification completed'
        ]);
    }

}