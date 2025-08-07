<h1>Edit Post</h1>
<form action="/posts/<?php echo $post->id; ?>" method="POST" enctype="multipart/form-data">
    <div>
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post->title); ?>" required>
    </div>
    <div>
        <label for="content">Content</label>
        <textarea name="content" id="content" required rows="10"><?php echo htmlspecialchars($post->content); ?></textarea>
    </div>
    <div style="margin: 15px 0 0 0;">
        <label for="image">Image</label>
        <input type="file" id="image" name="image">
        <?php if ($post->image_path): ?>
            <img src="<?php echo htmlspecialchars($post->image_path) ?>" alt="<?php echo htmlspecialchars($post->title) ?>">
        <?php endif; ?>
    </div>
    <button type="submit">Update Post</button>
</form>
<a href="/posts/<?php echo $post->id ?>">Cancel</a>