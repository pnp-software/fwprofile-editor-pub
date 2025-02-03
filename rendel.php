<?php

require_once 'inc_db.php';
require_once 'inc_lib.php';
require_once 'inc_session.php';

// Works only if user is logged in.
if (userID() > 0) {
    // Get and sanitize input values with defaults if not provided.
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $data = $_POST['data'] ?? '';
    $projname = isset($_POST['projname']) ? trim($_POST['projname']) : '';
    $projectFiles = $_POST['projectFiles'] ?? []; // default to an empty array

    $perm = access_rights($id);
    if ($perm !== 'ow' && $perm !== 'rw') {
        die("You do not have permissions for this");
    }

    // Retrieve the requested action.
    $action = $_REQUEST['ac'] ?? '';

    if ($action === 'rename') {
        // Check permissions if user can write.
        if ($name === '') {
            die("Filename cannot be empty");
        }
        $oldname = mysqli_result(execQuery("SELECT name FROM diagrams WHERE userID=" . userID() . " AND id=" . $id), 0);
        if ($oldname !== $name) {
            execQuery("UPDATE diagrams SET name=\"" . mysqli_escape($name) . "\", lastUpdate=NOW() WHERE id=" . $id);
        }
        execQuery("UPDATE IGNORE diagrams SET fwprop=\"" . mysqli_escape($data) . "\", lastUpdate=NOW() WHERE id=" . $id);

        // Check whether we have to rename other project diagrams as well.
        foreach ($projectFiles as $pid) {
            $pid = (int) $pid;
            $perm = access_rights($pid);
            if ($perm !== 'ow' && $perm !== 'rw') {
                die("You do not have permissions for this");
            }
            
            $str_fwprop = mysqli_result(execQuery("SELECT fwprop FROM diagrams WHERE id=" . $pid), 0);
            $fwprop = json_decode($str_fwprop);
            // Check that the structure exists before setting the new value.
            if (isset($fwprop->globals->fwprop)) {
                $fwprop->globals->fwprop->smTags = $projname;
            }
            $str_fwprop = json_encode($fwprop);
            execQuery("UPDATE diagrams SET fwprop=\"" . mysqli_escape($str_fwprop) . "\", lastUpdate=NOW() WHERE id=" . $pid);
        }
    }

    if ($action === 'delete') {
        execQuery("DELETE FROM diagrams WHERE userID=" . userID() . " AND id=" . $id);
        if (affectedRows() != 1) {
            die("Only owner can delete this diagram.");
        }
    }

    die("ok");
} else {
    die("User not signed in");
}

?>
