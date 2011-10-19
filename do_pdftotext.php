<?php                      
// bring in some settings  
include_once('config.php');
                         
// loop over all the pdfs - currently we have to expect these to be sucked down as files already (returnto)
foreach (new DirectoryIterator($cfg_infiledir) as $repofile) {
   // if the file is not this file, and does not start with a '.' or '..',
if ( (!$repofile->isDot()) && ($repofile != '.DS_Store') && (substr(strrchr($repofile,'.'),1) == "pdf") && ($repofile->getFilename() != basename($_SERVER['PHP_SELF'])) ) {

// open a place to spit out results
$txto2oc = preg_replace("/\.pdf$/", "", $cfg_infiledir."/".$repofile).".txt";
// scrape out text to .txt   
system($pdftotextpath. " " .escapeshellcmd($cfg_infiledir."/".$repofile)." ".escapeshellcmd($txto2oc), $ret); 
echo "doing pdftotext... \n";
    if ($ret == 0)
    {
        $value = file_get_contents($txto2oc);
}
    if ($ret == 127)
        print "Could not find pdftotext tool.";
    if ($ret == 1)
        print "Could not find pdf file.";

}
 }
?>