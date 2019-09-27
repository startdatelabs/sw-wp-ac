<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	/**
	 * Class WPDA_Upgrade
	 *
	 * Add code to be performed on upgrades in this class.
	 *
	 * Add a method for an upgrade (or downgrade) with the following name:
	 * + upgrade_{$version-from}_{$version-to}
	 *
	 * $version-from indicates the version number of the plugin. $version-to indicates the new version of the plugin.
	 *
	 * For example:
	 * + upgrade_1.0.0_1.5.0 >>> upgrade from version 1.0.0 to 1.5.0
	 * + upgrade_1.5.0_2.0.0 >>> upgrade from version 1.5.0 to 2.0.0
	 *
	 * Use the same naming convention to add downgrade code, for example:
	 * + upgrade_2.0.0_1.5.0 >>> downgrade from version 2.0.0 to 1.5.0
	 * + upgrade_1.5.0_1.0.0 >>> downgrade from version 1.5.0 to 1.0.0
	 *
	 * Plugin class WP_Data_Access_Switch will find the appropriate method.
	 *
	 * @package WPDataAccess\Utilities
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Upgrade {

	}

}
