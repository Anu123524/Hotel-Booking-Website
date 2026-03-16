<?php
require_once 'config/database.php';
session_start();

// Guest access enabled - no longer redirecting to login

require_once 'includes/header.php';

?>

<main>
    <section class="hero">
        <h1>Grand Vista Sanctuary</h1>
        <p>Experience the epitome of luxury tailored just for you. From serene suites to world-class service, discover your heaven in our haven.</p>
        <a href="rooms.php" class="btn-premium">View Our Suites</a>
    </section>

    <section style="background: var(--white); padding: 120px 10%; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center;" class="reveal">
        <div>
            <img src="images/hero.jpg" alt="Luxury Interior" style="width: 100%; border-radius: 4px; box-shadow: var(--shadow);">
        </div>
        <div>
            <h2 style="font-size: 2.8rem; color: var(--primary-color); margin-bottom: 25px;">The Gold Standard</h2>
            <p style="margin-bottom: 35px; font-size: 1.1rem; color: #555; font-weight: 300;">We redefine the boundaries of luxury, creating an atmosphere that is both grand and intimate. Our legacy is built on the memories of our guests.</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="padding: 20px; border: 1px solid #f0f0f0;">
                    <h4 style="color: var(--accent-color); margin-bottom: 5px;">24hr Butler</h4>
                    <p style="font-size: 0.8rem; color: #888;">Dedicated to your comfort at all times.</p>
                </div>
                <div style="padding: 20px; border: 1px solid #f0f0f0;">
                    <h4 style="color: var(--accent-color); margin-bottom: 5px;">Sea View</h4>
                    <p style="font-size: 0.8rem; color: #888;">Panoramic views of the infinite blue.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    const reveals = document.querySelectorAll('.reveal');
    const options = { threshold: 0.15 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, options);
    reveals.forEach(reveal => observer.observe(reveal));
</script>

<?php require_once 'includes/footer.php'; ?>
