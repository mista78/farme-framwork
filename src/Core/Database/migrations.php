<?php

/**
 * File-based Database Migrations for Farme Framework
 * 
 * Automated migration system with file discovery and ORM integration
 */

/**
 * Run all pending migrations
 */
function farme_run_migrations() {
    farme_create_migrations_table();
    
    $migrations = farme_discover_migration_files();
    $completed = farme_get_completed_migrations();
    
    $pending = [];
    foreach ($migrations as $migration) {
        if (!in_array($migration['name'], $completed)) {
            $pending[] = $migration;
        }
    }
    
    if (empty($pending)) {
        echo "✓ All migrations are up to date\n";
        return;
    }
    
    foreach ($pending as $migration) {
        echo "Running migration: {$migration['name']}\n";
        
        if (farme_run_migration_file($migration)) {
            farme_mark_migration_completed($migration['name']);
            echo "✓ Migration completed: {$migration['name']}\n";
        } else {
            echo "✗ Migration failed: {$migration['name']}\n";
            break;
        }
    }
}

/**
 * Create migrations tracking table
 */
function farme_create_migrations_table() {
    $sql = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTO_INCREMENT,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ";
    
    return farme_query($sql);
}

/**
 * Discover migration files in config/Migration directory
 */
function farme_discover_migration_files() {
    $migration_path = ROOT_PATH . '/config/Migration';
    
    if (!is_dir($migration_path)) {
        return [];
    }
    
    $files = glob($migration_path . '/*.php');
    $migrations = [];
    
    foreach ($files as $file) {
        $filename = basename($file, '.php');
        
        // Extract migration name (remove timestamp prefix for display)
        $migration_name = $filename;
        
        $migrations[] = [
            'name' => $migration_name,
            'file' => $file,
            'timestamp' => farme_extract_migration_timestamp($filename)
        ];
    }
    
    // Sort by timestamp
    usort($migrations, function($a, $b) {
        return strcmp($a['timestamp'], $b['timestamp']);
    });
    
    return $migrations;
}

/**
 * Extract timestamp from migration filename
 */
function farme_extract_migration_timestamp($filename) {
    // Extract YYYY_MM_DD_HHMMSS from filename
    if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})/', $filename, $matches)) {
        return $matches[1];
    }
    return $filename;
}

/**
 * Get list of completed migrations
 */
function farme_get_completed_migrations() {
    // Check if migrations table exists first
    try {
        $result = farme_query("SELECT migration FROM migrations ORDER BY id");
        
        if (!$result) {
            return [];
        }
        
        $completed = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $completed[] = $row['migration'];
        }
        
        return $completed;
    } catch (Exception $e) {
        // Migrations table doesn't exist yet
        return [];
    }
}

/**
 * Run a specific migration file
 */
function farme_run_migration_file($migration) {
    try {
        // Include the migration file
        require_once $migration['file'];
        
        // Build function name from migration name
        $function_name = 'farme_migration_up_' . $migration['name'];
        
        if (!function_exists($function_name)) {
            echo "Migration function not found: $function_name\n";
            return false;
        }
        
        // Execute the migration
        $result = $function_name();
        
        return $result !== false;
        
    } catch (Exception $e) {
        echo "Migration error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Mark migration as completed
 */
function farme_mark_migration_completed($migration) {
    $sql = "INSERT INTO migrations (migration) VALUES (?)";
    $stmt = farme_db_connection()->prepare($sql);
    $stmt->bindParam(1, $migration, PDO::PARAM_STR);
    return $stmt->execute();
}

/**
 * Check migration status
 */
function farme_migration_status() {
    $available = farme_discover_migration_files();
    $completed = farme_get_completed_migrations();
    
    $pending = [];
    foreach ($available as $migration) {
        if (!in_array($migration['name'], $completed)) {
            $pending[] = $migration['name'];
        }
    }
    
    echo "Migration Status:\n";
    echo "================\n";
    
    foreach ($available as $migration) {
        $status = in_array($migration['name'], $completed) ? '✓ Completed' : '✗ Pending';
        echo "$status - {$migration['name']}\n";
    }
    
    echo "\nSummary:\n";
    echo "- Total migrations: " . count($available) . "\n";
    echo "- Completed: " . count($completed) . "\n";
    echo "- Pending: " . count($pending) . "\n";
}

/**
 * Rollback migrations
 */
function farme_rollback_migrations($steps = 1) {
    $completed = farme_get_completed_migrations();
    
    if (empty($completed)) {
        echo "No migrations to rollback\n";
        return;
    }
    
    // Get the last N migrations to rollback
    $to_rollback = array_slice(array_reverse($completed), 0, $steps);
    
    foreach ($to_rollback as $migration_name) {
        echo "Rolling back migration: $migration_name\n";
        
        if (farme_rollback_migration($migration_name)) {
            farme_remove_migration_record($migration_name);
            echo "✓ Rollback completed: $migration_name\n";
        } else {
            echo "✗ Rollback failed: $migration_name\n";
            break;
        }
    }
}

/**
 * Rollback a specific migration
 */
function farme_rollback_migration($migration_name) {
    try {
        // Find the migration file
        $migrations = farme_discover_migration_files();
        $migration_file = null;
        
        foreach ($migrations as $migration) {
            if ($migration['name'] === $migration_name) {
                $migration_file = $migration['file'];
                break;
            }
        }
        
        if (!$migration_file) {
            echo "Migration file not found: $migration_name\n";
            return false;
        }
        
        // Include the migration file
        require_once $migration_file;
        
        // Build rollback function name
        $function_name = 'farme_migration_down_' . $migration_name;
        
        if (!function_exists($function_name)) {
            echo "Rollback function not found: $function_name\n";
            return false;
        }
        
        // Execute the rollback
        $result = $function_name();
        
        return $result !== false;
        
    } catch (Exception $e) {
        echo "Rollback error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Remove migration record
 */
function farme_remove_migration_record($migration_name) {
    $sql = "DELETE FROM migrations WHERE migration = ?";
    $stmt = farme_db_connection()->prepare($sql);
    $stmt->bindParam(1, $migration_name, PDO::PARAM_STR);
    return $stmt->execute();
}

/**
 * Generate a new migration file
 */
function farme_generate_migration($name, $type = 'table') {
    $timestamp = date('Y_m_d_His');
    $filename = "{$timestamp}_{$name}.php";
    $filepath = ROOT_PATH . "/config/Migration/{$filename}";
    
    $class_name = str_replace(['-', '_'], ' ', $name);
    $class_name = ucwords($class_name);
    $class_name = str_replace(' ', '', $class_name);
    
    $function_suffix = $timestamp . '_' . $name;
    
    if ($type === 'create') {
        $template = farme_generate_create_migration_template($name, $function_suffix);
    } elseif (strpos($name, 'add_') === 0 || strpos($name, 'remove_') === 0) {
        $template = farme_generate_alter_migration_template($name, $function_suffix);
    } else {
        $template = farme_generate_generic_migration_template($name, $function_suffix);
    }
    
    file_put_contents($filepath, $template);
    
    echo "✓ Migration created: {$filename}\n";
    echo "  Path: {$filepath}\n";
    
    return $filepath;
}

/**
 * Generate create table migration template
 */
function farme_generate_create_migration_template($name, $function_suffix) {
    $table_name = str_replace(['create_', '_table'], '', $name);
    
    return "<?php

/**
 * Migration: " . ucwords(str_replace('_', ' ', $name)) . "
 * 
 * Generated on: " . date('Y-m-d H:i:s') . "
 */

/**
 * Run the migration
 */
function farme_migration_up_{$function_suffix}() {
    return farme_schema_create('{$table_name}', function(\$table) {
        \$table->id();
        // Add your columns here
        \$table->string('name')->notNull();
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
}

/**
 * Generate alter table migration template
 */
function farme_generate_alter_migration_template($name, $function_suffix) {
    // Extract table name from migration name
    if (preg_match('/add_(.+)_to_(.+)_table/', $name, $matches)) {
        $column_name = $matches[1];
        $table_name = $matches[2];
        $action = 'add';
    } elseif (preg_match('/remove_(.+)_from_(.+)_table/', $name, $matches)) {
        $column_name = $matches[1];
        $table_name = $matches[2];
        $action = 'remove';
    } else {
        $table_name = 'your_table';
        $column_name = 'your_column';
        $action = 'add';
    }
    
    if ($action === 'add') {
        $up_action = "\$table->addColumn('{$column_name}', 'VARCHAR(255)', ['nullable' => true]);";
        $down_action = "\$table->dropColumn('{$column_name}');";
    } else {
        $up_action = "\$table->dropColumn('{$column_name}');";
        $down_action = "\$table->addColumn('{$column_name}', 'VARCHAR(255)', ['nullable' => true]);";
    }
    
    return "<?php

/**
 * Migration: " . ucwords(str_replace('_', ' ', $name)) . "
 * 
 * Generated on: " . date('Y-m-d H:i:s') . "
 */

/**
 * Run the migration
 */
function farme_migration_up_{$function_suffix}() {
    return farme_schema_table('{$table_name}', function(\$table) {
        {$up_action}
    });
}

/**
 * Reverse the migration
 */
function farme_migration_down_{$function_suffix}() {
    return farme_schema_table('{$table_name}', function(\$table) {
        {$down_action}
    });
}
";
}

/**
 * Generate generic migration template
 */
function farme_generate_generic_migration_template($name, $function_suffix) {
    return "<?php

/**
 * Migration: " . ucwords(str_replace('_', ' ', $name)) . "
 * 
 * Generated on: " . date('Y-m-d H:i:s') . "
 */

/**
 * Run the migration
 */
function farme_migration_up_{$function_suffix}() {
    // Add your migration logic here
    return true;
}

/**
 * Reverse the migration
 */
function farme_migration_down_{$function_suffix}() {
    // Add your rollback logic here
    return true;
}
";
}

/**
 * Reset all migrations (for development)
 */
function farme_reset_migrations() {
    $migrations = farme_discover_migration_files();
    $completed = farme_get_completed_migrations();
    
    // Rollback all completed migrations in reverse order
    $to_rollback = array_reverse($completed);
    
    foreach ($to_rollback as $migration_name) {
        echo "Rolling back: $migration_name\n";
        farme_rollback_migration($migration_name);
        farme_remove_migration_record($migration_name);
    }
    
    // Drop migrations table
    farme_query("DROP TABLE IF EXISTS migrations");
    
    echo "✓ All migrations reset\n";
    echo "Run migrations again to recreate tables\n";
}

/**
 * Get migration statistics for display
 */
function farme_get_migration_stats() {
    try {
        $available = farme_discover_migration_files();
        $completed = farme_get_completed_migrations();
        
        $pending = [];
        foreach ($available as $migration) {
            if (!in_array($migration['name'], $completed)) {
                $pending[] = $migration['name'];
            }
        }
        
        return [
            'total' => count($available),
            'completed' => count($completed),
            'pending' => count($pending),
            'latest_migrations' => array_slice($available, -3),
            'status' => count($pending) === 0 ? 'up_to_date' : 'pending'
        ];
    } catch (Exception $e) {
        return [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'latest_migrations' => [],
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
}