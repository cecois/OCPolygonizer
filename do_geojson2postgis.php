<?php 
include_once('config.php');
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// attempt a connection
	$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password=".$dbpsswd."'");
	if (!$dbh) {
	    die("Error in connection: " . pg_last_error());
	}
	
 
foreach (new DirectoryIterator($fb_jsons_out) as $file) {

if ( (!$file->isDot()) && (substr(strrchr($file,'.'),1) == "json") && ($file != '.DS_Store') && ($file->getFilename() != basename($_SERVER['PHP_SELF'])) ) {

	$extension = substr(strrchr($file,'.'),1); 
	$ochashfind = explode('.',$file);

$ochash = $ochashfind[0];

$doogr = shell_exec('ogr2ogr -update -append -select fbguid,name -f Postgresql PG:"host=localhost port=5432 user=ccmiller dbname=sparepo password=postgres" '.$fb_jsons_out.$file.' -nln "geogeo" -nlt MULTIPOLYGON');

// Execute the shell command 
$ogrstater = shell_exec($doogr.' > /dev/null; echo $?'); 
    
//return execute status; 
$ogrstat = trim($ogrstater);

if($ogrstat == 0){
	
	$sqlgetlast = "select max(ogc_fid) maxid from geogeo";

	$getlastresult = pg_query($dbh, $sqlgetlast);
	 if (!$getlastresult) {
	     die("Error in SQL query: " . pg_last_error());
	 } else {
			while ($row = pg_fetch_assoc($getlastresult)) {
$lastid = $row['maxid'];
// echo "ochash:".$ochash.";;;;;;".$lastid."\n\n\n";
              
$sqladdhash = "update geogeo set ochash ='".$ochash."' where ogc_fid=".$lastid;
echo $sqladdhash;
$addhash = pg_query($dbh, $sqladdhash);
 if (!$addhash) {
     die("Error in SQL query: " . pg_last_error());
}
}
}
}

	
      // if the element is a directory add to the file name "(Dir)"
      // echo ($file->isDir()) ? "(Dir) ".$file->getFilename() : $file->getFilename();
   }
}                                                                           

// Close database connection
   pg_close($dbh);
?>