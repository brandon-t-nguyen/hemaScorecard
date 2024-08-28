<?php
// API entrypoint

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$resource = array_shift($request);

require_once "$resource.php";

?>
