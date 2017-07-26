<?php

// Check allowed ip or show 404 page (to prevent abuse)

// Limits and errors
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

// Temporary config file outside of git
include('config.php');

// DB Config fallback
if (!defined('DB_HOST')) {
    define('DB_HOST', '');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('BACKUP_DIR', '');
}

$databases = '*'; // All databases
// $databases = array('db1', 'db2', 'db3'); // Specific databases

// Databases to exclude from backup
$exclude = array('information_schema', 'performance_schema', 'mysql', 'test');

// Create backup dir if it doesn't exist, with correct permissions
if (!is_dir(BACKUP_DIR) && !mkdir(BACKUP_DIR, 0755, true)) {
    die('Create Folder Error (' . BACKUP_DIR . ')');
}

// Connect to database server
$db = new mysqli(DB_HOST, DB_USER, DB_PASS);
if (mysqli_connect_error()) { // For compatibility with previous versions
    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}

// Get all database names
if ($databases === '*') {
    $databases = array();
    $result = $db->query('SHOW DATABASES');
    while ($row = $result->fetch_array()) {
        if (!in_array($row[0], $exclude)) {
            $databases[] = $row[0];
        }
    }
}

// For each database
foreach ($databases as $database) {

    echo 'Database: '.$database.'<br />';

    // Create output file or truncate if it exists

    // Output CREATE DATABASE and USE DATABASE

    // Get all table names

    // For each table

        // Output DROP TABLE

        // Fetch table structure (SHOW CREATE TABLE)

        // Output CREATE TABLE

        // For each table row

            // Escape row

            // Output INSERT INTO (maybe group multiple)

    // Close output file

}

// Close database connection
$db->close();

// TODO: Handle errors and exceptions gracefully
