<?php
/*
Plugin Name: Admin Notices
Plugin URI: https://github.com/jonmcpartland/Admin-Notices/
Description: Useable admin notices.
Author: Jon McPartland
Version: 0.1.0
Author URI: https://jon.mcpart.land
Textdomain: adminnotices
*/

new class {

	protected $optionName = 'adminnotices';

	public function __construct() {
		\add_option( $this->optionName, [], false, true );

		$this->create_helper();

		\add_action( 'admin_notices', [ $this, 'display_notices' ] );
	}

	public function display_notices() {
		$notices   = \get_option( $this->optionName, [] );
		$displayed = [];

		foreach ( $notices as $notice ) {
			if ( ! $this->should_display( $notice ) ) {
				continue;
			}

			$this->render( $notice );

			if ( $notice['persist'] ?? false ) {
				continue;
			}

			$displayed[] = $notice;
		}

		$this->update_notices( $notices, $displayed );
	}

	protected function should_display( $notice ) {
		if ( ! isset( $notice['display'] ) ) {
			return false;
		}

		foreach ( (array) $notice['display'] as $global => $comparison ) {
			if ( ! $baseValue = $GLOBALS[ $global ] ?? false || $baseValue !== $comparison ) {
				return false;
			}
		}

		return true;
	}

	protected function render( $notice ) {
		vprintf( '<div class="%s"><p>%s</p></div>', [
			"notice notice-{$notice['level']} is-dismissible",
			$notice['message'],
		] );
	}

	protected function update_notices( $notices, $displayed ) {
		// @TODO: ensure data integrity to allow removal of error suppression
		$notDisplayed = @array_diff( $notices, $displayed );

		\update_option( $this->optionName, $notDisplayed, true );
	}

	protected function create_helper() {
		// @TODO: Move this out of the class
		function create_admin_notice( $notice ) {
			if ( ! isset( $notice['display'], $notice['level'], $notice['message'] ) ) {
				throw new \Exception(
					vsprintf( 'The following properties are required for creating an admin notice: "%s", "%s", "%s".', [
						'level', 'message', 'display',
					] )
				);
			}

			$notices = \get_option( 'adminnotices', [], false, true );
			$notices[] = $notice;
			\update_option( 'adminnotices', $notices, false, true );
		}
	}

};
