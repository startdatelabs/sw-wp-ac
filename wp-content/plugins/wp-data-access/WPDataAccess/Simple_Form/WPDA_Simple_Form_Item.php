<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Simple_Form_Item
	 *
	 * Simple forms consist of items. Items correspond with table columns. Basically an item is generated for every
	 * table column in the base table.
	 *
	 * It's possible to add dummy columns. Values for dummy columns however are lost when data is saved.
	 *
	 * Check out {@see WPDA_Simple_Form} to see how to use simple form items.
	 *
	 * @package WPDataAccess\Simple_Form
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Simple_Form_Item {

		/**
		 * Database column name
		 *
		 * @var string
		 */
		protected $item_name;

		/**
		 * MySQL data type
		 *
		 * @var string
		 */
		protected $data_type;

		/**
		 * Item label
		 *
		 * @var string
		 */
		protected $item_label;

		/**
		 * Current column value in the database
		 *
		 * @var mixed
		 */
		protected $item_value;

		/**
		 * Default value
		 *
		 * @var mixed
		 */
		protected $item_default_value;

		/**
		 * Database column specific info
		 *
		 * Like auto_increment, on update, etc
		 *
		 * @var string
		 */
		protected $item_extra;

		/**
		 * Enum values for column or empty
		 *
		 * @var array
		 */
		protected $item_enum;

        /**
         * Enum options for column or empty
         *
         * @var array
         */
        protected $item_enum_options;

        /**
		 * Database column type
		 *
		 * Column type offers more info than data type, like column length or values for enum types.
		 *
		 * @var string
		 */
		protected $column_type;

		/**
		 * Array of events
		 *
		 * Add event to item for example: ["onclick" => "check_item_value()"]
		 *
		 * @var array
		 */
		protected $item_event;

		/**
		 * Item specific Javascript code
		 *
		 * Code is added to the end of the form.
		 *
		 * @var string
		 */
		protected $item_js;

		/**
		 * Show item icon (data type)
		 *
		 * TRUE = icon is shown after item, FALSE = hide icon (default FALSE)
		 *
		 * @var boolean
		 */
		protected $item_hide_icon;

		/**
		 * Item CSS class
		 *
		 * @var string
		 */
		protected $item_class;


        /**
         * TRUE = item not shown, FALSE = item shown
         *
         * @var boolean
         */
        protected $hide_item;

		/**
		 * TRUE = null values are allowed, FALSE = no null values allowed
		 *
		 * @var boolean
		 */
        protected $is_nullable;

		/**
		 * WPDA_Simple_Form_Item constructor
		 *
		 * Declare item with all its properties.
		 *
		 * @since   1.0.0
		 *
		 * @param array $args [
		 *
		 * 'item_name'          => item name
		 *
		 * 'data_type'          => data type
		 *
		 * 'item_label'         => label
		 *
		 * 'item_value'         => value (in database)
		 *
		 * 'item_default_value' => default value
		 *
		 * 'item_extra'         => check column extra in information_schema.columns
		 *
		 * 'item_enum'          => enum (if applicable)
		 *
         * 'item_enum_options'  => enum options (if applicable)
         *
		 * 'column_type'        => type
		 *
		 * 'item_event'         => JS event(s)
		 *
		 * 'item_js'            => JS code (global)
		 *
		 * 'item_hide_icon'     => icon (showing data type)
		 *
		 * 'item_class'         => css class
         *
         * 'hide_item'          => item visibility
		 *
		 * 'is_nullable'        => allow null values?
		 *
		 * ].
		 */
		public function __construct( $args = [] ) {

			$args = wp_parse_args(
				$args, [
					'item_name'          => '',
					'data_type'          => '',
					'item_label'         => '',
					'item_value'         => null,
					'item_default_value' => null,
					'item_extra'         => '',
					'item_enum'          => '',
                    'item_enum_options'  => '',
					'column_type'        => '',
					'item_event'         => '',
					'item_js'            => '',
					'item_hide_icon'     => false,
					'item_class'         => '',
                    'hide_item'          => false,
					'is_nullable'		 => null,
				]
			);

			if ( '' === $args['item_name'] || '' === $args['data_type'] ) {
				// Without an item name and/or data type it makes no sense to continue.
				wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			$this->item_name          = $args['item_name'];
			$this->data_type          = WPDA::get_type( $args['data_type'] );
			$this->item_label         = $args['item_label'];
			$this->item_value         = $args['item_value'];
			if ( 'CURRENT_TIMESTAMP' !== $args['item_default_value'] ) {
				$this->item_default_value = $args['item_default_value'];
			}
			$this->item_extra         = $args['item_extra'];
            $this->item_enum          = '';
            $this->item_enum_options  = '';
			if ( 'enum' === $this->data_type ) {
				$this->item_enum = explode(
					',',
					str_replace(
						'\'',
						'',
						substr( substr( $args['item_enum'], 5 ), 0, -1 )
					)
				);
			}
			if ( 'set' === $this->data_type ) {
				$this->item_enum = explode(
					',',
					str_replace(
						'\'',
						'',
						substr( substr( $args['item_enum'], 4 ), 0, -1 )
					)
				);
			}
			$this->column_type    = $args['column_type'];
			$this->item_event     = $args['item_event'];
			$this->item_js        = $args['item_js'];
			$this->item_hide_icon = $args['item_hide_icon'];
			$this->item_class     = $args['item_class'];
            $this->hide_item      = $args['hide_item'];
            $this->is_nullable    = $args['is_nullable'];

		}

		/**
		 * Het item name
		 *
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function get_item_name() {

			return $this->item_name;

		}

		/**
		 * Get item data type
		 *
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function get_data_type() {

			return $this->data_type;

		}

		/**
		 * Get item label
		 *
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function get_item_label() {

			return $this->item_label;

		}

		/**
		 * Get item value
		 *
		 * @since   1.0.0
		 *
		 * @return mixed
		 */
		public function get_item_value() {

			return $this->item_value;

		}

		/**
		 * Get item default value
		 *
		 * @since   1.0.0
		 *
		 * @return mixed
		 */
		public function get_item_default_value() {

			return $this->item_default_value;

		}

		/**
		 * Get item 'extra' info
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_Simple_Form_Item::$item_extra
		 *
		 * @return mixed
		 */
		public function get_item_extra() {

			return $this->item_extra;

		}

		/**
		 * Get enum values or empty
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_item_enum() {

			return $this->item_enum;

		}

        /**
         * Get enum options or empty
         *
         * @since   1.6.9
         *
         * @return array
         */
        public function get_item_enum_options() {

            return $this->item_enum_options;

        }

        /**
		 * Get column type
		 *
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function get_column_type() {

			return $this->column_type;

		}

		/**
		 * Get item event
		 *
		 * @since   1.0.0
		 *
		 * @return String
		 */
		public function get_item_event() {

			return $this->item_event;

		}

		/**
		 * Get item Javascript code
		 *
		 * @since   1.0.0
		 *
		 * @return mixed
		 */
		public function get_item_js() {

			return $this->item_js;

		}

		/**
		 * Hide icon?
		 *
		 * @since   1.0.0
		 *
		 * @return boolean
		 */
		public function get_item_hide_icon() {

			return $this->item_hide_icon;

		}

		/**
		 * Get item CSS class
		 *
		 * @since   1.0.0
		 *
		 * @return string
		 */
		public function get_item_class() {

			return $this->item_class;

		}

        /**
         * Get item visibility
         *
         * @since   1.6.9
         *
         * @return boolean
         */
		public function get_hide_item() {

		    return $this->hide_item;

        }

		/**
		 * Null values allowed?
		 *
		 * @since   2.0.0
		 *
		 * @return boolean
		 */
        public function is_nullable() {

			return $this->is_nullable;

		}

        /**
         * Set item default value
         *
         * @since   1.6.2
         *
         * @return mixed
         */
        public function set_item_default_value( $item_default_value ) {

            $this->item_default_value = $item_default_value;

        }

        /**
         * Set item CSS class
         *
         * @since   1.6.2
         *
         * @return string
         */
        public function set_item_class( $item_class ) {

            $this->item_class = $item_class;

        }

        /**
         * Set item visibility
         *
         * @since   1.6.9
         *
         * @return string
         */
        public function set_hide_item( $hide_item ) {

            $this->hide_item = $hide_item;

        }

        /**
         * Set item js code
         *
         * @since   1.6.9
         *
         * @return string
         */
        public function set_item_js( $item_js ) {

            $this->item_js = $item_js;

        }

        /**
         * Set item enum
         *
         * @since   1.6.9
         *
         * @return string
         */
        public function set_enum( $item_enum ) {

            $this->item_enum = $item_enum;

        }

        /**
         * Set item enum options
         *
         * @since   1.6.9
         *
         * @return string
         */
        public function set_enum_options( $item_enum_options ) {

            $this->item_enum_options = $item_enum_options;

        }

        /**
         * Set item data_type
         *
         * @since   1.6.9
         *
         * @return string
         */
        public function set_data_type( $data_type ) {

            $this->data_type = $data_type;

        }

		/**
		 * Set item visibility
		 *
		 * @since   2.0.8
		 *
		 * @return string
		 */
		public function set_item_hide_icon( $item_hide_icon ) {

			$this->item_hide_icon = $item_hide_icon;

		}

    }

}
