<?php
require_once 'config/database.php';
session_start();
require_once 'includes/header.php';
?>

<header style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/hero.jpg'); background-size: cover; background-position: center; height: 400px; display: flex; align-items: center; justify-content: center; text-align: center;">
    <div class="reveal visible">
        <h1 style="color: white; font-size: 4.5rem; margin-bottom: 10px;">Contact Us</h1>
        <p style="color: var(--accent-color); text-transform: uppercase; letter-spacing: 5px; font-weight: 700;">Visit the Sanctuary</p>
    </div>
</header>

<main style="padding: 100px 10%; background: #fff;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: start;">
        <section class="reveal">
            <h2 style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 30px;">Get in Touch</h2>
            <p style="font-size: 1.1rem; color: #666; margin-bottom: 40px; font-weight: 300; line-height: 1.8;">Whether you are planning a stay or have a question about our services, our dedicated concierge team is here to assist you 24/7.</p>
            
            <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 50px; height: 50px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--accent-color);">
                    📍
                </div>
                <div>
                    <h4 style="margin-bottom: 5px;">Location</h4>
                    <p style="color: #888; font-size: 0.9rem;">Surathkal, Mangalore, Karnataka 575014</p>
                </div>
            </div>

            <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 50px; height: 50px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--accent-color);">
                    📞
                </div>
                <div>
                    <h4 style="margin-bottom: 5px;">Phone</h4>
                    <p style="color: #888; font-size: 0.9rem;">+91 94812 34567</p>
                </div>
            </div>

            <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 50px; height: 50px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--accent-color);">
                    ✉️
                </div>
                <div>
                    <h4 style="margin-bottom: 5px;">Email</h4>
                    <p style="color: #888; font-size: 0.9rem;">arjunshetty@gmail.com</p>
                </div>
            </div>
        </section>

        <section class="reveal">
            <div style="width: 100%; height: 450px; border-radius: 4px; overflow: hidden; box-shadow: var(--shadow); border: 1px solid #eee;">
                <!-- Embed Google Maps -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15545.92244247506!2d74.7816174!3d13.0039755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ba35a092b3f114f%3A0xc319196d4fb07842!2sSurathkal%2C%20Mangalore%2C%20Karnataka!5e0!3m2!1sen!2sin!4v1709216000000!5m2!1sen!2sin" 
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </section>
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
