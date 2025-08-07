<?php

namespace App\Controllers;

use \App\Controllers\BaseController;
use App\Models\User;
use Core\Session;
use Core\View;
use Core\Validator;

class UsersController extends BaseController
{
    private $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    // Registration
    // users/register
    public function register()
    {
        $pageTitle = 'Register';

        View::render('users/register', [
            'pageTitle' => $pageTitle
        ]);
    }

    // for form action: /users/store
    public function store()
    {

        $validator = new Validator($_POST);

        $validator->validate([
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($this->userModel->findByEmail($_POST['email'])) {
            $validator->addError('email', 'This email address is already taken');
        }

        if ($validator->fails()) {
            // If validation fails, redirect back with errors
            Session::flash('errors', $validator->getErrors());
            Session::flash('old_input', $_POST); // Send back the old input to re-populate the form
            header('Location: /register');
            exit();
        }

        $data = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT) // Hash the password
        ];

        if ($this->userModel->register($data)) {
            Session::flash('sucess', 'Thank you for registering!');
            // Redirect to login page aftersuccessful registration
            header('Location: /users/login');
            exit();
        } else {
            die('Something went wrong during registration');
        }
    }

    // Login
    // users/login
    public function login()
    {
        $pageTitle = 'Login';

        View::render('users/login', [
            'pageTitle' => $pageTitle
        ]);
    }

    // for form action: /users/authenticate
    public function authenticate()
    {

        $validator = new Validator($_POST);

        $validator->validate([
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->getErrors());
            Session::flash('old_input', $_POST);
            header('Location: /login');
            exit();
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            // Password is correct, set session
            Session::set('user_id', $user->id);
            Session::set('user_name', $user->name);

            Session::flash('success', 'Welcome Back, ' . $user->name . '!');

            // Redirect to homepage or dashboard
            header('Location: /dashboard');
            exit();
        } else {
            Session::flash('error', 'Invalid Credentials');
            header('Location: /login');
            exit();
        }
    }

    // Logout
    // users/logout
    public function logout()
    {
        Session::destroy();
        header('Location: /login');
        exit();
    }
}
