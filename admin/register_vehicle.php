<?php
// admin/register_vehicle.php - Vehicle Registration and QR Code generation
$pageTitle = 'Register New Vehicle - Smart Parking';
require_once __DIR__ . '/layout_header.php';

$success = '';
$error = '';
$generatedVehicle = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleNumber = strtoupper(trim($_POST['vehicle_number'] ?? ''));
    $vehicleType = trim($_POST['vehicle_type'] ?? 'Car');
    $vehicleModel = trim($_POST['vehicle_model'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $ownerName = trim($_POST['owner_name'] ?? '');
    $ownerPhone = trim($_POST['owner_phone'] ?? '');
    $emergencyContactName = trim($_POST['emergency_contact_name'] ?? '');
    $emergencyPhone = trim($_POST['emergency_phone'] ?? '');
    $parkingSlot = trim($_POST['parking_slot'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($vehicleNumber) || empty($ownerName) || empty($ownerPhone) || empty($emergencyPhone)) {
        $error = "Please fill in all mandatory fields (Vehicle Number, Owner Name, Phone, and Emergency Contact).";
    } else {
        // Check if vehicle already exists
        $checkStmt = $pdo->prepare("SELECT id FROM vehicles WHERE vehicle_number = ?");
        $checkStmt->execute([$vehicleNumber]);
        if ($checkStmt->fetch()) {
            $error = "Vehicle number '$vehicleNumber' is already registered in the system!";
        } else {
            // Generate secure unique token
            $token = bin2hex(random_bytes(16));
            $scanUrl = BASE_URL . 'v.php?token=' . $token;

            // Generate QR Code URL via reliable QR API (or save locally)
            // Using qrserver API with fallback image path
            $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($scanUrl);

            // Save QR image locally to uploads/qrcodes/
            $localQrFileName = $token . '.png';
            $localQrPath = __DIR__ . '/../uploads/qrcodes/' . $localQrFileName;

            // Attempt to download and save local copy if curl/allow_url_fopen is active
            @$qrData = file_get_contents($qrApiUrl);
            if ($qrData) {
                file_put_contents($localQrPath, $qrData);
                $qrImageDb = 'uploads/qrcodes/' . $localQrFileName;
            } else {
                $qrImageDb = $qrApiUrl;
            }

            // Insert into database
            $insert = $pdo->prepare("
                INSERT INTO vehicles (
                    unique_token, vehicle_number, vehicle_type, vehicle_model, color,
                    owner_name, owner_phone, emergency_contact_name, emergency_phone,
                    parking_slot, notes, qr_image
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insert->execute([
                $token, $vehicleNumber, $vehicleType, $vehicleModel, $color,
                $ownerName, $ownerPhone, $emergencyContactName, $emergencyPhone,
                $parkingSlot, $notes, $qrImageDb
            ]);

            $newId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
            $stmt->execute([$newId]);
            $generatedVehicle = $stmt->fetch();

            $success = "Vehicle registered successfully! QR Code has been generated.";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Vehicle Registration</h3>
        <p class="text-muted small mb-0">Register a car to generate a unique smart QR code sticker</p>
    </div>
    <a href="vehicles_list.php" class="btn btn-outline-secondary">
        <i class="fa-solid fa-list me-1"></i> View All Vehicles
    </a>
</div>

<?php if ($success && $generatedVehicle): ?>
    <div class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-4 rounded-3">
        <div>
            <i class="fa-solid fa-circle-check fs-4 me-2 align-middle"></i>
            <strong><?= $success ?></strong>
        </div>
        <div>
            <a href="print_qr.php?id=<?= $generatedVehicle['id'] ?>" target="_blank" class="btn btn-success btn-sm fw-semibold">
                <i class="fa-solid fa-print me-1"></i> Print / Download QR Sticker
            </a>
        </div>
    </div>

    <!-- Quick Preview Card for freshly generated vehicle -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?= BASE_URL . $generatedVehicle['qr_image'] ?>" alt="QR Code" class="img-fluid rounded border p-2 bg-white" style="max-width: 180px;">
                    <div class="mt-2">
                        <a href="<?= BASE_URL . 'v.php?token=' . $generatedVehicle['unique_token'] ?>" target="_blank" class="small text-primary text-decoration-none">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Test Public View
                        </a>
                    </div>
                </div>
                <div class="col-md-9">
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($generatedVehicle['vehicle_number']) ?></h5>
                    <p class="text-muted mb-2"><?= htmlspecialchars($generatedVehicle['vehicle_type']) ?> - <?= htmlspecialchars($generatedVehicle['vehicle_model'] ?: 'No model') ?> (<?= htmlspecialchars($generatedVehicle['color'] ?: 'Color N/A') ?>)</p>
                    
                    <div class="row g-2 small">
                        <div class="col-sm-6"><strong>Owner:</strong> <?= htmlspecialchars($generatedVehicle['owner_name']) ?> (<?= htmlspecialchars($generatedVehicle['owner_phone']) ?>)</div>
                        <div class="col-sm-6"><strong>Emergency:</strong> <?= htmlspecialchars($generatedVehicle['emergency_contact_name'] ?: 'Contact') ?> (<?= htmlspecialchars($generatedVehicle['emergency_phone']) ?>)</div>
                        <div class="col-sm-6"><strong>Parking Slot:</strong> <?= htmlspecialchars($generatedVehicle['parking_slot'] ?: 'Not assigned') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger p-3 mb-4 rounded-3">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="POST">
            <h5 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-car-side me-2"></i>Vehicle Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Vehicle Plate Number *</label>
                    <input type="text" name="vehicle_number" class="form-control" placeholder="e.g. DL 01 AB 1234" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Vehicle Type</label>
                    <select name="vehicle_type" class="form-select">
                        <option value="Car" selected>Car</option>
                        <option value="SUV">SUV</option>
                        <option value="Bike">Bike / Two-Wheeler</option>
                        <option value="Truck">Truck / Commercial</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Model Name</label>
                    <input type="text" name="vehicle_model" class="form-control" placeholder="e.g. Honda City">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Color</label>
                    <input type="text" name="color" class="form-control" placeholder="e.g. White">
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-address-book me-2"></i>Owner & Emergency Contact Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Owner Full Name *</label>
                    <input type="text" name="owner_name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Owner Phone Number (with Country Code) *</label>
                    <input type="text" name="owner_phone" class="form-control" placeholder="" required>
                    <div class="form-text small">Used for direct calls & WhatsApp alerts</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Emergency Contact Person Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" placeholder="e.g. Priya (Spouse / Relative)">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Emergency Contact Phone Number *</label>
                    <input type="text" name="emergency_phone" class="form-control" placeholder="" required>
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-square-parking me-2"></i>Parking Information</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Assigned Parking Slot</label>
                    <input type="text" name="parking_slot" class="form-control" placeholder="e.g. B2-Slot-14">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Additional Notes / Instructions</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Do not park in front of gate, VIP pass, etc.">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="reset" class="btn btn-light px-4">Reset</button>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="fa-solid fa-qrcode me-2"></i>Register & Generate QR Code
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
