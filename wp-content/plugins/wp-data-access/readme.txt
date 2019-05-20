=== WP Data Access ===
Contributors: peterschulznl
Tags: wp data access, database, tables, table, tools, manage, manager
Donate link: https://www.paypal.me/kpsch
Requires at least: 4.8.3
Tested up to: 5.2
Stable tag: 2.0.14
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Data Access is a WordPress data administration and publication tool that helps you to manage your Wordpress data and database.

== Description ==
WP Data Access helps you to manage your WordPress data and database from the WordPress dashboard and to publish table data on your website using shortcodes.

WP Data Access also allows you to create your own data driven WordPress apps that run in the WordPress dashboard. Easily build data entry forms that support standard CRUD as well as parent-child functionality and static pages.

## Features

### WordPress Dashboard

Perform database administration tasks like create, drop, rename, copy, truncate, insert, update, delete, export and import directly from the WordPress dashboard.

Perform data administration tasks like search, insert, update and delete actions directly from the WordPress dashboard.

Create data driven WordPress apps from the Data Projects tool. Build data entry forms that support standard CRUD as well as parent-child functionality and static pages.

#### Data Explorer
* Explore tables and views
* Edit table data (through data entry forms)
* View table, view and index structures
* Rename tables and views
* Copy tables
* Delete table data (selections)
* Truncate tables
* Drop tables, views and indexes
* Export tables (full table export as well as data selections)
* Import tables
* Optimize tables

#### Data Designer
* Reverse engineer tables and indexes
* Design tables and indexes
* Create tables and indexes from design

#### Data Projects
* Create data driven WordPress apps running in the WordPress dashboard
* Create WordPress dashboard menus and menu-items
* Add table based data entry forms to WordPress apps
* Add master-detail forms to WordPress apps
* Add static pages to WordPress apps
* Configure and style table lists, data entry forms and parent-child forms
* Add role based access control to WordPress apps

#### Data Menus
* Add list tables and data entry forms to your own WordPress dashboard menu

#### Data Backup
* Create data backup jobs that run in the background
* Create adhoc data backups that run in the background
* Allows to create multiple data backup jobs
* Select the tables to be backed up
* Save backup files on local or Dropbox folder
* Run data backups at specified intervals
* Define the number of backup files te be kept

#### Manage Plugin
* Manage plugin behaviour, style and security

### Website HTML tables

Publish table data on your website. Add shortcode or wizard based HTML tables to your website. Tables are dynamically build using jQuery DataTables. Multiple layouts supported. Allows styling with CSS. Provides pagination, searching and sorting functionality.

### Use Wp Data Access classes in your own PHP code
* Generate list tables based on the WP_List_Table class to support standard WordPress list table layout and user interaction
* Write your own code from WP Data Access classes
* Add menus for generated list tables to the WordPress dashboard, plugin or theme

## Plugin Links

- [Download plugin](https://wordpress.org/plugins/wp-data-access/)
- [Download Source Code](https://bitbucket.org/wpdataaccess/wp-data-access/src)
- [Changelog](https://bitbucket.org/wpdataaccess/wp-data-access/src/master/CHANGES.md)

## Documentation
Documentation was moved to the plugin and can now be accessed directly from the plugins menu (WP Data Access > Plugin Help).

Developers who are interested in using the plugins PHP classes can generate the API documentation as follows:
1) Start your command line interface
2) Move to the plugins root directory
3) Enter: composer update (this will install phpdocumentor and all its necessary dependencies)
4) Enter: php vendor/phpdocumentor/phpdocumentor/bin/phpdoc.php (this will generate the plugins API documentation in subfolder named docs)
5) Move to the docs subfolder and open the file index.html in your browser

## Notes
* The documentation is currently not up to date. Sorry! I have to get to that. I just like programming more... ;-)

== Installation ==
(1) Upload the WP Data Access plugin to your blog, (2) activate it and (3) navigate to the WP Data Access menu.
1, 2, 3 and you’re done!
