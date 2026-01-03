<!-- BOOKING FORM (Left Column) -->
<?php
// Ensure variables are set to avoid warnings
$parking_id = $parking['id_tempat'] ?? '';
$price = $parking['harga_per_jam'] ?? 0;
$date = $bookingData['date'] ?? '';
$time = $bookingData['time_start'] ?? '';
$duration = $bookingData['duration'] ?? 0;
?>

<form id="bookingForm" action="<?= BASEURL ?>/actions/process-booking.php" method="POST" class="booking-form">
    <!-- Hidden Fields -->
    <input type="hidden" name="id_tempat" value="<?= htmlspecialchars($parking_id) ?>">
    <input type="hidden" name="harga_per_jam" value="<?= htmlspecialchars($price) ?>">
    <input type="hidden" name="tanggal_booking" value="<?= htmlspecialchars($date) ?>">
    <input type="hidden" name="waktu_mulai" value="<?= htmlspecialchars($time) ?>">
    <input type="hidden" name="durasi_jam" value="<?= htmlspecialchars($duration) ?>">

    <!-- Step Components -->
    <?php include __DIR__ . '/booking/step-contact.php'; ?>
    <?php include __DIR__ . '/booking/step-vehicle.php'; ?>
    <?php include __DIR__ . '/booking/step-payment.php'; ?>
    <?php include __DIR__ . '/booking/step-review.php'; ?>

</form>
