<?

function response_success($data, $next=null, $code=200) {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    http_response_code($code);
    $response = array("success" => true);
    $response['data'] = $data;
    if (!is_null($next)) {
        $response['next'] = $next;
    }
    echo json_encode($response);
}

function response_error($error, $code=200) {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    http_response_code($code);
    $response = array("success" => false);
    $response['error'] = $error;
    echo json_encode($response);
}

function response_todo() {
    response_error("not implemented yet");
}
