<html>
<head>
<title>Leaflet GeoJSON example</title>
<link rel='stylesheet' href='http://leaflet.cloudmade.com/dist/leaflet.css'>
<script src='http://leaflet.cloudmade.com/dist/leaflet.js'></script>
<script src='http://leaflet.cloudmade.com/examples/sample-geojson.js' type='text/javascript'></script>
</head>
<body>



<div id='map' style='width: 600px; height: 400px'></div>

<select id="rid">
  
<?php
include "dbh2.php";
$h=$_GET['hash'];
$sql1 = "SELECT * from oc_geo where hash=".$h;
$result1 = pg_query($dbh2, $sql1);
 if (!$result1) {
     die("Error in SQL query: " . pg_last_error());
 } else {
while ($row1 = pg_fetch_assoc($result1)) {
echo '<option value="'.$row1['rid'].'">'.$row1['rid'].'</option>';
echo $row1['rid'];

}
}
echo '</select>';
echo '<input type=button value="find me!" onclick=find_me(document.getElementById("rid").value)>';


		

echo		"<script type='text/javascript'>

		var map = new L.Map('map');
		var table='".$_GET['table']."';
		var hash=".$h.";
		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/22677/256/{z}/{x}/{y}.png',
			cloudmadeAttribution = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade',
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});
	
		map.setView(new L.LatLng(39.77, -86.16), 6).addLayer(cloudmade);
		
		var BaseballIcon = L.Icon.extend({
			iconUrl: 'http://leaflet.cloudmade.com/examples/baseball-marker.png',
			shadowUrl: null,
			iconSize: new L.Point(32, 37),
			shadowSize: null,
			iconAnchor: new L.Point(14, 37),
			popupAnchor: new L.Point(2, -32)
		});
		    

				
			var geojsonLayer = new L.GeoJSON();
		
 function copy_data(table_id){
 
 var xmlHttp;
 
 xmlHttp=GetXmlHttpObject();
 if (xmlHttp==null)
 {
 alert ('Browser does not support HTTP Request');
 return;
 }
 
 url='copy_data.php?table='+table+'&id='+table_id+'&hash='+hash;
 
 xmlHttp.onreadystatechange=stateChanged;
 xmlHttp.open('GET',url,true);
 xmlHttp.send(null);
 function GetXmlHttpObject()
 {
 var xmlHttp=null;
 try
 {
 xmlHttp=new XMLHttpRequest();
 }
 catch (e)
 {
 try
 {
 xmlHttp=new ActiveXObject('Msxml2.XMLHTTP');
 }
 catch (e)
 {
 xmlHttp=new ActiveXObject('Microsoft.XMLHTTP');
 }
 }
 return xmlHttp;
 }
 
 function stateChanged()
 {
 if (xmlHttp.readyState==4)
 {
alert(xmlHttp.responseText);
 }
 
 }
 map.closePopup();
}
 	

		


function find_me(rid){
		
				
		var xmlHttp;
		
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null)
		{
		alert ('Browser does not support HTTP Request');
		return;
		}
		
			
		url='find_me.php?table='+table+'&id='+rid;
//				alert(url);
		xmlHttp.onreadystatechange=stateChanged; 
		xmlHttp.open('GET',url,true);
		xmlHttp.send(null);		
		function GetXmlHttpObject()
		{
			var xmlHttp=null;
			try
			{			
			xmlHttp=new XMLHttpRequest();			
			}
			catch (e)
			{			
			try
			{
			xmlHttp=new ActiveXObject('Msxml2.XMLHTTP');
			}
			catch (e)
			{
			xmlHttp=new ActiveXObject('Microsoft.XMLHTTP');
			}
			}
			return xmlHttp;
		}
		
		function stateChanged() 
		{ 
			if (xmlHttp.readyState==4)
			{ 
			if (xmlHttp.responseText==':::{\"type\":\"FeatureCollection\",\"features\":[]}'){
			if(geojsonLayer){
			map.removeLayer(geojsonLayer);
			map.closePopup();
			}
				alert('nothing');
			}else{
			if(geojsonLayer){
			map.removeLayer(geojsonLayer);
                        map.closePopup();
			}
			var response=xmlHttp.responseText.split(\":::\");
			var gid=response[0];

			var geojson=response[1];
			var Gjson=eval('(' +geojson+ ')');
			geojsonLayer = new L.GeoJSON(Gjson);
			geojsonLayer.bindPopup(\"<html><head></head><body><input type='button' onclick='copy_data(\"+gid+\")' value='correct'/></body></html>\");
			map.addLayer(geojsonLayer);
			
			}
			}			
		}		

}
</script>";
?>

</body>
</html>





