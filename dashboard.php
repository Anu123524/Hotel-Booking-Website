<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
require_once 'includes/header.php';
?>

<div style="padding-top: 150px; padding-bottom: 80px; min-height: 80vh; background: #f9f9f9;">
    <div style="max-width: 1100px; margin: 0 auto; padding: 0 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
            <h1 style="font-size: 2.5rem; color: var(--primary-color);">Welcome, <?php echo $_SESSION['user_name']; ?></h1>
            <a href="rooms.php" class="btn-premium" style="font-size: 0.8rem;">Book New Stay</a>
        </div>

        <h3 style="margin-bottom: 30px; font-size: 1.5rem; border-bottom: 2px solid var(--accent-color); display: inline-block;">Your Recent Bookings</h3>

        <?php
$stmt = $pdo->prepare("SELECT b.*, r.name as room_name, r.image FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

if (empty($bookings)):
?>
            <div style="text-align: center; background: white; padding: 60px; border-radius: 20px; box-shadow: var(--shadow); margin-top: 30px;">
                <p style="font-size: 1.2rem; color: #666;">You haven't made any reservations yet.</p>
                <a href="rooms.php" style="color: var(--primary-color); font-weight: 600; text-decoration: underline;">Start exploring our rooms</a>
            </div>
        <?php
else: ?>
            <div style="display: grid; gap: 20px;">
                <?php foreach ($bookings as $booking): ?>
                <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: var(--shadow); display: flex; align-items: center; gap: 30px;">
                    <div style="width: 120px; height: 90px; background-image: url('images/rooms/<?php echo $booking['image']; ?>'); background-size: cover; background-position: center; border-radius: 10px;"></div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 1.2rem; color: var(--primary-color);"><?php echo $booking['room_name']; ?></h4>
                        <p style="font-size: 0.9rem; color: #666;"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?> — <?php echo date('M d, Y', strtotime($booking['check_out'])); ?></p>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--secondary-color);">$<?php echo $booking['total_price']; ?></div>
                        <span style="font-size: 0.8rem; background: #e8f5e9; color: #2e7d32; padding: 5px 15px; border-radius: 50px; font-weight: 600;"><?php echo $booking['status']; ?></span>
                    </div>
                </div>
                <?php
    endforeach; ?>
            </div>
        <?php
endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
