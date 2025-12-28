<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Home' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $title ?? 'Welcome' ?></h1>
        <p>এটি একটি custom PHP framework এর about page।</p>
        <nav>
            <a href="/">Home</a>
            <a href="/about">About</a>
            <a href="/users">Users</a>
        </nav>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>