<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
</head>

<body>
    <?php if ($post): ?>
        <h1><?php echo htmlspecialchars($post->title); ?></h1>
        <p><small>Created on: <?php echo date('F j, Y', strtotime($post->created_at)) ?></small></p>
        <div><?php echo nl2br(htmlspecialchars($post->body)) ?></div>
    <?php else: ?>
        <h1>Post not found</h1>
        <p>Sorry, we couldn't find the post you were looking for.</p>
    <?php endif; ?>
    <a href="/posts/index">Back to Posts</a>
</body>

</html>