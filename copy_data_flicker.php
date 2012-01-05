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
$insertsql="insert into geogeo (geomso, ochash, wkb_geometry) select 3,'".$hash."', wkb_geometry from ".$table_name." where woe_id=".$id;
echo $result = pg_query($dbh, $insertsql);

?>
