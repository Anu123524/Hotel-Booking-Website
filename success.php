<?php
require_once 'config/database.php';
session_start();

$guest_name = $_GET['guest'] ?? 'Valued Guest';
$booking_id = $_GET['booking_id'] ?? null;
$booking = null;
$error = '';
$success_msg = '';

if ($booking_id) {
    $stmt = $pdo->prepare("SELECT b.*, r.name as room_name FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
}

// Handle One-Time Edit Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['refine_stay'])) {
    $new_in = $_POST['check_in'];
    $new_out = $_POST['check_out'];
    $new_arrival = $_POST['arrival_time'];
    $bid = $_POST['bid'];

    // Verify it hasn't been edited yet
    $check = $pdo->prepare("SELECT is_edited FROM bookings WHERE id = ?");
    $check->execute([$bid]);
    $status = $check->fetchColumn();

    if ($status == 0) {
        $update = $pdo->prepare("UPDATE bookings SET check_in = ?, check_out = ?, arrival_time = ?, is_edited = 1 WHERE id = ?");
        $update->execute([$new_in, $new_out, $new_arrival, $bid]);
        $success_msg = "Your stay details have been successfully finalized!";

        // Refresh data
        $stmt->execute([$bid]);
        $booking = $stmt->fetch();
    }
    else {
        $error = "This booking has already been finalized and cannot be edited further.";
    }
}

require_once 'includes/header.php';
?>

<div style="padding-top: 150px; padding-bottom: 120px; min-height: 90vh; background: #fff;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px; text-align: center;">
        
        <?php if (!$success_msg && !$error): ?>
        <div style="margin-bottom: 30px;">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="11" stroke="var(--accent-color)" stroke-width="1"/>
                <path d="M7 13L10 16L17 9" stroke="var(--accent-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--primary-color); margin-bottom: 15px;">Reservation Confirmed</h1>
        <p style="color: #666; margin-bottom: 40px;">Thank you, <b><?php echo htmlspecialchars($guest_name); ?></b>. Your Booking ID is <b>#<?php echo $booking_id; ?></b>.</p>
        <?php
endif; ?>

        <?php if ($success_msg): ?>
            <div style="background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; padding: 20px; border-radius: 8px; margin-bottom: 30px; font-weight: 600;">
                <span style="margin-right: 10px;">✅</span> <?php echo $success_msg; ?>
            </div>
        <?php
endif; ?>

        <?php if ($error): ?>
            <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 20px; border-radius: 8px; margin-bottom: 30px; font-weight: 600;">
                <span style="margin-right: 10px;">⚠️</span> <?php echo $error; ?>
            </div>
        <?php
endif; ?>

        <?php if ($booking): ?>
        <div style="background: #fafafa; border: 1px solid #eee; border-radius: 8px; overflow: hidden; text-align: left; margin-top: 20px;">
            <div style="background: var(--primary-color); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;"><?php echo $booking['room_name']; ?></h3>
                <span>ID: #<?php echo $booking['id']; ?></span>
            </div>
            
            <div style="padding: 40px;">
                <?php if ($booking['is_edited'] == 0): ?>
                    <h4 style="color: var(--accent-color); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 2px; margin-bottom: 25px;">Finalize Your Details (One-time Option)</h4>
                    <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <input type="hidden" name="bid" value="<?php echo $booking['id']; ?>">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 0.8rem; color: #888;">Check-In Date</label>
                            <input type="date" name="check_in" value="<?php echo $booking['check_in']; ?>" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 0.8rem; color: #888;">Check-Out Date</label>
                            <input type="date" name="check_out" value="<?php echo $booking['check_out']; ?>" class="form-input" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display: block; margin-bottom: 8px; font-size: 0.8rem; color: #888;">Estimated Arrival Timing</label>
                            <select name="arrival_time" class="form-input" required>
                                <option value="10:00 AM" <?php if ($booking['arrival_time'] == '10:00 AM')
            echo 'selected'; ?>>10:00 AM</option>
                                <option value="12:00 PM" <?php if ($booking['arrival_time'] == '12:00 PM')
            echo 'selected'; ?>>12:00 PM (Standard)</option>
                                <option value="02:00 PM" <?php if ($booking['arrival_time'] == '02:00 PM')
            echo 'selected'; ?>>02:00 PM</option>
                                <option value="04:00 PM" <?php if ($booking['arrival_time'] == '04:00 PM')
            echo 'selected'; ?>>04:00 PM</option>
                                <option value="06:00 PM" <?php if ($booking['arrival_time'] == '06:00 PM')
            echo 'selected'; ?>>06:00 PM</option>
                                <option value="Late Night" <?php if ($booking['arrival_time'] == 'Late Night')
            echo 'selected'; ?>>After 8:00 PM</option>
                            </select>
                        </div>
                        <button type="submit" name="refine_stay" class="btn-premium" style="grid-column: span 2; padding: 18px; font-weight: 700;">Confirm Final Details</button>
                    </form>
                <?php
    else: ?>
                    <div style="text-align: center; padding: 20px 0;">
                        <h3 style="color: var(--primary-color); margin-bottom: 10px;">Details Finalized</h3>
                        <p style="color: #888; font-size: 0.9rem; margin-bottom: 30px;">Your stay dates and arrival timing are locked in. We'll see you soon!</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; border-top: 1px solid #eee; padding-top: 30px;">
                            <div>
                                <small style="display: block; color: #bbb; margin-bottom: 5px;">Check-In</small>
                                <b><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></b>
                            </div>
                            <div>
                                <small style="display: block; color: #bbb; margin-bottom: 5px;">Check-Out</small>
                                <b><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></b>
                            </div>
                            <div>
                                <small style="display: block; color: #bbb; margin-bottom: 5px;">Arrival</small>
                                <b><?php echo $booking['arrival_time']; ?></b>
                            </div>
                        </div>
                    </div>
                <?php
    endif; ?>
            </div>
        </div>
        <?php
endif; ?>

        <div style="margin-top: 50px; display: flex; gap: 20px; justify-content: center;">
            <a href="index.php" class="btn-premium" style="padding: 15px 40px; background: #eee; color: #444; border: none;">Return Home</a>
            <a href="rooms.php" class="btn-premium" style="padding: 15px 40px; background: var(--text-dark); color: white; border: none;">Explore More Suites</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
