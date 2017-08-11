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

/**
*  Kreacio Redirection Meta Box
**/
add_action( 'add_meta_boxes', 'kreacio_redirection_meta_box' );
function kreacio_redirection_meta_box($post)
{
   add_meta_box('meta_box_id', 'Kreacio Redirection', 'kreacio_redirection_meta_box_content', $post->post_type, 'normal' , 'high');
}

add_action('save_post', array($this, 'kreacio_redirection_save_meta_box_data'));
function kreacio_redirection_save_meta_box_data()
{
   // Verify this came from the our screen and with proper authorization,
      // because save_post can be triggered at other times
      if ( !wp_verify_nonce( $_POST['blc_nonce'], plugin_basename(__FILE__) )) {
        return $post_id;
      }

      // Verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
      // to do anything
      if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return $post_id;


      // Check permissions to edit pages and/or posts
      if ( 'page' == $_POST['ipr'] ||  'post' == $_POST['ipr']) {
        if ( !current_user_can( 'edit_page', $post_id ) || !current_user_can( 'edit_post', $post_id ))
          return $post_id;
      } 

      // OK, we're authenticated: we need to find and save the data
      $blc = $_POST['backlink_url'];

      // save data in INVISIBLE custom field (note the "_" prefixing the custom fields' name
      update_post_meta($post_id, '_backlink_url', $blc); 
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

function kreacio_redirection_meta_box_content($post)
{
	/** MaxMind GEO IP **/
	if (!function_exists('geoip_open')) require_once 'geoip.inc.php';

	$gi = geoip_open(dirname(__DIR__).'/includes/GeoIP.dat', GEOIP_STANDARD);
	// $ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
	$ip = $_SERVER['REMOTE_ADDR'];
	
	// $json = file_get_contents("http://ipinfo.io/{$ip}");
 	// $details = json_decode($json);

	$preselect_country = geoip_country_name_by_addr($gi, $ip);  

	echo geoip_country_code_by_addr($gi, "80.24.24.24") . "\t" .
	     geoip_country_name_by_addr($gi, "80.24.24.24") . "\n";

	geoip_close($gi);

	wp_nonce_field( plugin_basename( __FILE__ ), 'blc_nonce' );
  $meta_element = get_post_meta($post->ID,
                  'kreacio_redirection_element_grid_meta_box', true); //true ensures you get just one value instead of an array
  // $country_list = array_map('str_getcsv', file(dirname(__DIR__).'/includes/GeoIPCountry.csv'));
  $csvFile = dirname(__DIR__).'/includes/GeoIPCountry.csv';
  $countries = readCSV($csvFile);
	// echo '<pre>';
	// print_r($countries);
	// echo '</pre>';

	list($country_id, $country_name) = $countries;
	?>

    <form action="functions.php" method="POST">
	  	<table id="kr_table_layout">
	    	<tr>
	    		<td>Choose country </td>
	    		<td>
	    			<select name="r_country_code" id="r_country_code">
				    	<option value ="" <?php selected( $meta_element, '' ); ?>>DEFAULT REDIRECTION</option>
					    <?php
						    $i = 0;
						    foreach($countries as $country)
								{ ?>
									<option value="<? echo $countries[$i][0] ?>"
										<?php selected($meta_element, '<?php echo $countries[100][0]; ?>'); ?>
										<?php if($i <= 2) { ?> style="text-transform: uppercase;" <?php } ?>>
											<?php echo $countries[$i][1]; ?>
									</option>
									<?php if($i == 2)
									{ ?>
										<option value ="" <?php selected( $meta_element, '' ); ?> disabled>
											------------------- Countries -------------------
										</option>
									<?php } ?>
									<?php $i++;
								} ?>
			    	</select>
	    		</td>
	    	</tr>
	    	<tr>
	    		<td>Target URL </td>
	    		<td><input type="URL" id="target_url_box" placeholder="http://www.TargetURL.com/" required/></td>
	    	</tr>
	    </table>
		</form>
		<?php
		if($preselect_country == r_country_code)
		{
			$targetURL = $_POST['subject'];
			header("Location: ", $targetURL);
			die();
		}
		?>

    <br/><br/>
    <font size="1.5 em"><i>This product includes GeoLite data created by MaxMind, available from</i>
    <a href="http://www.maxmind.com" target="_blank">http://www.maxmind.com</a></font>
<?php
}