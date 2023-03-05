<?php
    // Allow access from any origin
    header("Access-Control-Allow-Origin: *");
    // Load the POST data from the fetch request
    $postdata = file_get_contents("php://input");
    // Decode the JSON data
    $request = json_decode($postdata);
    
    $token = $request['token'];
    $databaseId = $request['dbid'];
    
?>