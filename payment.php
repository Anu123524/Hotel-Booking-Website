<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['temp_booking'])) {
    header("Location: rooms.php");
    exit();
}

$booking = $_SESSION['temp_booking'];
$room_id = $booking['room_id'];

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

$days = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24);
$per_night = $room['price'] * 80;
$total_rupees = $days * $per_night * $booking['num_rooms'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_now'])) {
    $guest_name = $booking['guest_name'];
    $guest_email = $booking['guest_email'];
    $guest_contact = $booking['guest_contact'];
    $guest_address = $booking['guest_address'];
    $guest_age = $booking['guest_age'];
    $num_adults = $booking['num_adults'];
    $num_children = $booking['num_children'];
    $num_rooms = $booking['num_rooms'];
    $check_in = $booking['check_in'];
    $check_out = $booking['check_out'];

    // Backend Date Validation
    $today = date('Y-m-d');
    if ($check_in < $today) {
        die("Check-in date cannot be in the past.");
    }
    if ($check_out <= $check_in) {
        die("Check-out must be after check-in.");
    }

    $user_id = $_SESSION['user_id'] ?? null;
    $total_price = $total_rupees;
    $payment_method = $_POST['payment_method'];

    // Final Availability Check (Prevent double-booking)
    $final_check = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status != 'Cancelled' AND (
            (check_in < ? AND check_out > ?) OR
            (check_in < ? AND check_out > ?) OR
            (check_in >= ? AND check_out <= ?)
        )");
    $final_check->execute([$room_id, $check_out, $check_in, $check_out, $check_in, $check_in, $check_out]);
    if ($final_check->fetchColumn() > 0) {
        die("Fatal Error: The room was booked by someone else while you were processing. Please try again with different dates.");
    }

    // Anti-Spam Check: Limit active bookings per phone number
    if ($payment_method == 'Cash on Delivery') {
        $spam_check = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE guest_contact = ? AND status IN ('Confirmed', 'Checked In')");
        $spam_check->execute([$guest_contact]);
        if ($spam_check->fetchColumn() >= 1) {
            die("Security Notice: You already have an active 'Pay at Hotel' reservation. Please fulfill your current booking before creating another, or use Online Payment.");
        }
    }

    $_SESSION['last_booking_time'] = time();

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, guest_name, guest_email, guest_contact, guest_address, guest_age, room_id, check_in, check_out, num_adults, num_children, num_rooms, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
    $stmt->execute([$user_id, $guest_name, $guest_email, $guest_contact, $guest_address, $guest_age, $room_id, $check_in, $check_out, $num_adults, $num_children, $num_rooms, $total_price]);
    $booking_id = $pdo->lastInsertId();

    // Mock Email Confirmation
    $to = $guest_email;
    $subject = "Your Grand Vista Sanctuary is Confirmed";
    $message = "Hello $guest_name,\n\nYour reservation (ID: #$booking_id) for ${room['name']} is confirmed.\nTotal: ₹$total_price\n\nYou can manage your stay or change your arrival time here: http://grandvista.infinityfreeapp.com/success.php?booking_id=$booking_id";
    $headers = "From: arjunshetty@gmail.com";
    @mail($to, $subject, $message, $headers);

    unset($_SESSION['temp_booking']);
    header("Location: success.php?msg=success&guest=" . urlencode($guest_name) . "&booking_id=" . $booking_id);
    exit();
}

require_once 'includes/header.php';
?>

<div style="padding-top: 150px; padding-bottom: 100px; min-height: 90vh; background: #fafafa;">
    <div style="max-width: 800px; margin: 0 auto; background: white; padding: 50px; border-radius: 4px; box-shadow: var(--shadow);">
        <h2 style="text-align: center; color: var(--primary-color); font-size: 2.5rem; margin-bottom: 40px;">Select Payment Method</h2>
        
        <div style="background: #fdfdfd; padding: 30px; border: 1px solid #eee; margin-bottom: 40px; border-radius: 4px;">
            <h4 style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;">Booking Summary</h4>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Guest:</span>
                <span style="font-weight: 600;"><?php echo htmlspecialchars($booking['guest_name']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Room:</span>
                <span style="font-weight: 600;"><?php echo $room['name']; ?> (x<?php echo $booking['num_rooms']; ?>)</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.5rem; color: var(--primary-color); margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                <span style="font-weight: 700;">Total Payable:</span>
                <span style="font-weight: 700;">₹<?php echo number_format($total_rupees, 0); ?></span>
            </div>
        </div>

        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                <label style="border: 1px solid #eee; padding: 30px; border-radius: 4px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 10px; transition: all 0.3s;" class="pay-option">
                    <input type="radio" name="payment_method" value="UPI/GPay" required checked style="display: none;">
                    <img src="https://img.icons8.com/color/48/000000/google-pay.png" width="40">
                    <span style="font-weight: 600; font-size: 0.9rem;">UPI / Google Pay</span>
                    <small style="color: #999;">Instant Confirmation</small>
                </label>
                <label style="border: 1px solid #eee; padding: 30px; border-radius: 4px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 10px; transition: all 0.3s;" class="pay-option">
                    <input type="radio" name="payment_method" value="Cash on Delivery" required style="display: none;">
                    <img src="https://img.icons8.com/color/48/000000/cash-in-hand.png" width="40">
                    <span style="font-weight: 600; font-size: 0.9rem;">Pay at Hotel (COD)</span>
                    <small style="color: #999;">Pay on Arrival</small>
                </label>
            </div>

            <style>
                .pay-option:has(input:checked) {
                    border-color: var(--accent-color);
                    background: rgba(253, 187, 45, 0.05);
                    box-shadow: 0 5px 15px rgba(253, 187, 45, 0.1);
                }
                .pay-option:hover { border-color: #ccc; }
            </style>
            
            <button type="submit" name="pay_now" class="btn-premium" style="width: 100%; padding: 20px;">Confirm & Book Now</button>
        </form>
    </div>
</div>

<?php require_once 'includes/header.php'; ?>
