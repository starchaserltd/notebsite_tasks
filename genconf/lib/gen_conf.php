<?php
echo "<b>Starting price generations 2.0 </b><br><br>";
$multicons=dbs_connect();

if(!isset($prod_server)){ show_running_output("<br><b>THIS FILE IS NOT RUN PROPERLY!</b><br>"); exit(); }

echo "<b>Using the following SQL connections:</b><br>";
foreach ($multicons as $cons)
{ "<br>"; var_dump($cons); echo "<br>"; }
echo "<b>End of SQL info</b><br><br>";

echo "<br><b>Setting initial variables:</b><br>";
require_once("lib/init.php");
echo "<b>Making presearch tables:</b><br>";
require_once("gen_presearch_table_1.php");
echo "<br><b>Done with the presearch tables.</b><br><br>";
//ob_start();

/********* CREATE PERMANENT TABLE FOR CONFIGURATIONS ******/
// Don't ask how it's done, the procedures for it are stored in MariaDB

//GETTING RATING WEIGHTS 
require("lib/var_conf.php");

//GETTING LEGACY PRICE FUNCTIONS
require_once("legacy_price_calc.php");
//GETTING NEW PRICE FUNCTIONS
$no_all_conf_models=array();
if($new_prices){ require_once("lib/calc_price.php"); require_once("get_price_func.php"); }

//FIRST DELETING ANY TEMPORARY TABLES
if (isset($_SESSION['temp_configs']))
{
    $sel2="DROP TABLE IF EXISTS notebro_temp.".$_SESSION['temp_configs'].";";
	mysqli_query($multicons[$server],$sel2) or die(mysqli_error());
}	

//SETTING WEBSITE IN PRICE GENERATION MODE, CREATING TEMPORARY TABLES
$sel2='UPDATE notebro_site.vars SET value=1 WHERE name="genconfig"';
mysqli_query($con,$sel2);


// CREATING TEMPORARY TABLES
$sel2 = 'USE notebro_temp; CALL delete_tbls(); CALL allconf_tbl(); SELECT @tablename; ';
//mysqli_multi_query($cons, $sel2) or die (mysqli_error ($cons) . " The query was:" . $sel2);
$temp_table=local_multiquery($cons,$sel2,0);
mysqli_query($con,"USE notebro_db;");

$nr_configs=1;

//GETTING COMPONENT DATA FOR COMPONENTS WITHOUT FILTERS
foreach($comp_list as $comp)
{
	if(isset(${$comp."_s"}) && intval(${$comp."_s"})==0 && (!isset(${$comp."_selected_data"}) || (isset(${$comp."_selected_data"}) && count(${$comp."_selected_data"})<1)))
	{ ${$comp."_selected_data"}=nolist($comp,0,0); }
}

$time_start = microtime(true);

//SET ABC ORDER OF MODELS
mysqli_query($con,"CALL ABCORDER();");

//GET NEW PRICE SSTEM
$new_price_conf=array();
if($new_prices)
{
	$SELECT_NEW_PRICE="SELECT * FROM `notebro_buy`.`CONFIG` WHERE `type`='new_price_gen'";
	$new_price_q=mysqli_query($con,$SELECT_NEW_PRICE);
	if(have_results($new_price_q))
	{
		while($row=mysqli_fetch_assoc($new_price_q))
		{
			$new_price_conf[]=["prod"=>strval($row["data_1"]),"regions"=>explode(",",$row["data_2"])];
		}
		unset($row);
		mysqli_free_result($new_price_q);
	}
}

//GET MODELS FOR PROCESSING
$model_ids = array();
#$model_select_cond="AND id=4892";
$model_select_cond="";
$query_model = "SELECT DISTINCT `id` FROM `notebro_db`.`MODEL` WHERE 1=1 ".$model_select_cond." ORDER BY `id` ASC";
$result = mysqli_query($con,$query_model);
if(have_results($result))
{
	while($row = mysqli_fetch_row($result)) { array_push($model_ids, $row[0]); }
	mysqli_free_result($result); unset($row);
}

if(count($model_ids)>0)
{
	show_running_output("<br><b>GOING TO GENERATE TEMPORARY CONFIGURATIONS FOR THE ".count($model_ids)." MODEL IDS:</b><br>");
	foreach($model_ids as $id){ echo $id." "; }
	echo "<br><br>";
}
else { show_running_output("<br><b>SOMETHING WENT TERRIBLY WRONG AND THERE ARE NO MODELS TO GENERATE!</b><br>"); }

//PROCESS THE MODELS

foreach($model_ids as $model_id) 
{
	if(!isset($mdb_selected_data)){ $mdb_selected_data=array();} if(!isset($mem_selected_data)){ $mem_selected_data=array();} if(!isset($shdd_selected_data)){ $shdd_selected_data=array();}
	if(!isset($wnet_selected_data)){ $wnet_selected_data=array();} if(!isset($odd_selected_data)){ $odd_selected_data=array();} if(!isset($chassis_selected_data)){ $chassis_selected_data=array();}

	$configs=generate_configs($con,$rcon,$multicons,$model_id,$comp_list);

	$create_table = "USE `notebro_temp`; SET @p0='all_conf_".$model_id."'; CALL allconf_tbl2(@p0); DROP TABLE IF EXISTS 'all_conf_".$model_id."'; CALL allconf_tbl2(@p0); ";
	$cons=$multicons[$server];
	local_multiquery($cons,$create_table);
	
	mysqli_query($con, "USE notebro_db;");
	if($configs->valid())
	{
		if(!isset($max_configs_limit) || (isset($max_configs_limit) && $nr_configs<$max_configs_limit))
		{
			$INSERT_QUERY = "INSERT INTO `notebro_temp`.`".$temp_table."_".$model_id."` (`id`,`model`,`".implode("`,`",$comp_list)."`,`rating`,`price`,`value`,`err`,`batlife`,`capacity`) VALUES ";
			$INSERT_ID_MODEL = "INSERT INTO `notebro_temp`.`".$temp_table."` (`id`, `model`) VALUES ";
			insert_function ($configs,$BATCH_SIZE,$INSERT_QUERY,$INSERT_ID_MODEL,$multicons,$server,$model_id,$comp_list);
			show_running_output("<b>TRIED TO INSERT DATA FOR MODEL ID: ".$model_id." !</b><br>");
		}
		else
		{
			show_running_output("<b>MAXIMUM NUMBER OF CONFIGS REACHED: ".$max_configs_limit." !</b> Aborting insertation for model id: ".$model_id."<br>"); 
			show_running_output("<b>ABORTING CONF GENERATION!</b><br>"); break;
		}
	}
	//$time_end = microtime(true); $execution_time = ($time_end - $time_start); printf("<br><b>Time elapsed:</b> %.6f s\n<br><br>", $execution_time); exit();
}

if($prod_server==1){ get_prices_from_all_conf(100); }

//SETTING GENERATION VARIABLE TO ZERO
mysqli_query($con,'UPDATE notebro_site.vars SET value=0 WHERE name="genconfig"');

//DOING SOME EXTRA POST GENERATION WORK
require_once("gen_presearch_table_2.php");
require_once("gen_map_table.php");
require_once("best_low_opt.php");
	
mysqli_close($rcon);

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
foreach ($multicons as $cons) { mysqli_close($cons); }
printf("<br><b>Time elapsed:</b> %.6f s\n<br><br>", $execution_time);
if($prod_server==0){ echo "<br><b>Validating and updating the home top laptops:</b><br>"; require_once("../toplaptop/validconftop.php"); echo "<br>"; }
mysqli_close($con);
//ob_end_flush();
?>








<?php


#FUNCTIONS

//// MAIN FUNCTION TO BUILD TEMPORARY CONFIGURATIONS TABLE /////	
function generate_configs($con,$rcon,$multicons,$model_id,$comp_list)
{
    $final_configurations=array();
	global $current_model_prod, $current_model_regions;
	$current_model_prod=""; $current_model_regions="";
	if(!isset($GLOBALS["prod_server"])){ show_running_output("<br><b>THIS FILE IS NOT RUN PROPERLY!</b><br>"); exit(); }
	$sel3="SELECT * FROM `notebro_db`.`MODEL` WHERE `id`='".$model_id."' LIMIT 1";
	show_running_output("<br><b>SELECTING DATA</b>: ".$sel3."<br>");
	$result=mysqli_query($con,$sel3) or die(mysqli_error($con));
	if(have_results($result))
	{
		while($row=mysqli_fetch_array($result)) 
		{
			$row["id"]=intval($row["id"]); $new_price_calc=False;
			//GETTING PRICE DATA FOR FURTHER CALCULATION
			if($GLOBALS["new_prices"])
			{
				$GLOBALS["questionable_confs"]=array(); //This variable is used for extrapolating price for wrong warranties
				get_price_data($model_id,$comp_list,$con);	
				$new_price_calc=is_new_price_conf(strval($row["prod"]),strval($row["regions"]),$GLOBALS["new_price_conf"]);
				if($new_price_calc){ $GLOBALS["no_all_conf_models"][]=$model_id; }
			}

			//if($row["id"]==1156) { var_dump($raw); }
			$have_error=False;
			//GETTING COMPONENT IDS FOR PRICE CONF GENERATION
			foreach($comp_list as $comp)
			{
				if($comp=="war")
				{ ${$comp."_model_ids"}=explode(",",$row["warranty"]); }
				else
				{ ${$comp."_model_ids"}=explode(",",$row[$comp]); }
				
				if(count(${$comp."_model_ids"})<1) {$have_error=True;}
				
				if(isset($GLOBALS[$comp."_s"]) && intval($GLOBALS[$comp."_s"])>0)
				{ ${$comp."_selected_data"}=$GLOBALS[$comp."_selected_data"]; }
				else
				{  if(isset($GLOBALS[$comp."_selected_data"]) && count($GLOBALS[$comp."_selected_data"])>0 ){ ${$comp."_selected_data"}=$GLOBALS[$comp."_selected_data"]; } else { ${$comp."_selected_data"}=nolist($comp,${$comp."_model_ids"},0);} }
					
				if(!isset(${$comp."_selected_data"}) || (isset(${$comp."_selected_data"}) && count(${$comp."_selected_data"})<1))
				{ $have_error=True; show_running_output("<br><b>FOR MODEL ID ".$model_id." MAJOR ERROR ON COMPONENT, EMPTY MAIN SELECTED COMPONENTS: </b>: ".$comp."<br>"); }
			
				if($have_error==False)
				{		
					if(${$comp."_selected_data"})
					{
						${$comp."_ids"}=array_intersect(${$comp."_model_ids"},array_keys(${$comp."_selected_data"}));
						#if($row["id"]==88 && $comp=="wnet") { echo "<br>Data:<br>"; echo "Model ids: "; var_dump(${$comp."_model_ids"}); echo "<br>Selected ids: "; var_dump(${$comp."_selected_data"}); echo "<br>Final ids: "; var_dump(${$comp."_ids"}); echo "<br>"; }
						if(!isset(${$comp."_ids"}) || (isset(${$comp."_ids"}) && count(${$comp."_ids"})<1))
						{ $have_error=True; show_running_output("<br><b>FOR MODEL ID ".$model_id." THERE ARE NO INTERSECT COMPONENTS FOR: </b>: ".$comp."<br>");  }
					}
					else{ $have_error=True; show_running_output("<br><b>FOR MODEL ID ".$model_id." THERE ARE NO IDS FOR COMPONENT </b>: ".$comp."<br>");  }
				}
			}
			
			if($have_error==False)
			{
				show_running_output("<br><b>GOING TO GENERATE CONFIGURATIONS</b> FOR MODEL ".$model_id." WITH THE FOLLOWING IDS: <br>");
				foreach($comp_list as $comp)
				{ show_running_output("<br><b>".$comp."</b>:"); var_dump((${$comp."_ids"})); }
				show_running_output("<br><br>");
			}
			else{ show_running_output("<br><b>UNABLE TO GENERATE CONFIGURATIONS</b> FOR MODEL ".$model_id." WITH THE FOLLOWING IDS: <br>"); }
			$some_i=0;
			
			//GETTING THE INCOMPATIBLE CPU AND GPU THIS SHOULD PROBABLY BE IN A SEPARATE SCRIPT
			if($have_error==False)
			{
				$gen_configurations=array(); $abort=False;
				$incompatible_gpu_cpu=array();
				$nr_incomp=0;
		
				$SQL_TEST_GPU="SELECT GROUP_CONCAT(`id`) AS `ids` FROM `notebro_db`.`GPU` WHERE `id` IN (".implode(",",$gpu_ids).") AND `typegpu`=0";
				$test_sql_result=mysqli_query($con,$SQL_TEST_GPU);
				if(have_results($test_sql_result))
				{
					$test_row=mysqli_fetch_assoc($test_sql_result);
					$gpus_to_test=explode(",",$test_row["ids"]);
					$SQL_TEST_GPU="SELECT `id`,`gpu` FROM `notebro_db`.`CPU` WHERE `gpu` IN (".implode(",",$gpus_to_test).")";
					$test_sql_result_2=mysqli_query($con,$SQL_TEST_GPU);
					if(have_results($test_sql_result_2))
					{
						while($test_row_2=mysqli_fetch_assoc($test_sql_result_2))
						{
							#var_dump($test_row_2); echo "<br><br>";
							if(!isset($incompatible_gpu_cpu["gpu_".strval($test_row_2["gpu"])])){ $incompatible_gpu_cpu["gpu_".strval($test_row_2["gpu"])]=array(); }
							foreach($cpu_ids as $key=>$val)
							{
								if(intval($val)!=intval($test_row_2["id"]))
								{
									#var_dump($incompatible_gpu_cpu); echo "<br>"; var_dump(intval($val)); echo "<br>"; var_dump($test_row_2["id"]); echo "<br><br>";
									if(!isset($incompatible_gpu_cpu["gpu_".strval($test_row_2["gpu"])]["cpu_".strval($val)]))
									{ $incompatible_gpu_cpu["gpu_".strval($test_row_2["gpu"])]["cpu_".strval($val)]=True; }
								}
								else
								{
									$incompatible_gpu_cpu["gpu_".strval($test_row_2["gpu"])]["cpu_".strval($val)]=False;
								}
							}
							$nr_incomp++;
						}
						mysqli_free_result($test_sql_result_2);
					}
					mysqli_free_result($test_sql_result);
				}
				#var_dump($incompatible_gpu_cpu);
				unset($test_row_2); unset($test_row);
				
				#GETTING DISABLED CONFs FROM notebro_prices.disabled_configs
				if($new_price_calc){$temp_cond=" AND ( `retailer` IS NOT NULL AND `retailer`<>'')";}else{$temp_cond="";}
				$SELECT_DISABLED_DATA="SELECT * FROM `notebro_prices`.`disabled_configs` WHERE `model`=".$model_id."".$temp_cond."";
				unset($temp_cond);
				$test_disb_result=mysqli_query($rcon,$SELECT_DISABLED_DATA);
				$disabled_data=array(); $nr_valid_disabled=array();
				if(have_results($test_disb_result))
				{
					while($test_row=mysqli_fetch_assoc($test_disb_result))
					{
						if(!isset($nr_valid_disabled[$test_row["id"]])){$nr_valid_disabled[$test_row["id"]]=0;}
						foreach($comp_list as $comp_name)
						{ $disabled_data[$test_row["id"]][$comp_name]=array(); if($test_row[$comp_name]!==NULL){ $disabled_data[$test_row["id"]][$comp_name]=explode(",",$test_row[$comp_name]); $nr_valid_disabled[$test_row["id"]]++; }else{ $disabled_data[$test_row["id"]][$comp_name]=NULL; } }
					}			
					mysqli_free_result($test_disb_result);
					unset($test_row);
				}

				//DOING THE CONFIGURATION GENERATION
				$gen_configurations[]["model"]=$model_id;
				$init_data=array("rating"=>0,"bat_com"=>array(),"bat_cap"=>0,"dummy_price"=>0,"price_error"=>0,"storage_cap"=>0);
				$nr_iterations=count($comp_list); $iteration=0;
				foreach($comp_list as $comp)
				{
					$iteration++;
					
					$old_gen_configurations=$gen_configurations;
					$gen_configurations=array();

					foreach(${$comp."_ids"} as $key=>$val)
					{

						if(isset($old_gen_configurations[0]))
						{
							foreach($old_gen_configurations as $result_key=>$result_val)
							{
								$incompatible=False;
								$result_val[$comp]=$val;

								//COMPONENT SPECIFIC STUFF
								require("comp_incomp.php");
								//END OF COMPONENT SPECIFIC STUFF
	
								//CHECKING CPU_GPU COMPATIBILITY
								if(isset($result_val["cpu"]) && isset($result_val["gpu"]))
								{
									if(isset($incompatible_gpu_cpu["gpu_".$result_val["gpu"]]["cpu_".$result_val["cpu"]]) && $incompatible_gpu_cpu["gpu_".$result_val["gpu"]]["cpu_".$result_val["cpu"]])
									{ $incompatible=True; }
								}

								//CHECKING DISABLED CONF
								if(!$incompatible)
								{
									foreach($disabled_data as $some_row=>$d_data)
									{
										if(!isset($result_val["to_delete"][$some_row])){$result_val["to_delete"][$some_row]=0;}
										if($result_val["to_delete"][$some_row]>-1 && $result_val["to_delete"][$some_row]<1000)
										{
											if($d_data[$comp]!=NULL)
											{ 
												if(in_array($result_val[$comp],$d_data[$comp]))
												{ $result_val["to_delete"][$some_row]++; } else { $result_val["to_delete"][$some_row]=-1; }
											}
										}								
										if($nr_valid_disabled[$some_row]<=$result_val["to_delete"][$some_row]) { $result_val["to_delete"][$some_row]=99999; $incompatible=True; break; }
									}
								}
								
								if(!$incompatible)
								{
									if($iteration==$nr_iterations)
									{
										//Looks like this is the last iteration, time to calculate configuration stuff
										//$haha=True;
										//foreach($result_val["to_delete"] as $dis_val) { if($dis_val>1000){ $haha=False; break; echo "I: "; var_dump($result_val); echo "<br><br>";  } }
										//if($haha){ var_dump($result_val); $some_i++; var_dump($some_i); echo "<br>"; }
										unset($result_val["to_delete"]);
										$newid=hexdec(hash('fnv1a64',(implode(",",array_merge($result_val)))));
										$conf_data=array();	$conf_data=calculate_conf_data($result_val,$comp_list,$new_price_calc);
										#var_dump($result_val); echo "<br>"; var_dump($conf_data); echo "<br>";
										$yield_data=True;
										if(($GLOBALS["new_prices"]) && $new_price_calc && intval($conf_data["price"])<5)
										{ $yield_data=False; }
									
										if($yield_data)
										{
											$result_val["rating"]=$conf_data["rating"];
											$result_val["price"]=$conf_data["price"];
											if($result_val["price"]>0){ $result_val["value"]=$result_val["rating"]/$result_val["price"]; }
											else { $result_val["value"]=0; }
											$result_val["err"]=$conf_data["price_error"];
											$result_val["batlife"]=$conf_data["bat_life"];
											$result_val["capacity"]=$conf_data["storage_cap"];
											//$final_configurations[$newid]=$result_val;
											$final_configuration=array_merge([$newid], $result_val);
											$GLOBALS["nr_configs"]++;
											yield $final_configuration;
										}
									}
									$gen_configurations[]=$result_val;
								}
								else
								{ }
							}
						}
						else
						{
							if($iteration>1)
							{ show_running_output("<br><b>NO COMPATIBLE CONFIGURATIONS WITH ".$comp." ID: ".$val." </b> FOR MODEL ".$model_id."<br>"); break(2); }
							else
							{
								$result_val=array();
								$result_val[$comp]=$val;
								$result_val["to_delete"]=array();
								foreach($disabled_data as $some_row=>$d_data)
								{
									if(!isset($result_val["to_delete"][$some_row])){$result_val["to_delete"][$some_row]=0;}
									if($result_val["to_delete"][$some_row]>-1 && $result_val["to_delete"][$some_row]<1000)
									{
										if($d_data[$comp]!=NULL)
										{ 
											if(in_array($result_val[$comp],$d_data[$comp]))
											{ $result_val["to_delete"][$some_row]++; } else { $result_val["to_delete"][$some_row]=-1; }
										}
									}								
									if($nr_valid_disabled[$some_row]<=$result_val["to_delete"][$some_row]) { $result_val["to_delete"][$some_row]=99999; $incompatible=True; break; }
								}
								$gen_configurations[]=$result_val;
							}
						}
					}
				}
			
				if(isset($gen_configurations[0]))
				{
					//NOTHING TO DO?
				}
				else
				{ show_running_output("<br><b>FAILED TO GENERATE ANY CONFIGURATIONS</b> FOR MODEL ID: ".$model_id."<br>"); }
			}
		}
		mysqli_free_result($result);
	}
}



//THE FUNCTION THAT CALCULATES RATING,PRICE,ETC FOR EVERY GENERATED CONFIGURATION

function calculate_conf_data($conf,$comp_list,$new_price_calc=NULL)
{
	$to_return=array();
	//SELECTING COMPONENT DATA
	foreach($comp_list as $comp)
	{ 
		if(isset($GLOBALS[$comp."_selected_data"][$conf[$comp]]))
		{ $comp_data[$comp]=$GLOBALS[$comp."_selected_data"][$conf[$comp]]; }
		else { echo "<br><b>ERROR GENERATING CONFIGURATION DATA, MISSING DATA FOR COMPONENT </b>".$comp." WITH ID ".$conf[$comp].""; $to_return=NULL; }
	}
	
	if($to_return!==NULL)
	{
		//CALCULATE RATING
		$to_return["rating"]=0;
		foreach($comp_list as $comp)
		{ $to_return["rating"]+=$comp_data[$comp]["rating"]*$GLOBALS[$comp."_i"]; }
				
		//STORAGE CAPACITY
		$to_return["storage_cap"]=0;
		$to_return["storage_cap"]=0+intval($comp_data["hdd"]["cap"])+intval($comp_data["shdd"]["cap"]);
		
		//BATTERY LIFE
		$to_return["bat_life"]=0; $bat_com=0;
		
		$bat_com=$bat_com+round((0.5+floatval($comp_data["cpu"]["tdp"])/7),5);
		
		$display_data=$comp_data["display"];
		if(stripos($display_data["backt"],"OLED")!==FALSE)
		{ $c_display_pwc=((floatval($display_data["size"])*0.10)+(pow(intval($display_data["res"]),0.5)*0.00255-3.4))*0.6; }
		else
		{ $c_display_pwc=((floatval($display_data["size"])*0.10)+(pow(intval($display_data["res"]),0.5)*0.00255-3.4))*0.7; }
		if($display_data['touch']==1){ $c_display_pwc+=(floatval($display_data["size"])*floatval($display_data["size"]))/400; }
		$bat_com=$bat_com+round($c_display_pwc,5);
		
		foreach(["hdd","shdd"] as $storage_type)
		{
			$storage_data=$comp_data[$storage_type];
			switch($storage_data["type"])
			{
				case "SSD":	{ $bat_com=$bat_com+0.5; break; }
				case "HDD":	{ $bat_com=$bat_com+1; break; }
				case "SSHD": { $bat_com=$bat_com+0.9; break; }
				case "EMMC": { $bat_com=$bat_com+0.3; break; }
				default: { $bat_com=$bat_com+0; break; }			
			}
		}
		if($comp_data["gpu"]["type"]==0){ $bat_com=$bat_com+0.2; }
		else
		{
			$comp_data["gpu"]["tdp"]=floatval($comp_data["gpu"]["tdp"]);
			if(in_array($comp_data["gpu"]["arch"],["Turing","Pascal","Ampere"])){$comp_data["gpu"]["tdp"]/=1.5;}
			$gpu_bat_com=round((floatval($comp_data["gpu"]["tdp"])/8),5);
			
			if($comp_data["mdb"]["optimus"] && $gpu_bat_com>3){ $gpu_bat_com=3; }
			$bat_com=$bat_com+$gpu_bat_com;
		}
		
		$to_return["bat_life"]=floatval($comp_data["acum"]["cap"])/($bat_com+1);

		//CALCULATE PRICE
		$to_return["price"]=0; $to_return["price_error"]=0;
		if($GLOBALS["prod_server"]==0)
		{
			foreach($comp_list as $comp)
			{
				if($comp=="cpu")
				{ $comp_data[$comp]["price"]=$comp_data[$comp]["price"]*0.8; }
				$to_return["price"]+=$comp_data[$comp]["price"];
			}
			//PRICE ERROR RANGE

			foreach($comp_list as $comp)
			{ $to_return["price_error"]=$to_return["price_error"]+($comp_data[$comp]["price"]*($comp_data[$comp]["err"]/100)); }
		}
		
		if($GLOBALS["new_prices"])
		{
			if($new_price_calc)
			{
				$real_price_data=set_price_market_price($conf,$comp_list);
				$to_return["price"]=$real_price_data["price"];
				$to_return["price_error"]=$real_price_data["price_error"];
			}
		}
	}
	return $to_return;
}

function ok_to_replace_existing_price($base_fix_price_key,$data)
{
	$to_return=True;
	$no_replce_existing_price=["acum"];
	foreach($no_replce_existing_price as $comp)
	{
		if($GLOBALS["fixed_conf_prices"][$base_fix_price_key][$comp]!=$data[$comp])
		{ $to_return=False; break; }
	}
	return $to_return;
}

function chunk(\Iterator $iterable, $size): \Iterator 
{
	while ($iterable->valid())
	{
		$closure = function() use ($iterable, $size)
		{
			$count=$size;
            while ($count-- && $iterable->valid())
			{
				yield $iterable->current();
				$iterable->next();
			}
        };
        yield $closure();
    }
}

function iterable_to_traversable(iterable $it): Traversable { yield from $it; }

function insert_function ($configs,$BATCH_SIZE,$INSERT_QUERY,$INSERT_ID_MODEL,$multicons,$server,$model_id,$comp_list)
{
	$reinsert=0;
	if($configs instanceof \Traversable){ $configs_array=iterator_to_array($configs); }else{$configs_array=$configs;} $configs=iterable_to_traversable($configs_array);
	foreach(chunk($configs, $BATCH_SIZE) as $i=>$chunk)
	{
		$chunk_array=iterator_to_array($chunk);
		
		if($GLOBALS["prod_server"]==1)
		{
			if($GLOBALS["new_prices"]==1)
			{
				if(!(in_array($model_id,$GLOBALS["no_all_conf_models"])))
				{
					$computed_chunk=old_calc_configurator($chunk_array,$model_id); //Prices from legacy configurators
					$chunk_array=get_prices_from_ml($chunk_array,$computed_chunk); //Prices from machine learning
				}
			}
		}
		$query = $INSERT_QUERY . implode(", ", array_map("values_to_str", $chunk_array));
		$cons=$multicons[$server];
		{ mysqli_query($cons, $query) or $reinsert=ver_duplicate(mysqli_error($cons)); }
		$query_id_model = $INSERT_ID_MODEL . implode(", ", array_map("values_to_str", array_map(function ($xs) { return array_slice($xs, 0, 2); }, $chunk_array)));
		mysqli_query($cons, $query_id_model); //Duplicate entry '15375138152867000320' for key 'PRIMARY' mysqli_query($sel_db,'what!') or some_func(mysqli_error($sel_db));
		set_time_limit(1000);
		//DELETE BAD CONF
		if(count($GLOBALS["questionable_confs"])>0)
		{
			echo "<br>GOT QUESTIONABLE CONFIGURATIONS FOR THIS MODEL! TRYING TO DELETE EXTRA GENERATED CONFIGURATIONS.<br>";
			foreach($GLOBALS["questionable_confs"] as $q_conf)
			{
				$COM_SQL_SEL="";
				foreach($comp_list as $comp)
				{ if($comp!="war") { $COM_SQL_SEL=$COM_SQL_SEL." AND `".$comp."`='".$q_conf[$comp]."'"; } }
				
				$SQL_SEL_1="SELECT `id` FROM `all_conf_".$q_conf["model"]."` WHERE 1=1 ".$COM_SQL_SEL." AND `war`<".$q_conf["war"]." LIMIT 1";
				$temp_result=mysqli_query($cons,$SQL_SEL_1);
				if(have_results($temp_result))
				{
					#TIME DO DELETE
					$SQL_SEL_2="SELECT `id` FROM `all_conf_".$q_conf["model"]."` WHERE 1=1 ".$COM_SQL_SEL." AND `war`=".$q_conf["war"]." LIMIT 50";
					$temp_result_2=mysqli_query($cons,$SQL_SEL_2);
					if(have_results($temp_result_2))
					{
						while($temp_row=mysqli_fetch_assoc($temp_result_2))
						{
							$id_to_delete=$temp_row["id"];
							$SQL_DELETE="DELETE FROM `all_conf` WHERE `id`='".$temp_row["id"]."' LIMIT 1";
							mysqli_query($cons,$SQL_DELETE);
							$SQL_DELETE="DELETE FROM `all_conf_".$model_id."` WHERE `id`='".$temp_row["id"]."' LIMIT 1";
							mysqli_query($cons,$SQL_DELETE);
						}
						unset($temp_row);
						mysqli_free_result($temp_result_2);
					}
					mysqli_free_result($temp_result);
				}
			}
			$GLOBALS["questionable_confs"]=array();
		}
		//break 2;
	}
	
	//THERE IS STILL THE POSSIBILITY OF ID COLLUSION AND THIS IS WHERE WE FIX IT
	if($reinsert)
	{
		$newid=gmp_add($reinsert,"1");
		$i=0; $new_configs=array();
		foreach($configs_array as $value_i)
		{
			if($reinsert==$value_i[0])
			{$i=1; $value_i[0]=$newid;}
		
			if($i)
			{ $new_configs[]=$value_i;}
		}
		insert_function($new_configs,$BATCH_SIZE,$INSERT_QUERY,$INSERT_ID_MODEL,$multicons,$server,$model_id,$comp_list);
	}
}

function ver_duplicate($error_str)
{
	preg_match('/Duplicate entry \'(\d+)\'/', $error_str, $matches);
	if(isset($matches[1]) && $matches[1])
	{ return $matches[1]; }
	else
	{ echo "Generation failed!"; die($error_str); }
}

function values_to_str($xs) { return "(" . implode(", ", $xs) . ")"; }

function local_multiquery($con,$sql_query,$return_query_nr=NULL)
{
	$to_return=NULL;
	if (mysqli_multi_query($con, $sql_query))
	{
		do
		{
			if ($result=mysqli_store_result($con))
			{
				while ($row=mysqli_fetch_row($result)) { if($return_query_nr!==NULL && isset($row[intval($return_query_nr)])){ $to_return=$row[intval($return_query_nr)];}}
				mysqli_free_result($result);
			} 
		}
		while (mysqli_more_results($con) && mysqli_next_result($con));
	}
	return $to_return;
}
?>