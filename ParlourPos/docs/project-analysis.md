# Parlour POS Billing & Management System - Project Analysis

## 1. Project overview

This is a secure, web-based POS and management system for a small-to-medium parlour. Its central operational flow is taking a walk-in or appointment customer through service/product selection, staff assignment, billing, payment, invoice generation, product stock update, commission calculation, and reporting.

The system must use Core PHP, MySQL, HTML5/CSS3, Bootstrap, JavaScript/AJAX, a PDF library (DomPDF or mPDF), and Chart.js. It is intentionally a single-parlour system unless multi-branch support is later specified.

### Core modules

1. Authentication, users, roles, permissions, sessions, login activity, and audit logs.
2. Business and billing settings.
3. Customer management and customer history.
4. Service categories and services.
5. Product categories, products, suppliers, purchases, and inventory.
6. Staff/beautician profiles and commission tracking.
7. Appointment booking, scheduling, and calendar views.
8. POS billing, invoices, payment collection, and receipt/PDF generation.
9. Discounts, taxes, rounding, returns, and refunds.
10. Expenses.
11. Dashboard and reports.
12. Optional, separately enabled modules: memberships/loyalty and notifications.

## 2. Users, roles, and permissions

Permissions should be stored as granular actions (`module.action`) and assigned to roles. A user can hold more than one role; the default setup can still assign exactly one role per user for simplicity.

| Role | Main permissions | Restrictions |
|---|---|---|
| Super Admin | Full user/RBAC, settings, masters, staff, customers, appointments, billing, stock, purchases, expenses, commissions, refunds, reports, and audit logs | None within the application |
| Manager | Customers, staff, appointments, POS, inventory, purchases, expenses, sales monitoring, reports, and permitted refunds/discounts | Cannot change critical settings, RBAC, or other explicitly restricted actions |
| Receptionist / Cashier | Customer registration/search, appointments, POS bills, payments, invoice print/PDF/reprint, customer history, service/product availability | No administrative settings, user/RBAC management, or unrestricted inventory/expense controls |
| Beautician / Staff | Own assigned appointments/customers, service-status updates, completed work, and permitted commission view | No POS settlement, settings, inventory, or organization-wide reports by default |

Recommended permission examples are `customers.view/create/update`, `appointments.manage`, `invoices.create/view/reprint/cancel`, `payments.collect`, `refunds.create/approve`, `stock.adjust`, `reports.view`, `settings.manage`, and `audit_logs.view`. Refund approvals, custom charges, price overrides, bill discounts, and invoice cancellation should be separately permissioned.

## 3. Important workflows

### POS billing workflow

1. Cashier selects an existing customer or registers a walk-in customer.
2. For appointment billing, the selected appointment supplies customer, services, and staff assignments; for walk-ins these are selected in POS.
3. Cashier adds service and/or product lines, quantities, staff for service lines, and only authorized custom charges/overrides.
4. The system snapshots item price, tax, discount, and commission rule onto the bill line, then calculates subtotal, discounts, taxable amount, tax, rounding, charges, grand total, paid amount, and balance.
5. Cashier records one or more payments (cash, UPI, debit card, credit card, bank transfer, or other). Partial payment is allowed only when billing settings enable it.
6. On finalization, the system assigns an immutable invoice number, records payment(s), creates the invoice/receipt PDF, deducts sold product stock, creates service commission records, marks the linked appointment billed where applicable, and writes audit logs.
7. Reprints must use the saved invoice snapshot; editing a completed invoice should not silently rewrite history. Use cancellation/refund flows instead.

### Appointment workflow

1. Reception selects/registers a customer, one or more services, date/time, and staff.
2. The system derives each service duration from its service record (but stores a snapshot) and validates staff availability.
3. Booking fails if the same staff has another active appointment that overlaps the proposed time interval.
4. Status progresses through Pending, Confirmed, Checked In, In Progress, Completed, Cancelled, or No Show. Invalid backward transitions require a privileged override and audit record.
5. Completed/checked-in appointment information can be loaded into POS; an appointment should not generate duplicate invoices without an explicit permitted workflow.

### Inventory workflow

1. Admin/manager maintains products, categories, suppliers, minimum levels, unit, prices, and tax/discount defaults.
2. Completing a purchase records purchase items and one positive stock movement per item.
3. Completing an invoice records one negative `sale` stock movement for each sold product item in the same database transaction.
4. Authorized adjustments, damaged goods, stock returns, and product refunds create explicit movements; stock is never changed without a reason and reference.
5. Current stock is derived from the ledger or maintained as a transactionally updated product cache. Low-stock reports compare current stock to the stored minimum level.

### Payment workflow

1. A completed invoice can receive one or more payments.
2. Each payment records method, amount, received date/time, reference number when applicable, status, and receiving user.
3. Invoice payment status is calculated from valid captured payments minus refunds: unpaid, partial, paid, or refunded/partially refunded.
4. Payment reference values must be retained for reconciliation; cancelling/voiding a payment should preserve the audit trail rather than delete it.

### Refund workflow

1. An authorized user selects the original invoice and eligible product/service line(s).
2. The system prevents refund quantity/value above the original sold quantity/value minus prior refunds.
3. It records reason, refund method, amount, actor, approval state if required, and the individual refunded lines.
4. A completed product return adds stock only when the returned product is accepted into inventory; service refunds do not affect stock.
5. The invoice balance/payment status and reports are recalculated from immutable invoice, payment, and refund records. All actions are audited.

## 4. Dependencies between modules

| Module | Depends on | Provides to |
|---|---|---|
| Authentication/RBAC | Users, roles, permissions | Every protected module |
| Settings | Authentication/RBAC | Billing, invoice, taxes, payment methods, notifications |
| Categories/services/products | Authentication/RBAC, categories | Appointments, POS, reporting |
| Customers | Authentication/RBAC | Appointments, POS, history/reports |
| Staff | Authentication/RBAC | Appointments, POS service assignments, commissions |
| Suppliers/purchases | Products, inventory rules | Stock ledger, purchase reports |
| Appointments | Customers, services, staff | POS appointment billing, staff performance |
| POS/invoices | Customers, items, staff, tax/discount settings | Payments, inventory, commissions, refunds, reports |
| Payments | Finalized invoices, payment settings | Invoice balance, reports, receipts |
| Inventory | Products, purchases, finalized POS invoices, refunds | Low stock, inventory reports |
| Commissions | Finalized service invoice lines and staff assignment | Staff reports/payable tracking |
| Refunds | Invoices, payments, inventory | Stock, balances, reports |
| Dashboard/reports | All operational ledgers | Management insights |

## 5. Important business rules

- Use money values as `DECIMAL`, never floating point. Calculate and finalize all invoice totals server-side.
- Prices, service duration, tax, discount, product cost, staff, and commission rates must be copied to transactional lines. Later master-data changes must not alter history.
- A product sale cannot finalize into negative stock unless a future setting expressly permits it.
- A service may be inactive/deleted from future selection but must remain visible in historic records.
- Customer ID and invoice number are human-readable business identifiers distinct from numeric primary keys.
- Mobile number is searchable; do not assume it is globally unique unless the business adopts that policy.
- Product SKU and non-empty barcode are unique. Invoice numbers and purchase invoice references per supplier are unique.
- Invoice totals must satisfy the stated formula: subtotal less discounts equals taxable amount; taxable amount plus tax and additional charges (plus rounding adjustment) equals grand total.
- Discounts must obey their type, active period, scope, minimum bill, maximum discount, customer eligibility, and usage limits. A manual override requires permission.
- A staff overlap means `existing_start < proposed_end AND existing_end > proposed_start` for active scheduling statuses.
- Completed invoices, stock movements, payments, refunds, commissions, and audit records are append-only business records. Correct them with controlled reversing/cancelling transactions, not destructive edits.
- Use soft deletion for master data; do not soft-delete financial/stock/audit history.
- Store passwords only using `password_hash()` (bcrypt/Argon2); protect sessions against fixation, enforce authorization on every route, validate CSRF on state changes, and use prepared statements.

## 6. Scope boundaries

Memberships/loyalty, coupon use, WhatsApp/SMS/email delivery, attendance, barcode hardware, thermal printer integration, Excel/PDF report export, and email invoices are optional or conditional in the requirements. The core schema may reserve support for them, but they should be enabled only after the core POS is accepted.
