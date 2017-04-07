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

            if (isset($_GET['userid'])) {
                $uid = $_GET['userid'];
                $query = "SELECT * FROM Users WHERE uid = '$uid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                $row = pg_fetch_row($result);

                echo "<div class='container admin'>";
                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                echo '<table>';

                echo '<tr>';
                echo '<td>Old User ID:</td>';
                echo '<td>'.$row[0].'</td>';
                echo "<td>New User ID:</td>";
                echo "<td><input type='text' name='userid' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Username:</td>';
                echo '<td>'.$row[1].'</td>';
                echo "<td>New Username:</td>";
                echo "<td><input type='text' name='username' size='41'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Password:</td>';
                echo '<td>'.$row[2].'</td>';
                echo "<td>New Password:</td>";
                echo "<td><input type='text' name='userpw' size='41'></td>";
                echo '</tr>';

                echo '</table>';
                echo '<br>';
                echo "<input type='submit' name='submit' class='btn btn-primary btn-sm'>";
                echo '</form>';
                echo '</div>';

                if(isset($_POST['submit'])) {
                    $newuid = $_POST['userid'];
                    $username = $_POST['username'];
                    $password = $_POST['userpw'];

                    $query = "UPDATE Users SET uid = '$newuid', 
                              name = '$username', password = '$password' 
                              WHERE uid = '$uid'";
                    $result = pg_query($query) or die ('Please check the update fields again');

                    $query = "SELECT * FROM Users WHERE uid = '$newuid'
                              AND name = '$username' AND password = '$password'";
                    $result = pg_query($query) or die ('Update failed: Verification query failed');
                    if (pg_num_rows($result) == 1) {
                        echo "<div class='container addproj'>Successfully updated user details</div>";
                    } else {
                        echo "<div class='container addproj'>Update failed: Failed verification check</div>";
                    }
                }
            }
        ?>
    </body>
</html>
