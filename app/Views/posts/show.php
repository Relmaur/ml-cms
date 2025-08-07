<?php

use Core\Session; ?>
<?php if ($post): ?>
    <h1><?php echo htmlspecialchars($post->title); ?></h1>

    <?php if ($post->image_path): ?>
        <img src="<?php echo htmlspecialchars($post->image_path); ?>" alt="<?php echo htmlspecialchars($post->title) ?>">
    <?php endif; ?>
    
    <p><small>Created on: <?php echo date('F j, Y', strtotime($post->created_at)) ?></small> | <small>By: <strong><?php echo htmlspecialchars($post->author_name ?? 'Unknown') ?></strong></small></p>
    <div><?php echo nl2br(htmlspecialchars($post->content)) ?></div>
    <?php if (Session::isAuthenticated()): ?>
        <a href="/posts/<?php echo $post->id; ?>/edit">Edit this post</a>
        <form action="/posts/<?php echo $post->id; ?>/delete" method="POST" style="display: inline; margin-left: 20px;">
            <button type="submit" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
        </form>
    <?php endif; ?>
<?php else: ?>
    <h1>Post not found</h1>
    <p>Sorry, we couldn't find the post you were looking for.</p>
<?php endif; ?>
<div><a href="/posts">Back to Posts</a></div>