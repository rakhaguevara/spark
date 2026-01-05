# Geocoding Feature - Visual Flow Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     SPARK PARKING SYSTEM                        │
│                  Geocoding Feature Integration                  │
└─────────────────────────────────────────────────────────────────┘

                           OWNER INTERFACE
                              (Frontend)
┌──────────────────────────────────────────────────────────────────┐
│  "Tambah Lahan Parkir Baru" Modal Form                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Input: Nama Lahan Parkir  ────────→ [text input]               │
│                                                                  │
│  Input: Alamat             ────────→ [text input] ◄──── TRIGGER │
│         (Triggers geocoding on blur)                            │
│                                                                  │
│  Display: Latitude         ◄────────[read-only field]           │
│           (Auto-populated)                                      │
│                                                                  │
│  Display: Longitude        ◄────────[read-only field]           │
│           (Auto-populated)                                      │
│                                                                  │
│  Display: Status Message   ◄────────[Status indicator]          │
│           "⏳ Mencari koordinat..."                              │
│           "✓ Koordinat ditemukan: ..."                          │
│           "✗ Alamat tidak ditemukan..."                         │
│                                                                  │
│  Input: Time, Price, Slots ────────→ [other fields]             │
│                                                                  │
│  Button: [Tambah Lahan] ────────────→ Validate & Submit        │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
          ↓                              ↓
    [JavaScript]                   [Validation]
    Nominatim API Call            Form Submit Check
          ↓
┌──────────────────────────────────────────────────────────────────┐
│                      EXTERNAL API                               │
├──────────────────────────────────────────────────────────────────┤
│  Nominatim (OpenStreetMap)                                      │
│  URL: nominatim.openstreetmap.org/search                        │
│                                                                  │
│  Request:                                                       │
│  GET /search?format=json&q=[address]&limit=1                   │
│                                                                  │
│  Response:                                                      │
│  {                                                              │
│    "lat": "-7.7926",                                            │
│    "lon": "110.3652",                                           │
│    "display_name": "..."                                        │
│  }                                                              │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
          ↑
          │ Return Coordinates
          │
    [JavaScript Parser]
    Extract lat/lon
    Format to 7 decimals
          │
          ↓
  [Populate Form Fields]
  Show Status: ✓ Success
          │
          ↓
┌──────────────────────────────────────────────────────────────────┐
│                    SERVER-SIDE PROCESSING                       │
│                   (/owner/manage-parking.php)                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Receive POST Data:                                             │
│  - nama_tempat (string)                                         │
│  - alamat (string)                                              │
│  - latitude (float)         ◄────── From geocoding              │
│  - longitude (float)        ◄────── From geocoding              │
│  - jam_buka (time)                                              │
│  - jam_tutup (time)                                             │
│  - harga_jam (decimal)                                          │
│  - total_slot (integer)                                         │
│                                                                  │
│  Validate All Fields                                            │
│  ✓ All required fields present                                  │
│  ✓ Coordinates not empty                                        │
│                                                                  │
│  If Valid:                                                      │
│  ├─ INSERT INTO tempat_parkir (                                │
│  │   id_pengguna,                                              │
│  │   nama_tempat,                                              │
│  │   alamat_tempat,                                            │
│  │   latitude,          ◄────── From API                       │
│  │   longitude,         ◄────── From API                       │
│  │   jam_buka,                                                 │
│  │   jam_tutup,                                                │
│  │   harga_jam,                                                │
│  │   total_slot,                                               │
│  │   status_tempat = 'aktif'                                   │
│  │ )                                                           │
│  │                                                             │
│  └─ Show Success Message: "Lahan parkir berhasil ditambahkan"   │
│                                                                  │
│  If Invalid:                                                    │
│  └─ Show Error Message: "Semua field harus diisi dengan benar"  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────────────────┐
│                          DATABASE                               │
│                    (MySQL / MariaDB)                            │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Table: tempat_parkir                                           │
│  ┌────────────────┬──────────────┐                              │
│  │ id_tempat      │ INT          │                              │
│  │ id_pengguna    │ INT          │                              │
│  │ nama_tempat    │ VARCHAR(255) │                              │
│  │ alamat_tempat  │ TEXT         │                              │
│  │ latitude       │ DECIMAL(10,7)│ ◄──── STORES COORDINATES    │
│  │ longitude      │ DECIMAL(10,7)│ ◄──── STORES COORDINATES    │
│  │ jam_buka       │ TIME         │                              │
│  │ jam_tutup      │ TIME         │                              │
│  │ harga_jam      │ DECIMAL(10,2)│                              │
│  │ total_slot     │ INT          │                              │
│  │ status_tempat  │ VARCHAR(20)  │                              │
│  └────────────────┴──────────────┘                              │
│                                                                  │
│  Sample Data:                                                   │
│  ┌────────────────┬──────────────┬────────────┬────────────┐    │
│  │ nama_tempat    │ alamat       │ latitude   │ longitude  │    │
│  ├────────────────┼──────────────┼────────────┼────────────┤    │
│  │ Parkir Mall    │ Jl. Maliob...│ -7.7926000 │ 110.365200 │    │
│  │ Central        │              │            │            │    │
│  ├────────────────┼──────────────┼────────────┼────────────┤    │
│  │ Jakarta Park   │ Jl. Raya...  │ -6.2075000 │ 106.849200 │    │
│  │ Center #1      │              │            │            │    │
│  └────────────────┴──────────────┴────────────┴────────────┘    │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────────────────┐
│                    USER MOBILE/WEB APP                          │
│                                                                  │
│  Booking Page:                                                  │
│  ├─ List all parking locations                                  │
│  ├─ Display on interactive map                                  │
│  │  └─ Use stored coordinates (latitude, longitude)             │
│  ├─ Show distance from user location                            │
│  └─ Enable navigation/directions                                │
│                                                                  │
│  User sees parking at: -7.7926, 110.3652                        │
│  (Coordinates from geocoding feature)                           │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

## User Interaction Flow

```
START
  │
  ├─→ Owner logs in to dashboard
  │    │
  │    └─→ Navigate to "Kelola Lahan Parkir"
  │         │
  │         └─→ Click "Tambah Lahan" button
  │              │
  │              └─→ Modal opens: "Tambah Lahan Parkir Baru"
  │                   │
  │                   ├─ Enter: Parking name
  │                   │    │
  │                   │    └─→ Continue filling form
  │                   │
  │                   ├─ Enter: Address
  │                   │    │
  │                   │    └─→ "Jl. Malioboro No. 123, Yogyakarta"
  │                   │         │
  │                   │         └─→ [User moves to next field]
  │                   │              │
  │                   │              └─→ TRIGGER: blur event
  │                   │                   │
  │                   │                   └─→ JavaScript executes
  │                   │                        │
  │                   │                        ├─ Get address value
  │                   │                        │
  │                   │                        ├─ Show: "⏳ Mencari koordinat..."
  │                   │                        │
  │                   │                        ├─ Call Nominatim API
  │                   │                        │   URL: https://nominatim.openstreetmap.org/search?...
  │                   │                        │
  │                   │                        └─ Wait for response
  │                   │                             │
  │                   │                             ├─ Success:
  │                   │                             │  ├─ Latitude field = -7.7926000
  │                   │                             │  ├─ Longitude field = 110.3652000
  │                   │                             │  └─ Status = "✓ Koordinat ditemukan: ..."
  │                   │                             │
  │                   │                             └─ Error:
  │                   │                                ├─ Latitude field = (empty)
  │                   │                                ├─ Longitude field = (empty)
  │                   │                                └─ Status = "✗ Alamat tidak ditemukan..."
  │                   │
  │                   ├─ Review coordinates (read-only display)
  │                   │
  │                   ├─ Enter remaining fields:
  │                   │    ├─ Jam Buka
  │                   │    ├─ Jam Tutup
  │                   │    ├─ Harga per Jam
  │                   │    └─ Total Slot
  │                   │
  │                   └─→ Click "Tambah Lahan" button
  │                        │
  │                        └─→ Form submission
  │                             │
  │                             ├─ Client-side validation
  │                             │  ├─ Check coordinates populated
  │                             │  └─ Check all fields filled
  │                             │      │
  │                             │      ├─ Valid → Send POST
  │                             │      └─ Invalid → Show error
  │                             │
  │                             └─→ Server-side processing
  │                                  │
  │                                  ├─ Receive all form data
  │                                  │  └─ Include: latitude, longitude
  │                                  │
  │                                  ├─ Validate all fields
  │                                  │
  │                                  ├─ INSERT INTO database
  │                                  │  ├─ Column: latitude
  │                                  │  └─ Column: longitude
  │                                  │
  │                                  ├─ Success: "Lahan parkir berhasil ditambahkan"
  │                                  │
  │                                  └─→ Refresh parking list
  │
  └─→ New parking visible on map with coordinates
       (Available for users to book)

END
```

## Data Flow with Coordinates

```
┌─────────────────────────────────────────────────────────────────┐
│                     DATA JOURNEY                                │
└─────────────────────────────────────────────────────────────────┘

STEP 1: Owner Input
━━━━━━━━━━━━━━━━━━
Owner types: "Jl. Malioboro No. 123, Yogyakarta"
             │
             └─→ Stored in browser memory
                 (JavaScript variable)

STEP 2: API Request
━━━━━━━━━━━━━━━━━━
JavaScript sends:
GET https://nominatim.openstreetmap.org/search?
    format=json&
    q=Jl.%20Malioboro%20No.%20123,%20Yogyakarta,%20Indonesia&
    limit=1

STEP 3: API Response
━━━━━━━━━━━━━━━━━━
Nominatim returns:
{
    "lat": "-7.79260",
    "lon": "110.36520",
    "display_name": "Jl. Malioboro, Yogyakarta, Indonesia"
}

STEP 4: Form Population
━━━━━━━━━━━━━━━━━━━
JavaScript parses and fills:
│
├─ Latitude field  = -7.7926000
├─ Longitude field = 110.3652000
└─ Status message  = "✓ Koordinat ditemukan: -7.7926, 110.3652"

STEP 5: Form Submission
━━━━━━━━━━━━━━━━━━━
POST /owner/manage-parking.php
{
    action: "add",
    nama_tempat: "Parkir Malioboro",
    alamat: "Jl. Malioboro No. 123, Yogyakarta",
    latitude: "-7.7926000",        ◄───── From API
    longitude: "110.3652000",      ◄───── From API
    jam_buka: "08:00",
    jam_tutup: "22:00",
    harga_jam: "5000",
    total_slot: "100"
}

STEP 6: Server Processing
━━━━━━━━━━━━━━━━━━━
PHP extracts:
$latitude = floatval($_POST['latitude']);  // -7.7926
$longitude = floatval($_POST['longitude']); // 110.3652

STEP 7: Database Insert
━━━━━━━━━━━━━━━━━━━
INSERT INTO tempat_parkir (
    id_pengguna,     1
    nama_tempat,     "Parkir Malioboro"
    alamat_tempat,   "Jl. Malioboro No. 123, Yogyakarta"
    latitude,        -7.7926000       ◄──────┐
    longitude,       110.3652000      ◄──────┤ Stored!
    jam_buka,        "08:00:00"               │
    jam_tutup,       "22:00:00"               │
    harga_jam,       5000.00                  │
    total_slot,      100                      │
    status_tempat    "aktif"                  │
)                                            │
                                             │
STEP 8: User Access (For User App)          │
━━━━━━━━━━━━━━━━━━━                         │
SELECT * FROM tempat_parkir WHERE id = 1    │
Result includes:                            │
├─ nama_tempat: "Parkir Malioboro"          │
├─ alamat_tempat: "Jl. Malioboro..."        │
├─ latitude: -7.7926000    ◄────────────────┘
├─ longitude: 110.3652000  ◄────────────────┘
└─ Other fields...

STEP 9: Display on Map
━━━━━━━━━━━━━━━━━━━
User booking page receives coordinates:
const parking = {
    name: "Parkir Malioboro",
    latitude: -7.7926000,
    longitude: 110.3652000
};

Google Maps / Leaflet displays marker at:
(-7.7926, 110.3652)
```

## Status Message States

```
┌──────────────────────────────────────────────────┐
│         GEOCODING STATUS INDICATORS              │
└──────────────────────────────────────────────────┘

STATE 1: WAITING FOR INPUT
━━━━━━━━━━━━━━━━━━━━━━━━━
Status: (empty)
Color: none
User Action: Type address

STATE 2: SEARCHING
━━━━━━━━━━━━━━━━━━━━━━━━━
Status: ⏳ Mencari koordinat...
Color: #FFE100 (yellow)
User Action: Wait...

STATE 3: SUCCESS ✓
━━━━━━━━━━━━━━━━━━━━━━━━━
Status: ✓ Koordinat ditemukan: -7.7926, 110.3652
Color: #2ecc71 (green)
Latitude: -7.7926000
Longitude: 110.3652000
User Action: Continue form

STATE 4: ERROR ✗
━━━━━━━━━━━━━━━━━━━━━━━━━
Status: ✗ Alamat tidak ditemukan. Coba masukkan alamat yang lebih spesifik.
Color: #e74c3c (red)
Latitude: (empty)
Longitude: (empty)
User Action: Edit address and retry

STATE 5: NETWORK ERROR ✗
━━━━━━━━━━━━━━━━━━━━━━━━
Status: ✗ Error: Gagal menghubungi server geocoding
Color: #e74c3c (red)
Latitude: (empty)
Longitude: (empty)
User Action: Check internet and retry
```

## Database View

```
┌─────────────────────────────────────────────────────────────────────────┐
│ tempat_parkir TABLE - With Geocoded Coordinates                        │
├──────┬──────────┬──────────────┬───────────────┬──────────┬───────────┤
│ ID   │ Owner    │ Name         │ Address       │ Latitude │ Longitude │
├──────┼──────────┼──────────────┼───────────────┼──────────┼───────────┤
│  1   │ Owner-1  │ Parkir Mall  │ Jl. Maliob... │ -7.7926  │ 110.3652  │
├──────┼──────────┼──────────────┼───────────────┼──────────┼───────────┤
│  2   │ Owner-1  │ Jakarta Park │ Jl. Raya...   │ -6.2075  │ 106.8492  │
├──────┼──────────┼──────────────┼───────────────┼──────────┼───────────┤
│  3   │ Owner-2  │ Bandung Cent │ Jl. Raya...   │ -6.9216  │ 107.6241  │
├──────┼──────────┼──────────────┼───────────────┼──────────┼───────────┤
│  4   │ Owner-2  │ Surabaya Pk  │ Jl. Raya...   │ -7.2557  │ 112.7547  │
└──────┴──────────┴──────────────┴───────────────┴──────────┴───────────┘

All coordinates auto-populated via Nominatim Geocoding API
```

---

**Visual Documentation**: ✅ Complete
**Last Updated**: January 2025
**Format**: ASCII Diagrams & Flow Charts
