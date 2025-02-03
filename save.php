<?php
require_once 'inc_db.php';
require_once 'inc_lib.php';
require_once 'inc_session.php';

if (userID() == 0) {
    return; // Anonymous users can't save anything.
}

// Retrieve POST variables with defaults to avoid undefined index warnings.
$idMap      = $_POST['idMap']      ?? '';
$name       = $_POST['name']       ?? '';
$viewbox    = $_POST['viewbox']    ?? '{}';
$svg        = $_POST['svg']        ?? '';
$asID       = $_POST['asID']       ?? '0';
$fwprop     = $_POST['fwprop']     ?? '';
$editorType = $_POST['editorType'] ?? '';
$commit     = $_POST['commit']     ?? '';

// Update the user's idMap.
execQuery("UPDATE users SET idMap=\"" . mysqli_escape($idMap) . "\" WHERE id=" . userID());

// If the trimmed name is empty, this indicates that only idMap is being saved.
if (trim($name) == '') {
    die("");
}

// Decode viewbox JSON. If decoding fails or is not an array, default dimensions are used.
$box = json_decode($viewbox, true);
if (!is_array($box)) {
    $box = [];
}
$box['width']  = (isset($box['width']) && (int)$box['width'] !== 0)  ? (int)$box['width']  : 800;
$box['height'] = (isset($box['height']) && (int)$box['height'] !== 0) ? (int)$box['height'] : 400;

$existingID = (int)$asID;
$perm = '';
if ($existingID !== 0) {
    $perm = access_rights($existingID);
}

// Save diagram if the user has write permissions (or if it's a new diagram).
if ($perm === 'ow' || $perm === 'rw' || $existingID === 0) {
    $uid = userID(); // Current user ID.
    
    if ($existingID > 0) {
        // If the diagram already exists, retrieve its record and use its owner.
        $row = mysqli_fetch_assoc(execQuery("SELECT * FROM diagrams WHERE id=" . $existingID));
        $uid = $row['userID'];
    }

    // Build the REPLACE query. Note the explicit (int) casts for numeric values.
    $query = "REPLACE INTO diagrams SET userID=" . $uid . ",
            name=\"" . mysqli_escape(trim($name)) . "\",
            lastUpdate=NOW(),
            fwprop=\"" . mysqli_escape($fwprop) . "\",
            svg=\"" . mysqli_escape($svg) . "\",
            width=" . ((int)$box['width']) . ", height=" . ((int)$box['height']) . ",
            editorType=\"" . preg_replace("{[^a-z ]}i", "", $editorType) . "\""
            . ($existingID > 0 ? ", id=" . $existingID : "");
    execQuery($query);

    // If a new row was inserted, update $existingID.
    $current = insertId();
    if ($current > 0) {
        $existingID = $current;
    }

    // If a commit is requested, save a history version.
    if ($commit === 'true') {
        $row = mysqli_fetch_assoc(execQuery("SELECT * FROM diagrams WHERE id=" . $existingID));
        $row['diagramID'] = $row['id'];
        $row['userID'] = userID(); // Mark the current user as the one who saved this.
        $row['id'] = 0;

        // Prepare each field for insertion into the history table.
        foreach ($row as $key => $val) {
            $row[$key] = "$key=\"" . mysqli_escape($val) . "\"";
        }
        execQuery("INSERT INTO history SET " . join(",", $row));
    }
} else {
    die("-1"); // No rights to write.
}

// Output the internal ID of the saved diagram.
echo $existingID;
?>
