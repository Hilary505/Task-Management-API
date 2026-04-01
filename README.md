# Task Management API
A task management REST API built with pure PHP and MySQL, with a vanilla JS frontend.

## Requirements
- PHP 8.1+
- MySQL 5.7+

---

## Running Locally

### 1. Clone the repo
```bash
git clone https://github.com/Hilary505/Task-Management-API.git
cd Task-Management-API
```

### 2. Set up `.env`
```bash
cp .env.example .env
```
Edit `.env` with your MySQL credentials:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=taskmanager
DB_USER=your_mysql_user
DB_PASS=your_mysql_password
```

### 3. Run migration
```bash
sudo mysql < backend/migrations/create_tasks_table.sql
```

### 4. (Optional) Run seeder
```bash
sudo mysql < backend/migrations/seed_tasks.sql
```

### 5. Start the server
```bash
php -S localhost:8000 -t backend/public
```
Open `http://localhost:8000` in your browser.

---

## Deploying Online

Both Railway and Render offer free-tier MySQL + PHP hosting.

### Railway

1. Push your repo to GitHub.
2. Go to [railway.app](https://railway.app) → **New Project** → **Deploy from GitHub repo**.
3. Add a **MySQL** plugin from the Railway dashboard — it auto-sets `MYSQL_URL` and individual `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` variables.
4. In your service **Variables**, add:
   ```
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_NAME=${{MySQL.MYSQLDATABASE}}
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASS=${{MySQL.MYSQLPASSWORD}}
   ```
5. Set the **Start Command**:
   ```
   php -S 0.0.0.0:$PORT -t backend/public
   ```
6. Run the migration via Railway's **MySQL shell** or the CLI:
   ```bash
   railway run mysql < backend/migrations/create_tasks_table.sql
   ```

### Render

1. Push your repo to GitHub.
2. Go to [render.com](https://render.com) → **New Web Service** → connect your repo.
3. Set **Runtime** to `PHP`, **Start Command**:
   ```
   php -S 0.0.0.0:$PORT -t backend/public
   ```
4. Create a **New MySQL** database on Render and copy the connection details into **Environment Variables**:
   ```
   DB_HOST=<render-mysql-host>
   DB_PORT=3306
   DB_NAME=<db-name>
   DB_USER=<db-user>
   DB_PASS=<db-password>
   ```
5. Run the migration using Render's **Shell** tab:
   ```bash
   mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < backend/migrations/create_tasks_table.sql
   ```

---

## API Endpoints

### Create a task
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Fix bug","due_date":"2026-12-01","priority":"high"}'
```

### List all tasks
```bash
curl http://localhost:8000/api/tasks
```

### List tasks by status
```bash
curl http://localhost:8000/api/tasks?status=pending
```
Sorted by priority (high → medium → low), then `due_date` ascending.

### Update task status
```bash
curl -X PATCH http://localhost:8000/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'
```
Allowed transitions: `pending → in_progress → done`

### Delete a task
```bash
curl -X DELETE http://localhost:8000/api/tasks/1
```
Only tasks with status `done` can be deleted.

### Daily report
```bash
curl http://localhost:8000/api/tasks/report?date=2026-12-01
```
Returns counts per priority and status for the given `due_date`.
