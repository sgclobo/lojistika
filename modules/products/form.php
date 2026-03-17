<?php
require_roles(['admin', 'warehouse']);
$pageTitle = 'Product Form';

$id = (int) ($_GET['id'] ?? 0);
$editing = $id > 0;
$product = [
    'id' => 0,
    'category_id' => '',
    'supplier_id' => '',
    'code' => '',
    'name' => '',
    'unit' => 'unit',
    'min_stock' => '0',
    'is_active' => 1,
];

if ($editing) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$found) {
        set_flash('danger', 'Product not found.');
        redirect('index.php?page=products');
    }

    $product = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $supplierIdRaw = (int) ($_POST['supplier_id'] ?? 0);
    $supplierId = $supplierIdRaw > 0 ? $supplierIdRaw : null;
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $unit = $_POST['unit'] ?? 'unit';
    $minStock = (float) ($_POST['min_stock'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $userId = (int) current_user()['id'];

    if ($categoryId <= 0 || $code === '' || $name === '' || $minStock < 0) {
        set_flash('danger', 'Please fill all required fields correctly.');
        redirect('index.php?page=product_form' . ($editing ? '&id=' . $id : ''));
    }

    if (!in_array($unit, ['unit', 'liter', 'box', 'kg', 'pack'], true)) {
        set_flash('danger', 'Invalid unit selected.');
        redirect('index.php?page=product_form' . ($editing ? '&id=' . $id : ''));
    }

    if ($editing) {
        $sql = 'UPDATE products SET category_id = ?, supplier_id = ?, code = ?, name = ?, unit = ?, min_stock = ?, is_active = ?, updated_by = ? WHERE id = ?';
        $stmt = db()->prepare($sql);
        $stmt->bind_param('iisssdiii', $categoryId, $supplierId, $code, $name, $unit, $minStock, $isActive, $userId, $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Product updated successfully.');
    } else {
        $sql = 'INSERT INTO products (category_id, supplier_id, code, name, unit, min_stock, is_active, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = db()->prepare($sql);
        $stmt->bind_param('iisssdiii', $categoryId, $supplierId, $code, $name, $unit, $minStock, $isActive, $userId, $userId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Product created successfully.');
    }

    redirect('index.php?page=products');
}

$categories = db()->query('SELECT id, name FROM categories ORDER BY name ASC');
$suppliers = db()->query('SELECT id, name FROM suppliers ORDER BY name ASC');
$productNamesQuery = db()->query('SELECT DISTINCT category_id, name FROM products WHERE is_active = 1 ORDER BY name ASC');
$productNamesByCategory = [];
while ($nameRow = $productNamesQuery->fetch_assoc()) {
    $categoryId = (int) $nameRow['category_id'];
    if (!isset($productNamesByCategory[$categoryId])) {
        $productNamesByCategory[$categoryId] = [];
    }

    $productNamesByCategory[$categoryId][] = $nameRow['name'];
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header fw-semibold"><?= $editing ? 'Edit Product' : 'Add Product' ?></div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select category</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= (int) $product['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>>
                                <?= h($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Supplier (optional)</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">None</option>
                        <?php while ($sup = $suppliers->fetch_assoc()): ?>
                            <option value="<?= (int) $sup['id'] ?>" <?= (int) $product['supplier_id'] === (int) $sup['id'] ? 'selected' : '' ?>>
                                <?= h($sup['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" name="code" required value="<?= h($product['code']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Product Name</label>
                    <select class="form-select" name="name" data-product-name required>
                        <option value="">Select category first</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <select name="unit" class="form-select" required>
                        <?php foreach (['unit', 'liter', 'box', 'kg', 'pack'] as $unit): ?>
                            <option value="<?= h($unit) ?>" <?= $product['unit'] === $unit ? 'selected' : '' ?>><?= strtoupper(h($unit)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Minimum Stock</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="min_stock" required value="<?= h((string) $product['min_stock']) ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= (int) $product['is_active'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Save Product</button>
                    <a href="index.php?page=products" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.productNamesByCategory = <?= json_encode($productNamesByCategory, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.productFormCurrentCategory = <?= json_encode((int) $product['category_id']) ?>;
window.productFormCurrentName = <?= json_encode((string) $product['name'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
