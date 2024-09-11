<?php

require_once __DIR__."/../includes/db.php";

function event_from_row($row) {
    $event = array();
    $event['id']           = $row[0];
    $event['name']         = $row[1];
    $event['abbrev']       = $row[2];
    $event['start_date']   = $row[3];
    $event['end_date']     = $row[4];
    $event['country_code'] = $row[5];
    $event['country']      = $row[6];
    $event['division']     = $row[7];
    $event['city']         = $row[8];
    $event['status']       = $row[9];
    return $event;
}

function get_events($after = null, $before = null, $id = null, $limit=50, $offset=0) {
    $mysqli = db_connector();

    $sql =
"
    SELECT systemEvents.eventID
         , systemEvents.eventName
         , systemEvents.eventAbbreviation
         , systemEvents.eventStartDate
         , systemEvents.eventEndDate
         , systemEvents.countryIso2
         , systemCountries.countryName
         , systemEvents.eventProvince
         , systemEvents.eventCity
         , systemEvents.eventStatus
      FROM systemEvents
INNER JOIN systemCountries ON systemEvents.countryIso2 = systemCountries.countryIso2
";

    $params = array();
    $types = '';
    if ($after and $before) {
        $sql .= "WHERE eventStartDate BETWEEN '?' AND '?'\n";
        array_push($params, $after, $before);
        $types .= 'ss';
    } elseif ($after) {
        $sql .= "WHERE eventStartDate >= '?'\n";
        array_push($params, $after);
        $types .= 's';
    } elseif ($before) {
        $sql .= "WHERE eventStartDate <= '?'\n";
        array_push($params, $before);
        $types .= 's';
    }

    if ($id) {
        $sql .= "WHERE eventID = ?\n";
        array_push($params, $id);
        $types .= 'i';
    }

    $sql .= "ORDER BY eventStartDate DESC\n";
    $sql .= "LIMIT ?,?\n";
    array_push($params, $offset, $limit);
    $types .= 'ii';

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $events = array();
    if ($result = $stmt->get_result()) {
        while ($row = $result -> fetch_row()) {
            array_push($events, event_from_row($row));
        }
    }

    $out = $events;

    // if supplying ID, return the object directly instead of array
    if ($id) {
        $out = count($events) > 0 ? $events[0] : null;
    }

    $mysqli->close();
    return $out;
}
