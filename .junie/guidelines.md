### Project Structure & Responsibilities

The repository is organized into a standard Laravel (API) and Vue (SPA) layout:

*   **API Layer (Laravel):**
    *   `routes/api.php`: Defines API endpoints. Grouped by `auth` and `admin` (via `EnsureUserIsAdmin`) middleware.
    *   `app/Http/Controllers/Api`: Contains `AuthController` for JWT/Sanctum-like login, `ChartsController` for data aggregation, and a stubbed `ExportController`.
    *   `app/Models`: Eloquent models for `Revenue`, `Expense`, `Budget`, and `Project`.
    *   `database/migrations` & `database/seeders`: Schema definitions and sample financial data generators.
*   **Frontend Layer (Vue SPA):**
    *   `resources/js`: Contains the Vue 3 application.
    *   `resources/js/views`: `Graphs.vue` (main dashboard) and `Login.vue`.
    *   `resources/js/components/tabs`: `FinanceTab.vue` (General) and `AnalyticsTab.vue` (Admin-only).
    *   `resources/js/router/index.js`: Handles navigation and client-side auth guards.
*   **Documentation:**
    *   `/docs`: Detailed markdown files covering `API.md`, `ARCHITECTURE.md`, `AUTHENTICATION.md`, and `INSTALLATION.md`.

**Current Implementation:**
*   **Public (Auth) Endpoints:** `revenue`, `expenses`, `profit` (rendered as Line, Pie, and Bar charts in Finance Tab).
*   **Admin Endpoints:** `budget-vs-fact`, `available-budget-months`, `roi` (rendered as Bar and Horizontal Bar charts in Analytics Tab).

---

### Where to Implement Future Complexity

#### A) Reporting & Metric Layer
*   **Service Layer:** Avoid placing complex aggregation logic in `ChartsController`. Create dedicated services in `app/Services` (e.g., `FinancialReportingService.php`) to handle calculations.
*   **DTO Responses:** Use structured Data Transfer Objects or dedicated Resource classes for chart-ready data (labels/datasets) versus tabular data (raw rows for reports) to maintain consistency across different consumers.

#### B) Data Mart / Aggregation
*   **Summary Tables:** For performance, implement materialized summary tables (e.g., `monthly_aggregates`, `roi_snapshots`).
*   **Rebuild Workflow:** Implement recomputation logic within Artisan commands (e.g., `php artisan metrics:rebuild`) and schedule them via Laravel's Task Scheduler (`app/Console/Kernel.php`) or dispatch them to a queue.

#### C) Export
*   **Implementation:** Complete `ExportController`. Focus on exporting tabular data (CSV/XLSX) using libraries like `maatwebsite/excel`. 
*   **Schema Versioning:** Maintain a mapping between internal report schemas and export formats to ensure that what the user sees in a table matches the downloaded file.

#### D) Optional Import
*   **Staging Approach:** Use a staging table for CSV imports. Implement a three-step process: 
    1. Upload to staging and validate.
    2. Show preview/errors to user.
    3. Explicit "Apply" step to move data to production tables and trigger recomputation of summary tables.

#### E) Kubernetes Deployment
*   **Layout:** Use a minimal k8s configuration:
    *   `frontend`: Deployment + Service (Nginx serving built Vue assets).
    *   `backend`: Deployment + Service (PHP-FPM) + CronJob (for scheduled tasks).
    *   `database`: StatefulSet + PVC (MySQL).
    *   `ingress`: Routing traffic to frontend/backend services.
    *   `configmap/secret`: Managing `.env` variables.
*   **Manifests:** Store these in a `/k8s` directory and document the setup in `docs/DEPLOYMENT.md`.

---

### Documentation Consistency Rules

*   **API Sync:** Any change to `routes/api.php` or controller methods must be immediately reflected in `docs/API.md`.
*   **Naming:** Maintain strict consistency between API endpoint names, controller method names, and frontend composable/fetcher names.
*   **Cross-Reference:** Ensure `ARCHITECTURE.md` is updated if new architectural layers (like Services or Data Marts) are introduced.

---

### Coding & Design Conventions

*   **Authorization:** Continue using the `admin` middleware for route protection. Keep the logic in `EnsureUserIsAdmin.php` simple; use Policies if fine-grained resource-level access is needed later.
*   **Thin Controllers:** Controllers should only handle request validation and response formatting. All business logic must reside in Service classes.
*   **Data Integrity:** When changing database schemas via migrations, ensure corresponding seeders in `database/seeders` are updated to reflect the new structure.
*   **Isolated Queries:** Keep complex Eloquent/DB queries inside Model scopes or dedicated Repository/Service classes to keep them testable and reusable.
