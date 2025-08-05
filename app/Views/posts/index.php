<?php

use Core\Session; ?>

<h1>All Blog Posts</h1>

<?php if (Session::isAuthenticated()): ?>
    <a href="/posts/create" style="margin: 20px 0;">Create New Post</a>
<?php endif; ?>

<?php foreach ($posts as $post): ?>
    <article>
        <h2><?php echo htmlspecialchars($post->title) ?></h2>
        <p><?php echo nl2br(htmlspecialchars($post->content)) ?></p>
        <p><small><?php echo date('F j, Y', strtotime($post->created_at)); ?></small></p>
        <p><small>By: <strong><?php echo htmlspecialchars($post->author_name ?? 'Unknown') ?></strong></small></p>

        <?php if (Session::isAuthenticated()): ?>
            <p>
                <a href="/posts/show/<?php echo $post->id; ?>">Read More</a> |
                <a href="/posts/edit/<?php echo $post->id; ?>">Edit</a>
            </p>
        <?php endif; ?>
    </article>
    <hr>
<?php endforeach; ?>