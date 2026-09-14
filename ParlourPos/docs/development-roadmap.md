# Development Roadmap

This order builds foundations before features that create irreversible financial or stock records. Optional modules are intentionally deferred.

## Phase 0 - Discovery and foundation

**Modules:** project setup, configuration, database migration/seed approach, shared layout, error handling.

**Dependencies:** none.

**Main tasks:** confirm PHP/MySQL versions; define environment configuration; create MVC skeleton, database connection, migration runner, repository conventions, common validation/response helpers, Bootstrap layout, and seed reference roles/permissions.

**Expected outcome:** a runnable Core PHP skeleton with a repeatable empty database setup. No business UI is considered complete yet.

## Phase 1 - Security and access control

**Modules:** users, roles, permissions, authentication, sessions, password reset, login activity, audit logging.

**Dependencies:** Phase 0.

**Main tasks:** secure login/logout, password hashing, session regeneration/timeouts, authorization middleware, CSRF protection, user/RBAC administration, and audit events.

**Expected outcome:** authorized users can safely access only their permitted routes; all later modules can depend on a current user and permission check.

## Phase 2 - Settings and operational master data

**Modules:** business/billing settings, payment methods, categories, services, suppliers, products, staff, customers, expense categories.

**Dependencies:** Phase 1.

**Main tasks:** CRUD with validation, activation/deactivation, business IDs, search/filtering, file upload handling for logo/staff image, and default tax/discount/commission values.

**Expected outcome:** the business can configure all people, items, prices, categories, and billing defaults required by operations.

## Phase 3 - Purchasing and inventory ledger

**Modules:** purchases, purchase items, stock movements, stock adjustment, low stock.

**Dependencies:** Phase 2 products, suppliers, settings, and RBAC.

**Main tasks:** record purchases, transactionally increase stock, view per-product movement history, make authorized adjustments/damage entries, and show low-stock results.

**Expected outcome:** trusted current stock and an auditable inventory history exist before product sales are enabled.

## Phase 4 - Appointment management

**Modules:** appointments, appointment service lines, staff calendar/list views.

**Dependencies:** Phase 2 customers, services, staff, RBAC.

**Main tasks:** book/reschedule/cancel, service-level staff assignment, overlap detection, permitted status changes, daily/weekly displays, and customer appointment history.

**Expected outcome:** reception can manage appointments without double-booking staff and can hand a valid appointment to POS.

## Phase 5 - POS, invoices, and payments

**Modules:** walk-in/appointment POS, invoices/items, discounts/taxes/rounding/charges, payment collection, receipts/PDF/reprint.

**Dependencies:** Phases 2-4 and inventory ledger from Phase 3.

**Main tasks:** fast searchable POS, server-side calculations, item/bill discounts, staff assignment, split payments, partial-payment setting, invoice numbering, finalization transaction, stock deduction, invoice PDF, and audit records.

**Expected outcome:** the parlour can safely complete and reprint service, product, and mixed bills with accurate payment balances and product stock.

## Phase 6 - Commissions, refunds, and expenses

**Modules:** staff commissions, controlled returns/refunds, expenses.

**Dependencies:** finalized invoices/payments/stock from Phase 5; staff and expense categories from Phase 2.

**Main tasks:** create commission records from service invoice lines; track commission payment status; constrain refunds to prior sales; return accepted products to stock; record/refine expenses and attachments.

**Expected outcome:** financial corrections, staff payout liabilities, and operational costs are auditable and reflected in reports.

## Phase 7 - Dashboard and reports

**Modules:** dashboard KPIs/charts, sales/payment/inventory/staff/expense/profit reports, print/PDF/export as approved.

**Dependencies:** operational data from Phases 3, 5, and 6.

**Main tasks:** date/status filters, Chart.js datasets, daily/monthly sales, payment method collection, stock/purchase, commission/staff performance, expense, and estimated-profit reports.

**Expected outcome:** managers have reconciled, filterable business insight. Dashboard figures are sourced from finalized/valid transactions only.

## Phase 8 - Optional extensions and hardening

**Modules:** coupons, memberships, loyalty, notification integrations, email invoices, Excel export, barcode/thermal printer integration, attendance if approved.

**Dependencies:** stable core POS and explicit business decisions/provider credentials.

**Main tasks:** enable one optional feature at a time, configure integration credentials securely, add end-to-end tests, permission review, backup/restore procedure, performance/security testing, and user acceptance testing.

**Expected outcome:** optional value is added without destabilizing the core billing, stock, and financial history.

## Cross-phase quality gates

At the end of each phase: database migrations run on a clean database; permission checks are verified; validation/error cases are tested; transactional operations are rollback-safe; audit events are reviewed; and responsive usability is checked for desktop/tablet/mobile. Phase 5 also requires calculation and concurrency tests before production use.
