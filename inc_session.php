<?php
require_once 'inc_db.php';
require_once 'inc_lib.php';

/**
 * Returns the current user's ID from the session.
 *
 * @return int The user ID if set, otherwise 0.
 */
function userID(): int
{
    return isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
}

/**
 * Open the session.
 *
 * This function is called when a session is started.
 *
 * @param string $save_path    The path where session data is stored (not used here).
 * @param string $session_name The name of the session.
 *
 * @return bool Returns true on success.
 */
function sqlses_open(string $save_path, string $session_name): bool
{
    // Initialization can be done here if needed.
    return true;
}

/**
 * Close the session.
 *
 * This function is called when the session operation is complete.
 *
 * @return bool Returns true on success.
 */
function sqlses_close(): bool
{
    // Cleanup can be done here if needed.
    return true;
}

/**
 * Read session data from the database.
 *
 * Retrieves the session data for the given session ID.
 *
 * @param string $id The session ID.
 *
 * @return string The session data as a string, or an empty string if not found.
 */
function sqlses_read(string $id): string
{
    $result = execQuery(
        "SELECT sessionData FROM sessions WHERE id='" . mysqli_escape($id) . "' LIMIT 1"
    );
    $data = @mysqli_result($result, 0);
    return is_string($data) ? $data : '';
}

/**
 * Write session data to the database.
 *
 * Stores the session data along with additional metadata.
 *
 * @param string $id   The session ID.
 * @param string $data The session data to be saved.
 *
 * @return bool Returns true on success.
 */
function sqlses_write(string $id, string $data): bool
{
    execQuery(
        "REPLACE INTO sessions SET id='" . mysqli_escape($id) .
        "', lastUpdate=NOW(), userID='" . mysqli_escape(userID()) .
        "', userIP='" . mysqli_escape($_SERVER['REMOTE_ADDR']) .
        "', sessionData='" . mysqli_escape($data) . "'"
    );
    return true;
}

/**
 * Destroy a session.
 *
 * Deletes the session data from the database for the given session ID.
 *
 * @param string $id The session ID.
 *
 * @return bool Returns true on success.
 */
function sqlses_destroy(string $id): bool
{
    execQuery("DELETE FROM sessions WHERE id='" . mysqli_escape($id) . "'");
    return true;
}

/**
 * Cleanup old sessions.
 *
 * Removes session records that have expired.
 *
 * @param int $maxlifetime The maximum lifetime of a session in seconds.
 *
 * @return int|false Returns the number of deleted sessions, or false on failure.
 */
function sqlses_gc(int $maxlifetime): int|false
{
    execQuery("DELETE FROM sessions WHERE lastUpdate < NOW() - INTERVAL 24 HOUR");
    $rows = affectedRows();
    return $rows !== false ? (int)$rows : false;
}

// Set session cookie parameters (session cookie expires when the browser closes).
session_set_cookie_params(0);

// Register the custom session handler callbacks.
session_set_save_handler(
    'sqlses_open',
    'sqlses_close',
    'sqlses_read',
    'sqlses_write',
    'sqlses_destroy',
    'sqlses_gc'
);

// Start the session.
session_start();
?>
