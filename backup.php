<?php

// Check allowed ip or show 404 page (to prevent abuse)

define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('BACKUP_DIR', '');

$databases = '*'; // All databases
// $databases = array('db1', 'db2', 'db3'); // Specific databases

// Create backup dir if it doesn't exist + check permissions

// Connect to database server

// Get all database names

// Filter out default databases

// For each database

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

// TODO: Handle errors and exceptions gracefully
