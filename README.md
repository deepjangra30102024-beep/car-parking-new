# 🚗 Smart QR Code Based Car Parking Management System (Core PHP)

Ek modern, lightweight aur fast **Core PHP & MySQL** application jo QR Code technology ka use karke car parking aur vehicle emergency contact management ko asaan banati hai.

---

## 🌟 Key Features

1. **Unique QR Code for Every Car:**
   - Har register car ke liye cryptographic unique token generate hota hai.
   - High-resolution QR Code sticker generate ho kar server pe save hota hai.

2. **Instant QR Code Scanning (Mobile Camera Compatible):**
   - Kisi bhi smartphone ke regular camera ya Google Lens se QR scan karne par instant vehicle details page khulta hai.

3. **Instant Owner Contact & Emergency Options:**
   - **Direct Call:** Phone dialer direct launch karne ka option (`tel:+91...`).
   - **Direct WhatsApp Alert:** Pre-filled WhatsApp notification message button.
   - **Emergency Contact:** SOS / Emergency contact number call button.

4. **Printable Windshield Sticker Sheet:**
   - Print-ready format (`admin/print_qr.php`) jise direct print karke car windshield / dashboard par chipka sakte hain.

5. **Guard Live Camera QR Scanner (In-Browser):**
   - Security Guard bina kisi heavy hardware scanner ke apne mobile / tablet camera se directly QR scan karke gaadi ko **Check-In** aur **Check-Out** kar sakta hai.

6. **Admin Dashboard & Logs:**
   - Real-time parking occupancy, total vehicles, check-in history aur daily revenue logs.

---

## 📁 File Structure

```text
Car Parking/
│
├── config/
│   └── db.php                     # Database connection & URL helpers
│
├── admin/
│   ├── auth.php                   # Session check
│   ├── login.php                  # Guard / Admin login portal
│   ├── logout.php                 # Logout script
│   ├── layout_header.php          # Admin UI navigation & sidebar
│   ├── layout_footer.php          # Admin footer
│   ├── index.php                  # Main dashboard (metrics & recent activity)
│   ├── register_vehicle.php       # Add new car & auto-generate QR
│   ├── vehicles_list.php          # All cars list with QR preview
│   ├── print_qr.php               # Print-ready sticker template
│   ├── scanner.php                # HTML5 camera-based QR scanner for guards
│   ├── api_check_toggle.php       # AJAX API for fast Check-in/Check-out
│   └── parking_logs.php           # Audit logs & history
│
├── uploads/
│   └── qrcodes/                   # Stored QR code PNG images
│
├── v.php                          # Public landing page after scanning QR
├── index.php                      # Root landing page (redirects to admin)
├── database.sql                   # MySQL Database schema & seed data
└── README.md
```

---

## 🚀 Setup & Installation (XAMPP / WAMP)

### 1. Database Setup:
1. XAMPP / WAMP start karein aur **MySQL** chalu karein.
2. `http://localhost/phpmyadmin` open karein.
3. Ek naya database banayein `car_parking_db` ya direct `database.sql` file ko import kar lein.

### 2. Project Run:
1. Is folder ko apne web directory (jaise `htdocs/Car Parking`) mein rakhein.
2. Browser mein open karein:
   - **Admin Portal:** `http://localhost/Car Parking/admin/login.php`
   - **Default Credentials:**
     - **Username:** `admin`
     - **Password:** `password123`

---

## 📱 How It Works (Workflow):
1. **Register:** Admin `Register Vehicle` mein jakar car number, owner phone aur emergency phone enter karta hai.
2. **Generate & Print:** System automatic QR code generate karta hai. `Print Sticker` dabakar sticker print karke car par laga diya jata hai.
3. **Public / Emergency Scan:** Koi bhi person agar car ke samne scan karega to direct `v.php?token=...` open hoga jahan se wo car owner ko call ya WhatsApp kar sakta hai.
4. **Guard Entry/Exit:** Security guard `Guard Live Scanner` se car ka QR scan karega to car automatic **Check-in** ya **Check-out** mark ho jayegi.
