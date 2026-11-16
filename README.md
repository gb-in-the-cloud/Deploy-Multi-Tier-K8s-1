# Task Tracker: React + PHP + PostgreSQL

A small full-stack app for adding, completing and deleting tasks.

| Layer    | Tech                         | Folder      |
|----------|------------------------------|-------------|
| Frontend | React 18 + Vite, served by nginx | `frontend/` |
| Backend  | Plain PHP 8.4 (PDO), Apache  | `backend/`  |
| Database | PostgreSQL 16                | `db/`       |

```
Browser ──► nginx (web, :8080) ──/api──► PHP + Apache (api) ──► PostgreSQL (db)
```

## Run it

Requires Docker with Docker Compose.

```bash
cp .env.example .env        # then set a real DB_PASSWORD
docker compose up -d --build
```

Open **http://localhost:8080**. The database starts with three sample tasks from `db/init.sql`.

Check the API directly:

```bash
curl http://localhost:8000/api/health
curl http://localhost:8000/api/tasks
```

## API

| Method | Path              | Body                     | Response |
|--------|-------------------|--------------------------|----------|
| GET    | `/api/health`     |                          | `{"status":"ok"}` |
| GET    | `/api/tasks`      |                          | Array of tasks, newest first |
| POST   | `/api/tasks`      | `{"title": "Buy milk"}`  | `201` + the new task |
| PATCH  | `/api/tasks/{id}` | `{"completed": true}`    | The updated task |
| DELETE | `/api/tasks/{id}` |                          | `204` |

Errors return `{"error": "..."}` with a 4xx or 5xx status.

## Frontend development with hot reload

```bash
docker compose up -d db api
cd frontend
npm install
npm run dev                 # http://localhost:5173, /api is proxied to :8000
```

## Notes

- All SQL uses prepared statements, so user input never becomes part of a query.
- Database errors are logged on the server; the client only sees a generic message.
- The PHP image is pinned to 8.4.27, which includes the PHP2Shell fixes.
- Reset the database (deletes all data): `docker compose down -v`.
# Deploy-Multi-Tier-K8s-1
