<?php
// admin/scanner.php - Live Guard Camera QR Scanner
$pageTitle = 'Guard QR Scanner - Smart Parking';
require_once __DIR__ . '/layout_header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-camera text-primary me-2"></i>Guard Live QR Scanner</h4>
                <p class="text-muted small mb-0">Point camera at vehicle QR code to automatically Check-In / Check-Out</p>
            </div>
            <a href="parking_logs.php" class="btn btn-outline-secondary btn-sm">Logs History</a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
            <div class="card-body p-4 text-center">
                <!-- Camera Viewport Container -->
                <div id="reader" style="width: 100%; min-height: 280px; border-radius: 12px; overflow: hidden; background: #000;" class="mx-auto"></div>

                <div class="mt-3">
                    <span class="badge bg-primary px-3 py-2" id="scannerStatus">
                        <i class="fa-solid fa-spinner fa-spin me-1"></i> Starting Camera...
                    </span>
                </div>

                <div class="mt-3 row g-2">
                    <div class="col-6">
                        <input type="text" id="manualToken" class="form-control form-control-sm" placeholder="Or paste scan URL / token here...">
                    </div>
                    <div class="col-3">
                        <input type="number" id="parkingFee" class="form-control form-control-sm" step="0.01" min="0" value="0" placeholder="Fee (Rate)">
                    </div>
                    <div class="col-3">
                        <button type="button" onclick="submitToken(document.getElementById('manualToken').value)" class="btn btn-dark btn-sm w-100">
                            Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan Result Notification Area -->
        <div id="resultBox" style="display: none;" class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 text-center">
                <div id="resultIcon" class="mb-2"></div>
                <h5 id="resultTitle" class="fw-bold mb-1"></h5>
                <p id="resultMsg" class="text-muted small mb-3"></p>
                <button type="button" onclick="resumeScanning()" class="btn btn-primary btn-sm px-4">
                    <i class="fa-solid fa-camera me-1"></i> Scan Next Vehicle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- HTML5-QRCode Scanner Library CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let html5QrcodeScanner = null;
let isProcessing = false;

function onScanSuccess(decodedText, decodedResult) {
    if (isProcessing) return;
    isProcessing = true;

    // Check if the scanned string is a full URL (v.php?token=xyz) or direct token
    let token = decodedText;
    if (decodedText.includes('token=')) {
        try {
            let url = new URL(decodedText);
            token = url.searchParams.get("token") || decodedText;
        } catch (e) {
            let parts = decodedText.split('token=');
            if (parts[1]) token = parts[1].split('&')[0];
        }
    }

    submitToken(token);
}

function submitToken(token) {
    token = token.trim();
    if (!token) {
        alert("Please provide a valid token.");
        isProcessing = false;
        return;
    }

    // Stop scanning temporarily
    if (html5QrcodeScanner) {
        html5QrcodeScanner.pause(true);
    }

    document.getElementById('scannerStatus').innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing vehicle...';

    // Send to backend API
    let formData = new FormData();
    formData.append('token', token);
    formData.append('fee', document.getElementById('parkingFee').value || '0');

    fetch('api_check_toggle.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        let resultBox = document.getElementById('resultBox');
        let resultIcon = document.getElementById('resultIcon');
        let resultTitle = document.getElementById('resultTitle');
        let resultMsg = document.getElementById('resultMsg');

        resultBox.style.display = 'block';

        if (data.success) {
            if (data.action === 'CHECK_IN') {
                resultIcon.innerHTML = '<i class="fa-solid fa-circle-check text-success fs-1"></i>';
                resultTitle.innerText = "Vehicle Checked-In!";
                resultMsg.innerText = `${data.vehicle} (${data.owner}) parked in ${data.slot} at ${data.time}`;
            } else {
                resultIcon.innerHTML = '<i class="fa-solid fa-circle-xmark text-info fs-1"></i>';
                resultTitle.innerText = "Vehicle Checked-Out!";
                resultMsg.innerText = `${data.vehicle} (${data.owner}) exited at ${data.time}`;
            }
            document.getElementById('scannerStatus').innerHTML = '<i class="fa-solid fa-check me-1"></i> Success!';
        } else {
            resultIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger fs-1"></i>';
            resultTitle.innerText = "Scan Failed";
            resultMsg.innerText = data.message || "Unknown error occurred.";
            document.getElementById('scannerStatus').innerHTML = '<i class="fa-solid fa-xmark me-1"></i> Scan Error';
        }
    })
    .catch(err => {
        alert("Server request failed. Please check your connection.");
        resumeScanning();
    });
}

function resumeScanning() {
    document.getElementById('resultBox').style.display = 'none';
    document.getElementById('scannerStatus').innerHTML = '<i class="fa-solid fa-video me-1"></i> Camera Active';
    isProcessing = false;
    if (html5QrcodeScanner) {
        html5QrcodeScanner.resume();
    }
}

window.addEventListener('load', () => {
    html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", 
        { 
            fps: 10, 
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true
        }, 
        /* verbose= */ false
    );
    html5QrcodeScanner.render(onScanSuccess);
    document.getElementById('scannerStatus').innerHTML = '<i class="fa-solid fa-video me-1"></i> Camera Active';
});
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
