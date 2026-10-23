
<?php
require_once 'db.php';
require_login();

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT full_name, username, email FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$_SESSION['user_name'] = $user['full_name'];

$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total,
            COALESCE(MAX(weight_kg), 0) AS best_weight
     FROM workout_history WHERE user_id = ?'
);
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare(
    'SELECT exercise_name, sets_completed, reps_completed,
            weight_kg, workout_date
     FROM workout_history WHERE user_id = ?
     ORDER BY workout_date DESC, id DESC LIMIT 5'
);
$stmt->execute([$userId]);
$recent = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | ArmForce</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard.php">ARM<span>FORCE</span></a>
    <button class="menu-toggle" id="menuToggle" type="button">☰ Menu</button>
    <nav class="nav-links" id="navLinks">
        <a class="active" href="dashboard.php">Dashboard</a>
        <a href="workouts.php">Workout Programs</a>
        <a href="workout_tracker.php">Workout Tracker</a>
        <a href="progress.php">My Progress</a>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button class="btn btn-outline" type="submit">Logout</button>
        </form>
    </nav>
</header>

<main class="container">
    <section class="hero">
        <div>
            <p class="eyebrow">YOUR PERSONAL TRAINING SPACE</p>
            <h1>Welcome, <?= e($user['full_name']) ?>!</h1>
            <p>Train with purpose. Track every rep. Build your strength.</p>
            <a href="workouts.php" class="btn btn-yellow">Explore Workouts →</a>
        </div>
        <div class="hero-mark">AF</div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div>
                <p class="eyebrow">YOUR OVERVIEW</p>
                <h2>Training dashboard</h2>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card">
                <span class="stat-icon">↗</span>
                <p>Exercise Records</p>
                <h2><?= (int)$stats['total'] ?></h2>
                <small>Saved exercise entries</small>
            </article>
            <article class="stat-card">
                <span class="stat-icon">🏋</span>
                <p>Highest Recorded Load</p>
                <h2><?= e($stats['best_weight']) ?> kg</h2>
                <small>Across recorded exercises</small>
            </article>
            <article class="stat-card">
                <span class="stat-icon">✓</span>
                <p>Account</p>
                <h2>Active</h2>
                <small>@<?= e($user['username']) ?></small>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div>
                <p class="eyebrow">PICK YOUR FOCUS</p>
                <h2>Training categories</h2>
            </div>
            <a href="workouts.php">View programs →</a>
        </div>

        <div class="category-grid">
            <article class="category-card"><span>01</span><h3>Wrist Strength</h3><p>Wrist curls and controlled wrist flexion.</p></article>
            <article class="category-card"><span>02</span><h3>Pronation & Supination</h3><p>Train forearm rotation with resistance.</p></article>
            <article class="category-card"><span>03</span><h3>Cupping & Rising</h3><p>Develop wrist control and hand positioning.</p></article>
            <article class="category-card"><span>04</span><h3>Back Pressure</h3><p>Build pulling strength with controlled rows.</p></article>
            <article class="category-card"><span>05</span><h3>Grip & Fingers</h3><p>Practice manageable grip and finger work.</p></article>
            <article class="category-card"><span>06</span><h3>Recovery</h3><p>Prioritize mobility, rest, and recovery.</p></article>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div><p class="eyebrow">RECENT ACTIVITY</p><h2>Latest exercise records</h2></div>
            <a href="progress.php">View all →</a>
        </div>

        <div class="table-wrap">
        <table>
            <thead><tr><th>Exercise</th><th>Sets</th><th>Reps</th><th>Weight</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (!$recent): ?>
                <tr><td colspan="5">No records yet. Log your first workout!</td></tr>
            <?php else: foreach ($recent as $r): ?>
                <tr>
                    <td><?= e($r['exercise_name']) ?></td>
                    <td><?= (int)$r['sets_completed'] ?></td>
                    <td><?= (int)$r['reps_completed'] ?></td>
                    <td><?= e($r['weight_kg']) ?> kg</td>
                    <td><?= e($r['workout_date']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
        <a class="btn btn-primary" href="workout_tracker.php">+ Record Exercise</a>
    </section>
</main>
<footer class="footer">ARMFORCE · Train smart. Recover well.</footer>
<script src="script.js"></script>
</body>
</html>
