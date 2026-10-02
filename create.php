<?php
require_once 'config.php';
$errors = [];
$first_name = $middle_name = $last_name = $email = $birthday = $sex = '';
$student_number = $program = $enrolment_date = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name     = trim($_POST['first_name'] ?? '');
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $birthday       = trim($_POST['birthday'] ?? '');
    $sex            = trim($_POST['sex'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $program        = trim($_POST['program'] ?? '');
    $enrolment_date = trim($_POST['enrolment_date'] ?? '');

    if (empty($first_name) || strlen($first_name) < 2 || strlen($first_name) > 100 || !preg_match('/^[A-Za-z ]+$/', $first_name)) { $errors['first_name'] = 'First name is required (2-100 letters and spaces only).'; }
    if (empty($last_name) || strlen($last_name) < 2 || strlen($last_name) > 100 || !preg_match('/^[A-Za-z ]+$/', $last_name)) { $errors['last_name'] = 'Last name is required (2-100 letters and spaces only).'; }

    if ($middle_name !== '' && (strlen($middle_name) > 100 || !preg_match('/^[A-Za-z ]+$/', $middle_name))) { $errors['middle_name'] = 'Middle name must be letters and spaces only (max 100).'; }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Enter a valid email address.'; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday) || !checkdate((int)substr($birthday, 5, 2), (int)substr($birthday, 8, 2), (int)substr($birthday, 0, 4))) { $errors['birthday'] = 'Enter a valid date (YYYY-MM-DD).'; }

    if (!in_array($sex, ['Male', 'Female'], true)) { $errors['sex'] = 'Choose Male or Female.'; }

    if ($student_number === '') { $errors['student_number'] = 'Student number is required.'; }
    if ($program === '') { $errors['program'] = 'Program is required.'; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $enrolment_date) || !checkdate((int)substr($enrolment_date, 5, 2), (int)substr($enrolment_date, 8, 2), (int)substr($enrolment_date, 0, 4))) { $errors['enrolment_date'] = 'Enter a valid date (YYYY-MM-DD).'; }

    if (empty($errors)) {
        try {
            $sql = 'INSERT INTO students
                    (id, first_name, middle_name, last_name, birthday, sex, email,
                     student_number, program, enrolment_date)
                    VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?)';

     
            $data = [
                $first_name, $middle_name, $last_name, $birthday, $sex,
                $email, $student_number, $program, $enrolment_date
            ];

            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);

            if ($stmt->rowCount() === 1) {
                $pdo->commit();
                $success = 'Student created successfully!';
                $first_name = $middle_name = $last_name = $email = $birthday = $sex = '';
                $student_number = $program = $enrolment_date = '';
            } else {
                $pdo->rollBack();
                $errors['form'] = 'Could not save the student.';
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }

       
            if (($e->errorInfo[1] ?? 0) === 1062) {
                $errors['email'] = 'That email or student number already exists.';
            } else {
                error_log('Insert failed: ' . $e->getMessage());
                $errors['form'] = 'Could not save the student.';
            }
        }
    }
}

function e($v): string { return htmlspecialchars((string)$v); }
function err(array $errors, string $k): string {
    return isset($errors[$k]) ? '<span style="color:red">' . htmlspecialchars($errors[$k]) . '</span>' : '';
}
?>
<link rel="stylesheet" href="style.css">
<div class="center-page">
<div class="student-details">
<h2>Add New Student</h2>
<?php if ($success): ?><p><?= e($success) ?></p><?php endif; ?>
<?= err($errors, 'form') ?>
<form method="POST">
    <p><label>First Name: <input type="text" name="first_name" value="<?= e($first_name) ?>"></label> <?= err($errors, 'first_name') ?></p>
    <p><label>Middle Name: <input type="text" name="middle_name" value="<?= e($middle_name) ?>"></label> <?= err($errors, 'middle_name') ?></p>
    <p><label>Last Name: <input type="text" name="last_name" value="<?= e($last_name) ?>"></label> <?= err($errors, 'last_name') ?></p>
    <p><label>Birthday: <input type="date" name="birthday" value="<?= e($birthday) ?>"></label> <?= err($errors, 'birthday') ?></p>
    <p><label>Sex:
        <select name="sex">
            <option value="">-- Select --</option>
            <?php foreach (['Male', 'Female'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $sex === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select></label> <?= err($errors, 'sex') ?></p>
    <p><label>Email: <input type="email" name="email" value="<?= e($email) ?>"></label> <?= err($errors, 'email') ?></p>
    <p><label>Student Number: <input type="text" name="student_number" value="<?= e($student_number) ?>"></label> <?= err($errors, 'student_number') ?></p>
    <p><label>Program: <input type="text" name="program" value="<?= e($program) ?>"></label> <?= err($errors, 'program') ?></p>
    <p><label>Enrolment Date: <input type="date" name="enrolment_date" value="<?= e($enrolment_date) ?>"></label> <?= err($errors, 'enrolment_date') ?></p>
    <button type="submit">Add Student</button>
    <a href="index.php">Back</a>
</form>
</div>
</div>