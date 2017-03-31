<?php
    session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <!-- Return to user's personal dashboard page -->
        <a href="dashboard.php">Return to your dashboard</a>

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

                echo '<h1>Project ID: '.$project_id.'</h1>';
                echo '<h1>'.$row[1].'</h1>';

                // Query DB to check if the current user has a
                // row in the Back table yet
                $query = "SELECT * FROM Back b 
                          WHERE b.uid = '$userid' AND b.pid = '$project_id'";
                $result = pg_query($query) or die('Query failed: '.pg_last_error());
                if (pg_num_rows($result) == 0) {
                    $buttonLabel = 'Back this project';
                } else {
                    $buttonLabel = 'Update your pledge';
                    // Also display a small notification showing the user's
                    // current pledge
                    $temp = pg_fetch_row($result);
                    echo '<div>Your current pledge: '.$temp[2].'</div>';
                }

                // Display a mini form to back the project from here
                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                echo "<input type='text' name='submitAmount'>";
                echo "<input type='submit' name='submit' value='$buttonLabel'>";
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
            }
        ?>

        <table>
            <tr>
                <th colspan='2'>Comments</th>
            </tr>
            <?php
                if (isset($_GET['id'])) {
                    $project_id = $_GET['id'];
                    // Query database to receive the comments for that project
                    $query = "SELECT u.name, c.content FROM comment c, users u 
                              WHERE c.pid = '$project_id' AND c.uid = u.uid";
                    $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                    while ($row = pg_fetch_row($result)) {
                        echo '<tr>';
                        echo '<td>'.$row[0].'</td>';
                        echo '<td>'.$row[1].'</td>';
                        echo '</tr>';
                    }
                }
            ?>
        </table>
    </body>
</html>
