# SPARK Photo Upload Feature - Implementation Guide

## Overview
The photo upload feature allows parking lot owners to upload up to 5 photos per parking location. Photos are displayed in a carousel slider on the parking card.

## What Was Implemented

### 1. Database Schema
**New Table: `parking_photos`**
- `id_foto` - Primary key, auto-increment
- `id_tempat` - Foreign key to `tempat_parkir.id_tempat`
- `foto_path` - File path to the image
- `urutan` - Display order (1-5)
- `created_at` - Timestamp

**Location:** `/database/add_parking_photos.sql`

### 2. Backend Functions
**File:** `/owner/manage-parking.php`

#### `handlePhotoUpload($id_tempat, $id_pemilik)`
- Validates uploaded files (type, size, count)
- Creates upload directory if needed
- Saves files with unique names: `parking_{id}_{random}.jpg`
- Inserts file paths into database with order
- Limits to 5 photos per parking location

#### `getParkingPhotos($id_tempat, $pdo)`
- Fetches all photos for a parking location
- Returns ordered by display sequence
- Returns empty array if no photos

### 3. Frontend
**Photo Upload Form** (Add/Edit Modal)
- Drag-and-drop area for photos
- File input with multiple file selection
- Real-time preview grid
- Max 5 files validation (client-side)
- File type validation (image only)

**Photo Carousel** (Parking Card)
- Displays up to 5 photos
- Navigation buttons (previous/next)
- Indicator dots for navigation
- Automatic initialization on page load

### 4. File Upload Location
- **Directory:** `/uploads/parking_photos/`
- **Auto-created** on first upload
- **Permissions:** 0755 (readable by web server)

## Setup Instructions

### Step 1: Execute Migration
Visit the migration page in your browser:
```
http://localhost/spark/database/execute_migration.php
```

This will:
- ✓ Create `parking_photos` table
- ✓ Create upload directory
- ✓ Set proper permissions

### Step 2: Verify Installation
Check that:
1. Table created: `mysql> SHOW TABLES LIKE 'parking_photos';`
2. Directory exists: Check `/uploads/parking_photos/` folder
3. No errors in migration output

### Step 3: Test Upload
1. Go to Owner Dashboard
2. Click "Tambah Lahan Pertama" or "Tambah Lahan Parkir Baru"
3. Fill in parking details
4. Drag photos into the upload area or click to select
5. See preview grid with up to 5 photos
6. Submit form
7. Photos should appear in carousel on the card

## Usage

### For Owners
1. **Adding Photos to New Parking Lot:**
   - Fill parking details in the form
   - Drag 1-5 images into the upload area
   - Preview appears below the upload area
   - Submit the form to save

2. **Editing Parking Lot Photos:**
   - Click "Edit" button on parking card
   - Current photos NOT shown (can be improved)
   - Add new photos in the form
   - New photos are added to existing ones (up to 5 total)

3. **Viewing Photos:**
   - Click carousel arrows to navigate
   - Click dots to jump to specific photo
   - Hover shows navigation controls

### For Developers

**Add new photo to parking:**
```php
// Triggered automatically by handlePhotoUpload()
INSERT INTO parking_photos (id_tempat, foto_path, urutan)
VALUES (1, 'uploads/parking_photos/parking_1_abc123.jpg', 1);
```

**Fetch photos for display:**
```php
$photos = getParkingPhotos($id_tempat, $pdo);
// Returns: [
//   ['id_foto' => 1, 'foto_path' => 'uploads/...'],
//   ['id_foto' => 2, 'foto_path' => 'uploads/...']
// ]
```

**Delete parking with photos:**
```php
// CASCADE DELETE automatically removes photos
DELETE FROM tempat_parkir WHERE id_tempat = 1;
```

## Configuration

### File Size Limit
**Location:** `/owner/manage-parking.php` line ~195
```php
if ($fileSize > 5 * 1024 * 1024) { // 5MB
```
Change `5` to different value for different limit.

### Maximum Photos Per Parking
**Location:** `/owner/manage-parking.php` line ~178
```php
$maxFiles = 5;
```
Change to allow more/fewer photos.

### Allowed Image Types
**Location:** `/owner/manage-parking.php` line ~213
```php
if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
```
Add/remove MIME types as needed.

## Troubleshooting

### Photos Not Saving
**Check:**
1. Upload directory writable: `ls -la /uploads/parking_photos/`
2. Database table exists: Run migration again
3. File permissions on uploads folder

**Solution:**
```bash
chmod 755 /uploads/parking_photos
```

### Photos Not Displaying
**Check:**
1. Verify files exist in `/uploads/parking_photos/`
2. Check `BASEURL` constant in config (should be `/spark`)
3. No 404 errors in browser console

**Debug:**
```php
// In getParkingPhotos() function, add:
error_log("Photos for $id_tempat: " . json_encode($photos));
```

### Upload Directory Permission Denied
**Solution:**
```bash
mkdir -p /uploads/parking_photos
chmod 755 /uploads/parking_photos
```

## Database Queries

### View all photos for a parking lot
```sql
SELECT * FROM parking_photos 
WHERE id_tempat = 1 
ORDER BY urutan ASC;
```

### Delete all photos for a parking lot
```sql
DELETE FROM parking_photos WHERE id_tempat = 1;
```

### Update photo order
```sql
UPDATE parking_photos 
SET urutan = 2 
WHERE id_foto = 5;
```

### Count photos per parking lot
```sql
SELECT id_tempat, COUNT(*) as photo_count
FROM parking_photos
GROUP BY id_tempat;
```

## Future Enhancements

### Planned Features
- [ ] Drag-to-reorder photos
- [ ] Delete individual photos from parking card
- [ ] Show current photos in edit modal
- [ ] Photo compression for storage optimization
- [ ] Thumbnail caching for faster loading
- [ ] Lightbox popup for full-size photo view

### Code Locations for Enhancement
- **Photo reordering:** Add `sortable.js` library + update UI handler
- **Photo deletion:** Create `/api/delete-parking-photo.php` endpoint
- **Photo compression:** Use `ImageMagick` or `GD` library in `handlePhotoUpload()`
- **Edit modal:** Fetch existing photos in `editParking()` function

## File Manifest

**Modified Files:**
- `/owner/manage-parking.php` - Added functions, updated form, added photo display
- `/assets/css/owner.css` - Styling already in place

**New Files:**
- `/database/add_parking_photos.sql` - Migration SQL
- `/database/execute_migration.php` - Migration execution script

**Created on Upload:**
- `/uploads/parking_photos/` - Photo storage directory

## Support
For issues or questions, check:
1. Browser console for JavaScript errors
2. Server logs for PHP errors
3. Database for migration status
4. File permissions on upload directory
