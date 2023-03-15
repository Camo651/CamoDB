<?php
    function execute_calls($callData){
        // check if the call data is valid
        $username = $callData['username'];
        $password = $callData['password'];
        $calls = $callData['calls'];
        
        // execute the calls
        $result = array();
        foreach($calls as $call){
            $result[] = execute_singleCall($call, $username, $password);
        }
        returnSuccess($result);
    }
    function checkPermissions($dbConfig, $action, $username, $password){
        $requireAuth = $dbConfig['requireAuth'];
        $readable = $dbConfig['readable'];
        $writable = $dbConfig['writable'];
        $permissions = $dbConfig['permissions'];
        if($requireAuth != 1)
            return;
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
    function execute_singleCall($call, $username, $password){
        $path = $call['path'];
        $action = $call['action'];
        $data = $call['data'];
        $p_db = explode('/', $path)[0] ?? null;
        $p_col = explode('/', $path)[1] ?? null;
        $p_ent = explode('/', $path)[2] ?? null;
        $p_key = explode('/', $path)[3] ?? null;

        //check that the database exists and that the user has permission to access it
        if($p_db == null)
            returnError('Requested database does not exist', 404);
        $dbConfig = getDatabaseConfig($p_db);
        if($dbConfig == null)
            returnError('Cannot find the dbconfig', 404);
        checkPermissions($dbConfig, $action, $username, $password);

        //based on the action specified, execute the call
        switch($action){
            case 'get':
                if($p_col == null)
                    return getDirectories($p_db);
                if($p_ent == null)
                    return getDirectories($p_db, $p_col);
                if($p_key == null)
                    return getDirectories($p_db, $p_col, $p_ent);
                return getKvp($p_db, $p_col, $p_ent, $p_key);
            case 'set':
                return setKvp($p_db, $p_col, $p_ent, $data);
            case 'add':
                return addKvp($p_db, $p_col, $p_ent, $data);
            case 'delete':
                if($p_col == null)
                    returnError('Databases can only be deleted by the admin', 403);
                if($p_ent == null)
                    rrmdir(getDatabasePath() . '/' . $p_db . '/collections/' . $p_col);
                if($p_key == null)
                    rrmdir(getDatabasePath() . '/' . $p_db . '/collections/' . $p_col . '/' . $p_ent . '.json');
                else
                    return deleteKvp($p_db, $p_col, $p_ent, $p_key);
                return null;
            case 'query':
                return query($p_db, $p_col, $data);
        }
        returnError('Invalid action', 400);
    }
    function getDirectories($dbid, $collection = null, $entry = null){
        $path = getDatabasePath() . '/' . $dbid . '/collections';
        if($collection != null)
            $path .= '/'.$collection;
        if($entry != null){
            $path .= '/' . $entry . '.json';
            if(!file_exists($path))
                returnError('Entry does not exist', 404);
                //if the path goes to an entry, return a list of all the keys in the json
            $json = file_get_contents($path) or returnError('Could not read entry', 500);
            $json = json_decode($json, true) or returnError('Could not decode entry', 500);
            $result = array();
            foreach($json as $key => $value){
                $result[] = $key;
            }
            return $result;
        }
        if(!file_exists($path))
            returnError('Path does not exist', 404);
        if(!is_dir($path))
            returnError('Path is not a directory', 404);
        $result = array();
        $scan = scandir($path);
        foreach($scan as $file){
            if($file == '.' || $file == '..')
                continue;
            if(is_file($path . '/' . $file))
                $file = substr($file, 0, -5); //remove .json from the end of the file name
            $result[] = $file;
        }
        return $result;
    }
    function getKvp($dbid, $collection, $entry, $key){
        $path = getDatabasePath() . '/' . $dbid . '/collections/' . $collection . '/' . $entry . '.json';
        if(!file_exists($path))
            returnError('Entry does not exist', 404);
        $json = file_get_contents($path) or returnError('Could not read entry', 500);
        $json = json_decode($json, true) or returnError('Could not decode entry', 500);
        if($key == '*')
            return $json;
        if(!array_key_exists($key, $json))
            returnError('Key does not exist', 404);
        return $json[$key];
    }
    function setKvp($dbid, $collection, $entry, $data){
        $path = (getDatabasePath() . '/' . $dbid . '/collections/' . $collection . '/' . $entry . '.json');
        $path = stripcslashes($path);
        $json = array();
        if(file_exists($path)){
            $json = file_get_contents($path) or returnError('Could not read entry', 500);
            $json = json_decode($json, true) or returnError('Could not decode entry', 500);
        }
        foreach($data as $key => $value){
            $json[$key] = $value;
        }
        $json = json_encode($json, JSON_PRETTY_PRINT) or returnError('Could not encode entry', 500);
        file_force_contents($path, $json) or returnError('Could not write entry '.$path, 500);
        return $json;
    }
    function addKvp($dbid, $collection, $entry, $data){
        $path = getDatabasePath() . '/' . $dbid . '/collections/' . $collection . '/' . $entry . '.json';
        $json = array();
        if(file_exists($path)){
            $json = file_get_contents($path) or returnError('Could not read entry', 500);
            $json = json_decode($json, true) or returnError('Could not decode entry', 500);
        }
        foreach($data as $key => $value){
            if(array_key_exists($key, $json)){
                foreach($value as $subkey => $subvalue){
                    $json[$key][$subkey] = $subvalue;
                }
            }else{
                $json[$key] = $value;
            }
        }
        $json = json_encode($json, JSON_PRETTY_PRINT) or returnError('Could not encode entry', 500);
        file_force_contents($path, $json) or returnError('Could not write entry', 500);
        return $json;
    }
    function deleteKvp($dbid, $collection, $entry, $key){
        $path = getDatabasePath() . '/' . $dbid . '/collections/' . $collection . '/' . $entry . '.json';
        if(!file_exists($path))
            returnError('Entry does not exist', 404);
        $json = file_get_contents($path) or returnError('Could not read entry', 500);
        $json = json_decode($json, true) or returnError('Could not decode entry', 500);
        if(!key_exists($key, $json))
            returnError('Key does not exist', 404);
        unset($json[$key]);
        $json = json_encode($json, JSON_PRETTY_PRINT) or returnError('Could not encode entry', 500);
        file_force_contents($path, $json) or returnError('Could not write entry', 500);
        return $json;
    }
    function query($token, $caseSensitive, $dbid, $collection = null, $entry = null){
        return array();
        //TODO if the token is empty, return an error
        //TODO if the collection is null, loop over all collections in the database
        //TODO if the entry is null, loop over all entries in the collection
        //TODO if the entry is not null, recursively loop over all keys in the entry and find a value that contains the token
        //TODO add the path to that key and the value to the result as a key value pair
        //TODO return the result

        // $path = getDatabasePath() . '/' . $dbid;
        // if($collection != null)
        //     $path .= '/collections/' . $collection;
        // if($entry != null)
        //     $path .= '/' . $entry . '.json';
        // return recursivelySearchPath($path, $token, $caseSensitive);
    }
    // function recursivelySearchPath($path, $token, $caseSensitive){
    //     if(!file_exists($path))
    //         return;
    //     if(is_dir($path)){
    //         foreach(scandir($path) as $file){
    //             if($file == '.' || $file == '..')
    //                 continue;
    //             recursivelySearchPath($path . '/' . $file, $token, $caseSensitive);
    //         }
    //     }else{
    //         $json = file_get_contents($path) or returnError('Could not read entry', 500);
    //         $json = json_decode($json, true) or returnError('Could not decode entry', 500);
    //         recursivelySearchJsonFile($json, $token, $caseSensitive);
    //     }
    // }
    // function recursivelySearchJsonFile($json, $token, $caseSensitive){
    //     $result = array();
    //     foreach($json as $key => $value){
    //         if(is_array($value)){
    //             $r = recursivelySearchJsonFile($value, $token, $caseSensitive);
    //             foreach($r as $k => $v){
    //                 $result[$key . '.' . $k] = $v;
    //             }
    //         }else{
    //             if(strpos($value, $token) !== false){
    //                 return array($key => $value);
    //             }
    //         }
    //     }
    //     return $result;
    // }
?>