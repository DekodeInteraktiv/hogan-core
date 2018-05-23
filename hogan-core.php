<?php
/**
 * Plugin Name: Hogan
 * Plugin URI: https://github.com/DekodeInteraktiv/hogan-core
 * GitHub Plugin URI: https://github.com/DekodeInteraktiv/hogan-core
 * Description: Modular Flexible Content System for ACF Pro
 * Version: 1.3.1
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
namespace Dekode\Hogan;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HOGAN_CORE_VERSION', '1.3.1' );
define( 'HOGAN_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'HOGAN_CORE_DIR', dirname( plugin_basename( __FILE__ ) ) );
define( 'HOGAN_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'HOGAN_CORE_PHP_REQUIRED_VERSION', '7' );

/*
 * Check the PHP version, if it's not a supported version, return without running
 * any more code as the user will not be able to use Hogan.
 */
if ( version_compare( phpversion(), HOGAN_CORE_PHP_REQUIRED_VERSION, '<' ) ) {
	add_action( 'admin_notices', function() {
		$screen = get_current_screen();

		// Only display message on the plugin screen.
		if ( 'plugins' !== $screen->id ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<?php
			printf( '<p><strong>%s</strong> &#151; %s</p>',
				esc_html__( 'Hogan Disabled', 'hogan-core' ),
				esc_html__( 'You are running an unsupported version of PHP.', 'hogan-core' )
			);

			/* translators: %s: required PHP version */
			printf( '<p>' . esc_html__( 'Hogan requires PHP Version %s, please upgrade to use Hogan.', 'hogan-core' ) . '</p>',
				esc_html( HOGAN_CORE_PHP_REQUIRED_VERSION )
			);
			?>
		</div>
		<?php
	} );

	// Bail out now so no additional code is run.
	return;
}

require_once 'includes/class-module.php';
require_once 'includes/class-core.php';
require_once 'includes/helper-functions.php';
require_once 'includes/hogan-deprecated.php';
Core::get_instance();
