<?php
    include('system.php');
    $uid = uniqid();
    $path = getDatabasePath();
    
    // Check if database already exists
    if(file_exists($path . $uid))
        throwError('Database already exists for ' . $uid . '. Please try again', true, 5);

    // Create database directory
    if(!file_exists($path))
        mkdir($path) or throwError('Could not create general database directory', true, 5.1);
    mkdir($path . '/' . $uid) or throwError('Could not create database for ' . $uid . '', true, 6);
    mkdir($path . '/' . $uid . '/collections') or throwError('Could not create collections directory for ' . $uid . '', true, 7);
    
    // Create database configuration
    $config = array(
        'uid' => $uid,
        'name' => 'My New Database',
        'info' => 'This is a new database created by CamoDB. You can change the name and description of this database in the configuration file.',
        'created' => date('Y-m-d H:i:s'),
        'readable' => 1,
        'writable' => 1,
        'requireAuth' => 1,
        'apiKeyLifespan' => 3600,
    );

    $config = json_encode($config, JSON_PRETTY_PRINT) or throwError('Could not encode dbconfig.json for ' . $uid . '', true, 8);
    file_put_contents($path . '/' . $uid . '/dbconfig.json', $config) or throwError('Could not write dbconfig.json for ' . $uid . '', true, 9);

    // Show success message and return to index
    echo 'Successfully created database for ' . $uid . '. You can now manage this database from the <a href="index.php">Admin Hub</a>.';
?>