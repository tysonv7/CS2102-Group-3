-- Simple
-- Getting projects with funding needed > amount dictated by user
-- @REPLACE number with user's value
SELECT *
FROM Project
WHERE fundNeeded > 100000;

-- Get projects according to their Category
SELECT *
FROM Project
WHERE category = 'Technology'

-- Search project by name
-- convert values to lower-case as LIKE is case-sensitive
SELECT *
FROM Project
WHERE LOWER(title) LIKE LOWER('%water%')
  OR LOWER(description) LIKE LOWER('%water%');

-- Search user by name
SELECT *
FROM Users
WHERE LOWER(name) LIKE LOWER('%Julie%');

-- Search projects created by a specific user
SELECT p.pid, p.title, p.description
FROM Project p, Start s
WHERE s.uid = 'rarnoldks'
  AND s.pid = p.pid;

-- List of user's backings from most backed to least
SELECT p.pid, p.title, p.description, b.amount
FROM Project p, Back b
WHERE b.uid = 'dsullivan3'
  AND b.pid = p.pid
ORDER BY b.amount DESC;

-- List of who the user follows
SELECT u.name
FROM Following f, Users u
WHERE f.uid2 = 'kirigiri'
  AND f.uid1 = u.uid
ORDER BY u.name;

-- List of a user's following
SELECT u.name
FROM Following f, Users u
WHERE f.uid1 = 'kirigiri'
  AND f.uid2 = u.uid
ORDER BY u.name;

-- Get the list of all projects created by people whom the user is following
SELECT u.name, p.title, p.description
FROM Project p, Users u, Following f, Start s
WHERE f.uid2 = 'kirigiri'
  AND f.uid1 = u.uid
  AND s.uid = u.uid
  AND s.pid = p.pid
ORDER BY u.name, p.title;

-- Get the list of all projects backed by people whom the user is following
SELECT u.name, p.title, p.description
FROM Project p, Users u, Following f, Back b
WHERE f.uid2 = 'kirigiri'
  AND f.uid1 = u.uid
  AND b.uid = u.uid
  AND b.pid = p.pid
ORDER BY u.name, p.title;

--Get the list of comments associated to a project
SELECT c.content
FROM Project p, Comment c
WHERE c.pid = p.id;

--Get the list of all comments made by a user
SELECT c.content
FROM Comment c, Users u
WHERE c.uid = u.id;

--Get the list of all comments made by a user on a project
SELECT c.content
FROM Project p, Comment c, Users u
WHERE c.pid = p.id AND c.uid = u.id;

--Get the list of users and ordered alphabetically
SELECT u.id, u.name
FROM Users u
ORDER BY u.name ASC;

--Get the list of users and ordered reversed alphabetically
SELECT u.id, u.name
FROM Users u
GROUP BY u.name DESC;

--Get the list of project and ordered alphabetically
SELECT p.id, p.title, p.startDate, p.duration, p.category, p.fundNeeded
FROM Project p
ORDER BY p.title ASC;

--Get the list of project and ordered reversed alphabetically
SELECT p.id, p.title, p.startDate, p.duration, p.category, p.fundNeeded
FROM Project p
ORDER BY p.title DESC;

-- AGGREGATE QUERIES

-- Get how much a person has backed so far
SELECT SUM(amount)
FROM Back
WHERE uid = 'gintoki';

-- Get total amount backed for a project
SELECT SUM(amount)
FROM Back
WHERE pid = '100';

-- Getting projects with funding > an amount
-- not sure if this is okay because nested query in FROM clause, but seems to be no other way
-- Can be done without nesting in FROM clause if funding does not need to be displayed
SELECT p.title, p.description, tmp.funding
FROM Project p NATURAL JOIN (
  SELECT b.pid, SUM(b.amount) AS funding
  FROM Back b
  GROUP BY b.pid
  HAVING SUM(b.amount) > 800
) AS tmp
ORDER BY tmp.funding DESC;

-- Most funded project
SELECT p.pid, p.title, p.description
FROM Project p
WHERE p.pid IN (
  SELECT b.pid
  FROM Back b
  GROUP BY b.pid
  ORDER BY SUM(b.amount) DESC
  LIMIT 1
);

-- Most commented on project
SELECT p.pid, p.title, p.description
FROM Project p
WHERE p.pid IN (
  SELECT c.pid
  FROM Comment c
  GROUP BY c.pid
  ORDER BY COUNT(*) DESC
  LIMIT 1
);

-- Number of projects per category
SELECT category, COUNT(*)
FROM Project
GROUP BY category
ORDER BY COUNT(*) DESC;

-- People who have not started any projects
SELECT DISTINCT u.uid
FROM Users u
WHERE u.uid NOT IN (
  SELECT s.uid
  FROM Start s
)

-- People who have not backed any projects
SELECT DISTINCT u.uid
FROM Users u
WHERE u.uid NOT IN (
  SELECT b.uid
  FROM Back b
)


-- Admin can see how many projects each person backed
SELECT b.uid, COUNT(*) AS numProjectsBacked, SUM(amount) AS totalAmountBacked
FROM Back b
GROUP BY b.uid
ORDER BY COUNT(*) DESC;

--Count total number of backers for each project
SELECT COUNT(b.pid)
FROM Back b
WHERE b.pid = '100'; --The '100' is a stub. Must change to user input

--Count total number of projects a person has backed
SELECT COUNT(b.uid)
FROM Back b
WHERE b.uid = 'tysonv7'; -- The 'tysonv7' is a stub. Must change to user input

--GROUP the projects a user has backed by successful

--GROUP the projects a user has backed by unsuccessful

--Admin can view total amount of funding for all projects
SELECT b.pid, p.title, SUM(b.amount)
FROM Back b, Project p
WHERE b.pid = p.id
GROUP BY b.pid, p.title
ORDER BY p.title ASC;

--Admin can see average how many projects a person backed
SELECT COUNT(b.pid)/COUNT(b.uid) AS AverageNumberOfProjectsBackedPerPerson
FROM BACK b;

--Admin can see average backing a project receive
SELECT SUM(b.amount)/COUNT(b.uid) AS AverageAmountOfBackingPerProject
FROM Back b;

--Admin can see average number of projects created by user
SELECT COUNT(s.pid)/COUNT(s.uid) AS AverageNumberOfProjectsCreatedPerUser
FROM Start s;

--Nested Queries
--Admin can see a list of how many users have backed a project
SELECT u.id, u.name
FROM Users u
WHERE u.id = ANY (SELECT b.uid FROM Back b);

--Admin can see a list of how many users who have started a project
SELECT u.id, u.name
FROM Users u
WHERE u.id = ANY (SELECT s.uid FROM Start s);

--Algebraic
--Admin can get list of what users have started and backed
SELECT u.id AS Username, u.name, CASE WHEN s.pid IS NULL THEN 'Have not started any project' ELSE s.pid END AS StartedProject, CASE WHEN b.pid IS NULL THEN 'Have not backed any project' ELSE b.pid END AS BackedProject, b.amount
FROM USERS u LEFT OUTER JOIN Start s ON u.id = s.uid LEFT OUTER JOIN Back b ON u.id = b.uid
GROUP BY u.id, s.pid, b.pid, b.amount
ORDER BY u.id;

--Admin can get list of what users have started and backed, specified to which category of projects
SELECT u.id AS Username, u.name, CASE WHEN s.pid IS NULL THEN 'Have not started any project' ELSE s.pid END AS StartedProjectID, CASE WHEN b.pid IS NULL THEN 'Have not backed any project' ELSE b.pid END AS BackedProjectID, b.amount
FROM USERS u LEFT OUTER JOIN Start s ON u.id = s.uid LEFT OUTER JOIN Back b ON u.id = b.uid, Project p
WHERE (s.pid = p.id AND p.category = 'Technology') OR (b.uid = p.id AND p.category = 'Technology') --'Technology' is a stub. Need to replace with users input
GROUP BY u.id, s.pid,p.title, b.pid, b.amount
ORDER BY u.id;

