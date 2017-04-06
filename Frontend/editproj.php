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
            <input type='submit' name='toDashboard' value='< Back To Administrator Panel' class='btn btn-primary btn-sm'>
        </form>

        <?php
            if (isset($_POST['toDashboard'])) {
                header('Location: admin.php');
                exit();
            }
        ?>

        <?php        
        
            // Connect to DB
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());

            if (isset($_GET['id'])) {
                $pid = $_GET['id'];
                $query = "SELECT * FROM Project WHERE pid = '$pid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                $row = pg_fetch_row($result);

                echo "<div class='container admin'>";
                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                echo '<table>';

                echo '<tr>';
                echo '<td>Old Project ID:</td>';
                echo '<td>'.$row[0].'</td>';
                echo "<td>New Project ID:</td>";
                echo "<td><input type='text' name='projId' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Project Title:</td>';
                echo '<td>'.$row[1].'</td>';
                echo "<td>New Project Title:</td>";
                echo "<td><input type='text' name='projTitle' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Startdate:</td>';
                echo '<td>'.$row[2].'</td>';
                echo "<td>New Startdate:</td>";
                echo "<td><input type='text' name='projStart' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Duration:</td>';
                echo '<td>'.$row[3].'</td>';
                echo "<td>New Duration:</td>";
                echo "<td><input type='text' name='projDuration' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Category:</td>';
                echo '<td>'.$row[4].'</td>';
                echo "<td>New Category:</td>";
                echo "<td><input type='text' name='projCategory' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Funding Goal:</td>';
                echo '<td>'.$row[5].'</td>';
                echo "<td>New Funding Goal:</td>";
                echo "<td><input type='text' name='projFunding' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Description:</td>';
                echo '<td>'.$row[6].'</td>';
                echo "<td>New Description:</td>";
                echo "<td><textarea name='projDescription' cols='40' rows='5'></textarea></td>";
                echo '</tr>';

                echo '</table>';
                echo '<br>';
                echo "<input type='submit' name='submit' class='btn btn-primary btn-sm'>";
                echo '</form>';
                echo '</div>';

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
