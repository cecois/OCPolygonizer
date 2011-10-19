<?php
include_once('config.php');

// // first of all, have pdftotext process all of the pdf files we've identified in config
// include_once('do_pdftotext.php');
// include_once('do_oc_simple.php'); 
?>
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1'); 


	// loop over all the xmls - currently we have to expect these to be sucked down as files already (returnto)
	foreach (new DirectoryIterator($oc_xmls_concd) as $repofile) {
	   // if the file is not this file, and does not start with a '.' or '..',
	if ( (!$repofile->isDot()) && (substr(strrchr($repofile,'.'),1) == "xml") && ($repofile != '.DS_Store') && ($repofile->getFilename() != basename($_SERVER['PHP_SELF'])) ) {


// returnto - original idea was to sock away the full URI of the parent document (pdf prob.)
// because we r building this against bepress, which masks the download urls, we don't really have it
// so this little bit simply sets the repourl to be the local filename of the source document with no path context
$rurl = preg_replace("/\.xml$/", "", $repofile).".pdf";
$repourl = $rurl;			

// load some xml 
$respox = simplexml_load_file($oc_xmls_concd.$repofile); 

echo "doing oc xpath stuff... \n";

// register the namespace with some prefix, in this case 'c'
$respox->registerXPathNamespace('c', 'http://s.opencalais.com/1/pred/' );
$respox->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#' );

// pick up array of Description elements (the main return from OC)
$entyps = $respox->xpath('//rdf:Description');
                   
// we start an array we will populate with stuff for insert into pg
$gotopgArr = array('geonamesho'=>'','geonamelon'=>'', 'geolat'=>'', 'geolng'=>'', 'ocurl'=>'', 'hash'=>'','detection'=>'', 'score'=>'');


// for each found
foreach ($entyps as $entypch)
{         

// we start an array we will populate with stuff for insert into pg
// $gotopgArr = array('geonamesho'=>'','geonamelon'=>'', 'geolat'=>'', 'geolng'=>'', 'ocurl'=>'', 'hash'=>'','detection'=>'', 'score'=>'');


// prepare to check the specific type of Description   
  $entyp = end($entypch->xpath('rdf:type/@rdf:resource'));

// we're looking at the last two pieces of the rdf:resource URL
// so we explode it on slashes
$urlp = explode("/",$entyp);
// grab the last one - it is our true type
  $entypactu = end($urlp);                
// we will now test it next to its prev
  $entypcontxt = prev($urlp);
  $entypis = $entypcontxt.'/'.$entypactu;

  
// now we have the type and we'll look for two possibilities
switch ($entypis) {
	// is it a geo of either generic flavor?
	case 'e/City':
	case 'e/ProvinceOrState':
// then grab the hash, which becomes a unique id of sorts	
	$thishasha = end($entypch->xpath('@rdf:about'));
   $thishash = getHash($thishasha);

	// we're about to shop that hash around to find legitimate Geo entries from OC
	// presuming we won't find any, though, we'll start populating the topg array
	// and anything we do end up locating in a real Geo entry will just overwrite
	 $shoname = end($entypch->xpath('c:name'));
	$gotopgArr['geonamesho'] = $shoname[0];
	$geohasha = end($entypch->xpath('@rdf:about'));
					$geohash = getHash($geohasha);
	$gotopgArr['hash'] = $geohash;

	$hurl = end($entypch->xpath('@rdf:about'));
	$gotopgArr['ocurl'] = $hurl[0];
	
// great, but now let's go see if there's a Description element
// whose subject is our dear little hash	
	getLegit($respox,$thishash,'Geo');		

// whatever happened happened (we either found a legit Geo and wrote its values or 
	// we'll just keep the dumb ones we already stored)

//either way we now shop the hash around for detection and relevance entries
// that are about our hash
	getLegit($respox,$thishash,'InstanceInfo'); 
		getLegit($respox,$thishash,'RelevanceInfo');
 
		break;
	
	// not a geo - who cares then?
	default:
	unset($gotopgArr);
		break;
		

} // end etypcontxt switch 


// attempt a connection 
	$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
	if (!$dbh) {
	    die("Error in connection: " . pg_last_error());
	}                    
$sqlready = doPg($gotopgArr,$repourl);
if(isset($sqlready)){
// echo "sqlready:::::".$sqlready."\n";


// execute query
     // $sql = "INSERT INTO Countries (CountryID, CountryName) VALUES('$code', '$name')";
     $result = pg_query($dbh, $sqlready);
     if (!$result) {
         die("Error in SQL query: " . pg_last_error());
     } else {echo "inserted 1 \n";}
     
   
}

	
} //end entyps as entypch
// free memory
	pg_free_result($result);
	
	// close connection
	pg_close($dbh);
	
}} // end the DirectoryIterator

?>                        
<?php
function getHash($hasha){
	// just a quick thing to piece out a hash from a url
  $thehash = end(explode("/",$hasha[0]));
return $thehash;
}
function getLegit($inxml,$indumbhash,$which){
global $gotopgArr;

// take the whole incoming xml (again)	
// and loop through the Descriptions (again)
// this time looking for only those whose subjects match the incoming hash
	$submatches = $inxml->xpath('//rdf:Description/c:subject[contains(@rdf:resource,"'.$indumbhash.'")]');
	                 
	// for each one we find that matches our hash...
	foreach($submatches as $submatch){
		
		switch ($which) {
			// if this is a Geo request
			case 'Geo':
			// check if the current Descript is a legit OC Geo
			  if ($submatch->xpath('preceding-sibling::rdf:type[contains(@rdf:resource,"/Geo")]'))
			{
				
				//grab what we need from current
				$conname = end($submatch->xpath('following-sibling::c:name'));
				$gotopgArr['geonamelon'] = $conname[0];

				$shoname = end($submatch->xpath('following-sibling::c:shortname'));
				$gotopgArr['geonamesho'] = $shoname[0];
				
				$geolat = end($submatch->xpath('following-sibling::c:latitude'));
				$gotopgArr['geolat'] = $geolat[0];
				
				$geolng = end($submatch->xpath('following-sibling::c:longitude'));
				$gotopgArr['geolng'] = $geolng[0];
				
				$geohasha = $submatch->xpath('../@rdf:about');
								$geohash = getHash($geohasha);
				$gotopgArr['hash'] = $geohash;

				$hurl = end($submatch->xpath('../@rdf:about'));
				$gotopgArr['ocurl'] = $hurl[0];
					
			}
			
				break;
				 case 'InstanceInfo':
					  if ($submatch->xpath('preceding-sibling::rdf:type[contains(@rdf:resource,"/InstanceInfo")]'))
					{ 
						//grab what we need from current
						$detection = end($submatch->xpath('following-sibling::c:detection'));
						
						// returnto - need something here that
						$detection = addslashes($detection);
						$gotopgArr['detection'] .= $detection.":::::";

					}
					break;
					case 'RelevanceInfo':
					  if ($submatch->xpath('preceding-sibling::rdf:type[contains(@rdf:resource,"/RelevanceInfo")]'))
					{ 
						//grab what we need from current
						$relv = end($submatch->xpath('following-sibling::c:relevance'));
						$gotopgArr['score'] = $relv; 

					}
					break;
			default:
			// none
				break;
		}

		
	}   //end submatches as submatch                            
	
	
}   // end getlegit 

function doPg($pgarr,$repourl){ 
 
	
	$stuffinthere = checkEmpty($pgarr);
	if($stuffinthere > 0){ 
		
// var_dump($pgarr);
if(isset($pgarr['geolng'][0]) && isset($pgarr['geolat'][0])){
	$therebgeo = 1;
$wkt = "ST_GeomFromText('POINT(".$pgarr['geolng'][0]." ".$pgarr['geolat'][0].")', 4326),";	
}

$sql = "insert into oc_geo(geonamesho, ";
if($therebgeo == 1){
	$sql .= "the_geom,";
}
$sql .= "geonamelon, ocurl,hash,detection,score,ru) values ('".$pgarr['geonamesho'][0]."',";

if($therebgeo == 1){
 $sql .= $wkt;
}


$sql .= "'".$pgarr['geonamelon'][0]."','".$pgarr['ocurl'][0]."','".$pgarr['hash']."','".$pgarr['detection']."',".$pgarr['score'][0].",'".$repourl."');";
       

return $sql;

	}
}                     
function checkEmpty($array){
return (count($array)>0)?1:0;
}
?>