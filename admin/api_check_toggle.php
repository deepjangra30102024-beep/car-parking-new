<?php
// admin/api_check_toggle.php - Processes Guard Check-In / Check-Out via Camera Scan
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$token = trim($_POST['token'] ?? '');
$slot = trim($_POST['parking_slot'] ?? '');
$fee = floatval($_POST['fee'] ?? 0.00);

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'No vehicle token provided.']);
    exit;
}

// Find vehicle by token
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE unique_token = ? LIMIT 1");
$stmt->execute([$token]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    echo json_encode(['success' => false, 'message' => 'Invalid QR Code. Vehicle not found.']);
    exit;
}

// Check latest log
$logStmt = $pdo->prepare("SELECT * FROM parking_logs WHERE vehicle_id = ? ORDER BY id DESC LIMIT 1");
$logStmt->execute([$vehicle['id']]);
$lastLog = $logStmt->fetch();

$now = date('Y-m-d H:i:s');

if ($lastLog && $lastLog['status'] === 'PARKED') {
    // Perform Check-Out
    $updateStmt = $pdo->prepare("
        UPDATE parking_logs 
        SET check_out = ?, status = 'COMPLETED', fee = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$now, $fee, $lastLog['id']]);

    echo json_encode([
        'success' => true,
        'action' => 'CHECK_OUT',
        'vehicle' => $vehicle['vehicle_number'],
        'owner' => $vehicle['owner_name'],
        'time' => date('h:i A'),
        'message' => "Vehicle {$vehicle['vehicle_number']} Checked-Out successfully!"
    ]);
} else {
    // Perform Check-In
    $chosenSlot = !empty($slot) ? $slot : ($vehicle['parking_slot'] ?: 'General');
    $insertStmt = $pdo->prepare("
        INSERT INTO parking_logs (vehicle_id, check_in, parking_slot, status) 
        VALUES (?, ?, ?, 'PARKED')
    ");
    $insertStmt->execute([$vehicle['id'], $now, $chosenSlot]);

    echo json_encode([
        'success' => true,
        'action' => 'CHECK_IN',
        'vehicle' => $vehicle['vehicle_number'],
        'owner' => $vehicle['owner_name'],
        'slot' => $chosenSlot,
        'time' => date('h:i A'),
        'message' => "Vehicle {$vehicle['vehicle_number']} Checked-In to slot {$chosenSlot}!"
    ]);
}
