CREATE TABLE IF NOT EXISTS users (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  username varchar(20) unique,
  givenName varchar(20),
  familyName varchar(20),
  email varchar(40),
  passwordHash varchar(80),  
  role enum('sysadmin','admin','editor','trainee','external'), -- access control levels to build services upon
  INDEX user (username)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

-- Update: 17.8.2015
-- ALTER TABLE users CHANGE email email varchar(40);

CREATE TABLE IF NOT EXISTS sessions (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  user_id integer,
  session varchar(40),
  device text,
  time timestamp not null default CURRENT_TIMESTAMP,
  INDEX sessions_sessions (session),
  INDEX user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE 
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;
