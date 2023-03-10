<?php
    function getDatabasePath() {
        $config = file_get_contents('configuration.json') or throwError('Could not read configuration.json', false, 1);
        $config = json_decode($config, true) or throwError('Could not decode configuration.json', false, 2);
        return $config['databasePath'];
    }
    
    function getDatabaseConfig($uid){
        $path = getDatabasePath();
        $config = file_get_contents($path . '/' . $uid . '/dbconfig.json') or throwError('Could not read dbconfig.json for ' . $uid . '', false, 3);
        $config = json_decode($config, true) or throwError('Could not decode dbconfig.json for ' . $uid . '', false, 4);
        return $config;
    }

    function throwError($message, $fatal = false, $code = -1) {
        if($fatal)
            die( "<script>alert('FATAL ERROR: $message :: Exit Code $code');</script>");
        echo "<script>console.log('WARNING: $message :: Exit Code $code');</script>";   
    }
?>