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

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function login_user(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => (string) $user['full_name'],
        'role' => (string) $user['role'],
        'email' => (string) $user['email'],
        'departments' => (string) ($user['departments'] ?? ''),
    ];
}

function logout_user(): void
{
    unset($_SESSION['user']);
}

function authenticate_user(string $email, string $password): ?array
{
    $email = trim($email);

    if ($email === '' || $password === '') {
        return null;
    }

    $sql = 'SELECT id, full_name, email, password_hash, role, departments, is_active FROM users WHERE email = ? LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user || (int) $user['is_active'] !== 1) {
        return null;
    }

    $isValidPassword = password_verify($password, (string) $user['password_hash']);
    $legacySeedHash = '$2y$10$R7x4kS9qMpJ9T9M5oHZQrepp2QwXQ1oQ5dQb0KeTNOh2T4M84fF6q';

    if (!$isValidPassword && (string) $user['password_hash'] === $legacySeedHash && $password === 'ChangeMe@123') {
        $isValidPassword = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upgradeStmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $upgradeStmt->bind_param('si', $newHash, $user['id']);
        $upgradeStmt->execute();
        $upgradeStmt->close();
        $user['password_hash'] = $newHash;
    }

    if (!$isValidPassword) {
        return null;
    }

    return $user;
}

function available_departments(): array
{
    return [
        'Gabinete da Inspetora-Geral',
        'Gabinete da Subinspetora-Geral',
        'Gabinete do Fiscal Unico',
        'Departamento de Administracao e Financas',
        'Departamento de Operacoes',
        'Departamento de Plano Operacional, Risco Alimentar e Laboratorio',
        'Departamento de Metrologia e Padronizacao',
    ];
}

function default_department_for_role(string $role): string
{
    if (in_array($role, ['admin', 'warehouse'], true)) {
        return 'Departamento de Administracao e Financas';
    }

    return '';
}

function can_register_users(): bool
{
    $user = current_user();

    if ($user === null) {
        return false;
    }

    $email = strtolower(trim((string) ($user['email'] ?? '')));
    return $user['role'] === 'admin' && $email === 'drsergiolobo@gmail.com';
}

function require_register_users_permission(): void
{
    if (!can_register_users()) {
        set_flash('danger', 'Only the authorized administrator can register new users.');
        redirect('index.php?page=dashboard');
    }
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please sign in to continue.');
        redirect('index.php?page=login');
    }
}

function has_role(array $roles): bool
{
    $user = current_user();

    if ($user === null) {
        return false;
    }

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
