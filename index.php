<?php
    session_start();
    include('system.php');
    include('user.php');
    include('call.php');
    loadCORS();
    if($_POST == null || $_POST == array())
    $_POST = json_decode(file_get_contents("php://input"), true);
    main();
    function main(){
        // check if the user is trying to call an api
        if(isset($_POST['calls'])){
            execute_calls($_POST);
            return;
        }

        if(isset($_POST['user'])){
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
            return;
        }
        rrmdir(getDatabasePath() . '/' . $uid);
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
        if(isset($_GET['users'])){
            displayUsers();
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
                            <a href="createDatabase.php" class="btn btn-success">Create Database</a>
                            <a href="index.php?settings=true" class="btn btn-primary">Admin Settings</a>
                            <a href="index.php?users=true" class="btn btn-primary">Users</a>
                        </form>
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
                    <td>' . $config['uid'] . '</td>
                    <td>' . $config['info'] . '</td>
                    <td>' . $config['created'] . '</td>
                    <td>' . ($config['readable']==1?'true':'false') . '</td>
                    <td>' . ($config['writable']==1?'true':'false') . '</td>
                    <td>' . ($config['requireAuth']==1?'true':'false') . '</td>
                    <td>' . $config['apiKeyLifespan'] . '</td>
                    <td>
                        <a href="index.php?manageBase=' . $config['uid'] . '" class="btn btn-primary">Manage</a>
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
        if(isset($_POST['saveSettings'])){
            setAdminConfig('requireAuth', (isset($_POST['requireAuth']) && $_POST['requireAuth']=='1')?'1':'0');
            setAdminConfig('allowDynamicAuth', (isset($_POST['allowDynamicAuth']) && $_POST['allowDynamicAuth']=='1')?'1':'0');
            unset($_POST['allowDynamicAuth']);
            unset($_POST['requireAuth']);
            unset($_POST['saveSettings']);
        }
        $reqAuth = getAdminConfig('requireAuth');
        $dynAuth = getAdminConfig('allowDynamicAuth');
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
                    <form method="post" action="index.php?settings=true">
                        <div class="col-md-12">
                            <label for="requireAuth">Require User Authentication</label>
                            <input type="checkbox" name="requireAuth" id="requireAuth" value="1" class="form-check-input" ' . ($reqAuth==1?'checked':'') . '>
                        </div>
                        <div class="col-md-12">
                            <label for="allowDynamicAuth">Allow New User Accounts</label>
                            <input type="checkbox" name="allowDynamicAuth" id="allowDynamicAuth" value="1" class="form-check-input" ' . ($dynAuth==1?'checked':'') . '>
                        </div>
                        <div class="col-md-12">
                            <input type="submit" name="saveSettings" value="Save Settings" class="btn btn-success">
                        </div>
                        <div class="col-md-12">
                            <p><b>WARNING:</b> Changing these settings will affect all databases!</p>
                            <br>
                            <p>Change admin users and CORS headers in the <code>configuration.json</code> file.</p>
                            <p>See more information about the configuration file on the project <a href="https://github.com/Camo651/CamoDB" target="_blank">Github Page</a>.</p>
                        </div>
                    </form>
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
                        <p>Are you sure you want to delete the database <b>' . $config['uid'] . '</b>?</p>
                        <b>WARNING: This action cannot be undone!</b>
                    </div>
                    <div class="col-md-12">
                        <a href="index.php?deleteBase=' . $uid . '&confirm=true" class="btn btn-success">Yes</a>
                        <a href="index.php?manageBase=' . $uid . '" class="btn btn-danger">No</a>
                    </div>
                </div>
            </div>
        ';
    }
    function displayManager($uid){
        $config = getDatabaseConfig($uid);
        // display the manager as a form and handle its submission
        if(isset($_POST['manageSettings'])){
            $config['uid'] = $_POST['name'] == '' ? $config['uid'] : $_POST['name'];
            $config['info'] = $_POST['info'] == '' ? $config['info'] : $_POST['info'];
            $config['readable'] = $_POST['readable'] == 1 ? 1 : 0;
            $config['writable'] = $_POST['writable'] == 1 ? 1 : 0;
            $config['requireAuth'] = $_POST['requireAuth'] == 1 ? 1 : 0;
            $config['permissions']['read'] = $_POST['readable'] == '' ? $config['permissions']['read'] : $_POST['readable'];
            $config['permissions']['write'] = $_POST['writable'] == '' ? $config['permissions']['write'] : $_POST['writable'];
            $config['permissions']['delete'] = $_POST['delete'] == '' ? $config['permissions']['delete'] : $_POST['delete'];

            // save the config
            $json = json_encode($config, JSON_PRETTY_PRINT) or throwError('Failed to encode JSON', true, 15.1);
            file_put_contents(getDatabasePath() . '/' . $uid . '/dbconfig.json', $json) or throwError('Failed to save dbconfig', true, 15.2);
            if($config['uid'] != $uid){
                rename(getDatabasePath() . '/' . $uid, getDatabasePath() . '/' . $config['uid']) or throwError('Failed to rename database', true, 15.3);
            }
            unset($_POST['manageSettings']);
            unset($_POST['name']);
            unset($_POST['info']);
            unset($_POST['readable']);
            unset($_POST['writable']);
            unset($_POST['requireAuth']);
            unset($_POST['read']);
            unset($_POST['write']);
            unset($_POST['delete']);
        }

        echo '
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Manage Database</h1>
                    <p>Manage the settings for the database <b>' . $config['uid'] . '</b>.</p>
                    <a href="index.php" class="btn btn-primary">Back</a>
                </div>
            </div>
            <div class="row">
                <form class="form-horizontal" action="index.php?manageBase=' . $config['uid'] . '" method="post">
                    <input type="hidden" name="manageSettings" value="true">
                    <div class="form-group">
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Datebase Name</span>
                              </div>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Database Name" value="' . $config['uid'] . '">
                        </div>
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Database Info</span>
                              </div>
                            <input type="text" class="form-control" id="info" name="info" placeholder="Database Description" value="' . $config['info'] . '">
                        </div>
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Readable?</span>
                              </div>
                            <input type="checkbox" class="form-check-input" id="readable" name="readable" value="1" ' . ($config['readable']==1?'checked':'') . '>
                        </div>
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Writeable?</span>
                              </div>
                            <input type="checkbox" class="form-check-input" id="writable" name="writable" value="1" ' . ($config['writable']==1?'checked':'') . '>
                        </div>
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Require Auth?</span>
                              </div>
                            <input type="checkbox" class="form-check-input" id="requireAuth" name="requireAuth" value="1" ' . ($config['requireAuth']==1?'checked':'') . '>
                        </div>
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Min Read Level</span>
                              </div>
                            <input type="text" class="form-control" id="readable" name="readable" placeholder="1" value="' . $config['permissions']['read'] . '">
                        </div>
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Min Write Level</span>
                              </div>
                            <input type="text" class="form-control" id="writable" name="writable" placeholder="1" value="' . $config['permissions']['write'] . '">
                        </div>
                        <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Min Delete Level</span>
                              </div>
                            <input type="text" class="form-control" id="delete" name="delete" placeholder="1" value="' . $config['permissions']['delete'] . '">
                        </div>
                        <div class="input-group mb-3">
                            <input type="submit" class="btn btn-primary" name="manageSettings" value="Save Settings">
                            <a href="index.php?deleteBase=' . $config['uid'] . '" class="btn btn-danger">Delete</a>
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
    function displayUsers(){
        $users = getUuidMap();
        echo '
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>Manage Users</h1>
                        <p>Manage the users of the database.</p>
                        <a href="index.php" class="btn btn-primary">Back</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>UUID</th>
                                    <th>Username</th>
                                    <th>Permissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
        ';
        foreach($users as $uuid => $user){
            echo '
                <tr>
                    <td>' . $uuid . '</td>
                    <td>' . $user . '</td>
                    <td>' . getUserPermissions($uuid) . '</td>
                    <td>
                        <a href="" class="btn btn-secondary">Unavailable</a>
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
    function loadCORS(){
        $corsUrls = getAdminConfig('corsUrls');
        foreach($corsUrls as $url){
            header("Access-Control-Allow-Origin: $url");
        }
    }
?>