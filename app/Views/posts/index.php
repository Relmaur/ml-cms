<?php

use Core\Session; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
    </style>
</head>

<body>
    <header></header>
    <main>
        <h1>All Blog Posts</h1>

        <?php if (Session::isAuthenticated()): ?>
            <a href="/posts/create" style="margin: 20px 0;">Create New Post</a>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <article>
                <h2><?php echo htmlspecialchars($post->title) ?></h2>
                <p><?php echo nl2br(htmlspecialchars($post->content)) ?></p>
                <p><small><?php echo date('F j, Y', strtotime($post->created_at)); ?></small></p>

                <?php if (Session::isAuthenticated()): ?>
                    <p>
                        <a href="/posts/show/<?php echo $post->id; ?>">Read More</a> |
                        <a href="/posts/edit/<?php echo $post->id; ?>">Edit</a>
                    </p>
                <?php endif; ?>
            </article>
            <hr>
        <?php endforeach; ?>
    </main>
    <footer></footer>
</body>

</html>

