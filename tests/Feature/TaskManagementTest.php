<?php

namespace Tests\Feature;

use App\Jobs\ProcessTaskJob;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_create_task()
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', [
                'type' => 'report_generation',
                'title' => 'Monthly Report',
                'priority' => 'high',
                'payload' => [
                    'month' => '2026-08'
                ]
            ]);


        $response->assertStatus(201);


        $this->assertDatabaseHas('tasks', [
            'title' => 'Monthly Report',
            'status' => 'pending',
            'priority' => 'high'
        ]);


        Queue::assertPushed(ProcessTaskJob::class);
    }


    public function test_user_can_list_only_their_tasks()
    {
        $user = User::factory()->create();

        Task::factory()->create([
            'user_id'=>$user->id
        ]);


        $response = $this
            ->actingAs($user,'sanctum')
            ->getJson('/api/tasks');


        $response->assertStatus(200);

        $response->assertJsonCount(1,'data');
    }


    public function test_user_cannot_access_other_users_task()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();


        $task = Task::factory()->create([
            'user_id'=>$user2->id
        ]);


        $response = $this
            ->actingAs($user1,'sanctum')
            ->getJson('/api/tasks/'.$task->id);


        $response->assertStatus(403);
    }


    public function test_user_can_cancel_pending_task()
    {
        $user = User::factory()->create();


        $task = Task::factory()->create([
            'user_id'=>$user->id,
            'status'=>'pending'
        ]);


        $response = $this
            ->actingAs($user,'sanctum')
            ->postJson('/api/tasks/'.$task->id.'/cancel');


        $response->assertStatus(200);


        $this->assertDatabaseHas('tasks',[
            'id'=>$task->id,
            'status'=>'cancelled'
        ]);
    }


    public function test_user_can_retry_failed_task()
    {
        Queue::fake();

        $user = User::factory()->create();


        $task = Task::factory()->create([
            'user_id'=>$user->id,
            'status'=>'failed'
        ]);


        $response = $this
            ->actingAs($user,'sanctum')
            ->postJson('/api/tasks/'.$task->id.'/retry');


        $response->assertStatus(200);


        $this->assertDatabaseHas('tasks',[
            'id'=>$task->id,
            'status'=>'pending'
        ]);


        Queue::assertPushed(ProcessTaskJob::class);
    }
}