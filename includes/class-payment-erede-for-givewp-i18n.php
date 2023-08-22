<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
class Payment_Erede_For_Givewp_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'payment-erede-for-givewp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
