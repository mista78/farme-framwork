<?php

/**
 * Database Connection Manager
 * 
 * Manages multiple database connections
 */

// Global database connections
$farme_db_connections = [];
$farme_db_config = [];

/**
 * Initialize database configuration
 */
function farme_db_configure($config) {
    global $farme_db_config;
    $farme_db_config = $config;
}

/**
 * Get database connection by name
 */
function farme_db_connection($connection_name = null) {
    global $farme_db_connections, $farme_db_config;
    
    // Use default connection if none specified
    if ($connection_name === null) {
        $connection_name = $farme_db_config['default'] ?? 'main';
    }
    
    // Return existing connection if already established
    if (isset($farme_db_connections[$connection_name])) {
        return $farme_db_connections[$connection_name];
    }
    
    // Get connection config
    if (!isset($farme_db_config['connections'][$connection_name])) {
        throw new Exception("Database connection '{$connection_name}' not configured");
    }
    
    $config = $farme_db_config['connections'][$connection_name];
    
    // Create connection based on driver
    $connection = farme_create_connection($config);
    
    // Store connection for reuse
    $farme_db_connections[$connection_name] = $connection;
    
    return $connection;
}

/**
 * Create database connection
 */
function farme_create_connection($config) {
    $driver = $config['driver'];
    
    switch ($driver) {
        case 'mysql':
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            break;
            
        case 'pgsql':
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            break;
            
        case 'sqlite':
            $dsn = "sqlite:{$config['database']}";
            // Create directory if it doesn't exist
            $dir = dirname($config['database']);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            break;
            
        default:
            throw new Exception("Unsupported database driver: {$driver}");
    }
    
    try {
        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        
        $pdo = new PDO($dsn, $username, $password, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Execute query on specific connection
 */
function farme_query_on($connection_name, $sql, $params = []) {
    $db = farme_db_connection($connection_name);
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Execute query on default connection
 */
function farme_query($sql, $params = []) {
    return farme_query_on(null, $sql, $params);
}

/**
 * Find single record on specific connection
 */
function farme_find_on($connection_name, $table, $id, $id_column = 'id') {
    $sql = "SELECT * FROM {$table} WHERE {$id_column} = ? LIMIT 1";
    $stmt = farme_query_on($connection_name, $sql, [$id]);
    return $stmt->fetch();
}

/**
 * Find single record on default connection
 */
function farme_find($table, $id, $id_column = 'id') {
    return farme_find_on(null, $table, $id, $id_column);
}

/**
 * Find all records on specific connection
 */
function farme_find_all_on($connection_name, $table, $conditions = [], $order = '') {
    $sql = "SELECT * FROM {$table}";
    $params = [];
    
    if (!empty($conditions)) {
        $where_clauses = [];
        foreach ($conditions as $column => $value) {
            $where_clauses[] = "{$column} = ?";
            $params[] = $value;
        }
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }
    
    if ($order) {
        $sql .= " ORDER BY {$order}";
    }
    
    $stmt = farme_query_on($connection_name, $sql, $params);
    return $stmt->fetchAll();
}

/**
 * Find all records on default connection
 */
function farme_find_all($table, $conditions = [], $order = '') {
    return farme_find_all_on(null, $table, $conditions, $order);
}

/**
 * Insert record on specific connection
 */
function farme_insert_on($connection_name, $table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = farme_query_on($connection_name, $sql, $data);
    
    return farme_db_connection($connection_name)->lastInsertId();
}

/**
 * Insert record on default connection
 */
function farme_insert($table, $data) {
    return farme_insert_on(null, $table, $data);
}

/**
 * Update record on specific connection
 */
function farme_update_on($connection_name, $table, $data, $id, $id_column = 'id') {
    $set_clauses = [];
    foreach ($data as $column => $value) {
        $set_clauses[] = "{$column} = :{$column}";
    }
    
    $sql = "UPDATE {$table} SET " . implode(', ', $set_clauses) . " WHERE {$id_column} = :id";
    $data['id'] = $id;
    
    $stmt = farme_query_on($connection_name, $sql, $data);
    return $stmt->rowCount();
}

/**
 * Update record on default connection
 */
function farme_update($table, $data, $id, $id_column = 'id') {
    return farme_update_on(null, $table, $data, $id, $id_column);
}

/**
 * Delete record on specific connection
 */
function farme_delete_on($connection_name, $table, $id, $id_column = 'id') {
    $sql = "DELETE FROM {$table} WHERE {$id_column} = ?";
    $stmt = farme_query_on($connection_name, $sql, [$id]);
    return $stmt->rowCount();
}

/**
 * Delete record on default connection
 */
function farme_delete($table, $id, $id_column = 'id') {
    return farme_delete_on(null, $table, $id, $id_column);
}

/**
 * Begin transaction on specific connection
 */
function farme_begin_transaction($connection_name = null) {
    return farme_db_connection($connection_name)->beginTransaction();
}

/**
 * Commit transaction on specific connection
 */
function farme_commit($connection_name = null) {
    return farme_db_connection($connection_name)->commit();
}

/**
 * Rollback transaction on specific connection
 */
function farme_rollback($connection_name = null) {
    return farme_db_connection($connection_name)->rollBack();
}

/**
 * Close specific connection
 */
function farme_close_connection($connection_name) {
    global $farme_db_connections;
    if (isset($farme_db_connections[$connection_name])) {
        unset($farme_db_connections[$connection_name]);
    }
}

/**
 * Close all connections
 */
function farme_close_all_connections() {
    global $farme_db_connections;
    $farme_db_connections = [];
}

/**
 * Check if table exists on specific connection
 */
function farme_table_exists_on($connection_name, $table_name) {
    global $farme_db_config;
    
    try {
        $db = farme_db_connection($connection_name);
        $conn_name = $connection_name ?? $farme_db_config['default'] ?? 'main';
        $config = $farme_db_config['connections'][$conn_name];
        $driver = $config['driver'];
        
        switch ($driver) {
            case 'mysql':
                $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
                break;
            case 'pgsql':
                $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ? AND table_catalog = current_database()";
                break;
            case 'sqlite':
                $sql = "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name = ?";
                break;
            default:
                return false;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$table_name]);
        return $stmt->fetchColumn() > 0;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if table exists on default connection
 */
function farme_table_exists($table_name) {
    return farme_table_exists_on(null, $table_name);
}

/**
 * Get table columns information on specific connection
 */
function farme_get_table_columns_on($connection_name, $table_name) {
    global $farme_db_config;
    
    try {
        $db = farme_db_connection($connection_name);
        $conn_name = $connection_name ?? $farme_db_config['default'] ?? 'main';
        $config = $farme_db_config['connections'][$conn_name];
        $driver = $config['driver'];
        
        switch ($driver) {
            case 'mysql':
                $sql = "SELECT 
                    COLUMN_NAME as name,
                    DATA_TYPE as type,
                    IS_NULLABLE as nullable,
                    COLUMN_DEFAULT as default_value,
                    CHARACTER_MAXIMUM_LENGTH as max_length,
                    COLUMN_KEY as key_type,
                    EXTRA as extra
                FROM information_schema.COLUMNS 
                WHERE table_schema = DATABASE() AND table_name = ?
                ORDER BY ORDINAL_POSITION";
                break;
                
            case 'pgsql':
                $sql = "SELECT 
                    column_name as name,
                    data_type as type,
                    is_nullable as nullable,
                    column_default as default_value,
                    character_maximum_length as max_length,
                    '' as key_type,
                    '' as extra
                FROM information_schema.columns 
                WHERE table_catalog = current_database() AND table_name = ?
                ORDER BY ordinal_position";
                break;
                
            case 'sqlite':
                $sql = "PRAGMA table_info($table_name)";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $columns = $stmt->fetchAll();
                
                // Convert SQLite format to standard format
                $result = [];
                foreach ($columns as $col) {
                    $result[] = [
                        'name' => $col['name'],
                        'type' => $col['type'],
                        'nullable' => $col['notnull'] ? 'NO' : 'YES',
                        'default_value' => $col['dflt_value'],
                        'max_length' => null,
                        'key_type' => $col['pk'] ? 'PRI' : '',
                        'extra' => $col['pk'] ? 'auto_increment' : ''
                    ];
                }
                return $result;
                
            default:
                return [];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$table_name]);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get table columns information on default connection
 */
function farme_get_table_columns($table_name) {
    return farme_get_table_columns_on(null, $table_name);
}