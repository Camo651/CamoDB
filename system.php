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
            'data' => array()
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
?>