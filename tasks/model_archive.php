<?php
require_once("../etc/con_db.php");

$nr = 0;
$sel="SELECT * FROM notebro_db.MODEL WHERE inactive = '1'";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	//$type=$rand["prod"];
	//$name=$rand["inactive"];
	//echo $type."type";
	//echo $name."name<br>";
	//$insert.="INSERT INTO `notebro_site`.`nomen_models` (`prod`,`family`) VALUES ('$type', '$name');";
$nr++; 
	//If ($rand["inactive"]==1) {
  $sqli = "INSERT INTO notebro_arch.MODEL(`id_org`, `model`, `fam`, `prod`, `cpu`, `display`, `mem`, `hdd`, `shdd`, `gpu`, `wnet`, `odd`, `mdb`, `chassis`, `acum`, `warranty`, `sist`, `msc`, `ldate`, `link`, `link2`, `inactive`, `img_1`, `img_2`, `img_3`, `img_4`, `review_1`, `review_2`, `link_1`, `titlelink_1`, `link_2`, `titlelink_2`, `link_3`, `titlelink_3`, `link_4`, `titlelink_4`) VALUES ('".$rand["id"]."','".$rand["model"]."','".$rand["fam"]."','".$rand["prod"]."','".$rand["cpu"]."','".$rand["display"]."','".$rand["mem"]."','".$rand["hdd"]."','".$rand["shdd"]."','".$rand["gpu"]."','".$rand["wnet"]."','".$rand["odd"]."','".$rand["mdb"]."','".$rand["chassis"]."','".$rand["acum"]."','".$rand["warranty"]."','".$rand["sist"]."','".$rand["msc"]."','".$rand["ldate"]."','".$rand["link"]."','".$rand["link2"]."','".$rand["inactive"]."','".$rand["img_1"]."','".$rand["img_2"]."','".$rand["img_3"]."','".$rand["img_4"]."','".$rand["review_1"]."','".$rand["review_2"]."','".$rand["link_1"]."','".$rand["titlelink_1"]."','".$rand["link_2"]."','".$rand["titlelink_2"]."','".$rand["link_3"]."','".$rand["titlelink_3"]."','".$rand["link_4"]."','".$rand["titlelink_4"]."')";
  mysqli_query($con, $sqli); 
  //echo "Record were inserted successfully."; 
 //echo $rand["id"];
$sqls = "DELETE FROM notebro_db.MODEL WHERE id = ".$rand['id']."";//var_dump($sqls);
	mysqli_query($con, $sqls); echo "sters model  ".$rand['id']; echo "<br>";
  //echo $nr;
}echo $nr."  inserari in arhiva";
mysqli_close($con);
?>