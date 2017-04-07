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
            <input type='submit' name='toDashboard' value='< Back To Login Page' class='btn btn-primary btn-sm'>
        </form>

        <?php
            if (isset($_POST['toDashboard'])) {
                header('Location: index.php');
                exit();
            }
        ?>

        <div class='container addproj'>
            <h1>User Registration Page</h1>
            <br>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
                <div>User ID:</div>
                <input type='text' name='userid' size='41'>
                <br><br>
                <div>User Name:</div>
                <input type='text' name = 'username' size='41'>
                <br><br>
                <div>Password:</div>
                <input type='password' name='userpw' size='41' autocomplete='new-password'>
                <br><br>
                <input type='submit' name='submitUser' class='btn btn-primary btn-sm'>
            </form>
        </div>

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
                            echo "<div class='container addproj'>Successfully registered!</div>";
                        } else {
                            echo "<div class='container addproj'>Failed to register: Failed verification check</div>";
                        }
                    } else {
                        echo "<div class='container addproj'>Failed to register: Verification query error</div>";
                    }
                } else {
                    echo "<div class='container addproj'>Failed to register: Check the input fields again</div>";
                }
            }
        ?>
    </body>
</html>
