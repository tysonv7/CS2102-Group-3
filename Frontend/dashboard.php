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
        <?php
            echo "<form action='' method='post' id='form-logout'>";
            echo "<input type='submit' name='logout' value='< Log Out' class='btn btn-danger'>";
            echo '</form>';

            if (isset($_POST['logout'])) {
                session_destroy();
                session_unset();
                header('Location: index.php');
                exit();
            }
        ?>
        <h1 id='header-welcome'>Welcome, <?php echo $_SESSION['userid'];?></h1>
        <?php
            if ($_SESSION['isAdmin']) {
                echo "<a href='admin.php' id='header-admin-panel'>View administrator panel</a>";
            }
        ?>
        
        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <!-- Show a random featured project -->
        <div class='container fp'>
            <?php
                $query = "SELECT p.pid, p.title, fp.featureDate 
                          FROM FeaturedProject fp, project p WHERE fp.pid = p.pid";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                $randId = rand(0, pg_num_rows($result) - 1);
                $row = pg_fetch_row($result, $randId);

                echo '<span>Random Featured Project: </span>';
                echo '<a href="project.php?id='.$row[0].'">'.$row[1].'</a>';
                echo '<div>First featured: '.$row[2].'</div>';
            ?>
        </div>

        <!-- Display tabbed interface for showing statistics -->
        <div class='container stats'>
            <div class='jumbotron stats'>
            <ul class='nav nav-tabs'>
                <li class='active'><a data-toggle='tab' href='#mystats'>My Activity</a></li>
                <li><a data-toggle='tab' href='#projstats'>General Project Statistics</a></li>
            </ul>

            <!--Query DB for projects with searchterm-->
            <div class='search'>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'].'#search-results');?>" method="get">
                    <span>
                        <input type='radio' name='sk' value='title' class='search-control' checked> Title/Name
                        <input type='radio' name='sk' value='id' class='search-control' id='idRadioButton'> ID
                        <select name='ss' id='search-select' class='search-control'>
                            <option value='project'>Project</option>
                            <option value='users'>User</option>
                            <option value='comment'>Comments</option>
                            <option value='admin'>Administrators</option>
                            <option value='featuredproject'>Featured</option>
                            <option value='category'>Category</option>
                        </select>
                        <input type="text" name="st" class="search-bar search-control">
                        <input type="submit" class="search-control btn btn-primary" value='Search'>
                    </span>
                </form>
            </div>

            <div class='tab-content'>
                <div id='mystats' class='tab-pane fade in active'>
                    <!-- USER STATISTICS -->
                    <?php
                        echo "<div class='container'><div class=''row>";
                        $uid = $_SESSION['userid'];
                        // User's most backed and least backed amount
                        $query = "SELECT b1.pid, b1.amount, p.title FROM Back b1, Project P
                                WHERE NOT EXISTS (SELECT * FROM Back b2 
                                                    WHERE b2.amount > b1.amount
                                                    AND b2.uid = '$uid')
                                AND b1.uid = '$uid' AND b1.pid = p.pid";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Highest Backed Amount:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Highest Backed Amount: </div>";
                            echo "<div class='content-stats'><a href='project.php?id=".$row[0]."'>".$row[2].' ('.$row[1].")</a></div>";
                            echo '</div>';
                        }
                        // Least
                        $query = "SELECT b1.pid, b1.amount, p.title FROM Back b1 , Project p
                                WHERE NOT EXISTS (SELECT * FROM Back b2 
                                                    WHERE b2.amount < b1.amount
                                                    AND b2.uid = '$uid')
                                AND b1.uid = '$uid' AND b1.pid = p.pid";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Lowest Backed Amount:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Lowest Backed Amount:</div>";
                            echo "<div class='content-stats'><a href='project.php?id=".$row[0]."'>".$row[2].' ('.$row[1].")</a></div>";
                            echo '</div>';
                        }
                        echo '</div></div>';
                        // How much I have backed in total
                        echo "<div class='container'><div class=''row>";
                        $query = "SELECT SUM(amount) FROM Back WHERE uid = '$uid'";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Total Backed Amount:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Total Backed Amount:</div>";
                            echo "<div class='content-stats'>".$row[0]."</div>";
                            echo '</div>';
                        }
                        // How many projects I backed that have been successful
                        $query = "SELECT * FROM (SELECT b.pid FROM Back b, Project p
                                                GROUP BY b.pid, p.fundNeeded, p.pid
                                                HAVING SUM(b.amount) >= p.fundNeeded
                                                AND b.pid = p.pid) as fundedProjects, Back b
                                WHERE b.uid = '$uid' AND fundedProjects.pid = b.pid";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Successfully Backed Projects:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Successfully Backed Projects:</div>";
                            echo "<div class='content-stats'>".pg_num_rows($result)."</div>";
                            echo '</div>';
                        }
                        echo '</div></div>';
                        // Number of comments made by myself
                        echo "<div class='container'><div class=''row>";
                        $query = "SELECT * FROM Comment WHERE uid = '$uid'";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Total Comments:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Total Comments:</div>";
                            echo "<div class='content-stats'>".pg_num_rows($result)."</div>";
                            echo '</div>';
                        }
                        echo '</div></div>';
                    ?>
                </div>
                <div id='projstats' class='tab-pane fade in'>
                    <!-- PROJECT STATISTICS -->
                    <?php
                        $uid = $_SESSION['userid'];
                        // Find projects that are almost funded (>=90%)
                        $query = "SELECT b.pid, p.title, SUM(b.amount), p.fundNeeded 
                                FROM Back b, Project p 
                                GROUP BY b.pid, p.fundNeeded, p.pid, p.title
                                HAVING SUM(b.amount) >= p.fundNeeded*0.8
                                AND SUM(b.amount) < p.fundNeeded AND b.pid = p.pid";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        echo "<table id='table-80'>";
                        echo "<tr><th colspan='4'>Projects close to funding goal (80%)</th></tr>";
                        echo '<tr>';
                        echo '<th>Project ID</th>';
                        echo '<th>Project Title</th>';
                        echo '<th>Current Funding Amount</th>';
                        echo '<th>Funding Goal</th>';
                        echo '</tr>';
                        if (pg_num_rows($result) == 0) {
                            echo "<tr><td colspan='4'>None at the moment</td></tr>";
                        } else {
                            while ($row = pg_fetch_row($result)) {
                                echo '<tr>';
                                echo '<td>'.$row[0].'</td>';
                                echo "<td><a href='project.php?id=".$row[0]."'>".$row[1]."</a></td>";
                                echo '<td>'.$row[2].'</td>';
                                echo '<td>'.$row[3].'</td>';
                                echo '</tr>';
                            }
                        }
                        echo '</table>';
                        // Most funded project (both amount and percentage)
                        echo "<div class='container'><div class=''row>";
                        $query = "SELECT sumtable.pid, p.title, sumtable.sum 
                                FROM (SELECT b.pid, SUM(b.amount) as sum 
                                        FROM Back b GROUP BY b.pid) as sumtable, Project p
                                WHERE NOT EXISTS (SELECT * 
                                                    FROM (SELECT b.pid, SUM(b.amount) as sum 
                                                        FROM Back b GROUP BY b.pid) as sumtable2 
                                                    WHERE sumtable.sum < sumtable2.sum)
                                AND sumtable.pid = p.pid";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most funded project</div>";
                            echo "<div class='content-stats'>None</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most funded project:</div>";
                            echo "<div class='content-stats'><a href='project.php?id=".$row[0]."'>".$row[1]." (".$row[2].")</a></div>";
                            echo '</div>';
                        }
                        // Percentage
                        $query = "SELECT * 
                                FROM (SELECT b.pid, p.title, 100.0*SUM(b.amount)/p.fundNeeded as percent 
                                        FROM Back b, Project p GROUP BY b.pid, p.fundNeeded, p.pid 
                                        HAVING p.pid = b.pid) as ptable
                                WHERE NOT EXISTS (SELECT * 
                                                    FROM (SELECT b.pid, p.title, 100.0*SUM(b.amount)/p.fundNeeded as percent
                                                        FROM Back b, Project p GROUP BY b.pid, p.fundNeeded, p.pid 
                                                        HAVING b.pid = p.pid) as ptable2
                                                    WHERE ptable.percent < ptable2.percent)";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most funded project</div>";
                            echo "<div class='content-stats'>None</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[2], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most funded project:</div>";
                            echo "<div class='content-stats'><a href='project.php?id=".$row[0]."'>".$row[1]." (".$rounded."%)</a></div>";
                            echo '</div>';
                        }
                        echo '</div></div>';
                        // Average funding per project
                        echo "<div class='container'><div class=''row>";
                        $query = "SELECT AVG(sum) 
                                FROM (SELECT SUM(b.amount) as sum 
                                        FROM Back b GROUP BY b.pid) as sumtable";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average funding amount per project:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[0], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average funding amount per project:</div>";
                            echo "<div class='content-stats'>".$rounded."</div>";
                            echo '</div>';
                        }
                        $query = "SELECT AVG(sum) 
                                FROM (SELECT 100.0*SUM(b.amount)/p.fundNeeded as sum 
                                        FROM Back b, Project p 
                                        GROUP BY b.pid, p.fundNeeded, p.pid
                                        HAVING b.pid = p.pid) as sumtable";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average funding progress per project:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[0], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average funding progress per project:</div>";
                            echo "<div class='content-stats'>".$rounded."%</div>";
                            echo '</div>';
                        }
                        echo '</div></div>';
                        // Most commented on project
                        echo "<div class='container'><div class=''row>";
                        $query = "SELECT sumtable.pid, p.title, sumtable.sum
                                FROM (SELECT c.pid, COUNT(c.cid) as sum FROM Comment c
                                        GROUP BY c.pid) as sumtable, Project p
                                WHERE NOT EXISTS (SELECT * 
                                                    FROM (SELECT c.pid, COUNT(c.cid) as sum FROM Comment c
                                                        GROUP BY c.pid) as sumtable2
                                                    WHERE sumtable.sum < sumtable2.sum)
                                AND sumtable.pid = p.pid";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most commented project:</div>";
                            echo "<div class='content-stats'>None</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[0], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most commented project:</div>";
                            echo "<div class='content-stats'><a href='project.php?id=".$row[0]."'>".$row[1]." (".$row[2]." comments)</a></div>";
                            echo '</div>';
                        }
                        // Average number of comments per project
                        $query = "SELECT AVG(sum) 
                                FROM (SELECT COUNT(c.cid) as sum 
                                        FROM Comment c GROUP BY c.pid) as sumtable";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average comments per project:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[0], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average comments per project:</div>";
                            echo "<div class='content-stats'>".$rounded."</div>";
                            echo '</div>';
                        }
                        echo '</div></div>';
                        // Project with most backers
                        echo "<div class='container'><div class=''row>";
                        $query = "SELECT sumtable.pid, p.title, sumtable.sum
                                FROM (SELECT b.pid, COUNT(b.uid) as sum FROM Back b
                                        GROUP BY b.pid) as sumtable, Project p
                                WHERE NOT EXISTS (SELECT * 
                                                    FROM (SELECT b.pid, COUNT(b.uid) as sum FROM Back b
                                                        GROUP BY b.pid) as sumtable2
                                                    WHERE sumtable.sum < sumtable2.sum)
                                AND sumtable.pid = p.pid";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most backed project:</div>";
                            echo "<div class='content-stats'>None</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[0], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most backed project:</div>";
                            echo "<div class='content-stats'><a href='project.php?id=".$row[0]."'>".$row[1]." (".$row[2]." backers)</a></div>";
                            echo '</div>';
                        }
                        // Average backers per project
                        $query = "SELECT AVG(sum) 
                                FROM (SELECT COUNT(b.uid) as sum 
                                        FROM Back b GROUP BY b.pid) as sumtable";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average backers per project:</div>";
                            echo "<div class='content-stats'>0</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[0], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Average backers per project:</div>";
                            echo "<div class='content-stats'>".$rounded."</div>";
                            echo '</div>';
                        }
                        echo '</div></div>';
                        // Category with the most amount of projects
                        echo "<div class='container'><div class=''row>";
                        $query = "SELECT *
                                FROM (SELECT p.category, COUNT(p.pid) as sum FROM Project p
                                        GROUP BY p.category) as sumtable
                                WHERE NOT EXISTS (SELECT * 
                                                    FROM (SELECT p.category, COUNT(p.pid) as sum FROM Project p
                                                        GROUP BY p.category) as sumtable2
                                                    WHERE sumtable.sum < sumtable2.sum)";
                        $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                        if (pg_num_rows($result) == 0) {
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most popular category:</div>";
                            echo "<div class='content-stats'>None</div>";
                            echo '</div>';
                        } else {
                            $row = pg_fetch_row($result);
                            $rounded = number_format($row[0], 2, '.', '');
                            echo "<div class='col-md-6'>";
                            echo "<div class='header-stats'>Most popular category:</div>";
                            echo "<div class='content-stats'><a href='dashboard.php?st=".$row[0]."&ss=category&sk=title#search-results'>".$row[0]." (".$row[1]." backers)</a></div>";
                            echo '</div>';
                            echo '</div></div>';
                        }
                    ?>
                </div>
            </div>
            </div>
        </div>

        <!--Table for projects created-->
        <div class='container display-table'>
        <table>
            <tr>
                <th colspan="6" id='createProjHeader'>
                    <div id='createProjDiv'>
                        <span>Projects Created</span>
                        <form id='createProj' action="addproj.php" method='post'>
                            <input type='submit' name='submit' value='Create a new project' class='btn btn-primary'>
                        </form>
                    </div>
                </th>
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
                $query = "SELECT * FROM project p WHERE p.pid IN (SELECT s.pid FROM start s WHERE s.uid = '$userid')";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                //*/

                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    echo "<td colspan='6'>No projects created</td>";
                    echo '</tr>';
                }
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
        </table>
        </div>
        <!--Table for projects backed-->
        <div class='container display-table'>
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
                <th>Backed Amount</th>
            </tr>
            <!--Query the DB for all projects backed-->
            <?php
                $userid = $_SESSION['userid'];
                ///*
                $query = "SELECT * FROM project p, back b1 
                          WHERE p.pid IN (SELECT b2.pid FROM back b2 
                                          WHERE b2.uid = '$userid' 
                                          AND b1.pid = b2.pid AND b1.uid = b2.uid) 
                          AND b1.uid = '$userid'";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                //*/

                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    echo "<td colspan='6'>No projects backed</td>";
                    echo '</tr>';
                }
                while ($row = pg_fetch_row($result)) {
                    echo '<tr>';
                    echo '<td>' . $row[0] . '</td>';
                    echo '<td>'.'<a href="project.php?id='.$row[0].'">'.$row[1].'</a>'.'</td>';
                    echo '<td>' . $row[2] . '</td>';
                    echo '<td>' . $row[3] . '</td>';
                    echo '<td>' . $row[4] . '</td>';
                    echo '<td>' . $row[9] . '</td>';
                    echo '</tr>';
                }
            ?>
        </table>
        </div>

        <div class='container display-table'>
        <table>
            <!-- Query DB for all users this user is following -->
            <tr>
                <th colspan='8'>Following</th>
            </tr>
            <?php
                $userid = $_SESSION['userid'];
                $query = "SELECT u.uid, u.name FROM following f, users u 
                          WHERE f.uid2 = '$userid' AND u.uid = f.uid1";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    echo "<td colspan='8'>No one at present</td>";
                    echo '</tr>';
                } else {
                    $sum = 0;
                    while ($row = pg_fetch_row($result)) {
                        $sum = $sum + 1;
                        if ($sum > 8) {
                            echo '<tr>';
                        }
                        echo '<td><a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                        if ($sum > 8) {
                            echo '<tr>';
                            $sum = ($sum + 1) % 8;
                        }
                    }
                }
            ?>
            
            <!-- Query DB for all users following this user -->
            <tr>
                <th colspan='8'>Followed by</th>
            </tr>
            <?php
                $userid = $_SESSION['userid'];
                $query = "SELECT u.uid, u.name FROM following f, users u 
                          WHERE f.uid1 = '$userid' AND u.uid = f.uid2";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    echo "<td colspan='8'>No one at present</td>";
                    echo '</tr>';
                } else {
                    $sum = 0;
                    while ($row = pg_fetch_row($result)) {
                        $sum = $sum + 1;
                        if ($sum > 8) {
                            echo '<tr>';
                        }
                        echo '<td><a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                        if ($sum > 8) {
                            echo '<tr>';
                            $sum = ($sum + 1) % 8;
                        }
                    }
                }
            ?>
        </table>
        </div>

        <?php
            if (isset($_GET['st'])) {
                // Define the parameters for the search, there are three:
                // 1. Searchterm
                // 2. Table to search in
                // 3. Key to search by, ID or Title/Name
                $searchterm = $_GET['st'];
                $searchtable = $_GET['ss'];
                $searchkey = $_GET['sk'];
                $query = "SELECT * FROM "."$searchtable";
                $idRadioButton = 'false';
                switch ($searchtable) {
                    // 'category' is handled by the default case which uses projects 
                    // anyway hence it works
                    case 'category':
                        // Disable the radio button for ID and only allow searching by
                        // title of category
                        $idRadioButton = 'true';
                        $query = "SELECT * FROM Project WHERE LOWER(category) LIKE LOWER('%".$searchterm."%') ORDER BY category";
                        break;
                    case 'users':
                        switch ($searchkey) {
                            case 'id':
                                $query = $query." WHERE LOWER(uid) LIKE LOWER('%".$searchterm."%')";
                                break;
                            case 'title':
                            default:
                                $query = $query." WHERE LOWER(name) LIKE LOWER('%".$searchterm."%')";
                                break;
                        }
                        break;
                    case 'comment':
                        switch ($searchkey) {
                            case 'id':
                                $query = $query." WHERE cid = '$searchterm'";
                                break;
                            case 'title':
                            default:
                                $query = $query." WHERE LOWER(content) LIKE LOWER('%".$searchterm."%')";
                                break;
                        }
                        break;
                    case 'admin':
                        switch ($searchkey) {
                            case 'id':
                                $query = $query." WHERE LOWER(uid) LIKE LOWER('%".$searchterm."%')";
                                break;
                            case 'title':
                            default:
                                // Edit the query to match uids to users table
                                $query = $query." a, users u WHERE LOWER(u.name) LIKE LOWER('%".$searchterm."%') AND a.uid = u.uid";
                                break;
                        }
                        break;
                    case 'featuredproject':
                        switch ($searchkey) {
                            case 'id':
                                $query = $query." WHERE pid = '$searchterm'";
                                break;
                            case 'title':
                            default:
                                // Edit the query to match pids to the project table
                                $query = $query." fp, project p WHERE LOWER(p.title) LIKE LOWER('%".$searchterm."%') AND fp.pid = p.pid";
                                break;
                        }
                        break;
                    case 'project':
                    default:
                        switch ($searchkey) {
                            case 'id':
                                $query = $query." WHERE pid = '$searchterm'";
                                break;
                            case 'title':
                            default:
                                $query = $query." WHERE LOWER(title) LIKE LOWER('%".$searchterm."%')";
                                break;
                        }
                        break;
                }
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                
                echo "<div class='container search-table' id='search-results'>";
                echo "<table>";
                echo '<tr>';
                switch ($searchtable) {
                        case 'comment':
                            echo '<th colspan="4">Results</th>';
                            break;
                        case 'users':
                        case 'admin':
                            echo '<th colspan="2">Results</th>';
                            break;
                        case 'project':
                        case 'featuredproject':
                        default:
                            echo '<th colspan="6">Results</th>';
                            break;
                    }
                echo '</tr>';
                
                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    switch ($searchtable) {
                        case 'comment':
                            echo '<td colspan="4">No results</td>';
                            break;
                        case 'users':
                        case 'admin':
                            echo '<td colspan="2">No results</td>';
                            break;
                        case 'project':
                        case 'featuredproject':
                        default:
                            echo '<td colspan="6">No results</td>';
                            break;
                    }
                    echo '</tr>';
                } else {
                    switch ($searchtable) {
                        case 'comment':
                            echo '<tr>';
                            echo '<th>Comment ID</th>';
                            echo '<th>User ID</th>';
                            echo '<th>Project Title</th>';
                            echo '<th>Comment</th>';
                            echo '</tr>';
                            while ($row = pg_fetch_row($result)) {
                                // Query the database to match project IDs to titles
                                $subquery = "SELECT * FROM Project WHERE pid = '$row[2]'";
                                $subresult = pg_query($subquery) or die ('Query failed: '.pg_last_error());
                                $subrow = pg_fetch_row($subresult);
                                echo '<tr>';
                                echo '<td>' . $row[0] . '</td>';
                                echo '<td><a href="user.php?userid='.$row[1].'">'.$row[1].'</a>'.'</td>';
                                echo '<td><a href="project.php?id='.$row[2].'">'.$subrow[1].'</a>'.'</td>';
                                echo '<td>' . $row[3] . '</td>';
                                echo '</tr>';
                            }
                            break;
                        case 'admin':
                            echo '<tr>';
                            echo '<th>User ID</th>';
                            echo '<th>User Name</th>';
                            echo '</tr>';
                            while ($row = pg_fetch_row($result)) {
                                // Query the database to match project IDs to titles
                                $subquery = "SELECT * FROM Users WHERE uid = '$row[0]'";
                                $subresult = pg_query($subquery) or die ('Query failed: '.pg_last_error());
                                $subrow = pg_fetch_row($subresult);
                                echo '<tr>';
                                echo '<td>' . $subrow[0] . '</td>';
                                echo '<td><a href="user.php?userid='.$subrow[0].'">'.$subrow[1].'</a>'.'</td>';
                                echo '</tr>';
                            }
                            break;
                        case 'users':
                            echo '<tr>';
                            echo '<th>User ID</th>';
                            echo '<th>User Name</th>';
                            echo '</tr>';
                            while ($row = pg_fetch_row($result)) {
                                echo '<tr>';
                                echo '<td>' . $row[0] . '</td>';
                                echo '<td><a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                                echo '</tr>';
                            }
                            break;
                        case 'featuredproject':
                            echo '<tr>';
                            echo '<th>ID</th>';
                            echo '<th>Title</th>';
                            echo '<th>Start Date</th>';
                            echo '<th>Duration</th>';
                            echo '<th>Category</th>';
                            echo '<th>Funding Goal</th>';
                            echo '</tr>';
                            while ($row = pg_fetch_row($result)) {
                                // Query the database to match project IDs to titles
                                $subquery = "SELECT * FROM Project WHERE pid = '$row[0]'";
                                $subresult = pg_query($subquery) or die ('Query failed: '.pg_last_error());
                                $subrow = pg_fetch_row($subresult);
                                echo '<tr>';
                                echo '<td>' . $subrow[0] . '</td>';
                                echo '<td>'.'<a href="project.php?id='.$subrow[0].'">'.$subrow[1].'</a>'.'</td>';
                                echo '<td>' . $subrow[2] . '</td>';
                                echo '<td>' . $subrow[3] . '</td>';
                                echo '<td>' . $subrow[4] . '</td>';
                                echo '<td>' . $subrow[5] . '</td>';
                                echo '</tr>';
                            }
                            break;
                        case 'projects':
                        default:
                            echo '<tr>';
                            echo '<th>ID</th>';
                            echo '<th>Title</th>';
                            echo '<th>Start Date</th>';
                            echo '<th>Duration</th>';
                            echo '<th>Category</th>';
                            echo '<th>Funding Goal</th>';
                            echo '</tr>';
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
                            break;
                    }
                }
                echo '</table>';
                echo '</div>';
            }
        ?>
        <!-- Script to handle disabling of radio button -->
        <script>
            document.getElementById('search-select').onchange = function () {
                if (this.selectedIndex == 5) {
                    document.getElementById('idRadioButton').disabled = true;
                } else {
                    document.getElementById('idRadioButton').disabled = false;
                }
            }
        </script>
    </body>
</html>
