<?php

use Core\Session;

$errors = Session::getFlash('errors') ?? [];
$oldInput = Session::getFlash('old_input') ?? [];
?>

<h1>Register an Account</h1>
<form action="/register" method="POST">
    <?php echo csrf_field(); ?>
    
    <div>
        <label for="name">Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($oldInput['name'] ?? '') ?>" required>
        <?php if (isset($errors['name'])): ?>
            <div style="color: red; font-size: 0.9em; margin: 10px 0 0 0;"><?php echo $errors['name']['message']; ?></div>
        <?php endif; ?>
    </div>
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($oldInput['email'] ?? '') ?>" required>
        <?php if (isset($errors['email'])): ?>
            <div style="color: red; font-size: 0.9em; margin: 10px 0 0 0;"><?php echo $errors['email']['message']; ?></div>
        <?php endif; ?>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" required>
        <?php if (isset($errors['password'])): ?>
            <div style="color: red; font-size: 0.9em; margin: 10px 0 0 0;"><?php echo $errors['password']['message']; ?></div>
        <?php endif; ?>
    </div>
    <button type="submit">Register</button>
</form>