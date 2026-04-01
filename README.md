# Task-Management-API

Basic Task Management API built with pure PHP and MySQL.

## Requirements
- PHP 8.1+
- MySQL 5.7+

## Setup

### 1. Run the migration
```bash
sudo mysql < migrations/create_tasks_table.sql
```

### 2. Configure the database
Edit `config/database.php` or set environment variables:
```
DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
```

### 3. Start the server
```bash
php -S localhost:8000 -t backend/public
```
* The server runs at port 8000, paste ```localhost:8000``` on browser to access the frontend.
---

## API Endpoints

### Create Task
```
POST /api/tasks
Body: { "title": "Fix bug", "due_date": "2026-04-01", "priority": "high" }
```
use curl 

```bash
# Create
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Fix bug","due_date":"2026-12-01","priority":"high"}'

# List
curl http://localhost:8000/api/tasks

# Update status
curl -X PATCH http://localhost:8000/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'

# Delete (only works after status is "done")
curl -X DELETE http://localhost:8000/api/tasks/1
```
### List Tasks
```
GET /api/tasks
GET /api/tasks?status=pending
```
Sorted by priority (high → low), then due_date ascending.

### Update Task Status
```
PATCH /api/tasks/{id}/status
Body: { "status": "in_progress" }
```
Allowed transitions: `pending → in_progress → done`

### Delete Task
```
DELETE /api/tasks/{id}
```
Only `done` tasks can be deleted.

### Daily Report
```
GET /api/tasks/report?date=2026-03-28
```
Returns counts per priority and status for the given due_date.
