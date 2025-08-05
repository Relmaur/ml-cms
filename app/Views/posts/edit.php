<h1>Edit Post</h1>
<form action="/posts/update/<?php echo $post->id; ?>" method="POST">
    <div>
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post->title); ?>" required>
    </div>
    <div>
        <label for="content">Content</label>
        <textarea name="content" id="content" required rows="10"><?php echo htmlspecialchars($post->content); ?></textarea>
    </div>
    <button type="submit">Update Post</button>
</form>
<a href="/posts/show/<?php echo $post->id ?>">Cancel</a>