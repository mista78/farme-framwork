<?php

/**
 * ORM-like Database Functions
 * 
 * Provides ActiveRecord-style methods for models while maintaining
 * the procedural approach of Farme Framework
 */

/**
 * Model configuration
 */
$farme_model_config = [];

/**
 * Configure model settings
 */
function farme_model_configure($model, $config) {
    global $farme_model_config;
    $farme_model_config[$model] = array_merge([
        'table' => $model . 's',
        'primary_key' => 'id',
        'timestamps' => true,
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'connection' => null,
        'fillable' => [],
        'hidden' => [],
        'casts' => [],
        'relations' => []
    ], $config);
}

/**
 * Get model configuration
 */
function farme_model_config($model) {
    global $farme_model_config;
    
    if (!isset($farme_model_config[$model])) {
        // Set default config if not configured
        farme_model_configure($model, []);
    }
    
    return $farme_model_config[$model];
}

/**
 * Create a new query for a model
 */
function farme_model_query($model) {
    $config = farme_model_config($model);
    return farme_table($config['table'], $config['connection']);
}

/**
 * Find model by ID
 */
function farme_model_find($model, $id) {
    $config = farme_model_config($model);
    
    $query = farme_model_query($model);
    $query = farme_where($query, $config['primary_key'], $id);
    
    $result = farme_first($query);
    return $result ? farme_model_cast($model, $result) : null;
}

/**
 * Find model or fail with exception
 */
function farme_model_find_or_fail($model, $id) {
    $result = farme_model_find($model, $id);
    if (!$result) {
        throw new Exception("No {$model} found with ID: {$id}");
    }
    return $result;
}

/**
 * Find model by attribute
 */
function farme_model_find_by($model, $column, $value) {
    $query = farme_model_query($model);
    $query = farme_where($query, $column, $value);
    
    $result = farme_first($query);
    return $result ? farme_model_cast($model, $result) : null;
}

/**
 * Get all models
 */
function farme_model_all($model) {
    $query = farme_model_query($model);
    $results = farme_get($query);
    
    return array_map(function($result) use ($model) {
        return farme_model_cast($model, $result);
    }, $results);
}

/**
 * Get models with conditions
 */
function farme_model_where($model, $conditions) {
    $query = farme_model_query($model);
    
    if (is_array($conditions)) {
        foreach ($conditions as $column => $value) {
            $query = farme_where($query, $column, $value);
        }
    }
    
    return $query;
}

/**
 * Create new model record
 */
function farme_model_create($model, $data) {
    $config = farme_model_config($model);
    
    // Filter fillable attributes
    if (!empty($config['fillable'])) {
        $data = array_intersect_key($data, array_flip($config['fillable']));
    }
    
    // Add timestamps
    if ($config['timestamps']) {
        $now = date('Y-m-d H:i:s');
        if ($config['created_at']) {
            $data[$config['created_at']] = $now;
        }
        if ($config['updated_at']) {
            $data[$config['updated_at']] = $now;
        }
    }
    
    if ($config['connection']) {
        $id = farme_insert_on($config['connection'], $config['table'], $data);
    } else {
        $id = farme_insert($config['table'], $data);
    }
    
    return farme_model_find($model, $id);
}

/**
 * Update model record
 */
function farme_model_update($model, $id, $data) {
    $config = farme_model_config($model);
    
    // Filter fillable attributes
    if (!empty($config['fillable'])) {
        $data = array_intersect_key($data, array_flip($config['fillable']));
    }
    
    // Add updated timestamp
    if ($config['timestamps'] && $config['updated_at']) {
        $data[$config['updated_at']] = date('Y-m-d H:i:s');
    }
    
    if ($config['connection']) {
        $updated = farme_update_on($config['connection'], $config['table'], $data, $id, $config['primary_key']);
    } else {
        $updated = farme_update($config['table'], $data, $id, $config['primary_key']);
    }
    
    return $updated > 0 ? farme_model_find($model, $id) : null;
}

/**
 * Delete model record
 */
function farme_model_delete($model, $id) {
    $config = farme_model_config($model);
    
    if ($config['connection']) {
        return farme_delete_on($config['connection'], $config['table'], $id, $config['primary_key']);
    } else {
        return farme_delete($config['table'], $id, $config['primary_key']);
    }
}

/**
 * Save model record (create or update)
 */
function farme_model_save($model, $data) {
    $config = farme_model_config($model);
    $primary_key = $config['primary_key'];
    
    if (isset($data[$primary_key]) && !empty($data[$primary_key])) {
        // Update existing record
        $id = $data[$primary_key];
        unset($data[$primary_key]);
        return farme_model_update($model, $id, $data);
    } else {
        // Create new record
        return farme_model_create($model, $data);
    }
}

/**
 * Get model with relationships
 */
function farme_model_with($model, $relations) {
    $query = farme_model_query($model);
    $config = farme_model_config($model);
    
    foreach ($relations as $relation) {
        if (isset($config['relations'][$relation])) {
            $rel_config = $config['relations'][$relation];
            
            switch ($rel_config['type']) {
                case 'belongs_to':
                    $query = farme_left_join($query, 
                        $rel_config['table'], 
                        $config['table'] . '.' . $rel_config['foreign_key'], 
                        $rel_config['table'] . '.' . $rel_config['local_key']
                    );
                    break;
                    
                case 'has_many':
                    // For has_many, we'll need to handle this in a separate query
                    break;
            }
        }
    }
    
    return $query;
}

/**
 * Cast model attributes according to configuration
 */
function farme_model_cast($model, $data) {
    if (!$data) return $data;
    
    $config = farme_model_config($model);
    
    // Remove hidden attributes
    if (!empty($config['hidden'])) {
        foreach ($config['hidden'] as $hidden) {
            unset($data[$hidden]);
        }
    }
    
    // Cast attributes
    if (!empty($config['casts'])) {
        foreach ($config['casts'] as $attribute => $type) {
            if (isset($data[$attribute])) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $data[$attribute] = (int)$data[$attribute];
                        break;
                    case 'float':
                    case 'double':
                        $data[$attribute] = (float)$data[$attribute];
                        break;
                    case 'bool':
                    case 'boolean':
                        $data[$attribute] = (bool)$data[$attribute];
                        break;
                    case 'array':
                    case 'json':
                        $data[$attribute] = json_decode($data[$attribute], true);
                        break;
                    case 'date':
                        $data[$attribute] = date('Y-m-d', strtotime($data[$attribute]));
                        break;
                    case 'datetime':
                        $data[$attribute] = date('Y-m-d H:i:s', strtotime($data[$attribute]));
                        break;
                }
            }
        }
    }
    
    return $data;
}

/**
 * Get model count
 */
function farme_model_count($model, $conditions = []) {
    $query = farme_model_query($model);
    
    foreach ($conditions as $column => $value) {
        $query = farme_where($query, $column, $value);
    }
    
    return farme_count($query);
}

/**
 * Get paginated model results
 */
function farme_model_paginate($model, $page = 1, $per_page = 15, $conditions = []) {
    $query = farme_model_query($model);
    
    foreach ($conditions as $column => $value) {
        $query = farme_where($query, $column, $value);
    }
    
    $result = farme_paginate($query, $page, $per_page);
    
    // Cast all data items
    $result['data'] = array_map(function($item) use ($model) {
        return farme_model_cast($model, $item);
    }, $result['data']);
    
    return $result;
}

/**
 * Chunk model processing
 */
function farme_model_chunk($model, $chunk_size, $callback, $conditions = []) {
    $page = 1;
    
    do {
        $query = farme_model_query($model);
        
        foreach ($conditions as $column => $value) {
            $query = farme_where($query, $column, $value);
        }
        
        $query = farme_limit($query, $chunk_size, ($page - 1) * $chunk_size);
        $results = farme_get($query);
        
        if (empty($results)) {
            break;
        }
        
        // Cast results and call callback
        $casted_results = array_map(function($result) use ($model) {
            return farme_model_cast($model, $result);
        }, $results);
        
        $callback($casted_results);
        
        $page++;
    } while (count($results) === $chunk_size);
}

/**
 * Execute raw SQL query for model
 */
function farme_model_raw($model, $sql, $params = []) {
    $config = farme_model_config($model);
    
    if ($config['connection']) {
        $stmt = farme_query_on($config['connection'], $sql, $params);
    } else {
        $stmt = farme_query($sql, $params);
    }
    
    $results = $stmt->fetchAll();
    
    return array_map(function($result) use ($model) {
        return farme_model_cast($model, $result);
    }, $results);
}