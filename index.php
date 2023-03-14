<?php
    session_start();
    include_once('system.php');
    loadCORS();
    if($_POST == null || $_POST == array())
    $_POST = json_decode(file_get_contents("php://input"), true);
    main();
    function main(){
        // check if the user is trying to call an api
        if(isset($_POST['calls'])){
            include_once('call.php');
            execute_calls($_POST['calls']);
            return;
        }

        if(isset($_POST['user'])){
            include_once('user.php');
            execute_user($_POST);
            return;
        }

        displayHeader();

        // check if the user is trying to log out
        if(isset($_POST['logout'])){
            unset($_SESSION['user']);
        }
        $username = $_POST['username'];
        $password = $_POST['password'];
        $loggedInUser = $_SESSION['user'];
        $config = file_get_contents('configuration.json') or throwError('Could not read configuration.json', true, 10);
        $config = json_decode($config, true) or throwError('Could not decode configuration.json', true, 11);
        $users = $config['admins'];

        // check that the user is already logged in
        if($loggedInUser != null){
            if(isset($users[$loggedInUser])){
                displayHub($loggedInUser);
            } else {
                throwError('Invalid username or password', false, 12.0);
                unset($_SESSION['user']);
                displayLogin();
            }
        // check that the user is trying to log in
        }else if($username == null || $password == null ){
            throwError($username . ' ' . $password, false, 12.2);
            displayLogin();
        // check that the user is trying to log in with valid credentials
        } else {
            if(isset($users[$username]) && $users[$username] == $password){
                $_SESSION['user'] = $username;
                displayHub($username);
            } else {
                throwError('Invalid username or password', false, 12.1);
                unset($_SESSION['user']);
                displayLogin();
            }
        }
    }
    function deleteDatabase($uid){
        if(!is_dir(getDatabasePath() . '/' . $uid)){
            throwError('Database does not exist', true, 13.1);
            return;
        }
        rrmdir(getDatabasePath() . '/' . $uid);
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
    function displayHub($username){
        //route to other admin pages here

        if(isset($_GET['manageBase'])){
            displayManager($_GET['manageBase']);
            return;
        }
        if(isset($_GET['deleteBase'])){
            if(isset($_GET['confirm'])){
                deleteDatabase($_GET['deleteBase']);
                displayHub($username);
            } else {
                displayDeleteConfirm($_GET['deleteBase']);
            }
            return;
        }
        if(isset($_GET['settings'])){
            displaySettings();
            return;
        }
        
        displayDatabases($username);
        
    }
    function displayDatabases($username){
        
        echo '
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>CamoDB</h1>
                        <p>Welcome, <b>'.$username.'</b>. Please select a database to manage.</p>
                        <form method="post">
                            <input type="submit" name="logout" value="Logout" class="btn btn-danger">
                        </form>
                    </div>
                    <div class="col-md-12">
                        <a href="createDatabase.php" class="btn btn-success">Create Database</a>
                        <a href="index.php?settings=true" class="btn btn-primary">Admin Settings</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Database Name</th>
                                    <th>Database Description</th>
                                    <th>Database Created</th>
                                    <th>Database Readable</th>
                                    <th>Database Writable</th>
                                    <th>Database Requires Authentication</th>
                                    <th>Database API Key Lifespan</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
        ';

        $path = getDatabasePath();
        $databases = scandir($path);
        foreach($databases as $database){
            if($database == '.' || $database == '..')
                continue;

            $config = getDatabaseConfig($database);
            echo '
                <tr>
                    <td>' . $config['name'] . '</td>
                    <td>' . $config['info'] . '</td>
                    <td>' . $config['created'] . '</td>
                    <td>' . ($config['readable']==1?'true':'false') . '</td>
                    <td>' . ($config['writable']==1?'true':'false') . '</td>
                    <td>' . ($config['requireAuth']==1?'true':'false') . '</td>
                    <td>' . $config['apiKeyLifespan'] . '</td>
                    <td>
                        <a href="index.php?manageBase=' . $config['uid'] . '" class="btn btn-primary">Manage</a>
                        <a href="index.php?deleteBase=' . $config['uid'] . '" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
            ';
        }

        echo '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ';
    }
    function displaySettings(){
        echo '
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>CamoDB</h1>
                        <p>Admin Settings</p>
                    </div>
                    <div class="col-md-12">
                        <a href="index.php" class="btn btn-primary">Back</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        
                    </div>
                </div>
            </div>
        ';
    }
    function displayDeleteConfirm($uid){
        $config = getDatabaseConfig($uid);
        echo '
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <p>Are you sure you want to delete the database <b>' . $config['name'] . '</b>?</p>
                        <b>WARNING: This action cannot be undone!</b>
                    </div>
                    <div class="col-md-12">
                        <a href="index.php?deleteBase=' . $uid . '&confirm=true" class="btn btn-success">Yes</a>
                        <a href="index.php" class="btn btn-danger">No</a>
                    </div>
                </div>
            </div>
        ';
    }
    function displayManager($uid){
        $config = getDatabaseConfig($uid);
        // display the manager as a form and handle its submission
        if(isset($_POST['manageSettings'])){
            $config['name'] = $_POST['name'] == '' ? $config['name'] : $_POST['name'];
            $config['info'] = $_POST['info'] == '' ? $config['info'] : $_POST['info'];
            $config['readable'] = $_POST['readable'] == 1 ? 1 : 0;
            $config['writable'] = $_POST['writable'] == 1 ? 1 : 0;
            $config['requireAuth'] = $_POST['requireAuth'] == 1 ? 1 : 0;
            $config['apiKeyLifespan'] = $_POST['apiKeyLifespan'] == '' ? $config['apiKeyLifespan'] : $_POST['apiKeyLifespan'];
            // save the config
            $json = json_encode($config, JSON_PRETTY_PRINT) or throwError('Failed to encode JSON', true, 15.1);
            file_put_contents(getDatabasePath() . '/' . $uid . '/dbconfig.json', $json) or throwError('Failed to save dbconfig', true, 15.2);

            unset($_POST['manageSettings']);
            unset($_POST['name']);
            unset($_POST['info']);
            unset($_POST['readable']);
            unset($_POST['writable']);
            unset($_POST['requireAuth']);
            unset($_POST['apiKeyLifespan']);
        }

        echo '
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Manage Database</h1>
                    <p>Manage the settings for the database <b>' . $config['name'] . '</b>.</p>
                    <a href="index.php" class="btn btn-primary">Back</a>
                </div>
            </div>
            <div class="row">
                <form class="form-horizontal" action="index.php?manageBase=' . $config['uid'] . '" method="post">
                    <input type="hidden" name="manageSettings" value="true">
                    <div class="form-group">
                        <label for="name" class="col-sm-2 control-label">Database Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Database Name" value="' . $config['name'] . '">
                        </div>
                        <label for="info" class="col-sm-2 control-label">Database Description</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="info" name="info" placeholder="Database Description" value="' . $config['info'] . '">
                        </div>
                        <label for="readable" class="col-sm-2 control-label">Database Readable</label>
                        <div class="col-sm-10">
                            <input type="checkbox" class="form-control" id="readable" name="readable" value="1" ' . ($config['readable']==1?'checked':'') . '>
                        </div>
                        <label for="writable" class="col-sm-2 control-label">Database Writable</label>
                        <div class="col-sm-10">
                            <input type="checkbox" class="form-control" id="writable" name="writable" value="1" ' . ($config['writable']==1?'checked':'') . '>
                        </div>
                        <label for="requireAuth" class="col-sm-2 control-label">Database Requires Authentication</label>
                        <div class="col-sm-10">
                            <input type="checkbox" class="form-control" id="requireAuth" name="requireAuth" value="1" ' . ($config['requireAuth']==1?'checked':'') . '>
                        </div>
                        <label for="apiKeyLifespan" class="col-sm-2 control-label">Database API Key Lifespan</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="apiKeyLifespan" name="apiKeyLifespan" placeholder="Key Lifespan" value="' . $config['apiKeyLifespan'] . '" placeholder="minutes">
                        </div>
                        <div class="col-sm-10">
                            <input type="submit" class="btn btn-primary" name="manageSettings" value="Save Settings">
                        </div>
                    </div>
                </form>
            </div>
        </div
        ';
        
    }
    function displayLogin(){
        echo '
            <div class="login">
                <form action="" method="post" class="login-form">
                    <input type="text" name="username" placeholder="Username" require class="login-username" autofocus />
                    <input type="password" name="password" placeholder="Password" require class="login-password" />
                    <input type="submit" value="Login" class="login-submit" />
                </form>
            </div>
        ';
    }
    function displayHeader(){
        echo '
            <head>
                <title>CamoDB</title>
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
            </head>';
    }
    function loadCORS(){
        $corsUrls = getAdminConfig('corsUrls');
        foreach($corsUrls as $url){
            header("Access-Control-Allow-Origin: $url");
        }
    }
?>