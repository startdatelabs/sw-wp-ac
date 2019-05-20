<?php

namespace WPDataAccess\Utilities {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;

	class WPDA_Favourites {

		const FAVOURITES_OPTION_NAME = 'wpda_favourites';

		protected $schema_name = null;

		protected $table_name = null;

		public function __construct() {

			if ( isset( $_REQUEST['schema_name'] ) && isset( $_REQUEST['table_name'] ) ) {
				$schema_name = sanitize_text_field( wp_unslash( $_REQUEST['schema_name'] ) ); // input var okay.
				$table_name  = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.
				$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $schema_name, $table_name );
				if ( $wpda_dictionary_exists->table_exists() ) {
					$this->schema_name = $schema_name;
					$this->table_name  = $table_name;
				}
			}

		}

		public function add() {

			if ( null === $this->table_name ) {
				echo '0'; // Failed.
				return;
			}

			$option_value = get_option( self::FAVOURITES_OPTION_NAME );

			if ( false === $option_value ) {
				$favourites_array = [];
			} else {
				$favourites_array = $option_value;
			}

			if ( ! isset( $favourites_array[ $this->table_name ] ) ) {
				$favourites_array[$this->table_name] = $this->table_name;
				update_option( self::FAVOURITES_OPTION_NAME, $favourites_array );
				echo '1';
				return;
			}

			echo '0'; // Failed.

		}

		public function rem() {

			if ( null === $this->table_name ) {
				echo '0'; // Failed.
				return;
			}
			$option_value = get_option( self::FAVOURITES_OPTION_NAME );

			if ( false !== $option_value ) {
				$favourites_array = $option_value;
				if ( isset( $favourites_array[ $this->table_name ] ) ) {
					unset( $favourites_array[ $this->table_name ] );
					update_option( self::FAVOURITES_OPTION_NAME, $favourites_array );
					echo '1';
					return;
				}
			}

			echo '0'; // Failed.

		}

	}

}