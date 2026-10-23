<?php
require_once 'db.php';
require_login();

$userId = (int)$_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare(
    'SELECT id, name, email, username, created_at
     FROM users WHERE id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $username = trim($_POST['username'] ?? '');

        if (!preg_match("/^[\p{L}\p{M} .'-]{2,100}$/u", $name)) {
            $error = 'Enter a valid name.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)
                  || strlen($email) > 150) {
            $error = 'Enter a valid email address.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
            $error = 'Username must be 3–50 letters, numbers, or underscores.';
        } else {
            $stmt = $pdo->prepare(
                'SELECT id FROM users
                 WHERE (email = ? OR username = ?) AND id != ? LIMIT 1'
            );
            $stmt->execute([$email, $username, $userId]);

            if ($stmt->fetch()) {
                $error = 'Email or username is already in use.';
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE users SET name = ?, email = ?, username = ?
                     WHERE id = ?'
                );
                $stmt->execute([$name, $email, $username, $userId]);
                $success = 'Profile updated successfully.';
            }
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $account = $stmt->fetch();

        if (!$account || !password_verify($current, $account['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must have at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users SET password = ? WHERE id = ?'
            );
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);

            session_regenerate_id(true);
            $success = 'Password changed successfully.';
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, username, created_at
             FROM users WHERE id = ?'
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Profile | FitTrack</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#101216;color:#f5f5f5;font-family:Arial,sans-serif}
header{padding:20px 5%;background:#1b1e24;border-bottom:1px solid #343943;
display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap}
.logo{font-size:24px;font-weight:900;color:white;text-decoration:none}
.logo span{color:#ff7a18}
nav{display:flex;gap:18px;flex-wrap:wrap}
a{color:#ff9b50;text-decoration:none}
main{width:min(800px,94%);margin:30px auto}
.panel{background:#1b1e24;border:1px solid #343943;border-radius:12px;
padding:24px;margin-bottom:22px}
label{display:block;margin:15px 0 7px;font-size:14px}
input{width:100%;padding:12px;background:#252932;color:white;
border:1px solid #343943;border-radius:7px}
button{margin-top:20px;padding:12px 16px;border:0;border-radius:7px;
background:#ff7a18;font-weight:bold;cursor:pointer}
.muted{color:#a5aab4}
.alert,.success{padding:12px;border-radius:7px;margin:15px 0}
.alert{background:#532628;color:#ffd9d9}
.success{background:#183d2c;color:#c9ffdc}
</style>
</head>
<body>
<header>
    <a class="logo" href="index.php">FIT<span>TRACK</span></a>
    <nav>
        <a href="index.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1>My Profile</h1>
    <p class="muted">Manage your account information and password.</p>

    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= e($success) ?></div><?php endif; ?>

    <section class="panel">
        <h2>Account Information</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_profile">

            <label>Full Name</label>
            <input name="name" maxlength="100" value="<?= e($user['name']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" maxlength="150"
                   value="<?= e($user['email']) ?>" required>

            <label>Username</label>
            <input name="username" minlength="3" maxlength="50"
                   value="<?= e($user['username']) ?>" required>

            <label>Member Since</label>
            <input value="<?= e(date('F j, Y', strtotime($user['created_at']))) ?>" disabled>

            <button type="submit">Save Profile</button>
        </form>
    </section>

    <section class="panel">
        <h2>Change Password</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="change_password">

            <label>Current Password</label>
            <input type="password" name="current_password"
                   autocomplete="current-password" required>

            <label>New Password</label>
            <input type="password" name="new_password" minlength="8"
                   autocomplete="new-password" required>

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" minlength="8"
                   autocomplete="new-password" required>

            <button type="submit">Change Password</button>
        </form>
    </section>
</main>
</body>
</html>