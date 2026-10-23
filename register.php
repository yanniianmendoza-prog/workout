
<?php
require_once 'db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$full_name = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (
        $full_name === '' || $username === '' || $email === '' ||
        $password === '' || $confirm === ''
    ) {
        $error = 'Please complete all fields.';
    } elseif (strlen($full_name) > 100 ||
              !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username) ||
              !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Check your name, username, and email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE username = ? OR email = ?'
        );
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Username or email is already registered.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, username, email, password)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $full_name,
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Account | Armwrestling</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <div class="brand">ARM<span>FORCE</span></div>
    <p class="eyebrow">START YOUR JOURNEY</p>
    <h1>Create account</h1>
    <p class="muted">Build strength. Track progress.</p>

    <?php if ($error): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Full name</label>
        <input name="full_name" maxlength="100" required
               value="<?= e($full_name) ?>" autocomplete="name">

        <label>Username</label>
        <input name="username" maxlength="50" required
               value="<?= e($username) ?>" autocomplete="username">

        <label>Email address</label>
        <input type="email" name="email" maxlength="150" required
               value="<?= e($email) ?>" autocomplete="email">

        <label>Password (minimum 8 characters)</label>
        <input type="password" name="password" minlength="8" required
               autocomplete="new-password">

        <label>Confirm password</label>
        <input type="password" name="confirm_password" minlength="8" required
               autocomplete="new-password">

        <button class="btn btn-primary full" type="submit">Create Account</button>
    </form>

    <p class="auth-footer">Already registered? <a href="login.php">Log in</a></p>
</main>
</body>
</html>
