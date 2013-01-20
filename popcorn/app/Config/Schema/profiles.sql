CREATE TABLE profiles (
  id int(11) NOT NULL AUTO_INCREMENT,
  email varchar(64) NOT NULL,
  mobile varchar(32),
  pin_code varchar(16),
  pin_expiry datetime,
  mobile_status enum('UNVERIFIED','VERIFIED') NOT NULL DEFAULT 'UNVERIFIED',
  date_registered timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY email_uk (email),
  UNIQUE KEY mobile_uk (mobile)
);

CREATE TABLE gmail_token (
  id int(11) NOT NULL AUTO_INCREMENT,
  profile_id varchar(255),
  gmail_id varchar(60),
  email varchar(64),
  access_token varchar(255),
  status enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY access_token (access_token)
);


