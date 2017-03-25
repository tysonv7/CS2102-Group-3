CREATE TABLE Users (
  uid VARCHAR(64),
  name VARCHAR(64) NOT NULL,
  password VARCHAR(64) NOT NULL,
  PRIMARY KEY (uid)
);

CREATE TABLE Admin (
  uid VARCHAR(64),
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid)
);

CREATE TABLE Following (
  uid1 VARCHAR(64),
  uid2 VARCHAR(64),
  FOREIGN KEY (uid2) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (uid2) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid1, uid2)
);

CREATE TABLE Project (
  pid VARCHAR(64),
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
  pid VARCHAR(64),
  featureDate DATE NOT NULL,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (pid)
);

CREATE TABLE Start (
  uid VARCHAR(64),
  pid VARCHAR(64),
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid, pid)
);

CREATE TABLE Back (
  uid VARCHAR(64),
  pid VARCHAR(64),
  amount INT NOT NULL,
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid, pid)
);

CREATE TABLE Comment (
  cid VARCHAR(64),
  uid VARCHAR(64),
  pid VARCHAR(64),
  content VARCHAR(512) NOT NULL,
  FOREIGN KEY (uid) REFERENCES Users (uid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (pid) REFERENCES Project (pid)
    ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (uid, pid, cid)
);
