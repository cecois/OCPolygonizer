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

include "config.php";
//include "dbh2.php"; // db connection to oc_geo
$dbh2 = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password=".$dbpsswd);
        if (!$dbh2) {
            die("Error in connection: " . pg_last_error());
        }
//$sql1 = "select count(*) count, ocurl,geonamesho,hash ochash from oc_geo oc left join dblink('dbname=".$dbname." port=5432 host=".$dbhost." user=".$dbuser." password=".$dbpsswd."', 'SELECT ochash FROM geogeo') AS s(ochash char(255)) ON oc.hash = s.ochash where the_geom IS NOT NULL AND s.ochash IS NULL group by ocurl,geonamesho,oc.hash"; //Crossdb query


$sql1 = "select ocurl,geonamesho,hash from oc_geo oc left join geogeo ge on oc.hash=ge.ochash where the_geom IS NOT NULL AND ge.ochash IS NULL group by ocurl,geonamesho,oc.hash";


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
