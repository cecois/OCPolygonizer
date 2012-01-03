<?php
$id=$_GET['id'];
$table_name=$_GET['table'];
$hash=$_GET['hash'];
include "config.php";
$dbh = pg_connect("host=".$dbhost." dbname=".$dbname." user=".$dbuser." password='".$dbpsswd."'");
        if (!$dbh) {
            die("Error in connection: " . pg_last_error());
        }
//geomso=2 urban table
$insertsql="insert into geogeo (geomso, ochash, wkb_geometry) select 2,'".$hash."', the_geom from ".$table_name." where gid=".$id;
echo $result = pg_query($dbh, $insertsql);

?>
