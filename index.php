<?php
require_once 'config.php';

$sql = 'SELECT id, first_name, last_name, email, enrolment_date
        FROM students
        ORDER BY enrolment_date DESC, id DESC';


$rows = $pdo->query($sql)->fetchAll();
?>
<link rel="stylesheet" href="style.css">


<h2>All Student Records</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Enroll</th>
            <th>Actions</th>
        </tr>
    </thead>

    
    <a href="create.php" class="create-button">Create</a>
    <tbody>
        
        <?php foreach ($rows as $row): ?>
        <tr>
            
            <td><?= htmlspecialchars($row['id']) ?></td>

            <td>
                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
            </td>

            <td><?= htmlspecialchars($row['email']) ?></td>

            <td><?= htmlspecialchars($row['enrolment_date']) ?></td>

            
            <td>
                <a href="view.php?id=<?= $row['id'] ?>" class="action-button view-button">View</a>
                <a href="edit.php?id=<?= $row['id'] ?>" class="action-button edit-button">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="action-button delete-button" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>