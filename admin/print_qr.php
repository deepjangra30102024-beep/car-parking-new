<?php
// admin/print_qr.php - Printable QR Code Sticker Sheet / Windshield Tag
require_once __DIR__ . '/auth.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    die("Vehicle not found.");
}

$scanUrl = BASE_URL . 'v.php?token=' . $vehicle['unique_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Sticker - <?= htmlspecialchars($vehicle['vehicle_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px 10px;
        }
        .sticker-card {
            max-width: 380px;
            margin: 0 auto;
            background: #ffffff;
            border: 3px dashed #1e293b;
            border-radius: 18px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: relative;
        }
        .sticker-header {
            background: #0f172a;
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        .vehicle-tag {
            font-size: 1.4rem;
            font-weight: 800;
            background: #facc15;
            color: #000;
            display: inline-block;
            padding: 6px 18px;
            border-radius: 8px;
            border: 2px solid #000;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }
        .qr-wrapper {
            background: #ffffff;
            display: inline-block;
            padding: 10px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .instructions {
            font-size: 0.85rem;
            color: #475569;
            margin-top: 15px;
            font-weight: 500;
            line-height: 1.4;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .sticker-card {
                box-shadow: none;
                border: 2px solid #000;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>

<div class="text-center mb-4 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 fw-semibold shadow-sm">
        <i class="fa-solid fa-print me-2"></i> Print Sticker Now
    </button>
    <a href="vehicles_list.php" class="btn btn-outline-secondary px-3 ms-2">
        &larr; Back to Vehicles
    </a>
</div>

<!-- Windshield / Dashboard QR Sticker Format -->
<div class="sticker-card">
    <div class="sticker-header">
        <i class="fa-solid fa-square-parking me-1 text-warning"></i> Smart Parking Pass
    </div>

    <div class="vehicle-tag">
        <?= htmlspecialchars($vehicle['vehicle_number']) ?>
    </div>

    <div class="qr-wrapper mb-2">
        <img src="<?= BASE_URL . $vehicle['qr_image'] ?>" alt="Vehicle QR Code" style="width: 220px; height: 220px; display: block;">
    </div>

    <div class="instructions">
        <div class="fw-bold text-dark fs-6 mb-1">SCAN IN CASE OF EMERGENCY OR WRONG PARKING</div>
        <div>Scan this QR code with any mobile camera to contact owner or security instantly.</div>
    </div>

    <hr class="my-3">

    <div class="d-flex justify-content-between small text-muted">
        <span>Owner: <strong><?= htmlspecialchars($vehicle['owner_name']) ?></strong></span>
        <span>Slot: <strong><?= htmlspecialchars($vehicle['parking_slot'] ?: 'Standard') ?></strong></span>
    </div>
</div>

</body>
</html>
