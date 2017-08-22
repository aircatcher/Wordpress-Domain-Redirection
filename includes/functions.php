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

function _sprintf($array){
	echo sprintf('<pre>%s</pre>', print_r($array, true));
}

class Kreacio_Redirection
{
	private $mode = 1;

	public $meta_id		= 'meta_box_id';
	public $meta_title	= 'Kreacio Redirection';
	protected $post_type  = 'kred';
	public $content	  = 'meta_box_content';
	public $meta_position = 'normal';
	public $meta_priority = 'high';

	static $currentURL;
	static $country_list, $target_url;
	static $c_id, $c_name, $ip, $countries;
	
	function __construct()
	{
		add_action( 'init', array($this, 'custom_post') );
		add_action( 'add_meta_boxes', array($this, 'meta_box') );
		add_action( 'save_post', array($this, 'meta_box_save') );
	}

	function check_ip()
	{
		Kreacio_Redirection::$ip = $_SERVER['REMOTE_ADDR'];
	}

	function maxmind_geo($ip)
	{
		$geoPath = dirname(__FILE__).'/GeoIP.dat' ;
		$gi = geoip_open( $geoPath, GEOIP_STANDARD );

		Kreacio_Redirection::$c_id 	 = geoip_country_code_by_addr($gi, Kreacio_Redirection::$ip);
		Kreacio_Redirection::$c_name = geoip_country_name_by_addr($gi, Kreacio_Redirection::$ip);

		geoip_close($gi);
	}

	function read_csv()
	{
		Kreacio_Redirection::$countries = array_map('str_getcsv', file(dirname(__FILE__).'/GeoIPCountry.csv'));
		list($country_id, $country_name) = Kreacio_Redirection::$countries;
	}

	function content($countries)
	{
		wp_nonce_field( basename( __FILE__ ), 'meta_box_nonce' ); ?>
		<table>
			<tr>
				<td><label for="country_list">Country</label></td>
				<td> : 
					<select name="country_list" id="country_list">
						<option value=''<?php selected( Kreacio_Redirection::$country_list, '' ); ?>>Default Redirection</option>
						<?php foreach( Kreacio_Redirection::$countries as $country ) :
									$selected = (Kreacio_Redirection::$country_list == $country[0]) ? "selected" : ''; ?>
						<option value="<?php echo $country[0]; ?>" <?php echo $selected; ?>>
							<?php echo $country[1];?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="target_url">Target URL</label></td>
				<td> : <input type="URL" name="target_url" value="<?php echo Kreacio_Redirection::$target_url; ?>" placeholder="http://www.targeturl.com" style="width:317px"/></td>
			</tr>
		</table>
		<?php if($this->mode == 1) : ?>
			<br/>
			<table border="1">
				<tr>
					<th colspan='2'>TEMP (change $mode to 1)</th>
				</tr>
				<tr>
					<td>IP</td>
					<td><?php echo Kreacio_Redirection::$ip; ?></td>
				</tr>
				<?php if(Kreacio_Redirection::$ip !== '::1') : ?>
				<tr>
					<td>Country ID / Name</td>
					<td><?php echo Kreacio_Redirection::$c_id . " / " . Kreacio_Redirection::$c_name; ?></td>
				</tr>
				<?php endif; ?>
				<tr>
					<td>HTTP_HOST</td>
					<td><?php echo $_SERVER['HTTP_HOST']; ?></td>
				</tr>
				<tr>
					<td>PHP_SELF</td>
					<td><?php echo $_SERVER['PHP_SELF']; ?></td>
				</tr>
				<tr>
					<td>Meta Dropdown</td>
					<td><?php echo Kreacio_Redirection::$country_list; ?></td>
				</tr>
				<tr>
					<td>Meta Target URL</td>
					<td><?php echo Kreacio_Redirection::$target_url; ?></td>
				</tr>
			</table>
		<?php endif;
	}

	function custom_post()
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
		  'show_ui'				=> true,
		  'exclude_from_search'	=> false,
		  'capability_type'			=> "post",
		  'menu_position' => 5,
		  'map_meta_cap'	=> true,
		  'hierarchical'	=> false,
		  'supports'      => array('title'),
		  'has_archive'   => true,
		  'rewrite' => array( 'slug' => 'kred', 'with_front' => true ),
		  'query_var'			=> true,
		);
		register_post_type( $this->post_type, $args );
	}

	function meta_box($post)
	{
		add_meta_box( $this->meta_id,									// Meta Box ID
									$this->meta_title,							// Title
									array($this, $this->content),	// Meta Box Contents
									$this->post_type,								// Post Type
									$this->meta_position,						// Meta Box Placement
									$this->meta_priority );
	}

	function meta_box_content($post)
	{
		$this->post_meta($post);
		// wp_nonce_field(plugin_basename(__FILE__), 'kreacio_nonce');

		// $this->request_url();
		$this->check_ip();
		$this->maxmind_geo(Kreacio_Redirection::$ip);
		$this->read_csv();
		$this->content(Kreacio_Redirection::$countries);
	}

	function post_meta($post)
	{
		Kreacio_Redirection::$country_list 	= get_post_meta($post->ID, 'country_list', true);
		Kreacio_Redirection::$target_url	= get_post_meta($post->ID, 'target_url', true);
	}

	function meta_box_save($post_id)
	{
		if ( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], basename( __FILE__ ) ) )
			return;

		// return if autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		if( isset($_REQUEST['country_list']) )
			update_post_meta($post_id, 'country_list', sanitize_text_field( $_POST['country_list'] ));

		if( isset($_REQUEST['target_url']) )
			update_post_meta($post_id, 'target_url', sanitize_text_field( $_POST['target_url'] ));
	}
}