# ML - CMS

This project is a custom Content Management System built from the ground up using PHP, following modern development principles like OOP, MVC, and PSR-4 autoloading.

## Core features

- Declarative Routing: A clean, flexible routing system defined in routes/.
- DI Container: A simple but powerful service container for managing class dependencies.
- Database Migrations: A professional, reversible migration system to manage database schema changes.
- Event-Driven System: Decoupled architecture using events and listeners.
- Authentication: A complete user registration and session-based login system.
- File Uploads & Mailing: Functionality for handling file uploads and sending emails via SMTP.
- Automated Testing: A full testing suite using PHPUnit for both unit and feature tests.
- Developer Tools: A suite of command-line tools for generating code and managing the application.

## Prerequisites

- PHP 8.1 or higher
- Composer ([https://getcomposer.org/](https://getcomposer.org/))
- A local development server (e.g., Laravel Herd, XAMPP)
- SQLite PHP extension enabled

## Installation and Setup

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/Relmaur/ml-cms
    cd php-cms
    ```

2.  **Install PHP dependencies:**

    ```bash
    composer install
    ```

3.  **Set up Environment Variables:**
    Create a `.env` file in the project root by copying the example file.

    ```bash
    cp .env.example .env
    ```

    Open the `.env` file and add your Mailtrap credentials to the `MAILER_DSN` variable.

4.  **Set up the database:**
    This project uses SQLite. The database file will be created automatically. To set up the necessary tables, run the migration bootstrap command once:

    ```bash
    php database/migrations_setup.php
    ```

5.  **Run the database migrations:**
    This command will create all the necessary tables like `users` and `posts`.

    ```bash
    composer migrate
    ```

6.  **Serve the application:**
    Point your local server's web root to the `/public` directory of this project.

## Available Commands

### Migrations:

- `composer migrate`: Runs all pending database migrations.
- `composer migrate:rollback`: Reverts the last batch of migrations.
- `composer make-migration <MigrationName>`: Creates a new, empty migration file (e.g., `composer make-migration create_products_table`).

### Code Generation:

- `composer:make:controller <ControllerName>`: Creates a new controller file.
- `composer make:model <ModelName>`: Creates a new model file.

### Database

- `composer db:backup`: Creates a timestamped backup of the SQLite database in the /backups directory.

## Testing

This project includes a full testing suite using PHPUnit.

- Run all tests (both Unit and Feature):

```bash
composer test
```
