
<?php
require_once 'db.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $exercise = trim($_POST['exercise_name'] ?? '');
    $sets = filter_var($_POST['sets_completed'] ?? null, FILTER_VALIDATE_INT);
    $reps = filter_var($_POST['reps_completed'] ?? null, FILTER_VALIDATE_INT);
    $weight = filter_var($_POST['weight_kg'] ?? null, FILTER_VALIDATE_FLOAT);
    $date = $_POST['workout_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    $validDate = $parsedDate && $parsedDate->format('Y-m-d') === $date;

    if ($exercise === '' || strlen($exercise) > 120 ||
        $sets === false || $sets < 1 || $sets > 100 ||
        $reps === false || $reps < 1 || $reps > 1000 ||
        $weight === false || $weight < 0 || $weight > 10000 ||
        !$validDate || $date > date('Y-m-d') || strlen($notes) > 255) {
        $error = 'Enter valid exercise details. Dates cannot be in the future.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO workout_history
            (user_id, exercise_name, sets_completed, reps_completed,
             weight_kg, notes, workout_date)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            (int)$_SESSION['user_id'],
            $exercise,
            $sets,
            $reps,
            $weight,
            $notes !== '' ? $notes : null,
            $date
        ]);

        $success = 'Exercise record saved successfully.';
    }
}

$stmt = $pdo->prepare(
    'SELECT * FROM workout_history WHERE user_id = ?
     ORDER BY workout_date DESC, id DESC LIMIT 10'
);
$stmt->execute([(int)$_SESSION['user_id']]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Workout Tracker | ArmForce</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard.php">ARM<span>FORCE</span></a>
    <button class="menu-toggle" id="menuToggle" type="button">☰ Menu</button>
    <nav class="nav-links" id="navLinks">
        <a href="dashboard.php">Dashboard</a>
        <a href="workouts.php">Workout Programs</a>
        <a class="active" href="workout_tracker.php">Workout Tracker</a>
        <a href="progress.php">My Progress</a>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button class="btn btn-outline" type="submit">Logout</button>
        </form>
    </nav>
</header>

<main class="container">
    <section class="page-intro">
        <p class="eyebrow">LOG YOUR TRAINING</p>
        <h1>Workout Tracker</h1>
        <p>Record your exercise performance after each session.</p>
    </section>

    <section class="form-panel">
        <h2>Record an exercise</h2>

        <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?= e($success) ?></div><?php endif; ?>

        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="field full-field">
                <label for="exercise_name">Exercise name</label>
                <input id="exercise_name" name="exercise_name" list="exerciseOptions"
                       maxlength="120" required>
                <datalist id="exerciseOptions">
                    <option value="Wrist Curls">
                    <option value="Cable Pronation">
                    <option value="Supination">
                    <option value="Wrist Cupping">
                    <option value="Rising Exercise">
                    <option value="Back Pressure Row">
                    <option value="Hammer Curls">
                    <option value="Grip Holds">
                    <option value="Finger Extensions">
                    <option value="Recovery and Mobility">
                </datalist>
            </div>

            <div class="field">
                <label for="sets_completed">Sets completed</label>
                <input id="sets_completed" type="number" name="sets_completed"
                       min="1" max="100" value="3" required>
            </div>

            <div class="field">
                <label for="reps_completed">Reps per set</label>
                <input id="reps_completed" type="number" name="reps_completed"
                       min="1" max="1000" value="10" required>
            </div>

            <div class="field">
                <label for="weight_kg">Weight (kg)</label>
                <input id="weight_kg" type="number" name="weight_kg"
                       min="0" max="10000" step="0.25" value="0" required>
            </div>

            <div class="field">
                <label for="workout_date">Workout date</label>
                <input id="workout_date" type="date" name="workout_date"
                       max="<?= e(date('Y-m-d')) ?>"
                       value="<?= e(date('Y-m-d')) ?>" required>
            </div>

            <div class="field full-field">
                <label for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" maxlength="255"
                          placeholder="How did the exercise feel?"></textarea>
            </div>

            <div class="full-field">
                <button class="btn btn-primary" type="submit">Save Exercise Record</button>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Recent records</h2>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Exercise</th><th>Sets</th><th>Reps</th><th>Load</th><th>Date</th><th>Notes</th></tr></thead>
            <tbody>
            <?php if (!$history): ?>
                <tr><td colspan="6">No workout records yet.</td></tr>
            <?php else: foreach ($history as $r): ?>
                <tr>
                    <td><?= e($r['exercise_name']) ?></td>
                    <td><?= (int)$r['sets_completed'] ?></td>
                    <td><?= (int)$r['reps_completed'] ?></td>
                    <td><?= e($r['weight_kg']) ?> kg</td>
                    <td><?= e($r['workout_date']) ?></td>
                    <td><?= e($r['notes'] ?? '') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</main>
<footer class="footer">ARMFORCE · Train smart. Recover well.</footer>
<script src="script.js"></script>
</body>
</html>
