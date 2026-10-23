
<?php
require_once 'db.php';
require_login();

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total,
            COALESCE(MAX(weight_kg), 0) AS max_weight,
            COALESCE(SUM(sets_completed), 0) AS total_sets
     FROM workout_history WHERE user_id = ?'
);
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare(
    'SELECT workout_date, MAX(weight_kg) AS max_weight
     FROM workout_history
     WHERE user_id = ?
     GROUP BY workout_date
     ORDER BY workout_date ASC
     LIMIT 30'
);
$stmt->execute([$userId]);
$chartRows = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT * FROM workout_history WHERE user_id = ?
     ORDER BY workout_date DESC, id DESC'
);
$stmt->execute([$userId]);
$records = $stmt->fetchAll();

$chartLabels = array_column($chartRows, 'workout_date');
$chartWeights = array_map(
    'floatval',
    array_column($chartRows, 'max_weight')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Progress | ArmForce</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard.php">ARM<span>FORCE</span></a>
    <button class="menu-toggle" id="menuToggle" type="button">☰ Menu</button>
    <nav class="nav-links" id="navLinks">
        <a href="dashboard.php">Dashboard</a>
        <a href="workouts.php">Workout Programs</a>
        <a href="workout_tracker.php">Workout Tracker</a>
        <a class="active" href="progress.php">My Progress</a>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button class="btn btn-outline" type="submit">Logout</button>
        </form>
    </nav>
</header>

<main class="container">
    <section class="page-intro">
        <p class="eyebrow">MEASURE YOUR TRAINING</p>
        <h1>My Progress</h1>
        <p>Review your saved training records and recorded loads.</p>
    </section>

    <section class="stats-grid">
        <article class="stat-card">
            <p>Exercise Entries</p>
            <h2><?= (int)$stats['total'] ?></h2>
        </article>
        <article class="stat-card">
            <p>Total Sets Recorded</p>
            <h2><?= (int)$stats['total_sets'] ?></h2>
        </article>
        <article class="stat-card">
            <p>Highest Recorded Load</p>
            <h2><?= e($stats['max_weight']) ?> kg</h2>
        </article>
    </section>

    <section class="section chart-panel">
        <h2>Highest load recorded per day</h2>
        <p class="muted">This chart shows the highest weight among all exercises recorded on each date, not a single exercise's strength trend.</p>
        <?php if ($chartRows): ?>
            <canvas id="progressChart" aria-label="Highest recorded load by date"></canvas>
        <?php else: ?>
            <p>No progress data yet. Save some exercises in the workout tracker first.</p>
        <?php endif; ?>
    </section>

    <section class="section">
        <h2>All workout records</h2>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Exercise</th><th>Sets</th><th>Reps</th><th>Weight</th><th>Notes</th></tr></thead>
            <tbody>
            <?php if (!$records): ?>
                <tr><td colspan="6">No records available.</td></tr>
            <?php else: foreach ($records as $r): ?>
                <tr>
                    <td><?= e($r['workout_date']) ?></td>
                    <td><?= e($r['exercise_name']) ?></td>
                    <td><?= (int)$r['sets_completed'] ?></td>
                    <td><?= (int)$r['reps_completed'] ?></td>
                    <td><?= e($r['weight_kg']) ?> kg</td>
                    <td><?= e($r['notes'] ?? '') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</main>

<script src="script.js"></script>
<script>
const labels = <?= json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const weights = <?= json_encode($chartWeights) ?>;
const canvas = document.getElementById('progressChart');

if (canvas && labels.length) {
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Highest recorded load (kg)',
                data: weights,
                borderColor: '#2459df',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Weight (kg)' } },
                x: { title: { display: true, text: 'Workout date' } }
            }
        }
    });
}
</script>
</body>
</html>
