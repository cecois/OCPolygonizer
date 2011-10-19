<?php 
// bring in some settings  
include_once('config.php');
$dir = $oc_xmls_out;  //The place of split text files
$outdir=$oc_xmls_concd;
mkdir($oc_xmls_concd, 0700);
// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
 
if ($dh = opendir($dir)) {
 
$i=0;
while (($file = readdir($dh)) !== false) {
 
$rest = substr($file, -3);
if ($rest=="xml"){
if (filetype($dir."/".$file)=='file'){
 
$split_file=preg_split('/.txt|.xml/',$file);
$filename[$i]=$file;
$master[$i]=$split_file[0];
$fileno[$i]=$split_file[1];
$i++;
 
}
}
}
 
array_multisort($master, $fileno,$filename);
$no_xml=sizeof($filename);
/*for ($j=0;$j<$no_xml;$j++){
echo $filename[$j].'</br>';
}*/
}
}
 
$master_file=array_unique($master);
 
for ($k=0;$k<$no_xml;$k++){
if($master_file[$k]!=''){
$flag=0;
date_default_timezone_set ("America/Indianapolis");
$now = time();
$master_xml = $outdir."/".$master_file[$k].'.xml';
$fmaster = fopen($master_xml, 'w+');
 
for ($j=0;$j<$no_xml;$j++){
$comp_file=preg_split('/.txt/',$filename[$j]);
 
if ($master_file[$k]==$comp_file[0]){
$flag++;
 
if($flag==1){
 
$sub_xml = $dir."/".$filename[$j];
$fsub= fopen($sub_xml, 'r');
$sub_content=fread($fsub,filesize($sub_xml));
$first_cont=preg_split('/<\/rdf:RDF>/',$sub_content);
fwrite($fmaster, $first_cont[0]);
fclose($sub_xml);
}else{
$sub_xml = $dir."/".$filename[$j];
$fsub= fopen($sub_xml, 'r');
$sub_content=fread($fsub,filesize($sub_xml));
$cont=preg_split('/<rdf:Description/',$sub_content);
$no_block=sizeof($cont);
 
for ($m=1;$m<$no_block;$m++){
$content=preg_split('/<\/rdf:RDF/',$cont[$m]);
$new_content='<rdf:Description'.$content[0];
fwrite($fmaster, $new_content);
}
}
}
}
fwrite($fmaster, '</rdf:RDF></string>');
}
}
?>