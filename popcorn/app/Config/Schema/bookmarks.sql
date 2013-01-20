DROP TABLE IF EXISTS bookmarks;
CREATE TABLE bookmarks (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(256),
  url VARCHAR(1024) NOT NULL,
  local_path VARCHAR(512),
  archive VARCHAR(512),
  downloaded TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  downloaded_at DATETIME,
  created DATETIME,
  modified DATETIME,
  PRIMARY KEY (id),
  INDEX (user_id)
) ENGINE=InnoDB;
