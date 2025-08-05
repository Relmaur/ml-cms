<?php

namespace App\Controllers;

use App\Models\User;
use Core\Session;

class UsersController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // Registration
    // users/register
    public function register()
    {
        $pageTitle = 'Register';
        require_once '../app/Views/users/register.php';
    }

    // for form action: /users/store
    public function store()
    {
        // Simple validation
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
            die('Please fill out all fields.');
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            die('Invalid email format.');
        }

        if ($this->userModel->findByEmail($_POST['email'])) {
            die('Email is already taken.');
        }

        $data = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT) // Hash the password
        ];

        if ($this->userModel->register($data)) {
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
        require_once '../app/Views/users/login.php';
    }

    // for form action: /users/authenticate
    public function authenticate()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            // Password is correct, set session
            Session::set('user_id', $user->id);
            Session::set('user_name', $user->name);

            Session::set('redirect_email', $user->email);
            Session::set('redirect_name', $user->name);
            Session::set('redirect_message', 'Welcome back!');

            // Redirect to homepage or dashboard
            header('Location: /');
            exit();
        } else {
            die('Invalid credentials');
        }
    }

    // Logout
    // users/logout
    public function logout()
    {
        Session::destroy();
        header('Location: /users/login');
        exit();
    }
}


