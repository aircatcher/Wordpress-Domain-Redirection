<?php
# @package: Custom IP Redirect
// require_once dirname(__DIR__). '/' . 'includes/autoload.php';

add_action('wp_enqueue_style', 'register_styles');
function register_styles()
{
    wp_register_style('my-stylesheet', plugin_url('/css/style.css'));
    wp_enqueue_style ('my-stylesheet');
}

/**
*  Kreacio Redirection Meta Box
**/
function read_CSV($csv_file)
{
  $row = 1;
  $csv_file = dirname(__DIR__).'/includes/GeoIPCountry.csv';
	if (($handle = fopen($csv_file, "r")) !== FALSE)
	{
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
		{
			$num = count($data);
			echo "<p> $num fields in line $row: <br /></p>\n";
	    $row++;
	    for ($c=0; $c < $num; $c++)
	    {
	      echo $data[$c] . "<br />\n";
	    }
	  }
	  fclose($handle);
	}
}

add_action('add_meta_boxes', 'kr_meta_box');
function kr_meta_box($post)
{
  add_meta_box(
		'meta_box_id',
		'Kreacio Redirection',
		'kr_meta_box_cb',
		$post->post_type,
		'normal',
		'high');
}
add_action('save_post', 'kreacio_redirection_save');
function kreacio_redirection_save()
{
	global $post;
  if(isset($_POST["r_country_code"]))
  {
    //UPDATE: 
    $meta_dropdown = $_POST['r_country_code'];
    //END OF UPDATE

    update_post_meta($post->ID, 'kr_meta_box_cb', $meta_dropdown);
    //print_r($_POST);
  }
  if(isset($_POST["target_url"]))
  {
    //UPDATE: 
    $meta_textfield = $_POST['target_url'];
    //END OF UPDATE

    update_post_meta($post->ID, 'kr_meta_box_cb', $meta_textfield);
    //print_r($_POST);
  }
}
function kr_meta_box_cb($post)
{
	$meta_dropdown  = get_post_meta($post->ID, 'kr_meta_box_cb', true);
	$meta_textfield = get_post_meta($post->ID, 'kr_meta_box_cb', true);
	wp_nonce_field(plugin_basename(__FILE__), 'kreacio_redirection_nonce');

	/** MaxMind GEO IP **/
	if (!function_exists('geoip_open')) require_once 'geoip.inc.php';
	$ip_addr = $_SERVER['REMOTE_ADDR'];
	$mmdb    = dirname(__DIR__) . '/' . 'includes/GeoLite2-Country.mmdb';
	$reader  = new Reader($mmdb);
	$ip_info = $reader->get($ip_addr);
	// $gi = geoip_open(dirname(__DIR__).'/includes/GeoLiteCountry.dat', GEOIP_STANDARD);
	// // $ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
	// $ip = $_SERVER['REMOTE_ADDR'];
	// $reader = geoip_country_code_by_addr($gi, $ip);
	// geoip_close($gi);

	// $csv_file = dirname(__DIR__).'/includes/GeoIPCountry.csv';
	$countries = read_CSV();

	// print into <pre>array</pre>
	// echo '<pre>';
	// // print_r($reader->get($ip_info));
	// print_r($countries);
	// echo '</pre>';

	$value = get_post_meta($post->ID, '_my_meta_value_key', true);

	list($country_id, $country_name) = $countries;
	echo	'<form action="" method="POST">';
	echo		'<table id="kr_table_layout">';
	echo			'<tr>';
	echo				'<td id="meta_box_css">Choose country </td>';
	echo				'<td>: ';
	echo					'<select name="r_country_code" id="r_country_code">';
								?><option <?php selected($meta_dropdown, ''); ?>>DEFAULT REDIRECTION</option><?php
								$i = 0;
								foreach($countries as $country)
								{
									?><option <?php selected($meta_dropdown, '<?php echo $countries[100][0]; ?>');
									if($i <= 2) { echo 'style="text-transform: uppercase;"'; }
	echo						'>';
	echo						$countries[$i][1];
	echo						'</option>';
									if($i == 2)
									{
									?><option <?php selected($meta_dropdown, 'country-heading'); ?> disabled><?php
	echo							'------------------- Countries -------------------</option>';
									}
									$i++;
								}
	echo					'</select>';
	echo				'</td>';
	echo			'</tr>';
	echo			'<tr>';
	echo				'<td id="meta_box_css">Target URL </td>';
	echo				'<td>: <input type="URL" id="target_url_box" name="target_url" placeholder="http://www.TargetURL.com/" value="'. $meta_textfield .'" required/></td>';
	echo			'</tr>';
	echo		'</table>';
	echo	'</form>';

	echo	'<br/>';
				if($ip_addr == '::1' || $ip_addr == '127.0.0.1')
	echo 		'<i id="red_notice"><b>Notice:</b> Geolocation is not possible within localhost</i></font><br/><br/>';
	echo		'<font size="2px"><i>This product includes GeoLite data created by MaxMind, available from </i><a href="http://www.maxmind.com" target="_blank">http://www.maxmind.com</a></font>';
	// echo	'<pre>' . $ip_info . '</pre>';

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