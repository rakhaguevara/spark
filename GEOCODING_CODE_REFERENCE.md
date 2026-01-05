# Geocoding Implementation - Code Reference

## 1. Form HTML Structure
### File: `/owner/manage-parking.php` (Lines 216-250)

```html
<!-- ADD/EDIT MODAL -->
<div class="modal" id="addModal">
    <div class="modal-content" style="position: relative;">
        <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        <div class="modal-header">Tambah Lahan Parkir Baru</div>
        
        <form method="POST" id="addParkingForm">
            <input type="hidden" name="action" value="add">
            
            <!-- Parking Name -->
            <div class="form-group">
                <label>Nama Lahan Parkir</label>
                <input type="text" name="nama_tempat" required 
                       placeholder="Contoh: Parkir Mall Central">
            </div>
            
            <!-- Address Input (TRIGGERS GEOCODING) -->
            <div class="form-group">
                <label>Alamat 
                    <span style="color: var(--warning); font-size: 12px;">
                        (Koordinat akan otomatis terisi)
                    </span>
                </label>
                <input type="text" id="alamatInput" name="alamat" required 
                       placeholder="Jalan, No, Kota" autocomplete="off">
                <small style="color: var(--text-light); margin-top: 5px; display: block;" 
                       id="geocodeStatus"></small>
            </div>
            
            <!-- Latitude & Longitude (READ-ONLY) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" id="latitudeInput" name="latitude" readonly 
                           placeholder="-6.2088" 
                           style="background: var(--light); cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" id="longitudeInput" name="longitude" readonly 
                           placeholder="106.8456" 
                           style="background: var(--light); cursor: not-allowed;">
                </div>
            </div>
            
            <!-- Other Fields -->
            <div class="form-group">
                <label>Jam Buka</label>
                <input type="time" name="jam_buka" required>
            </div>
            <div class="form-group">
                <label>Jam Tutup</label>
                <input type="time" name="jam_tutup" required>
            </div>
            <div class="form-group">
                <label>Harga per Jam (Rp)</label>
                <input type="number" name="harga_jam" step="100" required placeholder="5000">
            </div>
            <div class="form-group">
                <label>Total Slot</label>
                <input type="number" name="total_slot" min="1" required placeholder="50">
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                <i class="fas fa-plus"></i> Tambah Lahan
            </button>
        </form>
    </div>
</div>
```

## 2. Backend PHP Processing
### File: `/owner/manage-parking.php` (Lines 15-40)

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Get form data
        $nama = trim($_POST['nama_tempat'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $jam_buka = trim($_POST['jam_buka'] ?? '');
        $jam_tutup = trim($_POST['jam_tutup'] ?? '');
        $harga_jam = floatval($_POST['harga_jam'] ?? 0);
        $total_slot = intval($_POST['total_slot'] ?? 0);
        
        // GET COORDINATES FROM GEOCODING API
        $latitude = floatval($_POST['latitude'] ?? 0);
        $longitude = floatval($_POST['longitude'] ?? 0);

        // Validation
        if (!$nama || !$alamat || !$jam_buka || !$jam_tutup || 
            $harga_jam <= 0 || $total_slot <= 0) {
            $message = 'Semua field harus diisi dengan benar';
            $message_type = 'error';
        } else {
            try {
                // INSERT WITH COORDINATES
                $stmt = $pdo->prepare("
                    INSERT INTO tempat_parkir 
                    (id_pengguna, nama_tempat, alamat_tempat, 
                     latitude, longitude, jam_buka, jam_tutup, 
                     harga_jam, total_slot, status_tempat)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
                ");
                $stmt->execute([
                    $owner['id_pengguna'],  // User ID
                    $nama,                  // Parking name
                    $alamat,                // Address
                    $latitude ?: null,      // Latitude from geocoding
                    $longitude ?: null,     // Longitude from geocoding
                    $jam_buka,              // Opening time
                    $jam_tutup,             // Closing time
                    $harga_jam,             // Price per hour
                    $total_slot             // Total parking slots
                ]);
                
                $message = 'Lahan parkir berhasil ditambahkan';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
    // ... other actions (delete, etc.)
}
```

## 3. JavaScript Geocoding Function
### File: `/owner/manage-parking.php` (Lines 304-391)

```javascript
<script>
// ============ MODAL FUNCTIONS ============
function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    openModal('deleteModal');
}

function editParking(id) {
    alert('Fitur edit akan segera tersedia');
}

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal(modal.id);
        }
    });
});

// ============ GEOCODING FUNCTIONALITY ============
// Get form element references
const alamatInput = document.getElementById('alamatInput');
const latitudeInput = document.getElementById('latitudeInput');
const longitudeInput = document.getElementById('longitudeInput');
const geocodeStatus = document.getElementById('geocodeStatus');

let geocodeTimeout;

// TRIGGER: When user leaves address field
alamatInput.addEventListener('blur', function() {
    const address = this.value.trim();
    if (address.length > 3) {  // Only geocode if address has meaningful length
        geocodeAddress(address);
    }
});

// GEOCODING FUNCTION: Call Nominatim API
async function geocodeAddress(address) {
    // Show searching status
    geocodeStatus.textContent = '⏳ Mencari koordinat...';
    geocodeStatus.style.color = '#FFE100';
    
    try {
        // Add "Indonesia" to improve search accuracy
        const searchAddress = address.includes('Indonesia') 
            ? address 
            : address + ', Indonesia';
        
        // Call Nominatim API
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchAddress)}&limit=1`,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        // Check response status
        if (!response.ok) {
            throw new Error('Gagal menghubungi server geocoding');
        }

        const data = await response.json();
        
        // Process API response
        if (data && data.length > 0) {
            // Success: Parse coordinates
            const result = data[0];
            const lat = parseFloat(result.lat).toFixed(7);
            const lon = parseFloat(result.lon).toFixed(7);
            
            // Populate form fields with coordinates
            latitudeInput.value = lat;
            longitudeInput.value = lon;
            
            // Show success status
            geocodeStatus.textContent = `✓ Koordinat ditemukan: ${lat}, ${lon}`;
            geocodeStatus.style.color = 'var(--success)';
        } else {
            // Address not found
            geocodeStatus.textContent = '✗ Alamat tidak ditemukan. Coba masukkan alamat yang lebih spesifik.';
            geocodeStatus.style.color = 'var(--danger)';
            
            // Clear coordinate fields
            latitudeInput.value = '';
            longitudeInput.value = '';
        }
    } catch (error) {
        // Network or other errors
        console.error('Geocoding error:', error);
        geocodeStatus.textContent = '✗ Error: ' + error.message;
        geocodeStatus.style.color = 'var(--danger)';
    }
}

// FORM VALIDATION: Check coordinates before submission
document.getElementById('addParkingForm').addEventListener('submit', function(e) {
    const latitude = latitudeInput.value.trim();
    const longitude = longitudeInput.value.trim();
    
    if (!latitude || !longitude) {
        e.preventDefault();
        alert('Koordinat belum terisi. Harap masukkan alamat yang valid.');
    }
});

// CLEAR COORDINATES: When address is modified
alamatInput.addEventListener('input', function() {
    latitudeInput.value = '';
    longitudeInput.value = '';
    geocodeStatus.textContent = '';
});
</script>
```

## 4. CSS Styling
### File: `/assets/css/owner.css` (Added at end)

```css
/* ========== GEOCODING STYLES ========== */

/* Status message styling */
#geocodeStatus {
  font-size: 12px;
  margin-top: 6px;
  display: block;
  padding: 6px 0;
  font-weight: 500;
  min-height: 18px;
  /* Changes color dynamically via JavaScript */
}

/* Read-only coordinate input fields */
#latitudeInput,
#longitudeInput {
  color: var(--spark-text);
  border: 1px solid rgba(255, 225, 0, 0.2);
  background: var(--light);  /* Light gray background */
  cursor: not-allowed;        /* Indicate read-only */
}

/* Focus state for coordinate fields */
#latitudeInput:focus,
#longitudeInput:focus {
  outline: none;
  border-color: rgba(255, 225, 0, 0.3);
  box-shadow: inset 0 0 0 2px rgba(255, 225, 0, 0.05);
}

/* Grid layout for coordinate fields */
/* (Applied inline in HTML: grid-template-columns: 1fr 1fr) */
```

## 5. API Request/Response Example

### Request to Nominatim API:
```
GET https://nominatim.openstreetmap.org/search?format=json&q=Jl.%20Malioboro%20No.%20123,%20Yogyakarta,%20Indonesia&limit=1

Headers:
  Accept: application/json
```

### Response from Nominatim API:
```json
[
    {
        "place_id": 275698642,
        "licence": "Data © OpenStreetMap contributors",
        "osm_type": "way",
        "osm_id": 123456,
        "lat": "-7.79260",
        "lon": "110.36520",
        "class": "highway",
        "type": "residential",
        "place_name": "Jl. Malioboro",
        "display_name": "Jl. Malioboro, Yogyakarta, Indonesia",
        "boundingbox": ["-7.79300", "-7.79200", "110.36500", "110.36600"]
    }
]
```

### Parsed by JavaScript:
```javascript
const result = data[0];
const lat = parseFloat(result.lat).toFixed(7);  // "-7.7926000"
const lon = parseFloat(result.lon).toFixed(7);  // "110.3652000"

// Set form fields
document.getElementById('latitudeInput').value = lat;
document.getElementById('longitudeInput').value = lon;
```

## 6. Form Submission Data Flow

### Client sends POST to `/owner/manage-parking.php`:
```
POST Data:
{
    "action": "add",
    "nama_tempat": "Parkir Malioboro",
    "alamat": "Jl. Malioboro No. 123, Yogyakarta",
    "latitude": "-7.7926000",        ← From Nominatim API
    "longitude": "110.3652000",      ← From Nominatim API
    "jam_buka": "08:00",
    "jam_tutup": "22:00",
    "harga_jam": "5000",
    "total_slot": "100"
}
```

### PHP Processes:
```php
$latitude = floatval($_POST['latitude']);   // -7.7926
$longitude = floatval($_POST['longitude']); // 110.3652

// INSERT statement includes coordinates
INSERT INTO tempat_parkir (..., latitude, longitude, ...)
VALUES (..., -7.7926, 110.3652, ...)
```

### Database stores:
```sql
id_tempat: 1
nama_tempat: "Parkir Malioboro"
alamat_tempat: "Jl. Malioboro No. 123, Yogyakarta"
latitude: -7.7926000
longitude: 110.3652000
jam_buka: "08:00:00"
jam_tutup: "22:00:00"
harga_jam: 5000.00
total_slot: 100
status_tempat: "aktif"
```

## 7. Event Flow Sequence

```
1. User clicks "Tambah Lahan" button
   ↓
2. openModal('addModal') executed
   ↓
3. Modal appears on screen
   ↓
4. User fills form (Nama, Alamat, etc.)
   ↓
5. User enters address: "Jl. Malioboro No. 123, Yogyakarta"
   ↓
6. User moves to next field (blur event)
   ↓
7. JavaScript blur event listener triggered
   ↓
8. geocodeAddress(address) function called
   ↓
9. Show status: "⏳ Mencari koordinat..."
   ↓
10. Fetch request sent to Nominatim API
    ↓
11. API searches address database
    ↓
12. API returns: {"lat": "-7.7926", "lon": "110.3652"}
    ↓
13. Parse response JSON
    ↓
14. Extract lat and lon values
    ↓
15. Set latitudeInput.value = "-7.7926000"
    ↓
16. Set longitudeInput.value = "110.3652000"
    ↓
17. Show status: "✓ Koordinat ditemukan: -7.7926, 110.3652"
    ↓
18. User continues filling form (Time, Price, Slots)
    ↓
19. User clicks "Tambah Lahan" button
    ↓
20. Form submit event triggered
    ↓
21. Client-side validation checks coordinates
    ↓
22. POST request sent to server with all data
    ↓
23. PHP receives $_POST data
    ↓
24. Extract latitude and longitude from $_POST
    ↓
25. Validate all required fields
    ↓
26. INSERT INTO tempat_parkir with coordinates
    ↓
27. Database stores record with latitude/longitude
    ↓
28. Show success message
    ↓
29. Refresh parking list display
    ↓
30. User sees parking location with coordinates in database
```

## 8. Error Handling Logic

```javascript
// Three types of errors handled:

1. NETWORK ERROR:
   Status: "✗ Error: Gagal menghubungi server geocoding"
   Cause: No internet, API down, CORS issue
   Solution: User checks connection and retries

2. ADDRESS NOT FOUND ERROR:
   Status: "✗ Alamat tidak ditemukan. Coba masukkan alamat yang lebih spesifik."
   Cause: Address too vague or doesn't exist in Nominatim database
   Solution: User makes address more specific (add district, province)

3. EMPTY ADDRESS ERROR:
   Trigger: Address length < 3 characters
   Action: No geocoding request made
   Solution: User enters longer address

4. FORM SUBMISSION ERROR:
   Status: "Koordinat belum terisi. Harap masukkan alamat yang valid."
   Cause: User submitted form without valid coordinates
   Solution: User enters valid address and waits for geocoding
```

## 9. Database Insert Statement Details

```php
$stmt = $pdo->prepare("
    INSERT INTO tempat_parkir 
    (
        id_pengguna,        /* From session/auth */
        nama_tempat,        /* From form input */
        alamat_tempat,      /* From form input */
        latitude,           /* From Nominatim API */
        longitude,          /* From Nominatim API */
        jam_buka,           /* From form input */
        jam_tutup,          /* From form input */
        harga_jam,          /* From form input */
        total_slot,         /* From form input */
        status_tempat       /* Hardcoded 'aktif' */
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
");

$stmt->execute([
    $owner['id_pengguna'],  // Logged-in owner's ID
    $nama,                  // "Parkir Malioboro"
    $alamat,                // "Jl. Malioboro No. 123, Yogyakarta"
    $latitude ?: null,      // -7.7926 (or NULL if empty)
    $longitude ?: null,     // 110.3652 (or NULL if empty)
    $jam_buka,              // "08:00:00"
    $jam_tutup,             // "22:00:00"
    $harga_jam,             // 5000.00
    $total_slot             // 100
]);
```

---

**Documentation Type**: Code Implementation Reference
**Status**: ✅ Complete and Documented
**Version**: 1.0
**Date**: January 2025
