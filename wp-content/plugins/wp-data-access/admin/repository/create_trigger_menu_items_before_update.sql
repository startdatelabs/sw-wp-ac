CREATE TRIGGER {wp_prefix}{wpda_prefix}menu_items_before_update
BEFORE UPDATE
  ON {wp_prefix}{wpda_prefix}menu_items
FOR EACH ROW
  BEGIN

  IF CHAR_LENGTH(NEW.menu_name) < 1 OR
     CHAR_LENGTH(NEW.menu_table_name) < 1 OR
     CHAR_LENGTH(NEW.menu_capability) < 1
  THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Menu name, table name and capability must be entered';
  END IF;

  IF (SELECT NOT EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = NEW.menu_table_name))
  THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Invalid table name';
  END IF;

END;