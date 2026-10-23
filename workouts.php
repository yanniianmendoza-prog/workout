
<?php
require_once 'db.php';
require_login();

$programs = $pdo->query(
    'SELECT * FROM workout_programs
     ORDER BY FIELD(level, "Beginner", "Intermediate", "Advanced"), id'
)->fetchAll();

$stmt = $pdo->query(
    'SELECT * FROM workout_exercises ORDER BY program_id, id'
);
$allExercises = $stmt->fetchAll();

$exercises = [];
foreach ($allExercises as $exercise) {
    $exercises[$exercise['program_id']][] = $exercise;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Workout Programs | ArmForce</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard.php">ARM<span>FORCE</span></a>
    <button class="menu-toggle" id="menuToggle" type="button">☰ Menu</button>
    <nav class="nav-links" id="navLinks">
        <a href="dashboard.php">Dashboard</a>
        <a class="active" href="workouts.php">Workout Programs</a>
        <a href="workout_tracker.php">Workout Tracker</a>
        <a href="progress.php">My Progress</a>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button class="btn btn-outline" type="submit">Logout</button>
        </form>
    </nav>
</header>

<main class="container">
    <section class="page-intro">
        <p class="eyebrow">TRAINING LIBRARY</p>
        <h1>Armwrestling Workout Programs</h1>
        <p>Choose a program suited to your current experience.</p>
    </section>

    <div class="program-grid">
    <?php foreach ($programs as $program): ?>
        <article class="program-card">
            <span class="level"><?= e($program['level']) ?></span>
            <h2><?= e($program['title']) ?></h2>
            <p><?= e($program['description']) ?></p>

            <h3>Exercises</h3>
            <ul class="exercise-list">
            <?php foreach (($exercises[$program['id']] ?? []) as $exercise): ?>
                <li>
                    <strong><?= e($exercise['exercise_name']) ?></strong>
                    <p><?= (int)$exercise['sets'] ?> sets ·
                        <?= e($exercise['reps']) ?> reps ·
                        <?= (int)$exercise['rest_seconds'] ?> sec rest</p>
                    <small><?= e($exercise['instructions']) ?></small>
                </li>
            <?php endforeach; ?>
            </ul>
            <a class="btn btn-primary full" href="workout_tracker.php">Track My Workout →</a>
        </article>
    <?php endforeach; ?>
    </div>

    <p class="notice">Train with controlled resistance. Warm up first, allow recovery between demanding sessions, and stop if you experience sharp pain.</p>
</main>
<footer class="footer">ARMFORCE · Train smart. Recover well.</footer>
<script src="script.js"></script>
</body>
</html>
