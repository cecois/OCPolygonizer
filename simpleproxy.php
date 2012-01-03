<?PHP

// Script: Simple PHP Proxy: Get external HTML, JSON and more!
//
// *Version: 1.6, Last updated: 1/24/2009*
// 
// Project Home - http://benalman.com/projects/php-simple-proxy/
// GitHub       - http://github.com/cowboy/php-simple-proxy/
// Source       - http://github.com/cowboy/php-simple-proxy/raw/master/ba-simple-proxy.php
// 
// About: License
// 
// Copyright (c) 2010 "Cowboy" Ben Alman,
// Dual licensed under the MIT and GPL licenses.
// http://benalman.com/about/license/
// 
// About: Examples
// 
// This working example, complete with fully commented code, illustrates one way
// in which this PHP script can be used.
// 
// Simple - http://benalman.com/code/projects/php-simple-proxy/examples/simple/
// 
// About: Release History
// 
// 1.6 - (1/24/2009) Now defaults to JSON mode, which can now be changed to
//       native mode by specifying ?mode=native. Native and JSONP modes are
//       disabled by default because of possible XSS vulnerability issues, but
//       are configurable in the PHP script along with a url validation regex.
// 1.5 - (12/27/2009) Initial release
// 
// Topic: GET Parameters
// 
// Certain GET (query string) parameters may be passed into ba-simple-proxy.php
// to control its behavior, this is a list of these parameters. 
// 
//   url - The remote URL resource to fetch. Any GET parameters to be passed
//     through to the remote URL resource must be urlencoded in this parameter.
//   mode - If mode=native, the response will be sent using the same content
//     type and headers that the remote URL resource returned. If omitted, the
//     response will be JSON (or JSONP). <Native requests> and <JSONP requests>
//     are disabled by default, see <Configuration Options> for more information.
//   callback - If specified, the response JSON will be wrapped in this named
//     function call. This parameter and <JSONP requests> are disabled by
//     default, see <Configuration Options> for more information.
//   user_agent - This value will be sent to the remote URL request as the
//     `User-Agent:` HTTP request header. If omitted, the browser user agent
//     will be passed through.
//   send_cookies - If send_cookies=1, all cookies will be forwarded through to
//     the remote URL request.
//   send_session - If send_session=1 and send_cookies=1, the SID cookie will be
//     forwarded through to the remote URL request.
//   full_headers - If a JSON request and full_headers=1, the JSON response will
//     contain detailed header information.
//   full_status - If a JSON request and full_status=1, the JSON response will
//     contain detailed cURL status information, otherwise it will just contain
//     the `http_code` property.
// 
// Topic: POST Parameters
// 
// All POST parameters are automatically passed through to the remote URL
// request.
// 
// Topic: JSON requests
// 
// This request will return the contents of the specified url in JSON format.
// 
// Request:
// 
// > ba-simple-proxy.php?url=http://example.com/
// 
// Response:
// 
// > { "contents": "<html>...</html>", "headers": {...}, "status": {...} }
// 
// JSON object properties:
// 
//   contents - (String) The contents of the remote URL resource.
//   headers - (Object) A hash of HTTP headers returned by the remote URL
//     resource.
//   status - (Object) A hash of status codes returned by cURL.
// 
// Topic: JSONP requests
// 
// This request will return the contents of the specified url in JSONP format
// (but only if $enable_jsonp is enabled in the PHP script).
// 
// Request:
// 
// > ba-simple-proxy.php?url=http://example.com/&callback=foo
// 
// Response:
// 
// > foo({ "contents": "<html>...</html>", "headers": {...}, "status": {...} })
// 
// JSON object properties:
// 
//   contents - (String) The contents of the remote URL resource.
//   headers - (Object) A hash of HTTP headers returned by the remote URL
//     resource.
//   status - (Object) A hash of status codes returned by cURL.
// 
// Topic: Native requests
// 
// This request will return the contents of the specified url in the format it
// was received in, including the same content-type and other headers (but only
// if $enable_native is enabled in the PHP script).
// 
// Request:
// 
// > ba-simple-proxy.php?url=http://example.com/&mode=native
// 
// Response:
// 
// > <html>...</html>
// 
// Topic: Notes
// 
// * Assumes magic_quotes_gpc = Off in php.ini
// 
// Topic: Configuration Options
// 
// These variables can be manually edited in the PHP file if necessary.
// 
//   $enable_jsonp - Only enable <JSONP requests> if you really need to. If you
//     install this script on the same server as the page you're calling it
//     from, plain JSON will work. Defaults to false.
//   $enable_native - You can enable <Native requests>, but you should only do
//     this if you also whitelist specific URLs using $valid_url_regex, to avoid
//     possible XSS vulnerabilities. Defaults to false.
//   $valid_url_regex - This regex is matched against the url parameter to
//     ensure that it is valid. This setting only needs to be used if either
//     $enable_jsonp or $enable_native are enabled. Defaults to '/.*/' which
//     validates all URLs.
// 
// ############################################################################

// Change these configuration options if needed, see above descriptions for info.
$enable_jsonp    = false;
$enable_native   = true;
$valid_url_regex = '/.*/';
$hash=$_GET['hash'];
// ############################################################################

$url = $_GET['url'];

if ( !$url ) {
  
  // Passed url not specified.
  $contents = 'ERROR: url not specified';
  $status = array( 'http_code' => 'ERROR' );
  
} else if ( !preg_match( $valid_url_regex, $url ) ) {
  
  // Passed url doesn't match $valid_url_regex.
  $contents = 'ERROR: invalid url';
  $status = array( 'http_code' => 'ERROR' );
  
} else {
  $ch = curl_init( $url );
  
  if ( strtolower($_SERVER['REQUEST_METHOD']) == 'post' ) {
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $_POST );
  }
  
  if ( $_GET['send_cookies'] ) {
    $cookie = array();
    foreach ( $_COOKIE as $key => $value ) {
      $cookie[] = $key . '=' . $value;
    }
    if ( $_GET['send_session'] ) {
      $cookie[] = SID;
    }
    $cookie = implode( '; ', $cookie );
    
    curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
  }
  
  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
  curl_setopt( $ch, CURLOPT_HEADER, true );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  
  curl_setopt( $ch, CURLOPT_USERAGENT, $_GET['user_agent'] ? $_GET['user_agent'] : $_SERVER['HTTP_USER_AGENT'] );
  
  list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', curl_exec( $ch ), 2 );
  
  $status = curl_getinfo( $ch );
  
  curl_close( $ch );
}

// Split header text into an array.
$header_text = preg_split( '/[\r\n]+/', $header );

if ( $_GET['mode'] == 'native' ) {
  if ( !$enable_native ) {
    $contents = 'ERROR: invalid mode';
    $status = array( 'http_code' => 'ERROR' );
  }
  
  // Propagate headers to response.
  foreach ( $header_text as $header ) {
    if ( preg_match( '/^(?:Content-Type|Content-Language|Set-Cookie):/i', $header ) ) {
      header( $header );
    }
  }
  $contents=str_replace("js/OpenLayers.js", "http://open.mapquestapi.com/nominatim/v1/js/OpenLayers.js", $contents);
  $contents=str_replace("js/tiles.js", "http://open.mapquestapi.com/nominatim/v1/js/tiles.js", $contents);
  $contents=str_replace("prototype-1.6.0.3.js", "http://open.mapquestapi.com/nominatim/v1/prototype-1.6.0.3.js", $contents);

//print $contents;
$curl_handle=curl_init();
if ($_GET['q'])
$curl_url="http://open.mapquestapi.com/nominatim/v1/search.php?q=".urlencode($_GET['q']);
else{
$curl_url="http://open.mapquestapi.com/nominatim/v1/search.php";
}
curl_setopt($curl_handle,CURLOPT_URL,$curl_url);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
$html=curl_exec($curl_handle);
$newhtml = str_replace("details.php", "http://localhost/simpleproxy.php?mode=native&q=".urlencode($_GET['q'])."&url=http://open.mapquestapi.com/nominatim/v1/details.php", $html);
$newhtml = str_replace('<form action="http://open.mapquestapi.com/nominatim/v1/search.php" method="get">','<form action="http://localhost/simpleproxy.php?mode=native&url=http://open.mapquestapi.com/nominatim/v1/search.php" method="get"><input id="url" name="url" value="null" hidden=hidden><input id="mode" name="mode" value="native" hidden=hidden>' , $newhtml);

curl_close($curl_handle);
  $doc2 = new DOMDocument();
$doc2->loadHTML($contents);
$xpath = new DOMXpath($doc2);
$tags = $xpath->query('//div[@class="locationdetails"]/div');
foreach ($tags as $tag) {
if(strpos($tag->nodeValue, 'OSM:')===0){
$relation=preg_split('/ /',$tag->nodeValue);
$relation[2];
include "config.php";
 $dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
        if (!$dbh) {
            die("Error in connection: " . pg_last_error());
        }
$sqlgetsgeo = "select name,osm_id,st_asgeojson(st_transform(way,4326)) as the_geom from planet_osm_polygon where osm_id=-".$relation[2].";";
$result = pg_query($dbh, $sqlgetsgeo);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 } else {    
       
    $geojson = array(
      'type'      => 'FeatureCollection',
      'features'  => array()
   );
	$m=0;
	while ($row = pg_fetch_assoc($result)) {
	$m++;

       $feature = array(
         'type' => 'Feature',
         'geometry' => json_decode($row['the_geom'], true),
         'crs' => array(
            'type' => 'EPSG',
            'properties' => array('code' => '4326')
         ),
         'properties' => array(
            'id' => $row['oid'],
			'name'=>$row['name']
         )
      );


		// Add feature array to feature collection array
	      array_push($geojson['features'], $feature);
	}

	$htmlfile = fopen('osm.html', 'w') or die("can't open html file");
	$htmlcontent="<html><head><title>Leaflet GeoJSON example</title><link rel='stylesheet' href='http://leaflet.cloudmade.com/dist/leaflet.css'><script src='http://leaflet.cloudmade.com/dist/leaflet.js'></script><script src='http://leaflet.cloudmade.com/examples/sample-geojson.js' type='text/javascript'></script></head><body>
	<div id='map' style='width: 600px; height: 400px'></div>

	<script>

		var map = new L.Map('map');
	
		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/22677/256/{z}/{x}/{y}.png',
			cloudmadeAttribution = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade',
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});
	
		map.setView(new L.LatLng(39.77, -86.16), 3).addLayer(cloudmade);
		
		var BaseballIcon = L.Icon.extend({
			iconUrl: 'http://leaflet.cloudmade.com/examples/baseball-marker.png',
			shadowUrl: null,
			iconSize: new L.Point(32, 37),
			shadowSize: null,
			iconAnchor: new L.Point(14, 37),
			popupAnchor: new L.Point(2, -32)
		});
		    var geojsonLayer = new L.GeoJSON(".json_encode($geojson).");
   		map.addLayer(geojsonLayer);

				

		
		geojsonLayer.on('featureparse', function (e) {
		    var popupContent = 'I am a Leaflet vector';
		    if (e.geometryType == 'Point') {
		        popupContent += '<p>This GeoJSON Point has been transformed into a <a href=\'http://leaflet.cloudmade.com/reference.html#circlemarker\'>CircleMarker by passing a <code>pointToLayer function in the <a href=\'http://leaflet.cloudmade.com/reference.html#geojson-options\'>GeoJSON options when instantiating the GeoJSON layer. View source for details.';
		    }
		    if (e.properties && e.properties.popupContent) {
		        popupContent += e.properties.popupContent;
		    }
		    e.layer.bindPopup(popupContent);
		    if (e.properties && e.properties.style && e.layer.setStyle) {
		        e.layer.setStyle(e.properties.style);
		    }
		});
		

		
		map.addLayer(geojsonLayer)

		function copytable(){
		
				
		var xmlHttp;
		
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null)
		{
		alert ('Browser does not support HTTP Request');
		return;
		}
		
			
		url='copy_table.php?hash=".$hash."&osm_id=".$relation[2]."';
				
		xmlHttp.onreadystatechange=stateChanged; 
		xmlHttp.open('GET',url,true);
		xmlHttp.send(null);		
		function GetXmlHttpObject()
		{
			var xmlHttp=null;
			try
			{			
			xmlHttp=new XMLHttpRequest();			
			}
			catch (e)
			{			
			try
			{
			xmlHttp=new ActiveXObject('Msxml2.XMLHTTP');
			}
			catch (e)
			{
			xmlHttp=new ActiveXObject('Microsoft.XMLHTTP');
			}
			}
			return xmlHttp;
		}
		
		function stateChanged() 
		{ 
			if (xmlHttp.readyState==4)
			{ 	
				alert(xmlHttp.responseText);
			}
			
		}		

}

	
		
	</script><input type='button' onclick='copytable()' value='copy table'></body></html>";
	fwrite($htmlfile, $htmlcontent);

     
      
           pg_close($dbh);
 	

	}
	

}
}
if ($m>0){
echo $htmlcontent;
}else{
$curl_handle=curl_init();
$curl_url="http://localhost/OCPolygonizer/oc.php?table=urban&hash='".$hash."'";
curl_setopt($curl_handle,CURLOPT_URL,$curl_url);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
echo $html=curl_exec($curl_handle);
curl_close($curl_handle);


}
} else {
  
  // $data will be serialized into JSON data.
  $data = array();
  
  // Propagate all HTTP headers into the JSON data object.
  if ( $_GET['full_headers'] ) {
    $data['headers'] = array();
    
    foreach ( $header_text as $header ) {
      preg_match( '/^(.+?):\s+(.*)$/', $header, $matches );
      if ( $matches ) {
        $data['headers'][ $matches[1] ] = $matches[2];
      }
    }
  }
  
  // Propagate all cURL request / response info to the JSON data object.
  if ( $_GET['full_status'] ) {
    $data['status'] = $status;
  } else {
    $data['status'] = array();
    $data['status']['http_code'] = $status['http_code'];
  }
  
  // Set the JSON data object contents, decoding it from JSON if possible.
  $decoded_json = json_decode( $contents );
  $data['contents'] = $decoded_json ? $decoded_json : $contents;
  
  // Generate appropriate content-type header.
  $is_xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  header( 'Content-type: application/' . ( $is_xhr ? 'json' : 'x-javascript' ) );
  
  // Get JSONP callback.
  $jsonp_callback = $enable_jsonp && isset($_GET['callback']) ? $_GET['callback'] : null;
  
  // Generate JSON/JSONP string
  $json = json_encode( $data );
  
  print $jsonp_callback ? "$jsonp_callback($json)" : $json;
  
}

?>

