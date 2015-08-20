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

-- Uupdate 19.8.2015
-- ALTER TABLE users CHANGE role role ENUM('sysadmin','admin','editor','trainee','external');

CREATE TABLE IF NOT EXISTS sessions (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  user_id integer,
  session varchar(40),
  device text,
  time timestamp not null default CURRENT_TIMESTAMP,
  INDEX session_ (session),
  INDEX user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE 
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE TABLE IF NOT EXISTS tokens (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  user_id integer,
  token varchar(100),
  time timestamp not null default CURRENT_TIMESTAMP,  
  INDEX user (user_id),
  INDEX token_ (token)
  FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE 
)