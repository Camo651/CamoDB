<?php
    function getDatabasePath() {
        return getAdminConfig('databasePath');
    }
    function getDatabaseConfig($uid){
        $path = getDatabasePath();
        $config = file_get_contents($path . '/' . $uid . '/dbconfig.json') or throwError('Could not read dbconfig.json for ' . $uid . '', false, 3);
        $config = json_decode($config, true) or throwError('Could not decode dbconfig.json for ' . $uid . '', false, 4);
        return $config;
    }
    function getDatabaseUsers($uid){
        $path = getDatabasePath();
        $config = file_get_contents($path . '/' . $uid . '/users.json') or throwError('Could not read users.json for ' . $uid . '', false, 5);
        $config = json_decode($config, true) or throwError('Could not decode users.json for ' . $uid . '', false, 6);
        return $config;
    }
    function getDatabaseKeys($uid){
        $path = getDatabasePath();
        $config = file_get_contents($path . '/' . $uid . '/keys.json') or throwError('Could not read keys.json for ' . $uid . '', false, 7);
        $config = json_decode($config, true) or throwError('Could not decode keys.json for ' . $uid . '', false, 8);
        return $config;
    }
    function throwError($message, $fatal = false, $code = -1) {
        if($fatal)
            die( "<script>alert('FATAL ERROR: $message :: Exit Code $code');</script>");
        echo "<script>console.log('WARNING: $message :: Exit Code $code');</script>";   
    }
    function returnError($message = "unknown error", $code = 500){
        $returnData = array(
            'status' => $code,
            'message' => $message,
            'data' => error_get_last()
        );
        echo json_encode($returnData);
        exit();
    }
    function returnSuccess($data = array()){
        $returnData = array(
            'status' => 200,
            'message' => "success",
            'data' => $data
        );
        echo json_encode($returnData);
        exit();
    }
    function getAdminConfig($prop){
        $config = json_decode(file_get_contents("configuration.json"), true) or returnError("invalid admin config", 500);
        return $config[$prop];
    }
    function setAdminConfig($prop, $value){
        $config = json_decode(file_get_contents("configuration.json"), true) or returnError("invalid admin config", 500);
        $config[$prop] = $value;
        file_put_contents("configuration.json", json_encode($config)) or returnError("could not write admin config", 500);
    }
    function rrmdir($dir) { 
        if (is_dir($dir)) { 
          $objects = scandir($dir);
          foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
              if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                rrmdir($dir. DIRECTORY_SEPARATOR .$object);
              else
                unlink($dir. DIRECTORY_SEPARATOR .$object); 
            } 
          }
          rmdir($dir); 
        }
    }
    function file_force_contents($dir, $contents){
        $parts = explode('/', $dir) or returnError("could not force directory explode", 500);
        $file = array_pop($parts) or returnError("could not force directory pop", 500);
        $dir = '';
        foreach($parts as $part){
            if($dir == '')
                $dir = $part;
            else
                $dir .= "/$part";
            if(!is_dir($dir)){
                mkdir($dir) or returnError("could not force directory ".$dir, 500);
            }
        }
        return file_put_contents("$dir/$file", $contents) or returnError("could not force file", 500);
    }
?>