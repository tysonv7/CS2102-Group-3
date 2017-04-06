<?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="styles.css">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </head>
    <body>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <!-- Return to user's personal dashboard page -->
        <form action='' method='post' id='form-to-dashboard'>
            <input type='submit' name='toDashboard' value='< Back To Dashboard' class='btn btn-primary btn-sm'>
        </form>

        <?php
            if (isset($_POST['toDashboard'])) {
                header('Location: dashboard.php');
                exit();
            }
        ?>
    
        <div class='container admin'>
            <ul class='nav nav-tabs'>
                <li class='active'><a data-toggle='tab' href='#projmgmt'>Project Management</a></li>
                <li><a data-toggle='tab' href='#usermgmt'>User Management</a></li>
            </ul>

            <div class='tab-content tab-admin'>
                <div id='projmgmt' class='tab-pane fade in active'>
                    <table class='table-admin'>
                        <tr>
                            <th colspan='6'>Project Management</th>
                        </tr>
                        <tr>
                            <th>Project ID</th>
                            <th>Project Title</th>
                            <th>Start Date</th>
                            <th>Delete</th>
                            <th>Edit</th>
                            <th>Featured Settings</th>
                        </tr>
                        <?php
                            $query = "SELECT * FROM Project";
                            $result = pg_query($query) or die('Query failed: ' . pg_last_error());

                            if (isset($_POST['deleteProj'])) {
                                $pid = $_POST['deleteProj'];
                                $query = "DELETE FROM Project
                                        WHERE pid = '$pid'";
                                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                                
                                // Refresh the page to update the backing amount notification
                                header('Location: '.$_SERVER['PHP_SELF']);
                                die;
                            } else if (isset($_POST['toggleFeatured'])) {
                                $pid = $_POST['toggleFeatured'];
                                // Run a subquery to check if the current project is also a featured project
                                $subquery = "SELECT * FROM FeaturedProject WHERE pid = '$pid'";
                                $subresult = pg_query($subquery) or die ('Subquery failed: '.pg_last_error());
                                if (pg_num_rows($subresult) == 1) {
                                    // Remove from featured projects
                                    $query = "DELETE FROM FeaturedProject WHERE pid = '$pid'";
                                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                                } else {
                                    // Add to featured projects
                                    $date = date('Y-m-d');
                                    $query = "INSERT INTO FeaturedProject (pid, featureDate) 
                                            VALUES ('$pid', '$date')";
                                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                                }
                                // Refresh the page to update the backing amount notification
                                header('Location: '.$_SERVER['PHP_SELF']);
                                die;
                            }
                            if (isset($_POST['deleteUser'])) {
                                $uid = $_POST['deleteUser'];
                                $query = "DELETE FROM Users WHERE uid = '$uid'";
                                $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                                // Refresh the page to update the backing amount notification
                                header('Location: '.$_SERVER['PHP_SELF']);
                                die;
                            } else if (isset($_POST['toggleAdmin'])) {
                                $uid = $_POST['toggleAdmin'];
                                // Run a subquery to check if the current user is an administrator
                                $subquery = "SELECT * FROM Admin WHERE uid = '$uid'";
                                $subresult = pg_query($subquery) or die ('Subquery failed: '.pg_last_error());
                                if (pg_num_rows($subresult) == 1) {
                                    // Remove from the Admin table
                                    $query = "DELETE FROM Admin WHERE uid = '$uid'";
                                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                                } else {
                                    // Insert into the Admin table
                                    $query = "INSERT INTO Admin (uid) VALUES ('$uid')";
                                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                                }
                                header('Location: '.$_SERVER['PHP_SELF']);
                                die;
                            }

                            while ($row = pg_fetch_row($result)) {
                                // Run a subquery to check if the current project is also a featured project
                                $subquery = "SELECT * FROM FeaturedProject WHERE pid = '$row[0]'";
                                $subresult = pg_query($subquery) or die ('Subquery failed: '.pg_last_error());
                                if (pg_num_rows($subresult) == 1) {
                                    $buttonLabel = 'Remove From Featured';
                                } else {
                                    $buttonLabel = 'Mark As Featured';
                                }

                                echo '<tr>';
                                echo '<td>' . $row[0] . '</td>';
                                echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                                echo '<td>' . $row[2] . '</td>';

                                echo '<td>';
                                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                                echo "<button type='submit' name='deleteProj' value='$row[0]' class='btn btn-danger btn-sm'>Delete</button>";
                                echo '</form>';
                                echo '</td>';

                                echo '<td>';
                                echo "<form action='".htmlspecialchars('editproj.php')."' method='get'>";
                                echo "<button type='submit' name='id' value='$row[0]' class='btn btn-warning btn-sm'>Edit</button>";
                                echo '</form>';
                                echo '</td>';

                                echo '<td>';
                                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                                echo "<button type='submit' name='toggleFeatured' value='$row[0]' class='btn btn-primary btn-sm'>$buttonLabel</button>";
                                echo '</form>';
                                echo '</td>';

                                echo '</tr>';
                            }
                        ?>
                    </table>
                </div>
                <div id='usermgmt' class='tab-pane fade in'>
                    <table class='table-admin'>
                        <tr>
                            <th colspan='6' id='createProjHeader'>
                                <div id='createProjDiv'>
                                    <span>User Management</span>
                                    <form action="adduser.php" method='post' id='createProj'>
                                        <input type='submit' name='submit' value='Register a new user' class='btn btn-success btn-sm'>
                                    </form>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Password</th>
                            <th>Delete</th>
                            <th>Edit</th>
                            <th>Administrator Settings</th>
                        </tr>
                        <?php
                            $query = "SELECT * FROM Users";
                            $result = pg_query($query) or die('Query failed: ' . pg_last_error());

                            while ($row = pg_fetch_row($result)) {
                                // Run a subquery to check if the current user is an administrator
                                $subquery = "SELECT * FROM Admin WHERE uid = '$row[0]'";
                                $subresult = pg_query($subquery) or die ('Subquery failed: '.pg_last_error());
                                if (pg_num_rows($subresult) == 1) {
                                    $buttonLabel = 'Remove From Admin';
                                } else {
                                    $buttonLabel = 'Make Admin';
                                }

                                echo '<tr>';
                                echo '<td>' . $row[0] . '</td>';
                                echo '<td>'.'<a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                                echo '<td>' . $row[2] . '</td>';

                                echo '<td>';
                                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                                echo "<button type='submit' name='deleteUser' value='$row[0]' class='btn btn-danger btn-sm'>Delete</button>";
                                echo '</form>';
                                echo '</td>';

                                echo '<td>';
                                echo "<form action='".htmlspecialchars('edituser.php')."' method='get'>";
                                echo "<button type='submit' name='userid' value='$row[0]' class='btn btn-warning btn-sm'>Edit</button>";
                                echo '</form>';
                                echo '</td>';

                                echo '<td>';
                                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                                echo "<button type='submit' name='toggleAdmin' value='$row[0]' class='btn btn-primary btn-sm'>$buttonLabel</button>";
                                echo '</form>';
                                echo '</td>';

                                echo '</tr>';
                            }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>