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
        <h1>Project Creation Page</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
            <div>Title of project:</div>
            <input type='text' name='projTitle'>
            <div>Duration:</div>
            <input type='text' name = 'projDuration'>
            <div>Category:</div>
            <input type='text' name='projCategory'>
            <div>Funding Goal:</div>
            <input type='text' name='projFunding'>
            <div>Description(Max 2000 characters):</div>
            <textarea name='projDescription' cols='40' rows='5'></textarea>
            <br>
            <input type='submit' name='submit'>
        </form>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());

            if (isset($_POST['submit'])) {
                $query = "SELECT COUNT(*) FROM project";
                $result = pg_query($query) or die;
                $row = pg_fetch_row($result);

                $pid = $row[0] + 1;
                $title = $_POST['projTitle'];
                $date = date('Y-m-d');
                $duration = $_POST['projDuration'];
                $category = $_POST['projCategory'];
                $funding = $_POST['projFunding'];
                $desc = $_POST['projDescription'];
                $sessionid = $_SESSION['userid'];

                $query = "INSERT INTO Project (pid, title, startDate, duration, category, fundNeeded, description) 
                          VALUES ('$pid', '$title', '$date', '$duration', '$category', '$funding', '$desc');
                          INSERT INTO Start (uid, pid) VALUES ('$sessionid', '$pid')";
                if ($result = pg_query($query)) {
                    // Verification
                    $query = "SELECT * FROM Project p
                              WHERE p.pid = '$pid' AND p.title = '$title'
                              AND p.startDate = '$date' AND p.duration = '$duration'
                              AND p.category = '$category' AND p.fundNeeded = '$funding'
                              AND p.description = '$desc'";
                    if ($result = pg_query($query)) {
                        if (pg_num_rows($result) == 1) {
                            echo '<div>Successfully created project!</div>';
                            echo "<a href='dashboard.php'>Return to dashboard</a>";
                        } else {
                            echo '<div>Failed to create project: Failed verification check</div>';
                        }
                    } else {
                        echo '<div>Failed to create project: Verification query error</div>';
                    }
                } else {
                    echo '<div>Failed to create project: Check the input fields again</div>';
                }
            }
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
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        echo 'Wrong username or password entered';
                    }
                }
            ?>
        </div>
    </body>
</html>
