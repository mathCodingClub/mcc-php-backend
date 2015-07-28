CREATE TABLE IF NOT EXISTS users (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  username varchar(20) unique,
  givenName varchar(20),
  familyName varchar(20),
  email varchar(20),
  passwordHash varchar(80),  
  role enum('admin','editor','trainee')
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE INDEX users_username on users (username);

CREATE TABLE IF NOT EXISTS sessions (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  user_id integer,
  session varchar(40),
  device text,
  time timestamp not null default CURRENT_TIMESTAMP
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE INDEX sessions_session on sessions (session);
