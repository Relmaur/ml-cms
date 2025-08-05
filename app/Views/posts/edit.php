<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        label {
            display: block;
            margin-top: 20px;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        button {
            margin: 20px 0;
            padding: 10px 20px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header></header>
    <main>
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
    </main>
    <footer></footer>
</body>

</html>