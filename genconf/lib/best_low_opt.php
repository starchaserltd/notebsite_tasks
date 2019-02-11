<?php
mysqli_select_db($cons,"notebro_temp");
echo "<br>Generating optimal configurations"; $succes=0;
mysqli_query($cons,"CALL `gen_best_low_tbl`();");
$lastid = mysqli_fetch_row(mysqli_query($con,"SELECT count(id) FROM notebro_db.MODEL WHERE 1=1"));//echo $lastid[0]; 
$id=mysqli_query($con,"SELECT DISTINCT id FROM notebro_db.MODEL WHERE 1=1"); //var_dump($id); 
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
		$no_insert=0;
		for($i=0;$i<3;$i++)
		{
			if(!(isset($val[$i])&&$val[$i]!=NULL&&$val[$i]!=""))
			{$val[$i]=""; $no_insert++;}
		}
			
		if($no_insert<=1)
		{
			$sql = "INSERT INTO notebro_temp.best_low_opt (id_model,lowest_price,best_performance,best_value) values ('".$key."','".$val[0]."','".$val[1]."','".$val[2]."')" ;
			if(mysqli_query($cons, $sql)){ $succes=1; /*echo "INSERTED".$idd[$x]['id']."<br>";*/ }
			else { echo "ERROR: Could not able to execute $sql. " . mysqli_error($cons); }
		}
		else
		{ echo "<br>Unable to generate optimal configs for model id: ".$key."."; }
	}
}

//Calculating optimal configs for p models//

$sql="SELECT `id` FROM `notebro_db`.`REGIONS` WHERE valid=1 AND id!=0 AND id!=1";
$result=mysqli_query($con,$sql); $regions_id=array();
while($row=mysqli_fetch_assoc($result))
{ $regions_id[]=$row["id"]; } mysqli_free_result($result);

$sql="SELECT * FROM `notebro_temp`.`m_map_table` WHERE `model_id`=`pmodel`";
$result=mysqli_query($cons,$sql);
while($row=mysqli_fetch_assoc($result))
{
	foreach($regions_id as $reg_val)
	{
		$sql2='SELECT CONCAT(IFNULL(`0`,""),",",IFNULL(`1`,""),",",IFNULL(`'.$reg_val.'`,"")) as `models` FROM `notebro_temp`.`m_map_table` WHERE `model_id`='.$row["model_id"].' LIMIT 1';
		$result2=mysqli_query($cons,$sql2);
		if($result2&&mysqli_num_rows($result2)>0)
		{
			$models_per_region=explode(",",mysqli_fetch_assoc($result2)["models"]); $sql_price=array(); $sql_value=array(); $sql_performance=array();
			foreach($models_per_region as $model)
			{
				if($model!=NULL&&$model!="")
				{
					$sql_price[]="(SELECT `model`,`id`,`price` FROM `notebro_temp`.`all_conf_".$model."` WHERE `id`=(SELECT `lowest_price` FROM `notebro_temp`.`best_low_opt` WHERE `id_model`=".$model." LIMIT 1) LIMIT 1)";
					$sql_value[]="(SELECT `model`,`id`,`value` FROM `notebro_temp`.`all_conf_".$model."` WHERE `id`=(SELECT `best_value` FROM `notebro_temp`.`best_low_opt` WHERE `id_model`=".$model." LIMIT 1) LIMIT 1)";
					$sql_performance[]="(SELECT `model`,`id`,`rating` FROM `notebro_temp`.`all_conf_".$model."` WHERE `id`=(SELECT `best_performance` FROM `notebro_temp`.`best_low_opt` WHERE `id_model`=".$model." LIMIT 1) LIMIT 1)";
				}
			}
			if(count($sql_price>0))
			{
				$final_sql="SELECT * FROM (".implode(" UNION ",$sql_price).") AS `all_tables` ORDER BY `price` ASC LIMIT 1";
				$lowest_price=mysqli_fetch_assoc(mysqli_query($cons,$final_sql));
			}
			else{$lowest_price=null;}
			
			if(count($sql_performance>0))
			{
				$final_sql="SELECT * FROM (".implode(" UNION ",$sql_performance).") AS `all_tables` ORDER BY `rating` DESC LIMIT 1";
				$best_performance=mysqli_fetch_assoc(mysqli_query($cons,$final_sql));
			}else{$best_performance=null;}
			
			if(count($sql_value>0))
			{
				$final_sql="SELECT `model`,`id`,`value` as value FROM (".implode(" UNION ",$sql_value).") AS `all_tables` ORDER BY `value` DESC LIMIT 1";
				$best_value=mysqli_fetch_assoc(mysqli_query($cons,$final_sql));
			}else{$best_value=null;}
			
			if($lowest_price!=null&&$best_performance!=null&&$best_value!=null)
			{
				$sql_insert="INSERT INTO `notebro_temp`.`best_low_opt` (`id_model`, `lowest_price`, `best_performance`, `best_value`) VALUES ('p_".$row["model_id"]."_".$reg_val."','".$lowest_price["id"]."_".$lowest_price["model"]."','".$best_performance["id"]."_".$best_performance["model"]."','".$best_value["id"]."_".$best_value["model"]."')";
				mysqli_query($cons,$sql_insert);
			}
		}
	}
}

if($succes){ echo "<br>Optimal configurations succesfully generated<br>"; } else { echo "<br>Optimal configurations generation failed<br>"; }
mysqli_select_db($con,"notebro_db");
?>