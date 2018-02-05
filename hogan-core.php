<?php
/**
 * Plugin Name: Hogan
 * Plugin URI: https://github.com/DekodeInteraktiv/hogan-core
 * GitHub Plugin URI: https://github.com/DekodeInteraktiv/hogan-core
 * Description: Modular Flexible Content System for ACF Pro
 * Version: 1.0.16
 * Author: Dekode
 * Author URI: https://dekode.no
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

 * Text Domain: hogan-core
 * Domain Path: /languages/
 *
 * @package Hogan
 * @author Dekode
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HOGAN_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'HOGAN_CORE_DIR', dirname( plugin_basename( __FILE__ ) ) );
define( 'HOGAN_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once 'includes/class-module.php';
require_once 'includes/class-core.php';
require_once 'includes/helper-functions.php';

\Dekode\Hogan\Core::get_instance( HOGAN_CORE_DIR, HOGAN_CORE_URL );
