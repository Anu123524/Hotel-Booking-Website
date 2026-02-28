<?php
require_once 'config/database.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'Admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        header("Location: admin_dashboard.php");
        exit();
    }
    else {
        $error = "Access Denied: Invalid Admin Credentials.";
    }
}

require_once 'includes/header.php';
?>

<div style="padding-top: 150px; min-height: 80vh; display: flex; justify-content: center; align-items: center; background: #0a0a0a;">
    <div style="background: #1a1a1a; padding: 50px; border-radius: 4px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); width: 100%; max-width: 450px; border: 1px solid #333;">
        <h2 style="text-align: center; color: #fff; margin-bottom: 10px; font-family: 'Playfair Display', serif; font-size: 2rem;">Admin Security</h2>
        <p style="text-align: center; color: var(--accent-color); text-transform: uppercase; letter-spacing: 2px; font-size: 0.7rem; margin-bottom: 40px; font-weight: 700;">Grand Vista Management Portal</p>
        
        <?php if ($error): ?>
            <div style="background: rgba(231, 76, 60, 0.1); border-left: 3px solid #e74c3c; color: #e74c3c; padding: 15px; margin-bottom: 25px; font-size: 0.85rem;">
                <?php echo $error; ?>
            </div>
        <?php
endif; ?>

        <form method="POST">
            <div style="margin-bottom: 25px;">
                <label style="color: #888; display: block; margin-bottom: 8px; font-size: 0.8rem; text-transform: uppercase;">Admin Email</label>
                <input type="email" name="email" class="form-input" required style="background: #252525; border-color: #333; color: #fff;">
            </div>
            <div style="margin-bottom: 35px;">
                <label style="color: #888; display: block; margin-bottom: 8px; font-size: 0.8rem; text-transform: uppercase;">Secure Password</label>
                <input type="password" name="password" class="form-input" required style="background: #252525; border-color: #333; color: #fff;">
            </div>
            
            <button type="submit" name="login" class="btn-premium" style="width: 100%; padding: 18px; border: none; cursor: pointer; background: var(--accent-color); color: #000; font-weight: 700;">Authorize Access</button>
            
            <div style="margin-top: 30px; text-align: center; font-size: 0.75rem; color: #555;">
                <p>Protected by Grand Vista Security Systems</p>
            </div>
        </form>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
