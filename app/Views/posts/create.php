<h1>Create Post</h1>
<form action="/posts/store" method="POST">
    <div>
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required>
    </div>
    <div>
        <label for="content">Content</label>
        <textarea name="content" id="content" rows="10" required></textarea>
    </div>
    <button type="submit">Save Post</button>
</form>

<a href="/posts/index">Back to Posts</a>