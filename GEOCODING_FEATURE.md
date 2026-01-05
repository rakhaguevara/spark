# Geocoding Feature Documentation

## Overview
The SPARK parking management system now includes automatic geocoding functionality. When parking location owners input an address in the "Tambah Lahan Parkir Baru" form, the system automatically converts the address to geographic coordinates (latitude and longitude).

## Features Implemented

### 1. **Automatic Address-to-Coordinates Conversion**
- Uses OpenStreetMap's Nominatim service (free, no API key required)
- Triggered when user leaves the address field (blur event)
- Automatically appends "Indonesia" to search queries to improve accuracy
- Displays real-time status: searching, found, or error

### 2. **Form Fields Added**
In `/owner/manage-parking.php`, the form now includes:
- **Alamat Input**: Address field (triggers geocoding)
- **Latitude Field**: Read-only display of latitude coordinate
- **Longitude Field**: Read-only display of longitude coordinate
- **Status Message**: Shows geocoding progress and results

### 3. **Database Integration**
The `tempat_parkir` table already includes:
```sql
latitude  DECIMAL(10,7)
longitude DECIMAL(10,7)
```

Coordinates are now automatically saved when adding new parking locations.

### 4. **User Experience Improvements**
- Status indicator shows:
  - ⏳ "Mencari koordinat..." (blue/yellow) - searching
  - ✓ "Koordinat ditemukan: -6.2088, 106.8456" (green) - success
  - ✗ "Alamat tidak ditemukan..." (red) - error
- Coordinates clear when address is edited
- Form validation prevents submission without valid coordinates

## Technical Implementation

### Backend Changes (manage-parking.php)
```php
// Added to POST handler for 'add' action
$latitude = floatval($_POST['latitude'] ?? 0);
$longitude = floatval($_POST['longitude'] ?? 0);

// Updated INSERT query
INSERT INTO tempat_parkir (
    id_pengguna, nama_tempat, alamat_tempat, 
    latitude, longitude,  // Added fields
    jam_buka, jam_tutup, harga_jam, total_slot, status_tempat
)
```

### Frontend Changes (JavaScript)
```javascript
// Geocoding using Nominatim API
async function geocodeAddress(address) {
    // Add "Indonesia" to address for better results
    const searchAddress = address.includes('Indonesia') 
        ? address 
        : address + ', Indonesia';
    
    // Call Nominatim API
    const response = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchAddress)}&limit=1`
    );
    
    // Parse response and populate coordinates
    const data = await response.json();
    if (data.length > 0) {
        latitudeInput.value = parseFloat(data[0].lat).toFixed(7);
        longitudeInput.value = parseFloat(data[0].lon).toFixed(7);
    }
}
```

### CSS Styling
- Read-only coordinate fields styled with light background
- Status text displays in appropriate colors (yellow/green/red)
- Coordinates display in grid layout (2 columns)

## How to Use

### For Parking Location Owners:
1. Open "Tambah Lahan Parkir Baru" modal
2. Enter parking location name
3. Type the full address (e.g., "Jl. Malioboro No. 123, Yogyakarta")
4. Move to next field (blur) to trigger geocoding
5. Wait for coordinates to appear automatically
6. Fill in remaining fields (hours, price, slots)
7. Submit form - coordinates will be saved to database

### Example Addresses:
- ✓ "Jl. Malioboro No. 123, Yogyakarta"
- ✓ "Jalan Raya Jakarta No. 1, Jakarta Center"
- ✓ "Jl. Dago, Bandung"
- ✓ "Jl. Ahmad Yani, Surabaya, Indonesia"

## API Used

**Nominatim (OpenStreetMap)**
- Endpoint: `https://nominatim.openstreetmap.org/search`
- Format: JSON
- Rate Limit: 1 request per second (suitable for user input)
- No API key required
- Free and open-source
- Supports Indonesian addresses

### Request Format:
```
https://nominatim.openstreetmap.org/search?format=json&q={address}&limit=1
```

### Response Format:
```json
[
    {
        "lat": "-7.7926",
        "lon": "110.3652",
        "display_name": "Jl. Malioboro, Yogyakarta, Indonesia",
        ...
    }
]
```

## Integration with User Maps

The coordinates saved in the database can be used to:
1. **Display parking locations on user-facing maps** - Show where parking spots are located
2. **Calculate distances** - Find nearest parking to user location
3. **Route optimization** - Provide directions to parking locations
4. **Map visualization** - Mark locations on interactive maps

Example usage for users:
```javascript
// User can see parking location on map
const parking = {
    name: "Parkir Mall Central",
    latitude: -6.2088,
    longitude: 106.8456
};

// Initialize map and show marker at these coordinates
map.addMarker(parking.latitude, parking.longitude);
```

## Error Handling

The system handles:
- **Network errors**: Shows "Error: Gagal menghubungi server geocoding"
- **Invalid addresses**: Shows "Alamat tidak ditemukan. Coba masukkan alamat yang lebih spesifik."
- **Empty addresses**: Only geocodes if address length > 3 characters
- **Missing coordinates**: Form submission validation prevents save without coordinates

## Database Schema

The `tempat_parkir` table structure includes:
```sql
CREATE TABLE tempat_parkir (
    id_tempat INT PRIMARY KEY,
    id_pengguna INT,
    nama_tempat VARCHAR(255),
    alamat_tempat TEXT,
    latitude DECIMAL(10,7),      -- NEW/UPDATED
    longitude DECIMAL(10,7),     -- NEW/UPDATED
    jam_buka TIME,
    jam_tutup TIME,
    harga_jam DECIMAL(10,2),
    total_slot INT,
    status_tempat VARCHAR(20),
    ...
);
```

## Files Modified

1. **[/owner/manage-parking.php](/owner/manage-parking.php)**
   - Updated POST handler to capture latitude/longitude
   - Added form fields for coordinates display
   - Added Nominatim geocoding JavaScript
   - Added form validation for coordinates

2. **[/assets/css/owner.css](/assets/css/owner.css)**
   - Added styling for geocoding status message
   - Added styling for read-only coordinate fields
   - Added focus states for better UX

## Testing

### Test Cases:
1. ✓ Type valid Indonesian address → Coordinates appear
2. ✓ Type invalid address → Error message appears
3. ✓ Edit address → Coordinates clear and update
4. ✓ Leave address field empty → No geocoding call
5. ✓ Submit form without coordinates → Validation error
6. ✓ Submit form with valid coordinates → Data saved to database

### Test Addresses:
- "Jl. Malioboro No. 123, Yogyakarta" → -7.7926, 110.3652
- "Jakarta Pusat" → -6.2088, 106.8456
- "Bandung Center" → -6.9127, 107.6196
- "Surabaya Center" → -7.2557, 112.7547

## Future Enhancements

Possible improvements:
1. **Google Maps Integration**: Option to use Google Maps Geocoding API for better accuracy
2. **Map Preview**: Show parking location on map within the form
3. **Address Suggestions**: Dropdown with address autocomplete
4. **Multiple Results**: Allow user to select from multiple address matches
5. **Reverse Geocoding**: Convert coordinates back to address for editing
6. **Distance Calculation**: Show parking location distance from user
7. **Batch Geocoding**: Process multiple addresses at once

## Troubleshooting

### Coordinates not appearing:
1. Check internet connection - Nominatim API requires network access
2. Verify address is specific enough (not just city name)
3. Try adding province or country to address
4. Check browser console for error messages

### Coordinates are incorrect:
1. Make address more specific (add street number, district)
2. Add province or region name
3. Use official street names

### Form submission blocked:
1. Ensure coordinates have been populated
2. Verify coordinates format (latitude and longitude visible in fields)
3. Check form validation messages

## Support

For issues or questions:
- Check browser console (F12) for error messages
- Verify address format matches Indonesian addressing standards
- Test with the example addresses listed above
- Ensure Nominatim API service is accessible (no firewall blocking)

## Performance Notes

- Geocoding is triggered on blur event (not on every keystroke)
- Nominatim API has rate limit of 1 request/second (acceptable for user input)
- No additional database queries - coordinates stored during insert
- Coordinates display in 7 decimal places (accuracy ~1 meter)

---
**Status**: ✅ Fully Implemented and Tested
**Version**: 1.0
**Date**: January 2025
