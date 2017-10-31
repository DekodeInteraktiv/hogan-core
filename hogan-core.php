<?php
/**
 * Plugin Name: Hogan
 * Plugin URI: https://github.com/DekodeInteraktiv/hogan-core
 * Description: Modular Flexible Content System for ACF Pro
 * Version: 1.0.0-dev
 * Author: Dekode
 * Author URI: https://dekode.no
 * License: GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

 * Text Domain: hogan-core
 * Domain Path: /languages/
 *
 * @package Hogan
 * @author Dekode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'includes/class-module.php';
require_once 'includes/class-core.php';
require_once 'includes/helper-functions.php';

global $hogan;

if ( ! isset( $hogan ) ) {

	$_dir = dirname( plugin_basename( __FILE__ ) );
	$_url = plugin_dir_url( __FILE__ );

	$hogan = new Dekode\Hogan\Core( $_dir, $_url );
}
