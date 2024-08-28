<?php

require_once __DIR__."/../includes/db.php";

require_once __DIR__."/person.php";

function match_from_row($row) {
    $event = array();
    $event['id']                = $row[0];
    $event['fighter1']          = get_person_event($row[1]);
    $event['fighter2']          = get_person_event($row[2]);
    $event['winner_id']         = $row[3];
    $event['fighter1_score']    = $row[4];
    $event['fighter2_score']    = $row[5];
    $event['match_complete']    = (bool) $row[6];
    $event['match_time']        = $row[7];
    return $event;
}

function get_match($match_id) {
    $mysqli = db_connector();

    $sql = "
            SELECT
                eventMatches.matchID,
                eventMatches.fighter1ID,
                eventMatches.fighter2ID,
                eventMatches.winnerID,
                eventMatches.fighter1Score,
                eventMatches.fighter2Score,
                eventMatches.matchComplete,
                eventMatches.matchTime
            FROM eventMatches
            WHERE eventMatches.matchID = '{$match_id}'
           ";

    if ($result = $mysqli -> query($sql)) {
        $out = match_from_row($result -> fetch_row());
    } else {
        $out = null;
    }

    $mysqli -> close();
    return $out;
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

function match_get_exchanges($match_id) {
    $mysqli = db_connector();

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

    $mysqli -> close();
    return $exchanges;
}
