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
        <a href="admin.php">Return to project management</a>

        <?php        
        
            // Connect to DB
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());

            if (isset($_GET['id'])) {
                $pid = $_GET['id'];
                $query = "SELECT * FROM Project WHERE pid = '$pid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                $row = pg_fetch_row($result);

                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                echo '<table>';

                echo '<tr>';
                echo '<td>Old Project ID: '.$row[0].'</td>';
                echo "<td>New Project ID: <input type='text' name='projId'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Project Title: '.$row[1].'</td>';
                echo "<td>New Project Title: <input type='text' name='projTitle'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Startdate: '.$row[2].'</td>';
                echo "<td>New Startdate: <input type='text' name='projStart'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Duration: '.$row[3].'</td>';
                echo "<td>New Duration: <input type='text' name='projDuration'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Category: '.$row[4].'</td>';
                echo "<td>New Category: <input type='text' name='projCategory'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Funding Goal: '.$row[5].'</td>';
                echo "<td>New Funding Goal: <input type='text' name='projFunding'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Description: '.$row[6].'</td>';
                echo "<td>New Description: <textarea name='projDescription' cols='40' rows='5'></textarea></td>";
                echo '</tr>';

                echo '</table>';
                echo "<input type='submit' name='submit'>";
                echo '</form>';

                if(isset($_POST['submit'])) {
                    $newpid = $_POST['projId'];
                    $title = $_POST['projTitle'];
                    $date = $_POST['projStart'];
                    $duration = $_POST['projDuration'];
                    $category = $_POST['projCategory'];
                    $funding = $_POST['projFunding'];
                    $desc = $_POST['projDescription'];
                    $sessionid = $_SESSION['userid'];
                    $query = "UPDATE Project SET pid = '$newpid', 
                              title = '$title', startDate = '$date', 
                              duration = '$duration', category = '$category',
                              fundNeeded = '$funding', description = '$desc'
                              WHERE pid = '$pid'";
                    $result = pg_query($query) or die ('Please check the update fields again');

                    $query = "SELECT * FROM Project WHERE pid = '$newpid'
                              AND title = '$title' AND startDate = '$date'
                              AND duration = '$duration' AND category = '$category'
                              AND fundNeeded = '$funding' AND description = '$desc'";
                    $result = pg_query($query) or die ('Update failed: Verification query failed');
                    if (pg_num_rows($result) == 1) {
                        echo '<div>Successfully updated project details</div>';
                    } else {
                        echo '<div>Update failed: Failed verification check</div>';
                    }
                }
            }
        ?>
    </body>
</html>
