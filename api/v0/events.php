<?php

require_once "includes/response.php";
require_once "includes/validation.php";

require_once "model/event.php";
require_once "model/location.php";

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$req_method = $_SERVER['REQUEST_METHOD'];
if (count($request) < 2) {
    // events
    handle_events();
} else {
    // events/{event.id}
    $event_id = $request[1];
    handle_event_id($event_id, array_slice($request, 2));
}

function handle_events() {
    $after = null;
    $before = null;
    $limit = 50;
    $offset = 0;

    if (isset($_GET['after'])) {
        $after = $_GET['after'];
        if (!is_iso8601_date($after)) {
            response_error("query string param 'after' is not a valid ISO-8601 date", 400);
            return;
        }
    }

    if (isset($_GET['before'])) {
        $before = $_GET['before'];
        if (!is_iso8601_date($before)) {
            response_error("query string param 'before' is not a valid ISO-8601 date", 400);
            return;
        }
    }

    if (isset($_GET['limit'])) {
        $limit = $_GET['limit'];
        if (!is_numeric($limit)) {
            response_error("query string param 'limit' is not a valid integer", 400);
            return;
        }
    }

    if (isset($_GET['offset'])) {
        $offset = $_GET['offset'];
        if (!is_numeric($offset)) {
            response_error("query string param 'offset' is not a valid integer", 400);
            return;
        }
    }

    $events = get_events($after, $before, null, $limit, $offset);
    if (count($events) == $limit) {
        // if we hit the limit, provide a pagination link
        $new_offset = $offset + $limit;
        $link = "/api/v0/events?offset={$new_offset}";
        if (isset($_GET['limit'])) {
            $link .= "&limit={$limit}";
        }
        response_success($events, $link);
    } else {
        response_success($events);
    }
}

function handle_event_id($event_id, $params) {
    // /events/event.id
    if (count($params) == 0) {
        $event = get_events(null, null, $event_id);
        if (is_null($event)) {
            response_error("no event of such id", 404);
        } else {
            response_success($event);
        }
    } else {
        $method = $params[0];
        if ($method == "locations") {
            $locations = get_locations($event_id);
            response_success($locations);
        }
    }
}
