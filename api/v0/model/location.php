<?php

require_once __DIR__."/../includes/db.php";

function location_from_row($row) {
    $loc = array();
    $loc['id']          = $row[0];
    $loc['event_id']    = $row[1];
    $loc['name']        = $row[2];
    $loc['name_short']  = $row[3];
    $loc['has_matches'] = $row[4];
    $loc['has_classes'] = $row[5];
    return $loc;
}

function get_locations($event_id = null, $location_id = null) {
    $mysqli = db_connector();

    $sql =
"
SELECT locationID
     , eventID
     , locationName 
     , locationNameShort
     , hasMatches
     , hasClasses
  FROM logisticsLocations
";

    $params = array();
    $types = '';
    if ($event_id) {
        $sql .= "WHERE eventID = ?\n";
        array_push($params, $event_id);
        $types .= 'i';
    }

    if ($location_id) {
        $sql .= "WHERE locationID = ?\n";
        array_push($params, $location_id);
        $types .= 'i';
    }

    $stmt = $mysqli->prepare($sql);

    if (count($params) > 0) {
        // bind the optional args
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $locations = array();
    if ($result = $stmt->get_result()) {
        while ($row = $result -> fetch_row()) {
            array_push($locations, location_from_row($row));
        }
    }

    // if supplying ID, return the object directly instead of array
    if ($location_id) {
        $out = count($locations) > 0 ? $locations[0] : null;
    } else {
        $out = $locations;
    }

    $mysqli->close();
    return $out;
}

function location_get_active_match($location_id) {
    // WARNING: this is in flux right now and is very hacky
    // currently piggybacks off of the livestream active ring info
    // hopefully there will be a more straightforward method in the future

    // pull the appropriate eventVideoStream for this location and grab the matchID in the associated eventVideo
    $mysqli = db_connector();

    $match = null;
    $stmt = $mysqli->prepare(
"
    SELECT eventVideo.matchID
      FROM eventVideoStreams
INNER JOIN eventVideo on eventVideoStreams.videoID = eventVideo.videoID
     WHERE eventVideoStreams.locationID = ?
"
    );
    $stmt->bind_param('i', $location_id);
    $stmt->execute();

    if ($result = $stmt->get_result()) {
        $row = $result->fetch_row();
        $match_id = $row[0];
        $match = $match_id;
    }

    $mysqli->close();
    return $match;
}
