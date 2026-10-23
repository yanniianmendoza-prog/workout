
<?php
require_once 'db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, full_name, password FROM users
         WHERE username = ? OR email = ? LIMIT 1'
    );
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Incorrect username/email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Armwrestling</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <div class="brand">ARM<span>FORCE</span></div>
    <p class="eyebrow">YOUR TRAINING STARTS HERE</p>
    <h1>Welcome back</h1>
    <p class="muted">Sign in to access your training dashboard.</p>

    <?php if (isset($_GET['registered'])): ?>
        <div class="success">Account created. You can now log in.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Username or email</label>
        <input name="identity" required autocomplete="username">

        <label>Password</label>
        <input type="password" name="password" required
               autocomplete="current-password">

        <button class="btn btn-primary full" type="submit">Log In</button>
    </form>

    <p class="auth-footer">New here? <a href="register.php">Create an account</a></p>
</main>
</body>
</html>
        