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
   add_meta_box('meta_box_id', 'Kreacio Redirection', 'kreacio_redirection_element_grid_meta_box', $post->post_type, 'normal' , 'high');
}

add_action('save_post', 'kreacio_redirection_save_meta_box_data');
function kreacio_redirection_save_meta_box_data()
{ 
   global $post;
   if(isset($_POST["r_country_code"]))
   {
      //UPDATE:
      $meta_element = $_POST['r_country_code'];
      //END OF UPDATE

      update_post_meta($post->ID, 'kreacio_redirection_element_grid_meta_box', $meta_element);
      //print_r($_POST);
   }
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

function kreacio_redirection_element_grid_meta_box($post)
{
	/** MaxMind GEO IP **/
	if (!function_exists('geoip_open')) require_once 'geoip.inc.php';

	$gi = geoip_open(dirname(__DIR__).'/includes/GeoIP.dat', GEOIP_STANDARD);
	$ip = $_SERVER['REMOTE_ADDR'];

	$json = file_get_contents("http://ipinfo.io/{$ip}");
    $details = json_decode($json);

	$preselect_country = geoip_country_name_by_addr($gi, $ip);  

	echo geoip_country_code_by_addr($gi, "80.24.24.24") . "\t" .
	     geoip_country_name_by_addr($gi, "80.24.24.24") . "\n";

	geoip_close($gi);

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
	    		<td>Whois </td>
	    		<td><?php echo $countries[1][1], " ( ",$ip," )"; ?></td>
	    		<td><font size="1.5 em"><i>Your IP details</i></font></td>
	    	</tr>
	    	<tr>
	    		<td>Choose country </td>
	    		<td>
	    			<select name="r_country_code" id="r_country_code">
				    	<option value ="" <?php selected( $meta_element, '' ); ?>>-- Default Redirection --</option>
					    <?php
					    $i = 0;
					    foreach($countries as $country)
						{ ?>
							<option value="<? echo $countries[$i][0] ?>"<?php selected($meta_element, '<?php echo $countries[$i][0] ?>'); ?>>
								<?php echo $countries[$i][1]; ?>
							</option>
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

    <br/><br/>
    <font size="1.5 em"><i>This product includes GeoLite data created by MaxMind, available from</i>
    <a href="http://www.maxmind.com" target="_blank">http://www.maxmind.com</a></font>
    <?php 
}