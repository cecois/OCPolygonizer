<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1'); 

// bring in some settings  
include_once('config.php');

$url = "http://api1.opencalais.com/enlighten/calais.asmx/Enlighten";
$contentType = "text/txt";

mkdir($oc_xmls_out, 0700); 

// loop over all the pdfs - currently we have to expect these to be sucked down as files already (returnto)
foreach (new DirectoryIterator($chunked_txt_out) as $repofile) {
   // if the file is not this file, and does not start with a '.' or '..',
if ( (!$repofile->isDot()) && (substr(strrchr($repofile,'.'),1) == "txt") && ($repofile != '.DS_Store') && ($repofile->getFilename() != basename($_SERVER['PHP_SELF'])) ) {

// returnto - need some logic here to verify it's a good OC result  



	// set some values for dumping out to file later
	$xmlfilenom = preg_replace("/\.txt$/", "", $repofile);
	$xmlo2xpthpth = $oc_xmls_out.$xmlfilenom.".xml";                  

	
	// returnto - this is brute forcing the filesize
	//      $rawtot = file_get_contents($cfg_infiledir.$repofile);
	// $raw = substr($rawtot,0,59999);  

	
// grab the contents  
// $rawin = $chunked_txt_out.$repofile;
$raw = file_get_contents($rawin); 


// 
// 
// $logpth = fopen($cfg_infiledir."log.txt","w+");
// echo "logging to ".$logpth."... \n";
// fwrite($logpth,$raw);
// fclose($logpth);
// die(); 

                       
// returnto - this is currently dumb because we can't actually dl straight from Berkeley Press instance
 $repourl = $repourl.htmlentities($repofile);                  

// params block that controlls oc output
$paramsXML = '<c:params xmlns:c="http://s.opencalais.com/1/pred/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><c:processingDirectives c:discardMetadata="er/Company;er/Product" c:contentType="'.$contentType.'" c:outputFormat="XML/RDF" calculateRelevanceScore="true" reltagBaseURL="true"></c:processingDirectives><c:userDirectives c:allowDistribution="false" c:allowSearch="false" c:externalID="'.$repourl.'" c:submitter="CCMPURDUE"></c:userDirectives><c:externalMetadata/></c:params>';

$data = "licenseID=".urlencode($apiKey);
$data .= "&paramsXML=".urlencode($paramsXML);
$data .= "&content=".urlencode($raw);

// the curl procedure 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_POST, 1);
echo "doing opencalais... \n";
$response = curl_exec($ch);
date_default_timezone_set ("America/Indianapolis");
// $now = time();
// $filnom = $filein."_".$now;       


// just dump it out to xml of same name
$xmlo2xpth = fopen($xmlo2xpthpth,"w+");
echo "writing results to ".$xmlo2xpthpth."... \n";
fwrite($xmlo2xpth,html_entity_decode($response));
fclose($xmlo2xpth);
       
//                    let's go easy on OC
// echo "resting... \n";
// sleep(2);
              

}} // end the DirectoryIterator

// repourl will be used by oc_xpath
// global $repourl;

// ----------------------------------- end  

?>               

<?php 
// returnto - pretty much obsolete now
function countchar ($string) { 

$result = strlen ($string)  -   substr_count($string, ' '); 
return $result;  
} 

countchar ($a); 
?>