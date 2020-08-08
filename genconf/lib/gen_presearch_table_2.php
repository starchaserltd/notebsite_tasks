<?php
/*
error_reporting(E_ALL);
require_once("../../etc/con_sdb.php");
if(!isset($server)){$server=0;}
$multicons=dbs_connect();
$con=$multicons[$server];
$cons=$con;
*/

echo "<br>Generating presearch table prices"; $succes=0;

$sql="SELECT `model_id` FROM `notebro_temp`.`presearch_tbl`";
$result=mysqli_query($cons,$sql);

if(have_results($result))
{
	while($model=mysqli_fetch_assoc($result))
	{			
		$sql="SELECT max(`price`) AS `max_price`,min(`price`) AS `min_price`,max(`batlife`) AS `max_batlife`,min(`batlife`) AS `min_batlife`,max(`capacity`) AS `max_cap`,min(`capacity`) AS `min_cap` FROM `notebro_temp`.`all_conf_".$model['model_id']."` WHERE `price`!=0 LIMIT 1";
		$result_price=mysqli_query($cons,$sql);
		if(have_results($result_price))
		{ 
			$row=mysqli_fetch_assoc($result_price);
			if($row["min_price"]!=NULL)
			{
				$insert_sql="UPDATE`notebro_temp`.`presearch_tbl` SET `min_price`='".$row["min_price"]."',`max_price`='".$row["max_price"]."',`min_batlife`='".$row["min_batlife"]."',`max_batlife`='".$row["max_batlife"]."',`min_cap`='".$row["min_cap"]."',`max_cap`='".$row["max_cap"]."' WHERE `model_id`=".$model['model_id']."";
				if(!mysqli_query($cons,$insert_sql)){echo("Error description: <pre>".mysqli_error($cons)." ".$insert_sql."</pre><br>");}
			}
			else
			{
				echo "<br>Unable to generate presearch record this model. CONFIG TABLE FOR MODEL ".$model['model_id']." is empty!"; 
			}
		}
	}
	mysqli_free_result($result);
}
echo "<br>";
?>