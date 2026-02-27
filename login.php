<?php
require_once 'config/database.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);
        $success = "Registration successful! You can now login.";
    }
    catch (PDOException $e) {
        $error = "Email already exists or database error.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: index.php");
        exit();
    }
    else {
        $error = "Invalid email or password.";
    }
}

require_once 'includes/header.php';
?>

<div style="padding-top: 150px; min-height: 80vh; display: flex; justify-content: center; align-items: center; background: #f4f7f6;">
    <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow); width: 100%; max-width: 450px;">
        <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;"><?php echo isset($_GET['type']) && $_GET['type'] == 'register' ? 'Join Grand Vista' : 'Welcome Back'; ?></h2>
        
        <?php if ($error): ?>
            <p style="color: var(--secondary-color); text-align: center; margin-bottom: 20px; font-weight: 500;"><?php echo $error; ?></p>
        <?php
endif; ?>
        <?php if ($success): ?>
            <p style="color: green; text-align: center; margin-bottom: 20px; font-weight: 500;"><?php echo $success; ?></p>
        <?php
endif; ?>

        <?php if (isset($_GET['type']) && $_GET['type'] == 'register'): ?>
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <input type="email" name="email" placeholder="Email Address" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 12px; margin-bottom: 30px; border: 1px solid #ddd; border-radius: 8px;">
                <button type="submit" name="register" class="btn-premium" style="width: 100%; border: none; cursor: pointer;">Register Now</button>
                <p style="text-align: center; margin-top: 20px; font-size: 0.9rem;">Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login</a></p>
            </form>
        <?php
else: ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email Address" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 12px; margin-bottom: 30px; border: 1px solid #ddd; border-radius: 8px;">
                <button type="submit" name="login" class="btn-premium" style="width: 100%; border: none; cursor: pointer;">Secure Login</button>
                <p style="text-align: center; margin-top: 20px; font-size: 0.9rem;">Don't have an account? <a href="login.php?type=register" style="color: var(--primary-color); font-weight: 600;">Register</a></p>
            </form>
        <?php
endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
