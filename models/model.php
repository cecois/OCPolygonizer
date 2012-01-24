<?php
class Model {  

	public function __construct()
	{
	      
	}

	public function searchDB()
	{
	include "/var/config/config.php";

	$dbh2 = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password=".$dbpsswd);
        if (!$dbh2) {
            die("Error in connection: " . pg_last_error());
        }
	$sql1 = "select ocurl,geonamesho,hash from oc_geo oc left join geogeo ge on oc.hash=ge.ochash where the_geom IS NOT NULL AND ge.ochash IS NULL group by 		ocurl,geonamesho,oc.hash";


	$result1 = pg_query($dbh2, $sql1);
	 if (!$result1) {
	     die("Error in SQL query: " . pg_last_error());
	 } else {
	$m=0;
	while ($row1 = pg_fetch_assoc($result1)) {		
		$places[$m][0]=$row1['geonamesho'];
		$places[$m][1]=$row1['hash'];
		$m++;
	}	
	return $places;
	}
	}

	public function searchOSM($location, $hash)
	{

		$curl_handle=curl_init();
		$curl_url="http://open.mapquestapi.com/nominatim/v1/search.php?q=".urlencode($location);
		curl_setopt($curl_handle,CURLOPT_URL,$curl_url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		return $html=curl_exec($curl_handle);

		curl_close($curl_handle);
	}

	public function searchID($url, $hash)
	{

		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$url);
 		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		$contents=curl_exec($curl_handle);
		$doc2 = new DOMDocument();
		$doc2->loadHTML($contents);
		$xpath = new DOMXpath($doc2);
		$tags = $xpath->query('//div[@class="locationdetails"]/div');
		foreach ($tags as $tag) {
			if(strpos($tag->nodeValue, 'OSM:')===0){
			$relation=preg_split('/ /',$tag->nodeValue);			
			curl_close($curl_handle);
			}
		}
		include "/var/config/config.php";
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
		if($m>0){
		return $gjson=array(
			'name'=> $relation[2],
			'geo'=>json_encode($geojson));
		}else{
		return $gjson="no_record";
		}
		}
		}
		public function copydata($oid, $hash)
		{			
			include "/var/config/config.php";
			$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
				if (!$dbh) {
				    die("Error in connection: " . pg_last_error());
				}		

			$insertsql="insert into geogeo (geomso, ochash, wkb_geometry) select 1,'".$hash."', way from planet_osm_polygon where osm_id=-".$oid." order by way_area limit 1";
			$result = pg_query($dbh, $insertsql);
			pg_close($dbh);

		}

		public function searchUrban($hash){
			include "/var/config/config.php";
			$dbh2 = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password=".$dbpsswd);
				if (!$dbh2) {
				    die("Error in connection: " . pg_last_error());
				}		
			$sql1 = "SELECT * from oc_geo where hash='".$hash."'";
			$result1 = pg_query($dbh2, $sql1);
			 if (!$result1) {
			     die("Error in SQL query: " . pg_last_error());
			 } else {
				$m=0;
				while ($row1 = pg_fetch_assoc($result1)) {
				$rid[$m]=$row1['rid'];
				$m++;
				}
			return $rid;
			}
		}

		public function dispUrban($rid){

			include "/var/config/config.php";
			$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
				if (!$dbh) {
				    die("Error in connection: " . pg_last_error());
				}
			$sql2 = "SELECT gid, st_asgeojson(st_transform(the_geom,4326)) as the_geom from urban WHERE ST_Intersects(urban.the_geom,(SELECT setsrid(the_geom,4326) from oc_geo where rid=".$rid."))";
			$result = pg_query($dbh, $sql2);
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
				    'id' => $row['gid']			
				 )
			      );

				$gid=$row['gid'];
				array_push($geojson['features'], $feature);
				}
				if($m>0){
					return $geojson=array(
						'gid'=>$gid,
						'geo'=>json_encode($geojson));
					}else{
					return $geojson="no_record";
					}
				}
			}		

	public function copyUrban($uid, $hash){

			include "/var/config/config.php";
			$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
				if (!$dbh) {
				    die("Error in connection: " . pg_last_error());
				}

			$insertsql="insert into geogeo (geomso, ochash, wkb_geometry) select 2,'".$hash."', the_geom from urban where gid=".$uid;
			$result = pg_query($dbh, $insertsql);
	}

	public function dispFlicker($rid){

			include "/var/config/config.php";
			$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
				if (!$dbh) {
				    die("Error in connection: " . pg_last_error());
				}

			$sql2 = "SELECT woe_id, st_asgeojson(st_transform(wkb_geometry,4326)) as the_geom from localities WHERE ST_Intersects(localities.wkb_geometry,(SELECT setsrid(the_geom,4326) from oc_geo where rid=".$rid."))";


			$result = pg_query($dbh, $sql2);
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
				    'id' => $row['woe_id']			
				 )
			      );

				$woe_id=$row['woe_id'];
				      array_push($geojson['features'], $feature);
				}
				if($m>0){
					return $geojson=array(
						'gid'=>$woe_id,
						'geo'=>json_encode($geojson));
					}else{
					return $geojson="no_record";
					}
			}
		}

			public function copyFlicker($uid, $hash){

					include "/var/config/config.php";
					$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
						if (!$dbh) {
						    die("Error in connection: " . pg_last_error());
						}

					$insertsql="insert into geogeo (geomso, ochash, wkb_geometry) select 3,'".$hash."', wkb_geometry from localities where woe_id=".$uid;
					$result = pg_query($dbh, $insertsql);
			}
} 
?>
