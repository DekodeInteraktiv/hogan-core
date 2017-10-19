<?php
/**
 * Plugin Name: Hogan
 * Plugin URI: https://github.com/DekodeInteraktiv/hogan
 * Description: Modular Flexible Content System
 * Version: 1.0.0-dev
 * Author: Dekode
 * Author URI: https://dekode.no
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html

 * Text Domain: hogan
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
	$hogan = new Dekode\Hogan\Core( dirname( __FILE__ ) );
}
