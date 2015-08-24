CREATE TABLE IF NOT EXISTS structure (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  namespace varchar(100),
  code varchar(100) UNIQUE not null,    
  title varchar(100), 
  content text,
  created timestamp not null default CURRENT_TIMESTAMP,
  updated timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  INDEX namespace_ (namespace)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;
