<?php
    // Load the fetch request data
    header("Access-Control-Allow-Origin: *");
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);

?>