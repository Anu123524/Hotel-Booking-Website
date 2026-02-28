<?php
require_once 'config/database.php';
session_start();

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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proceed_to_payment'])) {
    // Store data in session to pass to payment page
    $_SESSION['temp_booking'] = [
        'room_id' => $room_id,
        'guest_name' => trim($_POST['guest_name']),
        'guest_age' => (int)$_POST['guest_age'],
        'num_adults' => (int)$_POST['num_adults'],
        'num_children' => (int)$_POST['num_children'],
        'num_rooms' => (int)$_POST['num_rooms'],
        'check_in' => $_POST['check_in'],
        'check_out' => $_POST['check_out'],
        'room_price' => $room['price']
    ];

    $days = (strtotime($_POST['check_out']) - strtotime($_POST['check_in'])) / (60 * 60 * 24);
    if ($days <= 0) {
        $error = "Check-out date must be after check-in date.";
    }
    else {
        header("Location: payment.php");
        exit();
    }
}

// Fetch gallery images
$gallery = [];
try {
    $stmt_gallery = $pdo->prepare("SELECT image_path FROM room_images WHERE room_id = ?");
    $stmt_gallery->execute([$room_id]);
    $gallery = $stmt_gallery->fetchAll(PDO::FETCH_COLUMN);
}
catch (PDOException $e) {
    // Table might not exist yet on live
    $gallery = [];
}

// Fallback to main image if no gallery or table error
if (empty($gallery)) {
    $gallery = [$room['image']];
}

require_once 'includes/header.php';
?>

<style>
    .slider-container { position: relative; width: 100%; height: 400px; overflow: hidden; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: #eee; }
    .slider-track { display: flex; transition: transform 0.5s ease-in-out; height: 100%; }
    .slide { min-width: 100%; height: 100%; background-size: cover; background-position: center; }
    .slider-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.3); color: white; border: none; padding: 15px; cursor: pointer; z-index: 10; transition: background 0.3s; }
    .slider-btn:hover { background: rgba(0,0,0,0.6); }
    .prev { left: 10px; }
    .next { right: 10px; }
    .dots { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; }
    .dot.active { background: white; width: 20px; border-radius: 4px; }
</style>

<div style="padding-top: 150px; padding-bottom: 100px; min-height: 90vh; background: #fdfdfd;">
    <div style="max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 60px; background: white; padding: 50px; box-shadow: var(--shadow); border-radius: 4px;">
        <section>
            <div class="slider-container">
                <div class="slider-track" id="sliderTrack">
                    <?php foreach ($gallery as $img): ?>
                        <div class="slide" style="background-image: url('images/rooms/<?php echo $img; ?>');"></div>
                    <?php
endforeach; ?>
                </div>
                <?php if (count($gallery) > 1): ?>
                    <button class="slider-btn prev" onclick="moveSlide(-1)">❮</button>
                    <button class="slider-btn next" onclick="moveSlide(1)">❯</button>
                    <div class="dots" id="sliderDots">
                        <?php foreach ($gallery as $index => $img): ?>
                            <div class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $index; ?>)"></div>
                        <?php
    endforeach; ?>
                    </div>
                <?php
endif; ?>
            </div>

            <script>
                let currentIdx = 0;
                const track = document.getElementById('sliderTrack');
                const dots = document.querySelectorAll('.dot');
                const totalSlides = <?php echo count($gallery); ?>;

                function moveSlide(step) {
                    currentIdx = (currentIdx + step + totalSlides) % totalSlides;
                    updateSlider();
                }

                function currentSlide(idx) {
                    currentIdx = idx;
                    updateSlider();
                }

                function updateSlider() {
                    track.style.transform = `translateX(-${currentIdx * 100}%)`;
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('active', i === currentIdx);
                    });
                }
            </script>
            <div style="padding: 30px 0;">
                <h2 style="color: var(--primary-color); font-size: 2.2rem;"><?php echo $room['name']; ?></h2>
                <div style="height: 3px; width: 60px; background: var(--accent-color); margin: 15px 0 25px;"></div>
                <p style="color: #666; line-height: 1.8; font-weight: 300;"><?php echo $room['description']; ?></p>
                <div style="margin-top: 30px; font-size: 2rem; font-weight: 700; color: var(--text-dark);">₹<?php echo number_format($room['price'] * 80, 0); ?> <span style="font-size: 0.9rem; font-weight: 400; color: #999;">/ night</span></div>
            </div>
        </section>

        <section style="background: #fafafa; padding: 40px; border-radius: 4px;">
            <h3 style="margin-bottom: 40px; font-size: 1.8rem; text-align: center;">Guest Information</h3>
            <?php if ($error): ?>
                <p style="color: var(--secondary-color); text-align: center; margin-bottom: 25px; padding: 10px; background: #fff; border: 1px solid #eee;"><?php echo $error; ?></p>
            <?php
endif; ?>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Full Name</label>
                    <input type="text" name="guest_name" class="form-input" required placeholder="Enter your full name">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Age</label>
                        <input type="number" name="guest_age" class="form-input" required min="18">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">No. of Rooms</label>
                        <input type="number" name="num_rooms" class="form-input" required min="1" value="1">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Adults</label>
                        <input type="number" name="num_adults" class="form-input" required min="1" value="1">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Children</label>
                        <input type="number" name="num_children" class="form-input" required min="0" value="0">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Check-In</label>
                        <input type="date" name="check_in" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Check-Out</label>
                        <input type="date" name="check_out" class="form-input" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>
                
                <button type="submit" name="proceed_to_payment" class="btn-premium" style="width: 100%; padding: 20px;">Proceed to Payment</button>
            </form>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
