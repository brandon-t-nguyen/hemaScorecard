<?php

require_once "includes/response.php";
require_once "includes/validation.php";

require_once "model/event.php";
require_once "model/location.php";

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$req_method = $_SERVER['REQUEST_METHOD'];
if (count($request) < 2) {
    // locations
    handle_locations();
} else {
    // location/{location.id}
    $location_id = $request[1];
    handle_location_id($location_id, array_slice($request, 2));
}

function handle_locations() {
    $locations = get_locations($event_id);
    response_success($locations);
}

function handle_location_id($location_id, $params) {
    // /locations/location.id
    if (!is_uint($location_id)) {
        response_error("not a valid location id", 400);
        return;
    }

    if (count($params) == 0) {
        $location = get_locations(null, $location_id);
        if (is_null($location)) {
            response_error("no location of such location id", 404);
        } else {
            response_success($location);
        }
    } else {
        $attr = $params[0];
        if ($attr == "active") {
            $match = location_get_active_match($location_id);
            response_success($match);
        }
    }
}
