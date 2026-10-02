<?php
require_once 'config.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

$fields = ['first_name', 'last_name', 'birthday', 'sex', 'email',
           'student_number', 'program', 'enrolment_date'];
$errors = [];
$notice = '';
$values = array_fill_keys($fields, '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($fields as $f) {
        $values[$f] = trim($_POST[$f] ?? '');
    }

    foreach (['first_name', 'last_name', 'student_number', 'program'] as $f) {
        if ($values[$f] === '') {
            $errors[$f] = 'This field is required.';
        }
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    foreach (['birthday', 'enrolment_date'] as $f) {
        $d = DateTime::createFromFormat('Y-m-d', $values[$f]);
        if (!$d || $d->format('Y-m-d') !== $values[$f]) {
            $errors[$f] = 'Enter a valid date (YYYY-MM-DD).';
        }
    }
    if (!in_array($values['sex'], ['Male', 'Female', 'Other'], true)) {
        $errors['sex'] = 'Choose a valid option.';
    }

    if (!$errors) {
        try {
            $sql = 'UPDATE students
                    SET first_name = ?, last_name = ?, birthday = ?, sex = ?,
                        email = ?, student_number = ?, program = ?, enrolment_date = ?
                    WHERE id = ?';

            
            $data = [
                $values['first_name'], $values['last_name'], $values['birthday'],
                $values['sex'], $values['email'], $values['student_number'],
                $values['program'], $values['enrolment_date'], $id
            ];

            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $affected = $stmt->rowCount();

            if ($affected > 0) {
                $pdo->commit();
                header('Location: index.php');
                exit;
            }

            $pdo->rollBack();
            $notice = 'No changes were made (values unchanged, or student not found).';

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }

            if (($e->errorInfo[1] ?? 0) === 1062) {
                $errors['email'] = 'That email or student number already exists.';
            } else {
                error_log('Update failed: ' . $e->getMessage());
                $notice = 'Could not update the student.';
            }
        }
    }
} else {

    $stmt = $pdo->prepare(
        'SELECT first_name, last_name, birthday, sex, email,
                student_number, program, enrolment_date
         FROM students
         WHERE id = ?'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        die('Student not found.');
    }
    $values = $row;
}

function e($v): string { return htmlspecialchars((string)$v); }
?>
<link rel="stylesheet" href="style.css">
<div class="center-page">
<div class="student-details">
<h2>Edit Student</h2>

<?php if ($notice): ?>
    <p><?= e($notice) ?></p>
<?php endif; ?>

<form method="post" action="edit.php?id=<?= urlencode($id) ?>">
    <?php
    $labels = [
        'first_name' => 'First Name', 'last_name' => 'Last Name',
        'birthday' => 'Birthday', 'email' => 'Email',
        'student_number' => 'Student Number', 'program' => 'Program',
        'enrolment_date' => 'Enrolment Date',
    ];
    $types = ['birthday' => 'date', 'enrolment_date' => 'date', 'email' => 'email'];
    ?>

    <?php foreach (['first_name', 'last_name', 'birthday'] as $f): ?>
        <p>
            <label><?= e($labels[$f]) ?>:
                <input type="<?= $types[$f] ?? 'text' ?>" name="<?= $f ?>" value="<?= e($values[$f]) ?>">
            </label>
            <?php if (isset($errors[$f])): ?><span style="color:red"><?= e($errors[$f]) ?></span><?php endif; ?>
        </p>
    <?php endforeach; ?>

    <p>
        <label>Sex:
            <select name="sex">
                <?php foreach (['Male', 'Female', 'Other'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $values['sex'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php if (isset($errors['sex'])): ?><span style="color:red"><?= e($errors['sex']) ?></span><?php endif; ?>
    </p>

    <?php foreach (['email', 'student_number', 'program', 'enrolment_date'] as $f): ?>
        <p>
            <label><?= e($labels[$f]) ?>:
                <input type="<?= $types[$f] ?? 'text' ?>" name="<?= $f ?>" value="<?= e($values[$f]) ?>">
            </label>
            <?php if (isset($errors[$f])): ?><span style="color:red"><?= e($errors[$f]) ?></span><?php endif; ?>
        </p>
    <?php endforeach; ?>

    <button type="submit">Save Changes</button>
    <a href="index.php">Cancel</a>
</form>
</div>
</div>