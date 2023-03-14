<?php
    include('user.php');
    function execute_calls($callData){        
        // get the call data
        $callData = json_decode($callData, true) or returnError("invalid call data", 400);
        
        // check if the call data is valid
        $username = $callData['username'];
        $password = $callData['password'];
        $calls = $callData['calls'];
        
        // execute the calls
        $result = array();
        foreach($calls as $call){
            $path = $call['path'];
            $action = $call['action'];
            $data = $call['data'];

            //TODO check if the db needs to be authenticated
            //TODO if so, make sure the user is authenticated and has permission to make the call
            //TODO make sure the call is valid
            //TODO make the call and store the result in $result            
        }
        //TODO return the result as a success message
    }
?>