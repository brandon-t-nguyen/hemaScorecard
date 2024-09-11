<?php

require_once "includes/response.php";
require_once "includes/validation.php";

require_once "model/team.php";

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$req_method = $_SERVER['REQUEST_METHOD'];
if (count($request) < 2) {
    // teams
    handle_teams();
} else {
    // teams/{team.id}
    $team_id = $request[1];
    handle_team_id($team_id, array_slice($request, 2));
}

function handle_teams() {
    response_todo();
}

function handle_team_id($team_id, $params) {
    // /teams/team.id
    if (count($params) == 0) {
        $team = get_team($team_id);
        if (is_null($team)) {
            response_error("no team of such id", 404);
        } else {
            response_success($team);
        }
    } else {
        $method = $params[0];
        response_todo();
        /*
        if ($method == "exchanges") {
            response_success(team_get_exchanges($team_id));
        }
        */
    }
}
