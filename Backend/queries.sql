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
