<?php

use Core\Session;

$errors = Session::getFlash('errors') ?? [];
$oldValues = Session::getFlash('old_input') ?? [];
?>

<h1>Login</h1>
<form action="/login" method="POST">
    <?php echo csrf_field(); ?>

    <div>
        <label for="email">Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($oldValues['email'] ?? '') ?>" required>
        <?php if (isset($errors['email'])): ?>
            <div style="color: red; font-size: 0.9em; margin: 10px 0 0 0;"><?php echo $errors['email']['message']; ?></div>
        <?php endif; ?>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" required>
    </div>
    <button type="submit">Login</button>
</form>