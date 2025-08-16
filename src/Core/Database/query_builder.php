<?php

/**
 * Query Builder
 * 
 * Provides a fluent interface for building database queries
 */

/**
 * Initialize a query builder for a table
 */
function farme_table($table, $connection = null) {
    return [
        'table' => $table,
        'connection' => $connection,
        'select' => ['*'],
        'where' => [],
        'join' => [],
        'order' => [],
        'group' => [],
        'having' => [],
        'limit' => null,
        'offset' => null,
        'params' => []
    ];
}

/**
 * Add SELECT columns to query
 */
function farme_select($query, $columns) {
    if (is_string($columns)) {
        $columns = [$columns];
    }
    $query['select'] = $columns;
    return $query;
}

/**
 * Add WHERE condition to query
 */
function farme_where($query, $column, $operator = '=', $value = null) {
    // Handle array of conditions
    if (is_array($column)) {
        foreach ($column as $col => $val) {
            $query = farme_where($query, $col, '=', $val);
        }
        return $query;
    }
    
    // Handle two-parameter call (column, value)
    if ($value === null) {
        $value = $operator;
        $operator = '=';
    }
    
    $param_key = 'param_' . count($query['params']);
    $query['where'][] = "{$column} {$operator} :{$param_key}";
    $query['params'][$param_key] = $value;
    
    return $query;
}

/**
 * Add WHERE IN condition
 */
function farme_where_in($query, $column, $values) {
    if (empty($values)) {
        return $query;
    }
    
    $placeholders = [];
    foreach ($values as $value) {
        $param_key = 'param_' . count($query['params']);
        $placeholders[] = ":{$param_key}";
        $query['params'][$param_key] = $value;
    }
    
    $query['where'][] = "{$column} IN (" . implode(', ', $placeholders) . ")";
    return $query;
}

/**
 * Add WHERE NOT IN condition
 */
function farme_where_not_in($query, $column, $values) {
    if (empty($values)) {
        return $query;
    }
    
    $placeholders = [];
    foreach ($values as $value) {
        $param_key = 'param_' . count($query['params']);
        $placeholders[] = ":{$param_key}";
        $query['params'][$param_key] = $value;
    }
    
    $query['where'][] = "{$column} NOT IN (" . implode(', ', $placeholders) . ")";
    return $query;
}

/**
 * Add WHERE NULL condition
 */
function farme_where_null($query, $column) {
    $query['where'][] = "{$column} IS NULL";
    return $query;
}

/**
 * Add WHERE NOT NULL condition
 */
function farme_where_not_null($query, $column) {
    $query['where'][] = "{$column} IS NOT NULL";
    return $query;
}

/**
 * Add LIKE condition
 */
function farme_where_like($query, $column, $pattern) {
    $param_key = 'param_' . count($query['params']);
    $query['where'][] = "{$column} LIKE :{$param_key}";
    $query['params'][$param_key] = $pattern;
    return $query;
}

/**
 * Add JOIN to query
 */
function farme_join($query, $table, $first, $operator = '=', $second = null) {
    if ($second === null) {
        $second = $operator;
        $operator = '=';
    }
    
    $query['join'][] = "JOIN {$table} ON {$first} {$operator} {$second}";
    return $query;
}

/**
 * Add LEFT JOIN to query
 */
function farme_left_join($query, $table, $first, $operator = '=', $second = null) {
    if ($second === null) {
        $second = $operator;
        $operator = '=';
    }
    
    $query['join'][] = "LEFT JOIN {$table} ON {$first} {$operator} {$second}";
    return $query;
}

/**
 * Add RIGHT JOIN to query
 */
function farme_right_join($query, $table, $first, $operator = '=', $second = null) {
    if ($second === null) {
        $second = $operator;
        $operator = '=';
    }
    
    $query['join'][] = "RIGHT JOIN {$table} ON {$first} {$operator} {$second}";
    return $query;
}

/**
 * Add ORDER BY to query
 */
function farme_order_by($query, $column, $direction = 'ASC') {
    $query['order'][] = "{$column} {$direction}";
    return $query;
}

/**
 * Add GROUP BY to query
 */
function farme_group_by($query, $columns) {
    if (is_string($columns)) {
        $columns = [$columns];
    }
    $query['group'] = array_merge($query['group'], $columns);
    return $query;
}

/**
 * Add HAVING to query
 */
function farme_having($query, $column, $operator = '=', $value = null) {
    if ($value === null) {
        $value = $operator;
        $operator = '=';
    }
    
    $param_key = 'param_' . count($query['params']);
    $query['having'][] = "{$column} {$operator} :{$param_key}";
    $query['params'][$param_key] = $value;
    
    return $query;
}

/**
 * Add LIMIT to query
 */
function farme_limit($query, $limit, $offset = null) {
    $query['limit'] = $limit;
    if ($offset !== null) {
        $query['offset'] = $offset;
    }
    return $query;
}

/**
 * Add OFFSET to query
 */
function farme_offset($query, $offset) {
    $query['offset'] = $offset;
    return $query;
}

/**
 * Build and execute SELECT query
 */
function farme_get($query) {
    $sql = farme_build_select_sql($query);
    
    if ($query['connection']) {
        $stmt = farme_query_on($query['connection'], $sql, $query['params']);
    } else {
        $stmt = farme_query($sql, $query['params']);
    }
    
    return $stmt->fetchAll();
}

/**
 * Build and execute SELECT query, return first result
 */
function farme_first($query) {
    $query = farme_limit($query, 1);
    $results = farme_get($query);
    return !empty($results) ? $results[0] : null;
}

/**
 * Get count of matching records
 */
function farme_count($query) {
    $original_select = $query['select'];
    $query['select'] = ['COUNT(*) as count'];
    
    $sql = farme_build_select_sql($query);
    
    if ($query['connection']) {
        $stmt = farme_query_on($query['connection'], $sql, $query['params']);
    } else {
        $stmt = farme_query($sql, $query['params']);
    }
    
    $result = $stmt->fetch();
    return (int)$result['count'];
}

/**
 * Check if records exist
 */
function farme_exists($query) {
    return farme_count($query) > 0;
}

/**
 * Get paginated results
 */
function farme_paginate($query, $page = 1, $per_page = 15) {
    $total = farme_count($query);
    $offset = ($page - 1) * $per_page;
    
    $query = farme_limit($query, $per_page, $offset);
    $data = farme_get($query);
    
    return [
        'data' => $data,
        'current_page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'last_page' => ceil($total / $per_page),
        'from' => $offset + 1,
        'to' => min($offset + $per_page, $total)
    ];
}

/**
 * Build SELECT SQL from query array
 */
function farme_build_select_sql($query) {
    $sql = "SELECT " . implode(', ', $query['select']) . " FROM {$query['table']}";
    
    // Add JOINs
    if (!empty($query['join'])) {
        $sql .= " " . implode(' ', $query['join']);
    }
    
    // Add WHERE
    if (!empty($query['where'])) {
        $sql .= " WHERE " . implode(' AND ', $query['where']);
    }
    
    // Add GROUP BY
    if (!empty($query['group'])) {
        $sql .= " GROUP BY " . implode(', ', $query['group']);
    }
    
    // Add HAVING
    if (!empty($query['having'])) {
        $sql .= " HAVING " . implode(' AND ', $query['having']);
    }
    
    // Add ORDER BY
    if (!empty($query['order'])) {
        $sql .= " ORDER BY " . implode(', ', $query['order']);
    }
    
    // Add LIMIT and OFFSET
    if ($query['limit'] !== null) {
        $sql .= " LIMIT " . $query['limit'];
        if ($query['offset'] !== null) {
            $sql .= " OFFSET " . $query['offset'];
        }
    }
    
    return $sql;
}

/**
 * Build and execute UPDATE query
 */
function farme_update_query($query, $data) {
    if (empty($query['where'])) {
        throw new Exception("UPDATE query must have WHERE conditions");
    }
    
    $set_clauses = [];
    foreach ($data as $column => $value) {
        $param_key = 'set_' . $column;
        $set_clauses[] = "{$column} = :{$param_key}";
        $query['params'][$param_key] = $value;
    }
    
    $sql = "UPDATE {$query['table']} SET " . implode(', ', $set_clauses);
    
    if (!empty($query['where'])) {
        $sql .= " WHERE " . implode(' AND ', $query['where']);
    }
    
    if ($query['connection']) {
        $stmt = farme_query_on($query['connection'], $sql, $query['params']);
    } else {
        $stmt = farme_query($sql, $query['params']);
    }
    
    return $stmt->rowCount();
}

/**
 * Build and execute DELETE query
 */
function farme_delete_query($query) {
    if (empty($query['where'])) {
        throw new Exception("DELETE query must have WHERE conditions");
    }
    
    $sql = "DELETE FROM {$query['table']}";
    
    if (!empty($query['where'])) {
        $sql .= " WHERE " . implode(' AND ', $query['where']);
    }
    
    if ($query['connection']) {
        $stmt = farme_query_on($query['connection'], $sql, $query['params']);
    } else {
        $stmt = farme_query($sql, $query['params']);
    }
    
    return $stmt->rowCount();
}