<?php

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
	
	function http_request_get ($address) {
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $address,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true
		));
		$output = curl_exec($ch);
		curl_close($ch);
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
		
		$fetch = @self::http_request_get(CTA_INDEX);

		preg_match_all("/<li>(.*?)<br\/>/is", $fetch, $routes);
		
		if(is_array($routes)) {

			foreach($routes[0] as $item) {
			
				preg_match("/<a href=\"(.*?)\"/is", $item, $_href);
				preg_match("/\" >(.*?)<\/a>/is", $item, $_title);
				
				$_href = str_replace("selectdirection.jsp?route=", NULL, $_href[1]);
				
				$routes_array[] = array("title" => $_title[1],
										"id" => $_href
								  );
				
			}
			
		}
		
		return $routes_array;
		
	}
	
	function get_direction () {
		
		if($_GET['route']) {
			
			$fetch = @self::http_request_get(CTA_DIRECTION.$_GET['route']);
			
			preg_match("/Selected Route: (.*?)<br\/>/is", $fetch, $_route);
			$_route = str_replace(array("\n", "\r"), NULL, $_route[1]);
			
			preg_match_all("/<li>(.*?)<br\/>/is", $fetch, $directions);
			
			if(is_array($directions)) {
				
				foreach($directions[0] as $item) {
					
					preg_match("/<a href=\"(.*?)\"/is", $item, $_href);
					preg_match("/&direction=(.*?)\"/is", $_href[0], $_dir_slug);
					preg_match("/\">(.*?)<\/a>/is", $item, $_title);
					
					$dir_array[] = array("route" => $_GET['route'],
										 "title" => $_title[1],
										 "slug" => $_dir_slug[1]
								   );
					
				}
				
			}
			
			return array("route" => $_route,
						 "directions" => $dir_array);
			
		}
		
	}
	
	function get_stops () {
		
		if($_GET['route'] && $_GET['direction']) {
			
			/* Setup the API URI */
			$api = CTA_STOPS;
			$api = str_replace("__ROUTE__", $_GET['route'], $api);
			$api = str_replace("__DIRECTION__", urlencode($_GET['direction']), $api);

			$fetch = @self::http_request_get($api);
			
			preg_match("/Selected Route: (.*?)<br\/>/is", $fetch, $_route);
			$_route = str_replace(array("\n", "\r"), NULL, $_route[1]);
			
			preg_match("/Selected Direction: (.*?)<\/font>/is", $fetch, $_direction);
			$_direction = str_replace(array("\n", "\r"), NULL, $_direction[1]);
			
			preg_match_all("/<li>(.*?)<br\/>/is", $fetch, $stops);
			
			if(is_array($stops)) {
				
				foreach ($stops[0] as $item) {
					
					preg_match("/<a href=\"(.*?)\"/is", $item, $_href);
					preg_match("/\">(.*?)<\/a>/is", $item, $_title);
					
					$_get_array = explode("&", str_replace("eta.jsp?", NULL, $_href[1]));
					
					if(is_array($_get_array)) {
						foreach($_get_array as $item) {
							$e = explode("=", $item);
							$_get[$e[0]] = $e[1];
						}
					}
					
					$stops_array[] = array("title" => $_title[1],
										   "uri" => array(
										   		"route" => urlencode($_get['route']),
												"direction" => urlencode($_get['direction']),
												"stop" => urlencode($_get['stop']),
												"id" => $_get['id']
										   ));
										
					unset($_get_array, $_get);
					
				}
				
			}
			
			return array("route" => $_route,
						 "direction" => $_direction,
						 "stops" => $stops_array);
			
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
			
			$fetch = @self::http_request_get($api);
			
			/* Strip spaces */
			$fetch = str_replace(array("\n", "\r", "\t"), NULL, $fetch);
			
			/* KIll off excess */
			preg_match("/<hr><p\/><font size=\"\+0\">(.*?)<\/body><\/html>/is", $fetch, $_kill);
			$fetch = str_replace($_kill[0], NULL, $fetch);
			
			/* Get title */
			preg_match("/<title>(.*?) - Estimated Arrival Times/is", $fetch, $_title);
			
			/* Get currently */
			preg_match("/<br\/>Currently: (.*?)<br\/><\/font>/is", $fetch, $_currently);
			
			/* Fix currently */
			if(substr($_currently[1], -1, 1) == "F") {
				$_currently[1] = substr($_currently[1], 0, -2);
				$_currently[1] .= "&deg; F";
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
						 "currently" => $_currently[1],
						 "etas" => $eta_array,
						 "closest" => $eta_array[0]['bus_number']
						 );
			
		}
		
	}
	
	function build_map_for_route ($route, $bus = NULL) {
		
		$fetch = @self::http_request_get(CTA_MAP.$route);
		
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
				
					$map_uri = GMAPS_URI."?center=1&zoom=".GMAPS_ZOOM."&size=".GMAPS_SIZE."&maptype=mobile&markers=".$busses_by_id[$bus]['latitude'].",".$busses_by_id[$bus]['longitude']."&key=".GMAPS_KEY;
					
					$plot = array("latitude" => $busses_by_id[$bus]['latitude'],
								  "longitude" => $busses_by_id[$bus]['longitude']);
				
				} else {
				
					/* Form a markers string for gmaps */
					if(is_array($busses)) {
						$markers_all .= $item['latitude'].",".$item['longitude']."%7C";
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