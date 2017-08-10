<?php
/**
 * Plugin Name: Kreacio IP Redirector (Custom Post)
 * Author: Kreacio
 * Author URI: http://kreaciomedia.com
 * Description: This plugin redirects visitors to the specified URL based on their country.
 * Tags: page redirection, URL Redirection, 301 redirection plugin, 404, IP redirection ,Geo IP redirect, location redirect, post redirection plugin
 * Version: 1.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 **/

require 'vendor/autoload.php';

/*****settings options****/
// function kreacio_redirection_cp_settings_page()
	// require plugin_dir_path(__FILE__) . 'includes/options.php';
	require plugin_dir_path(__FILE__) . 'includes/functions.php';

if ( ! defined( 'ABSPATH' ) )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

add_action('admin_menu', 'kreacio_redirection_cp_create_menu');
function kreacio_redirection_cp_create_menu()
{
   $labels = array(
      'name'               => _x( 'Countries', 'post type general name' ),
      'singular_name'      => _x( 'Country', 'post type singular name' ),
      'add_new'            => _x( 'Add New Redirection', 'book' ),
      'add_new_item'       => __( 'Add New Redirection' ),
      'edit_item'          => __( 'Edit Redirection' ),
      'new_item'           => __( 'New Redirection' ),
      'all_items'          => __( 'All Redirections' ),
      'view_item'          => __( 'View Redirection' ),
      'search_items'       => __( 'Search Redirections' ),
      'not_found'          => __( 'No post(s) found' ),
      'not_found_in_trash' => __( 'No post(s) found in the Trash' ), 
      'parent_item_colon'  => '',
      'menu_name'          => 'Kreacio Redirection'
   );
   $args = array(
      'labels'        => $labels,
      'description'   => 'Holds countries and country specific redirection',
      'public'        => true,
      'menu_position' => 5,
      'supports'      => array( 'title' ),
      'has_archive'   => true,
   );
   register_post_type( 'ipr', $args );
}
add_action( 'init', 'kreacio_redirection_cp_create_menu' ); 

/*****register settings options****/
function fn_eip_cp_redirection_register_mysettings()
{
   register_setting( 'kreacio-redirection-cp-settings-group', 'kreacio-redirection-cp_redirect_provider' );
   register_setting( 'kreacio-redirection-cp-settings-group', 'kreacio-redirection-cp_redirect_api2' );
   register_setting( 'kreacio-redirection-cp-settings-group', 'kreacio-redirection-cp_redirect_api3' );
   register_setting( 'kreacio-redirection-cp-settings-group', 'kreacio-redirection-cp_country' );
   register_setting( 'kreacio-redirection-cp-settings-group', 'kreacio-redirection-cp_ruletype' );
   register_setting( 'kreacio-redirection-cp-settings-group', 'kreacio-redirection-cp_redirect_url' );
}

function kreacio_redirection_cp_activate() 
{
   global $wpdb;
   $table_name = "kreacio_redirection_cp_rule";   
   $sql =   "CREATE TABLE  $table_name (
            `ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `country_code` VARCHAR( 10 ) NOT NULL ,
            `type` VARCHAR( 15 ) NOT NULL ,
            `current_url` TEXT NOT NULL ,
            `url` TEXT NOT NULL
            );";
   $table_name = 
   $wpdb->query($sql);
}
register_activation_hook(__FILE__, 'kreacio_redirection_cp_activate');

function kreacio_redirection_cp_deactivate() 
{
   global $wpdb;
   $table_name = "kreacio_redirection_cp_rule";
   $sql        = "DROP TABLE IF EXISTS $table_name;";
   $wpdb->query($sql);
   
}
register_deactivation_hook(__FILE__, 'kreacio_redirection_cp_deactivate');