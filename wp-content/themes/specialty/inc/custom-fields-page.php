<?php
add_action( 'admin_init', 'specialty_cpt_page_add_metaboxes' );
add_action( 'save_post', 'specialty_cpt_page_update_meta' );

if ( ! function_exists( 'specialty_cpt_page_add_metaboxes' ) ) :
	function specialty_cpt_page_add_metaboxes() {
		add_meta_box( 'specialty-page-layout', esc_html__( 'Layout', 'specialty' ), 'specialty_add_page_layout_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'specialty-tpl-job-listing-meta', esc_html__( 'Job Listing Options', 'specialty' ), 'specialty_add_page_job_listing_meta_box', 'page', 'normal', 'high' );
	}
endif;

if ( ! function_exists( 'specialty_cpt_page_update_meta' ) ) :
	function specialty_cpt_page_update_meta( $post_id ) {

		// Nonce verification is being done inside specialty_can_save_meta()
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! specialty_can_save_meta( 'page' ) ) {
			return;
		}

		specialty_sanitize_metabox_tab_hero( $post_id );

		update_post_meta( $post_id, 'specialty_job_listing_sidebar', specialty_sanitize_job_listing_layout_choices( $_POST['specialty_job_listing_sidebar'] ) );

		// phpcs:enable
	}
endif;

if ( ! function_exists( 'specialty_add_page_layout_meta_box' ) ) :
	function specialty_add_page_layout_meta_box( $object, $box ) {
		specialty_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			specialty_print_metabox_tab_hero( $object, $box );

		?></div><?php

	}
endif;

if ( ! function_exists( 'specialty_add_page_job_listing_meta_box' ) ) :
	function specialty_add_page_job_listing_meta_box( $object, $box ) {
		specialty_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			specialty_metabox_open_tab( '' );
				specialty_metabox_dropdown( 'specialty_job_listing_sidebar', specialty_get_job_listing_layout_choices(), esc_html__( 'Sidebar:', 'specialty' ) );
			specialty_metabox_close_tab();

		?></div><?php

		specialty_bind_metabox_to_page_template( 'specialty-tpl-job-listing-meta', 'template-listing-jobs.php', 'specialty_tpl_job_listing_metabox' );
	}
endif;
