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
        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <?php
            if (isset($_GET['id'])) {
                $project_id = $_GET['id'];
                // Query database to receive the project details
                $query = "SELECT * FROM project WHERE id = '$project_id'";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                $row = pg_fetch_row($result);

                echo '<h1>Project ID: '.$project_id.'</h1>';
                echo '<h1>'.$row[1].'</h1>';
                echo '<div>Start Date: '.$row[2].'</div>';
                echo '<div>Duration: '.$row[3].' days</div>';
                echo '<div>Category: '.$row[4].'</div>';
                echo '<div>Funding Goal: '.$row[5].'</div>';
            }
        ?>
    </body>
</html>
