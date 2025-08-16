# Farme Framework

The PHP Framework That Just Works - Built with procedural simplicity, powered by dynamic CSS generation, and designed for developers who want to build fast without the complexity of modern frameworks.

## Features

- **🧩 Procedural Architecture** - Pure PHP functions, no classes required
- **⚡ Dynamic CSS Generation** - Like Tailwind CSS, but generated on-demand
- **🗺️ Route Auto-Discovery** - Routes automatically discovered from controller annotations
- **🗄️ ORM-like Database** - ActiveRecord-style operations with query builder
- **🎨 Atomic Components** - Reusable UI components built with atomic design principles
- **🎛️ Modern Templates** - Beautiful login/register templates with home page styling

## Installation

### Using Composer (Recommended)

```bash
composer create-project farme/project my-app
cd my-app
php console.php serve
```

### Manual Installation

1. Download the framework and project template
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env`
4. Start the server: `php console.php serve`

## Quick Start

### Create a Controller

```php
/**
 * @route GET /users/{id}
 * @route POST /users
 */
function user_show($params = []) {
    $user = user_find($params['id']);
    return farme_render('users/show', ['user' => $user]);
}
```

### Create a Model

```php
function user_find($id) {
    return farme_find('users', $id);
}

function user_create($data) {
    return farme_insert('users', $data);
}
```

### Create a Template

```php
<h1><?= farme_escape($user['name']) ?></h1>
<p><?= farme_escape($user['email']) ?></p>
```

### Use Form Organism

```php
<?= farme_organism('form', [
    'action' => '/login',
    'method' => 'POST',
    'csrf_token' => $csrf_token,
    'submit_text' => 'Sign In',
    'fields' => [
        'email' => [
            'type' => 'email',
            'placeholder' => 'Email Address',
            'required' => true
        ],
        'password' => [
            'type' => 'password',
            'placeholder' => 'Password',
            'required' => true
        ]
    ]
]) ?>
```

## Console Commands

```bash
# Start development server
php console.php serve

# Show registered routes  
php console.php routes

# Create new controller
php console.php make:controller User

# Create new model
php console.php make:model Post

# Run migrations
php console.php migrate
```

## Architecture

### Procedural Functions
No classes, no objects. Just functions that do one thing well.

### Auto-Discovery
Routes are automatically discovered from function annotations.

### Dynamic CSS
Only generates the CSS you actually use, resulting in dramatically smaller bundle sizes.

### ORM-like Operations
```php
// Simple queries
$users = user_query()
    |> farme_where('status', 1)
    |> farme_order_by('created_at', 'DESC')
    |> farme_get();

// Complex queries with relationships
$posts = post_query()
    |> farme_join('users', 'posts.user_id', 'users.id')
    |> farme_where('users.status', 1)
    |> farme_get();
```

### Modern UI Components

The framework includes a comprehensive set of UI components:

- **Atoms**: Button, Input, Label, Badge, Typography
- **Molecules**: Card, Alert, Form Field
- **Organisms**: Form, Header
- **Layouts**: Default, Minimal, API

All styled with FarmeDynamic CSS utility framework.

## Project Structure

```
framework/
├── farme-framework/           # Core framework package
│   ├── src/Core/             # Framework core components
│   ├── src/Template/         # UI components & layouts
│   ├── webroot/assets/       # Framework assets
│   └── composer.json         # Framework package
└── farme-project-template/    # Project starter template
    ├── src/Controller/       # Example controllers
    ├── src/Template/         # Project templates
    ├── config/               # Project configuration
    └── composer.json         # Project dependencies
```

## License

The Farme Framework is open-sourced software licensed under the [MIT license](LICENSE).