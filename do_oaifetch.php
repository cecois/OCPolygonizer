<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1'); 


// attempt a connection
	$dbh = pg_connect("host=localhost dbname=sparepo user=ccmiller password=postgres'");
	if (!$dbh) {
	    die("Error in connection: " . pg_last_error());
	} 
	
	
// load from local -- FOR DEBUGGING AND DEV ONLY
$reposxml = fopen("oai.xml", "r");	
	
	// $url = "http://docs.lib.purdue.edu/cgi/gateway.cgi?version=1.1&ancestor.link=http://docs.lib.purdue.edu/jtrp/&query=date=2010&maximumRecords=1000";
	  
    // create curl resource
    // $ch = curl_init();

    // set url
//     curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_HEADER, 0);

    //return the transfer as a string
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string
	// echo "doing fetch of oai or other... \n";
    // $reposxml = curl_exec($ch);

    // close curl resource to free up system resources
    // curl_close($ch);


	
// load the initial record dump in xml 
$respox = simplexml_load_file('oai.xml');                 

$respox->registerXPathNamespace('srw', 'http://www.loc.gov/zing/srw/' );
$respox->registerXPathNamespace('srw_dc', 'info:srw/schema/1/dc-schema' );
$respox->registerXPathNamespace('a', 'http://purl.org/dc/elements/1.1/' );
$respox->registerXPathNamespace('a1', 'http://www.loc.gov/zing/cql/xcql/' );

// $namespaces = $respox->getNamespaces();
// var_dump($namespaces); 

// pick up array of records
$records = $respox->xpath('//srw:searchRetrieveResponse/srw:records/srw:record');
 
// we start an array to make the db insert go smoother later
// $sqlvalues = array('origurl'=>'');
$sqlvalstring = '';

foreach($records as $record){   
	
	$record->registerXPathNamespace('srw', 'http://www.loc.gov/zing/srw/' );
	$record->registerXPathNamespace('srw_dc', 'info:srw/schema/1/dc-schema' );
	$record->registerXPathNamespace('a', 'http://purl.org/dc/elements/1.1/' );
	 
$rtitle = $record->xpath('srw:recordData/srw_dc:dc/a:title/text()');
	 
$rid = $record->xpath('srw:recordData/srw_dc:dc/a:identifier/text()'); 


$sql2go = "insert into oc_inco(origurl,origxml) values('".$rid[0]."','".$record->asXML()."');";

// echo $sql2go."\n\n\n\n";
$result = pg_query($dbh, $sql2go);
 if (!$result) {
     die("Error in SQL query: " . pg_last_error());
 } else {echo '';}

}

// foreach($rids as $rid){
// 	$ridsarr[] = $rid;
// 	$sql_ruvalstringfull .= "('".$rid."'),";
// }                                           

// $sql_ruvalstring = substr($sql_ruvalstringfull, 0, -1).";";
// 
// echo $sql_blovalstringfull."\n";

// $sql2go = "insert into oc_inco(origurl) values".$sql_ruvalstring."\n";
// $sql2go = "select count(ru) from oc_raw";  

// echo $sql2gox;

// $sql = "INSERT INTO Countries (CountryID, CountryName) VALUES('$code', '$name')";
 // $result = pg_query($dbh, $sql2go);
 // if (!$result) {
 //     die("Error in SQL query: " . pg_last_error());
 // } else {echo '';} 

// free memory
	pg_free_result($result);

	// close connection
	pg_close($dbh);
?>