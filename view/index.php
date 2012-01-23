<?php

 
//include_once 'models/model.php';
 
//wrap the whole thing in a try-catch block to catch any wayward exceptions!
include_once "controller/controller.php";
if($_GET['geo_name'] and !$_GET['osm_id']){
		$place=preg_split('/:::/',$_GET['geo_name']);
		$location=$place[0];
		$hash=$place[1];
                $controller = new Controller();
		$controller->osmAction($location, $hash);

}elseif($_GET['osm_id']){
$controller = new Controller();
$controller->searchIDAction($_GET['url'], $_GET['hash']);

}elseif($_GET['oid'] and $_GET['hash']){
$controller = new Controller();
$controller->copyAction($_GET['oid'], $_GET['hash']);
}elseif($_GET['rid'] and $_GET['hash']){
$controller = new Controller();
$controller->urbanDisplay($_GET['rid'], $_GET['hash']);
}elseif($_GET['urban_id'] and $_GET['hash']){
$controller = new Controller();
$controller->urbanCopy($_GET['urban_id'], $_GET['hash']);
}else{

	try {
	 
	   $controller = new Controller();
	   $controller->searchAction();
	 
	} catch( Exception $e ) {
	  
	}
} 

exit();

?>
