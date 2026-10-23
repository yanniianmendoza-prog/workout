
CREATE DATABASE IF NOT EXISTS armwrestling_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE armwrestling_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS workout_programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    level ENUM('Beginner','Intermediate','Advanced') NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS workout_exercises (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    exercise_name VARCHAR(120) NOT NULL,
    sets INT UNSIGNED NOT NULL DEFAULT 3,
    reps VARCHAR(30) NOT NULL DEFAULT '10-12',
    rest_seconds INT UNSIGNED NOT NULL DEFAULT 90,
    instructions TEXT,
    FOREIGN KEY (program_id) REFERENCES workout_programs(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS workout_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    exercise_name VARCHAR(120) NOT NULL,
    sets_completed INT UNSIGNED NOT NULL,
    reps_completed INT UNSIGNED NOT NULL,
    weight_kg DECIMAL(7,2) NOT NULL DEFAULT 0,
    notes VARCHAR(255) DEFAULT NULL,
    workout_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

INSERT INTO workout_programs (title, level, description) VALUES
('Armwrestling Foundations', 'Beginner',
 'Build basic wrist, grip, and forearm strength with controlled movements.'),
('Strength and Table Control', 'Intermediate',
 'Develop pronation, cupping, rising, and pulling strength.'),
('Advanced Strength Training', 'Advanced',
 'A structured higher-intensity plan for experienced athletes.');

INSERT INTO workout_exercises
(program_id, exercise_name, sets, reps, rest_seconds, instructions)
VALUES
(1, 'Wrist Curls', 3, '12-15', 60, 'Use a comfortable range of motion.'),
(1, 'Hammer Curls', 3, '10-12', 90, 'Keep your wrist neutral.'),
(1, 'Light Band Pronation', 3, '12', 60, 'Rotate slowly without jerking.'),
(1, 'Finger Extensions', 3, '15-20', 45, 'Use a light resistance band.'),
(2, 'Cable Pronation', 4, '8-12', 90, 'Keep the elbow supported.'),
(2, 'Wrist Cupping', 4, '10-12', 90, 'Flex the wrist under control.'),
(2, 'Rising Exercise', 3, '10-12', 75, 'Lift through the thumb side of the hand.'),
(2, 'Back Pressure Row', 3, '8-12', 120, 'Pull with controlled elbow and shoulder position.'),
(3, 'Pronation Holds', 4, '6-10', 120, 'Use challenging but controlled resistance.'),
(3, 'Wrist Curls', 4, '8-10', 120, 'Avoid bouncing the weight.'),
(3, 'Hammer Curls', 4, '8-10', 120, 'Keep your elbow stable.'),
(3, 'Grip Holds', 3, '20-30 sec', 90, 'Hold a manageable load without pain.');
