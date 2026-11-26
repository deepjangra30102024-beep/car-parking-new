<?php
// admin/layout_header.php
require_once __DIR__ . '/auth.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Smart Parking Management' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2563eb;
            --dark-bg: #0f172a;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: #1e293b;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            color: #e2e8f0;
            z-index: 1000;
        }
        .sidebar .brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid #334155;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            margin: 4px 12px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background: #2563eb;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            min-height: 100vh;
        }
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <i class="fa-solid fa-square-parking text-primary fs-3"></i>
        <span>SmartQR Park</span>
    </div>
    <ul class="nav flex-column mt-3">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="register_vehicle.php" class="nav-link <?= $currentPage === 'register_vehicle.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-plus-circle"></i> Register Vehicle
            </a>
        </li>
        <li class="nav-item">
            <a href="vehicles_list.php" class="nav-link <?= $currentPage === 'vehicles_list.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-car"></i> All Vehicles & QRs
            </a>
        </li>
        <li class="nav-item">
            <a href="scanner.php" class="nav-link <?= $currentPage === 'scanner.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-qrcode"></i> Guard Live Scanner
            </a>
        </li>
        <li class="nav-item">
            <a href="parking_logs.php" class="nav-link <?= $currentPage === 'parking_logs.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Parking Logs
            </a>
        </li>
        <li class="nav-item">
            <a href="change_password.php" class="nav-link <?= $currentPage === 'change_password.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-key"></i> Change Password
            </a>
        </li>
        <li class="nav-item mt-4">
            <a href="logout.php" class="nav-link text-danger">
                <i class="fa-solid fa-right-from-bracket"></i> Logout (<?= htmlspecialchars($_SESSION['admin_user']) ?>)
            </a>
        </li>
    </ul>
</div>

<div class="main-content">
