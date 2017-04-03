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
        <script>

            function switchToProjMgmt() {
                document.getElementById('projmgmt').style.display = '';
                document.getElementById('usermgmt').style.display = 'none';
            }

            function switchToUserMgmt() {
                document.getElementById('projmgmt').style.display = 'none';
                document.getElementById('usermgmt').style.display = '';
            }
        </script>
    </head>
    <body>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <!-- Return to user's personal dashboard page -->
        <a href="dashboard.php">Return to your dashboard</a>
        <br>
        <span onClick='switchToProjMgmt()'>Project Management</span>
        <span onClick='switchToUserMgmt()'>User Management</span>

        <table id='projmgmt' style="display: ''">
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
                        $buttonLabel = 'Remove from featured';
                    } else {
                        $buttonLabel = 'Mark as featured';
                    }

                    echo '<tr>';
                    echo '<td>' . $row[0] . '</td>';
                    echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                    echo '<td>' . $row[2] . '</td>';

                    echo '<td>';
                    echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                    echo "<button type='submit' name='deleteProj' value='$row[0]'>Delete</button>";
                    echo '</form>';
                    echo '</td>';

                    echo '<td>';
                    echo "<form action='".htmlspecialchars('editproj.php')."' method='get'>";
                    echo "<button type='submit' name='id' value='$row[0]'>Edit</button>";
                    echo '</form>';
                    echo '</td>';

                    echo '<td>';
                    echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                    echo "<button type='submit' name='toggleFeatured' value='$row[0]'>$buttonLabel</button>";
                    echo '</form>';
                    echo '</td>';

                    echo '</tr>';
                }
            ?>
        </table>
        <br>
        <table id='usermgmt' style="display: none">
            <tr>
                <th colspan='6'>
                    <span>User Management</span>
                    <form action="adduser.php" method='post'>
                        <input type='submit' name='submit' value='Register a new user'>
                    </form>
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
                        $buttonLabel = 'Remove from administrators';
                    } else {
                        $buttonLabel = 'Make administrator';
                    }

                    echo '<tr>';
                    echo '<td>' . $row[0] . '</td>';
                    echo '<td>'.'<a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                    echo '<td>' . $row[2] . '</td>';

                    echo '<td>';
                    echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                    echo "<button type='submit' name='deleteUser' value='$row[0]'>Delete</button>";
                    echo '</form>';
                    echo '</td>';

                    echo '<td>';
                    echo "<form action='".htmlspecialchars('edituser.php')."' method='get'>";
                    echo "<button type='submit' name='userid' value='$row[0]'>Edit</button>";
                    echo '</form>';
                    echo '</td>';

                    echo '<td>';
                    echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>";
                    echo "<button type='submit' name='toggleAdmin' value='$row[0]'>$buttonLabel</button>";
                    echo '</form>';
                    echo '</td>';

                    echo '</tr>';
                }
            ?>
        </table>
    </body>
</html>