<?php
    session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="styles.css">
        <script>

            function switchToMyStats() {
                document.getElementById('mystats').style.display = '';
                document.getElementById('projstats').style.display = 'none';
            }

            function switchToProjStats() {
                document.getElementById('mystats').style.display = 'none';
                document.getElementById('projstats').style.display = '';
            }
        </script>
    </head>
    <body>
        <h1>Welcome, <?php echo $_SESSION['userid'];?></h1>
        <div>You logged in with password: <?php echo $_SESSION['password'];?></div>
        <?php
            if ($_SESSION['isAdmin']) {
                echo "<a href='admin.php'>View administrator panel</a>";
                echo '<br><br>';
            }
        ?>
        
        <!--Connect to DB-->
        <?php
            $dbconn = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password")
            or die("Could not connect: " . pg_last_error());
        ?>

        <!-- Show a random featured project -->
        <div>
            <?php
                $query = "SELECT p.pid, p.title, fp.featureDate 
                          FROM FeaturedProject fp, project p WHERE fp.pid = p.pid";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                $randId = rand(0, pg_num_rows($result) - 1);
                $row = pg_fetch_row($result, $randId);

                echo '<div>';
                echo '<span>Random Featured Project: </span>';
                echo '<a href="project.php?id='.$row[0].'">'.$row[1].'</a>';
                echo '</div>';

                echo '<div>';
                echo '<span>First featured: '.$row[2].'</span>';
                echo '</div>';
            ?>
        </div>

        <span onClick=switchToMyStats()>My Activity</span>
        <span onClick=switchToProjStats()>General Statistics</span>

        <!-- Show statistics for user -->
        <div id='mystats' style="display: ''">
            <?php
                $uid = $_SESSION['userid'];
                // User's most backed and least backed amount
                $query = "SELECT * FROM Back b1 
                        WHERE NOT EXISTS (SELECT * FROM Back b2 
                                            WHERE b2.amount > b1.amount
                                            AND b2.uid = '$uid')
                        AND b1.uid = '$uid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                if (pg_num_rows($result) == 0) {
                    echo "<div>Your most backed amount on a project: 0</div>";
                } else {
                    $row = pg_fetch_row($result);
                    echo "<div>Your most backed amount on a project: "
                        ."<a href='project.php?id=".$row[1]."'>".$row[2]."</a>"
                        ."</div>";
                }
                // Least
                $query = "SELECT * FROM Back b1 
                        WHERE NOT EXISTS (SELECT * FROM Back b2 
                                            WHERE b2.amount < b1.amount
                                            AND b2.uid = '$uid')
                        AND b1.uid = '$uid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                if (pg_num_rows($result) == 0) {
                    echo "<div>Your least backed amount on a project: 0</div>";
                } else {
                    $row = pg_fetch_row($result);
                    echo "<div>Your least backed amount on a project: "
                        ."<a href='project.php?id=".$row[1]."'>".$row[2]."</a>"
                        ."</div>";
                }
                // How much I have backed in total
                $query = "SELECT SUM(amount) FROM Back WHERE uid = '$uid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                if (pg_num_rows($result) == 0) {
                    echo '<div>Total amount backed on all projects: 0</div>';
                } else {
                    $row = pg_fetch_row($result);
                    echo '<div>Total amount backed on all projects: '.$row[0].'</div>';
                }
                // How many projects I backed that have been successful
                $query = "SELECT * FROM (SELECT b.pid FROM Back b, Project p
                                        GROUP BY b.pid, p.fundNeeded, p.pid
                                        HAVING SUM(b.amount) >= p.fundNeeded
                                        AND b.pid = p.pid) as fundedProjects, Back b
                        WHERE b.uid = '$uid' AND fundedProjects.pid = b.pid";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                if (pg_num_rows($result) == 0) {
                    echo '<div>Successfully backed projects: 0</div>';
                } else {
                    echo '<div>Successfully backed projects: '.pg_num_rows($result).'</div>';
                }
                // Number of comments made by myself
                $query = "SELECT * FROM Comment WHERE uid = '$uid'";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                if (pg_num_rows($result) == 0) {
                    echo '<div>Total comments made: 0</div>';
                } else {
                    echo '<div>Total comments made: '.pg_num_rows($result).'</div>';
                }
            ?>
        </div>

        <!-- Show general statistics for projects -->
        <div id='projstats' style="display: none">
            <?php
                $uid = $_SESSION['userid'];
                // Find projects that are almost funded (>=90%)
                $query = "SELECT b.pid, p.title, SUM(b.amount), p.fundNeeded 
                          FROM Back b, Project p 
                          GROUP BY b.pid, p.fundNeeded, p.pid, p.title
                          HAVING SUM(b.amount) >= p.fundNeeded*0.8
                          AND SUM(b.amount) < p.fundNeeded AND b.pid = p.pid";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());
                echo '<table>';
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
                // Average funding per project
                // Most commented on project
                // Average number of comments per project
                // Category with the most amount of projects
                // Project with most backers
                // Average backers per project
            ?>
        </div>

        <!--Table for projects created-->
        <table>
            <tr>
                <th colspan="6">
                    <span>Projects Created</span>
                    <form action="addproj.php" method='post'>
                        <input type='submit' name='submit' value='Create a new project'>
                    </form>
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
        </table></br>

        <table>
            <!-- Query DB for all users this user is following -->
            <tr>
                <th>Following</th>
            </tr>
            <?php
                $userid = $_SESSION['userid'];
                $query = "SELECT u.uid, u.name FROM following f, users u 
                          WHERE f.uid2 = '$userid' AND u.uid = f.uid1";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    echo '<td>No one at present</td>';
                    echo '</tr>';
                } else {
                    while ($row = pg_fetch_row($result)) {
                        echo '<tr>';
                        echo '<td><a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                        echo '</tr>';
                    }
                }
            ?>
            
            <!-- Query DB for all users following this user -->
            <tr>
                <th>Followed by</th>
            </tr>
            <?php
                $userid = $_SESSION['userid'];
                $query = "SELECT u.uid, u.name FROM following f, users u 
                          WHERE f.uid1 = '$userid' AND u.uid = f.uid2";
                $result = pg_query($query) or die ('Query failed: '.pg_last_error());

                if (pg_num_rows($result) == 0) {
                    echo '<tr>';
                    echo '<td>No one at present</td>';
                    echo '</tr>';
                } else {
                    while ($row = pg_fetch_row($result)) {
                        echo '<tr>';
                        echo '<td><a href="user.php?userid='.$row[0].'">'.$row[1].'</a>'.'</td>';
                        echo '</tr>';
                    }
                }
            ?>
        </table>

        <!--Query DB for projects with searchterm-->
        <div>
            <div>Search for more projects:</div>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="get">
                <span>
                    <input type="text" name="st" class="search-bar search-control">
                    <select name='ss'>
                        <option value='project'>Project</option>
                        <option value='users'>User</option>
                        <option value='comment'>Comments</option>
                        <option value='admin'>Administrators</option>
                        <option value='featuredproject'>Featured</option>
                    </select>
                    <input type='radio' name='sk' value='title' checked>Title/Name
                    <input type='radio' name='sk' value='id'>ID
                    <input type="submit" class="search-control" value='Search'>
                </span>
            </form>
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
                switch ($searchtable) {
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
                
                echo '<table class="search-table">';
                echo '<tr>';
                switch ($searchtable) {
                        case 'comment':
                            echo '<td colspan="4">Results</td>';
                            break;
                        case 'users':
                        case 'admin':
                            echo '<td colspan="2">Results</td>';
                            break;
                        case 'project':
                        case 'featuredproject':
                        default:
                            echo '<td colspan="6">Results</td>';
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
            }
        ?>
    </body>
</html>
