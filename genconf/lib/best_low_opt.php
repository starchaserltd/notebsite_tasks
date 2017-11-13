<?php
mysqli_select_db($cons,"notebro_temp");
echo "<br>Generating optimal configurations"; $succes=0;
mysqli_query($cons,"TRUNCATE TABLE notebro_temp.best_low_opt");
$lastid = mysqli_fetch_row(mysqli_query($con,"SELECT count(id) FROM notebro_db.MODEL WHERE 1=1"));//echo $lastid[0]; 
$id =mysqli_query($con,"SELECT DISTINCT id FROM notebro_db.MODEL WHERE 1=1"); //var_dump($id); 
while($ids = mysqli_fetch_array($id)){$idd[] = $ids;} //echo $idd[0]['id'];//var_dump($idd);  
$query = '';
for ($x = 0; $x <=$lastid[0]-1; $x++) 
{
	$query.= "SET @p0 = 'all_conf_".$idd[$x]['id']."';";
	$query.= "CALL optimal_conf(@p0);";
} 

$array = array();						
if (mysqli_multi_query($cons, $query))
{
    do 
	{
        if ($result = mysqli_store_result($cons))
		{
			while ($row = mysqli_fetch_row($result)){ array_push($array,$row[0]); } 
			mysqli_free_result($result);
        }
	} while (mysqli_next_result($cons));
}

$result = array_chunk($array, ceil(count($array) / $lastid[0]));
for($x = 0;$x<=$lastid[0]-1;$x++ )
{
	array_push($result[$x],$idd[$x]['id']);
	$sql = "INSERT INTO notebro_temp.best_low_opt (id_model,lowest_price,best_performance,best_value) values (".$result[$x][3].",".$result[$x][0].",".$result[$x][1].",".$result[$x][2].")" ;
	if(mysqli_query($cons, $sql)){ $succes=1; /*echo "INSERTED".$idd[$x]['id']."<br>";*/ }
	else { echo "ERROR: Could not able to execute $sql. " . mysqli_error($cons); }
}
if($succes){ echo "<br>Optimal configurations succesfully generated<br>"; } else { echo "<br>Optimal configurations generation failed<br>"; }
mysqli_select_db($con,"notebro_db");
?>