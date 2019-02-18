<?php

if ($result = mysqli_query($con, "SELECT DATABASE()")) {
    $row = mysqli_fetch_row($result);
    printf("Default database is %s.\n", $row[0]);
    mysqli_free_result($result);
}
echo "<br>Generating presearch table configurations"; $succes=0;

mysqli_select_db($cons,"notebro_temp");
if(mysqli_multi_query($cons,"CALL `presearch_tbl`();"))
{
	do { if ($result=mysqli_store_result($cons)) { mysqli_free_result($result); } }
	while (mysqli_more_results($cons) && mysqli_next_result($cons));
} 
mysqli_select_db($con,"notebro_db");

$sql="SELECT `id`,`cpu`,`display`,`mem`,`hdd`,`shdd`,`gpu`,`wnet`,`odd`,`mdb`,`chassis`,`acum`,`warranty`,`sist` FROM `notebro_db`.`MODEL`";
$result=mysqli_query($con,$sql);

if($result&&mysqli_num_rows($result)>0)
{
	while($row=mysqli_fetch_assoc($result))
	{
		$sql="SELECT min(`price`) as `min_price` FROM `notebro_temp`.`all_conf_".$row['id']."` LIMIT 1";
		$result_price=mysqli_query($cons,$sql);
		if($result_price&&mysqli_num_rows($result_price>0))
		{ $min_price=mysqli_fetch_assoc($result_price)["min_price"]; }
		else
		{ $min_price=0;}
		
		$insert_sql="INSERT INTO `notebro_temp`.`presearch_tbl` (`model_id`,`cpu`,`display`,`mem`,`hdd`,`shdd`,`gpu`,`wnet`,`odd`,`mdb`,`chassis`,`acum`,`war`,`sist`,`price`) VALUES ('".$row["id"]."','".$row["cpu"]."','".$row["display"]."','".$row["mem"]."','".$row["hdd"]."','".$row["shdd"]."','".$row["gpu"]."','".$row["wnet"]."','".$row["odd"]."','".$row["mdb"]."','".$row["chassis"]."','".$row["acum"]."','".$row["warranty"]."','".$row["sist"]."','".$min_price."')";
		if(!mysqli_query($cons,$insert_sql)){echo("Error description: ".mysqli_error($cons)." ".$insert_sql."<br>");}
	}
}


?>