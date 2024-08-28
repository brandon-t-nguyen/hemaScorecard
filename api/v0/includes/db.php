<?

define("DEPLOYMENT_UNKNOWN",0);
include $_SERVER['DOCUMENT_ROOT'].'/includes/database.php';

function db_connector() {
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, PRIMARY_DATABASE);
    return $mysqli;
}
