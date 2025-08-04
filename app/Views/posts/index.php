<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
</head>

<body>
    <header></header>
    <main>
        <h1>All Blog Posts</h1>
        <a href="/posts/create" style="margin: 20px 0;">Create New Post</a>
        <?php foreach ($posts as $post): ?>
            <article>
                <h2><?php echo htmlspecialchars($post->title) ?></h2>
                <p><?php echo nl2br(htmlspecialchars($post->body)) ?></p>
                <p><small><?php echo date('F j, Y', strtotime($post->created_at)); ?></small></p>
            </article>
            <hr>
        <?php endforeach; ?>
    </main>
    <footer></footer>
</body>

</html>