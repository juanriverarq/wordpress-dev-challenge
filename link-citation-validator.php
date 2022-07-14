<?php

/**
 *
 * The plugin bootstrap file
 *
 * This file is responsible for starting the plugin using the main plugin class file.
 *
 * @since 1.0
 * @package Citalidator
 *
 * @wordpress-plugin
 * Plugin Name:     Citalidator
 * Description:     Este plugin permite crear citaciones en las entradas y a la vez validar enlaces.
 * Version:         1.0
 * Author:          Juan Rivera
 * Author URI:      https://www.example.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     Creador-de-citación-validador
 * Domain Path:     /lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

if ( ! class_exists( 'Citalidator' ) ) {

	/*
	 * main Citalidator class
	 *
	 * @class Citalidator
	 * @since 1.0
	 */
	class Citalidator {

		/*
		 * Citalidator plugin version
		 *
		 * @var string
		 */
		public $version = '1.0';

		/**
		 * The single instance of the class.
		 *
		 * @var Citalidator
		 * @since 1.0
		 */
		protected static $instance = null;

		/**
		 * Main Citalidator instance.
		 *
		 * @since 1.0
		 * @static
		 * @return Citalidator - main instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Citalidator class constructor.
		 */
		public function __construct() {
			$this->load_plugin_textdomain();
			$this->define_constants();
			$this->includes();
			$this->define_actions();
			$this->define_menus();

		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'plugin-name', false, basename( dirname( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Include required core files
		 */
		public function includes() {
			require_once __DIR__ . '/includes/register-citation.php';
			require_once __DIR__ . '/includes/display-citation.php';
			require_once __DIR__ . '/includes/save-citation.php';
			require_once __DIR__ . '/includes/shourtcode.php';
			require_once __DIR__ . '/includes/page.php';
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		/**
		 * Define Citalidator constants
		 */
		private function define_constants() {
			define( 'Citalidator_PLUGIN_FILE', __FILE__ );
			define( 'Citalidator_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'Citalidator_VERSION', $this->version );
			define( 'Citalidator_PATH', $this->plugin_path() );
		}

		/**
		 * Define Citalidator actions
		 */
		public function define_actions() {
            register_activation_hook( __FILE__, 'table_creation' );
            register_activation_hook( __FILE__, 'cron_activation' );
            register_deactivation_hook( __FILE__, 'cron_desactivation' );
		}

		/**
		 * Define Citalidator menus
		 */
		 
			public function define_menus() {
			require_once plugin_dir_path( __FILE__ ) . "/includes/menu.php";
		}
	}

	$Citalidator = new Citalidator();
}
