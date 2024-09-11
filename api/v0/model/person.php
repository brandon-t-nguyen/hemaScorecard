<?php

require_once __DIR__."/../includes/db.php";

function person_from_row($row) {
    $person = array();
    $person['id']               = $row[0];
    $person['event_roster_id']  = null;
    $person['first_name']       = $row[1];
    $person['middle_name']      = $row[2];
    $person['last_name']        = $row[3];
    $person['nickname']         = $row[4];
    $person['gender']           = $row[5];
    $person['country']          = $row[6];
    $person['division']         = $row[7];
    $person['city']             = $row[8];
    $person['school_id']        = $row[9];
    $person['school_name']      = $row[10];
    $person['hemaratings_id']   = $row[11];
    return $person;
}

function get_person_internal($person_id, $event=true) {
    $mysqli = db_connector();

    if ($event) {
        $stmt = $mysqli->prepare(
"
    SELECT systemRoster.systemRosterID
         , systemRoster.firstName
         , systemRoster.middleName
         , systemRoster.lastName
         , systemRoster.nickname
         , systemRoster.gender
         , systemRoster.rosterCountry
         , systemRoster.rosterProvince
         , systemRoster.rosterCity
         , eventRoster.schoolID
         , systemSchools.schoolFullname
         , systemRoster.HemaRatingsID
      FROM eventRoster
INNER JOIN systemRoster ON systemRoster.systemRosterID = eventRoster.systemRosterID
INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
     WHERE eventRoster.rosterID = ?
"
        );
    } else {
        $stmt = $mysqli->prepare(
"
    SELECT systemRoster.systemRosterID
         , systemRoster.firstName
         , systemRoster.middleName
         , systemRoster.lastName
         , systemRoster.nickname
         , systemRoster.gender
         , systemRoster.rosterCountry
         , systemRoster.rosterProvince
         , systemRoster.rosterCity
         , systemRoster.schoolID
         , systemSchools.schoolFullname
         , systemRoster.HemaRatingsID
      FROM systemRoster
INNER JOIN systemSchools ON systemRoster.schoolID = systemSchools.schoolID
     WHERE systemRoster.systemRosterID = ?
"
        );
    }
    $stmt->bind_param('i', $person_id);
    $stmt->execute();

    if ($result = $stmt->get_result()) {
        $person = person_from_row($result->fetch_row());
    } else {
        $person = null;
    }

    $mysqli->close();
    return $person;
}

function get_person_event($event_roster_id) {
    $person = get_person_internal($event_roster_id, true);
    if ($person) {
        $person['event_roster_id'] = $event_roster_id;
    }
    return $person;
}

function get_person($system_roster_id) {
    return get_person_internal($system_roster_id, false);
}
