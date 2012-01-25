<html>
<head><title>Leaflet GeoJSON example</title><link rel='stylesheet' href='http://leaflet.cloudmade.com/dist/leaflet.css'><script src='http://leaflet.cloudmade.com/dist/leaflet.js'></script><script src='http://leaflet.cloudmade.com/examples/sample-geojson.js' type='text/javascript'></script></head>
<body>
	<div id='map' style='width: 600px; height: 400px'></div>

	<script>
                alert('The result is from nominatum');
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
		<?php 
			echo "var geojsonLayer = new L.GeoJSON(".$gjson['geo'].");";
		?>
	   	map.addLayer(geojsonLayer);		
		geojsonLayer.on('featureparse', function (e) {
		var popupContent = 'I am a Leaflet vector';
		if (e.geometryType == 'Point') {
		        popupContent += '<p>This GeoJSON Point has been transformed into a <a href=\'http://leaflet.cloudmade.com/reference.html#circlemarker\'>CircleMarker by passing a <code>pointToLayer function in the <a href=\'http://leaflet.cloudmade.com/reference.html#geojson-options\'>GeoJSON options when instantiating the GeoJSON layer. View source for details.';
		}
		if (e.properties && e.properties.popupContent) {
		        popupContent += e.properties.popupContent;
		}
		e.layer.bindPopup(popupContent);
		if (e.properties && e.properties.style && e.layer.setStyle) {
		        e.layer.setStyle(e.properties.style);
		    }
		});
	
		map.addLayer(geojsonLayer);
		
	</script><form action='/mvc/index.php' method='get'>
<?php
	echo "<input type='hidden' name='hash' value='".$hash."'><input type='hidden' name='oid' value='".$gjson['name']."'>";
?>
<input type='submit' value='load into geogeo'/></form>
</body>
</html>
