<?php


namespace Tests\Feature;

use App\Controllers\UsersController;
use App\Models\User;
use Core\Container;
use PHPUnit\Framework\Attributes\Test;

class RegistrationTest extends BaseFeatureTestCase
{
    #[Test]
    public function can_register_an_account()
    {

        echo "DEBUG: Entered can_register_an_account() test.\n";

        // Resolve the controller from the fully configured container
        $controller = $this->container->resolve(UsersController::class);

        // Arrange: Simulate a POST request
        $_POST['name'] = 'John Doe';
        $_POST['email'] = 'john@example.com';
        $_POST['password'] = 'password123';

        // Act: Call the controller method that handles the form submission
        // We suppress the header output that our controller tries to send
        @$controller->store();

        // Assert: Check that the user was actually created in the database
        $userModel = $this->container->resolve(User::class);
        $newUser = $userModel->findByEmail('john@example.com');

        $this->assertNotFalse($newUser, 'User should be found in the database.');
        $this->assertSame('John Doe', $newUser->name);
    }
}
