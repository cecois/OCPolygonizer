<?php
		$newhtml = str_replace("details.php", "http://localhost/mvc/index.php?osm_id=yes&hash=".$hash."&mode=native&url=http://open.mapquestapi.com/nominatim/v1/details.php", $html);
		//$newhtml = str_replace('<form action="http://open.mapquestapi.com/nominatim/v1/search.php" method="get">','<form action="http://localhost/OCPolygonizer/nom.php" method="get">' , $newhtml);
		//$newhtml = str_replace('>details', 'onclick="find_polygon(this.href)">details', $newhtml);
		echo $newhtml;


?>
