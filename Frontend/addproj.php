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
            <input type='submit' name='toDashboard' value='< Back To Dashboard' class='btn btn-primary btn-sm'>
        </form>

        <?php
            if (isset($_POST['toDashboard'])) {
                header('Location: dashboard.php');
                exit();
            }
        ?>

        <div class='container addproj'>
            <h1>Project Creation Page</h1>
            <br>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
                <div>Title of project:</div>
                <input type='text' name='projTitle' size='41'>
                <br><br>
                <div>Duration:</div>
                <input type='text' name = 'projDuration' size='41'>
                <br><br>
                <div>Category:</div>
                <input type='text' name='projCategory' size='41'>
                <br><br>
                <div>Funding Goal:</div>
                <input type='text' name='projFunding' size='41'>
                <br><br>
                <div>Description(Max 2000 characters):</div>
                <textarea name='projDescription' cols='40' rows='5'></textarea>
                <br><br>
                <input type='submit' name='submitProj' class='btn btn-primary btn-sm'>
            </form>
        </div>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());

            if (isset($_POST['submitProj'])) {
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
                            echo "<div class='container addproj'>Successfully created project!</div>";
                        } else {
                            echo "<div class='container addproj'>Failed to create project: Failed verification check</div>";
                        }
                    } else {
                        echo "<div class='container addproj'>Failed to create project: Verification query error</div>";
                    }
                } else {
                    echo "<div class='container addproj'>Failed to create project: Check the input fields again</div>";
                }
            }
        ?>
    </body>
</html>
