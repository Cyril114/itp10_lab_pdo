<?php
require_once 'config.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

$stmt = $pdo->prepare('SELECT id, first_name, last_name, birthday, sex, email, student_number, program, enrolment_date, created_at
                       FROM students
                       WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    echo '<p>Student not found.</p>';
} else {
?>
<link rel="stylesheet" href="style.css">
<div class="center-page">
    <div class="student-details">
        <h2>Student Details</h2>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($row['id']) ?></p>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
        <p><strong>Birthday:</strong> <?= htmlspecialchars($row['birthday']) ?></p>
        <p><strong>Sex:</strong> <?= htmlspecialchars($row['sex']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
        <p><strong>Student Number:</strong> <?= htmlspecialchars($row['student_number']) ?></p>
        <p><strong>Program:</strong> <?= htmlspecialchars($row['program']) ?></p>
        <p><strong>Enrolment Date:</strong> <?= htmlspecialchars($row['enrolment_date']) ?></p>
        <p><strong>Created At:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
    </div>
</div>
<?php
}