<?php
require_roles(['admin', 'warehouse']);
$pageTitle = 'Products';

if (isset($_GET['delete'])) {
    require_roles(['admin']);
    $deleteId = (int) $_GET['delete'];
    $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Product deleted successfully.');
    redirect('index.php?page=products');
}

$q = trim($_GET['q'] ?? '');
[$currentPage, $perPage, $offset] = paginate(10);

$countSql = "SELECT COUNT(*) AS total
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id
             WHERE p.name LIKE CONCAT('%', ?, '%') OR p.code LIKE CONCAT('%', ?, '%') OR c.name LIKE CONCAT('%', ?, '%')";
$countStmt = db()->prepare($countSql);
$countStmt->bind_param('sss', $q, $q, $q);
$countStmt->execute();
$totalRows = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$totalPages = max(1, (int) ceil($totalRows / $perPage));

$listSql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            LEFT JOIN suppliers s ON s.id = p.supplier_id
            WHERE p.name LIKE CONCAT('%', ?, '%') OR p.code LIKE CONCAT('%', ?, '%') OR c.name LIKE CONCAT('%', ?, '%')
            ORDER BY p.id DESC
            LIMIT ? OFFSET ?";
$stmt = db()->prepare($listSql);
$stmt->bind_param('sssii', $q, $q, $q, $perPage, $offset);
$stmt->execute();
$products = $stmt->get_result();
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Products</span>
            <a href="index.php?page=product_form" class="btn btn-primary btn-sm">Add Product</a>
        </div>
        <div class="card-body">
            <form class="row g-2 mb-3" method="get">
                <input type="hidden" name="page" value="products">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="q" value="<?= h($q) ?>" placeholder="Search by code, name, category...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped align-middle table-sm">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Unit</th>
                        <th>Min Stock</th>
                        <th>Current Stock</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($products->num_rows === 0): ?>
                        <tr><td colspan="8" class="text-center text-muted">No products found.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($row['code']) ?></td>
                                <td><?= h($row['name']) ?></td>
                                <td><?= h($row['category_name']) ?></td>
                                <td><?= h($row['supplier_name']) ?></td>
                                <td><?= h($row['unit']) ?></td>
                                <td><?= h((string) $row['min_stock']) ?></td>
                                <td>
                                    <?php $stock = get_product_stock((int) $row['id']); ?>
                                    <span class="badge <?= $stock <= (float) $row['min_stock'] ? 'text-bg-danger' : 'text-bg-success' ?>">
                                        <?= h((string) $stock) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?page=product_form&id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <?php if (has_role(['admin'])): ?>
                                        <a href="index.php?page=products&delete=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this product?">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav>
                <ul class="pagination pagination-sm">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="index.php?page=products&q=<?= urlencode($q) ?>&p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php
$stmt->close();
