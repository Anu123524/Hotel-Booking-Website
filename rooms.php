<?php
require_once 'config/database.php';
session_start();

// Public guest access enabled

require_once 'includes/header.php';
?>

<header style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/hero.jpg'); background-size: cover; background-position: center; height: 350px; display: flex; align-items: center; justify-content: center;">
    <h1 style="color: white; font-size: 4rem; text-shadow: 0 5px 15px rgba(0,0,0,0.3);">Grand Selection</h1>
</header>

<main>
    <div class="room-grid" style="margin-top: 100px;">
        <?php
$stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'Available'");
while ($room = $stmt->fetch()):
?>
        <article class="room-card reveal visible">
            <div class="room-img-wrapper">
                <div class="room-img" style="background-image: url('images/rooms/<?php echo $room['image']; ?>');"></div>
            </div>
            <div class="room-info">
                <small style="color: var(--accent-color); letter-spacing: 1px; font-weight: 700; text-transform: uppercase;"><?php echo $room['type']; ?></small>
                <h3 style="margin: 10px 0;"><?php echo $room['name']; ?></h3>
                <p style="font-size: 0.9rem; color: #666; min-height: 80px;"><?php echo $room['description']; ?></p>
                <div class="room-price">₹<?php echo number_format($room['price'] * 80, 0); ?> <span style="font-size: 0.8rem; font-weight: 400; color: #999;">/ night</span></div>
                
                <form action="booking.php" method="GET">
                    <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                    <button type="submit" class="btn-premium" style="width: 100%; padding: 15px; font-size: 0.9rem;">Book Securely</button>
                </form>
            </div>
        </article>
        <?php
endwhile; ?>
    </div>
</main>

<script>
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });
    reveals.forEach(r => observer.observe(r));
</script>

<?php require_once 'includes/footer.php'; ?>
