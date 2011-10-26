<?php
$id=$_GET['id'];
$table_name=$_GET['table'];
$hash=$_GET['hash'];
include "dbh.php";
//geomso=2 urban table
$insertsql="insert into geogeo (geomso, ochash, the_geom) select 2,'".$hash."', the_geom from ".$table_name." where gid=".$id;
echo $result = pg_query($dbh, $insertsql);

?>
