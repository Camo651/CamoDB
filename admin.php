<?php session_start(); ?>
<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body>
        <h1 class="text-center">Admin Panel</h1>
        <?php
            main();
            
            // Main function for the admin page to handle login and show the admin page
            function main(){
                $logout = isset($_POST["logout"]);
                if($logout){
                    session_destroy();
                    unset ($_SESSION["user"]);
                    unset($_POST["logout"]);
                    showLoginForm();
                    return;
                }
                $ADMIN_CONFIG_FILE = "adminConfig.json";
                $isLoggedIn = isset($_SESSION["user"]);
                echo "<script>console.log('isLoggedIn: $isLoggedIn')</script>";
                $adminConfig = file_get_contents($ADMIN_CONFIG_FILE) or throwError("Could not read admin config file", true);
                $adminConfig = json_decode($adminConfig, true) or throwError("Could not decode admin config file", true);
                if(!$isLoggedIn && $adminConfig["requireAccountForAdminPage"]){
                    $username = $_POST["username"];
                    $password = $_POST["password"];
                    unset($_POST["username"]);
                    unset($_POST["password"]);
                    if(checkUsernameAndPassword($username, $password, $adminConfig)){
                        $_SESSION["user"] = $username;
                        showAdminPage();
                    }else{
                        showLoginForm();
                    }
                } else {
                    if(!$adminConfig["requireAccountForAdminPage"])
                        $_SESSION["user"] = "anonymous";
                    showAdminPage();
                }
            }
            // Show the admin page
            function showAdminPage(){
                setDatabaseSettings();
                $allDatabases = getDatabasesFromHub();
                $database = $_GET["database"] ?? null;
                $collection = $_GET["collection"] ?? null;
                $entry = $_GET["entry"] ?? null;
                $field = $_POST["field"];
                $setValue = $_POST["setValue"];
                echo "<h4 class='text-center'>Welcome, " . $_SESSION["user"] . "</h4>";
                echo "<form class='form-horizontal' action='admin.php' method='post'>";
                    echo "<div class='form-group'>";
                        echo "<div class='col-sm-offset-2 col-sm-10'>";
                            echo "<input type='hidden' name='logout' value='true'>";
                            echo "<button type='submit' class='btn btn-default'>Logout</button>";
                        echo "</div>";
                    echo "</div>";
                echo "</form>";
                echo "<div class='container-fluid'>";
                    echo "<div class='row'>";
                        echo "<div class='col-sm-2'>";
                            echo "<h5 class='text-center'>Databases</h5>";
                            // TODO add a button to add a new database here
                            for($i = 0; $i < count($allDatabases); $i++){
                                echo "<a href='admin.php?database=".$allDatabases[$i]."' class='btn btn-primary btn-block".($allDatabases[$i]==$database ? " active" : "")."' role='button'>".$allDatabases[$i]."</a>";
                            }
                        echo "</div>";
                        if ($database != null && count($database) > 0){
                            echo "<div class='col-sm-2'>";
                                echo "<h5 class='text-center'>Collections</h5>";
                                $allCollections = getCollectionsFromDatabase($database);
                                for($i = 0; $i < count($allCollections); $i++){
                                    echo "<a href='admin.php?database=$database&collection=".$allCollections[$i]."' class='btn btn-primary btn-block".($allCollections[$i]==$collection ? " active" : "")."' role='button'>".$allCollections[$i]."</a>";
                                }
                            echo "</div>";
                        }
                        if($collection != null && count($collection) > 0){
                            echo "<div class='col-sm-2'>";
                                echo "<h5 class='text-center'>Entries</h5>";
                                $allEntries = getEntriesFromCollection($database, $collection);
                                for($i = 0; $i < count($allEntries); $i++){
                                    echo "<a href='admin.php?database=$database&collection=$collection&entry=".$allEntries[$i]."' class='btn btn-primary btn-block".($allEntries[$i]==$entry ? " active" : "")."' role='button'>".$allEntries[$i]."</a>";
                                }
                            echo "</div>";
                        }
                        if($database != null){
                            $databaseConfig = file_get_contents("hub/$database/config.json") or throwError("Could not read database config file", true);
                            $databaseConfig = json_decode($databaseConfig, true) or throwError("Could not decode database config file", true);
                            echo "<div class='col-sm-6'>";
                                echo "<h5 class='text-center'>Database Settings</h5>";
                                echo "<table class='table table-striped'>";
                                    echo "<thead>";
                                        echo "<tr>";
                                            echo "<th>Setting</th>";
                                            echo "<th>Value</th>";
                                        echo "</tr>";
                                    echo "</thead>";
                                    echo "<tbody>";
                                        echo "<form class='form-horizontal' action='admin.php?database=$database' method='post'>";
                                            echo "<tr>";
                                                echo "<td>Database Name</td>";
                                                echo "<td><input type='text' class='form-control' name='database_name' value='".$databaseConfig["name"]."'></td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                                echo "<td>Database Description</td>";
                                                echo "<td><input type='text' class='form-control' name='database_description' value='".$databaseConfig["description"]."'></td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                                echo "<td>Require Authentication</td>";
                                                echo "<td><input type='checkbox' class='form-check-input' name='database_requireAuthentication' value='1'".($databaseConfig["requireAuthentication"] == "true" ? " checked": "")."></td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                                echo "<td>Require Encryption</td>";
                                                echo "<td><input type='checkbox' class='form-check-input' name='database_requireEncryption' value='1'".($databaseConfig["requireEncryption"] == "true" ? " checked": "")."></td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                                echo "<td>Auth Key Lifespan</td>";
                                                echo "<td><input type='text' class='form-control' name='database_authKeyLifespanInMinutes' value='".$databaseConfig["authKeyLifespanInMinutes"]."'></td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                                echo "<td>Save Changes</td>";
                                                echo "<input type='hidden' name='database' value='$database'>";
                                                echo "<td><button type='submit' class='btn btn-default'>Save</button></td>";
                                            echo "</tr>";
                                        echo "</form>";
                                    echo "</tbody>";
                                echo "</table>";
                            echo "</div>";
                        }
                    echo "</div>";
                    echo "<div class='row'>";
                        echo "<div class='col-sm-12'>";
                            echo "<h4 class='text-center'>Fields</h4>";
                            echo "<table class='table table-striped'>";
                                echo "<thead>";
                                    echo "<tr>";
                                        echo "<th>Type</th>";
                                        echo "<th>Key</th>";
                                        echo "<th>Value</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                    echo "<form class='form-horizontal' action='admin.php' method='post'>";
                                        if($database == null || $collection == null || $entry == null){
                                            echo "<tr> <td colspan='3'>Select a database, collection, and entry to view fields</td> </tr>";
                                        }else{
                                            $fields = getFieldsFromEntry($database, $collection, $entry);
                                            if($fields == false){
                                                echo "<tr> <td colspan='3'>Error opening $database / $collection / $entry</td> </tr>";
                                            }else{
                                                echo "<tr><form class='form-horizontal' action='admin.php' method='post'>";
                                                    echo "<td><input type='text' class='form-control' name='type' placeholder='Type'></td>";
                                                    echo "<td><input type='text' class='form-control' name='key' placeholder='Key'></td>";
                                                    echo "<td><input type='text' class='form-control' name='value' placeholder='Value'></td>";
                                                echo "</form></tr>";
                                                for($i = 0; $i < count($fields); $i++){
                                                    echo "<tr>";
                                                        echo "<td>".$fields[$i]["type"]."</td>";
                                                        echo "<td>".$fields[$i]["key"]."</td>";
                                                        echo "<td>".$fields[$i]["value"]."</td>";
                                                    echo "</tr>";
                                                }
                                            }
                                        }
                                    echo "</form>";
                                echo "</tbody>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
            }
            // Set database settings
            function setDatabaseSettings(){
                $database = $_POST["database"];
                if($database == null)
                    return;
                $values = array("name", "description", "requireAuthentication", "requireEncryption", "authKeyLifespanInMinutes");
                $databaseConfig = file_get_contents("hub/$database/config.json") or throwError("Could not read database config file", true);
                $databaseConfig = json_decode($databaseConfig, true) or throwError("Could not decode database config file", true);
                for($i = 0; $i < count($values); $i++){
                    if ($values[$i] == "requireAuthentication" || $values[$i] == "requireEncryption")
                        $_POST["database_".$values[$i]] = $_POST["database_".$values[$i]] == "1" ? "true" : "false";
                    echo $values[$i] . " = " . $_POST["database_".$values[$i]] . " | ";
                    $databaseConfig[$values[$i]] = $_POST["database_".$values[$i]];
                }
                $databaseConfig = json_encode($databaseConfig, JSON_PRETTY_PRINT) or throwError("Could not encode database config file", true);
                file_put_contents("hub/$database/config.json", $databaseConfig) or throwError("Could not write database config file", true);
            }
            // Get the hub tree
            function getHubTree(){
                $TREE_PATH = "hubTree.json";
                $tree = file_get_contents($TREE_PATH) or throwError("Could not read hub tree file", true);
                $tree = json_decode($tree, true) or throwError("Could not decode hub tree file", true);
                return $tree;
            }
            // Get a list of the paths to the databases
            function getDatabasesFromHub(){
                return array_keys(getHubTree());
            }
            // Get a list of the paths to the collections
            function getCollectionsFromDatabase($database){
                return array_keys(getHubTree()[$database]);
            }
            // Get a list of the paths to the entries
            function getEntriesFromCollection($database, $collection){
                return getHubTree()[$database][$collection];
            }
            // Get a list of the paths to the fields
            function getFieldsFromEntry($database, $collection, $entry){
                $path = "hub/".$database."/".$collection."/".$entry.".json";
                if(!file_exists($path))
                    return false;
                $entry = file_get_contents($path) or throwError("Could not read entry file", true);
                $entry = json_decode($entry, true) or throwError("Could not decode entry file", true);
                $fields = array();
                foreach($entry as $key => $value){
                    array_push($fields, array("key" => $key, "value" => $value, "type" => is_array($value) ? "Array" : "Value"));
                }
                return $fields;
            }
            // Show the login form
            function showLoginForm(){
                echo "<h4 class='text-center'>Please log in to continue</h4>";
                echo "<form class='form-horizontal' action='admin.php' method='post'>";
                    echo "<div class='form-group'>";
                        echo "<label class='control-label col-sm-2' for='username'>Username:</label>";
                        echo "<div class='col-sm-10'>";
                            echo "<input type='text' class='form-control' id='username' name='username' placeholder='Enter username'>";
                        echo "</div>";
                    echo "</div>";
                    echo "<div class='form-group'>";
                        echo "<label class='control-label col-sm-2' for='password'>Password:</label>";
                        echo "<div class='col-sm-10'>";
                            echo "<input type='password' class='form-control' id='password' name='password' placeholder='Enter password'>";
                        echo "</div>";
                    echo "</div>";
                    echo "<div class='form-group'>";
                        echo "<div class='col-sm-offset-2 col-sm-10'>";
                            echo "<button type='submit' class='btn btn-default'>Submit</button>";
                        echo "</div>";
                    echo "</div>";
                echo "</form>";
            }            
            // Check the username and password
            function checkUsernameAndPassword($username, $password, $adminConfig) {
                if($username == null || $password == null)
                    return false;
                if(!array_key_exists($username, $adminConfig["adminAccounts"]))
                    return throwError("Username not found");
                $pwdInConfig = $adminConfig["adminAccounts"][$username];
                if ($pwdInConfig != $password) {
                    return throwError("Incorrect password");
                }
                return true;
            }
            // Throw an error and stop the script
            function throwError($message = "unknown error", $fatal = false) {
                if($fatal){
                    echo "<script>alert('$message');</script>";
                    die();
                }
                echo "<p class='text-center text-danger'>$message</p>";
                return false;
            }
        ?>
    </body>
</html>