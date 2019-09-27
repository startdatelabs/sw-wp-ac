CREATE TABLE {wp_prefix}{wpda_prefix}table_design{wpda_postfix} (
wpda_table_name		VARCHAR(64)	NOT NULL,
wpda_table_design	TEXT		NOT NULL,
wpda_date_created	TIMESTAMP   NULL,
wpda_last_updated	TIMESTAMP   NULL,
PRIMARY KEY (wpda_table_name)
);