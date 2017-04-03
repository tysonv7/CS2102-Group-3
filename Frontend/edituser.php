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

            if (isset($_GET['userid'])) {
                $uid = $_GET['userid'];
                $query = "SELECT * FROM Users WHERE uid = '$uid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                $row = pg_fetch_row($result);

                echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])."' method='post'>";
                echo '<table>';

                echo '<tr>';
                echo '<td>Old User ID: '.$row[0].'</td>';
                echo "<td>New User ID: <input type='text' name='userid'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Username: '.$row[1].'</td>';
                echo "<td>New Username: <input type='text' name='username'></td>";
                echo '</tr>';

                echo '<tr>';
                echo '<td>Old Password: '.$row[2].'</td>';
                echo "<td>New Password: <input type='text' name='userpw'></td>";
                echo '</tr>';

                echo '</table>';
                echo "<input type='submit' name='submit'>";
                echo '</form>';

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
                        echo '<div>Successfully updated user details</div>';
                    } else {
                        echo '<div>Update failed: Failed verification check</div>';
                    }
                }
            }
        ?>
    </body>
</html>
