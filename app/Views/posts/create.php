<h1>Create Post</h1>
<form action="/posts" method="POST" enctype="multipart/form-data">
    <div>
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required>
    </div>
    <div>
        <label for="content">Content</label>
        <textarea name="content" id="content" rows="10" required></textarea>
    </div>
    <div style="margin: 15px 0 0 0;">
        <label for="image">Image</label>
        <input type="file" id="image" name="image">
    </div>
    <button type="submit">Save Post</button>
</form>

<a href="/posts">Back to Posts</a>