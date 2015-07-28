CREATE TABLE IF NOT EXISTS news (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  time timestamp not null default CURRENT_TIMESTAMP,
  edited timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  published boolean not null default true,
  ingress text,
  title text,
  contents text  
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE TABLE IF NOT EXISTS news_comments (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  news_id integer,
  time timestamp not null default CURRENT_TIMESTAMP,  
  contents text,
  name varchar(20),
  email varchar(50),
  ip varchar(15),
  reply text
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE INDEX news_comments_news_id on news_comments (news_id);
