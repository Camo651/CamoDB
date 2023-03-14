<?php
    function execute_calls($callData){
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
            $p_db = explode('/', $path)[0] ?? null;
            $p_col = explode('/', $path)[1] ?? null;
            $p_ent = explode('/', $path)[2] ?? null;
            if($p_db == null)
                returnError('Requested database does not exist', 404);        
            $dbConfig = getDatabaseConfig($p_db);
            if($dbConfig == null)
                returnError('Cannot find the dbconfig', 404);
            $requireAuth = $dbConfig['requireAuth'];
            $readable = $dbConfig['readable'];
            $writable = $dbConfig['writable'];
            $permissions = $dbConfig['permissions'];

            if($requireAuth == 1){
                if($username == null || $password == null)
                    returnError('This database requires authentication', 401);
                if(!authenticateUser($username, $password))
                    returnError('Invalid username or password', 401);
                if($permissions == null)
                    returnError('This database has no permissions', 500);
                $userPerms = getUserPermissions(getUUID($username));
                switch($action){
                    case 'get':
                    case 'query':
                        if($readable == 0)
                            returnError('This database is not readable', 403);
                        if($permissions['read'] > $userPerms)
                            returnError('You do not have permission to read from this database', 403);
                        break;
                    case 'set':
                    case 'add':
                        if($writable == 0)
                            returnError('This database is not writable', 403);
                        if($permissions['write'] > $userPerms)
                            returnError('You do not have permission to write to this database', 403);
                        break;
                    case 'delete':
                        if($permissions['delete'] > $userPerms)
                            returnError('You do not have permission to delete from this database', 403);
                        break;
                }
            }

            
            

            //TODO make sure the call is valid
            //TODO make the call and store the result in $result            
        }
        //TODO return the result as a success message
    }
?>