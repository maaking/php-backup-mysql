<?php

// Check allowed ip or show 404 page (to prevent abuse)

// Limits and errors
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

// Temporary config file outside of git
include('config.php');

// DB Config fallback
// TODO: Change to defaults overriden by config file
if (!defined('DB_HOST')) {
    define('DB_HOST', '');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('BACKUP_DIR', '');
    define('CREATE_DB', false);
}

$databases = '*'; // All databases
// $databases = array('db1', 'db2', 'db3'); // Specific databases

// Databases to exclude from backup
$exclude = array('information_schema', 'performance_schema', 'mysql', 'test');

// Create backup dir if it doesn't exist, with correct permissions
if (!is_dir(BACKUP_DIR) && !mkdir(BACKUP_DIR, 0777, true)) {
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
        $database = $row[0];
        if (!in_array($database, $exclude)) {
            $databases[] = $database;
        }
    }
}

// Get maximum allowed packet
$result_max = $db->query('SHOW VARIABLES LIKE \'max_allowed_packet\';');
$row_max = $result_max->fetch_array();
$max_allowed_packet = (int)$row_max[1];

// Override maximum allowed packet (usually 1MB)
$max_allowed_packet = 10000;

echo 'Max Allowed Packet: '.$max_allowed_packet.'<br /><br />';

// For each database
foreach ($databases as $database) {

    echo 'Database: '.$database.'<br />';
    $db->select_db($database);

    $filename = BACKUP_DIR.$database.'-'.date('Ymd').'.sql';

    touch($filename);
    chmod($filename, 0777);

    // Create output file or truncate if it exists
    $file = fopen($filename, 'w+');

    // Output CREATE DATABASE and USE DATABASE
    if (CREATE_DB) {
        fwrite($file, 'CREATE DATABASE `'.$database.'`;'.PHP_EOL);
        fwrite($file, 'USE DATABASE `'.$database.'`;'.PHP_EOL);
    }

    // Get all table names
    $result_tables = $db->query('SHOW TABLES');

    // For each table
    while ($row_tables = $result_tables->fetch_array()) {

        $table = $row_tables[0];

        echo 'Table: '.$table.'<br />';

        // Output DROP TABLE
        fwrite($file, 'DROP TABLE IF EXISTS `'.$table.'`;'.PHP_EOL);

        // Fetch table structure
        $result_table_struct = $db->query('SHOW CREATE TABLE `'.$table.'`;');
        $row_table_struct = $result_table_struct->fetch_array();
        $table_struct_sql = $row_table_struct[1].';';

        // Output CREATE TABLE
        fwrite($file, $table_struct_sql.PHP_EOL);

        // Fetch table rows
        $result_table_rows = $db->query('SELECT * FROM `'.$table.'`;');
        $field_count = $db->field_count;

        echo 'Field Count: '.$field_count.'<br />';

        $sql_start = 'INSERT INTO `'.$table.'`';
        $sql = $sql_start;

        // For each table row
        while ($row_table = $result_table_rows->fetch_array()) {

            $new_sql = PHP_EOL.'VALUES (';

            for ($i = 0; $i < $field_count; $i++) {
                $new_sql .= '"'.$row_table[$i].'", '; // Already escaped in the original database
            }

            $new_sql = substr($new_sql, 0, -2).'),';

            if (strlen($sql.$new_sql) > $max_allowed_packet) {
                $sql = substr($sql, 0, -1).';';
                echo $sql.PHP_EOL;
                $sql = $sql_start.$new_sql;
            } else {
                $sql .= $new_sql;
            }
        }

        $sql = substr($sql, 0, -1).';';
        echo $sql.PHP_EOL;

        break; // one table
    }

    // Close output file
    fclose($file);

    echo 'Success<br />';

    echo '<br />';

    break; // one database
}

// Close database connection
$db->close();

// TODO: Handle errors and exceptions gracefully
