<?php

use Core\Session; ?>

<?php if ($post): ?>
    <h1><?php echo e($post->title); ?></h1>

    <?php if ($post->image_path): ?>
        <img src="<?php echo e($post->image_path); ?>" alt="<?php echo e($post->title) ?>">
    <?php endif; ?>

    <p><small>Created on: <?php echo date('F j, Y', strtotime($post->created_at)) ?></small> | <small>By: <strong><?php echo e($post->author_name ?? 'Unknown') ?></strong></small></p>
    <div><?php echo nl2br(e($post->content)) ?></div>

    <?php if (Session::isAuthenticated()): ?>

        <a href="<?php echo route('posts.edit', ['id' => $post->id]); ?>">Edit this post</a>
        <form action="<?php echo route('posts.destroy', ['id' => $post->id]); ?>" method="POST" style="display: inline; margin-left: 20px;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>

            <button type="submit" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
        </form>

    <?php endif; ?>

<?php else: ?>
    <h1>Post not found</h1>
    <p>Sorry, we couldn't find the post you were looking for.</p>
<?php endif; ?>
<div><a href="<?php echo route('posts.index'); ?>">Back to Posts</a></div>