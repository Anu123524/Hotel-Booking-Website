<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$room_id = $_GET['room_id'] ?? null;
if (!$room_id) {
    header("Location: rooms.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    header("Location: rooms.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $user_id = $_SESSION['user_id'];

    // Simple price calculation
    $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
    if ($days <= 0) {
        $error = "Check-out date must be after check-in date.";
    }
    else {
        $total_price = $days * $room['price'];

        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, total_price, status) VALUES (?, ?, ?, ?, ?, 'Confirmed')");
        $stmt->execute([$user_id, $room_id, $check_in, $check_out, $total_price]);

        header("Location: dashboard.php?msg=success");
        exit();
    }
}

require_once 'includes/header.php';
?>

<div style="padding-top: 150px; padding-bottom: 80px; min-height: 80vh; background: #fdfdfd;">
    <div style="max-width: 900px; margin: 0 auto; display: flex; gap: 50px; background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow);">
        <div style="flex: 1;">
            <img src="images/rooms/<?php echo $room['image']; ?>" style="width: 100%; border-radius: 15px; box-shadow: var(--shadow);">
            <h2 style="margin-top: 20px; color: var(--primary-color);"><?php echo $room['name']; ?></h2>
            <p style="color: #666;"><?php echo $room['description']; ?></p>
            <div style="margin-top: 20px; font-size: 1.4rem; font-weight: 700; color: var(--secondary-color);">$<?php echo $room['price']; ?> <span style="font-size: 0.9rem; font-weight: 400; color: #999;">/ night</span></div>
        </div>

        <div style="flex: 1; border-left: 1px solid #eee; padding-left: 50px;">
            <h3 style="margin-bottom: 30px; font-size: 1.8rem;">Confirm Reservation</h3>
            <?php if ($error): ?>
                <p style="color: var(--secondary-color); margin-bottom: 20px;"><?php echo $error; ?></p>
            <?php
endif; ?>

            <form method="POST">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Check-In Date</label>
                <input type="date" name="check_in" required min="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 12px; margin-bottom: 25px; border: 1px solid #ddd; border-radius: 8px;">
                
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Check-Out Date</label>
                <input type="date" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" style="width: 100%; padding: 12px; margin-bottom: 40px; border: 1px solid #ddd; border-radius: 8px;">
                
                <button type="submit" name="confirm_booking" class="btn-premium" style="width: 100%; border: none; cursor: pointer; padding: 15px;">Complete Booking</button>
                <p style="text-align: center; margin-top: 20px; font-size: 0.8rem; color: #999;">By clicking, you agree to our terms of service.</p>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
