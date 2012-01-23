<html>  
    <head></head>  
      
    <body>  
    <form action="/mvc/index.php" method="get">
      <select name="geo_name" id="geo_id">
        <?php   
    		for ($i=0;$i<sizeof($geo_places);$i++){
           echo '<option value="'.$geo_places[$i][0].':::'.$geo_places[$i][1].'">'.$geo_places[$i][0].'</option>';
}
?>      

      </select>


  <input type="submit" value="Submit" />
</form></body></html>  


