# Micro-Finance ERP System

A multi-module ERP (Enterprise Resource Planning) system built with **Laravel 7**, focused on microfinance operations. The application runs in Docker containers for easy setup — no need to install PHP or MySQL on your machine.

## Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 7.4 | Backend language |
| Laravel | 7.x | PHP web framework |
| MySQL | 5.7 | Database |
| Nginx | Alpine | Web server |
| Docker | Latest | Containerization |
| Composer | 2.x | PHP dependency manager |
| Laravel Passport | 9.x | API authentication |

---

## Prerequisites

- **Docker Desktop** installed and running
  - Download: https://www.docker.com/products/docker-desktop
  - After installing, make sure Docker Desktop is **running** (check the system tray icon)

---

## Getting Started

### Step 1: Get the project

Download or clone the project to your local machine.

### Step 2: Start Docker Desktop

Open Docker Desktop and wait until it shows **"Docker is running"** in the bottom-left corner.

### Step 3: Build and start the containers

Open a terminal in the project folder and run:

```bash
docker compose up -d --build
```

This will:
- Download the required Docker images (first time only, may take a few minutes)
- Install PHP dependencies via Composer
- Generate an application encryption key
- Start three containers: **app**, **nginx**, and **db**

### Step 4: Wait for startup

The first run takes a minute or two. Check if all containers are running:

```bash
docker compose ps
```

You should see all three containers with status **Up**:

```
NAME                  STATUS
micro-finance-app     Up
micro-finance-db      Up (healthy)
micro-finance-nginx   Up
```

### Step 5: Open the application

Open your browser and go to:

**http://localhost:8080**

You should see the login page.

---

## Docker Services

| Service | Container Name | Internal Port | External Port | Description |
|---------|---------------|---------------|---------------|-------------|
| app | micro-finance-app | 9000 | — | PHP-FPM (Laravel application) |
| nginx | micro-finance-nginx | 80 | **8080** | Web server (serves the app) |
| db | micro-finance-db | 3306 | **3307** | MySQL database |

---

## Database Access

### Credentials

| Field | Value |
|-------|-------|
| Host | `localhost` (from your machine) or `db` (from app container) |
| Port | `3307` (from your machine) or `3306` (from app container) |
| Database | `micro_finance` |
| Username | `mf_user` |
| Password | `mf_secret` |
| Root Password | `root_secret` |

### Connecting with a GUI tool (e.g., MySQL Workbench, DBeaver, HeidiSQL)

Use these settings:
- **Host:** `127.0.0.1`
- **Port:** `3307`
- **Username:** `mf_user`
- **Password:** `mf_secret`

### Importing a database dump

If you have a `.sql` file to import:

```bash
docker exec -i micro-finance-db mysql -u mf_user -pmf_secret micro_finance < your_database_file.sql
```

---

## Project Structure

```
micro-finance/
├── app/
│   ├── BaseModel.php              # Base Eloquent model with audit logging
│   ├── Console/                   # Artisan commands
│   ├── Exceptions/                # Error handlers
│   ├── Helpers/
│   │   ├── CommonHelper.php       # Shared utility functions
│   │   ├── HTMLHelper.php         # Form/HTML generation helpers
│   │   └── RoleHelper.php         # Permission/role helpers
│   ├── Http/
│   │   ├── Controllers/           # Controllers organized by module
│   │   ├── Kernel.php             # HTTP middleware registration
│   │   └── Middleware/            # Request middleware (auth, permissions, etc.)
│   ├── Jobs/                      # Background jobs
│   ├── Mail/                      # Email templates
│   ├── Model/                     # Eloquent models organized by module
│   ├── Providers/                 # Service providers
│   ├── Rules/                     # Custom validation rules
│   └── Services/                  # Business logic services
├── bootstrap/                     # Framework bootstrap files
├── config/                        # Application configuration
├── database/
│   ├── migrations/                # Database schema migrations
│   └── seeds/                     # Database seeders
├── docker/
│   ├── entrypoint.sh              # Container startup script
│   └── nginx/default.conf         # Nginx configuration
├── public/
│   └── index.php                  # Application entry point
├── resources/
│   ├── views/                     # Blade templates organized by module
│   ├── lang/                      # Language files
│   └── sass/                      # Stylesheets
├── routes/
│   ├── web.php                    # Main routes (includes all module routes)
│   ├── ajax.php                   # AJAX endpoints
│   ├── api.php                    # REST API routes
│   ├── acc.php                    # Accounting routes
│   ├── bill.php                   # Billing routes
│   ├── gnl.php                    # General config routes
│   ├── hr.php                     # HR routes
│   ├── inv.php                    # Inventory routes
│   ├── mfn.php                    # Microfinance routes
│   └── pos.php                    # Point of Sale routes
├── storage/                       # Logs, cache, sessions
├── .env                           # Environment configuration
├── composer.json                  # PHP dependencies
├── docker-compose.yml             # Docker services definition
├── Dockerfile                     # App container build instructions
└── artisan                        # Laravel CLI tool
```

---

## Modules Overview

The application is divided into functional modules. Each module has its own controllers, models, views, and routes.

| Module | Code | Directory | Description |
|--------|------|-----------|-------------|
| **General Config** | GNL | `GNL/` | Company, branch, employee, department, area, fiscal year settings |
| **Microfinance** | MFN | `MFN/` | Core module — members, loans, savings, shares, samity (groups), reports |
| **Accounting** | ACC | `ACC/` | Vouchers, ledgers, chart of accounts, day/month/year end processing |
| **Human Resources** | HR | `HR/` | Employee management, departments, holidays, leave, transfers |
| **Inventory** | INV | `INV/` | Products, categories, stock issues, returns, purchase requisitions |
| **Point of Sale** | POS | `POS/` | Sales, purchases, collections |
| **Billing** | BILL | `BILL/` | Customer/supplier billing, packages, agreements |
| **Fixed Assets** | FAM | `FAM/` | Fixed asset management |
| **Procurement** | PROC | `PROC/` | Procurement processes |

### Where to find things for each module

- **Controllers:** `app/Http/Controllers/{MODULE}/`
- **Models:** `app/Model/{MODULE}/`
- **Views:** `resources/views/{MODULE}/`
- **Routes:** `routes/{module}.php`

**Example:** To study the Microfinance loan feature:
- Controller: `app/Http/Controllers/MFN/Loan/LoanController.php`
- Model: `app/Model/MFN/` (loan-related models)
- Views: `resources/views/MFN/Loan/`
- Routes: `routes/mfn.php`

---

## Common Commands

### Start the application
```bash
docker compose up -d
```

### Stop the application
```bash
docker compose down
```

### Rebuild after code changes to Dockerfile
```bash
docker compose up -d --build
```

### View container logs
```bash
# All containers
docker compose logs

# Only the app container (most useful for debugging)
docker compose logs app

# Follow logs in real-time
docker compose logs -f app
```

### Restart a specific container
```bash
docker compose restart app
```

### Run Laravel Artisan commands
```bash
# List all available commands
docker exec micro-finance-app php artisan list

# Run database migrations
docker exec micro-finance-app php artisan migrate

# Clear all caches
docker exec micro-finance-app php artisan config:clear
docker exec micro-finance-app php artisan cache:clear
docker exec micro-finance-app php artisan view:clear

# Open Laravel Tinker (interactive PHP shell)
docker exec -it micro-finance-app php artisan tinker
```

### Access the app container's shell
```bash
docker exec -it micro-finance-app bash
```

### Access the MySQL shell
```bash
docker exec -it micro-finance-db mysql -u mf_user -pmf_secret micro_finance
```

---

## Key Configuration Files

| File | Purpose |
|------|---------|
| `.env` | Database credentials, app URL, debug mode, etc. |
| `config/app.php` | App name, timezone, service providers, aliases |
| `config/auth.php` | Authentication guards (web + API via Passport) |
| `config/database.php` | Database connection settings |
| `app/Http/Kernel.php` | Middleware registration |
| `app/Providers/RouteServiceProvider.php` | Route loading configuration |

---

## Troubleshooting

### "Docker is not running" / Cannot connect to Docker

Make sure Docker Desktop is open and fully started. Look for the Docker icon in your system tray.

### Port 8080 already in use

Another application is using port 8080. Either stop that application, or change the port in `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "9090:80"   # Change 8080 to any free port
```

Then run `docker compose up -d` and access `http://localhost:9090`.

### Port 3307 already in use

Same idea — change it in `docker-compose.yml` under the `db` service.

### App container keeps restarting

Check the logs:

```bash
docker compose logs app
```

Common causes:
- **Vendor not installed:** The entrypoint script installs dependencies automatically on first run. Wait a minute and check again.
- **`.env` file issues:** Make sure `.env` exists and has valid settings.

### "Class not found" errors

Run inside the container:

```bash
docker exec micro-finance-app composer dump-autoload
```

### Database connection refused

Make sure the `db` container is healthy:

```bash
docker compose ps
```

If it shows `(health: starting)`, wait for it to become `(healthy)` before the app can connect.

### Clearing everything and starting fresh

```bash
docker compose down -v
docker compose up -d --build
```

> **Warning:** The `-v` flag deletes the database volume. All database data will be lost.
> If you have any questions, email me - farhanisrak.yen29@gmail.com
