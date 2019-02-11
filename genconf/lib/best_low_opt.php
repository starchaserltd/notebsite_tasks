<?php
mysqli_select_db($cons,"notebro_temp");
echo "<br>Generating optimal configurations"; $succes=0;
mysqli_query($cons,"CALL `gen_best_low_tbl`();");
$lastid = mysqli_fetch_row(mysqli_query($con,"SELECT count(id) FROM notebro_db.MODEL WHERE 1=1"));//echo $lastid[0]; 
$id =mysqli_query($con,"SELECT DISTINCT id FROM notebro_db.MODEL WHERE 1=1"); //var_dump($id); 
while($ids = mysqli_fetch_array($id)){$idd[] = $ids;} //echo $idd[0]['id'];//var_dump($idd);  

$array = array(); $conds="";
for ($x = 0; $x <=$lastid[0]-1; $x++) 
{
	$conds_query="SELECT id FROM notebro_db.WAR WHERE FIND_IN_SET(id,(SELECT warranty FROM notebro_db.MODEL WHERE id=".$idd[$x]['id']."))>0 ORDER BY rating ASC LIMIT 1";
	$cond["war"]=mysqli_fetch_row(mysqli_query($con,$conds_query))[0]; $conds="AND war=".$cond["war"]." ";
	
	$query= "SET @p0 = 'all_conf_".$idd[$x]['id']."'; SET @p1='".$idd[$x]['id']."'; SET @p2='".$conds."';";
	$query.= "CALL optimal_conf(@p0,@p1,@p2);";
	$result=array();
	$array[$idd[$x]['id']]=array();
	if (mysqli_multi_query($cons, $query))
	{
		do 
		{
			if ($result = mysqli_store_result($cons))
			{
				while ($row = mysqli_fetch_row($result)){ array_push($array[$idd[$x]['id']],$row[0]); } 
				mysqli_free_result($result);
			}
		} 	while (mysqli_next_result($cons));
	}
}

if(isset($array) && count($array)>0)
{
	foreach($array as $key=>$val)
	{
		$sql = "INSERT INTO notebro_temp.best_low_opt (id_model,lowest_price,best_performance,best_value) values (".$key.",".$val[0].",".$val[1].",".$val[2].")" ;
		if(mysqli_query($cons, $sql)){ $succes=1; /*echo "INSERTED".$idd[$x]['id']."<br>";*/ }
		else { echo "ERROR: Could not able to execute $sql. " . mysqli_error($con); }
	}
}
if($succes){ echo "<br>Optimal configurations succesfully generated<br>"; } else { echo "<br>Optimal configurations generation failed<br>"; }
mysqli_select_db($con,"notebro_db");
?>