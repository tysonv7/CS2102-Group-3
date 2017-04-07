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
    </head>
    <body>
        <div id='login-logo'>
            <img src='img/cf.png' alt='logo' width='auto' height='100px'>
        </div>
        <div class="container form-login">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
                <div class='form-group'>
                    <label>Username:</label>
                    <input type='text' class='form-control' name='userid'>
                </div>
                <div class='form-group'>
                    <label>Password:</label>
                    <input type='password' class='form-control' name='password'>
                </div>
                <input type="submit" class="btn btn-primary form-control" value='Log In'>
            </form>
            <form action='register.php' method='post' class='register'>
                <input type='submit' class='btn btn-success form-control' value='Register'>
            </form>
        </div>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <div class="form-response">
            <?php
                if (!empty($_POST['userid']) && !empty($_POST['password'])) {
                    $uid = $_POST['userid'];
                    $pw = $_POST['password'];
                    $query = "SELECT * FROM users WHERE uid = '$uid'";
                    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                    $row = pg_fetch_row($result);

                    if ($row[0] == $uid && $row[2] == $pw) {
                        $_SESSION['userid'] = $_POST['userid'];
                        $_SESSION['password'] = $_POST['password'];

                        // Check if the user is an admin and set the session
                        // variable as appropriate
                        $query = "SELECT * FROM Admin WHERE uid = '$uid'";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 1) {
                            $_SESSION['isAdmin'] = true;
                        } else {
                            $_SESSION['isAdmin'] = false;
                        }
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        echo "<div class='container form-login form-error'><label>Wrong username or password entered</label></div>";
                    }
                }
            ?>
        </div>
    </body>
</html>
