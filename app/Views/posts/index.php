<?php

use Core\Session;

?>

<h1>All Blog Posts</h1>

<?php if (Session::isAuthenticated()): ?>
    <a href="/posts/create" style="margin: 20px 0;">Create New Post</a>
<?php endif; ?>

<?php foreach ($posts as $post): ?>
    <article>

        <?php if ($post->image_path): ?>
            <img src="<?php echo htmlspecialchars($post->image_path); ?>" alt="<?php echo htmlspecialchars($post->image_path) ?>">
        <?php endif; ?>

        <h2><?php echo htmlspecialchars($post->title) ?></h2>
        <p><?php echo nl2br(htmlspecialchars($post->content)) ?></p>
        <p><small><?php echo date('F j, Y', strtotime($post->created_at)); ?></small></p>
        <p><small>By: <strong><?php echo htmlspecialchars($post->author_name ?? 'Unknown') ?></strong></small></p>

        <span>
            <a href="<?php echo route('posts.show', ['id' => $post->id]) ?>">Read More</a>
            <?php if (Session::isAuthenticated()): ?>
                | <a href="<?php echo route('posts.edit', ['id' => $post->id]); ?>">Edit</a>
            <?php endif; ?>
        </span>
        
    </article>
    <hr>
<?php endforeach; ?>