<?php
/**
 * This file configures the search server db connection
 * Single-file “sharded” DB connection example with optional host override.
 *
 * IMPORTANT:
 *  - The credentials below are only for example purposes.
 *  - Before deploying, replace the example values with your real credentials.
 *  - Never commit real passwords or hosts to source control.
 */

/* ──────────────────────────────────────────────────────
   Example credentials (replace these!):
────────────────────────────────────────────────────── */
define('DB_SUSER',     'example_sdb_user');       // e.g. 'notebro_sdb'
define('DB_SPASSWORD', 'example_sdb_pass@123');   // e.g. ''
define('DB_SNAME',     'example_database');       // e.g. 'notebro_temp'
define('DB_SPORT',     '3306');                   // e.g. ''
/* ────────────────────────────────────────────────────── */

function dbs_connect($host_ip = null)
{
    // Read the list of shard servers from file
    $lines = file('<path_to>vault/etc/sservers', FILE_SKIP_EMPTY_LINES);
    $servers = [];
    foreach ($lines as $line) {
        $parts = preg_split('/\s+/', trim($line));
        $servers[] = $parts;
    }

    // Assume the second line (index 1) contains host IPs after an optional label
    if (!isset($servers[1]) || count($servers[1]) < 2) {
        trigger_error('No valid hosts found in sservers file', E_USER_ERROR);
    }
    // Drop the first element (label) if present
    array_shift($servers[1]);
    $hosts = $servers[1];

    $port = (int)DB_SPORT;
    $links = [];
    $shardIndex = 1;

    // Override logic: if a specific host IP was provided, use that
    if ($host_ip !== null) {
        $hosts[$shardIndex] = $host_ip;
    } elseif (isset($GLOBALS['server'])) {
        // If globally set, use that shard list (first line)
        $shardIndex = $GLOBALS['server'];
        if (!empty($servers[0])) {
            $hosts = $servers[0];
        }
    }

    // Connect to the chosen host
    static $link;
    $link = mysqli_init();
    $success = mysqli_real_connect(
        $link,
        $hosts[$shardIndex],
        DB_SUSER,
        DB_SPASSWORD,
        DB_SNAME,
        $port
    );

    if ($success === false) {
        // Connection failed: return error message
        return mysqli_connect_error();
    }

    // Store link and reset global shard pointer
    $links[] = $link;
    $GLOBALS['server'] = 0;

    return $links;
}

// Utility to check for mysqli result rows
if (!function_exists('have_results')) {
    function have_results($result)
    {
        return ($result && mysqli_num_rows($result) > 0);
    }
}
