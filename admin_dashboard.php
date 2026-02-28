<?php
require_once 'config/database.php';
session_start();

// Admin Protection
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Room Management Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_room'])) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $image = $_POST['image'];

        $stmt = $pdo->prepare("INSERT INTO rooms (name, type, price, description, image, status) VALUES (?, ?, ?, ?, ?, 'Available')");
        $stmt->execute([$name, $type, $price, $description, $image]);
        header("Location: admin_dashboard.php#rooms");
        exit();
    }

    if (isset($_POST['delete_room'])) {
        $id = $_POST['room_id'];
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin_dashboard.php#rooms");
        exit();
    }

    if (isset($_POST['update_price'])) {
        $id = $_POST['room_id'];
        $price = $_POST['price'];
        $stmt = $pdo->prepare("UPDATE rooms SET price = ? WHERE id = ?");
        $stmt->execute([$price, $id]);
        header("Location: admin_dashboard.php#rooms");
        exit();
    }
}

// Download Report Logic
if (isset($_GET['action']) && $_GET['action'] == 'download') {
    $stmt = $pdo->query("SELECT b.id, b.guest_name, b.guest_age, r.name as room_name, b.num_rooms, b.num_adults, b.num_children, b.check_in, b.check_out, b.total_price, b.status, b.created_at 
                         FROM bookings b 
                         JOIN rooms r ON b.room_id = r.id 
                         ORDER BY b.created_at DESC");
    $bookings = $stmt->fetchAll();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="grand_vista_bookings_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Booking ID', 'Guest Name', 'Age', 'Room', 'No. Rooms', 'Adults', 'Children', 'Check-In', 'Check-Out', 'Total Price (₹)', 'Status', 'Booked On']);

    foreach ($bookings as $booking) {
        fputcsv($output, $booking);
    }
    fclose($output);
    exit();
}

require_once 'includes/header.php';
?>

<main style="padding-top: 150px; padding-bottom: 150px; min-height: 100vh; background: #fafafa;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 40px;">
        <header style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 60px;">
            <div>
                <h1 style="font-size: 3.5rem; color: var(--primary-color);">Admin Oversight</h1>
                <p style="color: #888; margin-top: 10px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; font-size: 0.8rem;">Master Management System</p>
            </div>
            <div style="display: flex; gap: 20px;">
                <a href="#rooms" class="btn-premium" style="background: transparent; border-color: var(--primary-color); color: var(--primary-color);">Manage Rooms</a>
                <a href="admin_dashboard.php?action=download" class="btn-premium" style="background: var(--primary-color); border-color: var(--primary-color);">Download Bookings</a>
            </div>
        </header>

        <section style="background: white; padding: 40px; border-radius: 4px; box-shadow: var(--shadow); overflow-x: auto; margin-bottom: 80px;">
            <h3 style="margin-bottom: 35px; color: var(--text-dark); border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; display: inline-block;">All Guest Reservations</h3>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; min-width: 1000px;">
                <thead>
                    <tr style="text-align: left; background: #f9f9f9; color: var(--primary-color); text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px;">
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Guest Details</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Room & Capacity</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Stay Dates</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Total Revenue</th>
                        <th style="padding: 20px; border-bottom: 2px solid #eee;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$stmt = $pdo->query("SELECT b.*, r.name as room_name 
                                         FROM bookings b 
                                         JOIN rooms r ON b.room_id = r.id 
                                         ORDER BY b.created_at DESC");
$bookings = $stmt->fetchAll();

if (empty($bookings)):
?>
                        <tr>
                            <td colspan="5" style="padding: 50px; text-align: center; color: #ccc;">No bookings found in the system.</td>
                        </tr>
                    <?php
else: ?>
                        <?php foreach ($bookings as $booking): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0; transition: background 0.3s;">
                            <td style="padding: 20px;">
                                <div style="font-weight: 700; color: var(--text-dark);"><?php echo htmlspecialchars($booking['guest_name']); ?></div>
                                <div style="font-size: 0.8rem; color: #999;">Age: <?php echo $booking['guest_age']; ?></div>
                            </td>
                            <td style="padding: 20px;">
                                <div style="font-weight: 600;"><?php echo $booking['room_name']; ?></div>
                                <div style="font-size: 0.75rem; color: #888;">Rooms: <?php echo $booking['num_rooms']; ?> | Adults: <?php echo $booking['num_adults']; ?> | Kids: <?php echo $booking['num_children']; ?></div>
                            </td>
                            <td style="padding: 20px; font-size: 0.9rem; color: #555;">
                                <?php echo date('d M', strtotime($booking['check_in'])); ?> - <?php echo date('d M, Y', strtotime($booking['check_out'])); ?>
                            </td>
                            <td style="padding: 20px; font-weight: 700; color: #2ecc71;">₹<?php echo number_format($booking['total_price'], 0); ?></td>
                            <td style="padding: 20px;">
                                <span style="font-size: 0.65rem; padding: 5px 12px; background: #e8f5e9; color: #2e7d32; font-weight: 800; border-radius: 20px; text-transform: uppercase;"><?php echo $booking['status']; ?></span>
                            </td>
                        </tr>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </tbody>
            </table>
        </section>

        <section id="rooms" style="background: white; padding: 40px; border-radius: 4px; box-shadow: var(--shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="color: var(--text-dark); margin: 0;">Inventory Management</h3>
                <button onclick="document.getElementById('addRoomModal').style.display='flex'" class="btn-premium" style="padding: 10px 25px; font-size: 0.75rem;">Add New Suite</button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
                <?php
$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll();
foreach ($rooms as $r):
?>
                <div style="border: 1px solid #eee; padding: 20px; border-radius: 4px; position: relative;">
                    <div style="height: 150px; background-image: url('images/rooms/<?php echo $r['image']; ?>'); background-size: cover; background-position: center; border-radius: 2px; margin-bottom: 15px;"></div>
                    <h4 style="margin-bottom: 5px;"><?php echo $r['name']; ?></h4>
                    <p style="font-size: 0.75rem; color: #888; margin-bottom: 15px;"><?php echo $r['type']; ?></p>
                    
                    <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                        <input type="hidden" name="room_id" value="<?php echo $r['id']; ?>">
                        <div style="flex: 1;">
                            <label style="font-size: 0.6rem; text-transform: uppercase;">Price (₹)</label>
                            <input type="number" name="price" value="<?php echo $r['price']; ?>" class="form-input" style="padding: 5px; margin: 0; font-size: 0.8rem;">
                        </div>
                        <button type="submit" name="update_price" class="btn-premium" style="padding: 10px; font-size: 0.6rem; background: #3498db; border-color: #3498db;">Update</button>
                        <button type="submit" name="delete_room" class="btn-premium" style="padding: 10px; font-size: 0.6rem; background: #e74c3c; border-color: #e74c3c;" onclick="return confirm('Ensure no active bookings exist for this room.')">Delete</button>
                    </form>
                </div>
                <?php
endforeach; ?>
            </div>
        </section>
    </div>
</main>

<div id="addRoomModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 40px; width: 100%; max-width: 500px; border-radius: 4px;">
        <h3 style="margin-bottom: 30px;">Add New Luxury Suite</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Room Name" class="form-input" required>
            <input type="text" name="type" placeholder="Room Type" class="form-input" required>
            <input type="number" name="price" placeholder="Price" class="form-input" required>
            <textarea name="description" placeholder="Description" class="form-input" style="height: 100px;" required></textarea>
            <input type="text" name="image" placeholder="Image Filename" class="form-input" required>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <button type="submit" name="add_room" class="btn-premium" style="flex: 1;">Create Room</button>
                <button type="button" onclick="document.getElementById('addRoomModal').style.display='none'" class="btn-premium" style="flex: 1; background: #777; border-color: #777;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
