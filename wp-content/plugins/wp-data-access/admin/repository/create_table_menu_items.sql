CREATE TABLE {wp_prefix}{wpda_prefix}menu_items{wpda_postfix} (
  menu_id         mediumint(9) NOT NULL AUTO_INCREMENT,
  menu_name       VARCHAR(100) NOT NULL,
  menu_table_name VARCHAR(64)  NOT NULL,
  menu_capability VARCHAR(100) NOT NULL,
  menu_slug       VARCHAR(100),
  PRIMARY KEY (menu_id)
);