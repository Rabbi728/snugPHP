<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>All Users</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>সব Users</h1>
        <a href="/users/create" class="btn">নতুন User যোগ করুন</a>
        
        <?php if (!empty($users)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <a href="/users/<?= $user['id'] ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>কোন user পাওয়া যায়নি।</p>
        <?php endif; ?>
    </div>
</body>
</html>