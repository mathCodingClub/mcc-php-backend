CREATE DATABASE IF NOT EXISTS treTrial;

USE treTrial;

CREATE TABLE IF NOT EXISTS events (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  name varchar(100) UNIQUE not null,    
  description text,
  isPublic bool not null default false,
  startTime timestamp not null default CURRENT_TIMESTAMP, 
  endTime timestamp not null default CURRENT_TIMESTAMP,
  signupStart timestamp not null default CURRENT_TIMESTAMP,
  signupEnd timestamp not null default CURRENT_TIMESTAMP,
  INDEX time (startTime)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE TABLE IF NOT EXISTS events_riders (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,
  givenName varchar(40),
  familyName varchar(40), 
  INDEX name (familyName)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE TABLE IF NOT EXISTS events_categories (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,  
  event_id INTEGER,
  category varchar(20),
  INDEX event (event_id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON UPDATE CASCADE ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE TABLE IF NOT EXISTS events_competitions (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,  
  category_id integer,
  competition varchar(40),  
  startTime timestamp not null default CURRENT_TIMESTAMP, 
  ridingTime integer, -- in seconds
  sections text, -- json array of sections that are included like [1,2,4,5,6,10]
  laps integer,  
  INDEX category (category_id),
  FOREIGN KEY (category_id) REFERENCES events_categories(id) ON UPDATE CASCADE ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

CREATE TABLE IF NOT EXISTS events_signups (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY, 
  category_id integer,
  givenName varchar(40),
  familyName varchar(40),  
  email varchar(50),
  time timestamp not null default CURRENT_TIMESTAMP,
  mobile varchar(20),
  notes text,
  INDEX category (category_id),
  FOREIGN KEY (category_id) REFERENCES events_categories(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS events_participations (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,  
  rider_id integer,
  competition_id integer,
  signup_id integer,
  handicap integer not null default 0,
  compNumber integer not null default 0,
  startTime timestamp, 
  ridingTime integer not null default 0, -- seconds
  penalties integer not null default 0,
  penaltiesExtra integer not null default 0,
  position integer not null default 0, -- compute positions with manual trigger
  notes text,  
  INDEX signup (signup_id),
  FOREIGN KEY (signup_id) REFERENCES events_signups(id) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX rider (rider_id),
  FOREIGN KEY (rider_id) REFERENCES events_riders(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX category (competition_id),
  FOREIGN KEY (competition_id) REFERENCES events_competitions(id) ON UPDATE CASCADE ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;


CREATE TABLE IF NOT EXISTS events_results (
  id INTEGER AUTO_INCREMENT UNIQUE PRIMARY KEY,    
  participation_id integer,
  section integer,
  lap integer,
  penaltyPoints integer,  
  notes text,
  INDEX participation (participation_id),
  INDEX result (participation_id, lap, section),
  FOREIGN KEY (participation_id) REFERENCES events_participations(id) ON UPDATE CASCADE ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;
