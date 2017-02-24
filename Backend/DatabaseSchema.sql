CREATE TABLE "user"( 
id VARCHAR(64) PRIMARY KEY,
name VARCHAR(64) NOT NULL, 
password VARCHAR(64) NOT NULL);

CREATE TABLE project (
id VARCHAR(64) PRIMARY KEY,
title VARCHAR(64) NOT NULL,
startDate DATE NOT NULL,
duration INT NOT NULL,
category VARCHAR(64),
fundNeeded INT NOT NULL);

CREATE TABLE "create" (
creatorId VARCHAR(64) REFERENCES "user" (id),
projectId VARCHAR(64) REFERENCES project (id));

CREATE TABLE back (
backerId VARCHAR(64) REFERENCES "user" (id),
projectId VARCHAR(64) REFERENCES project (id), 
amount INT NOT NULL);
