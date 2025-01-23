<?php
/**
 * Plugin Name: VIP Learn Audit
 * Plugin URI: https://learn.wpvip.com
 * Description: A plugin providing tools to audit VIP Learn Sensei data.
 * Version: 1.0.0
 * Author: Rick Hurst
 * Author URI: https://wpvip.com
 * License: GPL-2.0-or-later
 * Text Domain: vip-learn-audit
 */

// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include library classes
require_once plugin_dir_path( __FILE__ ) . 'inc/class-punctuation.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-title-case.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-sentence-case.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-time-estimation.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once plugin_dir_path( __FILE__ ) . 'inc/class-wp-cli-commands.php';
}
