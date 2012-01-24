<?php

echo "<html>
<head>
<title>Leaflet GeoJSON example</title>
<link rel='stylesheet' href='http://leaflet.cloudmade.com/dist/leaflet.css'>
<script src='http://leaflet.cloudmade.com/dist/leaflet.js'></script>
<script src='http://leaflet.cloudmade.com/examples/sample-geojson.js' type='text/javascript'></script>
</head>
<body>



<div id='map' style='width: 600px; height: 400px'></div>
		<script type='text/javascript'>
		 alert('There was no result in nominatum. The data is from ".$table." table');  
		var map = new L.Map('map');
		
		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/22677/256/{z}/{x}/{y}.png',
			cloudmadeAttribution = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade',
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});
	
		map.setView(new L.LatLng(39.77, -86.16), 1).addLayer(cloudmade);
		
		var BaseballIcon = L.Icon.extend({
			iconUrl: 'http://leaflet.cloudmade.com/examples/baseball-marker.png',
			shadowUrl: null,
			iconSize: new L.Point(32, 37),
			shadowSize: null,
			iconAnchor: new L.Point(14, 37),
			popupAnchor: new L.Point(2, -32)
		});
		
			var gid=".$geojson['gid'].";			
			geojsonLayer = new L.GeoJSON(".$geojson['geo'].");
			geojsonLayer.bindPopup(\"<html><head></head><body><form action='/mvc/index.php' method='get'><input type='hidden' name='uid' value='".$geojson['gid']."'><input type='hidden' name='table' value='".$table."'><input type='hidden' name='hash' value='".$hash."'><input type='submit' value='load into geogeo'/></form></body></html>\");
			map.addLayer(geojsonLayer);

</script>
</body>
</html>"

?>
