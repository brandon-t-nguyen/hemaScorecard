<?php

require_once __DIR__."/../includes/db.php";
require_once __DIR__."/person.php";

function get_team($team_id) {
    $mysqli = db_connector();

    $team = array();

    $sql =
"
SELECT teamID
     , memberName
  FROM eventTeamRoster
 WHERE memberRole = 'teamName' AND teamID = ?
";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();

    $team['id'] = $row[0];
    $team['name'] = $row[1];

    // get list of member IDs
    $members = array();
    $sql =
"
  SELECT rosterID
    FROM eventTeamRoster
   WHERE memberRole = 'member' AND teamID = ?
ORDER BY teamOrder ASC
";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();

    // we will get a list of (event) roster IDs, use each of them to get the appropriate person
    $result = $stmt->get_result();
    while ($row = $result->fetch_row()) {
        $member = get_person_event($row[0]);
        array_push($members, $member);
    }

    $team['members'] = $members;

    $mysqli->close();
    return $team;
}
