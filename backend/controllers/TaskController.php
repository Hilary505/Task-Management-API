<?php

require_once __DIR__ . '/../app/Database.php';

class TaskController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // POST /api/tasks
    public function create(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $title    = trim($data['title'] ?? '');
        $due_date = trim($data['due_date'] ?? '');
        $priority = trim($data['priority'] ?? '');

        // Validate required fields
        if (!$title || !$due_date || !$priority) {
            $this->respond(['error' => 'title, due_date, and priority are required.'], 422);
        }

        // Validate priority
        if (!in_array($priority, ['low', 'medium', 'high'])) {
            $this->respond(['error' => 'priority must be low, medium, or high.'], 422);
        }

        // Validate due_date is today or later
        if ($due_date < date('Y-m-d')) {
            $this->respond(['error' => 'due_date must be today or a future date.'], 422);
        }

        // Check duplicate title + due_date
        $stmt = $this->db->prepare('SELECT id FROM tasks WHERE title = ? AND due_date = ?');
        $stmt->execute([$title, $due_date]);
        if ($stmt->fetch()) {
            $this->respond(['error' => 'A task with the same title and due_date already exists.'], 422);
        }

        $stmt = $this->db->prepare(
            'INSERT INTO tasks (title, due_date, priority) VALUES (?, ?, ?)'
        );
        $stmt->execute([$title, $due_date, $priority]);

        $task = $this->findById((int) $this->db->lastInsertId());
        $this->respond($task, 201);
    }

    // GET /api/tasks
    public function index(): void
    {
        $status = $_GET['status'] ?? null;
        $validStatuses = ['pending', 'in_progress', 'done'];

        $sql = 'SELECT * FROM tasks';
        $params = [];

        if ($status !== null) {
            if (!in_array($status, $validStatuses)) {
                $this->respond(['error' => 'status must be pending, in_progress, or done.'], 422);
            }
            $sql .= ' WHERE status = ?';
            $params[] = $status;
        }

        $sql .= " ORDER BY FIELD(priority, 'high', 'medium', 'low'), due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();

        if (empty($tasks)) {
            $this->respond(['message' => 'No tasks found.', 'data' => []]);
        }

        $this->respond($tasks);
    }

    // PATCH /api/tasks/{id}/status
    public function updateStatus(int $id): void
    {
        $task = $this->findById($id);
        if (!$task) {
            $this->respond(['error' => 'Task not found.'], 404);
        }

        $data       = json_decode(file_get_contents('php://input'), true);
        $newStatus  = trim($data['status'] ?? '');

        $transitions = [
            'pending'     => 'in_progress',
            'in_progress' => 'done',
        ];

        if (!isset($transitions[$task['status']]) || $transitions[$task['status']] !== $newStatus) {
            $this->respond([
                'error' => "Invalid status transition. '{$task['status']}' can only move to '{$transitions[$task['status']]}'."
            ], 422);
        }

        $this->db->prepare('UPDATE tasks SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
        $this->respond($this->findById($id));
    }

    // DELETE /api/tasks/{id}
    public function delete(int $id): void
    {
        $task = $this->findById($id);
        if (!$task) {
            $this->respond(['error' => 'Task not found.'], 404);
        }

        if ($task['status'] !== 'done') {
            $this->respond(['error' => 'Only tasks with status "done" can be deleted.'], 403);
        }

        $this->db->prepare('DELETE FROM tasks WHERE id = ?')->execute([$id]);
        $this->respond(['message' => 'Task deleted successfully.']);
    }

    // GET /api/tasks/report?date=YYYY-MM-DD
    public function report(): void
    {
        $date = $_GET['date'] ?? '';

        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->respond(['error' => 'A valid date query parameter (YYYY-MM-DD) is required.'], 422);
        }

        $stmt = $this->db->prepare(
            'SELECT priority, status, COUNT(*) as count FROM tasks WHERE due_date = ? GROUP BY priority, status'
        );
        $stmt->execute([$date]);
        $rows = $stmt->fetchAll();

        $summary = [];
        foreach (['high', 'medium', 'low'] as $p) {
            $summary[$p] = ['pending' => 0, 'in_progress' => 0, 'done' => 0];
        }

        foreach ($rows as $row) {
            $summary[$row['priority']][$row['status']] = (int) $row['count'];
        }

        $this->respond(['date' => $date, 'summary' => $summary]);
    }

    private function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function respond(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}
