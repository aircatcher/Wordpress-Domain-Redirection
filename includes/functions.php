<style>
	#meta_box_css
	{
		font-size: 14.5px;
	}
	#target_url_box
	{
		position: relative;
		top: 2px;
		height: 28px;
		width: 317px;
	}
	#meta_box_css tr
	{
		position: relative;
		height: 5px !important;
	}
	#red_notice
	{
		color: #FA5A5A;
	}
</style>
<?php
# @package: Custom IP Redirect
require_once dirname(__DIR__). '/' . 'vendor/autoload.php';
use MaxMind\Db\Reader;

/**
*  Kreacio Redirection Meta Box
**/
function read_CSV($csv_file)
{
  $file_handle = fopen($csv_file, 'r');
  while (!feof($file_handle))
  {
  	$line_of_text[] = fgetcsv($file_handle, 1024);
  }
  fclose($file_handle);
  return $line_of_text;
}

add_action('add_meta_boxes', 'kr_meta_box');
function kr_meta_box($post)
{
  add_meta_box(
		'kr_meta_box',
		'Kreacio Redirection',
		'kr_meta_box_cb',
		'ipr',
		'normal',
		'high' );
}
function kr_meta_box_cb($post)
{
	$values = get_post_custom( $post->ID );
	$text = isset($values['target_url']) ? esc_attr($values['target_url'][0]) : ”;
	$selected = isset($values['r_country_code']) ? esc_attr($values['r_country_code'][0]) : ”;
	wp_nonce_field(plugin_basename(__FILE__), 'kreacio_redirection_nonce');

	/** MaxMind GEO IP **/
	if (!function_exists('geoip_open')) require_once 'geoip.inc.php';
	$ip_addr = $_SERVER['REMOTE_ADDR'];
	$mmdb    =  dirname(__DIR__) . '/' . 'vendor/maxmind-db/reader/src/MaxMind/Db/GeoLite2-Country.mmdb';
	$reader  = new Reader($mmdb);
	$ip_info = $reader->get($ip_addr);
	// $gi = geoip_open(dirname(__DIR__).'/includes/GeoLiteCountry.dat', GEOIP_STANDARD);
	// // $ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
	// $ip = $_SERVER['REMOTE_ADDR'];
	// $reader = geoip_country_code_by_addr($gi, $ip);
	// geoip_close($gi);

	// echo '<pre>';
	// print_r($reader->get($ip_addr));
	// echo '</pre>';

	$csv_file = dirname(__DIR__).'/includes/GeoIPCountry.csv';
	$countries = read_CSV($csv_file);

	$value = get_post_meta($post->ID, '_my_meta_value_key', true);

	list($country_id, $country_name) = $countries;
	echo	'<form action="" method="POST">';
	echo		'<table id="kr_table_layout">';
	echo			'<tr>';
	echo				'<td id="meta_box_css">Choose country </td>';
	echo				'<td>: ';
	echo					'<select name="r_country_code" id="r_country_code">';
	echo					'<option ' . selected($r_country_code, '') . '>DEFAULT REDIRECTION</option>';
								$i = 0;
								foreach($countries as $country)
								{
	echo						'<option ' . selected($r_country_code, '<?php echo $countries[100][0]');
									if($i <= 2) { echo 'style="text-transform: uppercase;"'; }
	echo						'>';
	echo						$countries[$i][1];
	echo						'</option>';
									if($i == 2)
									{
	echo							'<option ' . selected($r_country_code, 'country-heading') . ' disabled>';
	echo							'------------------- Countries -------------------</option>';
									}
									$i++;
								}
	echo					'</select>';
	echo				'</td>';
	echo			'</tr>';
	echo			'<tr>';
	echo				'<td id="meta_box_css">Target URL </td>';
	echo				'<td>: <input type="URL" id="target_url_box" name="target_url" placeholder="http://www.TargetURL.com/" required>';
	echo								$target_url . '</input></td>';
	echo			'</tr>';
	echo		'</table>';
	echo	'</form>';

	echo	'<br/>';
				if($ip_addr == '::1' || $ip_addr == '127.0.0.1')
					echo '<i id="red_notice"><b>Notice:</b> Geolocation is not possible within localhost</i></font><br/><br/>';
	echo	'<font size="2px"><i>This product includes GeoLite data created by MaxMind, available from</i>
				<a href="http://www.maxmind.com" target="_blank">http://www.maxmind.com</a></font>';
	echo	'<pre>' . $ip_info . '</pre>';

				if($reader == $_POST['r_country_code'])
				{
					$t_URL = $_POST['target_url'];
					function Redirect($t_URL, $status_code = 303)
					{
						if (headers_sent()) die('<br/><br/><span id="red_notice"><b>ERROR:</b> Header is already sent</span>');
						else die(header('Location: ' . 'http://www.kreaciomedia.com/', true, $status_code));
					}
					Redirect('http://www.kreaciomedia.com/', false);
				}

	$reader->close();
}

add_action('save_post', 'kreacio_redirection_save', 10, 1);
function kreacio_redirection_save($post_id)
{
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
    return;

    if ( !wp_verify_nonce( $_POST['kreacio_redirection_nonce'], plugin_basename( __FILE__ ) ) )
    return;

  	if ( 'ipr' !== $post->post_type )        
    	return;

		if ( !current_user_can( 'edit_post' ) )
	    return;

    $r_country_code = $_POST['r_country_code'];
    update_post_meta($post_id, 'r_country_code', $r_country_code);
    $target_url = $_POST['target_url'];
    update_post_meta($post_id, 'target_url', $target_url);
}