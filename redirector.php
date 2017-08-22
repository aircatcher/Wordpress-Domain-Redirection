<?php
/**
 * Plugin Name: Country Based Redirection
  * Author: Ferick Andrew
 * Author URI: http://fandrew.xyz
 * Description: Redirects visitors to the specified URL based on their country.
 * Tags: page redirection, URL Redirection, 301 redirection plugin, 404, IP redirection ,Geo IP redirect, location redirect, post redirection plugin
 * Version: 1.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 **/

require 'includes/functions.php';

$class = new Kreacio_Redirection();

$getip = $class::check_ip();
$ip_info = Kreacio_Redirection::maxmind_geo(Kreacio_Redirection::$ip);

$url = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // 'http://'.
$explodeURL = explode("/", $url);
$explodeDomain = explode(".", $explodeURL[0]);

$args = array(
		  'post_type'   => 'kred',
		  'post_status' => 'publish',
		);
$list = get_posts( $args );

// Loop each posts
foreach ($list as $key)
{
	// Get the post meta to get each ID from the posts
	$post_meta = get_post_meta($key->ID);
	$meta_country = $post_meta['country_list'];
	$meta_target_url = $post_meta['target_url'];

	$country_id = str_split($meta_country[0], 2);
	// Check if there is a saved country id that match the client country ID
	if( Kreacio_Redirection::$c_id == in_array(Kreacio_Redirection::$c_id, $country_id) )
	{
		if( in_array("wp-login.php", $explodeURL) || in_array("wp-admin", $explodeURL) ) {}
		else
		{
			header("Location:".$meta_target_url[0]);
			die();
			exit;
		}
	}
}