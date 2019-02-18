<?php
echo "<br>Generating presearch table prices"; $succes=0;

$sql="SELECT `model_id` FROM `notebro_temp`.`presearch_tbl`";
$result=mysqli_query($cons,$sql);

if($result&&mysqli_num_rows($result)>0)
{

	while($row=mysqli_fetch_assoc($result))
	{			
		$sql="SELECT min(`price`) as `min_price` FROM `notebro_temp`.`all_conf_".$row['model_id']."` WHERE `price`!=0 LIMIT 1";
		$result_price=mysqli_query($cons,$sql);
		if($result_price&&mysqli_num_rows($result_price)>0)
		{ $min_price=mysqli_fetch_assoc($result_price)["min_price"]; }
		else
		{ $min_price=0;}

		$insert_sql="UPDATE`notebro_temp`.`presearch_tbl` SET `price`='".$min_price."' WHERE `model_id`=".$row['model_id']."";
		if(!mysqli_query($cons,$insert_sql)){echo("Error description: ".mysqli_error($cons)." ".$insert_sql."<br>");}
	}
}
mysqli_free_result($result);
?>