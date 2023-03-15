<?php
    function execute_user($post){
        $callData = $post['user'];
        $username = $post['username'];
        $password = $post['password'];
        $action = $callData['action'];
        $data = $callData['data'];

        if($action == 'create'){
            createUser($username, $password, $data['perms']);
            return;
        }

        if(!authenticateUser($username, $password)){
            returnError("invalid username or password", 401);
            return;
        }

        switch($action){
            case 'delete':
                deleteUser(getUUID($username));
                break;
            case 'chngpw':
                changePassword(getUUID($username), $password);
                break;
            case 'chngun':
                changeUsername(getUUID($username), $username);
                break;
            default:
                returnError("invalid action", 400);
        }
    }
    function getUserPermissions($uuid){
        $path = "users/users/$uuid.json";
        if(!file_exists($path))
            return -1;
        $user = json_decode(file_get_contents($path), true) or returnError("invalid user", 500);
        return $user['perms'];
    }
    function authenticateUser($username, $password){
        $uuid = getUUID($username);
        $path = "users/users/$uuid.json";
        if(!file_exists($path))
            return false;
        $user = json_decode(file_get_contents($path), true);
        return $user['password'] == $password;
    }
    function createUser($username, $password, $perms){
        if(getAdminConfig('allowDynamicAuth') == false)
            returnError("dynamic authentication is disabled", 403);
        $uuidMap = getUuidMap();
        $uuid = uniqid();
        while(isset($uuidMap[$uuid]))
            $uuid = uniqid();
        foreach($uuidMap as $user){
            if($user == $username){
                returnError("username already exists", 400);
                return;
            }
        }
        $uuidMap[$uuid] = $username;
        file_put_contents("users/uuidMap.json", json_encode($uuidMap)) or returnError("unable to write uuid map", 500);
        $path = "users/users/$uuid.json";
        $user = array(
            "username" => $username,
            "password" => $password,
            "perms" => $perms
        );
        file_put_contents($path, json_encode($user)) or returnError("unable to write user", 500);
        returnSuccess($user);
    }
    function deleteUser($uuid){
        $uuidMap = getUuidMap();
        unset($uuidMap[$uuid]);
        file_put_contents("users/uuidMap.json", json_encode($uuidMap)) or returnError("unable to write uuid map", 500);
        $path = "users/users/$uuid.json";
        unlink($path) or returnError("unable to delete user", 500);
        returnSuccess();
    }
    function changePassword($uuid, $password){
        $path = "users/users/$uuid.json";
        $user = json_decode(file_get_contents($path), true) or returnError("invalid user", 500);
        $user['password'] = $password;
        file_put_contents($path, json_encode($user)) or returnError("unable to write user", 500);
        returnSuccess($user);
    }
    function changeUsername($uuid, $username){
        $uuidMap = getUuidMap();
        $uuidMap[$uuid] = $username;
        file_put_contents("users/uuidMap.json", json_encode($uuidMap)) or returnError("unable to write uuid map", 500);
        $path = "users/users/$uuid.json";
        $user = json_decode(file_get_contents($path), true) or returnError("invalid user", 500);
        $user['username'] = $username;
        file_put_contents($path, json_encode($user)) or returnError("unable to write user", 500);
        returnSuccess($user);
    }
    function getUUID($username): mixed{
        $uuidMap = getUuidMap();
        foreach($uuidMap as $uuid => $user){
            if($user == $username){
                return $uuid;
            }
        }
        return null;
    }
    function getUsername($uuid): mixed{
        $uuidMap = getUuidMap();
        if(!isset($uuidMap[$uuid]))
            return null;
        return $uuidMap[$uuid];
    }
    function getUuidMap(): array{
        $uuidMap = file_get_contents("users/uuidMap.json") or returnError("unable to read uuid map", 500);
        $uuidMap = json_decode($uuidMap, true) or returnError("invalid uuid map", 500);
        return $uuidMap;
    }
?>