<?php

// postgres connx info
$dbhost = 'localhost';
$dbname = '';
$dbuser = '';
$dbpsswd = '';

//include the trailing slash
$cfg_infiledir = 'path/to/folder/';
$chunked_txt_out = $cfg_infiledir."path/to/folder/";

$pdftotextpath = '/path/to/pdftotext';
$oc_xmls_out = $cfg_infiledir."xmlfromoc/";

// opencalais api key
$apiKey = "";

$fb_jsons_out = $cfg_infiledir."/fbjsons/";
$fb_2geo_log = $fb_jsons_out."log_invalids.txt";
$oc_xmls_concd = $cfg_infiledir."xmlconcatenated/";

$fakeru = "http://docs.lib.purdue.edu/jtrp/1123";
$repourl = 'http://docs.lib.purdue.edu/jrtp/';
?>