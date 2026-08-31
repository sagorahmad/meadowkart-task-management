# Meadowkart Task Management System

## Installation

Clone the repository:

```bash
git clone https://github.com/sagorahmad/meadowkart-task-management.git

cd meadowkart-task-management
```

Create environment file:

```bash
cp .env.example .env
```

Start Docker containers:

```bash
docker compose up -d --build
```

Install PHP dependencies:

```bash
docker compose run --rm app composer install
```

Restart containers:

```bash
docker compose up -d
```

Generate application key:

```bash
docker compose exec app php artisan key:generate
```

---

# Database Migration

Run migrations:

```bash
docker compose exec app php artisan migrate
```

Database:

-   PostgreSQL
-   Redis

---

# Start Queue Worker

The application uses Laravel Horizon with Redis queues.

Worker starts automatically through Docker.

Check worker status:

```bash
docker compose exec worker php artisan horizon:status
```

Expected:

```
Horizon is running.
```

---

# Run Tests

Run:

```bash
docker compose exec app php artisan test
```

Tests cover:

-   Task creation
-   Task authorization
-   Task cancellation
-   Task retry
-   Task processing success
-   Task processing failure

---

# Architecture Decisions

Task processing follows a processor-based design.

The queue job does not contain task-specific logic.

Flow:

```
ProcessTaskJob
        |
        ↓
TaskProcessorResolver
        |
        ↓
TaskProcessorInterface
        |
        ↓
--------------------------------
ReportTaskProcessor

BulkNotificationTaskProcessor

DataProcessingTaskProcessor
```

New task types can be added by creating a new processor without modifying the core job logic.

---

# Queue Strategy

Tasks are processed asynchronously using Laravel Queue and Redis.

Flow:

```
API Request

    ↓

Create Task

    ↓

Dispatch Job

    ↓

Redis Queue

    ↓

Horizon Worker

    ↓

Task Processor

    ↓

Update Status
```

Workers consume queues based on priority:

critical

high

normal

low

Laravel Horizon manages Redis queue workers and monitoring.

Priority queues are supported:

```
critical
high
normal
low
```

---

# Retry Strategy

Failed jobs are retried automatically.

Configuration:

```php
tries = 3

backoff = [10,30,60]
```

Retry flow:

```
processing
     ↓
failed
     ↓
retry
     ↓
processing
     ↓
completed
```

After maximum retries:

-   Task status becomes failed
-   Error message is stored
-   Failure log is created

---

# Idempotency Strategy

The system prevents duplicate task processing using:

-   Database transactions
-   Row-level locking (`lockForUpdate`)
-   Task status validation

Before processing, a task must be in `pending` state.

Once claimed by a worker, the task changes to `processing`, preventing concurrent workers from processing the same task.

For external side effects such as emails or third-party APIs, a production system could additionally use idempotency keys and operation logs to guarantee exactly-once execution.

# Concurrency Considerations

Multiple workers can process tasks simultaneously.

To avoid race conditions:

-   Database transactions are used
-   Row-level locking prevents duplicate task claiming
-   Task state transitions are controlled

Example:

```
pending
   ↓
processing
   ↓
completed
```

A completed task cannot be processed again.

---

# Observability

Task execution events are stored in `task_logs`.

Tracked events:

-   Task created
-   Task queued
-   Processing started
-   Retry attempted
-   Task failed
-   Task completed
-   Task cancelled

# Security

Implemented:

-   Laravel Sanctum authentication
-   Task ownership validation
-   User data isolation
-   Request validation
-   Rate limiting

# Known Limitations

-   Real-time progress updates using WebSockets are not implemented.
-   Batch progress tracks completed tasks but does not expose individual task percentage.
-   Advanced scheduling rules can be added in future.

---

# Additional Features Implemented

## Bonus 1 — Laravel Horizon

Implemented Laravel Horizon for queue monitoring and worker management.

## Bonus 2 — Scheduled Tasks

Implemented automatic cleanup and retry of stale tasks.

## Bonus 3 — Rate Limiting

Implemented rate limiting to prevent excessive task creation requests.

## Bonus 4 — Batch Processing

Implemented batch task creation with batch-level progress tracking.

---

# API Documentation

API documentation is available in:

```
API.md
```

It contains:

-   Authentication APIs
-   Task APIs
-   Batch APIs
-   Request examples
-   Response examples
