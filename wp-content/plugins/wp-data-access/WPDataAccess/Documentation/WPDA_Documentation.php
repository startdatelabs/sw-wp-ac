<?php

namespace WPDataAccess\Documentation {

	use WPDataAccess\Utilities\WPDA_Repository;

	class WPDA_Documentation {

		// Menu slug of help page.
		const PAGE = 'wpda_help';

		// Actual help topic requested.
		// Template loaded: 'help_' + docid + '.tmpl' (sub folder Templates)
		protected $docid = '';

		protected $template_dir_name;

		public function __construct() {
			if ( isset( $_REQUEST['docid'] ) ) {
				$this->docid = sanitize_text_field( wp_unslash( $_REQUEST['docid'] ) ); // input var okay.
			}
			$this->template_dir_name = __DIR__ . '/Templates/help_';
		}

		public function show() {
			$wpda_repository = new WPDA_Repository();
			$wpda_repository->inform_user();
			?>
			<style>
				#container {
					display: table;
					width: 100%;
					padding-top: 20px;
				}
				#row  {
					display: table-row;
				}
				#left {
					display: table-cell;
					width: 25%;
				}
				#left a {
					padding-left: 15px;
					text-decoration: none;
				}
				#menu {
					background-color: #e5e5e5;
					border: solid 1px #ccc;
					border-radius: 5px;
					padding: 10px;
				}
				#right {
					display: table-cell;
					width: 75%;
					padding: 10px;
					padding-left: 20px;
				}
			</style>
			<div class="wrap">
				<h1>Plugin Help</h1>
				<div id="container">
					<div id="row">

						<div id="left">
							<div id="menu">
								<a href="?page=<?php echo self::PAGE; ?>&docid=whats_new" style="padding-left:0;">What's New?</a><br/>
								<br/>
								<strong>Getting Started</strong><br/>
								<ul>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=quick_tour">Quick Tour</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=known_limitations">Known Limitations</a></li>
								</ul>
								<strong>Data(base) Administration</strong><br/>
								<ul>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_explorer">Data Explorer</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=export_import">Export/Import</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_backup">Data Backup</a></li>
								</ul>
								<strong>Data Publication</strong><br/>
								<ul>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=short_code_usage">Short Code Usage</a></li>
								</ul>
								<strong>Data Apps</strong><br/>
								<ul>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_designer">Data Designer</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_projects">Data Projects - Manage Projects</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_projects_tables">Data Projects - Manage Table Options</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_projects_menus">Data Projects - Manage Dashboard Menus</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_projects_roles">Data Projects - User Roles and WHERE Clauses</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=data_projects_videos">Data Projects - Video Tutorials</a></li>
								</ul>
								<strong>Source Code</strong><br/>
								<ul>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=download_source_code">Download Source Code</a></li>
								</ul>
								<strong>Tutorials</strong><br/>
								<ul>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=demo_wpda_sas">Demo "School Administration System"</a></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=demo_wpda_sas_video">Demo "School Administration System" - Video Tutorial</a> <span style="color:red">(NEW)</span></li>
									<li><a href="?page=<?php echo self::PAGE; ?>&docid=add_menu_by_code">Use WP Data Access classes in your own PHP code</a></li>
								</ul>
							</div>
						</div>

						<div id="right">
							<?php
							if ( '' === $this->docid ) {
								include( $this->template_dir_name . 'quick_tour.tmpl' );
							} else {
								$template_file_name = $this->template_dir_name . $this->docid . '.tmpl';
								if ( file_exists( $template_file_name ) ) {
									include( $template_file_name );
								} else {
									?>
									Sorry, no help available for this topic!
									<?php
								}
							}
							?>
						</div>

					</div>
				</div>
			</div>
			<?php
		}

	}

}
