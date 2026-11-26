<?php
// admin/index.php - Smart Parking Management Dashboard
$pageTitle = 'Dashboard - Smart QR Parking Management';
require_once __DIR__ . '/layout_header.php';

// Quick metrics queries
$totalVehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$parkedVehicles = $pdo->query("SELECT COUNT(*) FROM parking_logs WHERE status = 'PARKED'")->fetchColumn();
$todayCheckIns = $pdo->query("SELECT COUNT(*) FROM parking_logs WHERE DATE(check_in) = CURDATE()")->fetchColumn();
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(fee), 0) FROM parking_logs WHERE DATE(check_out) = CURDATE()")->fetchColumn();

// Recent check-in activities
$recentLogsStmt = $pdo->query("
    SELECT pl.*, v.vehicle_number, v.vehicle_type, v.owner_name, v.owner_phone 
    FROM parking_logs pl 
    JOIN vehicles v ON pl.vehicle_id = v.id 
    ORDER BY pl.id DESC LIMIT 8
");
$recentLogs = $recentLogsStmt->fetchAll();

// Recently registered vehicles
$recentVehicles = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Smart Parking Overview</h3>
        <p class="text-muted small mb-0">Live status of registered cars, parking occupancy, and QR activities</p>
    </div>
    <div class="d-flex gap-2">
        <a href="scanner.php" class="btn btn-warning fw-semibold shadow-sm text-dark">
            <i class="fa-solid fa-camera me-1"></i> Open Scanner
        </a>
        <a href="register_vehicle.php" class="btn btn-primary fw-semibold shadow-sm">
            <i class="fa-solid fa-plus-circle me-1"></i> Register New Car
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Vehicles</span>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalVehicles) ?></h3>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="fa-solid fa-car-side fs-4"></i>
                </div>
            </div>
            <div class="mt-3 small text-muted">Registered in database</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Currently Parked</span>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= number_format($parkedVehicles) ?></h3>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="fa-solid fa-square-parking fs-4"></i>
                </div>
            </div>
            <div class="mt-3 small text-success fw-medium">Active inside parking</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Today's Check-ins</span>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= number_format($todayCheckIns) ?></h3>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="fa-solid fa-right-to-bracket fs-4"></i>
                </div>
            </div>
            <div class="mt-3 small text-muted">Arrivals today</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Today's Revenue</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">₹<?= number_format($todayRevenue, 2) ?></h3>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="fa-solid fa-receipt fs-4"></i>
                </div>
            </div>
            <div class="mt-3 small text-muted">Parking collections</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Real-time Parking Logs -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent Check-in / Out Activity</h5>
                <a href="parking_logs.php" class="text-decoration-none small fw-semibold">View All Logs &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th class="ps-4">Vehicle</th>
                                <th>Owner</th>
                                <th>Check-in</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($recentLogs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No parking activities recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($log['vehicle_number']) ?></span>
                                            <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($log['vehicle_type']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($log['owner_name']) ?></td>
                                        <td><?= date('d M, h:i A', strtotime($log['check_in'])) ?></td>
                                        <td>
                                            <?php if ($log['status'] === 'PARKED'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">PARKED</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">EXITED</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="print_qr.php?id=<?= $log['vehicle_id'] ?>" target="_blank" class="btn btn-sm btn-light border" title="Print QR">
                                                <i class="fa-solid fa-qrcode"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Vehicle Registry -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-car me-2 text-primary"></i>Latest Vehicles</h5>
                <a href="vehicles_list.php" class="text-decoration-none small fw-semibold">Manage &rarr;</a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($recentVehicles)): ?>
                    <p class="text-muted text-center py-4 mb-0">No registered cars yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentVehicles as $v): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($v['vehicle_number']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($v['owner_name']) ?> | <?= htmlspecialchars($v['vehicle_type']) ?></small>
                                </div>
                                <a href="print_qr.php?id=<?= $v['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Print Sticker">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
