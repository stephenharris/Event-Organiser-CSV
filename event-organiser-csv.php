<?php
/**
 * Plugin Name: Event Organiser CSV
 * Plugin URI:  http://wp-event-organiser.com/
 * Description: Import and Export events via CSV 
 * Version:     0.1.0
 * Author:      Stephen Harris
 * Author URI:  http://stephenharris.info
 * License:     GPLv2+
 * Text Domain: event-organiser-csv
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2013 Stephen Harris (email : contact@stephenharris.info)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * The concept of this plug-in is based on http://wordpress.org/plugins/wordpress-importer/
 * and code has been used from that to kick-start this plug-in.
 */

define( 'EVENT_ORGANISER_CSV_VERSION', '0.1.0' );
define( 'EVENT_ORGANISER_CSV_URL',     plugin_dir_url( __FILE__ ) );
define( 'EVENT_ORGANISER_CSV_DIR',    dirname( __FILE__ ) . '/' );

/**
 * Default initialization for the plugin:
 */
function eventorganisercsv_init() {

	load_plugin_textdomain( 'event-organiser-csv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
	if( is_admin() ){
		
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/class-eo-csv-parser.php');
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/class-eo-event-csv-parser.php');
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/admin.php');
		
		$admin_page = new EO_CSV_Import_Admin_Page();
		
		$ext = (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG) ? '' : '.min';
		
		wp_register_script( 'eo_csv_jquery_csv', EVENT_ORGANISER_CSV_URL . "assets/js/vendor/jquery-csv{$ext}.js", array( 'jquery' ),  EVENT_ORGANISER_CSV_VERSION );
		wp_register_script( 'eo_csv_admin', EVENT_ORGANISER_CSV_URL . "assets/js/event_organiser_csv{$ext}.js", array( 'jquery', 'eo_csv_jquery_csv' ),  EVENT_ORGANISER_CSV_VERSION );
		wp_register_style( 'eo_csv_admin', EVENT_ORGANISER_CSV_URL . "assets/css/event_organiser_csv{$ext}.css", array(),  EVENT_ORGANISER_CSV_VERSION );
		
		wp_localize_script( 'eo_csv_admin', 'eo_csv', array(
			'locale' => array(
				'title' 			=> __( 'Title', 'event-organiser-csv' ),
				'start' 			=> __( 'Start', 'event-organiser-csv' ),
				'end' 				=> __( 'End', 'event-organiser-csv' ),
				'recur_until' 		=> __( 'Recur until', 'event-organiser-csv' ),
				'recur_schedule' 	=> __( 'Recurrence schedule', 'event-organiser-csv' ),
				'recur_freq'	 	=> __( 'Recurrence frequency', 'event-organiser-csv' ),
				'schedule_meta'		=> __( 'Schedule meta', 'event-organiser-csv' ),
				'content' 			=> __( 'Content', 'event-organiser-csv' ),
				'venue' 			=> __( 'Venue', 'event-organiser-csv' ),
				'categories' 		=> __( 'Categories', 'event-organiser-csv' ),
				'tags' 				=> __( 'Tags', 'event-organiser-csv' ),
				'include_dates' 	=> __( 'Include dates', 'event-organiser-csv' ),
				'exclude_dates' 	=> __( 'Exclude dates', 'event-organiser-csv' ),
				'post_meta' 		=> __( 'Post Meta', 'event-organiser-csv' ),
			)
		
		));
	}
}
add_action( 'init', 'eventorganisercsv_init' );
