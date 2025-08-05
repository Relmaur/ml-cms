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

        a {
            display: inline-block;
            margin: 20px 0 0 0;
        }
    </style>
</head>

<body>
    <?php if ($post): ?>
        <h1><?php echo htmlspecialchars($post->title); ?></h1>
        <p><small>Created on: <?php echo date('F j, Y', strtotime($post->created_at)) ?></small> | <small>By: <strong><?php echo htmlspecialchars($post->author_name ?? 'Unknown') ?></strong></small></p>
        <div><?php echo nl2br(htmlspecialchars($post->content)) ?></div>
        <?php if (Session::isAuthenticated()): ?>
            <a href="/posts/edit/<?php echo $post->id; ?>">Edit this post</a>
            <form action="/posts/destroy/<?php echo $post->id; ?>" method="POST" style="display: inline; margin-left: 20px;">
                <button type="submit" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
            </form>
        <?php endif; ?>
        <hr>
    <?php else: ?>
        <h1>Post not found</h1>
        <p>Sorry, we couldn't find the post you were looking for.</p>
    <?php endif; ?>
    <a href="/posts/index">Back to Posts</a>
</body>

</html>