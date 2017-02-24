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
        <!--PHP script-->
        <div class="form-response">
            <?php
                if (!empty($_POST['userid']) && !empty($_POST['password'])) {
                    // Need to connect to db to verify username against password
                    // but leave this unimplemented for now
                    $_SESSION['userid'] = $_POST['userid'];
                    $_SESSION['password'] = $_POST['password'];
                    header('Location: user.php');
                    exit();
                }
            ?>
        </div>
    </body>
</html>
