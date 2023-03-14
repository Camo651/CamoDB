<?php
    include('system.php');
    $uid = uniqid();
    $path = getDatabasePath();
    
    // Check if database already exists
    if(file_exists($path . $uid))
        throwError('Database already exists for ' . $uid . '. Please try again', true, 5.0);

    // Create database directory
    if(!file_exists($path))
        mkdir($path) or throwError('Could not create general database directory', true, 5.1);
    mkdir($path . '/' . $uid) or throwError('Could not create database for ' . $uid . '', true, 5.2);
    mkdir($path . '/' . $uid . '/collections') or throwError('Could not create collections directory for ' . $uid . '', true, 5.3);
    
    // Create database configuration
    $config = array(
        'uid' => $uid,
        'info' => 'This is a new database created by CamoDB. You can change the name and description of this database in the configuration file.',
        'created' => date('Y-m-d H:i:s'),
        'readable' => 1,
        'writable' => 1,
        'requireAuth' => 1,
        'permissions' => array(
            'read' => 0,
            'write' => 1,
            'delete' => 2,
        ),
    );

    // create dbconfig.json
    $config = json_encode($config, JSON_PRETTY_PRINT) or throwError('Could not encode dbconfig.json for ' . $uid . '', true, 8.1);
    file_put_contents($path . '/' . $uid . '/dbconfig.json', $config) or throwError('Could not write dbconfig.json for ' . $uid . '', true, 8.2);

    // Show success message and return to index
    echo 'Successfully created database for ' . $uid . '. You can now manage this database from the <a href="index.php">Admin Hub</a>.';
?>