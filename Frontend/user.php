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
        <h1>Welcome, <?php echo $_SESSION['userid'];?></h1>
        <p>You logged in with password: <?php echo $_SESSION['password'];?></p>

        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <!--Table for projects created-->
        <table>
            <tr>
                <th colspan="6">Projects Created</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Start Date</th>
                <th>Duration</th>
                <th>Category</th>
                <th>Funding Goal</th>
            </tr>
            <!--Query the DB for all projects created-->
            <?php
                $userid = $_SESSION['userid'];
                ///*
                $query = "SELECT * FROM project p WHERE p.id IN (SELECT projectid FROM start s WHERE s.creatorid = '$userid')";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                //*/

                while ($row = pg_fetch_row($result)) {
                    echo '<tr>';
                    echo '<td>' . $row[0] . '</td>';
                    echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                    echo '<td>' . $row[2] . '</td>';
                    echo '<td>' . $row[3] . '</td>';
                    echo '<td>' . $row[4] . '</td>';
                    echo '<td>' . $row[5] . '</td>';
                    echo '</tr>';
                }
            ?>
        </table></br>
        <!--Table for projects backed-->
        <table>
            <tr>
                <th colspan="6">Projects Backed</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Start Date</th>
                <th>Duration</th>
                <th>Category</th>
                <th>Funding Goal</th>
            </tr>
            <!--Query the DB for all projects backed-->
            <?php
                $userid = $_SESSION['userid'];
                ///*
                $query = "SELECT * FROM project p WHERE p.id IN (SELECT projectid FROM back b WHERE b.backerid = '$userid')";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                //*/

                while ($row = pg_fetch_row($result)) {
                    echo '<tr>';
                    echo '<td>' . $row[0] . '</td>';
                    echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                    echo '<td>' . $row[2] . '</td>';
                    echo '<td>' . $row[3] . '</td>';
                    echo '<td>' . $row[4] . '</td>';
                    echo '<td>' . $row[5] . '</td>';
                    echo '</tr>';
                }
            ?>
        </table></br>
        <div>
            <div>Search for more projects:</div>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="get">
                <span>
                    <input type="text" name="searchterm" class="search-bar search-control">
                    <input type="submit" class="search-control">
                </span>
            </form>
        </div>

        <!--Query DB for projects with searchterm-->
        <?php
            if (isset($_GET['searchterm'])) {
                $searchterm = $_GET['searchterm'];
                $query = "SELECT * FROM project WHERE title LIKE '%".$searchterm."%'";
                $result = pg_query($query) or die ('Query failed: ' . pg_last_error());

                echo '<table class="search-table">';
                echo '<tr>';
                echo '<th colspan="6">Results</th>';
                echo '</tr>';

                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    echo '<td colspan="6">No results</td>';
                    echo '</tr>';
                } else {
                    while ($row = pg_fetch_row($result)) {
                        echo '<tr>';
                        echo '<td>' . $row[0] . '</td>';
                        echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                        echo '<td>' . $row[2] . '</td>';
                        echo '<td>' . $row[3] . '</td>';
                        echo '<td>' . $row[4] . '</td>';
                        echo '<td>' . $row[5] . '</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
            }
        ?>
    </body>
</html>
