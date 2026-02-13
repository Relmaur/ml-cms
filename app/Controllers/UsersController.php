<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\View;
use Core\Session;
use Core\Validator;
use App\Models\User;
use Core\Http\Request;
use Core\Security\Csrf;

use Core\EventDispatcher;
use App\Events\UserRegistered;
use Core\Http\RedirectResponse;
use App\Controllers\BaseController;

class UsersController extends BaseController
{
    private $userModel;
    private $dispatcher;

    // Inject the dispatcher via the constructor
    public function __construct(User $userModel, EventDispatcher $dispatcher)
    {
        $this->userModel = $userModel;
        $this->dispatcher = $dispatcher;
    }

    // Registration
    // users/register
    public function register()
    {
        $pageTitle = 'Riven | Register';

        return View::render('users/register', [
            'pageTitle' => $pageTitle
        ]);
    }

    // for form action: /users/store
    public function store(Request $request)
    {

        $validator = new Validator($request->all());

        $validator->validate([
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($this->userModel->findByEmail($request->input('email'))) {
            $validator->addError('email', 'This email address is already taken');
        }

        if ($validator->fails()) {
            // If validation fails, redirect back with errors
            Session::flash('errors', $validator->getErrors());
            Session::flash('old_input', $request->all()); // Send back the old input to re-populate the form
            return new RedirectResponse('/register');
        }

        $data = [
            'name' => trim($request->input('name')),
            'email' => trim($request->input('email')),
            'password' => password_hash($request->input('password'), PASSWORD_DEFAULT) // Hash the password
        ];

        if ($this->userModel->register($data)) {

            // Get the newly created user data (we need the ID)
            $newUser = $this->userModel->findByEmail($data['email']);

            // Dispatch the event!
            $this->dispatcher->dispatch(new UserRegistered($newUser));

            Session::flash('success', 'Thank you for registering!');

            // Redirect to login page after successful registration
            return new RedirectResponse('/login');
        }
    }

    // Login
    // users/login
    public function login()
    {
        $pageTitle = 'Riven | Login';

        return View::render('users/login', [
            'pageTitle' => $pageTitle
        ]);
    }

    // for form action: /users/authenticate
    public function authenticate(Request $request)
    {

        $validator = new Validator($request->all());

        $validator->validate([
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->getErrors());
            Session::flash('old_input', $request->all());
            return new RedirectResponse('/login');
        }

        $email = $request->input('email');
        $password = $request->input('password');

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            // Password is correct, set session
            Session::set('user_id', $user->id);
            Session::set('user_name', $user->name);

            Csrf::regenerateToken();

            Session::flash('success', 'Welcome Back, ' . $user->name . '!');

            // Redirect to homepage or dashboard
            return new RedirectResponse('/dashboard');
        } else {
            Session::flash('error', 'Invalid Credentials');
            return new RedirectResponse('/login');
        }
    }

    // Logout
    // users/logout
    public function logout()
    {
        Session::destroy();
        return new RedirectResponse('/login');
    }
}
