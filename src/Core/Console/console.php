<?php

/**
 * Console command system
 */

// Global commands registry
$farme_commands = [];

/**
 * Register a console command
 */
function farme_command($name, $description, $callback) {
    global $farme_commands;
    $farme_commands[$name] = [
        'description' => $description,
        'callback' => $callback
    ];
}

/**
 * Run console command
 */
function farme_run_command($argv) {
    global $farme_commands;
    
    if (count($argv) < 2) {
        farme_show_help();
        return;
    }
    
    $command = $argv[1];
    $args = array_slice($argv, 2);
    
    if ($command === 'help' || $command === '--help' || $command === '-h') {
        farme_show_help();
        return;
    }
    
    if (!isset($farme_commands[$command])) {
        echo "Unknown command: $command\n";
        farme_show_help();
        return;
    }
    
    $callback = $farme_commands[$command]['callback'];
    if (is_callable($callback)) {
        call_user_func($callback, $args);
    } else {
        echo "Command callback not callable: $command\n";
    }
}

/**
 * Show help
 */
function farme_show_help() {
    global $farme_commands;
    
    echo "Farme Framework Console\n";
    echo "Usage: php console.php <command> [arguments]\n\n";
    echo "Available commands:\n";
    
    foreach ($farme_commands as $name => $info) {
        echo sprintf("  %-20s %s\n", $name, $info['description']);
    }
    
    echo "\n";
}

/**
 * Console output helpers
 */
function farme_console_write($message, $newline = true) {
    echo $message . ($newline ? "\n" : '');
}

function farme_console_success($message) {
    farme_console_write("\033[32m✓ $message\033[0m");
}

function farme_console_error($message) {
    farme_console_write("\033[31m✗ $message\033[0m");
}

function farme_console_info($message) {
    farme_console_write("\033[34mℹ $message\033[0m");
}

function farme_console_warning($message) {
    farme_console_write("\033[33m⚠ $message\033[0m");
}

/**
 * Get user input
 */
function farme_console_ask($question) {
    echo "$question: ";
    return trim(fgets(STDIN));
}

/**
 * Confirm action
 */
function farme_console_confirm($question) {
    $response = farme_console_ask("$question (y/N)");
    return strtolower($response) === 'y' || strtolower($response) === 'yes';
}