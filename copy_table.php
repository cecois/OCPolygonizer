<?php
$osm_id=$_GET['osm_id'];
$hash=$_GET['hash'];
include "dbh.php";

//geomso osm=1;

$insertsql="insert into geogeo (geomso, ochash, the_geom) select 1,'".$hash."', way from planet_osm_polygon where osm_id=-".$osm_id." order by way_area desc limit 1";
echo $result = pg_query($dbh, $insertsql);

pg_close($dbh);


?>
