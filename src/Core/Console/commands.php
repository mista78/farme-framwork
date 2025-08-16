<?php

/**
 * Built-in console commands
 */

/**
 * Register default commands
 */
function farme_register_default_commands() {
    
    // Show routes
    farme_command('routes', 'Show all registered routes', function($args) {
        $routes = farme_get_routes();
        
        if (empty($routes)) {
            farme_console_info('No routes found');
            return;
        }
        
        farme_console_write("Registered routes:\n");
        farme_console_write(sprintf("%-8s %-30s %-20s", "METHOD", "PATH", "CONTROLLER@ACTION"));
        farme_console_write(str_repeat("-", 60));
        
        foreach ($routes as $route) {
            farme_console_write(sprintf(
                "%-8s %-30s %-20s", 
                $route['method'], 
                $route['path'], 
                $route['controller'] . '@' . $route['action']
            ));
        }
    });
    
    // Create controller
    farme_command('make:controller', 'Create a new controller', function($args) {
        if (empty($args[0])) {
            farme_console_error('Controller name required');
            return;
        }
        
        $name = $args[0];
        $filename = ucfirst($name) . 'Controller.php';
        $path = farme_config_get('app.paths.controllers') . '/' . $filename;
        
        if (file_exists($path)) {
            farme_console_error("Controller $filename already exists");
            return;
        }
        
        $template = "<?php\n\n/**\n * @route GET /" . strtolower($name) . "\n */\nfunction " . strtolower($name) . "_index(\$params = []) {\n    return farme_render('" . strtolower($name) . "/index', ['title' => '" . ucfirst($name) . "']);\n}\n";
        
        file_put_contents($path, $template);
        farme_console_success("Controller $filename created successfully");
    });
    
    // Create model
    farme_command('make:model', 'Create a new model', function($args) {
        if (empty($args[0])) {
            farme_console_error('Model name required');
            return;
        }
        
        $name = $args[0];
        $filename = ucfirst($name) . '.php';
        $path = farme_config_get('app.paths.models') . '/' . $filename;
        
        if (file_exists($path)) {
            farme_console_error("Model $filename already exists");
            return;
        }
        
        $table = strtolower($name) . 's';
        $prefix = strtolower($name);
        
        $template = "<?php\n\n/**\n * " . ucfirst($name) . " model functions\n */\n\n";
        $template .= "function {$prefix}_find(\$id) {\n    return farme_find('$table', \$id);\n}\n\n";
        $template .= "function {$prefix}_all() {\n    return farme_find_all('$table');\n}\n\n";
        $template .= "function {$prefix}_create(\$data) {\n    return farme_insert('$table', \$data);\n}\n\n";
        $template .= "function {$prefix}_update(\$id, \$data) {\n    return farme_update('$table', \$data, \$id);\n}\n\n";
        $template .= "function {$prefix}_delete(\$id) {\n    return farme_delete('$table', \$id);\n}\n";
        
        file_put_contents($path, $template);
        farme_console_success("Model $filename created successfully");
    });
    
    // Serve command
    farme_command('serve', 'Start development server', function($args) {
        $host = isset($args[0]) ? $args[0] : 'localhost';
        $port = isset($args[1]) ? $args[1] : '8000';
        
        farme_console_info("Starting server at http://$host:$port");
        farme_console_info("Press Ctrl+C to stop");
        
        $webroot = WEBROOT_PATH;
        $command = "php -S $host:$port -t $webroot";
        
        passthru($command);
    });
    
    // Migration commands
    farme_command('migrate', 'Run database migrations', function($args) {
        farme_console_info('Running database migrations...');
        farme_run_migrations();
    });
    
    farme_command('migrate:status', 'Show migration status', function($args) {
        farme_migration_status();
    });
    
    farme_command('migrate:rollback', 'Rollback migrations', function($args) {
        $steps = isset($args[0]) ? (int)$args[0] : 1;
        farme_console_info("Rolling back $steps migration(s)...");
        farme_rollback_migrations($steps);
    });
    
    farme_command('migrate:reset', 'Reset all migrations (drops all tables)', function($args) {
        farme_console_warning('This will drop all tables! Are you sure? (y/N)');
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            farme_reset_migrations();
        } else {
            farme_console_info('Migration reset cancelled');
        }
    });
    
    farme_command('make:migration', 'Create a new migration file', function($args) {
        if (empty($args[0])) {
            farme_console_error('Migration name required');
            farme_console_info('Usage: php console.php make:migration create_users_table');
            farme_console_info('       php console.php make:migration add_email_to_users_table');
            return;
        }
        
        $name = $args[0];
        $type = isset($args[1]) ? $args[1] : 'table';
        
        farme_generate_migration($name, $type);
    });
    
    // Log management commands
    farme_command('logs:stats', 'Show log statistics for today or specific date', function($args) {
        $date = isset($args[0]) ? $args[0] : date('Y-m-d');
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            farme_console_error('Invalid date format. Use YYYY-MM-DD');
            return;
        }
        
        farme_console_info("Log statistics for $date:");
        $stats = farme_get_log_stats($date);
        
        farme_console_write("Errors: " . $stats['errors']);
        farme_console_write("Warnings: " . $stats['warnings']);
        farme_console_write("Requests: " . $stats['requests']);
        farme_console_write("Average Response Time: " . $stats['avg_response_time'] . "ms");
    });
    
    farme_command('logs:cleanup', 'Clean up old log files', function($args) {
        $days = isset($args[0]) ? (int)$args[0] : 30;
        
        farme_console_info("Cleaning up log files older than $days days...");
        $deleted = farme_cleanup_old_logs($days);
        farme_console_success("Deleted $deleted old log files");
    });
    
    farme_command('logs:tail', 'Tail log files in real time', function($args) {
        $category = isset($args[0]) ? $args[0] : 'application';
        $date = date('Y-m-d');
        $log_file = farme_get_log_file($category, $date);
        
        if (!file_exists($log_file)) {
            farme_console_error("Log file not found: $log_file");
            return;
        }
        
        farme_console_info("Tailing log file: $log_file");
        farme_console_info("Press Ctrl+C to stop");
        
        $handle = fopen($log_file, 'r');
        fseek($handle, 0, SEEK_END);
        
        while (true) {
            $line = fgets($handle);
            if ($line) {
                echo $line;
            } else {
                usleep(100000); // Sleep for 0.1 seconds
            }
        }
    });
    
    // Generate CRUD scaffold
    farme_command('make:crud', 'Generate complete CRUD (Model, Controller, Views)', function($args) {
        if (empty($args[0])) {
            farme_console_error('Entity name required');
            farme_console_info('Usage: php console.php make:crud Post');
            farme_console_info('       php console.php make:crud User --admin');
            return;
        }
        
        $name = $args[0];
        $admin = in_array('--admin', $args);
        $table_name = strtolower($name) . 's';
        
        farme_console_info("Generating CRUD for: $name");
        farme_console_info("Expected table: $table_name");
        farme_console_info("Admin interface: " . ($admin ? 'Yes' : 'No'));
        
        // Check if table exists in database
        farme_console_info('Checking if table exists...');
        
        try {
            if (!farme_table_exists($table_name)) {
                farme_console_error("Table '$table_name' does not exist in the database!");
                farme_console_info('Please ensure the table exists before generating CRUD.');
                farme_console_info('You can:');
                farme_console_info("1. Create a migration: php console.php make:migration create_{$table_name}_table");
                farme_console_info('2. Run the migration: php console.php migrate');
                farme_console_info('3. Then run this command again');
                return;
            }
            
            farme_console_success("Table '$table_name' found in database");
            
            // Get table columns
            farme_console_info('Analyzing table structure...');
            $columns = farme_get_table_columns($table_name);
            
            if (empty($columns)) {
                farme_console_warning('Could not retrieve table columns, using default fields');
                $columns = [];
            } else {
                farme_console_success('Found ' . count($columns) . ' columns in table');
                foreach ($columns as $col) {
                    farme_console_info("  - {$col['name']} ({$col['type']})");
                }
            }
            
        } catch (Exception $e) {
            farme_console_error('Failed to check table existence: ' . $e->getMessage());
            farme_console_warning('Proceeding with CRUD generation anyway...');
            $columns = [];
        }
        
        // Generate model
        farme_console_info('Creating model...');
        farme_generate_crud_model($name, $columns);
        
        // Generate controller
        farme_console_info('Creating controller...');
        farme_generate_crud_controller($name, $admin, $columns);
        
        // Generate views
        farme_console_info('Creating views...');
        farme_generate_crud_views($name, $admin, $columns);
        
        // Generate migration if it doesn't exist
        farme_console_info('Checking migration...');
        farme_generate_crud_migration($name);
        
        farme_console_success("CRUD for '$name' generated successfully!");
        farme_console_info('The table exists and CRUD is ready to use.');
    });
}

/**
 * Generate CRUD model
 */
function farme_generate_crud_model($name, $columns = []) {
    $filename = ucfirst($name) . '.php';
    $path = farme_config_get('app.paths.models') . '/' . $filename;
    
    if (file_exists($path)) {
        farme_console_warning("Model $filename already exists, skipping...");
        return;
    }
    
    $table = strtolower($name) . 's';
    $prefix = strtolower($name);
    
    // Process columns to generate fillable, casts, etc.
    $fillable = [];
    $casts = [];
    $primary_key = 'id';
    $has_timestamps = false;
    
    if (!empty($columns)) {
        foreach ($columns as $col) {
            $col_name = $col['name'];
            $col_type = strtolower($col['type']);
            
            // Skip auto-increment primary key and timestamps from fillable
            if ($col['key_type'] === 'PRI' && strpos($col['extra'], 'auto_increment') !== false) {
                $primary_key = $col_name;
                $casts[$col_name] = 'int';
                continue;
            }
            
            // Check for timestamps
            if (in_array($col_name, ['created_at', 'updated_at'])) {
                $has_timestamps = true;
                $casts[$col_name] = 'datetime';
                continue;
            }
            
            // Add to fillable fields
            $fillable[] = $col_name;
            
            // Set appropriate casts based on column type
            if (strpos($col_type, 'int') !== false || strpos($col_type, 'bigint') !== false) {
                $casts[$col_name] = 'int';
            } elseif (strpos($col_type, 'decimal') !== false || strpos($col_type, 'float') !== false || strpos($col_type, 'double') !== false) {
                $casts[$col_name] = 'float';
            } elseif (strpos($col_type, 'bool') !== false || strpos($col_type, 'tinyint(1)') !== false) {
                $casts[$col_name] = 'boolean';
            } elseif (strpos($col_type, 'json') !== false) {
                $casts[$col_name] = 'json';
            } elseif (strpos($col_type, 'date') !== false) {
                $casts[$col_name] = 'datetime';
            }
        }
    } else {
        // Fallback to default fields
        $fillable = ['name', 'email', 'status'];
        $casts = ['id' => 'int', 'status' => 'boolean'];
        $has_timestamps = true;
    }
    
    $template = "<?php\n\n/**\n * " . ucfirst($name) . " model functions\n */\n\n";
    $template .= "// Configure model with advanced features\n";
    $template .= "farme_model_configure('$prefix', [\n";
    $template .= "    'table' => '$table',\n";
    $template .= "    'primary_key' => '$primary_key',\n";
    $template .= "    'timestamps' => " . ($has_timestamps ? 'true' : 'false') . ",\n";
    $template .= "    'fillable' => ['" . implode("', '", $fillable) . "'],\n";
    $template .= "    'hidden' => [],\n";
    $template .= "    'casts' => [\n";
    foreach ($casts as $field => $cast) {
        $template .= "        '$field' => '$cast',\n";
    }
    $template .= "    ]\n";
    $template .= "]);\n\n";
    
    $template .= "function {$prefix}_find(\$id) {\n    return farme_find('$table', \$id);\n}\n\n";
    $template .= "function {$prefix}_all() {\n    return farme_find_all('$table');\n}\n\n";
    $template .= "function {$prefix}_paginate(\$page = 1, \$per_page = 15) {\n    return farme_model_paginate('$prefix', \$page, \$per_page);\n}\n\n";
    $template .= "function {$prefix}_create(\$data) {\n    return farme_insert('$table', \$data);\n}\n\n";
    $template .= "function {$prefix}_update(\$id, \$data) {\n    return farme_update('$table', \$data, \$id);\n}\n\n";
    $template .= "function {$prefix}_delete(\$id) {\n    return farme_delete('$table', \$id);\n}\n\n";
    $template .= "function {$prefix}_find_by_name(\$name) {\n    return farme_find_by('$table', 'name', \$name);\n}\n\n";
    $template .= "function {$prefix}_count(\$conditions = []) {\n    return farme_model_count('$prefix', \$conditions);\n}\n\n";
    $template .= "function {$prefix}_query() {\n    return farme_query('$table');\n}\n";
    
    file_put_contents($path, $template);
    farme_console_success("Model $filename created");
}

/**
 * Generate CRUD controller
 */
function farme_generate_crud_controller($name, $admin = false, $columns = []) {
    $lower_plural = strtolower($name) . 's';
    $filename = $admin ? 'Admin_' . $lower_plural . 'Controller.php' : ucfirst($name) . 'sController.php';
    $path = farme_config_get('app.paths.controllers') . '/' . $filename;
    
    if (file_exists($path)) {
        farme_console_warning("Controller $filename already exists, skipping...");
        return;
    }
    
    $prefix = strtolower($name);
    $lower_plural = strtolower($name) . 's';
    $route_prefix = $admin ? '/admin-' . $lower_plural : '/' . $lower_plural;
    $view_prefix = $admin ? 'admin/' . $lower_plural : $lower_plural;
    
    $template = "<?php\n\n/**\n * " . ucfirst($name) . " Controller\n */\n\n";
    
    // Index - List all
    $template .= "/**\n * @route GET $route_prefix\n */\n";
    $template .= "function " . ($admin ? "admin_{$lower_plural}_index" : "{$lower_plural}_index") . "(\$params = []) {\n";
    if ($admin) {
        $template .= "    farme_require_auth();\n";
        $template .= "    farme_require_admin();\n\n";
    }
    $template .= "    \$page = (int)(\$_GET['page'] ?? 1);\n";
    $template .= "    \$pagination = {$prefix}_paginate(\$page, 15);\n";
    $template .= "    \$current_page = \$pagination['current_page'];\n\n";
    $template .= "    return farme_render('$view_prefix/index', [\n";
    $template .= "        'title' => '" . ucfirst($name) . "s',\n";
    $template .= "        '{$lower_plural}' => \$pagination['data'],\n";
    $template .= "        'pagination' => \$pagination,\n";
    $template .= "        'current_page' => \$current_page\n";
    $template .= "    ]" . ($admin ? ", 'admin'" : "") . ");\n}\n\n";
    
    // Show - View single
    $template .= "/**\n * @route GET $route_prefix/{id}\n */\n";
    $template .= "function " . ($admin ? "admin_{$lower_plural}_show" : "{$lower_plural}_show") . "(\$params = []) {\n";
    if ($admin) {
        $template .= "    farme_require_auth();\n";
        $template .= "    farme_require_admin();\n\n";
    }
    $template .= "    \${$prefix} = {$prefix}_find(\$params['id']);\n";
    $template .= "    \n";
    $template .= "    if (!\${$prefix}) {\n";
    $template .= "        return farme_not_found();\n";
    $template .= "    }\n\n";
    $template .= "    return farme_render('$view_prefix/show', [\n";
    $template .= "        'title' => \${$prefix}['name'] ?? 'View " . ucfirst($name) . "',\n";
    $template .= "        '{$prefix}' => \${$prefix}\n";
    $template .= "    ]" . ($admin ? ", 'admin'" : "") . ");\n}\n\n";
    
    // Create - Show form
    $template .= "/**\n * @route GET $route_prefix-create\n */\n";
    $template .= "function " . ($admin ? "admin_{$lower_plural}_create" : "{$lower_plural}_create") . "(\$params = []) {\n";
    if ($admin) {
        $template .= "    farme_require_auth();\n";
        $template .= "    farme_require_admin();\n\n";
    }
    $template .= "    return farme_render('$view_prefix/create', [\n";
    $template .= "        'title' => 'Create " . ucfirst($name) . "',\n";
    $template .= "        'csrf_token' => farme_csrf_token()\n";
    $template .= "    ]" . ($admin ? ", 'admin'" : "") . ");\n}\n\n";
    
    // Store - Handle create  
    $template .= "/**\n * @route POST $route_prefix-create\n */\n";
    $template .= "function " . ($admin ? "admin_{$lower_plural}_store" : "{$lower_plural}_store") . "(\$params = []) {\n";
    if ($admin) {
        $template .= "    farme_require_auth();\n";
        $template .= "    farme_require_admin();\n\n";
    }
    $template .= "    farme_verify_csrf();\n\n";
    
    // Generate dynamic data extraction based on columns
    $template .= "    \$data = [];\n";
    if (!empty($columns)) {
        foreach ($columns as $col) {
            $col_name = $col['name'];
            $col_type = strtolower($col['type']);
            
            // Skip auto-increment primary key and timestamps
            if ($col['key_type'] === 'PRI' && strpos($col['extra'], 'auto_increment') !== false) {
                continue;
            }
            if (in_array($col_name, ['created_at', 'updated_at'])) {
                continue;
            }
            
            if (strpos($col_type, 'bool') !== false || strpos($col_type, 'tinyint(1)') !== false) {
                $template .= "    \$data['$col_name'] = isset(\$_POST['$col_name']) ? (bool)\$_POST['$col_name'] : false;\n";
            } elseif (strpos($col_type, 'int') !== false) {
                $template .= "    \$data['$col_name'] = (int)(\$_POST['$col_name'] ?? 0);\n";
            } elseif (strpos($col_type, 'decimal') !== false || strpos($col_type, 'float') !== false) {
                $template .= "    \$data['$col_name'] = (float)(\$_POST['$col_name'] ?? 0.0);\n";
            } else {
                $template .= "    \$data['$col_name'] = \$_POST['$col_name'] ?? '';\n";
            }
        }
    } else {
        // Fallback to default fields
        $template .= "    \$data['name'] = \$_POST['name'] ?? '';\n";
        $template .= "    \$data['email'] = \$_POST['email'] ?? '';\n";
        $template .= "    \$data['status'] = isset(\$_POST['status']) ? (bool)\$_POST['status'] : true;\n";
    }
    
    $template .= "\n    // Basic validation\n";
    if (!empty($columns)) {
        // Find the first non-auto-increment, non-timestamp varchar/text field for validation
        $first_text_field = null;
        foreach ($columns as $col) {
            if ($col['key_type'] !== 'PRI' && !in_array($col['name'], ['created_at', 'updated_at'])) {
                $col_type = strtolower($col['type']);
                if (strpos($col_type, 'varchar') !== false || strpos($col_type, 'text') !== false || strpos($col_type, 'char') !== false) {
                    $first_text_field = $col['name'];
                    break;
                }
            }
        }
        if ($first_text_field) {
            $template .= "    if (empty(\$data['$first_text_field'])) {\n";
            $template .= "        farme_flash_error('" . ucfirst(str_replace('_', ' ', $first_text_field)) . " is required');\n";
            $template .= "        return farme_redirect('$route_prefix-create');\n";
            $template .= "    }\n\n";
        }
    } else {
        $template .= "    if (empty(\$data['name'])) {\n";
        $template .= "        farme_flash_error('Name is required');\n";
        $template .= "        return farme_redirect('$route_prefix-create');\n";
        $template .= "    }\n\n";
    }
    $template .= "    \${$prefix} = {$prefix}_create(\$data);\n";
    $template .= "    \n";
    $template .= "    farme_flash_success('" . ucfirst($name) . " created successfully!');\n";
    $template .= "    return farme_redirect('$route_prefix');\n}\n\n";
    
    // Edit - Show edit form
    $template .= "/**\n * @route GET $route_prefix/{id}/edit\n */\n";
    $template .= "function " . ($admin ? "admin_{$lower_plural}_edit" : "{$lower_plural}_edit") . "(\$params = []) {\n";
    if ($admin) {
        $template .= "    farme_require_auth();\n";
        $template .= "    farme_require_admin();\n\n";
    }
    $template .= "    \${$prefix} = {$prefix}_find(\$params['id']);\n";
    $template .= "    \n";
    $template .= "    if (!\${$prefix}) {\n";
        $template .= "        return farme_not_found();\n";
    $template .= "    }\n\n";
    $template .= "    return farme_render('$view_prefix/edit', [\n";
    $template .= "        'title' => 'Edit " . ucfirst($name) . "',\n";
    $template .= "        '{$prefix}' => \${$prefix},\n";
    $template .= "        'csrf_token' => farme_csrf_token()\n";
    $template .= "    ]" . ($admin ? ", 'admin'" : "") . ");\n}\n\n";
    
    // Update - Handle edit
    $template .= "/**\n * @route POST $route_prefix/{id}/edit\n */\n";
    $template .= "function " . ($admin ? "admin_{$lower_plural}_update" : "{$lower_plural}_update") . "(\$params = []) {\n";
    if ($admin) {
        $template .= "    farme_require_auth();\n";
        $template .= "    farme_require_admin();\n\n";
    }
    $template .= "    farme_verify_csrf();\n\n";
    $template .= "    \${$prefix} = {$prefix}_find(\$params['id']);\n";
    $template .= "    \n";
    $template .= "    if (!\${$prefix}) {\n";
    $template .= "        return farme_not_found();\n";
    $template .= "    }\n\n";
    // Generate dynamic data extraction based on columns
    $template .= "    \$data = [];\n";
    if (!empty($columns)) {
        foreach ($columns as $col) {
            $col_name = $col['name'];
            $col_type = strtolower($col['type']);
            
            // Skip auto-increment primary key and timestamps
            if ($col['key_type'] === 'PRI' && strpos($col['extra'], 'auto_increment') !== false) {
                continue;
            }
            if (in_array($col_name, ['created_at', 'updated_at'])) {
                continue;
            }
            
            if (strpos($col_type, 'bool') !== false || strpos($col_type, 'tinyint(1)') !== false) {
                $template .= "    \$data['$col_name'] = isset(\$_POST['$col_name']) ? (bool)\$_POST['$col_name'] : false;\n";
            } elseif (strpos($col_type, 'int') !== false) {
                $template .= "    \$data['$col_name'] = (int)(\$_POST['$col_name'] ?? 0);\n";
            } elseif (strpos($col_type, 'decimal') !== false || strpos($col_type, 'float') !== false) {
                $template .= "    \$data['$col_name'] = (float)(\$_POST['$col_name'] ?? 0.0);\n";
            } else {
                $template .= "    \$data['$col_name'] = \$_POST['$col_name'] ?? '';\n";
            }
        }
    } else {
        // Fallback to default fields
        $template .= "    \$data['name'] = \$_POST['name'] ?? '';\n";
        $template .= "    \$data['email'] = \$_POST['email'] ?? '';\n";
        $template .= "    \$data['status'] = isset(\$_POST['status']) ? (bool)\$_POST['status'] : true;\n";
    }
    
    $template .= "\n    // Basic validation\n";
    if (!empty($columns)) {
        // Find the first non-auto-increment, non-timestamp varchar/text field for validation
        $first_text_field = null;
        foreach ($columns as $col) {
            if ($col['key_type'] !== 'PRI' && !in_array($col['name'], ['created_at', 'updated_at'])) {
                $col_type = strtolower($col['type']);
                if (strpos($col_type, 'varchar') !== false || strpos($col_type, 'text') !== false || strpos($col_type, 'char') !== false) {
                    $first_text_field = $col['name'];
                    break;
                }
            }
        }
        if ($first_text_field) {
            $template .= "    if (empty(\$data['$first_text_field'])) {\n";
            $template .= "        farme_flash_error('" . ucfirst(str_replace('_', ' ', $first_text_field)) . " is required');\n";
            $template .= "        return farme_redirect('$route_prefix/{\$params[\"id\"]}/edit');\n";
            $template .= "    }\n\n";
        }
    } else {
        $template .= "    if (empty(\$data['name'])) {\n";
        $template .= "        farme_flash_error('Name is required');\n";
        $template .= "        return farme_redirect('$route_prefix/{\$params[\"id\"]}/edit');\n";
        $template .= "    }\n\n";
    }
    $template .= "    {$prefix}_update(\$params['id'], \$data);\n";
    $template .= "    \n";
    $template .= "    farme_flash_success('" . ucfirst($name) . " updated successfully!');\n";
    $template .= "    return farme_redirect('$route_prefix');\n}\n\n";
    
    // Delete - Handle delete
    $template .= "/**\n * @route POST $route_prefix/{id}/delete\n */\n";
    $template .= "function " . ($admin ? "admin_{$lower_plural}_destroy" : "{$lower_plural}_destroy") . "(\$params = []) {\n";
    if ($admin) {
        $template .= "    farme_require_auth();\n";
        $template .= "    farme_require_admin();\n\n";
    }
    $template .= "    farme_verify_csrf();\n\n";
    $template .= "    \${$prefix} = {$prefix}_find(\$params['id']);\n";
    $template .= "    \n";
    $template .= "    if (!\${$prefix}) {\n";
    $template .= "        return farme_not_found();\n";
    $template .= "    }\n\n";
    $template .= "    {$prefix}_delete(\$params['id']);\n";
    $template .= "    \n";
    $template .= "    farme_flash_success('" . ucfirst($name) . " deleted successfully!');\n";
    $template .= "    return farme_redirect('$route_prefix');\n}\n";
    
    file_put_contents($path, $template);
    farme_console_success("Controller $filename created");
}

/**
 * Generate CRUD views
 */
function farme_generate_crud_views($name, $admin = false, $columns = []) {
    $lower_plural = strtolower($name) . 's';
    $prefix = strtolower($name);
    $view_dir = farme_config_get('app.paths.templates') . '/' . ($admin ? 'admin/' . $lower_plural : $lower_plural);
    
    // Create directory
    if (!is_dir($view_dir)) {
        mkdir($view_dir, 0755, true);
    }
    
    farme_generate_index_view($view_dir, $name, $prefix, $admin, $columns);
    farme_generate_show_view($view_dir, $name, $prefix, $admin, $columns);
    farme_generate_create_view($view_dir, $name, $prefix, $admin, $columns);
    farme_generate_edit_view($view_dir, $name, $prefix, $admin, $columns);
}

/**
 * Generate index view
 */
function farme_generate_index_view($dir, $name, $prefix, $admin, $columns = []) {
    $lower_plural = strtolower($name) . 's';
    $route_prefix = $admin ? '/admin-' . $lower_plural : '/' . $lower_plural;
    
    $template = $admin ? 
        "<div class=\"admin-page-header\">\n    <h1 class=\"admin-page-title\">" . ucfirst($name) . "s</h1>\n    <p class=\"admin-page-subtitle\">Manage " . strtolower($name) . " records</p>\n</div>\n\n<div class=\"admin-card\">\n    <div class=\"admin-card-header\" style=\"display: flex; justify-content: space-between; align-items: center;\">\n        <h3 class=\"admin-card-title\">All " . ucfirst($name) . "s</h3>\n        <div style=\"display: flex; align-items: center; gap: 1rem;\">\n            <span style=\"color: #6c757d;\">\n                Total: <?= \$pagination['total'] ?? count(\${$lower_plural}) ?> records\n            </span>\n            <a href=\"$route_prefix-create\" class=\"btn btn-success\" style=\"font-size: 0.9rem;\">Create " . ucfirst($name) . "</a>\n        </div>\n    </div>\n    <div class=\"admin-card-body\">"
        :
        "<h1>" . ucfirst($name) . "s</h1>\n\n<div style=\"display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;\">\n    <p>Manage your " . strtolower($name) . " records</p>\n    <a href=\"$route_prefix-create\" class=\"btn btn-success\">Create " . ucfirst($name) . "</a>\n</div>\n\n<div>";
    
    $template .= "\n        <?php if (!empty(\${$lower_plural})): ?>\n";
    $template .= "            <table class=\"table\">\n";
    $template .= "                <thead>\n";
    $template .= "                    <tr>\n";
    
    // Generate dynamic table headers based on columns
    if (!empty($columns)) {
        // Only show the first 5-6 most relevant columns to avoid overcrowding
        $display_columns = [];
        $primary_key_col = null;
        
        foreach ($columns as $col) {
            if ($col['key_type'] === 'PRI') {
                $primary_key_col = $col['name'];
                $display_columns[] = $col;
            } elseif (!in_array($col['name'], ['created_at', 'updated_at'])) {
                $display_columns[] = $col;
            }
        }
        
        // Add created_at back if we have space and it exists
        if (count($display_columns) < 5) {
            foreach ($columns as $col) {
                if ($col['name'] === 'created_at') {
                    $display_columns[] = $col;
                    break;
                }
            }
        }
        
        // Limit to first 5 columns to keep table manageable
        $display_columns = array_slice($display_columns, 0, 5);
        
        foreach ($display_columns as $col) {
            $header = ucfirst(str_replace('_', ' ', $col['name']));
            $template .= "                        <th>$header</th>\n";
        }
    } else {
        // Fallback headers
        $template .= "                        <th>ID</th>\n";
        $template .= "                        <th>Name</th>\n";
        $template .= "                        <th>Email</th>\n";
        $template .= "                        <th>Status</th>\n";
        $template .= "                        <th>Created</th>\n";
    }
    
    $template .= "                        <th>Actions</th>\n";
    $template .= "                    </tr>\n";
    $template .= "                </thead>\n";
    $template .= "                <tbody>\n";
    $template .= "                    <?php foreach (\${$lower_plural} as \${$prefix}): ?>\n";
    $template .= "                        <tr>\n";
    
    // Generate dynamic table cells based on columns
    if (!empty($columns)) {
        foreach ($display_columns as $col) {
            $col_name = $col['name'];
            $col_type = strtolower($col['type']);
            
            $template .= "                            <td>\n";
            
            if (strpos($col_type, 'bool') !== false || strpos($col_type, 'tinyint(1)') !== false) {
                // Boolean field - show as badge
                $template .= "                                <span class=\"badge badge-<?= \${$prefix}['$col_name'] ? 'success' : 'secondary' ?>\">\n";
                $template .= "                                    <?= \${$prefix}['$col_name'] ? 'Yes' : 'No' ?>\n";
                $template .= "                                </span>\n";
            } elseif (strpos($col_type, 'date') !== false) {
                // Date field - format nicely
                if (strpos($col_type, 'datetime') !== false || strpos($col_type, 'timestamp') !== false) {
                    $template .= "                                <?= isset(\${$prefix}['$col_name']) ? date('M j, Y g:i A', strtotime(\${$prefix}['$col_name'])) : 'Unknown' ?>\n";
                } else {
                    $template .= "                                <?= isset(\${$prefix}['$col_name']) ? date('M j, Y', strtotime(\${$prefix}['$col_name'])) : 'Unknown' ?>\n";
                }
            } elseif (strpos($col_type, 'text') !== false && $col_name !== 'id') {
                // Text field - truncate if too long
                $template .= "                                <?= farme_escape(strlen(\${$prefix}['$col_name'] ?? '') > 50 ? substr(\${$prefix}['$col_name'], 0, 50) . '...' : (\${$prefix}['$col_name'] ?? '')) ?>\n";
            } elseif (strpos($col_name, 'email') !== false) {
                // Email field - make it a mailto link
                $template .= "                                <a href=\"mailto:<?= \${$prefix}['$col_name'] ?>\"><?= farme_escape(\${$prefix}['$col_name']) ?></a>\n";
            } else {
                // Default field display
                $template .= "                                <?= farme_escape(\${$prefix}['$col_name'] ?? '') ?>\n";
            }
            
            $template .= "                            </td>\n";
        }
    } else {
        // Fallback cells
        $template .= "                            <td><?= \${$prefix}['id'] ?></td>\n";
        $template .= "                            <td><?= farme_escape(\${$prefix}['name']) ?></td>\n";
        $template .= "                            <td><?= farme_escape(\${$prefix}['email']) ?></td>\n";
        $template .= "                            <td>\n";
        $template .= "                                <span class=\"badge badge-<?= \${$prefix}['status'] ? 'success' : 'secondary' ?>\">\n";
        $template .= "                                    <?= \${$prefix}['status'] ? 'Active' : 'Inactive' ?>\n";
        $template .= "                                </span>\n";
        $template .= "                            </td>\n";
        $template .= "                            <td>\n";
        $template .= "                                <?= isset(\${$prefix}['created_at']) ? date('M j, Y', strtotime(\${$prefix}['created_at'])) : 'Unknown' ?>\n";
        $template .= "                            </td>\n";
    }
    
    $template .= "                            <td>\n";
    $template .= "                                <div style=\"display: flex; gap: 0.5rem;\">\n";
    
    // Use primary key for URLs (might not always be 'id')
    $pk_field = 'id';
    if (!empty($columns)) {
        foreach ($columns as $col) {
            if ($col['key_type'] === 'PRI') {
                $pk_field = $col['name'];
                break;
            }
        }
    }
    
    $template .= "                                    <a href=\"$route_prefix/<?= \${$prefix}['$pk_field'] ?>\" class=\"btn btn-primary\" style=\"font-size: 0.8rem; padding: 0.25rem 0.5rem;\">View</a>\n";
    $template .= "                                    <a href=\"$route_prefix/<?= \${$prefix}['$pk_field'] ?>/edit\" class=\"btn btn-success\" style=\"font-size: 0.8rem; padding: 0.25rem 0.5rem;\">Edit</a>\n";
    $template .= "                                    <form method=\"POST\" action=\"$route_prefix/<?= \${$prefix}['$pk_field'] ?>/delete\" style=\"display: inline;\"\n";
    $template .= "                                          onsubmit=\"return confirm('Are you sure you want to delete this " . strtolower($name) . "?')\">\n";
    $template .= "                                        <input type=\"hidden\" name=\"_token\" value=\"<?= farme_csrf_token() ?>\">\n";
    $template .= "                                        <button type=\"submit\" class=\"btn btn-danger\" style=\"font-size: 0.8rem; padding: 0.25rem 0.5rem;\">Delete</button>\n";
    $template .= "                                    </form>\n";
    $template .= "                                </div>\n";
    $template .= "                            </td>\n";
    $template .= "                        </tr>\n";
    $template .= "                    <?php endforeach; ?>\n";
    $template .= "                </tbody>\n";
    $template .= "            </table>\n";
    
    // Pagination
    $template .= "            \n";
    $template .= "            <!-- Pagination -->\n";
    $template .= "            <?php if (isset(\$pagination) && \$pagination['last_page'] > 1): ?>\n";
    $template .= "                <div style=\"display: flex; justify-content: center; align-items: center; margin-top: 2rem; gap: 1rem;\">\n";
    $template .= "                    <?php if (\$current_page > 1): ?>\n";
    $template .= "                        <a href=\"$route_prefix?page=<?= \$current_page - 1 ?>\" class=\"btn btn-secondary\">Previous</a>\n";
    $template .= "                    <?php endif; ?>\n";
    $template .= "                    \n";
    $template .= "                    <span style=\"color: #6c757d;\">\n";
    $template .= "                        Page <?= \$current_page ?> of <?= \$pagination['last_page'] ?>\n";
    $template .= "                        (<?= \$pagination['total'] ?> total records)\n";
    $template .= "                    </span>\n";
    $template .= "                    \n";
    $template .= "                    <?php if (\$current_page < \$pagination['last_page']): ?>\n";
    $template .= "                        <a href=\"$route_prefix?page=<?= \$current_page + 1 ?>\" class=\"btn btn-secondary\">Next</a>\n";
    $template .= "                    <?php endif; ?>\n";
    $template .= "                </div>\n";
    $template .= "            <?php endif; ?>\n";
    
    $template .= "        <?php else: ?>\n";
    $template .= "            <div style=\"text-align: center; padding: 3rem;\">\n";
    $template .= "                <p style=\"color: #6c757d; font-size: 1.1rem;\">No " . strtolower($name) . "s found.</p>\n";
    $template .= "                <p style=\"color: #6c757d;\">Records will appear here once they are created.</p>\n";
    $template .= "            </div>\n";
    $template .= "        <?php endif; ?>\n";
    
    $template .= $admin ? "    </div>\n</div>" : "</div>";
    
    file_put_contents($dir . '/index.php', $template);
    farme_console_success("View index.php created");
}

/**
 * Generate show view
 */
function farme_generate_show_view($dir, $name, $prefix, $admin, $columns = []) {
    $lower_plural = strtolower($name) . 's';
    $route_prefix = $admin ? '/admin-' . $lower_plural : '/' . $lower_plural;
    
    // For admin subtitle, try to find a suitable display field
    $display_field = 'name';
    if (!empty($columns)) {
        foreach ($columns as $col) {
            $col_type = strtolower($col['type']);
            if ($col['key_type'] !== 'PRI' && !in_array($col['name'], ['created_at', 'updated_at']) && 
                (strpos($col_type, 'varchar') !== false || strpos($col_type, 'text') !== false)) {
                $display_field = $col['name'];
                break;
            }
        }
    }
    
    $template = $admin ? 
        "<div class=\"admin-page-header\">\n    <h1 class=\"admin-page-title\">View " . ucfirst($name) . "</h1>\n    <p class=\"admin-page-subtitle\"><?= farme_escape(\${$prefix}['$display_field'] ?? 'Record Details') ?></p>\n</div>\n\n<div class=\"admin-card\">\n    <div class=\"admin-card-header\">\n        <h3 class=\"admin-card-title\">" . ucfirst($name) . " Details</h3>\n    </div>\n    <div class=\"admin-card-body\">"
        :
        "<h1>View " . ucfirst($name) . "</h1>\n\n<div>";
    
    $template .= "\n        <table style=\"width: 100%;\">\n";
    
    // Generate dynamic fields based on actual table columns
    if (!empty($columns)) {
        $row_count = 0;
        foreach ($columns as $col) {
            $col_name = $col['name'];
            $col_type = strtolower($col['type']);
            $label = ucfirst(str_replace('_', ' ', $col_name));
            
            // Add border except for last row
            $border_style = ($row_count < count($columns) - 1) ? ' style="border-bottom: 1px solid #dee2e6;"' : '';
            
            $template .= "            <tr$border_style>\n";
            $template .= "                <td style=\"padding: 0.75rem 0; font-weight: 600; color: #495057; width: 30%;\">$label</td>\n";
            $template .= "                <td style=\"padding: 0.75rem 0;\">\n";
            
            if (strpos($col_type, 'bool') !== false || strpos($col_type, 'tinyint(1)') !== false) {
                // Boolean field - show as badge
                $template .= "                    <span class=\"badge badge-<?= \${$prefix}['$col_name'] ? 'success' : 'secondary' ?>\">\n";
                $template .= "                        <?= \${$prefix}['$col_name'] ? 'Yes' : 'No' ?>\n";
                $template .= "                    </span>\n";
            } elseif (strpos($col_type, 'date') !== false) {
                // Date field - format nicely
                if (strpos($col_type, 'datetime') !== false || strpos($col_type, 'timestamp') !== false) {
                    $template .= "                    <?= isset(\${$prefix}['$col_name']) && \${$prefix}['$col_name'] ? date('M j, Y g:i A', strtotime(\${$prefix}['$col_name'])) : 'Not set' ?>\n";
                } else {
                    $template .= "                    <?= isset(\${$prefix}['$col_name']) && \${$prefix}['$col_name'] ? date('M j, Y', strtotime(\${$prefix}['$col_name'])) : 'Not set' ?>\n";
                }
            } elseif (strpos($col_name, 'email') !== false) {
                // Email field - make it a mailto link
                $template .= "                    <?php if (!empty(\${$prefix}['$col_name'])): ?>\n";
                $template .= "                        <a href=\"mailto:<?= \${$prefix}['$col_name'] ?>\"><?= farme_escape(\${$prefix}['$col_name']) ?></a>\n";
                $template .= "                    <?php else: ?>\n";
                $template .= "                        <span style=\"color: #6c757d;\">Not provided</span>\n";
                $template .= "                    <?php endif; ?>\n";
            } elseif (strpos($col_name, 'url') !== false || strpos($col_name, 'website') !== false || strpos($col_name, 'link') !== false) {
                // URL field - make it a link
                $template .= "                    <?php if (!empty(\${$prefix}['$col_name'])): ?>\n";
                $template .= "                        <a href=\"<?= \${$prefix}['$col_name'] ?>\" target=\"_blank\" rel=\"noopener\"><?= farme_escape(\${$prefix}['$col_name']) ?></a>\n";
                $template .= "                    <?php else: ?>\n";
                $template .= "                        <span style=\"color: #6c757d;\">Not provided</span>\n";
                $template .= "                    <?php endif; ?>\n";
            } elseif (strpos($col_type, 'text') !== false) {
                // Text field - preserve line breaks
                $template .= "                    <?php if (!empty(\${$prefix}['$col_name'])): ?>\n";
                $template .= "                        <div style=\"white-space: pre-wrap;\"><?= farme_escape(\${$prefix}['$col_name']) ?></div>\n";
                $template .= "                    <?php else: ?>\n";
                $template .= "                        <span style=\"color: #6c757d;\">Not provided</span>\n";
                $template .= "                    <?php endif; ?>\n";
            } elseif (strpos($col_type, 'json') !== false) {
                // JSON field - format nicely
                $template .= "                    <?php if (!empty(\${$prefix}['$col_name'])): ?>\n";
                $template .= "                        <pre style=\"background: #f8f9fa; padding: 0.5rem; border-radius: 0.25rem; font-size: 0.9rem; margin: 0;\"><?= farme_escape(json_encode(json_decode(\${$prefix}['$col_name']), JSON_PRETTY_PRINT)) ?></pre>\n";
                $template .= "                    <?php else: ?>\n";
                $template .= "                        <span style=\"color: #6c757d;\">No data</span>\n";
                $template .= "                    <?php endif; ?>\n";
            } else {
                // Default field display
                $template .= "                    <?php if (isset(\${$prefix}['$col_name']) && \${$prefix}['$col_name'] !== '' && \${$prefix}['$col_name'] !== null): ?>\n";
                $template .= "                        <?= farme_escape(\${$prefix}['$col_name']) ?>\n";
                $template .= "                    <?php else: ?>\n";
                $template .= "                        <span style=\"color: #6c757d;\">Not set</span>\n";
                $template .= "                    <?php endif; ?>\n";
            }
            
            $template .= "                </td>\n";
            $template .= "            </tr>\n";
            $row_count++;
        }
    } else {
        // Fallback for when no columns are available
        $template .= "            <tr style=\"border-bottom: 1px solid #dee2e6;\">\n";
        $template .= "                <td style=\"padding: 0.75rem 0; font-weight: 600; color: #495057; width: 30%;\">ID</td>\n";
        $template .= "                <td style=\"padding: 0.75rem 0;\"><?= \${$prefix}['id'] ?></td>\n";
        $template .= "            </tr>\n";
        $template .= "            <tr style=\"border-bottom: 1px solid #dee2e6;\">\n";
        $template .= "                <td style=\"padding: 0.75rem 0; font-weight: 600; color: #495057;\">Name</td>\n";
        $template .= "                <td style=\"padding: 0.75rem 0;\"><?= farme_escape(\${$prefix}['name']) ?></td>\n";
        $template .= "            </tr>\n";
        $template .= "            <tr style=\"border-bottom: 1px solid #dee2e6;\">\n";
        $template .= "                <td style=\"padding: 0.75rem 0; font-weight: 600; color: #495057;\">Email</td>\n";
        $template .= "                <td style=\"padding: 0.75rem 0;\"><?= farme_escape(\${$prefix}['email']) ?></td>\n";
        $template .= "            </tr>\n";
        $template .= "            <tr>\n";
        $template .= "                <td style=\"padding: 0.75rem 0; font-weight: 600; color: #495057;\">Status</td>\n";
        $template .= "                <td style=\"padding: 0.75rem 0;\">\n";
        $template .= "                    <span class=\"badge badge-<?= \${$prefix}['status'] ? 'success' : 'secondary' ?>\">\n";
        $template .= "                        <?= \${$prefix}['status'] ? 'Active' : 'Inactive' ?>\n";
        $template .= "                    </span>\n";
        $template .= "                </td>\n";
        $template .= "            </tr>\n";
    }
    
    $template .= "        </table>\n";
    $template .= "        \n";
    $template .= "        <div style=\"display: flex; gap: 1rem; margin-top: 2rem;\">\n";
    
    // Use primary key for URLs (might not always be 'id')
    $pk_field = 'id';
    if (!empty($columns)) {
        foreach ($columns as $col) {
            if ($col['key_type'] === 'PRI') {
                $pk_field = $col['name'];
                break;
            }
        }
    }
    
    $template .= "            <a href=\"$route_prefix/<?= \${$prefix}['$pk_field'] ?>/edit\" class=\"btn btn-success\">Edit</a>\n";
    $template .= "            <a href=\"$route_prefix\" class=\"btn btn-secondary\">Back to List</a>\n";
    $template .= "            <form method=\"POST\" action=\"$route_prefix/<?= \${$prefix}['$pk_field'] ?>/delete\" style=\"display: inline;\"\n";
    $template .= "                  onsubmit=\"return confirm('Are you sure you want to delete this " . strtolower($name) . "?')\">\n";
    $template .= "                <input type=\"hidden\" name=\"_token\" value=\"<?= farme_csrf_token() ?>\">\n";
    $template .= "                <button type=\"submit\" class=\"btn btn-danger\">Delete</button>\n";
    $template .= "            </form>\n";
    $template .= "        </div>\n";
    
    $template .= $admin ? "    </div>\n</div>" : "</div>";
    
    file_put_contents($dir . '/show.php', $template);
    farme_console_success("View show.php created");
}

/**
 * Generate create view
 */
function farme_generate_create_view($dir, $name, $prefix, $admin, $columns = []) {
    $lower_plural = strtolower($name) . 's';
    $route_prefix = $admin ? '/admin-' . $lower_plural : '/' . $lower_plural;
    
    $template = $admin ? 
        "<div class=\"admin-page-header\">\n    <h1 class=\"admin-page-title\">Create " . ucfirst($name) . "</h1>\n    <p class=\"admin-page-subtitle\">Add new " . strtolower($name) . " record</p>\n</div>\n\n<div class=\"admin-card\">\n    <div class=\"admin-card-header\">\n        <h3 class=\"admin-card-title\">" . ucfirst($name) . " Information</h3>\n    </div>\n    <div class=\"admin-card-body\">"
        :
        "<h1>Create " . ucfirst($name) . "</h1>\n\n<div>";
    
    $template .= "\n        <form method=\"POST\" action=\"$route_prefix-create\">\n";
    $template .= "            <input type=\"hidden\" name=\"_token\" value=\"<?= \$csrf_token ?>\">\n";
    $template .= "            \n";
    
    // Generate form fields based on table columns
    if (!empty($columns)) {
        foreach ($columns as $col) {
            $col_name = $col['name'];
            $col_type = strtolower($col['type']);
            
            // Skip auto-increment primary key and timestamps
            if ($col['key_type'] === 'PRI' && strpos($col['extra'], 'auto_increment') !== false) {
                continue;
            }
            if (in_array($col_name, ['created_at', 'updated_at'])) {
                continue;
            }
            
            $label = ucfirst(str_replace('_', ' ', $col_name));
            $required = ($col['nullable'] === 'NO') ? ' *' : '';
            $required_attr = ($col['nullable'] === 'NO') ? ' required' : '';
            
            $template .= "            <div class=\"form-group\">\n";
            $template .= "                <label class=\"form-label\">$label$required</label>\n";
            
            if (strpos($col_type, 'bool') !== false || strpos($col_type, 'tinyint(1)') !== false) {
                // Boolean field - checkbox or select
                $template .= "                <select name=\"$col_name\" class=\"form-control\">\n";
                $template .= "                    <option value=\"1\" <?= (\$_POST['$col_name'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>\n";
                $template .= "                    <option value=\"0\" <?= (\$_POST['$col_name'] ?? '') === '0' ? 'selected' : '' ?>>No</option>\n";
                $template .= "                </select>\n";
            } elseif (strpos($col_type, 'text') !== false || (strpos($col_type, 'varchar') !== false && $col['max_length'] > 255)) {
                // Large text field - textarea
                $template .= "                <textarea name=\"$col_name\" class=\"form-control\" rows=\"4\"$required_attr\n";
                $template .= "                          placeholder=\"Enter $label\"><?= farme_escape(\$_POST['$col_name'] ?? '') ?></textarea>\n";
            } elseif (strpos($col_name, 'email') !== false) {
                // Email field
                $template .= "                <input type=\"email\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\$_POST['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            } elseif (strpos($col_name, 'password') !== false) {
                // Password field
                $template .= "                <input type=\"password\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            } elseif (strpos($col_type, 'int') !== false || strpos($col_type, 'bigint') !== false) {
                // Number field
                $template .= "                <input type=\"number\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\$_POST['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            } elseif (strpos($col_type, 'decimal') !== false || strpos($col_type, 'float') !== false || strpos($col_type, 'double') !== false) {
                // Decimal number field
                $template .= "                <input type=\"number\" step=\"0.01\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\$_POST['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            } elseif (strpos($col_type, 'date') !== false) {
                // Date field
                if (strpos($col_type, 'datetime') !== false || strpos($col_type, 'timestamp') !== false) {
                    $template .= "                <input type=\"datetime-local\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                } else {
                    $template .= "                <input type=\"date\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                }
                $template .= "                       value=\"<?= farme_escape(\$_POST['$col_name'] ?? '') ?>\">\n";
            } else {
                // Default text field
                $template .= "                <input type=\"text\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\$_POST['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            }
            
            $template .= "            </div>\n";
            $template .= "            \n";
        }
    } else {
        // Fallback to default fields if no columns available
        $template .= "            <div class=\"form-group\">\n";
        $template .= "                <label class=\"form-label\">Name *</label>\n";
        $template .= "                <input type=\"text\" name=\"name\" class=\"form-control\" required\n";
        $template .= "                       value=\"<?= farme_escape(\$_POST['name'] ?? '') ?>\"\n";
        $template .= "                       placeholder=\"Enter " . strtolower($name) . " name\">\n";
        $template .= "            </div>\n";
        $template .= "            \n";
        $template .= "            <div class=\"form-group\">\n";
        $template .= "                <label class=\"form-label\">Email *</label>\n";
        $template .= "                <input type=\"email\" name=\"email\" class=\"form-control\" required\n";
        $template .= "                       value=\"<?= farme_escape(\$_POST['email'] ?? '') ?>\"\n";
        $template .= "                       placeholder=\"Enter email address\">\n";
        $template .= "            </div>\n";
        $template .= "            \n";
        $template .= "            <div class=\"form-group\">\n";
        $template .= "                <label class=\"form-label\">Status</label>\n";
        $template .= "                <select name=\"status\" class=\"form-control\">\n";
        $template .= "                    <option value=\"1\" <?= (\$_POST['status'] ?? '1') === '1' ? 'selected' : '' ?>>Active</option>\n";
        $template .= "                    <option value=\"0\" <?= (\$_POST['status'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>\n";
        $template .= "                </select>\n";
        $template .= "            </div>\n";
        $template .= "            \n";
    }
    $template .= "            <div style=\"display: flex; gap: 1rem; margin-top: 2rem;\">\n";
    $template .= "                <button type=\"submit\" class=\"btn btn-success\">Create " . ucfirst($name) . "</button>\n";
    $template .= "                <a href=\"$route_prefix\" class=\"btn btn-secondary\">Cancel</a>\n";
    $template .= "            </div>\n";
    $template .= "        </form>\n";
    
    $template .= $admin ? "    </div>\n</div>" : "</div>";
    
    file_put_contents($dir . '/create.php', $template);
    farme_console_success("View create.php created");
}

/**
 * Generate edit view
 */
function farme_generate_edit_view($dir, $name, $prefix, $admin, $columns = []) {
    $lower_plural = strtolower($name) . 's';
    $route_prefix = $admin ? '/admin-' . $lower_plural : '/' . $lower_plural;
    
    $template = $admin ? 
        "<div class=\"admin-page-header\">\n    <h1 class=\"admin-page-title\">Edit " . ucfirst($name) . "</h1>\n    <p class=\"admin-page-subtitle\"><?= farme_escape(\${$prefix}['name']) ?></p>\n</div>\n\n<div class=\"admin-card\">\n    <div class=\"admin-card-header\">\n        <h3 class=\"admin-card-title\">" . ucfirst($name) . " Information</h3>\n    </div>\n    <div class=\"admin-card-body\">"
        :
        "<h1>Edit " . ucfirst($name) . "</h1>\n\n<div>";
    
    $template .= "\n        <form method=\"POST\" action=\"$route_prefix/<?= \${$prefix}['id'] ?>/edit\">\n";
    $template .= "            <input type=\"hidden\" name=\"_token\" value=\"<?= \$csrf_token ?>\">\n";
    $template .= "            \n";
    
    // Generate form fields based on table columns with data binding
    if (!empty($columns)) {
        foreach ($columns as $col) {
            $col_name = $col['name'];
            $col_type = strtolower($col['type']);
            
            // Skip auto-increment primary key and timestamps
            if ($col['key_type'] === 'PRI' && strpos($col['extra'], 'auto_increment') !== false) {
                continue;
            }
            if (in_array($col_name, ['created_at', 'updated_at'])) {
                continue;
            }
            
            $label = ucfirst(str_replace('_', ' ', $col_name));
            $required = ($col['nullable'] === 'NO') ? ' *' : '';
            $required_attr = ($col['nullable'] === 'NO') ? ' required' : '';
            
            $template .= "            <div class=\"form-group\">\n";
            $template .= "                <label class=\"form-label\">$label$required</label>\n";
            
            if (strpos($col_type, 'bool') !== false || strpos($col_type, 'tinyint(1)') !== false) {
                // Boolean field - select with data binding
                $template .= "                <select name=\"$col_name\" class=\"form-control\">\n";
                $template .= "                    <option value=\"1\" <?= \${$prefix}['$col_name'] ? 'selected' : '' ?>>Yes</option>\n";
                $template .= "                    <option value=\"0\" <?= !\${$prefix}['$col_name'] ? 'selected' : '' ?>>No</option>\n";
                $template .= "                </select>\n";
            } elseif (strpos($col_type, 'text') !== false || (strpos($col_type, 'varchar') !== false && $col['max_length'] > 255)) {
                // Large text field - textarea with data binding
                $template .= "                <textarea name=\"$col_name\" class=\"form-control\" rows=\"4\"$required_attr\n";
                $template .= "                          placeholder=\"Enter $label\"><?= farme_escape(\${$prefix}['$col_name'] ?? '') ?></textarea>\n";
            } elseif (strpos($col_name, 'email') !== false) {
                // Email field with data binding
                $template .= "                <input type=\"email\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\${$prefix}['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            } elseif (strpos($col_name, 'password') !== false) {
                // Password field - don't show existing value for security
                $template .= "                <input type=\"password\" name=\"$col_name\" class=\"form-control\"\n";
                $template .= "                       placeholder=\"Enter new $label (leave blank to keep current)\">\n";
            } elseif (strpos($col_type, 'int') !== false || strpos($col_type, 'bigint') !== false) {
                // Number field with data binding
                $template .= "                <input type=\"number\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\${$prefix}['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            } elseif (strpos($col_type, 'decimal') !== false || strpos($col_type, 'float') !== false || strpos($col_type, 'double') !== false) {
                // Decimal number field with data binding
                $template .= "                <input type=\"number\" step=\"0.01\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\${$prefix}['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            } elseif (strpos($col_type, 'date') !== false) {
                // Date field with data binding
                if (strpos($col_type, 'datetime') !== false || strpos($col_type, 'timestamp') !== false) {
                    $template .= "                <input type=\"datetime-local\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                    $template .= "                       value=\"<?= isset(\${$prefix}['$col_name']) ? date('Y-m-d\\TH:i', strtotime(\${$prefix}['$col_name'])) : '' ?>\">\n";
                } else {
                    $template .= "                <input type=\"date\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                    $template .= "                       value=\"<?= isset(\${$prefix}['$col_name']) ? date('Y-m-d', strtotime(\${$prefix}['$col_name'])) : '' ?>\">\n";
                }
            } else {
                // Default text field with data binding
                $template .= "                <input type=\"text\" name=\"$col_name\" class=\"form-control\"$required_attr\n";
                $template .= "                       value=\"<?= farme_escape(\${$prefix}['$col_name'] ?? '') ?>\"\n";
                $template .= "                       placeholder=\"Enter $label\">\n";
            }
            
            $template .= "            </div>\n";
            $template .= "            \n";
        }
    } else {
        // Fallback to default fields if no columns available
        $template .= "            <div class=\"form-group\">\n";
        $template .= "                <label class=\"form-label\">Name *</label>\n";
        $template .= "                <input type=\"text\" name=\"name\" class=\"form-control\" required\n";
        $template .= "                       value=\"<?= farme_escape(\${$prefix}['name'] ?? '') ?>\"\n";
        $template .= "                       placeholder=\"Enter " . strtolower($name) . " name\">\n";
        $template .= "            </div>\n";
        $template .= "            \n";
        $template .= "            <div class=\"form-group\">\n";
        $template .= "                <label class=\"form-label\">Email *</label>\n";
        $template .= "                <input type=\"email\" name=\"email\" class=\"form-control\" required\n";
        $template .= "                       value=\"<?= farme_escape(\${$prefix}['email'] ?? '') ?>\"\n";
        $template .= "                       placeholder=\"Enter email address\">\n";
        $template .= "            </div>\n";
        $template .= "            \n";
        $template .= "            <div class=\"form-group\">\n";
        $template .= "                <label class=\"form-label\">Status</label>\n";
        $template .= "                <select name=\"status\" class=\"form-control\">\n";
        $template .= "                    <option value=\"1\" <?= \${$prefix}['status'] ? 'selected' : '' ?>>Active</option>\n";
        $template .= "                    <option value=\"0\" <?= !\${$prefix}['status'] ? 'selected' : '' ?>>Inactive</option>\n";
        $template .= "                </select>\n";
        $template .= "            </div>\n";
        $template .= "            \n";
    }
    $template .= "            <div style=\"display: flex; gap: 1rem; margin-top: 2rem;\">\n";
    $template .= "                <button type=\"submit\" class=\"btn btn-success\">Update " . ucfirst($name) . "</button>\n";
    $template .= "                <a href=\"$route_prefix/<?= \${$prefix}['id'] ?>\" class=\"btn btn-secondary\">Cancel</a>\n";
    $template .= "            </div>\n";
    $template .= "        </form>\n";
    
    $template .= $admin ? "    </div>\n</div>" : "</div>";
    
    file_put_contents($dir . '/edit.php', $template);
    farme_console_success("View edit.php created");
}

/**
 * Generate CRUD migration if it doesn't exist
 */
function farme_generate_crud_migration($name) {
    $table_name = strtolower($name) . 's';
    
    // Check if migration already exists
    $migration_exists = farme_check_migration_exists($table_name);
    
    if ($migration_exists) {
        farme_console_info("Migration for table '$table_name' already exists, skipping...");
        return;
    }
    
    // Generate migration name
    $migration_name = "create_{$table_name}_table";
    
    // Generate the migration
    $migration_file = farme_generate_crud_migration_file($migration_name, $table_name);
    
    if ($migration_file) {
        farme_console_success("Migration created: " . basename($migration_file));
    } else {
        farme_console_error("Failed to create migration for table '$table_name'");
    }
}

/**
 * Check if migration exists for table
 */
function farme_check_migration_exists($table_name) {
    $migration_path = ROOT_PATH . '/config/Migration';
    
    if (!is_dir($migration_path)) {
        // Create migration directory if it doesn't exist
        mkdir($migration_path, 0755, true);
        return false;
    }
    
    $files = glob($migration_path . '/*.php');
    
    foreach ($files as $file) {
        $filename = basename($file, '.php');
        
        // Check if filename contains create_{table_name}_table pattern
        if (preg_match("/create_{$table_name}_table/", $filename)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Generate CRUD migration file
 */
function farme_generate_crud_migration_file($migration_name, $table_name) {
    $timestamp = date('Y_m_d_His');
    $filename = "{$timestamp}_{$migration_name}.php";
    $filepath = ROOT_PATH . "/config/Migration/{$filename}";
    
    $function_suffix = $timestamp . '_' . $migration_name;
    
    $template = "<?php

/**
 * Migration: " . ucwords(str_replace('_', ' ', $migration_name)) . "
 * 
 * Generated on: " . date('Y-m-d H:i:s') . "
 */

/**
 * Run the migration
 */
function farme_migration_up_{$function_suffix}() {
    return farme_schema_create('{$table_name}', function(\$table) {
        \$table->id();
        \$table->string('name')->notNull();
        \$table->string('email')->notNull();
        \$table->boolean('status')->default(1);
        \$table->timestamps();
    });
}

/**
 * Reverse the migration
 */
function farme_migration_down_{$function_suffix}() {
    return farme_schema_drop('{$table_name}');
}
";
    
    if (file_put_contents($filepath, $template)) {
        return $filepath;
    }
    
    return false;
}