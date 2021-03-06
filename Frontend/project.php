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

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <?php
            if (isset($_GET['id'])) {
                // Get the session userid and project id
                $userid = $_SESSION['userid'];
                $project_id = $_GET['id'];

                // Query database to receive the project details
                $query = "SELECT * FROM project WHERE pid = '$project_id'";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                $row = pg_fetch_row($result);

                $subquery = "SELECT SUM(amount) FROM Back WHERE pid = '$project_id'";
                $subresult = pg_query($subquery) or die ('Query failed: '.pg_last_error());
                $subrow = pg_fetch_row($subresult);
                $goalpercent = $subrow[0]/$row[5]*100.0;
                $rounded = number_format($goalpercent, 2, '.', '');

                echo "<div id='header-project'>";
                echo '<h1>Project ID: '.$project_id.'</h1>';
                echo '<h1>'.$row[1].'</h1>';
                echo '<div>Current Funding Progress:</div>';
                echo "<div id='projFunding'>".$subrow[0].'/'.$row[5].' ('.$rounded.'%)'.'</div>';
                if ($goalpercent >= 100) {
                    echo "<strong id='header-success'>Project successfully funded!</strong><br>";
                }
                echo '<br>';

                // Query DB to check if the current user has a
                // row in the Back table yet
                $query = "SELECT * FROM Back b 
                          WHERE b.uid = '$userid' AND b.pid = '$project_id'";
                $result = pg_query($query) or die('Query failed: '.pg_last_error());
                if (pg_num_rows($result) == 0) {
                    $buttonLabel = 'Back this project';
                    $buttonColor = 'btn-success';
                } else {
                    $buttonLabel = 'Update your pledge';
                    $buttonColor = 'btn-warning';
                    // Also display a small notification showing the user's
                    // current pledge
                    $temp = pg_fetch_row($result);
                    echo '<div>Your current pledge: '.$temp[2].'</div>';
                }

                // Display a mini form to back the project from here
                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                echo "<input type='text' name='submitAmount' id='header-back'>";
                echo "<input type='submit' name='submit' value='$buttonLabel' class='btn btn-sm ".$buttonColor."'>";
                echo '</form>';
                // Check if a backAmount has been submitted, run the 
                // insertion script if backAmount is submitted
                if (isset($_POST['submit'])) {
                    $submitAmount = $_POST['submitAmount'];
                    if (pg_num_rows($result) == 0) {
                        // Case 1: The current user has not backed this
                        // project before and thus a new row needs to be
                        // inserted into the back table
                        $query = "INSERT INTO Back (uid, pid, amount) 
                                  VALUES ('$userid', '$project_id', $submitAmount)";
                    } else {
                        // Case 2: The current user has already backed
                        // this project and thus just needs to update
                        // the back amount
                        $query = "UPDATE Back SET amount = $submitAmount 
                                  WHERE uid = '$userid'
                                  AND pid = '$project_id'";
                    }

                    if ($result = pg_query($query)) {
                        // Verify the result of the insertion before confirming to user
                        $query = "SELECT * FROM Back b 
                                  WHERE b.pid = '$project_id'
                                  AND b.uid = '$userid'
                                  AND b.amount = $submitAmount";
                        if ($result = pg_query($query)) {
                            if (pg_num_rows($result) == 1) {
                                echo '<div>Successfully backed this project for $'.$submitAmount.'</div>';
                            } else {
                                echo '<div>Failed to update backing amount: Failed verification check</div>';
                            }
                        } else {
                            echo '<div>Failed to update backing amount: Verification query error</div>';
                        }
                    } else {
                        echo '<div>Failed to update backing amount: Only numbers allowed</div>';
                    }

                    // Refresh the page to update the backing amount notification
                    header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
                    die;
                }

                echo '<br>';
                echo '<div>Start Date: '.$row[2].'</div>';
                echo '<div>Duration: '.$row[3].' days</div>';
                echo '<div>Category: '.$row[4].'</div>';
                echo '<div>Funding Goal: '.$row[5].'</div>';
                echo '<div>Description: '.$row[6].'</div>';
                echo '</div>';
            }
        ?>

        <!-- Display backers for this project -->
        <div class='container display-table'>
        <table>
            <?php
                if (isset($_GET['id'])) {
                    $query = "SELECT b.uid, u.name FROM Back b, Users u 
                              WHERE b.pid = '$project_id' AND u.uid = b.uid";
                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                    echo "<tr><th colspan='8'>Backers (".pg_num_rows($result).")</th></tr>";
                    if (pg_num_rows($result) == 0) {
                        echo "<tr><td colspan='8'>No backers</td></tr>";
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
                }
            ?>
        </table>
        </div>

        <!-- Comment section -->
        <div class='container comment'>
            <label>Leave a comment:</label>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);?>" method='post'>
                <textarea name='commentBox' cols='80' rows='5'></textarea>
                <br>
                <input type='submit' name='submitComment' value='Comment' class='btn btn-primary btn-sm'>
            </form>
        </div>

        <?php

            if (isset($_POST['deleteComment'])) {
                $cid = $_POST['deleteComment'];
                $query = "DELETE FROM Comment 
                            WHERE cid = '$cid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                // Refresh the page to update the backing amount notification
                header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
                die;
            }

            if (isset($_GET['id'])) {
                $userid = $_SESSION['userid'];
                $project_id = $_GET['id'];
                if (isset($_POST['submitComment'])) {
                    // Query database to receive the comments for that project
                    $query = "SELECT c.cid FROM Comment c";
                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                    $commentid = pg_num_rows($result) + 1;
                    $content = $_POST['commentBox'];
                    $query = "INSERT INTO Comment (cid, uid, pid, content)
                              VALUES ('$commentid', '$userid', '$project_id', '$content')";
                    $result = pg_query($query) or die('Query failed: '.pg_last_error());

                    // Refresh the page to update the backing amount notification
                    header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
                    die;
                }
            }
        ?>

        <div class='container comment-table'>
        <table>
            <?php
                if (isset($_GET['id'])) {
                    $project_id = $_GET['id'];
                    // Query database to receive the comments for that project
                    $query = "SELECT u.name, c.content, c.cid FROM comment c, users u 
                              WHERE c.pid = '$project_id' AND c.uid = u.uid";
                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                    if ($_SESSION['isAdmin']) {
                        echo "<tr><th colspan='3'>Comments (".pg_num_rows($result).")</th></tr>";
                    } else {
                        echo "<tr><th colspan='2'>Comments (".pg_num_rows($result).")</th></tr>";
                    }

                    if (pg_num_rows($result) == 0) {
                        if ($_SESSION['isAdmin']) {
                            echo "<tr><th colspan='3'>No comments</th></tr>";
                        } else {
                            echo "<tr><th colspan='2'>No comments</th></tr>";
                        }
                    }
                    while ($row = pg_fetch_row($result)) {
                        echo '<tr>';
                        echo '<td>'.$row[0].'</td>';
                        echo '<td>'.$row[1].'</td>';
                        if ($_SESSION['isAdmin']) {
                            echo '<td>';
                            echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                            echo "<button type='submit' name='deleteComment' value='$row[2]' class='btn btn-danger btn-sm'>Delete</button>";
                            echo '</form>';
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                }
            ?>
        </table>
        </div>
    </body>
</html>
