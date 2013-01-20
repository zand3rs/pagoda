DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(128) NOT NULL,
  mobile VARCHAR(32),
  pin_code VARCHAR(16),
  pin_expiry DATETIME,
  mobile_status ENUM('UNVERIFIED','VERIFIED') NOT NULL DEFAULT 'UNVERIFIED',
  date_registered TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  auth_code VARCHAR(256),
  access_token VARCHAR(256),
  refresh_token VARCHAR(256),
  token_type VARCHAR(256),
  token_expiry INT(6),
  created DATETIME,
  modified DATETIME,
  PRIMARY KEY (id),
  UNIQUE KEY email_uk (email),
  UNIQUE KEY mobile_uk (mobile)
) ENGINE=InnoDB;
