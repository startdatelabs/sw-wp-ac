CREATE TABLE {wp_prefix}wpdp_page{wpda_postfix}
( project_id mediumint(9) NOT NULL
, page_id mediumint(9) NOT NULL AUTO_INCREMENT
, add_to_menu enum('Yes','No') DEFAULT NULL
, page_name varchar(100) NOT NULL
, page_type enum('table','parent/child','static') NOT NULL
, page_table_name varchar(64) DEFAULT NULL
, page_mode enum('edit','view') NOT NULL
, page_allow_insert enum('yes','no') NOT NULL
, page_allow_delete enum('yes','no') NOT NULL
, page_content bigint(20) unsigned DEFAULT NULL
, page_title varchar(100) DEFAULT NULL
, page_subtitle varchar(100) DEFAULT NULL
, page_role varchar(100) DEFAULT NULL
, page_where varchar(4096) DEFAULT NULL
, page_sequence smallint(6) DEFAULT NULL
, PRIMARY KEY (page_id)
, UNIQUE KEY (project_id, page_name, page_role)
);