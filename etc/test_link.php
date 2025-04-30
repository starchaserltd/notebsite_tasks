<?php
/**
 * Single-file dual-DB connection example.
 *
 * IMPORTANT:
 *  - The credentials below are only for example purposes.
 *  - Before deploying, replace the example values with your real credentials.
 *  - Never commit real passwords or hosts to source control.
 */

/* ──────────────────────────────────────────────────────
   Example credentials for “sdb” (read-shard) DB:
────────────────────────────────────────────────────── */
define('DB_SDB_USER',     'example_sdb_user');        // e.g. ''
define('DB_SDB_PASSWORD', 'example_sdb_pass@123');    // e.g. ''
define('DB_SDB_NAME',     'example_sdb_database');    // e.g. 'notebro_temp'
define('DB_SDB_HOST',     '203.0.113.10');            // e.g. '
define('DB_SDB_PORT',     3306);                      // e.g. 3306

/* ──────────────────────────────────────────────────────
   Example credentials for “main” DB:
────────────────────────────────────────────────────── */
define('DB_MAIN_USER',     'example_main_user');      // e.g. ''
define('DB_MAIN_PASSWORD', 'example_main_pass&456');  // e.g. ''
define('DB_MAIN_NAME',     'example_main_database');  // e.g. ''
define('DB_MAIN_HOST',     '198.51.100.20');          // e.g. ''
define('DB_MAIN_PORT',     3306);                     // e.g. 3306

// Connect to the “sdb” (sharded/read) database
$sdbLink = mysqli_init();
$sdbConn = mysqli_real_connect(
    $sdbLink,
    DB_SDB_HOST,
    DB_SDB_USER,
    DB_SDB_PASSWORD,
    DB_SDB_NAME,
    DB_SDB_PORT
);
if ($sdbConn === false) {
    // Handle error (log, alert, etc.)
    die('SDB connection error: ' . mysqli_connect_error());
}

// Connect to the main application database
$mainLink = mysqli_init();
$mainConn = mysqli_real_connect(
    $mainLink,
    DB_MAIN_HOST,
    DB_MAIN_USER,
    DB_MAIN_PASSWORD,
    DB_MAIN_NAME,
    DB_MAIN_PORT
);
if ($mainConn === false) {
    // Handle error (log, alert, etc.)
    die('Main DB connection error: ' . mysqli_connect_error());
}

// From here you can use $sdbLink and $mainLink as your mysqli resources.
