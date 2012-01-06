<?php
$osm_id=$_GET['osm_id'];
$hash=$_GET['hash'];
include "config.php";
$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
        if (!$dbh) {
            die("Error in connection: " . pg_last_error());
        }

//geomso osm=1;

$insertsql="insert into geogeo (geomso, ochash, wkb_geometry) select 1,'".$hash."', way from planet_osm_polygon where osm_id=-".$osm_id." order by way_area limit 1";
echo $result = pg_query($dbh, $insertsql);

pg_close($dbh);


?>
