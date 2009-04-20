<?php

require 'simple_html_dom.php';

define(CTA_INDEX, "http://www.ctabustracker.com/bustime/wireless/html/home.jsp");
define(CTA_DIRECTION, "http://www.ctabustracker.com/bustime/wireless/html/selectdirection.jsp?route=");
define(CTA_STOPS, "http://www.ctabustracker.com/bustime/wireless/html/selectstop.jsp?route=__ROUTE__&direction=__DIRECTION__");
define(CTA_ETA, "http://www.ctabustracker.com/bustime/wireless/html/eta.jsp?route=__ROUTE__&direction=__DIRECTION__&stop=__STOP__&id=__ID__");
define(CTA_MAP, "http://www.ctabustracker.com/bustime/map/getBusesForRoute.jsp?route=");

define(CTA_NO_SERVICE_STRING, "No service is scheduled");

define(GMAPS_URI, "http://maps.google.com/staticmap");
define(GMAPS_ZOOM, "14");
define(GMAPS_SIZE, "300x350");
define(GMAPS_KEY, "ABQIAAAA8gCh7IXrFSJdmzKS3GN1mRSCy_B9jsA2HMkiadZ6GocUNCtPGBSYvqaT2z682ofGfshX_2BJlgHv0g");

class CTA {
	
	function http_request_get ($address, $selector="ul li") {
		
		$output = file_get_html($address);
		if(isset($selector))
			$output = $output->find($selector);
		return $output;
		
	}
	
	function convert_object ($object) { 
		$return = NULL;
		if(is_array($object)) {
			if(count($object) == 1) {
				$return[$key][] = self::convert_object($value);
			} else {
				foreach($object as $key => $value) {
					$return[$key] = self::convert_object($value);
				}
			}
		} else {
			$var = get_object_vars($object);
			if($var) {
				foreach($var as $key => $value) {
					$return[$key] = self::convert_object($value);
				}
			} else {
				return strval($object);
			}
		}
		return $return; 
	}
	
	function index () {
		$els = @self::http_request_get(CTA_INDEX);
		
		if(is_array($els)) {
			$routes = array();
			$routes["items"] = array();
			foreach($els as $el) {
				$url = parse_url($el->children(0)->href);
				parse_str($url['query']);
		 		array_push($routes["items"], array("name" => trim($el->plaintext),
										"route_id" => $route));
			}
			
		}
		
		return json_encode($routes);
		
	}
	
	function get_direction () {
		if($_GET['route']) {
			$els = @self::http_request_get(CTA_DIRECTION.$_GET['route']);
			$_route = $_GET['route'];
			
			if(is_array($els)) {
				$directions = array();
				$directions["route_id"] = $_GET["route"];
				$directions["items"] = array();
				foreach($els as $el) {
					$url = parse_url($el->children(0)->href);
					parse_str($url['query']);
					array_push($directions["items"], array("route_id" => $route,
										 "name" => $el->plaintext,
										 "slug" => $direction
								   ));
					
				}
				
			}
			
			return json_encode($directions);
			
		}
		
	}
	
	function get_stops () {
		
		if($_GET['route'] && $_GET['direction']) {
			
			/* Setup the API URI */
			$api = CTA_STOPS;
			$api = str_replace("__ROUTE__", $_GET['route'], $api);
			$api = str_replace("__DIRECTION__", urlencode($_GET['direction']), $api);
			$els = @self::http_request_get($api);
			
			if(is_array($els)) {
				$stops = array();
				$stops["items"] = array();
				
				foreach($els as $el) {
					$url = parse_url($el->children(0)->href);
					parse_str($url['query']);
					array_push($stops["items"], array("name" => $el->plaintext,
										   	"route_id" => $route,
											"direction" => $direction,
											"stop" => $stop,
											"id" => $id
										   ));
										
					
				}
				
			}
			
			return json_encode($stops);
			
		}
		
	}
	
	function get_eta () {
		
		if($_GET['route'] && $_GET['direction'] && $_GET['stop'] && $_GET['id']) {
			
			/* Setup the API URI */
			$api = CTA_ETA;
			$api = str_replace("__ROUTE__", urlencode($_GET['route']), $api);
			$api = str_replace("__DIRECTION__", urlencode($_GET['direction']), $api);
			$api = str_replace("__STOP__", urlencode($_GET['stop']), $api);
			$api = str_replace("__ID__", $_GET['id'], $api);
			
			$fetch = @self::http_request_get($api, null);
			
			/* Get currently */
			$currently = trim($fetch->find("p font", 1)->plaintext);
			
			/* Strip spaces */
			$fetch = str_replace(array("\n", "\r", "\t"), NULL, $fetch);
			
			/* KIll off excess */
			preg_match("/<hr><p\/><font size=\"\+0\">(.*?)<\/body><\/html>/is", $fetch, $_kill);
			$fetch = str_replace($_kill[0], NULL, $fetch);
			
			/* Get title */
			preg_match("/<title>(.*?) - Estimated Arrival Times/is", $fetch, $_title);
			
			
			/* Fix currently */
			if(substr($currently, -1, 1) == "F") {
				$currently = substr($currently, 0, -2);
				$currently .= "&deg; F";
			}
						
			if (stristr($fetch, CTA_NO_SERVICE_STRING) == false) {
			
				/* Get times */
				$_times_a = explode("<hr/>", $fetch);
				unset($_times_a[0]);
			
				/* Parse times */
				if(is_array($_times_a)) {
				
					foreach ($_times_a as $item) {
					
						preg_match("/<font size=\"\+1\"><b>(.*?)&nbsp;<\/b><\/font>/is", $item, $bus);
						preg_match("/<\/font><font size=\"\+0\">(.*?)&nbsp;<\/font>/is", $item, $to);
						preg_match("/<\/font><font size=\"\+1\"><b>(.*?)<\/b><\/font><br\/>/is", $item, $time);
						preg_match("/<\/font><br\/><font size=\"\-1\">&nbsp;\(Bus (.*?)\)<\/font>/is", $item, $bus_num);
					
						$eta_array[] = array("bus" => $bus[1],
											 "to" => $to[1],
											 "time" => $time[1],
											 "bus_number" => $bus_num[1]
									   );
					
					}
				
				}
				
			}
			
			return array("route" => $_GET['route'],
						 "title" => $_title[1],
						 "currently" => $currently,
						 "etas" => $eta_array,
						 "closest" => $eta_array[0]['bus_number']
						 );
			
		}
		
	}
	
	function build_map_for_route ($route, $bus = NULL) {
		
		$fetch = @self::http_request_get(CTA_MAP.$route, null);
		
		if($fetch) {
			
			$xml = @simplexml_load_string($fetch);
			$xml = self::convert_object($xml);
			
			/* Fix array issues */
			if(isset($xml['bus'][0])) {
				$busses = $xml['bus'];
			} else if(isset($xml['bus'])) {
				$busses = array($xml['bus']);
			}
			
			if(is_array($busses)) {
			
				/* Set up the ID's as array keys */
				if(is_array($busses)) {
					foreach ($busses as $item) {
						$busses_by_id[$item['id']] = $item;
					}
				}
			
				if($bus && $busses_by_id[$bus]) {
					$map_uri = GMAPS_URI."?center=1&zoom=".GMAPS_ZOOM."&size=".GMAPS_SIZE."&maptype=mobile&markers=".$busses_by_id[$bus]['lat'].",".$busses_by_id[$bus]['lon']."&key=".GMAPS_KEY;
					
					$plot = array("latitude" => $busses_by_id[$bus]['latitude'],
								  "longitude" => $busses_by_id[$bus]['longitude']);
				
				} else {
					/* Form a markers string for gmaps */
					if(is_array($busses)) {
						$markers_all .= $item['lat'].",".$item['lon']."%7C";
					}
				
					$markers_all = substr($markers_all, 0, -3);
				
					$map_uri = GMAPS_URI."?zoom=".GMAPS_ZOOM."&size=".GMAPS_SIZE."&maptype=mobile&markers=".$markers_all."&key=".GMAPS_KEY;
				
				}
				
				return array("map_uri" => $map_uri,
							 "plot" => $plot);
				
			}
			
		}
		
	}
	
}

?>