<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/yeongkongpoh
 * @since      1.0.0
 *
 * @package    Searchable_Archives
 * @subpackage Searchable_Archives/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Searchable_Archives
 * @subpackage Searchable_Archives/includes
 * @author     Gerald Yeong <gnyeong@gmail.com>
 */
class Searchable_Archives_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'searchable-archives',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
