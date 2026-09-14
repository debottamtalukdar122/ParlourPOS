# Database Design

## Design principles

Use MySQL 8.0+, `InnoDB`, `utf8mb4`, UTC timestamps, and `DECIMAL(12,2)` for money (or `DECIMAL(14,2)` for higher-volume deployments). Primary keys are `BIGINT UNSIGNED`. Foreign keys use compatible unsigned types. Business identifiers use readable unique codes alongside numeric keys.

Every master table has `created_at`, `updated_at`, and, where stated, `deleted_at`. Financial, stock, payment, commission, refund, notification-log, and audit rows are never soft-deleted. Use `CHECK` constraints for valid mutually exclusive line references/status values where practical and enforce remaining rules in the service layer and transactions.

## Table catalogue

### Identity and access

| Table | Purpose and important columns | Keys, constraints, indexes |
|---|---|---|
| `roles` | Named roles: `id`, `name`, `description`, `is_system` | PK `id`; unique `name`; soft delete |
| `permissions` | Permission catalogue: `id`, `code`, `module`, `action`, `description` | PK; unique `code`; index `(module, action)` |
| `role_permissions` | Role-to-permission mapping | PK `(role_id, permission_id)`; FKs to roles/permissions |
| `users` | Login account: `id`, `username`, `email`, `password_hash`, `staff_id` nullable, `is_active`, `last_login_at` | PK; unique username/email; unique non-null `staff_id`; FK staff; soft delete |
| `user_roles` | Supports a user holding one or more roles | PK `(user_id, role_id)`; FKs users/roles |
| `password_reset_tokens` | Hashed reset token, expiry, used time | PK; FK user; index `(user_id, expires_at)` |
| `login_activities` | Login/logout and failed-login history: user nullable, event, IP, user agent, occurred time | PK; FK user; index `(user_id, occurred_at)` |
| `audit_logs` | Append-only actor/action/module/record/IP/context history | PK; FK actor user nullable; indexes `(module, record_id, created_at)`, `(user_id, created_at)` |

### Master data

| Table | Purpose and important columns | Keys, constraints, indexes |
|---|---|---|
| `customers` | Customer code, name, mobile, email, gender, DOB, address, registration date, notes, status | PK; unique `customer_code`; indexes mobile, name, status; soft delete |
| `staff` | Staff code, contact/demographics, joining date, designation, salary, default commission type/value, status, photo path | PK; unique `staff_code`; indexes mobile, status, designation; soft delete |
| `service_categories` | Service grouping: name, description, active | PK; unique name; soft delete |
| `services` | Service code/name/category/description/default duration, price, tax rate, default discount, commission type/value, active | PK; unique code; FK category; indexes `(category_id, is_active)`, name; soft delete |
| `product_categories` | Product grouping | PK; unique name; soft delete |
| `suppliers` | Supplier code/name/company/contact/address/GSTIN/notes/status | PK; unique supplier code; index name; soft delete |
| `products` | Product code/name, SKU, barcode nullable, category, default supplier nullable, purchase/selling price, tax/discount defaults, current stock cache, minimum stock, unit, active | PK; unique product code/SKU; unique non-null barcode; FKs category/supplier; indexes `(category_id, is_active)`, current-stock lookup; soft delete |
| `expense_categories` | Expense grouping | PK; unique name; soft delete |
| `payment_methods` | Configurable method code/name, display order, active | PK; unique code; soft delete/active flag |
| `discount_rules` | Reusable percentage/fixed rules; scope (service/product/customer/bill/promotion), optional target/customer, dates, min bill, max discount, usage limit, coupon code | PK; unique non-null coupon code; FKs target category/item as modeled, customer nullable; index `(is_active, starts_at, ends_at)`; soft delete |

### Appointments

| Table | Purpose and important columns | Keys, constraints, indexes |
|---|---|---|
| `appointments` | Appointment code, customer, appointment date, overall start/end, status, notes, created/updated by, check-in/completion/cancellation timestamps | PK; unique appointment code; FK customer/users; indexes `(appointment_date, status)`, `(customer_id, appointment_date)` |
| `appointment_services` | One service per appointment line: service, assigned staff, start/end, duration snapshot, price snapshot, line status, notes | PK; FKs appointment/service/staff; indexes `(staff_id, start_at, end_at, status)`, appointment; no soft delete after booking |

### Purchasing and inventory

| Table | Purpose and important columns | Keys, constraints, indexes |
|---|---|---|
| `purchases` | Purchase code, supplier, supplier invoice number, date, subtotal/discount/tax/grand total, payment status, recorded by, status | PK; unique purchase code; unique `(supplier_id, supplier_invoice_number)` when reference exists; FK supplier/user; index purchase date |
| `purchase_items` | Product, quantity, unit cost, discount/tax snapshot, line total | PK; FKs purchase/product; unique `(purchase_id, line_no)`; index product |
| `stock_movements` | Immutable inventory ledger: product, movement type (`purchase`, `sale`, `adjustment`, `damage`, `return_in`, `return_out`), signed quantity, before/after quantity, unit cost, reference type/id, reason, actor, occurred time | PK; FKs product/user; indexes `(product_id, occurred_at)`, `(reference_type, reference_id)` |

### Billing, payments, and refunds

| Table | Purpose and important columns | Keys, constraints, indexes |
|---|---|---|
| `invoices` | Invoice number, customer, source appointment nullable, cashier, issue date, status, subtotal, item/bill discount, taxable total, tax total, charges, rounding, grand total, paid/refunded/balance cached totals, payment status, notes | PK; unique invoice number; unique non-null source appointment for a one-invoice-per-appointment policy; FKs customer/appointment/user; indexes `(issue_date, status)`, `(customer_id, issue_date)`, payment status |
| `invoice_items` | Immutable line snapshot: invoice, line number, item type (`service`/`product`), service or product FK, description, quantity, unit price/cost, assigned staff nullable, discount/tax/commission snapshots, line totals | PK; FKs invoice/service/product/staff; unique `(invoice_id, line_no)`; indexes invoice, product, service, staff; CHECK exactly one item FK matching type |
| `payments` | Payment code, invoice, method, amount, transaction/reference number, status, received by, paid time, notes | PK; unique payment code; FKs invoice/payment method/user; indexes `(invoice_id, status)`, `(paid_at, payment_method_id)` |
| `refunds` | Refund code, invoice, refund method, total, reason, status, requested/approved/processed users and times | PK; unique refund code; FKs invoice/payment method/users; indexes `(invoice_id, status)`, processed time |
| `refund_items` | The returned invoice item, accepted quantity (product), amount, return-to-stock flag, reason | PK; FKs refund/invoice item; unique `(refund_id, invoice_item_id)`; index invoice item |
| `staff_commissions` | Immutable payable/paid commission per finalized service line: staff, invoice item, customer, invoice, base amount, type/rate, calculated amount, status, calculated/paid dates | PK; unique `invoice_item_id` (one assignment per service line); FKs staff/invoice/invoice item/customer; indexes `(staff_id, status, calculated_at)` |

### Expenses, settings, and optional modules

| Table | Purpose and important columns | Keys, constraints, indexes |
|---|---|---|
| `expenses` | Expense code/category/description/amount/date/payment method/reference/attachment path/added by | PK; unique expense code; FKs category/payment method/user; indexes `(expense_date, category_id)`; attachment is a stored file path |
| `settings` | Typed key/value configuration: `setting_key`, JSON/text value, group, data type, updated by | PK; unique setting key; index setting group |
| `membership_plans` | Optional plan name, validity days, price, service/product discount benefits, terms, active | PK; unique name; soft delete |
| `customer_memberships` | Optional customer plan purchase/renewal, start/end date, price paid, status | PK; FKs customer/plan/invoice nullable; indexes `(customer_id, status, ends_at)` |
| `loyalty_point_transactions` | Optional immutable earned/redeemed/adjusted point ledger and source reference | PK; FK customer/user; indexes `(customer_id, occurred_at)`, reference |
| `notification_logs` | Optional outbound notification history: customer, appointment/invoice/payment nullable, channel, template/event, recipient, payload, provider reference, status, sent time/error | PK; FKs to related records; indexes `(status, created_at)`, customer |

## Relationship explanation

```
User --< UserRole >-- Role --< RolePermission >-- Permission
Customer --< Appointment --< AppointmentService >-- Service
                                      |             |
                                      `----------> Staff

Customer --< Invoice --< InvoiceItem >-- Product/Service
                  |          |               |
                  |          `-------------> Staff (service assignment)
                  +--< Payment >-- PaymentMethod
                  +--< Refund --< RefundItem >-- InvoiceItem
                  +--< StaffCommission >-- Staff

Supplier --< Purchase --< PurchaseItem >-- Product --< StockMovement
InvoiceItem (product sale) ------------------------------> StockMovement
RefundItem (accepted product return) --------------------> StockMovement
ExpenseCategory --< Expense >-- PaymentMethod
```

### Cardinality

- One customer has many appointments, invoices, memberships, loyalty transactions, and notification logs.
- One appointment has many appointment-service lines; each line has one service and zero/one assigned staff.
- One invoice has many invoice items, payments, and refunds. An appointment links to zero/one invoice under the proposed policy.
- An invoice item represents either one service or one product. It has zero/one staff assignment and may have many refund-item records over time.
- One supplier has many purchases; each purchase has many purchase items; each product has many stock movements.
- Roles and permissions are many-to-many through `role_permissions`; users and roles are many-to-many through `user_roles`.
- A staff profile may have zero/one login user. A user may link to zero/one staff profile.

## Deletion and retention

Soft delete: users, roles, customers, staff, categories, services, products, suppliers, discount rules, membership plans. Do not delete referenced master records; deactivate them instead. Retain all transactional and audit data permanently. Uploaded expense attachments and staff photos should be stored outside the public web root with only generated filenames/paths in the database.

## Integrity and performance notes

- Use database transactions for purchase completion, invoice finalization, payment/refund completion, and stock updates.
- Lock the affected `products` row (`SELECT ... FOR UPDATE`) while posting a stock movement to prevent concurrent overselling.
- Keep `products.current_stock` as an optimized cache only; `stock_movements` is the authoritative history.
- Index all foreign keys and frequently filtered date/status pairs listed above. Add report-specific composite indexes only after measuring real report queries.
- Store JSON only for flexible settings/audit context/notification payload; keep business relationships normalized in columns and join tables.
