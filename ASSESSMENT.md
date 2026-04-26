# Assessment Notes

## Assumptions

### Company and branch selection
A user belongs to one or more companies via the `employees` table (an employee record links a user to a specific company and branch). On login the application checks which companies the user is associated with and sets the first one as the `selected_company_id` in the session. A **Company Switcher** dropdown in the navigation lets the user change context at any time. Once a company is selected, only that company's branches are shown, and every commission note route is scoped under `/companies/{company}/branches/{branch}/notes`. There are no "global" notes.

### Employee model
The seeder creates `Employee` rows that link a `User` to a `Company` + `Branch`. `user_id` on `employees` is nullable so that employees who do not have a system login can still appear as payees on commission notes (useful for non-user staff added by a manager).

### Data seeded to satisfy the exercise
The seeder creates the company **Spar** with two branches (**Spar Bellville** and **Spar Gardens**), two employees in different branches (Alice Nkosi in Bellville, Bob Dlamini in Gardens), and one commission note per employee (R 10 000 and R 20 000 respectively), exactly as specified.

---

## Permission names

| Permission | Role(s) | Meaning |
|---|---|---|
| `view commission notes` | viewer, manager | May open the notes UI and see notes for the selected company + branch context. |
| `manage commission notes` | manager | May create, update, and delete commission notes. A user with this permission may also edit or delete notes authored by anyone else. |

Users without either permission are denied access to the notes area entirely (HTTP 403 from `$this->authorize('view commission notes')`).

---

## Tradeoffs

### Role-based vs. permission-based enforcement
Spatie's permission system is used at the permission level (`can('view commission notes')`) rather than the role level (`hasRole('manager')`). This makes it easy to add new roles or reassign permissions later without touching the authorization code.

### Author-bypass on edit and delete
The spec states "only the original author may edit a note unless the user has manage commission notes". The same principle is applied to deletion — an author can delete their own note, a manager can delete anyone's note. This is enforced in `CommissionNoteService` and the controller delegates fully to the service rather than duplicating the check.

### No separate API layer
All data transfer goes through Inertia shared props and controller responses. There is no `/api` prefix or JSON-only endpoint. This keeps the surface area small for an internal tool and avoids duplicating authorization logic.

### Assets built at Docker image build time
Frontend assets (`npm run build`) are compiled inside the `Dockerfile` during `docker compose build`. This means no separate Node container is needed at runtime, and the production nginx serves pre-compiled static files. The tradeoff is a slightly longer initial build; hot-reload is not available in this Docker setup.

### SQLite for tests
Tests run against an in-memory SQLite database (configured in `phpunit.xml`), independent of the MariaDB container. This keeps the test suite fast and self-contained and means the `db` service does not need to be healthy to run tests.

---

## What I would harden for production

| Area | Action |
|---|---|
| **Queued notifications** | Commission note creation/update would dispatch a `CommissionNoteCreated` event. A listener would push a `SendCommissionNotification` job onto a Redis-backed queue (`php artisan queue:work`). The job would send an email (via Laravel's `Mail` facade) or an SMS (via a gateway like BulkSMS) to the employee. This keeps the HTTP request fast and decouples delivery from the web process. |
| **Backups** | Schedule `spatie/laravel-backup` to snapshot the MariaDB volume nightly to an off-site S3 bucket. |
| **Rate limiting** | Apply stricter per-user throttle on the `notes.store` and `notes.update` routes. |
| **Audit trail** | Add an `activity_log` table (or use `spatie/laravel-activitylog`) to record who created or changed each note and when. |
| **HTTPS** | Terminate TLS at a reverse proxy (e.g. Caddy or an AWS ALB) in front of the nginx container; set `SESSION_SECURE_COOKIE=true`. |
| **Permissions cache** | Warm the Spatie permission cache on deployment (`php artisan permission:cache-reset`) and set a reasonable TTL to avoid per-request DB hits. |
| **Environment secrets** | Move `APP_KEY`, DB credentials, and mail credentials out of `docker-compose.yml` into a secrets manager (e.g. AWS Secrets Manager or Doppler). |
