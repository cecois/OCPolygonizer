<html>
<body>
<script type='text/javascript'>
function find_polygon(url){

parent.document.getElementById('poly_frame').src=url;


}

</script>
<?php
$q=urlencode($_GET['geo_name']);
$h=urlencode($_GET['hash']);
$curl_handle=curl_init();
if ($q){
$curl_url="http://open.mapquestapi.com/nominatim/v1/search.php?q=".$q;
}else{
$curl_url="http://open.mapquestapi.com/nominatim/v1/search.php?q=".urlencode("West Lafayette");
}
curl_setopt($curl_handle,CURLOPT_URL,$curl_url);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
$html=curl_exec($curl_handle);
$newhtml = str_replace("details.php", "http://localhost/OCPolygonizer/simpleproxy.php?hash=".$h."&mode=native&url=http://open.mapquestapi.com/nominatim/v1/details.php", $html);
$newhtml = str_replace('<form action="http://open.mapquestapi.com/nominatim/v1/search.php" method="get">','<form action="http://localhost/OCPolygonizer/nom.php" method="get">' , $newhtml);
$newhtml = str_replace('>details', 'onclick="find_polygon(this.href)">details', $newhtml);

$doc = new DOMDocument();
$doc->loadHTML($newhtml);
echo $doc->saveHTML();
curl_close($curl_handle);
?>
</body>
</html>
