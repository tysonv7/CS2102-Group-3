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














-- Admin can see how many projects each person backed
SELECT b.uid, COUNT(*) AS numProjectsBacked, SUM(amount) AS totalAmountBacked
FROM Back b
GROUP BY b.uid
ORDER BY COUNT(*) DESC;
