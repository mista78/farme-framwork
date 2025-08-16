<?php

/**
 * Database Schema Builder for Farme Framework
 * 
 * ORM-based table creation and alteration system
 */

/**
 * Schema builder for table operations
 */
class FarmeSchema {
    private $table_name;
    private $columns = [];
    private $indexes = [];
    private $foreign_keys = [];
    private $operations = [];
    private $if_not_exists = false;
    
    public function __construct($table_name) {
        $this->table_name = $table_name;
    }
    
    /**
     * Create table only if it doesn't exist
     */
    public function ifNotExists() {
        $this->if_not_exists = true;
        return $this;
    }
    
    /**
     * Add auto-incrementing ID column
     */
    public function id($name = 'id') {
        $this->columns[] = [
            'name' => $name,
            'type' => 'INTEGER',
            'primary' => true,
            'auto_increment' => true,
            'nullable' => false
        ];
        return $this;
    }
    
    /**
     * Add string column
     */
    public function string($name, $length = 255) {
        $this->columns[] = [
            'name' => $name,
            'type' => "VARCHAR($length)",
            'nullable' => true
        ];
        return $this;
    }
    
    /**
     * Add text column
     */
    public function text($name) {
        $this->columns[] = [
            'name' => $name,
            'type' => 'TEXT',
            'nullable' => true
        ];
        return $this;
    }
    
    /**
     * Add integer column
     */
    public function integer($name) {
        $this->columns[] = [
            'name' => $name,
            'type' => 'INTEGER',
            'nullable' => true
        ];
        return $this;
    }
    
    /**
     * Add boolean column
     */
    public function boolean($name) {
        $this->columns[] = [
            'name' => $name,
            'type' => 'BOOLEAN',
            'nullable' => true,
            'default' => 0
        ];
        return $this;
    }
    
    /**
     * Add datetime column
     */
    public function datetime($name) {
        $this->columns[] = [
            'name' => $name,
            'type' => 'DATETIME',
            'nullable' => true
        ];
        return $this;
    }
    
    /**
     * Add timestamp column
     */
    public function timestamp($name) {
        $this->columns[] = [
            'name' => $name,
            'type' => 'TIMESTAMP',
            'nullable' => true
        ];
        return $this;
    }
    
    /**
     * Add timestamps (created_at, updated_at)
     */
    public function timestamps() {
        $this->columns[] = [
            'name' => 'created_at',
            'type' => 'DATETIME',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP'
        ];
        $this->columns[] = [
            'name' => 'updated_at',
            'type' => 'DATETIME',
            'nullable' => false,
            'default' => 'CURRENT_TIMESTAMP'
        ];
        return $this;
    }
    
    /**
     * Make last column nullable
     */
    public function nullable() {
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['nullable'] = true;
        }
        return $this;
    }
    
    /**
     * Make last column not nullable
     */
    public function notNull() {
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['nullable'] = false;
        }
        return $this;
    }
    
    /**
     * Set default value for last column
     */
    public function default($value) {
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['default'] = $value;
        }
        return $this;
    }
    
    /**
     * Make last column unique
     */
    public function unique() {
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['unique'] = true;
        }
        return $this;
    }
    
    /**
     * Add index
     */
    public function index($columns, $name = null) {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        
        if (!$name) {
            $name = $this->table_name . '_' . implode('_', $columns) . '_index';
        }
        
        $this->indexes[] = [
            'name' => $name,
            'columns' => $columns,
            'type' => 'INDEX'
        ];
        return $this;
    }
    
    /**
     * Add foreign key constraint
     */
    public function foreign($column, $references_table, $references_column = 'id', $on_delete = 'CASCADE') {
        $this->foreign_keys[] = [
            'column' => $column,
            'references_table' => $references_table,
            'references_column' => $references_column,
            'on_delete' => $on_delete
        ];
        return $this;
    }
    
    /**
     * Generate CREATE TABLE SQL
     */
    public function toCreateSql() {
        $sql = "CREATE TABLE ";
        
        if ($this->if_not_exists) {
            $sql .= "IF NOT EXISTS ";
        }
        
        $sql .= "`{$this->table_name}` (\n";
        
        $column_definitions = [];
        
        // Add columns
        foreach ($this->columns as $column) {
            $def = "    `{$column['name']}` {$column['type']}";
            
            if (!$column['nullable']) {
                $def .= " NOT NULL";
            }
            
            if (isset($column['auto_increment']) && $column['auto_increment']) {
                $def .= " AUTO_INCREMENT";
            }
            
            if (isset($column['default'])) {
                if ($column['default'] === 'CURRENT_TIMESTAMP') {
                    $def .= " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $def .= " DEFAULT '" . addslashes($column['default']) . "'";
                }
            }
            
            if (isset($column['unique']) && $column['unique']) {
                $def .= " UNIQUE";
            }
            
            $column_definitions[] = $def;
        }
        
        // Add primary key
        $primary_columns = [];
        foreach ($this->columns as $column) {
            if (isset($column['primary']) && $column['primary']) {
                $primary_columns[] = "`{$column['name']}`";
            }
        }
        
        if (!empty($primary_columns)) {
            $column_definitions[] = "    PRIMARY KEY (" . implode(', ', $primary_columns) . ")";
        }
        
        $sql .= implode(",\n", $column_definitions);
        $sql .= "\n);";
        
        return $sql;
    }
    
    /**
     * Execute the schema
     */
    public function execute() {
        $sql = $this->toCreateSql();
        return farme_query($sql);
    }
}

/**
 * Table alteration builder
 */
class FarmeTableAlteration {
    private $table_name;
    private $operations = [];
    
    public function __construct($table_name) {
        $this->table_name = $table_name;
    }
    
    /**
     * Add column
     */
    public function addColumn($name, $type, $options = []) {
        $this->operations[] = [
            'type' => 'ADD_COLUMN',
            'name' => $name,
            'column_type' => $type,
            'options' => $options
        ];
        return $this;
    }
    
    /**
     * Drop column
     */
    public function dropColumn($name) {
        $this->operations[] = [
            'type' => 'DROP_COLUMN',
            'name' => $name
        ];
        return $this;
    }
    
    /**
     * Rename column
     */
    public function renameColumn($old_name, $new_name) {
        $this->operations[] = [
            'type' => 'RENAME_COLUMN',
            'old_name' => $old_name,
            'new_name' => $new_name
        ];
        return $this;
    }
    
    /**
     * Modify column
     */
    public function modifyColumn($name, $type, $options = []) {
        $this->operations[] = [
            'type' => 'MODIFY_COLUMN',
            'name' => $name,
            'column_type' => $type,
            'options' => $options
        ];
        return $this;
    }
    
    /**
     * Add index
     */
    public function addIndex($columns, $name = null) {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        
        if (!$name) {
            $name = $this->table_name . '_' . implode('_', $columns) . '_index';
        }
        
        $this->operations[] = [
            'type' => 'ADD_INDEX',
            'name' => $name,
            'columns' => $columns
        ];
        return $this;
    }
    
    /**
     * Drop index
     */
    public function dropIndex($name) {
        $this->operations[] = [
            'type' => 'DROP_INDEX',
            'name' => $name
        ];
        return $this;
    }
    
    /**
     * Generate ALTER TABLE SQL
     */
    public function toSql() {
        $sql_statements = [];
        
        foreach ($this->operations as $operation) {
            $sql = "ALTER TABLE `{$this->table_name}` ";
            
            switch ($operation['type']) {
                case 'ADD_COLUMN':
                    $sql .= "ADD COLUMN `{$operation['name']}` {$operation['column_type']}";
                    
                    $options = $operation['options'];
                    if (isset($options['nullable']) && !$options['nullable']) {
                        $sql .= " NOT NULL";
                    }
                    
                    if (isset($options['default'])) {
                        if ($options['default'] === 'CURRENT_TIMESTAMP') {
                            $sql .= " DEFAULT CURRENT_TIMESTAMP";
                        } else {
                            $sql .= " DEFAULT '" . addslashes($options['default']) . "'";
                        }
                    }
                    
                    if (isset($options['after'])) {
                        $sql .= " AFTER `{$options['after']}`";
                    }
                    break;
                    
                case 'DROP_COLUMN':
                    $sql .= "DROP COLUMN `{$operation['name']}`";
                    break;
                    
                case 'RENAME_COLUMN':
                    $sql .= "RENAME COLUMN `{$operation['old_name']}` TO `{$operation['new_name']}`";
                    break;
                    
                case 'MODIFY_COLUMN':
                    $sql .= "MODIFY COLUMN `{$operation['name']}` {$operation['column_type']}";
                    
                    $options = $operation['options'];
                    if (isset($options['nullable']) && !$options['nullable']) {
                        $sql .= " NOT NULL";
                    }
                    break;
                    
                case 'ADD_INDEX':
                    $columns = implode('`, `', $operation['columns']);
                    $sql .= "ADD INDEX `{$operation['name']}` (`{$columns}`)";
                    break;
                    
                case 'DROP_INDEX':
                    $sql .= "DROP INDEX `{$operation['name']}`";
                    break;
            }
            
            $sql_statements[] = $sql;
        }
        
        return $sql_statements;
    }
    
    /**
     * Execute the alterations
     */
    public function execute() {
        $statements = $this->toSql();
        $results = [];
        
        foreach ($statements as $sql) {
            $results[] = farme_query($sql);
        }
        
        return $results;
    }
}

/**
 * Create a new table schema
 */
function farme_schema_create($table_name, $callback) {
    $schema = new FarmeSchema($table_name);
    $callback($schema);
    return $schema->execute();
}

/**
 * Alter an existing table
 */
function farme_schema_table($table_name, $callback) {
    $alteration = new FarmeTableAlteration($table_name);
    $callback($alteration);
    return $alteration->execute();
}

/**
 * Drop a table
 */
function farme_schema_drop($table_name) {
    $sql = "DROP TABLE IF EXISTS `{$table_name}`";
    return farme_query($sql);
}

/**
 * Check if table exists
 */
function farme_schema_has_table($table_name) {
    $sql = "SHOW TABLES LIKE ?";
    $stmt = farme_db_connection()->prepare($sql);
    $stmt->bindParam(1, $table_name, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}

/**
 * Check if column exists in table
 */
function farme_schema_has_column($table_name, $column_name) {
    $sql = "SHOW COLUMNS FROM `{$table_name}` LIKE ?";
    $stmt = farme_db_connection()->prepare($sql);
    $stmt->bindParam(1, $column_name, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}