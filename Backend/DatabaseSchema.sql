CREATE TABLE Users (
  uid VARCHAR(64),
  name VARCHAR(64) NOT NULL,
  password VARCHAR(64) NOT NULL,
  PRIMARY KEY (uid)
);

CREATE OR REPLACE FUNCTION UsersUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'Users Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER UsersTableTrigger
BEFORE INSERT OR UPDATE
ON Users
FOR EACH STATEMENT
EXECUTE PROCEDURE UsersUpdate();

CREATE TABLE Admin (
  uid VARCHAR(64),
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid)
);

CREATE OR REPLACE FUNCTION AdminUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'Admin Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER AdminTableTrigger
BEFORE INSERT OR UPDATE
ON Admin
FOR EACH STATEMENT
EXECUTE PROCEDURE AdminUpdate();

-- uid1: person being followed
-- uid2: the follower
CREATE TABLE Following (
  uid1 VARCHAR(64),
  uid2 VARCHAR(64),
  FOREIGN KEY (uid2) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (uid2) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid1, uid2)
);

CREATE OR REPLACE FUNCTION FollowingUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'Following Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER FollowingTableTrigger
BEFORE INSERT OR UPDATE
ON Following
FOR EACH STATEMENT
EXECUTE PROCEDURE FollowingUpdate();

CREATE TABLE Project (
  pid INT,
  title VARCHAR(64) NOT NULL,
  startDate DATE NOT NULL,
  duration INT NOT NULL,
  category VARCHAR(64),
  fundNeeded INT NOT NULL,
  description VARCHAR(2000),
  PRIMARY KEY (pid)
);

CREATE OR REPLACE FUNCTION ProjectUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'Project Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER ProjectTableTrigger
BEFORE INSERT OR UPDATE
ON Project
FOR EACH STATEMENT
EXECUTE PROCEDURE ProjectUpdate();

CREATE TABLE FeaturedProject (
  pid INT,
  featureDate DATE NOT NULL,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (pid)
);

CREATE OR REPLACE FUNCTION FeaturedProjectUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'FeaturedProject Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER FeaturedProjectTableTrigger
BEFORE INSERT OR UPDATE
ON FeaturedProject
FOR EACH STATEMENT
EXECUTE PROCEDURE FeaturedProjectUpdate();

CREATE TABLE Start (
  uid VARCHAR(64),
  pid INT,
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid, pid)
);

CREATE OR REPLACE FUNCTION StartUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'Start Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER StartTableTrigger
BEFORE INSERT OR UPDATE
ON Start
FOR EACH STATEMENT
EXECUTE PROCEDURE StartUpdate();

CREATE TABLE Back (
  uid VARCHAR(64),
  pid INT,
  amount INT NOT NULL,
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid, pid)
);

CREATE OR REPLACE FUNCTION BackUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'Back Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER BackTableTrigger
BEFORE INSERT OR UPDATE
ON Back
FOR EACH STATEMENT
EXECUTE PROCEDURE BackUpdate();

CREATE TABLE Comment (
  cid INT,
  uid VARCHAR(64),
  pid INT,
  content VARCHAR(512) NOT NULL,
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid, pid, cid)
);

CREATE OR REPLACE FUNCTION CommentUpdate()
RETURNS TRIGGER AS $$
BEGIN
RAISE NOTICE 'Comment Table Being Updated';
RETURN NULL;
END; $$
LANGUAGE PLPGSQL;

CREATE TRIGGER CommentTableTrigger
BEFORE INSERT OR UPDATE
ON Comment
FOR EACH STATEMENT
EXECUTE PROCEDURE CommentUpdate();
