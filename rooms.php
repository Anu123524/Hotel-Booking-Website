<?php
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/hero.jpg'); background-size: cover; background-position: center; height: 300px; display: flex; align-items: center; justify-content: center;">
    <h1 class="text-white" style="font-size: 3.5rem;">Our Luxurious Rooms</h1>
</div>

<div class="room-grid" style="margin-top: 80px;">
    <?php
$stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'Available'");
while ($room = $stmt->fetch()):
?>
    <div class="room-card">
        <div class="room-img" style="background-image: url('images/rooms/<?php echo $room['image']; ?>');"></div>
        <div class="room-info">
            <span style="color: #666; font-size: 0.8rem; text-transform: uppercase;"><?php echo $room['type']; ?></span>
            <h3 style="margin: 5px 0;"><?php echo $room['name']; ?></h3>
            <p style="font-size: 0.9rem; color: #555;"><?php echo $room['description']; ?></p>
            <div class="room-price">$<?php echo $room['price']; ?> <span style="font-size: 0.8rem; font-weight: 400; color: #999;">/ night</span></div>
            
            <form action="booking.php" method="GET">
                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                <button type="submit" class="btn-premium" style="width: 100%; border: none; cursor: pointer; padding: 12px; font-size: 0.9rem;">Book This Room</button>
            </form>
        </div>
    </div>
    <?php
endwhile; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
