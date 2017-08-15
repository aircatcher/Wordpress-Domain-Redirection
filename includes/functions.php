<style>
	#target_url_box
	{
		position: relative;
		top: 2px;
		height: 28px;
		width: 317px;
	}
	#kr_table_layout tr
	{
		position: relative;
		height: 5px !important;
	}
</style>

<?php
# @package: Custom IP Redirect
require_once dirname(__DIR__) . '/' . 'vendor/autoload.php';
require_once __DIR__ . '/' . 'MaxMind/Db/Reader.php';
require_once __DIR__ . '/' . 'MaxMind/Db/Reader/Decoder.php';
require_once __DIR__ . '/' . 'MaxMind/Db/Reader/InvalidDatabaseException.php';
require_once __DIR__ . '/' . 'MaxMind/Db/Reader/Metadata.php';
require_once __DIR__ . '/' . 'MaxMind/Db/Reader/Util.php';
use MaxMind\Db\Reader;

/**
*  Kreacio Redirection Meta Box
**/
add_action( 'add_meta_boxes', 'kreacio_redirection_meta_box' );
function kreacio_redirection_meta_box($post)
{
   add_meta_box('meta_box_id', 'Kreacio Redirection', 'kreacio_redirection_meta_box_cb', $post->post_type, 'normal' , 'high');
}

function readCSV($csvFile)
{
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle) ) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    return $line_of_text;
}

function kreacio_redirection_meta_box_cb($post)
{
	wp_nonce_field( plugin_basename( __FILE__ ), 'meta_box_nonce' );
	$meta_dropdown = get_post_meta($post->ID, 'r_country_code', true); //true ensures you get just one value instead of an array
	$meta_target = get_post_meta( $post->ID, 'target_url', true );

	/** MaxMind GEO IP **/
	if (!function_exists('geoip_open')) require_once 'geoip.inc.php';
	$ipAddr = $_SERVER['REMOTE_ADDR'];
	$mmdb   = __DIR__. '/' .'MaxMind/DB/GeoLite2-Country.mmdb';
	$reader = new Reader($mmdb);
	$ipData = $reader->get($ipAddr);

	// $record = $reader->country($ipAddr)->isoCode;
	// echo '<pre>';
	// print_r($record->get($ipAddr));
	// echo '</pre>';
	$reader->close();

	// $gi = geoip_open(dirname(__DIR__).'/includes/GeoLiteCountry.dat', GEOIP_STANDARD);
	// // $ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
	// $ip = $_SERVER['REMOTE_ADDR'];
	// $record = geoip_country_code_by_addr($gi, $ip);
	// geoip_close($gi);

	// $country_list = array_map('str_getcsv', file(dirname(__DIR__).'/includes/GeoIPCountry.csv'));
	$csvFile = dirname(__DIR__).'/includes/GeoIPCountry.csv';
	$countries = readCSV($csvFile);
	// echo '<pre>';
	// print_r($countries);
	// echo '</pre>';

	list($country_id, $country_name) = $countries;
	?><form action="functions.php" method="POST">
	  	<table id="kr_table_layout">
	    	<tr>
	    		<td>Choose country </td>
	    		<td> :
	    			<select name="r_country_code" id="r_country_code">
				    	<option value ="" <?php selected( $meta_dropdown, '' ); ?>>DEFAULT REDIRECTION</option>
					    <?php
						    $i = 0;
						    foreach($countries as $country)
							{ ?><option value="<? echo $countries[$i][0] ?>"
									<?php selected($meta_dropdown, '<?php echo $countries[100][0]; ?>'); ?>
									<?php if($i <= 2) { ?> style="text-transform: uppercase;" <?php } ?>>
									<?php echo $countries[$i][1]; ?>
								</option>
								<?php if($i == 2)
								{
									?>
									<option value ="" <?php selected( $meta_dropdown, '' ); ?> disabled>
									------------------- Countries -------------------
									</option>
									<?php
								} ?><?php $i++;
							} ?>
			    	</select>
	    		</td>
	    	</tr>
	    	<tr>
	    		<td>Target URL </td>
	    		<td>: <input type="URL" id="target_url_box" name="target_url" placeholder="http://www.TargetURL.com/" value="<?php echo $meta_target; ?>" required/></td>
	    	</tr>
	    </table>
		</form>

		<br/>
    <font size="1.5 em"><i>This product includes GeoLite data created by MaxMind, available from</i>
    <a href="http://www.maxmind.com" target="_blank">http://www.maxmind.com</a></font>
		<br/><br/>
		<?php
		if($record == $_POST['r_country_code'])
		{
			$targetURL = $_POST['target_url'];
			function Redirect($targetURL, $statusCode = 303)
			{
				if (headers_sent()) die("<font color='RED'><b>ERROR:</b> Header is already sent</font>");
				else exit(header('Location: ' . $targetURL, true, $statusCode));
			}
			Redirect('http://www.google.com/', false);
		}
}

add_action('save_post', 'kreacio_redirection_save_meta_box_data', 1, 2);
// add_action('save_post', array($this, 'kreacio_redirection_save_meta_box_data'));
function kreacio_redirection_save_meta_box_data($post_id)
{
   	//1. verifies meta box nonce (to prevent CSRF attacks)
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], basename( __FILE__ ) ) )
      return;

    //2. if autosaves
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
      return;
    
    //3. if user's not admin
    if( !current_user_can( 'edit_post', $post_id ) )
      return;
    elseif ( !current_user_can ( 'edit_page', $post_id) )
      return;
    
    //4. checks all custom field values (see 'create_work_meta()' function)
    if( isset( $_REQUEST['meta_dropdown'] ) )
        update_post_meta( $post_id, 'meta_dropdown', sanitize_text_field( $_POST['meta_dropdown'] ) );
    if( isset( $_REQUEST['meta_target'] ) )
        update_post_meta( $post_id, 'meta_target', sanitize_text_field( $_POST['meta_target'] ) );
}