<?php

namespace Tests\Feature;

use App\Jobs\ProcessTaskJob;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskProcessingTest extends TestCase
{
    use RefreshDatabase;


    public function test_task_can_be_processed_successfully()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'type' => 'report_generation',
            'status' => 'pending',
            'payload' => [
                'month' => '2026-08'
            ]
        ]);


        ProcessTaskJob::dispatchSync($task);


        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed'
        ]);
    }


    public function test_failed_task_is_marked_failed()
    {
        $user = User::factory()->create();


        $task = Task::factory()->create([
            'user_id' => $user->id,
            'type' => 'report_generation',
            'status' => 'pending',
            'payload' => [
                'force_fail' => true
            ]
        ]);


        try {
            ProcessTaskJob::dispatchSync($task);
        } catch (\Exception $e) {
            // expected failure
        }


        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'failed'
        ]);
    }
}