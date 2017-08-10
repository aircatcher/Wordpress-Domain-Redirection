<?php
# @package: Custom IP Redirect

/** MaxMind GEO IP **/
include("geoip.inc");
// include("src/geoipcity.inc");

$gi = geoip_open("GeoIP.dat", GEOIP_STANDARD);

echo geoip_country_code_by_addr($gi, "24.24.24.24") . "\t" .
     geoip_country_name_by_addr($gi, "24.24.24.24") . "\n";
   
$country_code = geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']);

geoip_close($gi);