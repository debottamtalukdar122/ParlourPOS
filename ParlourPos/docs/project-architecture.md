# Proposed Core PHP MVC-Style Architecture

## Architectural approach

Use a small, explicit MVC-style application with a front controller, route definitions, controllers for HTTP coordination, models/repositories for database access, service classes for business transactions, and Bootstrap/PHP views for presentation. This is Core PHP: no Laravel or other PHP framework.

Controllers should remain thin. Invoice finalization, stock posting, payment collection, refunds, and appointment availability belong in services because they require validation, multiple table writes, and database transactions.

## Suggested structure

```text
Parlour-POS-System/
├── app/
│   ├── Controllers/          # HTTP actions by module
│   ├── Models/               # Small domain entities/data objects where useful
│   ├── Repositories/         # PDO queries and persistence
│   ├── Services/             # Billing, inventory, appointment, report business rules
│   ├── Middleware/           # Auth, permission, CSRF, guest checks
│   ├── Requests/             # Input validation rules/DTOs
│   ├── Views/
│   │   ├── layouts/          # Main, auth, invoice layouts
│   │   ├── partials/         # Header, navigation, alerts, pagination
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── customers/ services/ products/ staff/ appointments/
│   │   ├── pos/ invoices/ payments/ inventory/ purchases/
│   │   ├── expenses/ reports/ settings/ users/
│   │   └── errors/
│   ├── Helpers/              # Escaping, money/date formatting, response helpers
│   ├── Support/              # Router, Request, Response, View, Validator, Logger
│   └── Exceptions/
├── bootstrap/
│   ├── app.php               # Builds container/application services
│   └── database.php          # Creates PDO connection
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── billing.php
│   ├── permissions.php
│   └── routes.php
├── database/
│   ├── migrations/           # Versioned SQL/PHP migration files
│   ├── seeds/                # Initial roles, permissions, settings, methods
│   └── schema/               # Generated/current reference schema only
├── public/                   # Only web-accessible directory / document root
│   ├── index.php             # Front controller
│   ├── .htaccess             # Rewrite non-file requests to index.php (Apache)
│   └── assets/
│       ├── css/ js/ img/ vendor/
├── routes/
│   ├── web.php               # HTML routes
│   └── api.php               # Small authenticated AJAX JSON endpoints
├── storage/                  # Not public; writable by web server only
│   ├── uploads/              # logo, staff photos, expense attachments
│   ├── invoices/             # generated invoice PDFs
│   ├── logs/                 # application/error logs
│   └── cache/
├── tests/                    # Calculation and service-level tests
├── docs/
├── vendor/                   # Composer-managed, never hand-edited
├── composer.json
├── .env.example              # no secrets
└── .gitignore
```

## Request lifecycle and routes

`public/index.php` starts the application, loads environment/configuration, creates the PDO connection, starts a secure session, builds the router, and dispatches the request. A route applies middleware before calling a controller. The controller validates input, calls a service/repository, then returns either a PHP view redirect/response or a JSON response for AJAX.

Use clear REST-like routes, for example `/customers`, `/appointments`, `/pos`, `/invoices/{id}`, `/inventory/adjustments`, and `/reports/sales`. Keep AJAX endpoints under `/api/` for customer/product lookup, appointment availability, POS calculations/line lookup, and dashboard chart data. AJAX endpoints use the same authentication, authorization, CSRF, validation, and server-side calculation rules as pages.

## Controller and service responsibilities

Suggested controllers: `AuthController`, `DashboardController`, `CustomerController`, `ServiceController`, `ProductController`, `SupplierController`, `StaffController`, `AppointmentController`, `PosController`, `InvoiceController`, `PaymentController`, `PurchaseController`, `InventoryController`, `RefundController`, `ExpenseController`, `CommissionController`, `ReportController`, `SettingsController`, `UserController`, `RoleController`, and `AuditLogController`.

Critical services: `AuthService`, `AppointmentService`, `BillingService`, `InvoicePdfService`, `PaymentService`, `InventoryService`, `PurchaseService`, `RefundService`, `CommissionService`, `DiscountService`, `TaxService`, `ReportService`, `UploadService`, and `AuditService`. `BillingService` is the sole path that finalizes an invoice; it wraps invoice, payment, stock, commission, and audit work in a database transaction.

## Authentication, authorization, and session handling

- Store `password_hash()` output only; use `password_verify()` and rate-limit repeated failed login attempts.
- Regenerate the session ID on login; configure `HttpOnly`, `Secure` (HTTPS), and `SameSite=Lax` or stricter session cookies.
- Store minimal session data: authenticated user ID, session nonce/version, login time, and CSRF token. Load current roles/permissions server-side.
- `AuthMiddleware` requires a logged-in active user. `PermissionMiddleware` checks the route's named permission. `GuestMiddleware` protects login/reset screens. `CsrfMiddleware` validates every POST/PUT/PATCH/DELETE form and AJAX request.
- Log login/logout and sensitive operations; destroy the session fully on logout and enforce idle/absolute expiry.

## Database and configuration

Use PDO with exceptions, `ATTR_EMULATE_PREPARES=false`, prepared statements, and transaction boundaries in services. Keep database credentials and external provider secrets in `.env`, loaded by a small environment loader; do not commit them. PHP config files contain defaults and named options, while the `settings` table contains approved runtime business configuration such as logo, GSTIN, invoice prefix, tax, rounding, payment method enablement, and notification settings.

Migrations must be ordered, reversible where practical, and include foreign keys, indexes, unique constraints, seeds for the four initial roles/permissions, and required billing settings. Avoid automatic schema changes at ordinary page load.

## Views, assets, uploads, and PDFs

Server-render standard screens with reusable PHP layouts/partials and Bootstrap. Add focused vanilla JavaScript (or jQuery only where it clearly simplifies an AJAX task); use Chart.js for dashboard/report charts. Escape all output by default, return structured JSON errors, and show clear validation/toast/loading states.

Store uploads and invoice PDFs in `storage/`, not `public/`. Validate MIME type, size, extension, and image dimensions; generate random filenames and serve protected files through an authorized controller. Generate invoice PDFs from a dedicated invoice HTML view using DomPDF or mPDF, preserving the finalized invoice snapshots.

## Maintainability conventions

- One module per controller/service/repository/view folder area; avoid a single generic model handling all SQL.
- Use meaningful names and small methods; add PHPDoc only where types/intent are not obvious.
- Keep financial calculations and status transitions in tested services, never in JavaScript alone.
- Use standard PRG redirects for successful form submissions and JSON only for true asynchronous interactions.
- Centralize error logging and user-safe error pages; never expose stack traces or database details in production.
