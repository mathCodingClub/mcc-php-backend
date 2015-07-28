CREATE DATABASE IF NOT EXISTS treTrial;

USE treTrial;

CREATE TABLE IF NOT EXISTS data(
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  code varchar(100) UNIQUE not null,    
  title varchar(100), 
  content text,
  created timestamp not null default CURRENT_TIMESTAMP,
  updated timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  INDEX codeIndex (code)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;
