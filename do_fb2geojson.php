<?php 
set_time_limit(60);   
echo "clearing previous log \n";
// unlink($fb_2geo_log);
include_once('config.php');
// prepare some output space
// mkdir($fb_jsons_out, 0700);
// attempt a connection
	$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
	if (!$dbh) {
	    die("Error in connection: " . pg_last_error());
	}

// $ocurl = "http://d.opencalais.com/er/geo/provinceorstate/ralg-geo1/1f4482fa-8b1f-d76a-8943-1a7e3fa9bf1a";

// $sql2go = "select count(*) count, ocurl,geonamesho,hash ochash from oc_geo where the_geom IS NOT NULL group by ocurl,geonamesho,ochash;";
                                                                                         
// here we want a query that gets just those ocurls from opencalais responses
// that are valid geogs (e.g. will have freebase URIs)
// and that haven't been processed already (e.g. don't have valid entries in geogeo for given hash)

// FIRST RUN ONLY
$sql2go = "select count(*) count, ocurl,geonamesho,hash ochash from oc_geo oc where ocurl like '%/er/geo%' AND the_geom IS NOT NULL AND geonamesho like '%Paris%' group by ocurl,geonamesho,oc.hash;";


// $sql2go = "select count(*) count, ocurl,geonamesho,hash ochash from oc_geo oc join geogeo ge on oc.hash=ge.ochash where the_geom IS NOT NULL AND ge.wkb_geometry IS NULL group by ocurl,geonamesho,oc.hash;";


$result = pg_query($dbh, $sql2go);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 } else {    
	
	

 while ($row = pg_fetch_assoc($result)) {	
$ocurl = $row['ocurl'];
$ochash = $row['ochash'];
	
// we need to force oc to respond in rdf 	
			$ocurlrdf = $ocurl.".rdf";
			$choc = curl_init();
			curl_setopt($choc, CURLOPT_URL, $ocurlrdf); 
			curl_setopt($choc, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($choc, CURLOPT_TIMEOUT, '0');
			$ocresponse = curl_exec($choc);
			curl_close($choc);
			
			// load the response for real 
			$inocrdf = simplexml_load_string($ocresponse); 
			
			// RETURNTO AND KILLME - DEBUGGING ONLY
			// $inocrdf = simplexml_load_file('ocfromcurl.xml');
			
			// register the namespace with some prefix, in this case 'c'
			$inocrdf->registerXPathNamespace('owl', 'http://www.w3.org/2002/07/owl#' );
						$inocrdf->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#' );
			
			// go straight to owl:sameAs for freebase
			$sameasfreeb = end($inocrdf->xpath('//owl:sameAs[contains(@rdf:resource,"freebase")]/@rdf:resource'));                      			
			                                         
			// bust it open
			$freebkey = end(explode(".",$sameasfreeb));
                                                                   
// shop the hash to freebase using our freebase hash 
$freeburler = "http://api.freebase.com/api/service/geosearch?location=".$freebkey."&limit=1&geometry_type=polygon,multipolygon";
// $freeburler = "http://api.freebase.com/api/service/geosearch?location=9202a8c04000641f80000000040d2441&limit=1&simplify=.2&geometry_type=polygon,multipolygon";

// now prep it for pass through proxy
// $freeburl = "http://localhost/leaf4sparepo/proxy.php?url=".urlencode($freeburler)."&mode=native";		

             // here it is, a pristine url that will fetch valid geoJSON from freebase (via proxy)
		     // return $freeburl;
		
			$chfb = curl_init();
			curl_setopt($chfb, CURLOPT_URL, $freeburler); 
			curl_setopt($chfb, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($chfb, CURLOPT_TIMEOUT, '0');
			$fbresponse = curl_exec($chfb);
			curl_close($chfb);    
			
			
// here we need some routine to check the validity of the response  
// e.g. check for empty set a la {"features":[],"type":"FeatureCollection"}
			echo "checking validity of the json response \n"; 
$invaliditystr1 = '{"features":[],';
$invaliditystr2 = '{"coordinates":[],';
$invaliditystr3 = '500 Server Error';
$validi1 = strpos($fbresponse,$invaliditystr1);
$validi2 = strpos($fbresponse,$invaliditystr2);
$validi3 = strpos($fbresponse,$invaliditystr3);

if(($validi1 === false) && ($validi2 === false) && ($validi3 === false)) {

echo "assuming valid \n";
//RETURNTO - need to grab that guid so we can reference this feature after successful ogr2ogr 
// see line 111 below where we update geogeo with a geomso value - there should be a where clause there to specify a row

// assuming valid, we proceed, sending fbresponse to postgis with ogr2ogr
$jsonfile = fopen("/tmp/tmp.json","w+");
fwrite($jsonfile,$fbresponse);
fclose($jsonfile);

$doogr = shell_exec('ogr2ogr -update -append -select guid,name -f Postgresql PG:"host=localhost port=5432 user=ccmiller dbname='.$dbname.' password=postgres" /tmp/tmp.json -nln "geogeo" -nlt MULTIPOLYGON');

// Execute the shell command 
$ogrstater = shell_exec($doogr.' > /dev/null; echo $?'); 
    
//return execute status; 
$ogrstat = trim($ogrstater);
if($ogrstat == 0){
$sql_geomso = "update geogeo set geomso='fb'";			
$upd_geomso = pg_query($dbh, $sql_geomso);
 if (!$upd_geomso) {
     die("Error in SQL query: " . pg_last_error());
 } else {
	echo "updated geogeo with fb marker \n";
}
}
//if not a valid json			 
} else {
	
	echo "this one's invalid, let's log it \n";
	  $logfile = fopen($fb_2geo_log,"w+");
		fwrite($logfile,$ochash."\n");
		fclose($logfile);
} 
			 		
            
// let's go easy on freebase
			echo "resting... \n";
						sleep(2);
			
			}
 }   		
			// free memory
				pg_free_result($result);

				// close connection
				pg_close($dbh);					
?>