<?php

require_once 'config/database.php';
require_once 'includes/header.php';

?>

<div class="hero">
    <h1>Your Sanctuary of Serenity</h1>
    <p>Discover a world where luxury meets nature. From golden sunsets to silver service, every moment at Grand Vista is a masterpiece.</p>
    <a href="rooms.php" class="btn-premium">Explore our Suites</a>
</div>

<section class="section-title">
    <h2>Exceptional Accommodations</h2>
    <p>Curated spaces designed for ultimate comfort and sophistication.</p>
</section>

<div class="room-grid">
    <?php
$stmt = $pdo->query("SELECT * FROM rooms LIMIT 3");
while ($room = $stmt->fetch()):
?>
    <div class="room-card">
        <div class="room-img" style="background-image: url('images/rooms/<?php echo $room['image']; ?>');"></div>
        <div class="room-info">
            <span style="color: #666; font-size: 0.8rem; text-transform: uppercase;"><?php echo $room['type']; ?></span>
            <h3 style="margin: 5px 0;"><?php echo $room['name']; ?></h3>
            <p style="font-size: 0.9rem; color: #555;"><?php echo substr($room['description'], 0, 100) . '...'; ?></p>
            <div class="room-price">$<?php echo $room['price']; ?> <span style="font-size: 0.8rem; font-weight: 400; color: #999;">/ night</span></div>
            <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn-premium" style="display: block; text-align: center; padding: 10px; font-size: 0.8rem;">View Details</a>
        </div>
    </div>
    <?php
endwhile; ?>
</div>

<section style="background: #fff; padding: 100px 10%; display: flex; align-items: center; gap: 50px;">
    <div style="flex: 1;">
        <img src="images/hero.jpg" alt="Hotel Interior" style="width: 100%; border-radius: 20px; box-shadow: var(--shadow);">
    </div>
    <div style="flex: 1;">
        <h2 style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 20px;">Unparalleled Excellence</h2>
        <p style="margin-bottom: 30px; font-size: 1.1rem; color: #666;">At Grand Vista, we believe in the art of hospitality. Our dedicated staff is committed to anticipating your every need, ensuring your stay is nothing short of extraordinary.</p>
        <ul style="list-style: none;">
            <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                <span style="color: var(--accent-color); font-weight: bold;">✓</span> 24/7 Personalized Butler Service
            </li>
            <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                <span style="color: var(--accent-color); font-weight: bold;">✓</span> Award-winning Coastal Dining
            </li>
            <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                <span style="color: var(--accent-color); font-weight: bold;">✓</span> Private Beach and Infinity Pools
            </li>
        </ul>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
