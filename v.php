<?php
// Public Vehicle Details & Instant Contact Page
require_once __DIR__ . '/config/db.php';

$token = trim($_GET['token'] ?? '');

if (empty($token)) {
    die("Invalid request: No vehicle token provided.");
}

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE unique_token = ? LIMIT 1");
$stmt->execute([$token]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    die("<h3>Vehicle Not Found</h3><p>The scanned QR code is either invalid or expired.</p>");
}

// Format numbers for direct phone / WhatsApp links
$cleanPhone = preg_replace('/[^0-9]/', '', $vehicle['owner_phone']);
$cleanEmergencyPhone = preg_replace('/[^0-9]/', '', $vehicle['emergency_phone']);

// Pre-filled WhatsApp message
$waMessage = urlencode("Hello " . $vehicle['owner_name'] . ", regarding your vehicle " . $vehicle['vehicle_number'] . " parked at slot: " . ($vehicle['parking_slot'] ?: 'N/A') . ". Please check.");
$waLink = "https://wa.me/" . $cleanPhone . "?text=" . $waMessage;

// Check parking status
$logStmt = $pdo->prepare("SELECT * FROM parking_logs WHERE vehicle_id = ? ORDER BY id DESC LIMIT 1");
$logStmt->execute([$vehicle['id']]);
$lastLog = $logStmt->fetch();
$isParked = ($lastLog && $lastLog['status'] === 'PARKED');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Details - <?= htmlspecialchars($vehicle['vehicle_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 12px;
        }
        .vehicle-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 480px;
            width: 100%;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        .plate-badge {
            background: #facc15;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 2px;
            padding: 8px 18px;
            border-radius: 8px;
            display: inline-block;
            font-size: 1.25rem;
            border: 2px solid #000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        }
        .action-btn {
            border-radius: 12px;
            padding: 14px;
            font-size: 1.05rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="vehicle-card">
    <div class="card-header-custom">
        <p class="mb-1 text-uppercase text-light small fw-semibold" style="letter-spacing: 1px;">Smart QR Parking System</p>
        <div class="plate-badge mb-2">
            <i class="fa-solid fa-car-side me-2"></i><?= htmlspecialchars($vehicle['vehicle_number']) ?>
        </div>
        <div class="mt-2">
            <?php if ($isParked): ?>
                <span class="badge bg-success status-badge"><i class="fa-solid fa-circle-check me-1"></i> Currently Parked (Slot: <?= htmlspecialchars($vehicle['parking_slot'] ?: 'Assigned') ?>)</span>
            <?php else: ?>
                <span class="badge bg-secondary status-badge"><i class="fa-solid fa-circle-info me-1"></i> Status: Checked Out / Idle</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="p-4">
        <!-- Vehicle Info List -->
        <div class="bg-light rounded-3 p-3 mb-4">
            <div class="row g-2 text-muted small">
                <div class="col-6">
                    <strong>Vehicle Type:</strong><br>
                    <span class="text-dark fs-6"><?= htmlspecialchars($vehicle['vehicle_type']) ?></span>
                </div>
                <div class="col-6">
                    <strong>Model / Color:</strong><br>
                    <span class="text-dark fs-6"><?= htmlspecialchars(($vehicle['vehicle_model'] ?: 'N/A') . ' ' . ($vehicle['color'] ? '(' . $vehicle['color'] . ')' : '')) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <strong>Registered Owner:</strong><br>
                    <span class="text-dark fs-6"><?= htmlspecialchars($vehicle['owner_name']) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <strong>Parking Slot:</strong><br>
                    <span class="text-dark fs-6"><?= htmlspecialchars($vehicle['parking_slot'] ?: 'Not Assigned') ?></span>
                </div>
            </div>
        </div>

        <h6 class="text-secondary text-uppercase fw-bold mb-3" style="font-size: 0.8rem; letter-spacing: 0.5px;">Quick Contact Options</h6>

        <!-- Action Buttons -->
        <div class="d-grid gap-2 mb-3">
            <!-- Call Owner -->
            <a href="tel:<?= htmlspecialchars($vehicle['owner_phone']) ?>" class="btn btn-primary action-btn">
                <i class="fa-solid fa-phone-volume fs-5"></i> Call Car Owner
            </a>

            <!-- WhatsApp Message -->
            <a href="<?= $waLink ?>" target="_blank" class="btn btn-success action-btn">
                <i class="fa-brands fa-whatsapp fs-5"></i> WhatsApp Car Owner
            </a>

            <!-- Emergency Contact -->
            <a href="tel:<?= htmlspecialchars($vehicle['emergency_phone']) ?>" class="btn btn-danger action-btn">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i> Call Emergency Contact
                <?php if (!empty($vehicle['emergency_contact_name'])): ?>
                    <span class="small opacity-75">(<?= htmlspecialchars($vehicle['emergency_contact_name']) ?>)</span>
                <?php endif; ?>
            </a>
        </div>

        <?php if (!empty($vehicle['notes'])): ?>
            <div class="alert alert-info py-2 px-3 small mb-0">
                <i class="fa-solid fa-circle-info me-1"></i> <strong>Note:</strong> <?= htmlspecialchars($vehicle['notes']) ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <span class="text-muted" style="font-size: 0.75rem;">Powered by Smart QR Car Parking System</span>
        </div>
    </div>
</div>

</body>
</html>
