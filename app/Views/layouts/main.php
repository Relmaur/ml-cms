<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo csrf_meta(); ?>
    <title><?php echo htmlspecialchars($pageTitle ?? 'Riven') ?></title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: auto;
            padding: 20px;
            color: #333;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;

            /* position: relative;
            animation-name: fade-left;
            animation-delay: 3s;
            animation-timing-function: cubic-bezier(0.455, 0.03, 0.515, 0.955);
            animation-duration: 1s;
            animation-fill-mode: forwards; */
        }

        @keyframes fade-up {
            0% {
                top: 0;
                opacity: 1;
            }

            100% {
                top: -500px;
                opacity: 0;
            }
        }

        @keyframes fade-left {
            0% {
                left: 0;
                opacity: 1;
            }

            100% {
                left: -1000px;
                opacity: 0;
            }
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
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

        main hr:last-child {
            display: none;
        }

        img {
            width: 100%;
            max-width: 300px;
        }
    </style>
</head>

<body>

    <?php if ($successMessage = Core\Session::getFlash('success')): ?>

        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage) ?></div>

    <?php elseif ($errorMessage = Core\Session::getFlash('error')): ?>

        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage) ?></div>

    <?php endif; ?>

    <?php require_once '../app/Views/partials/header.php'; ?>

    <main>
        <?php echo $content; ?>
    </main>

    <?php require_once '../app/Views/partials/footer.php'; ?>

    <script>
        // Get the CSRF token from the meta tag
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Add to all AJAX requests
        fetch('/api/posts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                title: 'My Post',
                content: 'Post content'
            })
        });
    </script>

</body>

</html>