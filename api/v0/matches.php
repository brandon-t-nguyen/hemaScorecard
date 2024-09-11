<?php

require_once "includes/response.php";
require_once "includes/validation.php";

require_once "model/match.php";

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$req_method = $_SERVER['REQUEST_METHOD'];
if (count($request) < 2) {
    // matches
    handle_matches();
} else {
    // matches/{match.id}
    $match_id = $request[1];
    handle_match_id($match_id, array_slice($request, 2));
}

function handle_matches() {
    response_todo();
}

function handle_match_id($match_id, $params) {
    // /matches/match.id
    if (!is_uint($match_id)) {
        response_error("not a valid match id", 400);
        return;
    }
    if (count($params) == 0) {
        $match = get_match($match_id);
        if (is_null($match)) {
            response_error("no match of such id", 404);
        } else {
            response_success($match);
        }
    } else {
        $method = $params[0];
        if ($method == "status") {
            response_success(match_get_status($match_id));
        } else if ($method == "exchanges") {
            if (isset($_GET['after'])) {
                $after = $_GET['after'];
                if (!is_uint($after)) {
                    response_error("query string param 'after' is not an unsigned integer", 400);
                    return;
                }
            } else {
                $after = 0;
            }
            response_success(match_get_exchanges($match_id, $after));
        }
    }
}
