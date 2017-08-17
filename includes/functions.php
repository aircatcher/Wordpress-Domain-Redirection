<?php
# @package: Custom IP Redirect
require_once dirname(__DIR__) . '\vendor\autoload.php';
use MaxMind\Db\Reader;

// add_action( 'wp_enqueue_style', 'register_styles' );
// function register_styles()
// {
//     wp_register_style( 'my-stylesheet', plugin_url('/css/style.css') );
//     wp_enqueue_style ( 'my-stylesheet' );
// }

$kreacio_meta_box = new Kreacio_Redirection_Meta_Box();

class Kreacio_Redirection_Meta_Box
{
	protected $meta_id		= 'meta_box_id';
	protected $meta_title	= 'Kreacio Redirection';
	protected $post_type	= 'kred';
	protected $callback		= 'meta_box_callback';
	protected $meta_position = 'normal';
	protected $meta_priority = 'high';

	function __construct()
	{
		add_action( 'init', array($this, 'meta_box_custom_post') );
	  add_action( 'add_meta_boxes', array($this, 'meta_box') );
		add_action( 'save_post', array($this, 'meta_box_save') );
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
	 	add_meta_box( $this->meta_id, $this->meta_title, array($this, $this->callback), $this->post_type, $this->meta_position, $this->meta_priority );
	}
	function meta_box_callback($post)
	{
	  $meta_dropdown  = get_post_meta($post->ID, 'meta_box_callback', true);
		$meta_targeturl = get_post_meta($post->ID, 'meta_box_callback', true);
		wp_nonce_field(plugin_basename(__FILE__), 'kreacio_nonce');

		/** MaxMind GEO IP **/
		if ( !function_exists('geoip_open') ) require_once 'geoip.inc.php';
		// $ip_addr = $_SERVER['REMOTE_ADDR'];
		$mmdb    = dirname(__DIR__) . '/' . 'vendor/maxmind-db/reader/src/MaxMind/Db/GeoLite2-Country.mmdb';
		$reader  = new Reader($mmdb);
		// $ip_info = $reader->get($ip_addr);
		$reader->close();
		
		// Read CSV and put it into an array map
		$countries = array_map('str_getcsv', file(dirname(__DIR__).'/includes/GeoIPCountry.csv'));

		list($country_id, $country_name) = $countries;
		?>
		<table>
			<tr>
				<td id="meta_box_css"><label for="country_code">Country</label></td>
				<td> :
					<select name="country_code" id="country_code">
						<option value="">Default Redirection</option>
						<?php meta ?>
						<?php foreach( $countries as $country ) : ?>
						<option value="<?php echo $country[0]; ?>" selected="selected"><?php echo $country[1]; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td id="meta_box_css"><label for="target_url">Target URL</label></td>
				<td> :<input type="URL" name="target_url" value="<?php echo $meta_targeturl; ?>" placeholder="http://www.targeturl.com" /></td>
			</tr>
			</select>
		<?php
	}
	function meta_box_save()
	{
		if( isset($_POST['country_code']) )
			update_post_meta($this->meta_id, $this->callback, $_POST['country_code']);

		if( isset($_POST['target_url']) )
			update_post_meta($this->meta_id, $this->callback, $_POST['target_url']);
	}
}