### ChangeLog for WP Data Access

#### 2.0.14 /  2019-06-08

* Changed: Import from Data Explorer main page is always allowed (admin user)
* Added: Data Designer integrated with Data Explorer (alter table and indexes directly from Data Explorer) 
* Fixed: Cannot enter html characters in Simple Form text fields (support topic 11562559 - leouesb)
* Added: Export from Data Explorer table page to XML, JSON, Excel and CSV  (support topic 11565221 - rswebmaster)
* Fixed: Error on delete parent when parent has lookups defined
* Added: Reconcile button to Data Designer
* Added: (re)Create index button to Data Designer
* Added: Alter table button to Data Designer
* Added: Drop index button to Data Designer
* Added: Drop table button to Data Designer
* Added: Show alter table script button to Data Designer
* Added: Show create table script button to Data Designer
* Added: Allow to show/hide deleted columns (compared with database table)
* Added: Highlight new, deleted and modified columns in Data Designer
* Added: Listbox to Data Backup to enable viewing all scheduled WordPress jobs
* Added: Data Backup button to Data Explorer header
* Changed: Uniform layout and behaviour for all buttons and links in page titles 
* Changed: Import title and info text (checks if zip upload is allowed)  
* Fixed: Export to csv deletes double quotes in text

#### 2.0.13 / 2019-05-17

* Fixed: Database name containing minus character leads to query errors (support topic 11540179 - Prause)
* Added: Export tables from Data Explorer to SQL (with(out) WP prefix), XML, json, Excel, csv files (support topic 11533487 - rswebmaster)

#### 2.0.12 / 2019-05-14

* Updated: Plugin help pages
* Added: Video tutorial to install the demo app
* Fixed: Search on table with no search columns should show no rows
* Fixed: Cannot search on lookup items
* Fixed: Sorting on lookup columns is not possible (removed header link from table list)
* Added: Check if file_uploads = On before upload (disable file upload if file_uploads = Off) 
* Fixed: Not correctly jumping back to list table source page after "Add Existing" > "search"
* Fixed: Data Explorer main page shows all tables on show favourites only no favourites defined
* Fixed: Export and Data Backup fail when memory_limit is too small
* Added: Check file size against upload_max_filesize before uploading imnport file
* Changed: Import now using streams to better support large files
* Changed: Export and Data Backup now using streams to better support large files
* Added: Log table to "Manage Repository" and "System Info"
* Changed: Export procedure now writes seperate insert statement for every row
* Fixed: Export/import procedures non WP schema performed on WP schema
* Added: System info tab to improve and simplify plugin support and communication

#### 2.0.11 / 2019-04-30

* Fixed: After editing a data record user always returns to page 1 (support topic 11476140 - Hannes - Decentris)
* Fixed: Cannot add new page to project (support topic 11477423 - fendervr)
* Added: Drop logging table on uninstall
* Added: Possibility to save repository backup tables during a plugin update
* Changed: Simplified repository (re)creation to decrease the possibility of failure
* Fixed: Export files writes {wp_prefix}_ instead of {wp_prefix}
* Fixed: View only list tables should not allow delete bulk actions
* Fixed: Cannot search in list of values (search is performed on main list table)
* Fixed: Site blocked after unattended plugin update (support topic 11472418 - tjgorman) (patched version 2.0.10)
* Fixed: Class 'WPDataProjects\List_Table\WPDP_List_Columns_Cache' not found (patched version 2.0.10)
* Fixed: Plugin table array removed from table cache (patched version 2.0.10)

#### 2.0.10 / 2019-04-25

* Changed: Moved all security checks from menu preparation to page preparation
* Added: Data Backup now supports unattended (background/no browser) adhoc backups (support topic 11466155 - stevekatasi)
* Changed: Improved and simplified Data Backup procedure
* Fixed: Added WordPress database schema and plugin tables to cache (support topic 11461930 - stevekatasi)
* Fixed: Added cache to list column classes to increase database performance (support topic 11461930 - stevekatasi)
* Fixed: Optimized class WPDP_List_Table_Lookup due to bad performance issue (support topic 11461930 - stevekatasi)
* Fixed: Create table menu items fails for MySQL 5.6 and prior (support topic 11461174 - rswebmaster)
* Added: Demo project (app) WPDA_SAS - School Administration System
* Added: Code example how to use WP Data Access classes in PHP plugin code
* Fixed: Default and list-values imported without single quotes on Reverse Engineering (support topic 11423815 - Hannes - Decentris)
* Fixed: Data Projects export not working in FireFix (support topic 11429499 - Hannes - Decentris)
* Fixed: Submenus of data apps not shown correctly for roles null or empty string
* Changed: Export tables with variable wpdb prefix to support import into repository with different wpdb prefix
* Fixed: Set data type not handled correctly in the Data Designer (support topic 11423815 - Hannes - Decentris)
* Added: Explain how to define enum and list type in the Data Designer (support topic 11423815 - Hannes - Decentris)
* Fixed: Added latest version of WP_List_Table to project to reclaim navigation buttons
* Fixed: Submenus of data apps not shown correctly for roles other than administrator
* Fixed: WP table prefix not taken into account (support topic 11411195 - Hannes - Decentris)
* Fixed: Key column labels not displayed correctly in table list
* Added: A listbox is generated for lookup items in data entry forms  
* Added: It is now possible to add a lookup column to a table list 
* Added: Disable relationship and data entry form config for views and tables without a primary key (Data Projects)
* Added: Allow views and tables without a primary key to be used (Data Projects)
* Added: Allow to create relationships between tables and views (Data Projects)
* Added: Table type (TABLE,VIEW) to WPDA_Design_Table_Model (WPDP_Project_Design_Table_Model inherited)
* Fixed: Import script containing multiple SQL statements failed on Windows (using \r\n) 

#### 2.0.8 / 2019-02-05

* Added: Video tutorial to explain how to create many to many relationships in Data Projects
* Changed: Static content not correctly filtered
* Added: Make username accessible in where clause of project list tables
* Added: Where clause to project list tables to influence selection (parent only)
* Added: Support for MySQL set data type (listbox handling multiple selections)
* Added: Role (multiple) to data project pages to give non admin users access to data apps
* Changed: Content in list table wrapped (request from Enterprise Branding) 
* Changed: What's new message now shown on all plugin pages
* Changed: Dropbox path now updatable
* Changed: Add / at the end of the backup folder name if not entered

#### 2.0.7 / 2019-01-27

* Added: Data backup tool to automatically backup table data to a local folder or Dropbox folder

#### 2.0.6 / 2018-12-16

* Added: Check number max size and precision in data entry forms
* Added: "Add New" button for parent in parent-child pages
* Added: Show list of available tables in data entry form for project>pages
* Fixed: Data Explorer manage table tabs not working correctly with multiple windows 
* Changed: Allow to hide primary key columns in data entry forms
* Changed: Allow to hide primary key columns in table list
* Fixed: Hide columns not working in all data entry forms
* Fixed: Data Project table page: mode, title and subtitle not taken into account

#### 2.0.5 / 2018-12-14

* Removed /themes/smoothness/jquery-ui.css from plugin admin class (shortcode button not working)
* Added: New screenshots to WordPress Plugin Directory
* Fixed: Export not working when "ask for confirmation when starting export" in settings is checked
* Changed: Tabs in list table (table actions) not working in Internet Explorer
* Changed: Links in list table not working in Internet Explorer

#### 2.0.4 / 2018-12-11

* Changed: Plugin description in WordPress Plugin Directory
* Changed: Layout of the manage table window

#### 2.0.3 / 2018-12-05

* Added: Optimize table from Data Explorer > manage table > actions tab
* Added: Hint user if table optimization should be considered
* Changed: Data menus was moved to Data Projects > Manage Dashboard Menus
* Added: Columns data size, index size and overhead to Data Explorer main page
* Added: Hide columns on Data Explorer mainpage
* Changed: Changed to order of the tabs in the manage table/view window
* Changed: Replaced icon to manage table of view with standard WordPress listtable link
* Changed: Changed import button text and labels for better understanding of import functionality
* Added: Video tutorial to explain how to create one to many relationships in Data Projects

#### 2.0.2 / 2018-12-03

* Fixed: Removed subtitle from Data Designer and Data Menus list
* Added: What's new page to inform users about new features
* Added: First video tutorial to explain Data Projects tool

#### 2.0.1 / 2018-11-27

* Fixed: Null values not exported correctly
* Fixed: Do not allow to hide mandatory columns in data entry forms

#### 2.0.0 / 2018-11-09

* Added: Data Projects to plugin
  * Create WordPress Data Apps
  * Add app to dashboard menu
  * Supports static pages
  * Supports CRUD pages
  * Supports parent/child pages
* Added: Documentation to plugin menu
* Fixed: Repository activation error
* Stopped: Website redirected to WordPress Plugin Directory

#### 1.6.9 / 2018-03-20

* Fixed: Bulk actions not executed due to fix in 1.6.7 on favourites change
* Added: Show MySQL error when create table fails
* Changed: Prepared WPDA_Design_Table_Model to support transparent structures

#### 1.6.8 / 2018-03-17

* Changed: Added new screenshots
* Fixed: Missing check unique column names and index names
* Fixed: Delete index in Data Designer not working
* Changed: Default mode Data Designer changed to advanced

#### 1.6.7 / 2018-03-16

* Fixed: Switch to editing mode after create table/index in Data Designer
* Fixed: Prevent bulk selections being executed on favourites change
* Fixed: Multiple alerts on invalid bulk drop or truncate selection

#### 1.6.6 / 2018-03-16

* Added: Copy table (including/excluding data)
* Added: Rename table/view
* Changed: Simplified usage of table/view/index actions from Data Explorer

#### 1.6.5 / 2018-03-15

* Added: Drop index from Data Explorer

#### 1.6.4 / 2018-03-14

* Fixed: Column 'Unique?' on 'Indexes' tab of Data Explorer always showing 'No'

#### 1.6.3 / 2018-03-14

* Fixed: Create table not working

#### 1.6.2 / 2018-03-01

* Fixed: Action button issues
* Fixed: Ask for confirmation on bulk-drop and bulk-truncate
* Fixed: Schema issues

#### 1.6.1 / 2018-03-01

* Added: Allow ZIP file imports to support larger import files (uses ZipArchive)

#### 1.6.0 / 2018-02-15

* Added: Create tables in basic or advanced mode (switch between modes)
* Added: Allow data and database administration of other schemas
* Added: Import table(s) button to Data Explorer (allows multiple imports)

#### 1.5.2 / 2018-02-06

* Added: Check every request for plugin updates (compare db version with plugin version)  

#### 1.5.1 / 2018-02-03

* Added: Check #Rows ( perform count if #Rows < WPDA::OPTION_BE_INNODB_COUNT )

#### 1.5.0 / 2018-01-23

* Added: Engine field to Data Explorer
* Added: Number of records field to Data Explorer
* Added: Drop and bulk drop for views (accessible through icon in Data Explorer)
* Added: Bulk drop and bulk truncate for tables (accessible through icon in Data Explorer)
* Added: View table/view structure (accessible through icon in Data Explorer)
* Added: Option to backend settings to get default search value functionality (forget search value)
* Added: Support for parent detail navigation
* Added: Added argument 'allow_import' to WPDA_List_Table to hide import button
* Changed: Always show page 1 on new search 
* Changed: Improved layout Simple Form
* Changed: Hide button 'Back To List' in view mode
* Removed: Menu WP Data Tables (replaced by favourites menu)
* Fixed: Current page selector not working
* Fixed: Check max length for input (attribute maxlength)
* Fixed: On expanding favourites table name not shown
* Fixed: Remember search value after navigating to details
* Fixed: WPDA_List_Table::construct_where_clause() not respecting values already in $this->where 
* Fixed: Searching in favourites not working
* Fixed: Disable only form items in view mode
* Fixed: Argument 'show_view_link' has no effect
* Fixed: Argument 'allow_insert' has no effect
* Fixed: Back button in list table when called from data explorer or favourites

#### 1.2.1 / 2018-01-14

* Fixed: Skip empty index on create table
* Fixed: Data entry form should showing CURRENT_TIMESTAMP as default value
* Fixed: Bulk checkboxes shown without bulk actions (tables export disabled)
* Fixed: List table favourites not showing labels when empty

#### 1.2.0 / 2018-01-13

* Fixed: Recognize missing wp_wpda_table_design
* Fixed: Single file for every alter table stetement (wp_wpda_table_design) 
* Added: Add tables to favourites (WP Data Tables still in menu but will be removed soon)

#### 1.1.1 / 2018-01-13

* Fixed: Create table wp_wpda_table_design (older versions of mysql not supporting timestamp)
* Fixed: Hidden columns array returns false

#### 1.1.0 / 2018-01-09

* Added: Data Designer
    * Design tables and indexes
    * Create tables and indexes from design
* Added: Drop table (from list table)
* Added: Truncate table (from list table)
* Fixed: Recognize all WordPress tables (single and multisite)
* Fixed: Link 'export' not showing in Data Explorer

#### 1.0.0 / 2017-12-04

* Fixed: I can’t add table to menu (2017-12-29)
* Fixed: Activating the plugin affects styles on the front page (2017-12-29)
* Fixed: Sanitization error (2017-12-29)
* Initial commit
