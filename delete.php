<?php
require_once 'config.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

$s = $pdo->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$s->execute([$id]);
$r = $s->fetch();

if (!$r) {
    echo '<p>Student not found.</p>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        $d = $pdo->prepare('DELETE FROM students WHERE id = ?');
        $d->execute([$id]);

        if ($d->rowCount() === 1) {
            $pdo->commit();
            echo '<p>Student deleted successfully.</p>';
        } else {
            $pdo->rollBack();
            echo '<p>Failed to delete student.</p>';
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log('Delete failed: ' . $e->getMessage());
        echo '<p>Failed to delete student.</p>';
    }
    echo '<p><a href="index.php">Back to all students</a></p>';
} else {
    $name = htmlspecialchars($r['first_name'] . ' ' . $r['last_name']);
    $safeId = htmlspecialchars($id);
    echo '<h2>Delete Student</h2>'
       . '<p>Are you sure you want to delete <strong>' . $name . '</strong>?</p>'
       . '<form method="post" action="delete.php?id=' . urlencode($id) . '">'
       . '<input type="hidden" name="id" value="' . $safeId . '">'
       . '<button type="submit">Yes, delete</button> '
       . '<a href="index.php">Cancel</a>'
       . '</form>';
}
?>