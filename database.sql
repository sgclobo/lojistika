CREATE DATABASE IF NOT EXISTS logistics_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE logistics_lms;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS requisition_items;
DROP TABLE IF EXISTS requisitions;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'warehouse', 'requester') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_categories_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(120) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(120) NULL,
    address VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_suppliers_name UNIQUE (name),
    CONSTRAINT fk_suppliers_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_suppliers_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    unit ENUM('unit', 'liter', 'box', 'kg', 'pack') NOT NULL DEFAULT 'unit',
    min_stock DECIMAL(12,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_products_code UNIQUE (code),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    CONSTRAINT fk_products_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_products_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE requisitions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    req_number VARCHAR(30) NOT NULL,
    requester_id INT UNSIGNED NOT NULL,
    department VARCHAR(120) NOT NULL,
    purpose VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    approved_by INT UNSIGNED NULL,
    approved_at DATETIME NULL,
    delivered_by INT UNSIGNED NULL,
    delivered_at DATETIME NULL,
    rejection_reason VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_requisitions_req_number UNIQUE (req_number),
    CONSTRAINT fk_requisitions_requester FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_requisitions_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_requisitions_delivered_by FOREIGN KEY (delivered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE requisition_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity_requested DECIMAL(12,2) NOT NULL,
    quantity_approved DECIMAL(12,2) NULL,
    notes VARCHAR(255) NULL,
    CONSTRAINT chk_requisition_items_qty_requested CHECK (quantity_requested > 0),
    CONSTRAINT fk_requisition_items_requisition FOREIGN KEY (requisition_id) REFERENCES requisitions(id) ON DELETE CASCADE,
    CONSTRAINT fk_requisition_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    movement_type ENUM('in', 'out') NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    reference_type ENUM('purchase', 'donation', 'adjustment', 'requisition', 'distribution', 'return') NOT NULL DEFAULT 'adjustment',
    reference_id INT UNSIGNED NULL,
    remarks VARCHAR(255) NULL,
    performed_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_stock_movements_quantity CHECK (quantity > 0),
    CONSTRAINT fk_stock_movements_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_stock_movements_user FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_stock_movements_product_date (product_id, created_at),
    INDEX idx_stock_movements_reference (reference_type, reference_id)
) ENGINE=InnoDB;

INSERT INTO users (full_name, email, password_hash, role) VALUES
('System Admin', 'admin@lms.local', '$2y$10$CRmXDEAJM/yn7o31.Ed5Xen3ZiqTn9uyGhtpEQHmZIag0Kyp8X84y', 'admin'),
('Warehouse Officer', 'warehouse@lms.local', '$2y$10$CRmXDEAJM/yn7o31.Ed5Xen3ZiqTn9uyGhtpEQHmZIag0Kyp8X84y', 'warehouse'),
('Department Requester', 'requester@lms.local', '$2y$10$CRmXDEAJM/yn7o31.Ed5Xen3ZiqTn9uyGhtpEQHmZIag0Kyp8X84y', 'requester');

INSERT INTO categories (name, description, created_by) VALUES
('Office Supplies', 'Paper, toner, pens and related items', 1),
('Cleaning Materials', 'Soap, floor cleaner, and sanitation items', 1),
('Maintenance Materials', 'Bulbs, tools, and maintenance consumables', 1);

INSERT INTO suppliers (name, contact_person, phone, email, address, created_by) VALUES
('National Office Depot', 'John Supplier', '+2600000001', 'sales@officedepot.local', 'Central Business District', 1),
('CleanPro Distributors', 'Mary Hygiene', '+2600000002', 'orders@cleanpro.local', 'Industrial Area', 1),
('Maintenance Hub', 'Peter Tools', '+2600000003', 'support@maintenancehub.local', 'North Park', 1);

INSERT INTO products (category_id, supplier_id, code, name, unit, min_stock, created_by) VALUES
(1, 1, 'OFF-PAPER-A4', 'A4 Printing Paper', 'box', 10, 1),
(1, 1, 'OFF-TONER-85A', 'Printer Toner 85A', 'unit', 5, 1),
(2, 2, 'CLN-LIQ-SOAP', 'Liquid Hand Soap', 'liter', 20, 1),
(3, 3, 'MNT-BULB-18W', 'LED Bulb 18W', 'unit', 15, 1);

INSERT INTO stock_movements (product_id, movement_type, quantity, unit_cost, reference_type, remarks, performed_by) VALUES
(1, 'in', 40, 12.50, 'purchase', 'Initial stock balance', 2),
(2, 'in', 10, 95.00, 'purchase', 'Initial stock balance', 2),
(3, 'in', 60, 5.00, 'purchase', 'Initial stock balance', 2),
(4, 'in', 30, 8.00, 'purchase', 'Initial stock balance', 2),
(1, 'out', 5, 0, 'distribution', 'Issued to Finance Department', 2),
(3, 'out', 8, 0, 'distribution', 'Issued to Admin Department', 2);

INSERT INTO requisitions (req_number, requester_id, department, purpose, status, created_at) VALUES
('REQ-20260317-0001', 3, 'HR Department', 'Monthly stationery requirement', 'pending', NOW()),
('REQ-20260317-0002', 3, 'Operations Department', 'Cleaning restock for Q1', 'approved', NOW());

INSERT INTO requisition_items (requisition_id, product_id, quantity_requested, quantity_approved) VALUES
(1, 1, 3, NULL),
(1, 2, 1, NULL),
(2, 3, 10, 10);

INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, reference_id, remarks, performed_by)
VALUES (3, 'out', 10, 'requisition', 2, 'Auto deduction for approved requisition REQ-20260317-0002', 1);

CREATE OR REPLACE VIEW v_product_stock AS
SELECT
    p.id AS product_id,
    p.code,
    p.name,
    p.unit,
    p.min_stock,
    COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE -sm.quantity END), 0) AS current_stock
FROM products p
LEFT JOIN stock_movements sm ON sm.product_id = p.id
GROUP BY p.id, p.code, p.name, p.unit, p.min_stock;
