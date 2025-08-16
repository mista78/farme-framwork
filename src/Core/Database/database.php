<?php

/**
 * Legacy Database Functions
 * 
 * This file is kept for backward compatibility.
 * All database functions have been moved to connection_manager.php
 */

// Backward compatibility - redirect to new connection manager functions
function farme_db_init($config) {
    // Convert old config format to new format for default connection
    $new_config = [
        'default' => 'main',
        'connections' => [
            'main' => $config
        ]
    ];
    
    farme_db_configure($new_config);
    return farme_db_connection('main');
}

function farme_db() {
    return farme_db_connection();
}