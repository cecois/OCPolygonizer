<?php

include_once("/var/www/mvc/models/model.php");  
 
    class Controller {  
//        private $_params;
 
	public function __construct()
	{
	      
	}      
         
	public function searchAction()
	   {
		//search postgresqldb by searchmodel
	
		//if there is result, show the result on leaflet and invoke copyAction(), otherwise invoke nextAction()
		$model = new Model();
		$geo_places=$model->searchDB();
		
          	include '/var/www/mvc/view/placelist.php'; 
	   }
	
	public function osmAction($location, $hash)
	   {

		$model = new Model();
		$html=$model->searchOSM($location, $hash);
		include '/var/www/mvc/view/osm.php'; 
	   }
	public function searchIDAction($url, $hash)
	   {

		$model = new Model();
		$gjson=$model->searchID($url, $hash);
		if ($gjson=='no_record'){
		$table='urban';
		$control=new Controller();
		$control->ocgeoAction($hash, $table);
		}else{
		include '/var/www/mvc/view/show_osm.php'; 
		}
	   }

	public function ocgeoAction($hash, $table)
	   {
		// configure next dataset in cascade way (nominatum->urban->flicker) in nextmodel, then do searchAction()
		$model = new Model();
		$rid=$model->searchUrban($hash);
		include '/var/www/mvc/view/show_ocgeo.php'; 
	   }

	public function copyAction($oid, $hash)
	   {
		//copy data chosen by user to postgresqldb.
		$model = new Model();
		$model->copydata($oid, $hash);
		include '/var/www/mvc/view/default.php'; 

	   }

	public function urbanDisplay($rid, $hash, $table)
	   {
		//copy data chosen by user to postgresqldb.
		$model = new Model();
		$geojson=$model->dispUrban($rid);
		if ($geojson=='no_record'){
		echo "No record in urban. Searching Flicker data";
		$table='localities';
		$control=new Controller();
		$control->ocgeoAction($hash,$table);
		}else{
		include '/var/www/mvc/view/disp_data.php'; 
		}

	   }
	public function urbanCopy($uid, $hash){
		$model = new Model();
		$geojson=$model->copyUrban($uid, $hash);
		include '/var/www/mvc/view/default.php'; 
	}
	public function flickerDisplay($rid, $hash, $table)
	   {
		//copy data chosen by user to postgresqldb.
		$model = new Model();
		$geojson=$model->dispFlicker($rid);
		if ($geojson=='no_record'){
			echo "no record anywhere";
			include '/var/www/mvc/view/default.php'; 
		}else{
		include '/var/www/mvc/view/disp_data.php'; 
		}
	   }
	public function flickerCopy($uid, $hash){
		$model = new Model();
		$geojson=$model->copyFlicker($uid, $hash);
		include '/var/www/mvc/view/default.php'; 
	}
    }  
?>
