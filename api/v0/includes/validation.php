<?php

/** Checks if a string is a valid ISO-8601 date
 * @param date
 * @return true if valid date, else false
 */
function is_iso8601_date($date) {
    return boolval(preg_match("/[+-]?[0-9]{4,}-[0-9]{2}-[0-9]{2}/", $date));
}
