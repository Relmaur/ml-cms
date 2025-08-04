<!-- app/Views/users/login.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
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
    <h1>Login</h1>
    <form action="/users/authenticate" method="POST">
        <div><label for="email">Email</label><input type="email" name="email" required></div>
        <div><label for="password">Password</label><input type="password" name="password" required></div>
        <button type="submit">Login</button>
    </form>
</body>

</html>