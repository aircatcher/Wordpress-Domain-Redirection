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
require 'nav_menu.php';
// require plugin_dir_path(__FILE__) . 'includes/functions.php';

if (!defined( 'ABSPATH'))
{
   header('Status: 403 Forbidden');
   header('HTTP/1.1 403 Forbidden');
   exit;
}