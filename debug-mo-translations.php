<?php
/**
 * Plugin Name: Debug MO Translations
 * Description: Get translation data: language, files, possible problems.
 * Plugin URI:  https://github.com/closemarketing/debug-mo-translations
 * Version:     1.0
 * Author:      closemarketing
 * Author URI:  https://www.closemarketing.es/
 * Licence:     GPL 2
 * License URI: http://opensource.org/licenses/GPL-2.0
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

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;

		if ( ! current_user_can( 'update_core' ) )
			return;

		$logger = new Debug_MO_Translations_Logger();
		$output = new Debug_MO_Translations_Output( $logger );

		add_filter(
			'override_load_textdomain',
			array ( $logger, 'log_file_load' ),
			10,
			3
		);

		add_action( 'shutdown', array ( $output, 'show' ), 0 );
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

		$data = array (
			"<b>$name (Version $version)</b>\n",
			"Locale: " . esc_html( get_locale() ) . "\n"
		);
		$data += $this->get_log();

		print '<div id="wpcontent"><pre>' . join( "\n", $data ) . '</pre></div>';
	}

	/**
	 * Get log data from model.
	 *
	 * @return array
	 */
	protected function get_log() {

		$logs = $this->logger->get_log();

		if ( empty ( $logs ) )
			return array ( 'No MO file loaded or logged.' );

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

        if(!isset($log[ 'caller' ][ 'file' ]) ) return '';

		return sprintf( '
Domain:    %1$s
File:      %2$s (%3$s)
Called in: %4$s line %5$s %6$s',
			$log[ 'domain' ],
			$log[ 'mofile' ],
			$log[ 'found' ] ? $log[ 'found' ] . 'kb' : '<b>not found</b>',
			$log[ 'caller' ][ 'file' ],
			$log[ 'caller' ][ 'line' ],
			$log[ 'caller' ][ 'function' ]
		);

	}
}

new Debug_MO_Translations_Controller;