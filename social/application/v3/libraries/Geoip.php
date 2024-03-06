<?php defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'libraries/GeoIP/src/geoipcity.inc';

class Geoip{

	public function info($ip){
		global $GEOIP_REGION_NAME;
		$gi = geoip_open(APPPATH . 'libraries/GeoIP/data/GeoLiteCity.dat', GEOIP_STANDARD);
		$record = geoip_record_by_addr($gi, $ip);
		if(isset($record->country_code) && isset($record->region))
		{
			$record->state_name = $GEOIP_REGION_NAME[$record->country_code][$record->region];
		}
		geoip_close($gi);
		return $record;
	}

}