<?php

/**
 * this file is similar to do_fb2geojson.php but does two things differently:
* 1. it grabs the freebase key from a _GET b/c it's designed to be ajax-called from a GUI where somebody 
* has selected an entity manually
*
*
* 2. it also does not write out json to file, rather sends it directly to a (hard-coded) postis table
 */
set_time_limit(60);   
	
if(isset($_GET['fbkey'])){
	
$freebkey = $_GET['fbkey'];
} else {
	// testing, hawaii
	// $freebkey = '9202a8c04000641f800000000001b9e4';
	die("no freebase key detected");
	}

if(isset($_GET['db'])){
	
	$dbname = $_GET['db'];
} else {
   	die("no db detected"); 
}
                                                                   
// shop the hash to freebase using our freebase hash 
$freeburler = "http://api.freebase.com/api/service/geosearch?location=".$freebkey."&limit=1&geometry_type=polygon,multipolygon";
		
			$chfb = curl_init();
			curl_setopt($chfb, CURLOPT_URL, $freeburler); 
			curl_setopt($chfb, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($chfb, CURLOPT_TIMEOUT, '0');
			$fbresponse = curl_exec($chfb);
			curl_close($chfb);    
			
			
// here we need some routine to check the validity of the response  
// e.g. check for empty set a la {"features":[],"type":"FeatureCollection"}
$invaliditystr1 = '{"features":[],';
$invaliditystr2 = '{"coordinates":[],';
$invaliditystr3 = '500 Server Error';
$validi1 = strpos($fbresponse,$invaliditystr1);
$validi2 = strpos($fbresponse,$invaliditystr2);
$validi3 = strpos($fbresponse,$invaliditystr3);

if(($validi1 === false) && ($validi2 === false) && ($validi3 === false)) {

// assuming valid, we proceed, sending fbresponse to postgis with ogr2ogr
$jsonfile = fopen("/tmp/tmp.json","w+");
fwrite($jsonfile,$fbresponse);
fclose($jsonfile);

$doogr = shell_exec('ogr2ogr -update -append -select fbguid,name -f Postgresql PG:"host=localhost port=5432 user=ccmiller dbname='.$dbname.' password=postgres" /tmp/tmp.json -nln "geogeo" -nlt MULTIPOLYGON');

// Execute the shell command 
$ogrstater = shell_exec($doogr.' > /dev/null; echo $?'); 
    
//return execute status; 
$ogrstat = trim($ogrstater);

if($ogrstat == 0){			 		
return "Done!";
} else {
	return "Um...No.";
	}				
}
?>