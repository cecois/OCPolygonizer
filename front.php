<html>

<body>
<script type="text/javascript">


function start_osm(){

var str_array=document.getElementById('geo_id').value.split(":::");
var geoname=str_array[0];
var hash=str_array[1];
document.getElementById('poly_frame').src='nom.php?geo_name='+geoname+'&hash='+hash;

}
</script>

<select name="geo_name" id="geo_id">
  
<?php
include "dbh2.php";
$sql1 = "SELECT * from oc_geo";
$result1 = pg_query($dbh2, $sql1);
 if (!$result1) {
     die("Error in SQL query: " . pg_last_error());
 } else {
while ($row1 = pg_fetch_assoc($result1)) {
echo '<option value="'.$row1['geonamesho'].':::'.$row1['hash'].'">'.$row1['geonamesho'].'</option>';


}
}


?>
</select>
<input type=button value="find" onclick="start_osm()" />

<div>
<iframe id='poly_frame' width=1200 height=800/>
</div>
</body>
</html>
