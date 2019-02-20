<?php

echo "<br>Generating presearch table prices"; $succes=0;

$sql="SELECT `model_id` FROM `notebro_temp`.`presearch_tbl`";
$result=mysqli_query($cons,$sql);

if($result&&mysqli_num_rows($result)>0)
{

	while($model=mysqli_fetch_assoc($result))
	{			
		$sql="SELECT max(`price`) as `max_price`,min(`price`) as `min_price`,max(`batlife`) as `max_batlife`,min(`batlife`) as `min_batlife`,max(`capacity`) as `max_cap`,min(`capacity`) as `min_cap` FROM `notebro_temp`.`all_conf_".$model['model_id']."` WHERE `price`!=0 LIMIT 1";
		$result_price=mysqli_query($cons,$sql);
		if($result_price&&mysqli_num_rows($result_price)>0)
		{ $row=mysqli_fetch_assoc($result_price);
			$insert_sql="UPDATE`notebro_temp`.`presearch_tbl` SET `min_price`='".$row["min_price"]."',`max_price`='".$row["max_price"]."',`min_batlife`='".$row["min_batlife"]."',`max_batlife`='".$row["max_batlife"]."',`min_cap`='".$row["min_cap"]."',`max_cap`='".$row["max_cap"]."' WHERE `model_id`=".$model['model_id']."";
			if(!mysqli_query($cons,$insert_sql)){echo("Error description: ".mysqli_error($cons)." ".$insert_sql."<br>");}
		}
	}
}
mysqli_free_result($result);
?>