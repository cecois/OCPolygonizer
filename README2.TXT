1. Database setup

   Postgresql DB Host name, DB name, ID, and Password should be configured in config.php.
   Postgis should be installed in the database. Regarding to postgis installation, please refer to,
   http://postgis.refractions.net/documentation/
   
   (1) geogeo
   
	Database should contain master table, which will store polygons user chosen. The table can be created by below sql.
    

		CREATE TABLE geogeo
		(
		  ogc_fid serial NOT NULL,
		  wkb_geometry geometry,
		  sourceid character varying,
		  name character varying,
		  ochash character varying,
		  geomso character varying
		)
		WITH (
		  OIDS=FALSE
		);
		ALTER TABLE geogeo
		  OWNER TO postgres;

	For geomso column, below are the reference to data source table.
	geomso 1: planet_osm_polygon (Nominatum)
	geomso 2: urban
	geomso 3: localities (flickr shapefile)
	
	(2) oc_geo
	
		should be edited
		
	(3) planet_osm_polygon
	
		planet_osm_polygon is one of the dataset from openstreetmap. The whole dataset can be downloaded from
		
		http://wiki.openstreetmap.org/wiki/Planet.osm#Worldwide_data	
		
		Since data is huge, we can only extract "administrative" boundary data by following.
		
		echo "<osmfilter_pre/>" |bzip2 -1 >lim.bz2
		
		bzcat lim.bz2 a.osm.bz2 lim.bz2 a.osm.bz2 |./osmfilter -k"boundary=administrative">gis.osm

		osm2pgsql gis.osm -H servername -d DBname -U username -P 5432 -S default.style  -x -W
		
	(4) urban
	
		should be edited
		
    (5) Flickr shapefile

		Flickr shaplefile can be downloaded from 

		http://code.flickr.com/blog/2011/01/08/flickr-shapefiles-public-dataset-2-0/
		
		The format is geojson and ogr2ogr can be used to load geojson to postgis table.		
		
		ogr2ogr -s_srs EPSG:4326 -t_srs EPSG:4326 -f "PostgreSQL"  PG:"host= user= dbname= password=" flickr_shapes_localities.geojson -nln localities
		
		Regarding to ogr2ogr, refer to,

		http://www.gdal.org/ogr2ogr.html
		
2. 	front.php

	front.php is the main page of the application. It parses data from oc_geo table in selection window. 
	It only shows data in oc_geo which is not in geogeo table by running below query.
	
	"select ocurl,geonamesho,hash from oc_geo oc left join geogeo ge on oc.hash=ge.ochash where the_geom IS NOT NULL AND ge.ochash IS NULL group by ocurl,geonamesho,oc.hash"
	
	The data will contain "geo_name" of the dataset and "hash" which is	the unique id of the table. 
	If user choose any location, it will open nom.php inside main iframe.
	
3.	nom.php
	
	nom.php gets "geo_name" and "hash" from front.php. It searches nominatum data in
	
	"http://open.mapquestapi.com/nominatim/v1/search.php?"

	It replaces "details.php" to "http://localhost/OCPolygonizer/simpleproxy.php?hash=".$h."&mode=native&url=http://open.mapquestapi.com/nominatim/v1/details.php",
	because the osm id should be extracted from http://open.mapquestapi.com/nominatim/v1/details.php and the polygon data intersecting with origianl data from oc_geo 
	should be parsed.
	
4. simpleproxy.php & copy_table.php

   simpleproxy.php queries data in planet_osm_polygon by osm_id. If there is any result, it parses data as geojson format and shows the result polygon on leaflet application.
   Otherwise, "hash", "table(urban)" variable will be passed to oc.php, and it will run.
   
   For leaflet with geojson, please refer to,
   
   http://leaflet.cloudmade.com/examples/geojson.html
   
   If the polygon is correct, user can press "copy table" button. The "hash" and "osm id" variables will be passed to copy_table.php, and it will run.
   The php contains below sql query
   
   "insert into geogeo (geomso, ochash, wkb_geometry) select 1,'".$hash."', way from planet_osm_polygon where osm_id=-".$osm_id." order by way_area limit 1"
   
   And the data will be saved to geogeo.
   
5. oc.php, find_me.php & copy_data.php

   oc.php lists data from oc_geo table with passed "hash" variable.	If user choose one of the values, it will run find_me.php      
   find_me.php searches any intersecting polygons in "urban" table with the data chosen in oc_geo by running below sql query.
   
   "SELECT gid, st_asgeojson(st_transform(the_geom,4326)) as the_geom from ".$table_name." WHERE ST_Intersects(".$table_name.".the_geom,(SELECT setsrid(the_geom,4326) from oc_geo where rid=".$_GET['id']."))"

   If there is any result, the polygon will be layered on leaflet applications. Otherwise, flicker.php will run.
   If polygon is correct, user can click the polygon and it will show "correct" button, which let user save data to geogeo table by runnign copy_data.php.

 6. flicker.php, find_me_flicker.php & copy_data_flicker.php
 
   flicker.php lists data from oc_geo table with passed "hash" variable.	If user choose one of the values, it will run find_me_flicker.php      
   find_me_flicker.php searches any intersecting polygons in "localities" table with the data chosen in oc_geo by running below sql query.
   
   SELECT woe_id, st_asgeojson(st_transform(wkb_geometry,4326)) as the_geom from ".$table_name." WHERE ST_Intersects(".$table_name.".wkb_geometry,(SELECT setsrid(the_geom,4326) from oc_geo where rid=".$_GET['id']."))

   If there is any result, the polygon will be layered on leaflet applications. 
   If polygon is correct, user can click the polygon and it will show "correct" button, which let user save data to geogeo table by runnign copy_data_flicker.php
   
