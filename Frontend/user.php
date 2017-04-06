<?php
    session_start();
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

        <h1 id='header-user'>
            <?php echo $_GET['userid'];?>
        </h1>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <?php
            // Do not display the follow option if the user is viewing
            // his own page (for whatever reason)
            $sessionid = $_SESSION['userid'];
            $userid = $_GET['userid'];
            if ($sessionid != $userid) {
                // Check if the session user is already following this user
                $query = "SELECT * FROM Following f 
                          WHERE f.uid1 = '$userid' AND f.uid2 = '$sessionid'";
                $result = pg_query($query);
                
                if ($result = pg_query($query)) {
                    if (pg_num_rows($result) == 0) {
                        // Case 1: The session user is not following this user
                        // Display the follow button
                        $buttonLabel = 'Follow this user';
                        $buttonColor = 'btn-success';
                        $query = "INSERT INTO Following (uid1, uid2) 
                                  VALUES ('$userid', '$sessionid')";
                    } else {
                        // Case 2: The session user is already following this user
                        // Display the unfollow button
                        $buttonLabel = 'Unfollow this user';
                        $buttonColor = 'btn-danger';
                        $query = "DELETE FROM Following     
                                  WHERE uid1 = '$userid' AND uid2 = '$sessionid'";
                    }
                } else {
                    die;
                }

                echo "<div class='container header-follow'>";
                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                echo "<input type='submit' name='submit' value='$buttonLabel' class='btn btn-sm ".$buttonColor."'>";
                echo '</form>';
                echo '</div>';

                // Check if the follow button was submitted to run the
                // insertion script
                if (isset($_POST['submit'])) {
                    if (pg_num_rows($result) == 0) {
                        // Case 1: Add follow relation
                        if ($result = pg_query($query)) {
                            // Verify the insertion
                            $query = "SELECT * FROM Following f 
                                      WHERE f.uid1 = '$userid' AND f.uid2 = '$sessionid'";
                            if ($result = pg_query($query)) {
                                if (pg_num_rows($result) == 1) {
                                    echo '<div>You are now following '.$userid.'</div>';
                                } else {
                                    echo '<div>Failed to follow user: Failed verification check</div>';
                                }
                            } else {
                                echo '<div>Failed to follow user: Verification query error</div>';
                            }
                        } else {
                            echo '<div>Failed to follow user: Database insertion failed</div>';
                        }
                    } else {
                        // Case 2: Delete follow relation
                        if ($result = pg_query($query)) {
                            // Verify the deletion
                            $query = "SELECT * FROM Following f 
                                      WHERE f.uid1 = '$userid' AND f.uid2 = '$sessionid'";
                            if ($result = pg_query($query)) {
                                if (pg_num_rows($result) == 0) {
                                    echo '<div>You are no longer following '.$userid.'</div>';
                                } else {
                                    echo '<div>Failed to unfollow user: Failed verification check</div>';
                                }
                            } else {
                                echo '<div>Failed to unfollow user: Verification query error</div>';
                            }
                        } else {
                            echo '<div>Failed to unfollow user: Database deletion failed</div>';
                        }
                    }

                    // Refresh the page to update the follow list
                    header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
                    die;
                }
            }
        ?>

        <!--Table for projects created-->
        <div class='container display-table'>
            <table>
                <tr>
                    <th colspan="6">Projects created</th>
                </tr>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Start Date</th>
                    <th>Duration</th>
                    <th>Category</th>
                    <th>Funding Goal</th>
                </tr>
                <!--Query the DB for all projects created-->
                <?php
                    $userid = $_GET['userid'];
                    ///*
                    $query = "SELECT * FROM project p WHERE p.pid IN (SELECT s.pid FROM start s WHERE s.uid = '$userid')";
                    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                    //*/

                    if (pg_num_rows($result) == 0) {
                        echo '<tr>';
                        echo "<td colspan='6'>No projects created</td>";
                        echo '</tr>';
                    }
                    while ($row = pg_fetch_row($result)) {
                        echo '<tr>';
                        echo '<td>' . $row[0] . '</td>';
                        echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                        echo '<td>' . $row[2] . '</td>';
                        echo '<td>' . $row[3] . '</td>';
                        echo '<td>' . $row[4] . '</td>';
                        echo '<td>' . $row[5] . '</td>';
                        echo '</tr>';
                    }
                ?>
            </table>
            </div>
            <!--Table for projects backed-->
            <div class='container display-table'>
            <table>
                <tr>
                    <th colspan="6">Projects Backed</th>
                </tr>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Start Date</th>
                    <th>Duration</th>
                    <th>Category</th>
                    <th>Backed Amount</th>
                </tr>
                <!--Query the DB for all projects backed-->
                <?php
                    $userid = $_GET['userid'];
                    ///*
                    $query = "SELECT * FROM project p, back b1 
                            WHERE p.pid IN (SELECT b2.pid FROM back b2 
                                            WHERE b2.uid = '$userid' 
                                            AND b1.pid = b2.pid AND b1.uid = b2.uid) 
                            AND b1.uid = '$userid'";
                    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                    //*/

                    if (pg_num_rows($result) == 0) {
                        echo '<tr>';
                        echo "<td colspan='6'>No projects backed</td>";
                        echo '</tr>';
                    }
                    while ($row = pg_fetch_row($result)) {
                        echo '<tr>';
                        echo '<td>' . $row[0] . '</td>';
                        echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                        echo '<td>' . $row[2] . '</td>';
                        echo '<td>' . $row[3] . '</td>';
                        echo '<td>' . $row[4] . '</td>';
                        echo '<td>' . $row[9] . '</td>';
                        echo '</tr>';
                    }
                ?>
            </table>
            </div>

            <div class='container display-table'>
            <table>
                <!-- Query DB for all users this user is following -->
                <tr>
                    <th colspan='8'>Following</th>
                </tr>
                <?php
                    $userid = $_GET['userid'];
                    $query = "SELECT u.uid, u.name FROM following f, users u 
                            WHERE f.uid2 = '$userid' AND u.uid = f.uid1";
                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                    if (pg_num_rows($result) == 0) {
                        echo '<tr>';
                        echo "<td colspan='8'>No one at present</td>";
                        echo '</tr>';
                    } else {
                        $sum = 0;
                        while ($row = pg_fetch_row($result)) {
                            $sum = $sum + 1;
                            if ($sum > 8) {
                                echo '<tr>';
                                $sum = 1;
                            }
                            echo '<td><a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                            if ($sum > 8) {
                                echo '</tr>';
                            }
                        }
                    }
                ?>
                
                <!-- Query DB for all users following this user -->
                <tr>
                    <th colspan='8'>Followed by</th>
                </tr>
                <?php
                    $userid = $_GET['userid'];
                    $query = "SELECT u.uid, u.name FROM following f, users u 
                            WHERE f.uid1 = '$userid' AND u.uid = f.uid2";
                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                    if (pg_num_rows($result) == 0) {
                        echo '<tr>';
                        echo "<td colspan='8'>No one at present</td>";
                        echo '</tr>';
                    } else {
                        $sum = 0;
                        while ($row = pg_fetch_row($result)) {
                            $sum = $sum + 1;
                            if ($sum > 8) {
                                echo '<tr>';
                                $sum = 1;
                            }
                            echo '<td><a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                            if ($sum > 8) {
                                echo '</tr>';
                            }
                        }
                    }
                ?>
            </table>
        </div>
    </body>
</html>
