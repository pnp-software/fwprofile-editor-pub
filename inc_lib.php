<?php

// Set locale so iconv() can work correctly.
setlocale(LC_ALL, "en_US");
setlocale(LC_COLLATE, "C");

/**
 * Sends HTTP headers to force the download of a file.
 *
 * @param string $name The name of the file to be downloaded.
 *
 * @return void
 */
function sendDownloadHeaders($name)
{
    header("Content-Type: application/x-unknown");
    header("Content-Disposition: attachment; filename=" . rawurlencode(trim($name)));
}

/**
 * Generates a password by alternating characters from two strings.
 *
 * @param int $length The length of the password to generate (default 8).
 *
 * @return string The generated password.
 */
function nicePassword($length = 8)
{
    $w1 = 'aeiou';
    $w2 = 'bcdfghjklmnprstvxz';
    $pass = ''; // Initialize the password variable.

    // Loop increments by 2 each iteration.
    for ($i = 1; $i < $length; $i++) {
        $pass .= $w2[mt_rand(0, strlen($w2) - 1)] . $w1[mt_rand(0, strlen($w1) - 1)];
        $i++;
    }
    return $pass;
}

/**
 * Logs debug information to a file.
 *
 * @param array $params An array of parameters to be logged.
 *
 * @return void
 */
function debug($params)
{
    $debug = debug_backtrace();
    // Safely retrieve file, function, and line from debug info.
    $file = isset($debug[1]['file']) ? basename($debug[1]['file']) : 'unknown file';
    $function = $debug[1]['function'] ?? 'unknown function';
    $line = $debug[1]['line'] ?? 'unknown line';

    // Convert each parameter to its printable representation.
    foreach ($params as &$param) {
        $param = print_r($param, true);
    }

    file_put_contents(
        "/tmp/debug.txt",
        date("Y-m-d H:i:s") . " - " . $file . " - " . $function . "[" . $line . "]: " . join(", ", $params) . "\n",
        FILE_APPEND
    );
}

/**
 * Determines the access rights for a diagram.
 *
 * Possible return values:
 * - 'ow' for owner,
 * - 'rw' for read-write,
 * - 'ro' for read-only.
 *
 * @param int $diagramID The diagram ID.
 *
 * @return string The access rights.
 */
function access_rights($diagramID)
{
    $diagramID = (int)$diagramID;

    $result = execQuery("SELECT * FROM diagrams WHERE id=" . $diagramID . " AND userID=" . userID());
    if (mysqli_num_rows($result) > 0) {
        return 'ow'; // owner
    }

    $email = get_user_email();
    $result = execQuery("SELECT perm FROM shares WHERE diagramID=" . $diagramID . " AND email=\"" . mysqli_escape($email) . "\"");
    $row = mysqli_fetch_assoc($result);

    // Check if the 'perm' key exists; if not, assume read-only.
    return isset($row['perm']) ? $row['perm'] : 'ro';
}

/**
 * Retrieves the email address of the currently logged-in user.
 *
 * @return string|null The email address, or null if not found.
 */
function get_user_email()
{
    $email = @mysqli_result(execQuery("SELECT email FROM users WHERE id=" . userID()), 0);
    return $email;
}

/**
 * Retrieves the IDs of diagrams shared with the current user.
 *
 * @return array An array of diagram IDs.
 */
function shared_diagram_ids()
{
    $email = get_user_email();
    $ids = array();
    $result = execQuery("SELECT diagramID FROM shares WHERE email=\"" . mysqli_escape($email) . "\"");
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['diagramID'];
    }
    return $ids;
}

/**
 * Retrieves the IDs of diagrams for which the current user is sharing access.
 *
 * @return array An array of diagram IDs.
 */
function sharing_diagram_ids()
{
    $ids = array();
    $result = execQuery("SELECT diagramID FROM diagrams LEFT JOIN shares ON shares.diagramID = diagrams.id WHERE diagrams.userID=" . userID() . " HAVING diagramID IS NOT NULL");
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['diagramID'];
    }
    return $ids;
}

?>
