<?php
require_once 'config/database.php';
session_start();

$guest_name = $_GET['guest'] ?? 'Valued Guest';

require_once 'includes/header.php';
?>

<div style="padding-top: 180px; padding-bottom: 120px; min-height: 90vh; background: #fff; text-align: center;">
    <div style="max-width: 700px; margin: 0 auto; padding: 40px;">
        <div style="margin-bottom: 40px;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="11" stroke="var(--accent-color)" stroke-width="1"/>
                <path d="M7 13L10 16L17 9" stroke="var(--accent-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <h1 style="font-family: 'Playfair Display', serif; font-size: 3rem; color: var(--primary-color); margin-bottom: 20px;">Reservation Confirmed</h1>
        <p style="font-size: 1.2rem; color: #666; font-weight: 300; line-height: 1.8; margin-bottom: 50px;">
            Thank you, <span style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($guest_name); ?></span>. <br>
            Your sanctuary is being prepared. We look forward to welcoming you to the extraordinary.
        </p>
        
        <div style="display: flex; gap: 20px; justify-content: center;">
            <a href="index.php" class="btn-premium" style="padding: 15px 40px;">Return Home</a>
            <a href="rooms.php" class="btn-premium" style="background: transparent; color: var(--primary-color); border-color: var(--primary-color); padding: 15px 40px;">Book Another Stay</a>
        </div>
        
        <p style="margin-top: 80px; font-size: 0.8rem; color: #999; text-transform: uppercase; letter-spacing: 2px;">A confirmation details will be available at the front desk upon arrival.</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
