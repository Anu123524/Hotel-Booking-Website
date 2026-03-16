<?php
require_once 'config/database.php';
session_start();

$error = '';

// Handle Booking Submission from Modal (Self-POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proceed_to_payment'])) {
    $_SESSION['temp_booking'] = [
        'room_id' => $_POST['room_id'],
        'guest_name' => trim($_POST['guest_name']),
        'guest_email' => trim($_POST['guest_email']),
        'guest_contact' => trim($_POST['guest_contact']),
        'guest_address' => trim($_POST['guest_address']),
        'guest_age' => (int)$_POST['guest_age'],
        'num_adults' => (int)$_POST['num_adults'],
        'num_children' => (int)$_POST['num_children'],
        'num_rooms' => (int)$_POST['num_rooms'],
        'check_in' => $_POST['check_in'],
        'check_out' => $_POST['check_out']
    ];

    $days = (strtotime($_POST['check_out']) - strtotime($_POST['check_in'])) / (60 * 60 * 24);
    if ($days <= 0) {
        $error = "Check-out must be after check-in.";
    }
    else {
        // Availability Check
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status != 'Cancelled' AND (
            (check_in < ? AND check_out > ?) OR
            (check_in < ? AND check_out > ?) OR
            (check_in >= ? AND check_out <= ?)
        )");
        $check_stmt->execute([
            $_POST['room_id'],
            $_POST['check_out'], $_POST['check_in'],
            $_POST['check_out'], $_POST['check_in'],
            $_POST['check_in'], $_POST['check_out']
        ]);

        if ($check_stmt->fetchColumn() > 0) {
            $error = "This room is already reserved for the selected dates. Please choose different dates or another suite.";
        }
        else {
            header("Location: payment.php");
            exit();
        }
    }
}

require_once 'includes/header.php';
?>

<main style="padding-top: 120px; background: #fdfdfd; min-height: 100vh;">
    <!-- Hero Section -->
    <section style="text-align: center; padding: 60px 20px; background: white; margin-bottom: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.02);">
        <h1 style="font-size: 3rem; color: var(--primary-color);">Exquisite Suites</h1>
        <p style="color: #888; letter-spacing: 2px; text-transform: uppercase; font-size: 0.8rem; font-weight: 600; margin-top: 10px;">Select your sanctuary at Grand Vista</p>
    </section>

    <div style="max-width: 1400px; margin: 0 auto; padding: 0 40px;">
        <?php if ($error): ?>
            <div style="background: #fff5f5; color: #c53030; padding: 15px; border-radius: 4px; margin-bottom: 30px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php
endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 40px; margin-bottom: 100px;">
            <?php
// Fetch rooms with gallery images
$rooms_stmt = $pdo->query("SELECT r.*, GROUP_CONCAT(ri.image_path) as gallery 
                                     FROM rooms r 
                                     LEFT JOIN room_images ri ON r.id = ri.room_id 
                                     WHERE r.status = 'Available' 
                                     GROUP BY r.id");
while ($room = $rooms_stmt->fetch()):
    $gallery = $room['gallery'] ? explode(',', $room['gallery']) : [];
    array_unshift($gallery, $room['image']); // Add main image to start
    $room['gallery_list'] = $gallery;

    // Base64 encode the room data to prevent ANY quote/special char issues
    $room_data = base64_encode(json_encode($room));
?>
            <div class="room-card" style="background: white; border: 1px solid #eee; border-radius: 4px; overflow: hidden; transition: all 0.4s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                <div style="height: 250px; background-image: url('images/rooms/<?php echo $room['image']; ?>'); background-size: cover; background-position: center; border-bottom: 3px solid var(--accent-color);"></div>
                <div style="padding: 35px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <small style="color: var(--accent-color); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 2px;"><?php echo $room['type']; ?></small>
                        <span style="font-weight: 700; color: var(--primary-color); font-size: 1.3rem;">₹<?php echo number_format($room['price'] * 80, 0); ?><small style="font-size: 0.7rem; color: #999; font-weight: 400;">/night</small></span>
                    </div>
                    <h3 style="margin-bottom: 15px; font-size: 1.5rem;"><?php echo $room['name']; ?></h3>
                    <p style="color: #777; font-size:0.9rem; line-height: 1.8; margin-bottom: 30px;"><?php echo substr($room['description'], 0, 120); ?>...</p>
                    <button data-room="<?php echo $room_data; ?>" onclick='openBookingModal(this)' class="btn-premium" style="width: 100%; padding: 18px; font-weight: 700; letter-spacing: 1px;">Start Reservation</button>
                </div>
            </div>
            <?php
endwhile; ?>
        </div>
    </div>
</main>

<!-- Unified Booking Modal with Slideshow -->
<div id="bookingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(8px);">
    <div style="background: white; width: 95%; max-width: 1000px; border-radius: 8px; display: grid; grid-template-columns: 1fr 1.2fr; overflow: hidden; position: relative; animation: modalIn 0.4s ease;">
        <button onclick="closeBookingModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #aaa; z-index: 10;">✕</button>
        
        <div id="modalImageArea" style="position: relative; height: 100%; overflow: hidden; background: #000;">
            <div id="modalSlideshow" style="height: 100%; display: flex; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);">
                <!-- Images injected via JS -->
            </div>
            
            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); padding: 40px; color: white; z-index: 5;">
                <h3 id="modalRoomName" style="font-size: 2.2rem; border-bottom: 2px solid var(--accent-color); display: inline-block; padding-bottom: 10px; margin-bottom: 15px; font-family: 'Playfair Display', serif;"></h3>
                <p id="modalRoomPrice" style="font-size: 1.2rem; font-weight: 300; opacity: 0.9;"></p>
            </div>

            <button onclick="changeSlide(-1)" style="position: absolute; top: 50%; left: 20px; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: none; color: white; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); z-index: 6; border: 1px solid rgba(255,255,255,0.2);">❮</button>
            <button onclick="changeSlide(1)" style="position: absolute; top: 50%; right: 20px; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: none; color: white; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); z-index: 6; border: 1px solid rgba(255,255,255,0.2);">❯</button>
            
            <div id="slideDots" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 6;"></div>
        </div>

        <div style="padding: 50px; overflow-y: auto; max-height: 90vh;">
            <h4 style="text-transform: uppercase; letter-spacing: 2px; color: #bbb; font-size: 0.7rem; margin-bottom: 30px; font-weight: 700;">Reservations Desk</h4>
            <form method="POST">
                <input type="hidden" name="room_id" id="modalRoomId">
                
                <div style="margin-bottom: 20px;">
                    <label class="modal-label">Guest Name</label>
                    <input type="text" name="guest_name" class="form-input" required placeholder="John Doe" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label class="modal-label">Gmail / Email</label>
                        <input type="email" name="guest_email" class="form-input" required placeholder="user@gmail.com">
                    </div>
                    <div>
                        <label class="modal-label">Mobile Contact</label>
                        <input type="tel" name="guest_contact" class="form-input" required placeholder="10-Digit Mobile Number" pattern="[0-9]{10}" maxlength="10" title="Please enter a 10-digit mobile number">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="modal-label">Residential Address</label>
                    <textarea name="guest_address" class="form-input" required style="height: 60px;" placeholder="Full Address (No special characters)" pattern="[A-Za-z0-9\s,\.]+"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                    <div>
                        <label class="modal-label">Age</label>
                        <input type="number" name="guest_age" class="form-input" required min="18">
                    </div>
                    <div>
                        <label class="modal-label">Adults</label>
                        <input type="number" name="num_adults" class="form-input" value="1" min="1" max="2">
                    </div>
                    <input type="hidden" name="num_rooms" value="1">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 35px; background: #fafafa; padding: 20px; border-radius: 4px;">
                    <div>
                        <label class="modal-label">Check-In</label>
                        <input type="date" name="check_in" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label class="modal-label">Check-Out</label>
                        <input type="date" name="check_out" class="form-input" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>

                <button type="submit" name="proceed_to_payment" class="btn-premium" style="width: 100%; padding: 20px; font-weight: 700;">Proceed to Secure Payment</button>
            </form>
        </div>
    </div>
</div>

<style>
    .modal-label { display: block; margin-bottom: 6px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #555; }
    @keyframes modalIn { from { opacity: 0; transform: translateY(30px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .room-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-color: var(--accent-color); }
</style>

<script>
let currentSlideIndex = 0;
let roomGallery = [];

function openBookingModal(btn) {
    try {
        const roomData = atob(btn.getAttribute('data-room'));
        const room = JSON.parse(roomData);
        console.log("Opening modal for room:", room);
        
        roomGallery = room.gallery_list || [room.image];
        currentSlideIndex = 0;

        // Build Slideshow
        const slideshow = document.getElementById('modalSlideshow');
        slideshow.innerHTML = '';
        roomGallery.forEach(img => {
            const slide = document.createElement('div');
            slide.style.minWidth = '100%';
            slide.style.height = '100%';
            slide.style.backgroundImage = `url('images/rooms/${img}')`;
            slide.style.backgroundSize = 'cover';
            slide.style.backgroundPosition = 'center';
            slideshow.appendChild(slide);
        });

        // Build Dots
        const dots = document.getElementById('slideDots');
        dots.innerHTML = '';
        roomGallery.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.style.width = '8px';
            dot.style.height = '8px';
            dot.style.background = i === 0 ? 'var(--accent-color)' : 'rgba(255,255,255,0.3)';
            dot.style.borderRadius = '50%';
            dots.appendChild(dot);
        });

        document.getElementById('modalRoomId').value = room.id;
        document.getElementById('modalRoomName').innerText = room.name;
        document.getElementById('modalRoomPrice').innerText = 'Starting from ₹' + (room.price * 80).toLocaleString() + ' / night';
        
        document.getElementById('bookingModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        updateSlide();
    } catch (e) {
        console.error("Error opening booking modal:", e);
        alert("Sorry, there was an issue opening the booking form. Please try again.");
    }
}

function changeSlide(n) {
    currentSlideIndex += n;
    if (currentSlideIndex >= roomGallery.length) currentSlideIndex = 0;
    if (currentSlideIndex < 0) currentSlideIndex = roomGallery.length - 1;
    updateSlide();
}

function updateSlide() {
    const slideshow = document.getElementById('modalSlideshow');
    slideshow.style.transform = `translateX(-${currentSlideIndex * 100}%)`;
    
    // Update Dots
    const dots = document.querySelectorAll('#slideDots div');
    dots.forEach((dot, i) => {
        dot.style.background = i === currentSlideIndex ? 'var(--accent-color)' : 'rgba(255,255,255,0.3)';
    });
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}
</script>

<?php require_once 'includes/footer.php'; ?>
