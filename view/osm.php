<?php
		$newhtml = str_replace("details.php", "http://localhost/mvc/index.php?osm_id=yes&hash=".$hash."&mode=native&url=http://open.mapquestapi.com/nominatim/v1/details.php", $html);		
		echo $newhtml;


?>
