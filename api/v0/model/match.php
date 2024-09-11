<?php

require_once __DIR__."/../includes/db.php";

require_once __DIR__."/person.php";
require_once __DIR__."/team.php";

function match_get_status($match_id, $connector = null) {
    if ($connector) {
        $mysqli = $connector;
    } else {
        $mysqli = db_connector();
    }

    $status = array();

    $stmt = $mysqli->prepare(
"
SELECT fighter1Score
     , fighter2Score
     , matchComplete
     , winnerID
     , matchTime
  FROM eventMatches
 WHERE eventMatches.matchID = ?
"
    );
    $stmt->bind_param('i', $match_id);
    $stmt->execute();
    if ($result = $stmt->get_result()) {
        $row = $result->fetch_assoc();
        $status['scores'] = array($row['fighter1Score'], $row['fighter2Score']);
        $status['time'] = $row['matchTime'];
        $status['complete'] = (bool) $row['matchComplete'];
        $status['winner_id'] = $row['winnnerID'];
    }

    if (!$connector) {
        $mysqli->close();
    }
    return $status;
}

function get_match($match_id) {
    $mysqli = db_connector();

    $stmt = $mysqli->prepare(
"
    SELECT eventMatches.matchID
         , eventMatches.groupID
         , eventMatches.matchNumber
         , eventMatches.fighter1ID
         , eventMatches.fighter2ID
         , eventGroups.groupName
         , eventTournaments.tournamentID
         , eventTournaments.isTeams
         , sTWeapon.tournamentType AS tournamentWeapon
         , sTPrefix.tournamentType AS tournamentPrefix
         , sTGender.tournamentType AS tournamentGender
         , sTMaterial.tournamentType AS tournamentMaterial
      FROM eventMatches
INNER JOIN eventGroups ON eventGroups.groupID = eventMatches.groupID
INNER JOIN eventTournaments ON eventTournaments.tournamentID = eventGroups.tournamentID
INNER JOIN systemTournaments AS sTWeapon   ON sTWeapon.tournamentTypeID   = eventTournaments.tournamentWeaponID
INNER JOIN systemTournaments AS sTPrefix   ON sTPrefix.tournamentTypeID   = eventTournaments.tournamentPrefixID
INNER JOIN systemTournaments AS sTGender   ON sTGender.tournamentTypeID   = eventTournaments.tournamentGenderID
INNER JOIN systemTournaments AS sTMaterial ON sTMaterial.tournamentTypeID = eventTournaments.tournamentMaterialID
     WHERE eventMatches.matchID = ?
"
    );
    //printf("Error: %s\n", $mysqli->error);
    $stmt->bind_param('i', $match_id);
    $stmt->execute();

    $match = null;
    if ($result = $stmt->get_result()) {
        $row = $result->fetch_assoc();

        $match = array();
        $match['id']            = $row['matchID'];
        $match['group_id']      = $row['groupID'];
        $match['group_name']    = $row['groupName'];
        $match['tournament_id'] = $row['tournamentID'];

        // determine tournament name
        $tournament_name = $row['tournamentWeapon'];
        if ($row['tournamentPrefix'] || $row['tournamentGender'] || $row['tournamentMaterial']) {
            $tournament_name .= ' -';
            if ($row['tournamentPrefix']) $tournament_name .= ' '.$row['tournamentPrefix'];
            if ($row['tournamentGender']) $tournament_name .= ' '.$row['tournamentGender'];
            if ($row['tournamentMaterial']) $tournament_name .= ' '.$row['tournamentMaterial'];
        }
        $match['tournament_name'] = $tournament_name;

        // null in fighters or teams determines if it's a team match
        if ($row['isTeams'] == 1) {
            $match['fighters'] = null;
            $match['teams']    = array(get_team($row['fighter1ID']), get_team($row['fighter2ID']));
        } else {
            $match['fighters'] = array(get_person_event($row['fighter1ID']), get_person_event($row['fighter2ID']));
            $match['teams']    = null;
        }

        $match['status'] = match_get_status($match_id, $mysqli);
        $match['exchanges'] = match_get_exchanges($match_id, 0, $mysqli);
    }
    $mysqli->close();
    return $match;
}

function exchange_from_row($row) {
    $exchange = array();

    $exchange['id']                 = $row['exchangeID'];
    $exchange['match_id']           = $row['matchID'];
    $exchange['type']               = $row['exchangeType'];
    $exchange['scoring_id']         = $row['scoringID'];
    $exchange['receiving_id']       = $row['receivingID'];
    $exchange['score_value']        = $row['scoreValue'];
    $exchange['score_deduction']    = $row['scoreDeduction'];
    $exchange['number']             = $row['exchangeNumber'];
    $exchange['time']               = $row['exchangeTime'];

    // attack as own object
    $attack = array();
    $attack['prefix']               = $row['attackPrefix'];
    $attack['target']               = $row['attackTarget'];
    $attack['type']                 = $row['attackType'];
    $attack['prefix_text']          = $row['attackPrefixText'];
    $attack['target_text']          = $row['attackTargetText'];
    $attack['type_text']            = $row['attackTypeText'];

    $exchange['attack']             = $attack;
    $exchange['timestamp']          = $row['timestamp'];

    return $exchange;
}

function match_get_exchanges($match_id, $after, $connector=null) {
    if ($connector) {
        $mysqli = $connector;
    } else {
        $mysqli = db_connector();
    }

    $stmt = $mysqli->prepare(
"
   SELECT eventExchanges.exchangeID
        , eventExchanges.matchID
        , eventExchanges.exchangeType
        , eventExchanges.scoringID
        , eventExchanges.receivingID
        , eventExchanges.scoreValue
        , eventExchanges.scoreDeduction
        , eventExchanges.exchangeNumber
        , eventExchanges.exchangeTime
        , sAPrefix.attackCode AS attackPrefix
        , sATarget.attackCode AS attackTarget
        , sAType.attackCode   AS attackType
        , sAPrefix.attackText AS attackPrefixText
        , sATarget.attackText AS attackTargetText
        , sAType.attackText   AS attackTypeText
        , eventExchanges.timestamp
     FROM eventExchanges
LEFT JOIN systemAttacks AS sAPrefix ON sAPrefix.attackID = eventExchanges.refPrefix
LEFT JOIN systemAttacks AS sATarget ON sATarget.attackID = eventExchanges.refTarget
LEFT JOIN systemAttacks AS sAType   ON sAType.attackID   = eventExchanges.refType
    WHERE eventExchanges.matchID = ?
      AND eventExchanges.exchangeNumber > ?
 ORDER BY exchangeNumber ASC
"
    );
    $stmt->bind_param('ii', $match_id, $after);
    $stmt->execute();

    $exchanges = array();
    if ($result = $stmt->get_result()) {
        while ($row = $result -> fetch_assoc()) {
            array_push($exchanges, exchange_from_row($row));
        }
    }

    if (!$connector) {
        $mysqli->close();
    }
    return $exchanges;
}
