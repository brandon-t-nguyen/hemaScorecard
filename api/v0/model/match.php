<?php

require_once __DIR__."/../includes/db.php";

require_once __DIR__."/person.php";
require_once __DIR__."/team.php";

function get_match_status($match_id, $connector = null) {
    if ($connector) {
        $mysqli = $connector;
    } else {
        $mysqli = db_connector();
    }

    $status = array();

    $stmt = $mysqli->prepare(
"
SELECT
    fighter1Score,
    fighter2Score,
    matchComplete,
    winnerID,
    matchTime
FROM
    eventMatches
WHERE
    eventMatches.matchID = ?
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
        $status['exchanges'] = match_get_exchanges($match_id, $mysqli);
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
SELECT
    eventMatches.matchID
    , eventMatches.groupID
    , eventMatches.matchNumber
    , eventMatches.fighter1ID
    , eventMatches.fighter2ID
    , eventGroups.groupName
    , eventTournaments.tournamentID
    , eventTournaments.isTeams
    , sTWeapon.tournamentType as tournamentWeapon
    , sTPrefix.tournamentType as tournamentPrefix
    , sTGender.tournamentType as tournamentGender
    , sTMaterial.tournamentType as tournamentMaterial
FROM
    eventMatches
INNER JOIN eventGroups ON eventGroups.groupID = eventMatches.groupID
INNER JOIN eventTournaments ON eventTournaments.tournamentID = eventGroups.tournamentID
INNER JOIN systemTournaments as sTWeapon ON sTWeapon.tournamentTypeID = eventTournaments.tournamentWeaponID
INNER JOIN systemTournaments as sTPrefix ON sTPrefix.tournamentTypeID = eventTournaments.tournamentPrefixID
INNER JOIN systemTournaments as sTGender ON sTGender.tournamentTypeID = eventTournaments.tournamentGenderID
INNER JOIN systemTournaments as sTMaterial ON sTMaterial.tournamentTypeID = eventTournaments.tournamentMaterialID
WHERE
    eventMatches.matchID = ?
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
        $match['tournament_name'] = trim($row['tournamentGender'] . " ". $row['tournamentMaterial'] . " " . $row['tournamentWeapon'] . " - " . $row['tournamentPrefix']);

        if ($row['isTeams'] == 1) {
            $match['fighters'] = null;
            $match['teams']    = array(get_team($row['fighter1ID']), get_team($row['fighter2ID']));
        } else {
            $match['fighters'] = array(get_person_event($row['fighter1ID']), get_person_event($row['fighter2ID']));
            $match['teams']    = null;
        }

        $match['status'] = get_match_status($match_id, $mysqli);
    }
    $mysqli->close();
    return $match;
}

function exchange_from_row($row) {
    $exchange = array();

    $exchange['id']                 = $row[0];
    $exchange['match_id']           = $row[1];
    $exchange['type']               = $row[2];
    $exchange['scoring_id']         = $row[3];
    $exchange['receiving_id']       = $row[4];
    $exchange['score_value']        = $row[5];
    $exchange['score_deduction']    = $row[6];
    $exchange['number']             = $row[7];
    $exchange['time']               = $row[8];

    // attack as own object
    $attack = array();
    $attack['prefix']               = $row[9];
    $attack['target']               = $row[10];
    $attack['type']                 = $row[11];

    $exchange['attack']             = $attack;
    $exchange['timestamp']          = $row[12];

    return $exchange;
}

function match_get_exchanges($match_id, $connector) {
    if ($connector) {
        $mysqli = $connector;
    } else {
        $mysqli = db_connector();
    }

    $sql =
"
SELECT
    eventExchanges.exchangeID,
    eventExchanges.matchID,
    eventExchanges.exchangeType,
    eventExchanges.scoringID,
    eventExchanges.receivingID,
    eventExchanges.scoreValue,
    eventExchanges.scoreDeduction,
    eventExchanges.exchangeNumber,
    eventExchanges.exchangeTime,
    sAPrefix.attackCode,
    sATarget.attackCode,
    sAType.attackCode,
    eventExchanges.timestamp
FROM
    eventExchanges
LEFT JOIN
    systemAttacks AS sAPrefix ON sAPrefix.attackID = eventExchanges.refPrefix
LEFT JOIN
    systemAttacks AS sATarget ON sATarget.attackID = eventExchanges.refTarget
LEFT JOIN
    systemAttacks AS sAType ON sAType.attackID = eventExchanges.refType
WHERE
    matchID = '{$match_id}'
ORDER BY
    timestamp ASC
";

    $exchanges = array();
    if ($result = $mysqli -> query($sql)) {
        while ($row = $result -> fetch_row()) {
            array_push($exchanges, exchange_from_row($row));
        }
    }

    if (!$connector) {
        $mysqli->close();
    }
    return $exchanges;
}
