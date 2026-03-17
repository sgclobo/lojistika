<?php

declare(strict_types=1);

require_once __DIR__ . '/db_connect.php';

function db(): mysqli
{
    return db_connect();
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function current_user(): array
{
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = [
            'id' => 1,
            'full_name' => 'System Admin',
            'role' => 'admin',
        ];
    }

    return $_SESSION['user'];
}

function has_role(array $roles): bool
{
    $user = current_user();
    return in_array($user['role'], $roles, true);
}

function require_roles(array $roles): void
{
    if (!has_role($roles)) {
        set_flash('danger', 'You do not have permission to access this section.');
        redirect('index.php?page=dashboard');
    }
}

function get_product_stock(int $productId): float
{
    $sql = "SELECT COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE -quantity END), 0) AS balance
            FROM stock_movements
            WHERE product_id = ?";

    $stmt = db()->prepare($sql);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (float) ($result['balance'] ?? 0);
}

function record_stock_movement(
    int $productId,
    string $movementType,
    float $quantity,
    float $unitCost,
    string $referenceType,
    ?int $referenceId,
    ?string $remarks,
    int $performedBy
): bool {
    if ($quantity <= 0) {
        return false;
    }

    if ($movementType === 'out' && get_product_stock($productId) < $quantity) {
        return false;
    }

    $sql = "INSERT INTO stock_movements
            (product_id, movement_type, quantity, unit_cost, reference_type, reference_id, remarks, performed_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = db()->prepare($sql);
    $stmt->bind_param(
        'isddsisi',
        $productId,
        $movementType,
        $quantity,
        $unitCost,
        $referenceType,
        $referenceId,
        $remarks,
        $performedBy
    );

    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function generate_requisition_number(): string
{
    return 'REQ-' . date('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
}

function paginate(int $defaultPerPage = 10): array
{
    $page = max(1, (int) ($_GET['p'] ?? 1));
    $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? $defaultPerPage)));
    $offset = ($page - 1) * $perPage;

    return [$page, $perPage, $offset];
}
