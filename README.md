# Logistics Management System (LMS)

## Stack

- PHP (no framework)
- MySQL / MariaDB
- Bootstrap 5 + Vanilla JS

## Setup

1. Create database and tables:
   - Import `database.sql` into MySQL/MariaDB.
2. Configure database credentials:
   - Preferred: environment variables `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME`
   - Alternative (shared hosting): copy `config/db_credentials.example.php` to `config/db_credentials.php` and set real values.
3. Serve the `logistics` folder from Apache/Nginx.
4. Open:
   - `http://localhost/logistics/index.php`

## Demo roles

Use the sidebar role switcher:

- Admin
- Warehouse
- Requester

## Step Coverage

1. Database schema and sample data: complete.
2. Product module (list/add/edit/delete): complete.
3. Stock management (IN/OUT via movements): complete.
4. Requisition workflow (pending -> approved/rejected -> delivered): complete.
5. Reports (consumption, stock snapshot, movement logs): complete.

## Security + Data Integrity

- Prepared statements for write/read flows.
- Role-based access checks by module.
- Stock cannot go negative for OUT movements.
- No direct stock editing; stock is calculated from `stock_movements` only.

## Manual Test Scenarios

1. Add Product
   - Go to Products -> Add Product.
   - Fill code/name/category/unit/min stock.
   - Verify item appears in product list.

2. Add Stock
   - Go to Stock In.
   - Select product and quantity.
   - Submit and verify movement is logged.

3. Create Requisition
   - Switch role to Requester.
   - Go to Requisitions and create request with one or more items.
   - Verify status = pending.

4. Approve Requisition
   - Switch role to Admin.
   - Approve pending requisition.
   - Verify status = approved and stock is deducted automatically.

5. Deliver Requisition
   - Admin or Warehouse marks approved requisition as delivered.
   - Verify status = delivered.

## Notes for Production

- Replace demo role switcher with login/auth.
- Store real password hashes and enforce password policy.
- Enable HTTPS and secure session settings.
- Do not use `root` as production DB user. Use your hosting database user/password.
