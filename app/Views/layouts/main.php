<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo csrf_meta(); ?>
    <title><?php echo e($pageTitle ?? 'Riven') ?></title>
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

    <nav>
        <ul>
            <li><a href="<?php echo route('home'); ?>">Home</a></li>
            <li><a href="<?php echo route('posts.index'); ?>">Posts</a></li>
            <li><a href="<?php echo route('pages.about'); ?>">About</a></li>

            <?php if (Core\Session::isAuthenticated()): ?>
                <li><a href="<?php echo route('dashboard'); ?>">Dashboard</a></li>
                <li><a href="<?php echo route('logout'); ?>">Logout (<?php echo e(Core\Session::get('user_name')); ?>)</a></li>
            <?php else: ?>
                <li><a href="<?php echo route('login'); ?>">Login</a></li>
                <li><a href="<?php echo route('register'); ?>">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <main>
        <?php
        // Display flash messages
        $success = Core\Session::getFlash('success');
        $error = Core\Session::getFlash('error');
        ?>

        <?php if ($success): ?>
            <div style="padding: 10px; margin: 10px 0; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                <?php echo e($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="padding: 10px; margin: 10px 0; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php echo $content; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> ML CMS. All rights reserved.</p>
    </footer>

</body>

</html>