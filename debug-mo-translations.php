<?php
/**
 * Plugin Name:       Debug MO Translations
 * Description:       Get translation data: language, files, possible problems.
 * Plugin URI:        https://github.com/closemarketing/debug-mo-translations
 * Version:           1.2
 * Requires at least: 4.6
 * Author:            closemarketing
 * Author URI:        https://www.closemarketing.es/
 * Licence:           GPL 2
 * License URI:       http://opensource.org/licenses/GPL-2.0
 * Text Domain:       debug-mo-translations
 */

/**
 * Main controller for this plugin.
 *
 * @author toscho
 */
class Debug_MO_Translations_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'setup' ), -1 );
	}

	/**
	 * Create objects and register callbacks.
	 *
	 * @wp-hook plugins_loaded
	 * @return  void
	 */
	public function setup() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$user = wp_get_current_user();
		if ( ! ( current_user_can( 'update_core' ) || $user->allcaps['update_core'] ) ) {
			return;
		}

		$logger = new Debug_MO_Translations_Logger();
		$output = new Debug_MO_Translations_Output( $logger );

		add_filter(
			'override_load_textdomain',
			array ( $logger, 'log_file_load' ),
			10,
			3
		);

		if ( is_admin() ) {
			/* Print debug in admin. */
			add_action( 'in_admin_footer', array ( $output, 'show' ), 0 );
		} else {
			/* Print debug in frontend. */
			add_action( 'wp_footer', array ( $output, 'show' ), 0 );
		}

	}
}

/**
 * Logger. Collects information about the load attempts.
 *
 * @author toscho
 */
class Debug_MO_Translations_Logger {

	/**
	 * List of log entries.
	 *
	 * @type array
	 */
	protected $log = array();

	/**
	 * Store log data.
	 *
	 * @wp-hook override_load_textdomain
	 * @param   bool $false FALSE, passed though
	 * @param   string $domain Text domain
	 * @param   string $mofile Path to file.
	 * @return  bool
	 */
	public function log_file_load( $false, $domain, $mofile ) {

		// DEBUG_BACKTRACE_IGNORE_ARGS is available since 5.3.6
		if ( version_compare(PHP_VERSION, '5.3.6') >= 0 )
			$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		else
			$trace = debug_backtrace();

		$this->log[] = array (
			'caller' => $trace[ 4 ], // entry 4 is the calling file
			'domain' => $domain,
			'mofile' => $mofile,
			'found'  => file_exists( $mofile ) ? round( filesize( $mofile ) / 1024, 2 ): FALSE
		);

		return $false;
	}

	/**
	 * Return complete log.
	 *
	 * Used by the model.
	 *
	 * @return array
	 */
	public function get_log() {
		return $this->log;
	}
}

/**
 * Print a pretty output.
 *
 * @author toscho
 */
class Debug_MO_Translations_Output {

	/**
	 * The data model
	 *
	 * @type Debug_MO_Translations_Logger
	 */
	protected $logger;

	/**
	 * Constructor.
	 *
	 * @param Debug_MO_Translations_Logger $logger
	 */
	public function __construct( Debug_MO_Translations_Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Create and print the output.
	 *
	 * @wp-hook shutdown
	 * @return  void
	 */
	public function show() {

		list( $name, $version )
			= get_file_data( __FILE__, array ( 'Plugin Name', 'Version' ) );

		$log = $this->get_log();

		?>
		<style>
			#wpfooter {
				position: relative !important;
			}
			.debug-mo-translations {
				margin-right: 0;
			}
			.debug-mo-translations .notice {
				padding: 1em 1.5em;
			}
			.debug-mo-translations table {
				margin: 1em 0;
			}
			.debug-mo-translations table > tbody > tr > :nth-child(1) {
				width: 1em;
				white-space: nowrap;
				font-weight: 600;
			}
		</style>
		<div class="wrap debug-mo-translations">
			<div class="notice inline is-dismissible">
				<h3><?php echo esc_html( $name ); ?></h3>
				<p>
					<?php
					printf(
						/* translators: %s: Version number. */
						esc_html__( 'Version: %s', 'debug-mo-translations' ),
						$version
					);
					?>
				</p>
				<p>
					<?php
					printf(
						/* translators: %s: Locale code. */
						esc_html__( 'Locale: %s', 'debug-mo-translations' ),
						'<code>' . get_locale() . '</code>'
					);
					?>
				</p>

				<?php
				/* Print log. */
				echo implode( $log );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get log data from model.
	 *
	 * @return array
	 */
	protected function get_log() {

		$logs = $this->logger->get_log();

		if ( empty ( $logs ) ) {
			return array(
				'<p>' . esc_html__( 'No MO file loaded or logged.', 'debug-mo-translations') . '</p>',
			);
		}

		$out = array ();

		foreach ( $logs as $log ) {
			$out[] = $this->get_formatted_log( $log );
		}

		return $out;
	}

	/**
	 * Prettify a log entry
	 *
	 * @param  array $log
	 * @return string
	 */
	protected function get_formatted_log( Array $log ) {

		if ( ! isset( $log[ 'caller' ][ 'file' ]) ) return;

		$result = sprintf(
			'<table class="widefat striped">
				<tbody>
					<tr>
						<td>' . esc_html__( 'Domain:', 'debug-mo-translations' ) . '</td>
						<td><code>%1$s</code></td>
					</tr>
					<tr>
						<td>' . esc_html__( 'File:', 'debug-mo-translations' ) . '</td>
						<td>%2$s %3$s</td>
					</tr>
					<tr>
						<td>' . esc_html__( 'Called in:', 'debug-mo-translations' ) . '</td>
						<td>%4$s %5$s <code>%6$s()</code></td>
					</tr>
				</tbody>
			</table>',
			$log[ 'domain' ],
			$log[ 'mofile' ],
			$log[ 'found' ] ? sprintf(
				/* translators: %s: Kilobytes amount. */
				esc_html__( '(%s KB)', 'debug-mo-translations' ),
				$log[ 'found' ],
			) : '<b>' . esc_html__( '(Not found)', 'debug-mo-translations' ) . '</b>',
			$log[ 'caller' ][ 'file' ],
			sprintf(
				/* translators: %s: Line number. */
				esc_html__( '(Line %s)', 'debug-mo-translations' ),
				$log[ 'caller' ][ 'line' ],
			),
			$log[ 'caller' ][ 'function' ]
		);

		return $result;

	}
}

new Debug_MO_Translations_Controller;