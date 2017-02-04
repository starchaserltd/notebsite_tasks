<?php 

require_once("../etc/con_db.php");
mysqli_select_db($con,"notebro_db");

echo "Script is working! <br>";

$sql="SELECT id,cpu,display,mem,hdd,shdd,gpu,wnet,odd,mdb,chassis,acum,warranty,sist FROM MODEL";
$result=mysqli_query($con,$sql);

while($main_row=mysqli_fetch_assoc($result))
{
	$update=1;
//var_dump($main_row); // echo "<br>";

$cpulist=explode(",",$main_row["cpu"]);
$cpulist=array_unique($cpulist);
$newcpu=implode(",",$cpulist);

$displaylist=explode(",",$main_row["display"]);
$displaylist=array_unique($displaylist);
$newdisplay=implode(",",$displaylist);

$memlist=explode(",",$main_row["mem"]);
$memlist=array_unique($memlist);
$newmem=implode(",",$memlist);

$hddlist=explode(",",$main_row["hdd"]);
$hddlist=array_unique($hddlist);
$newhdd=implode(",",$hddlist);

$shddlist=explode(",",$main_row["shdd"]);
$shddlist=array_unique($shddlist);
$newshdd=implode(",",$shddlist);

$gpulist=explode(",",$main_row["gpu"]);
$gpulist=array_unique($gpulist);
$newgpu=implode(",",$gpulist);

$wnetlist=explode(",",$main_row["wnet"]);
$wnetlist=array_unique($wnetlist);
$newwnet=implode(",",$wnetlist);

$oddlist=explode(",",$main_row["odd"]);
$oddlist=array_unique($oddlist);
$newodd=implode(",",$oddlist);

$mdblist=explode(",",$main_row["mdb"]);
$mdblist=array_unique($mdblist);
$newmdb=implode(",",$mdblist);

$chassislist=explode(",",$main_row["chassis"]);
$chassislist=array_unique($chassislist);
$newchassis=implode(",",$chassislist);

$acumlist=explode(",",$main_row["acum"]);
$acumlist=array_unique($acumlist);
$newacum=implode(",",$acumlist);

$warrantylist=explode(",",$main_row["warranty"]);
$warrantylist=array_unique($warrantylist);
$newwarranty=implode(",",$warrantylist);

$sistlist=explode(",",$main_row["sist"]);
$sistlist=array_unique($sistlist);
$newsist=implode(",",$sistlist);



$sql="UPDATE MODEL SET cpu='".$newcpu."', display='".$newdisplay."', mem='".$newmem."', hdd='".$newhdd."', shdd='".$newshdd."', gpu='".$newgpu."', wnet='".$newwnet."', odd='".$newodd."', mdb='".$newmdb."', chassis='".$newchassis."', acum='".$newacum."', warranty='".$newwarranty."', sist='".$newsist."' WHERE id=".$main_row["id"];
//echo $sql; echo "<br>";
echo "FOR SAFETY THIS SCRIPT NEEDS MANUAL EDIT TO WORK! <br>";  
// uncomment below
//mysqli_query($con,$sql);	

}

echo "Script executed succesfully, hopefully everything is still there! <br>";
?>