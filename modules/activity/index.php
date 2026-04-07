<?php
require_roles(['admin']);

// Ensure table exists (first-time setup via the helper)
log_activity('page_access', 'Accessed page: activity_monitor');

// Filters
$filterUser  = trim((string) ($_GET['user'] ?? ''));
$filterEvent = trim((string) ($_GET['event'] ?? ''));
$filterDate  = trim((string) ($_GET['date'] ?? ''));

// Build WHERE clause
$where  = [];
$params = [];
$types  = '';

if ($filterUser !== '') {
    $where[]  = 'user_name LIKE ?';
    $params[] = '%' . $filterUser . '%';
    $types   .= 's';
}

if ($filterEvent !== '' && in_array($filterEvent, ['login', 'logout', 'page_access'], true)) {
    $where[]  = 'event_type = ?';
    $params[] = $filterEvent;
    $types   .= 's';
}

if ($filterDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) {
    $where[]  = 'DATE(created_at) = ?';
    $params[] = $filterDate;
    $types   .= 's';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT id, user_id, user_name, event_type, description, ip_address, created_at
        FROM activity_log
        {$whereSql}
        ORDER BY id DESC
        LIMIT 500";

$stmt = db()->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result();
$stmt->close();

// Badge colour per event type
function event_badge(string $type): string
{
    return match ($type) {
        'login'       => 'text-bg-success',
        'logout'      => 'text-bg-warning',
        'page_access' => 'text-bg-info',
        default       => 'text-bg-secondary',
    };
}

function event_label(string $type): string
{
    return match ($type) {
        'login'       => 'Login',
        'logout'      => 'Logout',
        'page_access' => 'Page Access',
        default       => ucfirst($type),
    };
}
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-activity me-2"></i>Activity Monitor</h5>
        <a class="btn btn-sm btn-outline-secondary" href="index.php?page=activity_monitor" title="Reset filters">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="activity_monitor">
                <div class="col-sm-4">
                    <label class="form-label mb-1 small">User</label>
                    <input class="form-control form-control-sm" name="user" placeholder="Search by name…"
                           value="<?= h($filterUser) ?>">
                </div>
                <div class="col-sm-3">
                    <label class="form-label mb-1 small">Event Type</label>
                    <select class="form-select form-select-sm" name="event">
                        <option value="">All events</option>
                        <option value="login"       <?= $filterEvent === 'login'       ? 'selected' : '' ?>>Login</option>
                        <option value="logout"      <?= $filterEvent === 'logout'      ? 'selected' : '' ?>>Logout</option>
                        <option value="page_access" <?= $filterEvent === 'page_access' ? 'selected' : '' ?>>Page Access</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label mb-1 small">Date</label>
                    <input type="date" class="form-control form-control-sm" name="date"
                           value="<?= h($filterDate) ?>">
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-sm btn-primary w-100" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Log Table -->
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-sm align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th style="width:150px">Date / Time</th>
                        <th>User</th>
                        <th style="width:120px">Event</th>
                        <th>Description</th>
                        <th style="width:130px">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 0;
                    while ($row = $logs->fetch_assoc()):
                        $count++;
                    ?>
                        <tr>
                            <td class="text-muted"><?= (int) $row['id'] ?></td>
                            <td class="text-muted text-nowrap">
                                <?= h(date('d/m/Y H:i:s', strtotime($row['created_at']))) ?>
                            </td>
                            <td>
                                <?= $row['user_name'] !== '' ? h($row['user_name']) : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td>
                                <span class="badge <?= event_badge($row['event_type']) ?>">
                                    <?= event_label(h($row['event_type'])) ?>
                                </span>
                            </td>
                            <td><?= h($row['description']) ?></td>
                            <td class="text-muted font-monospace"><?= h($row['ip_address']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($count === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No activity records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($count >= 500): ?>
        <div class="card-footer text-muted small">
            Showing last 500 records. Use filters to narrow results.
        </div>
        <?php endif; ?>
    </div>
</div>
