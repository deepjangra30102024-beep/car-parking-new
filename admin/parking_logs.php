<?php
// admin/parking_logs.php - Detailed Parking History and Check-in/Out records
$pageTitle = 'Parking Logs - Smart Parking';
require_once __DIR__ . '/layout_header.php';

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$totalRows = (int)$pdo->query("SELECT COUNT(*) FROM parking_logs")->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$stmt = $pdo->query("
    SELECT pl.*, v.vehicle_number, v.vehicle_type, v.owner_name, v.owner_phone 
    FROM parking_logs pl 
    JOIN vehicles v ON pl.vehicle_id = v.id 
    ORDER BY pl.id DESC
    LIMIT {$perPage} OFFSET {$offset}
");
$logs = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Parking Activity Logs</h3>
        <p class="text-muted small mb-0">Complete historical audit trail of vehicle arrivals, exits, and slots</p>
    </div>
    <a href="scanner.php" class="btn btn-primary btn-sm fw-semibold">
        <i class="fa-solid fa-camera me-1"></i> Open Scanner
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-secondary">
                    <tr>
                        <th class="ps-4">Log ID</th>
                        <th>Vehicle</th>
                        <th>Owner & Contact</th>
                        <th>Assigned Slot</th>
                        <th>Check-In Time</th>
                        <th>Check-Out Time</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Fee Charged</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No check-in/out records found yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-4 text-muted">#<?= $log['id'] ?></td>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($log['vehicle_number']) ?></span>
                                    <div class="text-muted small"><?= htmlspecialchars($log['vehicle_type']) ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($log['owner_name']) ?><br>
                                    <span class="text-muted small"><?= htmlspecialchars($log['owner_phone']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($log['parking_slot'] ?: 'General') ?></span>
                                </td>
                                <td><?= date('d M Y, h:i A', strtotime($log['check_in'])) ?></td>
                                <td>
                                    <?= $log['check_out'] ? date('d M Y, h:i A', strtotime($log['check_out'])) : '<span class="text-muted fst-italic">Still inside</span>' ?>
                                </td>
                                <td>
                                    <?php if ($log['status'] === 'PARKED'): ?>
                                        <span class="badge bg-success">PARKED</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">COMPLETED</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 fw-semibold">
                                    ₹<?= number_format($log['fee'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?> (<?= $totalRows ?> logs)</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
