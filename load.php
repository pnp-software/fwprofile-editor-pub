<?php
ob_start("ob_gzhandler");

require_once 'inc_db.php';
require_once 'inc_lib.php';
require_once 'inc_session.php';
require 'version.php';

// Use null coalescing to avoid an undefined array key warning if 'known' is not provided.
$known = (array) ($_REQUEST['known'] ?? []);

// Get the user's map data.
$map = @mysqli_result(execQuery("SELECT idMap FROM users WHERE id=" . userID()), 0);
if (trim($map) == '') {
    $map = "{}";
}

// Output the beginning of the JSON structure.
echo "{\"userID\": " . json_encode(userID()) . ", \"username\": " . json_encode($_SESSION['name']) . ",
      \"idMap\": " . json_encode($map) . ", \"version\": " . json_encode($version) . ",
      \"diagrams\":[";

$i = 0;

// Check if the user is logged in.
if (userID() > 0) {
    // Get all diagrams owned by the user, plus any shared with the user.
    $shared_ids = shared_diagram_ids();
    $sharing_ids = sharing_diagram_ids();
    $result = execQuery(
        "SELECT *, CASE WHEN id IN (0" . join(',', $sharing_ids) . ") THEN 1 ELSE 0 END as isShared, " .
        "UNIX_TIMESTAMP(lastUpdate) as lastUpdate, UNIX_TIMESTAMP() as curtime FROM diagrams WHERE userID=" . userID() .
        (count($shared_ids) > 0 ? " OR id IN (" . join(",", $shared_ids) . ")" : "")
    );

    // Process each diagram row.
    while ($row = mysqli_fetch_assoc($result)) {
        if ($i++ > 0) {
            echo ",";
        }

        // Retrieve known entry if available; otherwise, $entry will be null.
        $entry = $known[$row['id']] ?? null;
        if ($entry !== null
            && isset($entry['lu'], $entry['n'])
            && $entry['lu'] == $row['lastUpdate']
            && $entry['n'] == $row['name']
        ) {
            // If the last update and name match, set change to "none".
            $row = array("id" => $row['id'], "change" => "none");
        } else {
            // Otherwise, mark the diagram as updated.
            $row['change'] = 'updt';
        }
        echo json_encode($row);

        // Remove the processed diagram from the $known array.
        unset($known[$row['id']]);
    }

    // For any remaining diagram IDs in $known (i.e. those that no longer exist),
    // output an entry with "noex" change.
    foreach ($known as $id => $value) {
        echo (($i > 0) ? "," : "") . json_encode(array("id" => $id, "change" => "noex"));
        $i++;
    }
}

echo "]}";
?>
