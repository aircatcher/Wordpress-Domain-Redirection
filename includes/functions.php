<?php
ob_clean(); ob_start();
// ini_set('display_errors', '1'); //Only if the server disables the errors
# @package: Custom IP Redirect
require dirname(__DIR__)  . '/vendor/autoload.php';
include dirname(__FILE__) . '/geoipcity.inc';
include dirname(__FILE__) . '/geoipregionvars.php';
// use MaxMind\Db\Reader;

// add_action( 'wp_enqueue_style', 'register_styles' );
// function register_styles()
// {
//     wp_register_style( 'my-stylesheet', plugin_url('/css/style.css') );
//     wp_enqueue_style ( 'my-stylesheet' );
// }

$kreacio_redirection = new Kreacio_Redirection();
function is_user_logged_in() {
	$user = wp_get_current_user();

	if ( empty( $user->ID ) ) return false;
	return true;
}

if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php"); 
}

class Kreacio_Redirection
{
	private $mode = 0;
	public $meta_target_url = '';
	public $meta_id		= 'meta_box_id';
	public $meta_title	= 'Kreacio Redirection';
	protected $post_type  = 'kred';
	public $callback	  = 'meta_box_callback';
	public $meta_position = 'normal';
	public $meta_priority = 'high';
	
	function __construct()
	{
		add_action( 'init', array($this, 'meta_box_custom_post') );
		add_action( 'add_meta_boxes', array($this, 'meta_box') );
		add_action( 'save_post', array($this, 'meta_box_save') );

		// Check Client IP
		if ( !empty($_SERVER['HTTP_CLIENT_IP']) ) $ip = $_SERVER['HTTP_CLIENT_IP'];	// Client IP
		elseif ( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];	// VPN
		else $ip = $_SERVER['REMOTE_ADDR'];	// Admin

		$geoPath = dirname(__FILE__).'/GeoIP.dat' ;
		$gi = geoip_open( $geoPath, GEOIP_STANDARD );
		if($ip == '::1' || $ip == '127.0.0.1') {
			$c_id   = 'localhost';
			$c_name = 'localhost';
		} else {
			$c_id   = geoip_country_code_by_addr($gi, $ip);
			$c_name = geoip_country_name_by_addr($gi, $ip);
		}
		geoip_close($gi);

		$countries = array_map('str_getcsv', file(dirname(__FILE__).'/GeoIPCountry.csv'));
		list($country_id, $country_name) = $countries;
		
		$url = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // 'http://'.
		$explodeURL = explode("/", $url);
		$explodeDomain = explode(".", $url);
		
		//$query = new WP_Query( array(  ) );
		//$args = array(
		//		'post_type' => 'kred'
		//	);
		//$qquery = new WP_Query($args);
		//_sprintf($qquery);

		if( !( in_array("wp-login.php", $explodeURL) || in_array("wp-admin", $explodeURL) ) )
		{
			if( $c_id == "ID" )
			{
				if( strpos($_SERVER['HTTP_HOST'], 'com') !== false ){
					//header("Location: http://" . $explodeDomain[0] . "." . $explodeDomain[1] . ".co.id" . "/wordpress"); die();
				}
			}
		}
	}
	function meta_box_custom_post()
	{
		$labels = array(
		  'name'               => _x( 'Redirections', 'post type general name' ),
		  'singular_name'      => _x( 'Redirection', 'post type singular name' ),
		  'add_new'            => _x( 'Add New Redirection', 'book' ),
		  'add_new_item'       => __( 'Add New Redirection' ),
		  'edit_item'          => __( 'Edit Redirection' ),
		  'new_item'           => __( 'New Redirection' ),
		  'all_items'          => __( 'All Redirections' ),
		  'view_item'          => __( 'View Redirection' ),
		  'search_items'       => __( 'Search Redirections' ),
		  'not_found'          => __( 'No redirection(s) found' ),
		  'not_found_in_trash' => __( 'No redirection(s) found in the Trash' ), 
		  'parent_item_colon'  => '',
		  'menu_name'          => $this->meta_title
		);
		$args = array(
		  'menu_icon'     => 'dashicons-admin-site',
		  'labels'        => $labels,
		  'description'   => 'Holds countries and country specific redirection',
		  'public'        => true,
		  'menu_position' => 5,
		  'supports'      => array('title'),
		  'has_archive'   => true,
		);
		register_post_type( $this->post_type, $args );
	}
	function meta_box($post)
	{
	 	add_meta_box( $this->meta_id,									// Meta Box ID
	 								$this->meta_title,							// Title
	 								array($this, $this->callback),	// Meta Box Contents
	 								$this->post_type,								// Post Type
	 								$this->meta_position,						// Meta Box Placement
	 								$this->meta_priority );
	}
	function meta_box_callback($post, $meta_target_url)
	{
		$meta_dropdown   = get_post_meta($post->ID, 'country_dropdown', true);
		$meta_target_url = get_post_meta($post->ID, 'target_url', true);
		// wp_nonce_field(plugin_basename(__FILE__), 'kreacio_nonce');

		// Check current page URL
		$currentURL = 'http';
	  if ($_SERVER["HTTPS"] == "on") {$currentURL .= "s";} $currentURL .= "://";
	  if ($_SERVER["SERVER_PORT"] != "80")
	  	$currentURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	  else
	  	$currentURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

	  // $pageURL = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
		
		$countries = array_map('str_getcsv', file(dirname(__FILE__).'/GeoIPCountry.csv'));
		list($country_id, $country_name) = $countries;
		?>
		
		<table>
			<tr>
				<td><label for="country_dropdown">Country</label></td>
				<td> : 
					<select name="country_dropdown" id="country_dropdown">
						<option value=''<?php selected( $meta_dropdown, '' ); ?>>Default Redirection</option>
						<?php foreach( $countries as $country ) :
									$selected = ($meta_dropdown == $country[0]) ? "selected" :''; ?>
						<option value="<?php echo $country[0]; ?>" <?php echo $selected; ?>>
							<?php echo $country[1];?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="target_url">Target URL</label></td>
				<td> : <input type="URL" name="target_url" value="<?php echo $meta_target_url; ?>" placeholder="http://www.targeturl.com" style="width:317px"/></td>
			</tr>
		</table>
		<?php if($mode == 1) : ?>
			<br/>
			<table border="1">
				<tr>
					<th colspan='2'>TEMP</th>
				</tr>
				<tr>
					<td>IP</td>
					<td><?php echo $ip; ?></td>
				</tr>
				<tr>
					<td>host</td>
					<td><?php echo $_SERVER['HTTP_HOST']; ?></td>
				</tr>
				<tr>
					<td>self</td>
					<td><?php echo $_SERVER['PHP_SELF']; ?></td>
				</tr>
				<tr>
					<td>Country ID / Name</td>
					<td><?php echo $c_id . " / " . $c_name; ?></td>
				</tr>
				<tr>
					<td>Meta Dropdown</td>
					<td><?php echo $meta_dropdown; ?></td>
				</tr>
				<tr>
					<td>Meta Target URL</td>
					<td><?php echo $meta_target_url; ?></td>
				</tr>
				<tr>
					<td>Current URL</td>
					<td><?php echo $currentURL; ?></td>
				</tr>
			</table>
		<?php endif;
	}
	function meta_box_save()
	{
		global $post;
		if( isset($_POST['country_dropdown']) )
			update_post_meta( $post->ID, 'country_dropdown', esc_attr( $_POST['country_dropdown'] ));

		if( isset($_POST['target_url']) )
			update_post_meta($post->ID, 'target_url', $_POST['target_url']);
	}
}