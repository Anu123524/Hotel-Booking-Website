<?php
require_once 'config/database.php';
session_start();

// Admin Protection
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Logic: Image Upload, Delete, and Booking Edits
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Add Main Room
    if (isset($_POST['add_room'])) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        $image_name = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'pdf'];

        if (!in_array($ext, $allowed)) {
            echo "<script>alert('Invalid file format. Only JPG, JPEG, and PDF are allowed.'); window.location='admin_dashboard.php';</script>";
            exit();
        }

        $target = "images/rooms/" . basename($image_name);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $stmt = $pdo->prepare("INSERT INTO rooms (name, type, price, description, image, status) VALUES (?, ?, ?, ?, ?, 'Available')");
        $stmt->execute([$name, $type, $price, $description, $image_name]);
        header("Location: admin_dashboard.php?msg=room_added");
        exit();
    }

    // 2. Update Gallery Images
    if (isset($_POST['upload_gallery_image'])) {
        $room_id = $_POST['room_id'];
        $image_name_raw = $_FILES['gallery_image']['name'];
        $ext = strtolower(pathinfo($image_name_raw, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'pdf'];

        if (!in_array($ext, $allowed)) {
            header("Location: admin_dashboard.php?msg=invalid_file");
            exit();
        }

        $image_name = time() . "_" . $image_name_raw;
        $target = "images/rooms/" . basename($image_name);

        if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO room_images (room_id, image_path) VALUES (?, ?)");
            $stmt->execute([$room_id, $image_name]);
        }
        header("Location: admin_dashboard.php?msg=gallery_updated");
        exit();
    }

    // 3. Delete Gallery Image
    if (isset($_POST['delete_gallery_image'])) {
        $image_id = $_POST['image_id'];
        $stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE id = ?");
        $stmt->execute([$image_id]);
        $img = $stmt->fetch();
        if ($img) {
            @unlink("images/rooms/" . $img['image_path']);
            $pdo->prepare("DELETE FROM room_images WHERE id = ?")->execute([$image_id]);
        }
        header("Location: admin_dashboard.php?msg=image_deleted");
        exit();
    }

    // 4. Update Booking (Admin Edit)
    if (isset($_POST['update_booking'])) {
        $booking_id = $_POST['booking_id'];
        $room_id = $_POST['room_id'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE bookings SET room_id = ?, check_in = ?, check_out = ?, status = ? WHERE id = ?");
        $stmt->execute([$room_id, $check_in, $check_out, $status, $booking_id]);
        header("Location: admin_dashboard.php?msg=booking_updated");
        exit();
    }

    // 5. Delete Room
    if (isset($_POST['delete_room'])) {
        $id = $_POST['room_id'];
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin_dashboard.php?msg=room_deleted");
        exit();
    }

    // 6. Delete All Cancelled Bookings
    if (isset($_POST['clear_cancelled'])) {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE status = 'Cancelled'");
        $stmt->execute();
        header("Location: admin_dashboard.php?msg=cancelled_cleared");
        exit();
    }

    // 7. Delete Individual Booking
    if (isset($_POST['delete_booking'])) {
        $booking_id = $_POST['booking_id'];
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        header("Location: admin_dashboard.php?msg=booking_deleted");
        exit();
    }
}

// Download Report Logic
if (isset($_GET['action']) && $_GET['action'] == 'download') {
    $stmt = $pdo->query("SELECT b.id, b.guest_name, b.guest_email, b.guest_contact, b.num_adults + b.num_children as total_guests, r.name as room_name, b.check_in, b.check_out, b.total_price, b.status, b.created_at 
                         FROM bookings b 
                         JOIN rooms r ON b.room_id = r.id 
                         ORDER BY b.created_at DESC");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="grand_vista_report_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Guest Name', 'Email', 'Contact', 'Guests', 'Room', 'In', 'Out', 'Price', 'Status', 'Booked At']);
    foreach ($bookings as $b) {
        fputcsv($output, $b);
    }
    fclose($output);
    exit();
}

require_once 'includes/header.php';
?>

<main style="padding-top: 150px; padding-bottom: 100px; min-height: 100vh; background: #fafafa; font-family: 'Inter', sans-serif;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 40px;">
        <header style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 60px;">
            <div>
                <h1 style="font-size: 3.5rem; color: var(--primary-color);">Admin Oversight</h1>
                <p style="color: #888; margin-top: 10px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; font-size: 0.8rem;">Master Management System</p>
            </div>
            <div style="display: flex; gap: 20px;">
                <form method="POST" onsubmit="return confirm('Permanently delete all cancelled reservations?')">
                    <button type="submit" name="clear_cancelled" class="btn-premium" style="background: transparent; border-color: #e74c3c; color: #e74c3c; cursor: pointer;">Cleanup Cancelled</button>
                </form>
                <button onclick="document.getElementById('inventoryModal').style.display='flex'" class="btn-premium" style="background: transparent; border-color: var(--primary-color); color: var(--primary-color); cursor: pointer;">Manage Inventory</button>
                <a href="admin_dashboard.php?action=download" class="btn-premium">Download Full Report</a>
                <a href="logout.php" class="btn-premium" style="background: #e74c3c; border-color: #e74c3c;">Logout</a>
            </div>
        </header>

        <!-- Reservations Section -->
        <section id="bookings" style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); overflow-x: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; border-bottom: 2px solid #f9f9f9; padding-bottom: 15px;">
                <h3 style="color: var(--text-dark); margin: 0;">Live Reservations</h3>
                <small style="color: #bbb; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Sorted by Status & Date</small>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; min-width: 1100px;">
                <thead>
                    <tr style="text-align: left; background: #fdfdfd; color: var(--primary-color); text-transform: uppercase; font-size: 0.7rem; letter-spacing: 2px;">
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Guest Details</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Room Type</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Stay Window</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Status & Revenue</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$stmt = $pdo->query("SELECT b.*, r.name as room_name FROM bookings b JOIN rooms r ON b.room_id = r.id ORDER BY FIELD(b.status, 'Confirmed', 'Checked In', 'Checked Out', 'Cancelled'), b.created_at DESC");
$bookings = $stmt->fetchAll();

if (empty($bookings)): ?>
                        <tr><td colspan="5" style="padding: 100px; text-align: center; color: #ccc; font-style: italic;">No reservations yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): 
                            $status = strtolower(trim($b['status']));
                            $row_bg = 'transparent';
                            if (strpos($status, 'confirmed') !== false) $row_bg = 'rgba(16, 185, 129, 0.1)'; // Light Green
                            if (strpos($status, 'cancel') !== false) $row_bg = 'rgba(239, 68, 68, 0.1)'; // Light Red
                        ?>
                    <tr style="border-bottom: 1px solid #f9f9f9; transition: background 0.3s; cursor: default; background: <?php echo $row_bg; ?>;" onmouseover="this.style.background='<?php echo strpos($status, 'confirmed') !== false ? 'rgba(16, 185, 129, 0.15)' : (strpos($status, 'cancel') !== false ? 'rgba(239, 68, 68, 0.15)' : '#fcfcfc'); ?>'" onmouseout="this.style.background='<?php echo $row_bg; ?>'">
                        <td style="padding: 20px;">
                            <div style="font-weight: 700; font-size: 1.1rem; color: #333;"><?php echo htmlspecialchars($b['guest_name']); ?></div>
                            <div style="font-size: 0.85rem; color: #666; margin-top: 4px;"><?php echo htmlspecialchars($b['guest_email']); ?></div>
                            <div style="font-size: 0.8rem; color: #999;"><?php echo htmlspecialchars($b['guest_contact']); ?></div>
                        </td>
                        <td style="padding: 20px;">
                            <div style="font-weight: 600; color: var(--primary-color);"><?php echo $b['room_name']; ?></div>
                            <div style="font-size: 0.75rem; color: #888; margin-top: 4px;">Guests: <?php echo $b['num_adults'] + $b['num_children']; ?> (<?php echo $b['num_adults']; ?>A, <?php echo $b['num_children']; ?>K)</div>
                        </td>
                        <td style="padding: 20px;">
                            <div style="font-size: 0.95rem; font-weight: 600; color: #555;">
                                <?php echo date('d M', strtotime($b['check_in'])); ?> - <?php echo date('d M, Y', strtotime($b['check_out'])); ?>
                            </div>
                            <?php if ($b['arrival_time']): ?>
                                <div style="font-size: 0.7rem; color: var(--accent-color); font-weight: 800; text-transform: uppercase; margin-top: 5px;">ETA: <?php echo $b['arrival_time']; ?></div>
                            <?php
        endif; ?>
                        </td>
                        <td style="padding: 20px;">
                            <div style="font-weight: 800; color: <?php echo (strpos($status, 'cancel') !== false) ? '#ef4444' : '#2ecc71'; ?>; font-size: 1.1rem; margin-bottom: 8px;">₹<?php echo number_format($b['total_price'], 0); ?></div>
<?php
        $is_cancelled = (strpos($status, 'cancel') !== false);
        $badge_color = $is_cancelled ? '#ef4444' : (strpos($status, 'confirmed') !== false ? '#10b981' : (strpos($status, 'check') !== false ? '#3b82f6' : '#6b7280'));
        $icon = $is_cancelled ? '❌' : (strpos($status, 'confirmed') !== false ? '✅' : '🏨');
?>
                            <span style="font-size: 0.65rem; padding: 6px 14px; background: <?php echo $badge_color; ?>; border-radius: 30px; font-weight: 800; text-transform: uppercase; color: #fff; display: inline-flex; align-items: center; gap: 4px;">
                                <?php echo $icon; ?> <?php echo $b['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 20px; display: flex; gap: 10px;">
                            <button onclick='openEditBooking(<?php echo json_encode($b); ?>)' class="btn-premium" style="padding: 10px 20px; font-size: 0.7rem; background: #3498db; border-color: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.2);">Edit</button>
                            <?php if ($b['status'] == 'Cancelled'): ?>
                                <form method="POST" onsubmit="return confirm('Delete this record permanently?')">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <button type="submit" name="delete_booking" class="btn-premium" style="padding: 10px 15px; font-size: 0.7rem; background: #e74c3c; border-color: #e74c3c; font-weight: 800;">🗑️</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
    endforeach;
endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</main>

<!-- Modal: Inventory Manager (Integrated) -->
<div id="inventoryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(10px);">
    <div style="background: white; width: 95%; max-width: 1100px; height: 90vh; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; position: relative;">
        <button onclick="document.getElementById('inventoryModal').style.display='none'" style="position: absolute; top: 25px; right: 25px; background: #eee; border: none; width: 40px; height: 40px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; z-index: 10;">✕</button>
        
        <div style="padding: 40px; border-bottom: 2px solid #f9f9f9; display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
            <div>
                <h2 style="font-size: 2rem; color: var(--primary-color);">Room Inventory</h2>
                <p style="color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px;">Manage Suites & Galleries</p>
            </div>
            <button onclick="document.getElementById('addRoomModal').style.display='flex'" class="btn-premium" style="padding: 12px 30px;">Add New Suite</button>
        </div>

        <div style="flex: 1; overflow-y: auto; padding: 40px; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; background: white;">
            <?php
$rooms_stmt = $pdo->query("SELECT * FROM rooms");
while ($r = $rooms_stmt->fetch()):
    $gallery_stmt = $pdo->prepare("SELECT * FROM room_images WHERE room_id = ?");
    $gallery_stmt->execute([$r['id']]);
    $gallery = $gallery_stmt->fetchAll();
?>
            <div style="border: 1px solid #eee; padding: 25px; border-radius: 10px; background: white; transition: all 0.3s;" onmouseover="this.style.borderColor='var(--accent-color)'" onmouseout="this.style.borderColor='#eee'">
                <div style="height: 180px; background-image: url('images/rooms/<?php echo $r['image']; ?>'); background-size: cover; background-position: center; border-radius: 6px; margin-bottom: 20px; border: 1px solid #eee;"></div>
                <h4 style="font-size: 1.3rem; margin-bottom: 5px;"><?php echo $r['name']; ?></h4>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <small style="color: var(--accent-color); font-weight: 800; text-transform: uppercase;"><?php echo $r['type']; ?></small>
                    <span style="font-weight: 700; color: #2ecc71;">₹<?php echo number_format($r['price'] * 80, 0); ?><small style="font-weight: 300; font-size: 0.7rem; color: #999;">/night</small></span>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button onclick='openGalleryManager("<?php echo $r['id']; ?>", <?php echo json_encode($gallery); ?>)' class="btn-premium" style="flex: 1; font-size: 0.7rem; padding: 12px; background: #eee; color: #444; border-color: #ddd;">Manage Gallery</button>
                    <form method="POST" style="flex: 0 0 auto;" onsubmit="return confirm('Ensure no active bookings exist for this room.')">
                        <input type="hidden" name="room_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="delete_room" class="btn-premium" style="background: #e74c3c; border-color: #e74c3c; padding: 12px 18px;">🗑️</button>
                    </form>
                </div>
            </div>
            <?php
endwhile; ?>
        </div>
    </div>
</div>

<!-- Modal: Gallery Manager -->
<div id="galleryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(10px);">
    <div style="background: white; padding: 40px; width: 95%; max-width: 800px; border-radius: 12px; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; border-bottom: 2px solid #f9f9f9; padding-bottom: 15px;">
            <h3 style="font-size: 1.8rem; color: var(--primary-color);">Room Gallery Manager</h3>
            <button onclick="document.getElementById('galleryModal').style.display='none'" style="background:none; border:none; font-size:1.8rem; cursor:pointer; color: #ccc;">✕</button>
        </div>

        <form method="POST" enctype="multipart/form-data" style="background: #fdfdfd; padding: 30px; border: 2px dashed #eee; border-radius: 8px; margin-bottom: 35px; text-align: center;">
            <input type="hidden" name="room_id" id="galleryRoomId">
            <p style="margin-bottom: 15px; font-weight: 700; color: #555;">Add New Angle to Room</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <input type="file" name="gallery_image" class="form-input" required accept=".pdf,.jpg,.jpeg" style="max-width: 300px; background: white;">
                <button type="submit" name="upload_gallery_image" class="btn-premium" style="padding: 12px 30px;">Upload Now</button>
            </div>
        </form>

        <div id="galleryGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
            <!-- Gallery images injected here -->
        </div>
    </div>
</div>

<!-- Modal: Edit Booking -->
<div id="editBookingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(5px);">
    <div style="background: white; padding: 50px; width: 100%; max-width: 550px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
        <h3 style="font-size: 2rem; color: var(--primary-color); margin-bottom: 10px;">Refine Reservation</h3>
        <p style="color: #888; margin-bottom: 40px; font-size: 0.9rem;">Adjust stay details or status professionally.</p>
        
        <form method="POST">
            <input type="hidden" name="booking_id" id="editBookingId">
            
            <div style="margin-bottom: 25px;">
                <label style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #777; display: block; margin-bottom: 10px;">Assigned Suite</label>
                <select name="room_id" id="editRoomId" class="form-input" style="height: 55px; border-radius: 6px; font-weight: 600;">
                    <?php
$all_rooms = $pdo->query("SELECT id, name FROM rooms")->fetchAll();
foreach ($all_rooms as $rm)
    echo "<option value='{$rm['id']}'>{$rm['name']}</option>";
?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div>
                    <label style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #777; display: block; margin-bottom: 10px;">Check-In</label>
                    <input type="date" name="check_in" id="editCheckIn" class="form-input" style="height: 55px; font-weight: 600;" required>
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #777; display: block; margin-bottom: 10px;">Check-Out</label>
                    <input type="date" name="check_out" id="editCheckOut" class="form-input" style="height: 55px; font-weight: 600;" required>
                </div>
            </div>

            <div style="margin-bottom: 40px;">
                <label style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #777; display: block; margin-bottom: 10px;">Booking Status</label>
                <select name="status" id="editBookingStatus" class="form-input" style="height: 55px; border-radius: 6px; font-weight: 700; color: var(--primary-color);">
                    <option value="Confirmed">Confirmed</option>
                    <option value="Checked In">Checked In</option>
                    <option value="Checked Out">Checked Out</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div style="display: flex; gap: 20px;">
                <button type="submit" name="update_booking" class="btn-premium" style="flex: 1.5; padding: 20px; font-weight: 700;">Save Refinements</button>
                <button type="button" onclick="document.getElementById('editBookingModal').style.display='none'" class="btn-premium" style="flex: 1; background: #eee; border-color: #ddd; color: #666; padding: 20px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Room -->
<div id="addRoomModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10001; justify-content: center; align-items: center; backdrop-filter: blur(10px);">
    <div style="background: white; padding: 50px; width: 100%; max-width: 600px; border-radius: 12px; box-shadow: 0 30px 90px rgba(0,0,0,0.4);">
        <h3 style="font-size: 2rem; color: var(--primary-color); margin-bottom: 30px; border-bottom: 2px solid #f9f9f9; padding-bottom: 15px;">Create Luxury Suite</h3>
        <form method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <input type="text" name="name" placeholder="Suite Name" class="form-input" required style="height: 55px;">
                <input type="text" name="type" placeholder="Suite Type (e.g. Deluxe)" class="form-input" required style="height: 55px;">
            </div>
            <input type="number" name="price" placeholder="Price Per Night (USD)" class="form-input" required style="height: 55px; margin-bottom: 20px;">
            <textarea name="description" placeholder="Compelling Description..." class="form-input" style="height: 120px; margin-bottom: 25px; padding: 20px;" required></textarea>
            
            <div style="margin-bottom: 35px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.8rem; font-weight: 700; color: #666; text-transform: uppercase;">Hero Image</label>
                <input type="file" name="image" class="form-input" required accept=".pdf,.jpg,.jpeg" style="padding: 10px; background: white; border: none;">
            </div>

            <div style="display: flex; gap: 20px;">
                <button type="submit" name="add_room" class="btn-premium" style="flex: 2; padding: 20px; font-weight: 700;">Create Suite</button>
                <button type="button" onclick="document.getElementById('addRoomModal').style.display='none'" class="btn-premium" style="flex: 1; background: #eee; border-color: #ddd; color: #666; padding: 20px;">Discard</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGalleryManager(roomId, images) {
    document.getElementById('galleryRoomId').value = roomId;
    const grid = document.getElementById('galleryGrid');
    grid.innerHTML = '';
    
    images.forEach(img => {
        const div = document.createElement('div');
        div.style.position = 'relative';
        div.style.height = '140px';
        div.innerHTML = `
            <img src="images/rooms/${img.image_path}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
            <form method="POST" style="position: absolute; top: -8px; right: -8px;">
                <input type="hidden" name="image_id" value="${img.id}">
                <button type="submit" name="delete_gallery_image" style="background: #e74c3c; color: white; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">✕</button>
            </form>
        `;
        grid.appendChild(div);
    });
    
    document.getElementById('galleryModal').style.display = 'flex';
}

function openEditBooking(b) {
    document.getElementById('editBookingId').value = b.id;
    document.getElementById('editRoomId').value = b.room_id;
    document.getElementById('editCheckIn').value = b.check_in;
    document.getElementById('editCheckOut').value = b.check_out;
    document.getElementById('editBookingStatus').value = b.status;
    document.getElementById('editBookingModal').style.display = 'flex';
}

// Close modals on background click
window.onclick = function(event) {
    const modals = [document.getElementById('inventoryModal'), document.getElementById('galleryModal'), document.getElementById('editBookingModal'), document.getElementById('addRoomModal')];
    modals.forEach(modal => {
        if (event.target == modal) modal.style.display = "none";
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
