<?php
declare(strict_types=1);

// ---- CORS (only needed when the frontend runs on a different origin, e.g. `npm run dev`) ----
$allowedOrigin = getenv('CORS_ORIGIN') ?: 'http://localhost:5173';
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(int $status, $data = null): void
{
    http_response_code($status);
    if ($data !== null) {
        echo json_encode($data);
    }
    exit;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            getenv('DB_HOST') ?: 'db',
            getenv('DB_PORT') ?: '5432',
            getenv('DB_NAME') ?: 'tasks'
        );
        $pdo = new PDO($dsn, getenv('DB_USER') ?: 'tasks', getenv('DB_PASSWORD') ?: '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function jsonBody(): array
{
    $data = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($data)) {
        respond(400, ['error' => 'Request body must be valid JSON']);
    }
    return $data;
}

function castTask(array $row): array
{
    $row['id'] = (int) $row['id'];
    $row['completed'] = (bool) $row['completed'];
    return $row;
}

// ---- Routing ----
$method = $_SERVER['REQUEST_METHOD'];
$path   = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

try {
    if ($path === '/api/health' && $method === 'GET') {
        db()->query('SELECT 1');
        respond(200, ['status' => 'ok']);
    }

    if ($path === '/api/tasks' && $method === 'GET') {
        $rows = db()->query('SELECT id, title, completed, created_at FROM tasks ORDER BY id DESC')->fetchAll();
        respond(200, array_map('castTask', $rows));
    }

    if ($path === '/api/tasks' && $method === 'POST') {
        $title = trim((string) (jsonBody()['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) {
            respond(422, ['error' => 'Title is required and must be at most 255 characters']);
        }
        $stmt = db()->prepare('INSERT INTO tasks (title) VALUES (:title) RETURNING id, title, completed, created_at');
        $stmt->execute(['title' => $title]);
        respond(201, castTask($stmt->fetch()));
    }

    if (preg_match('#^/api/tasks/(\d+)$#', $path, $m)) {
        $id = (int) $m[1];

        if ($method === 'PATCH') {
            $body = jsonBody();
            if (!array_key_exists('completed', $body) || !is_bool($body['completed'])) {
                respond(422, ['error' => 'Field "completed" (boolean) is required']);
            }
            $stmt = db()->prepare('UPDATE tasks SET completed = :completed WHERE id = :id RETURNING id, title, completed, created_at');
            $stmt->bindValue(':completed', $body['completed'], PDO::PARAM_BOOL);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            $row ? respond(200, castTask($row)) : respond(404, ['error' => 'Task not found']);
        }

        if ($method === 'DELETE') {
            $stmt = db()->prepare('DELETE FROM tasks WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $stmt->rowCount() ? respond(204) : respond(404, ['error' => 'Task not found']);
        }
    }

    respond(404, ['error' => 'Not found']);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());   // details go to the logs, not to the client
    respond(500, ['error' => 'Database error']);
}
