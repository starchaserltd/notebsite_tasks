<?php
/*
error_reporting(E_ALL);
require_once("../../etc/con_sdb.php");
require_once("../../etc/con_db.php");
if(!isset($server)){$server=1;}
$multicons=dbs_connect();
$server=0;
$cons=$multicons[$server];
$user="notebro_sdb"; $pass="nBdBnologinsdb2"; $database="notebro_temp"; $host="172.31.4.253"; //172.31.4.253 //172.31.2.33
$cons=mysqli_connect($host, $user, $pass, $database);
$user="notebro_db"; $pass="nBdBnologin&4"; $database="notebro_db"; $host="172.31.13.210";
$con=mysqli_connect($host, $user, $pass, $database);
*/
if ($result = mysqli_query($con, "SELECT DATABASE()")) {
    $row = mysqli_fetch_row($result);
    printf("Default database is %s.\n", $row[0]);
    mysqli_free_result($result);
}
echo "<br>Generating map table configurations"; $succes=0;

mysqli_select_db($con,"notebro_db");
$result=mysqli_query($con,"SELECT DISTINCT `id` FROM `notebro_db`.`REGIONS`"); $regions="";
if($result){ while($row=mysqli_fetch_row($result)){ $regions.='`'.$row[0]."`,"; } $regions=substr($regions,0,-1); }

mysqli_select_db($cons,"notebro_temp");
if(mysqli_multi_query($cons,"SET @p0='".$regions."'; CALL `gen_map_table`(@p0);"))
{
	do { if ($result=mysqli_store_result($cons)) { mysqli_free_result($result); } }
	while (mysqli_more_results($cons) && mysqli_next_result($cons));
} 

mysqli_select_db($con,"notebro_db");

$query="SELECT DISTINCT `notebro_db`.`MODEL`.`p_model` as `p_model` FROM `notebro_db`.`MODEL` WHERE `notebro_db`.`MODEL`.`p_model` NOT IN (SELECT DISTINCT `notebro_db`.`MODEL`.`id` FROM `notebro_db`.`MODEL`)";
$result=mysqli_query($con,$query);
$archived_p_model=array();
while($row=mysqli_fetch_assoc($result)){  $archived_p_model[intval($row["p_model"])]=true; }
mysqli_free_result($result);

$query="SELECT `id`,`p_model`,`regions`,`show_smodel` FROM `notebro_db`.`MODEL`";
$result=mysqli_query($con,$query);
$map=new stdClass(); 
while($row=mysqli_fetch_assoc($result))
{
	if(!(isset($row["p_model"])&&$row["p_model"]!=""&&$row["p_model"]!=null))
	{ mysqli_query($con,"UPDATE `notebro_db`.`MODEL` SET `p_model`='".$row["id"]."' WHERE id=".$row["id"].""); $row["p_model"]=$row["id"]; }
	
	$row["p_model"]=intval($row["p_model"]);
	$map->{$row["id"]}=new stdClass(); $map->{$row["id"]}->pmodel=array(0=>$row["p_model"]); $map->{$row["id"]}->show_smodel=array(0=>intval($row["show_smodel"]));
	$result2=mysqli_query($con,"SELECT `id`,`regions` FROM `notebro_db`.`MODEL` WHERE `p_model`=".$row["p_model"]."");
	while($row2=mysqli_fetch_assoc($result2))
	{
		if(!(isset($row2["regions"])&&$row2["regions"]!=null&$row2["regions"]!="")){mysqli_query($con,"UPDATE `notebro_db`.`MODEL` SET `regions`='0' WHERE id=".$row["id"].""); $row2["regions"]="0"; }
		foreach(explode(",",$row2["regions"]) as $x)
		{
			if(!isset($map->{$row["id"]}->{$x})){ $map->{$row["id"]}->{$x}=array(); }
			array_push($map->{$row["id"]}->{$x},$row2["id"]);
		}
	}
	if(isset($archived_p_model[$row["p_model"]])&&$archived_p_model[$row["p_model"]])
	{
		$map->{$row["p_model"]}=new stdClass(); $map->{$row["p_model"]}=$map->{$row["id"]};
		$archived_p_model[$row["p_model"]]=false;
	}
}

foreach($map as $key=>$val)
{
	$r_keys=array(); $r_val=array();
	foreach($val as $key2=>$val2)
	{
		$val2=array_unique($val2);
		$r_keys[]="`".$key2."`"; $r_val[]=implode(",",$val2);
	}
	$insert="INSERT INTO `notebro_temp`.`m_map_table` (`model_id`,".implode(",",$r_keys).") VALUES (".$key.",'".implode("','",$r_val)."')";
	if(!mysqli_query($cons,$insert)){echo("Error description: ".mysqli_error($cons)." ".$insert."<br>");}
}

$query="SELECT `code`,`regions`,`ex_war`,`id` FROM `notebro_site`.`exchrate`";
$result=mysqli_query($con,$query);
while($row=mysqli_fetch_assoc($result))
{
	$insert="INSERT INTO `notebro_temp`.`ex_map_table` (`ex`,`regions`,`ex_war`,`ex_id`) VALUES ('".$row["code"]."','".$row["regions"]."','".$row["ex_war"]."','".$row["id"]."')"; 
	if(!mysqli_query($cons,$insert)){echo("Error description: ".mysqli_error($cons)." ".$insert."<br>");}
}
?>