<?php
include_once('config.php'); 
// $dir = "text";
$dir = $cfg_infiledir;   
mkdir($chunked_txt_out, 0700);
// $chunked_txt_out = $cfg_infiledir."/txts2oc/";

echo "processing text sizes..."; 
 
  	// loop over all the txts
	foreach (new DirectoryIterator($cfg_infiledir) as $txtfile) {
	   // if the file is not this file, and does not start with a '.' or '..',
	if ( (!$txtfile->isDot()) && (substr(strrchr($txtfile,'.'),1) == "txt") && ($txtfile != '.DS_Store') && ($txtfile->getFilename() != basename($_SERVER['PHP_SELF'])) ) {


  	
	
	$handle = fopen($dir.$txtfile, "rb");
	    $contents = fread($handle, filesize($dir.$txtfile));
	    $arr = str_split($contents, 20000);
	    for ($i = 0; $i < sizeof($arr); $i++) {
	    $filein = $chunked_txt_out.$txtfile.$i.'.txt';
	    $fh = fopen($filein, 'w');
	    fwrite($fh, $arr[$i]); 
	    }

}}
?>

<?php

// Open a known directory, and proceed to read its contents
// if (is_dir($dir)) {
//    if ($dh = opendir($dir)) {
//        while (($file = readdir($dh)) !== false) {
//        if (filetype($dir.$file)=='file'){
//        $handle = fopen($dir.$file, "rb");
//        $contents = fread($handle, filesize($dir.$file));
//        $arr = str_split($contents, 10000);
//        for ($i = 0; $i < sizeof($arr); $i++) {
//        $filein = $chunked_txt_out.'/'.$file.$i.'.txt';
//        $fh = fopen($filein, 'w');
//        fwrite($fh, $arr[$i]); 
//        }
//        }
//    }
//        }
// close($dh);
//        }           
?>