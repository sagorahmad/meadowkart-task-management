# API Documentation

Base URL:

```
http://localhost:8000/api
```

Authentication is handled using Laravel Sanctum.

Protected endpoints require:

```
Authorization: Bearer {token}
```

---

# 1. Authentication

## Register User

Creates a new user account.

### Request

```
POST /register
```

### Body

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password"
}
```

### Response

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

---

## Login User

Authenticates a user and returns an access token.

### Request

```
POST /login
```

### Body

```json
{
    "email": "john@example.com",
    "password": "password"
}
```

### Response

```json
{
    "token": "1|xxxxxxxxxxxx"
}
```

Use this token for protected APIs.

Example:

```
Authorization: Bearer 1|xxxxxxxxxxxx
```

---

# 2. Task Management

## Create Task

Creates a task and dispatches it to the queue.

### Request

```
POST /tasks
```

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body Example

```json
{
    "type": "report_generation",
    "title": "Monthly Report",
    "priority": "high",
    "payload": {
        "month": "2026-08"
    }
}
```

### Supported Task Types

```
report_generation
bulk_notification
data_processing
```

### Priority Levels

```
critical
high
normal
low
```

### Response

```json
{
    "id": 1,
    "status": "pending",
    "message": "Task queued successfully"
}
```

---

# List Tasks

Returns tasks belonging only to the authenticated user.

### Request

```
GET /tasks
```

### Response

```json
[
    {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Monthly Report",
                "status": "completed",
                "priority": "high"
            }
        ],
        "total": 1
    }
]
```

---

# View Single Task

Returns details of a specific task.

### Request

```
GET /tasks/{id}
```

Example:

```
GET /tasks/1
```

### Response

```json
{
    "id": 1,
    "type": "report_generation",
    "title": "Monthly Report",
    "status": "completed",
    "attempts": 1,
    "started_at": "2026-08-23 10:00:00",
    "completed_at": "2026-08-23 10:05:00"
}
```

A user cannot access another user's task.

---

# Cancel Task

Cancels a pending task.

### Request

```
POST /tasks/{id}/cancel
```

Example:

```
POST /tasks/1/cancel
```

### Response

```json
{
    "message": "Task cancelled successfully"
}
```

---

# Retry Failed Task

Retries a failed task.

### Request

```
POST /tasks/{id}/retry
```

Example:

```
POST /tasks/1/retry
```

### Response

```json
{
    "message": "Task retry queued successfully"
}
```

---

# 3. Batch Processing

Batch processing allows multiple tasks to be executed together.

Example:

```
Batch
 |
 ├── Task 1
 ├── Task 2
 ├── Task 3
```

---

## Create Batch

Creates multiple tasks under one batch.

### Request

```
POST /batches
```

### Body

```json
{
    "tasks": [
        {
            "type": "report_generation",
            "title": "Generate Report",
            "priority": "high",
            "payload": {
                "month": "2026-08"
            }
        },
        {
            "type": "bulk_notification",
            "title": "Send Notification",
            "priority": "normal",
            "payload": {
                "recipients": ["user@test.com"],
                "message": "Report ready"
            }
        }
    ]
}
```

### Response

```json
{
    "batch_id": 1,
    "message": "Batch created successfully"
}
```

---

## View Batch Progress

Returns batch execution progress.

### Request

```
GET /batches/{id}
```

Example:

```
GET /batches/1
```

### Response

```json
{
    "id": 1,
    "total_tasks": 2,
    "completed_tasks": 2,
    "progress": 100,
    "status": "completed"
}
```

---

# 4. Task Status Flow

Tasks follow this lifecycle:

```
pending
   |
   ↓
processing
   |
   ↓
completed
```

If execution fails:

```
pending
   |
   ↓
processing
   |
   ↓
failed
```

Failed tasks can be retried.

---

# 5. Queue Processing

Tasks are processed asynchronously using:

```
Laravel Queue
        |
        ↓
Redis
        |
        ↓
Laravel Horizon Worker
```

Workers can be started using:

```
php artisan horizon
```

---

# 6. Error Responses

## Unauthorized

HTTP 401

```json
{
    "message": "Unauthenticated."
}
```

## Forbidden

HTTP 403

```json
{
    "message": "Forbidden"
}
```

## Validation Error

HTTP 422

```json
{
    "message": "The given data was invalid."
}
```

---

# 7. API Summary

| Method | Endpoint           | Description         |
| ------ | ------------------ | ------------------- |
| POST   | /register          | Create user         |
| POST   | /login             | Login user          |
| POST   | /tasks             | Create task         |
| GET    | /tasks             | List tasks          |
| GET    | /tasks/{id}        | View task           |
| POST   | /tasks/{id}/cancel | Cancel task         |
| POST   | /tasks/{id}/retry  | Retry task          |
| POST   | /batches           | Create batch        |
| GET    | /batches/{id}      | View batch progress |
