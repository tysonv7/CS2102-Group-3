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
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post" class="container form-signin">
            <div class="form-header">Username:</div>
            <input type="text" name="userid" class="form-control"></br>
            <div class="form-header">Password:</div>
            <input type="text" name="password" class="form-control"></br>
            <input type="submit" class="form-control">
        </form>

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
                    $query = "SELECT * FROM users WHERE id = '$uid'";
                    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                    $row = pg_fetch_row($result);

                    if ($row[0] == $uid && $row[2] == $pw) {
                        $_SESSION['userid'] = $_POST['userid'];
                        $_SESSION['password'] = $_POST['password'];
                        header('Location: user.php');
                        exit();
                    } else {
                        echo 'Wrong username or password entered';
                    }
                }
            ?>
        </div>
    </body>
</html>
