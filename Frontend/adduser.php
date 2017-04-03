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

        <h1>User Registration Page</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
            <div>User ID:</div>
            <input type='text' name='userid'>
            <div>User Name:</div>
            <input type='text' name = 'username'>
            <div>Password:</div>
            <input type='text' name='userpw'>
            <br>
            <input type='submit' name='submitUser'>
        </form>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());

            if (isset($_POST['submitUser'])) {
                $query = "SELECT COUNT(*) FROM project";
                $result = pg_query($query) or die;
                $row = pg_fetch_row($result);

                $userid = $_POST['userid'];
                $username = $_POST['username'];
                $password = $_POST['userpw'];

                $query = "INSERT INTO Users (uid, name, password) 
                          VALUES ('$userid', '$username', '$password')";          
                if ($result = pg_query($query)) {
                    // Verification
                    $query = "SELECT * FROM Users WHERE uid = '$userid'
                              AND name = '$username' AND password = '$password'";
                    if ($result = pg_query($query)) {
                        if (pg_num_rows($result) == 1) {
                            echo '<div>Successfully registered user!</div>';
                        } else {
                            echo '<div>Failed to register user: Failed verification check</div>';
                        }
                    } else {
                        echo '<div>Failed to register user: Verification query error</div>';
                    }
                } else {
                    echo '<div>Failed to register user: Check the input fields again</div>';
                }
            }
        ?>
    </body>
</html>
