<?php
/**
 * cleanup_model_images.php
 *
 * 1️⃣  Moves **orphan** images from  /models  →  /archive/models   (dry‑run by default)
 * 2️⃣  Restores **archived** images that once again appear in `notebro_db.Model`
 *     from  /archive/models  →  /models
 * 3️⃣  Deletes any files that are referenced by **neither** `notebro_db.Model`
 *     nor `notebro_arch.Model`.
 *
 * ▸ Pass `--apply` (CLI) or `?apply=1` (web) to perform the operations — the
 *   actual `rename()` / `unlink()` calls remain commented for an extra layer
 *   of safety. Uncomment them once you’re ready.
 * ▸ Large per‑file logs print only when `--verbose` (CLI) or `?verbose=1` (web)
 *   is supplied; otherwise you get concise section summaries.
 */

/* === 1. CONFIGURE THESE SETTINGS ==================================== */
require_once __DIR__ . '/../etc/con_db.php';   // sets $con (mysqli connection)

$basePath        = dirname(__DIR__, 2) . '/res/img';
$modelDir        = $basePath . '/models';
$modelThumbDir   = $modelDir . '/thumb';
$archiveDir      = $basePath . '/archive/models';
$archiveThumbDir = $archiveDir . '/thumb';

/* =================================================================== */

$isCli  = (PHP_SAPI === 'cli');
$apply  = $isCli ? in_array('--apply', $argv ?? [], true)
                 : isset($_GET['apply']);
// Default to *verbose* when not applying (dry‑run) — override with --quiet / ?verbose=0
$verbose = $isCli ? (in_array('--verbose', $argv ?? [], true) || !$apply)
                  : (isset($_GET['verbose']) ? (bool)$_GET['verbose'] : !$apply);

// Helper: conditional output
function out(string $msg, bool $always = false): void
{
    global $verbose, $isCli;
    if ($always || $verbose) {
        if ($isCli) {
            echo $msg . PHP_EOL;
        } else {
            echo htmlspecialchars($msg) . "<br>\n";
        }
    }
}

// Helper: portable error output
function log_error(string $msg): void
{
    if (defined('STDERR')) {
        fwrite(STDERR, $msg . PHP_EOL);
    } else {
        error_log($msg);
        echo '<span style="color:red">' . htmlspecialchars($msg) . "</span><br>\n";
    }
}

function ensure_dir(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException("Cannot create directory: $dir");
    }
}

try {
    /* === 2. COLLECT EXPECTED FILENAMES FROM *BOTH* DATABASES ====== */
    if (!mysqli_set_charset($con, 'utf8mb4')) {
        throw new RuntimeException('Cannot set charset: ' . mysqli_error($con));
    }

    $expectedCurrent = $expectedArchiveDB = [];
    $tables = [
        'notebro_db'   => &$expectedCurrent,
        'notebro_arch' => &$expectedArchiveDB,
    ];

    foreach ($tables as $dbName => &$targetArray) {
        if (!mysqli_select_db($con, $dbName)) {
            log_error("Warning: cannot select $dbName — " . mysqli_error($con));
            continue;
        }
        $sql    = 'SELECT img_1, img_2, img_3, img_4 FROM `MODEL`';
        $result = mysqli_query($con, $sql);
        if (!$result) {
            log_error("Warning: query failed in $dbName — " . mysqli_error($con));
            continue;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            foreach (['img_1', 'img_2', 'img_3', 'img_4'] as $col) {
                $val = trim((string)($row[$col] ?? ''));
                if ($val !== '') {
                    $targetArray[$val]        = true;
                    $targetArray['t_' . $val] = true;
                }
            }
        }
        mysqli_free_result($result);
    }

    // Merge set of *all* referenced names across both DBs
    $expectedAll = $expectedCurrent + $expectedArchiveDB;

    /* === 3. SECTION ONE: ARCHIVE ORPHANS =========================== */
    out("\n=== SECTION 1: Move Orphan Images to Archive ===", true);

    $fullImages  = array_diff(scandir($modelDir) ?: [], ['.', '..']);
    $thumbImages = array_diff(scandir($modelThumbDir) ?: [], ['.', '..']);

    $orphansFull  = [];
    $orphansThumb = [];

    foreach ($fullImages as $file) {
        if (!isset($expectedCurrent[$file]) && is_file("$modelDir/$file")) {
            $orphansFull[] = $file;
        }
    }
    foreach ($thumbImages as $file) {
        if (!isset($expectedCurrent[$file]) && is_file("$modelThumbDir/$file")) {
            $orphansThumb[] = $file;
        }
    }

    if ($apply) {
        ensure_dir($archiveDir);
        ensure_dir($archiveThumbDir);
    }

    $movedFull = $movedThumb = 0;

    foreach ($orphansFull as $file) {
        $src = "$modelDir/$file";
        $dst = "$archiveDir/$file";
        if ($apply) {
            rename($src, $dst);
            out("[MOVE] $src → $dst");
            $movedFull++;
        } else {
            out("[DRY-RUN] Would move $src → $dst");
        }
    }
    foreach ($orphansThumb as $file) {
        $src = "$modelThumbDir/$file";
        $dst = "$archiveThumbDir/$file";
        if ($apply) {
            rename($src, $dst);
            out("[MOVE] $src → $dst");
            $movedThumb++;
        } else {
            out("[DRY-RUN] Would move $src → $dst");
        }
    }

    out(sprintf('Section 1: %d full‑size and %d thumbnails queued for archive.', count($orphansFull), count($orphansThumb)), true);

    /* === 4. SECTION TWO: RESTORE VALID ARCHIVED IMAGES ============= */
    out("\n=== SECTION 2: Restore Archived Images for Existing Models ===", true);

    $archFullImages  = array_diff(scandir($archiveDir) ?: [], ['.', '..']);
    $archThumbImages = array_diff(scandir($archiveThumbDir) ?: [], ['.', '..']);

    $restoreFull  = [];
    $restoreThumb = [];

    foreach ($archFullImages as $file) {
        if (isset($expectedCurrent[$file])) {
            $restoreFull[] = $file;
        }
    }
    foreach ($archThumbImages as $file) {
        if (isset($expectedCurrent[$file])) {
            $restoreThumb[] = $file;
        }
    }

    if ($apply) {
        ensure_dir($modelDir);
        ensure_dir($modelThumbDir);
    }

    $restoredFull = $restoredThumb = 0;

    foreach ($restoreFull as $file) {
        $src = "$archiveDir/$file";
        $dst = "$modelDir/$file";
        if ($apply) {
            rename($src, $dst);
            out("[RESTORE] $src → $dst");
            $restoredFull++;
        } else {
            out("[DRY-RUN] Would restore $src → $dst");
        }
    }
    foreach ($restoreThumb as $file) {
        $src = "$archiveThumbDir/$file";
        $dst = "$modelThumbDir/$file";
        if ($apply) {
            rename($src, $dst);
            out("[RESTORE] $src → $dst");
            $restoredThumb++;
        } else {
            out("[DRY-RUN] Would restore $src → $dst");
        }
    }

    out(sprintf('Section 2: %d full‑size and %d thumbnails queued for restore.', count($restoreFull), count($restoreThumb)), true);

/* === 5. SECTION THREE: DELETE TRULY UNUSED FILES =================== */
out("\n=== SECTION 3: Delete Unreferenced Images (Neither DB) ===", true);

$deleteTargets = [];
$dirs = [
    $modelDir,
    $modelThumbDir,
    $archiveDir,
    $archiveThumbDir,
];

foreach ($dirs as $dir) {
    foreach (array_diff(scandir($dir) ?: [], ['.', '..']) as $file) {
        $path = "$dir/$file";

        /* 🚫  Skip anything that is **not** a regular file (i.e. directories,
           symlinks, sockets…).  This single guard keeps folders safe. */
        if (!is_file($path)) {
            continue;
        }

        if (!isset($expectedAll[$file])) {
            $deleteTargets[] = $path;   // store full path for convenience
        }
    }
}

$deleted = 0;
foreach ($deleteTargets as $path) {
    if ($apply) {
        // unlink($path);   // ← uncomment when ready
        out("[DELETE] $path");
        $deleted++;
    } else {
        out("[DRY-RUN] Would delete $path");
    }
}

out(sprintf('Section 3: %d files queued for deletion.', count($deleteTargets)), true);

    /* === 6. FINAL SUMMARY ========================================== */
    out("\n=== SUMMARY ===", true);
    out(sprintf('Archive->%d/%d, Restore->%d/%d, Delete->%d', count($orphansFull), count($orphansThumb), count($restoreFull), count($restoreThumb), count($deleteTargets)), true);

    mysqli_close($con);

} catch (Throwable $e) {
    log_error('Error: ' . $e->getMessage());
    exit(1);
}
?>
