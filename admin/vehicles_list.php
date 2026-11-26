<?php
// admin/vehicles_list.php - Full list of vehicles with QR codes and action options
$pageTitle = 'Vehicles List - Smart Parking';
require_once __DIR__ . '/layout_header.php';

// Search filter & Pagination
$search = trim($_GET['search'] ?? '');
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$whereSql = '';
$params = [];
if (!empty($search)) {
    $whereSql = "WHERE v.vehicle_number LIKE ? OR v.owner_name LIKE ? OR v.owner_phone LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v {$whereSql}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$baseSelect = "
    SELECT v.*,
        (SELECT pl.status FROM parking_logs pl WHERE pl.vehicle_id = v.id ORDER BY pl.id DESC LIMIT 1) AS parking_status,
        (SELECT pl.fee FROM parking_logs pl WHERE pl.vehicle_id = v.id ORDER BY pl.id DESC LIMIT 1) AS last_fee
    FROM vehicles v
";
$stmt = $pdo->prepare("{$baseSelect} {$whereSql} ORDER BY v.id DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$vehicles = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-1">Registered Vehicles & QR Codes</h3>
        <p class="text-muted small mb-0">View all registered vehicles, test QR links, or download stickers</p>
    </div>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search number, owner..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-sm btn-dark ms-1"><i class="fa-solid fa-search"></i></button>
        </form>
        <a href="register_vehicle.php" class="btn btn-primary btn-sm fw-semibold">
            <i class="fa-solid fa-plus-circle me-1"></i> Register Car
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-secondary">
                    <tr>
                        <th class="ps-4">QR Code</th>
                        <th>Vehicle Details</th>
                        <th>Owner & Emergency</th>
                        <th>Assigned Slot</th>
                        <th>Parking Status</th>
                        <th>Created Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($vehicles)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-car-side fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No vehicles found. <a href="register_vehicle.php">Register a vehicle now</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $row): ?>
                            <tr>
                                <td class="ps-4" style="width: 80px;">
                                    <a href="<?= BASE_URL . $row['qr_image'] ?>" target="_blank">
                                        <img src="<?= BASE_URL . $row['qr_image'] ?>" alt="QR" class="img-thumbnail" style="width: 55px; height: 55px; object-fit: contain;">
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-dark fs-6 px-2 py-1"><?= htmlspecialchars($row['vehicle_number']) ?></span>
                                    <div class="text-muted mt-1 small">
                                        <?= htmlspecialchars($row['vehicle_type']) ?> - <?= htmlspecialchars($row['vehicle_model'] ?: 'N/A') ?> 
                                        <?= $row['color'] ? '(' . htmlspecialchars($row['color']) . ')' : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['owner_name']) ?></strong><br>
                                    <a href="tel:<?= $row['owner_phone'] ?>" class="text-decoration-none text-muted small"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($row['owner_phone']) ?></a>
                                    <div class="text-danger small mt-1">
                                        <i class="fa-solid fa-shield-halved me-1"></i>Emg: <?= htmlspecialchars($row['emergency_phone']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['parking_slot'] ?: 'General') ?></span>
                                </td>
                                <td>
                                    <?php if (($row['parking_status'] ?? '') === 'PARKED'): ?>
                                        <span class="badge bg-success">PARKED</span>
                                    <?php elseif (($row['parking_status'] ?? '') === 'COMPLETED'): ?>
                                        <span class="badge bg-secondary">CHECKED OUT</span>
                                        <div class="text-muted small mt-1">Fee: <?= number_format((float)($row['last_fee'] ?? 0), 2) ?></div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Not Parked</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <?php $isParked = (($row['parking_status'] ?? '') === 'PARKED'); ?>
                                        <button type="button" onclick="toggleCheck('<?= htmlspecialchars($row['unique_token']) ?>', '<?= htmlspecialchars($row['vehicle_number']) ?>')" class="btn btn-sm <?= $isParked ? 'btn-warning' : 'btn-primary' ?>" title="Check-In / Check-Out">
                                            <i class="fa-solid fa-right-left"></i> <?= $isParked ? 'Check Out' : 'Check In' ?>
                                        </button>
                                        <a href="<?= BASE_URL . 'v.php?token=' . $row['unique_token'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Preview Public Scan Page">
                                            <i class="fa-solid fa-eye"></i> View Scan
                                        </a>
                                        <a href="print_qr.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-success" title="Print Sticker">
                                            <i class="fa-solid fa-print"></i> Print Sticker
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <?php $searchQuery = !empty($search) ? '&search=' . urlencode($search) : ''; ?>
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?> (<?= $totalRows ?> vehicles)</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $searchQuery ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $searchQuery ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $searchQuery ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleCheck(token, vehicleNumber) {
    let feeInput = prompt("Enter Parking Fee / Rate for " + vehicleNumber + " (0 for Check-In or Free):", "0");
    if (feeInput === null) return;

    let formData = new FormData();
    formData.append('token', token);
    formData.append('fee', parseFloat(feeInput) || 0);

    fetch('api_check_toggle.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || (data.success ? 'Updated!' : 'Failed!'));
        if (data.success) location.reload();
    })
    .catch(() => alert('Network error while updating status.'));
}
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
