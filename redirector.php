<?php
/**
 * Plugin Name: Kreacio Redirection
 * Author: Kreacio
 * Author URI: http://kreaciomedia.com
 * Description: Redirects visitors to the specified URL based on their country.
 * Tags: page redirection, URL Redirection, 301 redirection plugin, 404, IP redirection ,Geo IP redirect, location redirect, post redirection plugin
 * Version: 1.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 **/

require 'includes/functions.php';

function _sprintf($array){
	echo sprintf('<pre>%s</pre>', print_r($array, true));
}

$args = array( 'post_type' => 'kred', );
$query = new WP_Query( array( $args ) );
_sprintf( $query );