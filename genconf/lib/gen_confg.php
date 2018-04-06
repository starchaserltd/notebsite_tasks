<?php
ini_set('memory_limit', '512M');
echo "Start";
$multicons=dbs_connect();
foreach ($multicons as $cons)
{ var_dump($cons); echo "<br>";}
echo "<br><br>";

/////// FIRST LETS GET THE COMPONENT FILTER LIST
	if($cpu_s)
	{
		require_once("lib/cpu_search.php");
		$cpu_list=search_cpu ($cpu_prod, $cpu_model, $cpu_ldmin, $cpu_ldmax, $cpu_status, $cpu_socket, $cpu_techmin, $cpu_techmax, $cpu_cachemin, $cpu_cachemax, $cpu_clockmin, $cpu_clockmax, $cpu_turbomin, $cpu_turbomax, $cpu_tdpmax,$cpu_tdpmin, $cpu_coremin, $cpu_coremax, $cpu_intgpu, $cpu_misc, $cpu_ratemin, $cpu_ratemax, $pricemin, $budgetmax, 0.1);
	}

	if($display_s)
	{
		require_once("lib/display_search.php");
		$display_list=search_display ($display_model, $display_sizemin, $display_sizemax, $display_format, $display_hresmin, $display_hresmax, $display_vresmin, $display_vresmax, $display_surft, $display_backt, $display_touch,  $display_misc, $display_resolutions, $display_ratingmin, $display_ratingmax, $pricemin, $budgetmax, 0.1);
	}

	if($gpu_s)
	{
		require_once("lib/gpu_search.php");
		$gpu_list=search_gpu ($gpu_typegpumin, $gpu_typegpumax, $gpu_prod, $gpu_model, $gpu_arch, $gpu_techmin, $gpu_techmax, $gpu_shadermin, $gpu_cspeedmin, $gpu_cspeedmax, $gpu_sspeedmin, $gpu_sspeedmax, $gpu_mspeedmin, $gpu_mspeedmax, $gpu_mbwmin, $gpu_mbwmax, $gpu_mtype, $gpu_maxmemmin, $gpu_maxmemmax, $gpu_sharem, $gpu_powermin, $gpu_powermax, $gpu_misc, $gpu_ratemin, $gpu_ratemax, $pricemin ,$budgetmax, 0.1);
	}

	if($acum_s)
	{
		require_once("lib/acum_search.php");
		$acum_list=search_acum ($acum_tipc, $acum_nrcmin, $acum_nrcmax, $acum_volt, $acum_capmin, $acum_capmax, $pricemin, $budgetmax, $acum_misc, 0.1);
	}

	if($war_s)
	{
		require_once("lib/war_search.php");
		$war_list=search_war ($war_prod, $war_yearsmin, $war_yearsmax, $war_typewar, $war_misc, $war_ratemin, $war_ratemax, $pricemin,$budgetmax);
	}

	if($hdd_s)
	{
		require_once("lib/hdd_search.php");
		$hdd_list=search_hdd ($hdd_model, $hdd_capmin, $hdd_capmax, $hdd_type, $hdd_readspeedmin, $hdd_readspeedmax, $hdd_writesmin, $hdd_writesmax, $hdd_rpmmin, $hdd_rpmmax, $hdd_misc, $hdd_ratemin, $hdd_ratemax, $pricemin, $budgetmax);
	}
	
	if($shdd_s)
	{
		$shdd_list=search_hdd ($hdd_model, $hdd_capmin, $hdd_capmax, $hdd_type, $hdd_readspeedmin, $hdd_readspeedmax, $hdd_writesmin, $hdd_writesmax, $hdd_rpmmin, $hdd_rpmmax, $hdd_misc, $hdd_ratemin, $hdd_ratemax, $pricemin, $budgetmax);
		$shdd_list[0]=array("price"=> 0, "rating"=> 0, "err"=> 0, "cap"=> 0, "type"=> "N/A");
	}
	if($wnet_s)
	{
		require_once("lib/wnet_search.php");
		$wnet_list=search_wnet ($wnet_prod, $wnet_model, $wnet_misc, $wnet_speedmin, $wnet_speedmax, $wnet_ratemin, $wnet_ratemax, $pricemin, $budgetmax);
	}

	if($sist_s)
	{
		require_once("lib/sist_search.php");
		$sist_list=search_sist ($sist_sist, $sist_vers, $sist_misc, $pricemin, $budgetmax);
	}

	if($odd_s)
	{
		require_once("lib/odd_search.php");
		$odd_list=search_odd ($odd_type, $odd_prod, $odd_misc, $odd_speedmin, $odd_speedmax, $odd_ratemin, $odd_ratemax, $odd_pricemin, $budgetmax);
	}
		
	if($mem_s)
	{
		require_once("lib/mem_search.php");
		$mem_list=search_mem ($mem_prod, $mem_capmin, $mem_capmax, $mem_stan, $mem_freqmin, $mem_freqmax, $mem_type, $mem_latmin, $mem_latmax, $mem_voltmin, $mem_voltmax, $mem_misc, $mem_ratemin, $mem_ratemax, $pricemin,$budgetmax);
	}

	if($mdb_s)
	{
		require_once("lib/mdb_search.php");
		$mdb_list=search_mdb ($mdb_prod, $mdb_model, $mdb_ramcap, $mdb_gpu, $mdb_chip, $mdb_socket, $mdb_interface, $mdb_netw, $mdb_hdd, $mdb_misc, $mdb_ratemin, $mdb_ratemax, $pricemin,$budgetmax);
	}

	if($chassis_s)
	{
		require_once("lib/chassis_search.php");
		$chassis_list=search_chassis ($chassis_prod, $chassis_model, $chassis_thicmin, $chassis_thicmax, $chassis_depthmin, $chassis_depthmax, $chassis_widthmin, $chassis_widthmax, $chassis_color, $chassis_weightmin, $chassis_weightmax, $chassis_made, $chassis_ports, $chassis_vports, $chassis_web, $chassis_touch, $chassis_misc, $chassis_ratemin, $chassis_ratemax, $pricemin,$budgetmax);
	}


//IN CASE I DON'T DO THE FILTER FOR SOME COMPONENTS, NEVER USE THIS FUNCTION ON CPU,GPU,DISPLAY,ACUM,SISTC
	function nolist ($component,$ids,$tdp)
	{
		
	/*	
		if(strcmp($component,"WNET")==0)
		{
			if($tdp>0)
			{
				$fields="id,price,rating,err,gpu,tdp";	
			}
			else
			{
				$fields="id,price,rating,err,gpu";
			}
		}*/

		$fields="id,price,rating,err";
		if($component=="HDD")
		{
		$fields="id,price,rating,err,cap,type,model";	
		}
		if($component=="DISPLAY")
		{
		$fields="id,price,rating,err,size, (`hres`*`vres`) as res,backt";	
		}
		if($component=="MDB")
		{
		$fields="id,price,rating,err,msc,hdd";	
		}
		$uni_return = array();
		
		foreach($ids as $x)
		{
			$sel_uni="SELECT ".$fields." FROM $component WHERE id=$x";
			$result = mysqli_query($GLOBALS['con'], "$sel_uni");

			if($result)
			{
				while($rand = mysqli_fetch_array($result)) 
				{ 
					if($component!="HDD")
					{
					$uni_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>floatval($rand[3]));
					}
					else
					{
					$uni_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>floatval($rand[3]),"cap"=>intval($rand[4]),"type"=>strval($rand[5]),"model"=>strval($rand[6]));
					}
					if($component=="MDB")
					{ 
						if(stripos($rand[4],"optimus")===false && stripos($rand[4],"enduro")===false) { $rand[4]=0; } else {$rand[4]=1;}
						$uni_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]),"optimus"=>$rand[4],"hdd"=>$rand[5]);
					}	
				}
				mysqli_free_result($result);
			}
		}
	       // echo "Done";
		return($uni_return);
	}



//$c_battery_life=$acum["cap"]/((($cpu["tdp"]/4)+($display["size"]-10)+1)+$gpu["tdp"]/5);

	 /********* CREATE PERMANENT TABLE FOR CONFIGURATIONS ******/
	 // Don't ask how it's done, the procedures for it are stored in MariaDB

if (isset($_SESSION['temp_configs'])) {
    $sel2="DROP TABLE IF EXISTS notebro_temp.".$_SESSION['temp_configs'].";";
	mysqli_query($multicons[$server],$sel2) or die(mysqli_error());

}	

$sel2 = 'USE notebro_temp; CALL delete_tbls(); CALL allconf_tbl(); SELECT @tablename; ';
//mysqli_multi_query($cons, $sel2) or die (mysqli_error ($cons) . " The query was:" . $sel2);
$cons=$multicons[$server];

if (mysqli_multi_query($cons,$sel2)) { mysqli_next_result($cons);
	do {
		// Store first result set 
		if ($result=mysqli_store_result($cons)) {
			while ($row=mysqli_fetch_row($result)) {
				$temp_table=$row[0];
			}
			mysqli_free_result($result);
		}
	}
	while (mysqli_more_results($cons) && mysqli_next_result($cons));
}

mysqli_query($con,"USE notebro_db;");

$nr_configs=1;

///// BUILD TEMPORARY CONFIGURATIONS TABLE /////	
function generate_configs(
        $con,
        $multicons,
	$model_id,
        $cpu_list,
        $gpu_list,
        $display_list,
        $acum_list,
        $mdb_list,
        $war_list,
        $mem_list,
        $hdd_list,
        $sist_list,
        $shdd_list,
        $wnet_list,
        $odd_list,
        $chassis_list,
        $cpu_s,
        $gpu_s,
        $display_s,
        $acum_s,
        $sist_s,
        $war_s,
        $hdd_s,
        $shdd_s) {

    $ex=0;

	global $nr_configs, $cpu_i, $gpu_i, $display_i, $wnet_i, $hdd_i, $odd_i, $war_i, $shdd_i, $acum_i, $chassis_i, $mdb_i, $mem_i, $sist_i;


			$sel3 = "SELECT * FROM notebro_db.MODEL WHERE id=" . $model_id . " LIMIT 1";
			echo $sel3; echo "<br>";
			$result=mysqli_query($con,$sel3) or die(mysqli_error($con));
			while ($raw = mysqli_fetch_array($result)) 
			{

				$raw["id"]=intval($raw["id"]);
				
				// $raw["cpu"] $raw["display"] $raw["mem"] $raw["hdd"] $raw["shdd"] $raw["gpu"] $raw["wnet"] $raw["odd"] $raw["mdb"] $raw["chassis"] $raw["acum"] $raw["warranty"] $raw["sist"] 

				
				$cpu_model=explode(",",$raw["cpu"]);
				if($cpu_list)
				{

					$cpu_conf=array_intersect($cpu_model,array_keys($cpu_list));
				}
				else
					echo "THERE IS A MAJOR ERROR!";
				
				if($cpu_conf)
				{
					
					
					$gpu_model=explode(",",$raw["gpu"]);
					if($gpu_list)
					{
						$gpu_conf=array_intersect($gpu_model,array_keys($gpu_list));
						//var_dump($gpu_list);
					}
					else
						echo "THERE IS A MAJOR ERROR!";
					
					if($gpu_conf)
					{
					
						$display_model=explode(",",$raw["display"]);
						if($display_list)
						{
							$display_conf=array_intersect($display_model,array_keys($display_list));
						}
						else
							echo "THERE IS A MAJOR ERROR!";
						
						if($display_conf)
						{
					
							$mem_model=explode(",",$raw["mem"]);
							if($GLOBALS["mem_s"])
							{
								$mem_conf=array_intersect($mem_model,array_keys($mem_list));
							}
							else
							{
								$mem_list=nolist("MEM",$mem_model,0);
								//var_dump($mem_list)." ";
								$mem_conf=array_keys($mem_list);
								//var_dump($mem_conf);								
							}
						
							if($mem_conf)
							{
					
								$hdd_model=explode(",",$raw["hdd"]);
								if($hdd_s)
								{
									$hdd_conf=array_intersect($hdd_model,array_keys($hdd_list));
								}
								else
								{
									$hdd_list=nolist("HDD",$hdd_model,0);
									$hdd_conf=array_keys($hdd_list);				
								}	
						
								if($hdd_conf)
								{
									//var_dump($raw["shdd"]);
									$shdd_model=explode(",",$raw["shdd"]);
									$shdd_model[]=0;
									if($shdd_s)
									{
										$shdd_conf=array_intersect($shdd_model,array_keys($shdd_list));
									}
									else
									{ //var_dump($model_id); var_dump($shdd_model); echo "<br><br><br><br>";
										if(($shdd_model[0]!=0) || (count($shdd_model)>1))
										{
										$shdd_list=nolist("HDD",$shdd_model,0);
										$shdd_conf=array_keys($shdd_list);				
										//echo "balbla";
										//var_dump($shdd_list);
										}
										else
										{
										unset($shdd_conf);
										$shdd_conf[]=0;
										}
									}
									sort($shdd_conf);
									var_dump($shdd_conf);
									
									if($shdd_conf)
									{
										
										$wnet_model=explode(",",$raw["wnet"]);
									//	var_dump($wnet_model);
										if($GLOBALS["wnet_s"])
										{
											$wnet_conf=array_intersect($wnet_model,array_keys($wnet_list));
										}
										else
										{
											$wnet_list=nolist("WNET",$wnet_model,0);
											$wnet_conf=array_keys($wnet_list);	
											}
										
										if($wnet_conf)
										{
					
											$odd_model=explode(",",$raw["odd"]);
											if($GLOBALS["odd_s"])
											{
												$odd_conf=array_intersect($odd_model,array_keys($odd_list));
											}
											else
											{
												$odd_list=nolist("ODD",$odd_model,0);
												$odd_conf=array_keys($odd_list);				
											}
							
											if($odd_conf)
											{
					
												$mdb_model=explode(",",$raw["mdb"]);
												if($GLOBALS["mdb_s"])
												{
													$mdb_conf=array_intersect($mdb_model,array_keys($mdb_list));
												}
												else
												{
													$mdb_list=nolist("MDB",$mdb_model,0);
													$mdb_conf=array_keys($mdb_list);				
												}
							
												if($mdb_conf)
												{
													$chassis_model=explode(",",$raw["chassis"]);
													if($GLOBALS["chassis_s"])
													{
														$chassis_conf=array_intersect($chassis_model,array_keys($chassis_list));
													}
													else
													{
														$chassis_list=nolist("CHASSIS",$chassis_model,0);
														$chassis_conf=array_keys($chassis_list);				
													}
						
													if($chassis_conf)
													{
														$acum_model=explode(",",$raw["acum"]);
														if($acum_list)
														{
															$acum_conf=array_intersect($acum_model,array_keys($acum_list));
															
														}
														else
															echo "WE HAVE A MAJOR ERROR FOR ACUM";
															
														if($acum_conf)
														{
					
															$war_model=explode(",",$raw["warranty"]);
															if($war_list)
															{
																$war_conf=array_intersect($war_model,array_keys($war_list));
															}
															else
																echo "WE HAVE A MAJOR ERROR FOR WAR";

															if($war_conf)
															{
					
																$sist_model=explode(",",$raw["sist"]);
																if($sist_s)
																{
																	$sist_conf=array_intersect($sist_model,array_keys($sist_list));
																}
																else
																{
																	$sist_list=nolist("SIST",$sist_model,0);
																	$sist_conf=array_keys($sist_list);
																						
																}
							
																if($sist_conf)
																{
																
																//echo var_dump($sist_conf)."aaaa";
																//nothing. We're done.
																//echo "we got to the end";
																
																	 /********* BUILD PERMANENT RESULTS TABLE ******/	
																	 var_dump($cpu_conf);
																	 var_dump($gpu_conf);
																	echo "DISPLAY:"; var_dump($display_conf);
																	 var_dump($acum_conf);
																	 var_dump($mdb_conf);
																	 var_dump($war_conf);
																	 var_dump($mem_conf);
																	echo "HDD: "; var_dump($hdd_conf);
																	echo "SIST: "; var_dump($sist_conf);
																	 var_dump($shdd_conf);
																	 var_dump($wnet_conf);
																	 var_dump($odd_conf);
																	 var_dump($chassis_conf);
																	 
																	require_once("lib/var_conf.php");
																	foreach($cpu_conf as $cpu_id)
																	{
																		$c_battery_life=0;
																		$c_price=0;
																		$c_rating=0;
																		$c_err=0;
																		$cpu=$cpu_list[$cpu_id];

																		$c_rating+=$cpu["rating"]*$cpu_i;
																		$cpu["price"]=$cpu["price"]*0.8;
																		$cpu_bat_life=(0.5+floatval($cpu["tdp"])/7);
																		$c_battery_life+=$cpu_bat_life;
																		//echo "consumption:".$c_battery_life."endofc<br>";
																		$c_price+=$cpu["price"]; //echo $c_price."cpu";
																		$c_err+=$cpu["price"]*$cpu["err"]/100;
																	//	echo " cpuprice=".$cpu["price"]." ";
																		
																			foreach($gpu_conf as $gpu_id)
																			{						
																				$gpu=$gpu_list[$gpu_id];
																				$c_rating+=$gpu["rating"]*$gpu_i;
																				$c_price+=$gpu["price"]; // echo $c_price."gpu";
																				//echo $c_battery_life; echo "a";
																				if($gpu["arch"]=="Pascal"){$gpu["tdp"]/=1.5;}
																				$gpu_bat_life=floatval($gpu["tdp"])/8;
																				if($gpu["type"]==0){ $gpu_bat_life=0.2; }
																				$testvalue=$c_battery_life; $c_battery_life+=$gpu_bat_life;
																				//echo "consumption:".$c_battery_life."endofc<br>";
																				$c_err+=$gpu["price"]*$gpu["err"]/100;
																				if(($gpu["type"]==0 && $gpu_id==$cpu["gpu"]) || $gpu["type"]>0)
																					foreach($display_conf as $display_id)
																					{

																						$display=$display_list[$display_id];
																						$c_rating+=$display["rating"]*$display_i;
																						$c_price+=$display["price"]; // echo $c_price."display";
																								
																						if(stripos($display["backt"],"OLED")!==FALSE)
																					    { $c_display_pwc=((floatval($display["size"])*0.10)+(pow(intval($display["res"]),0.5)*0.00255-3.4))*0.6; }
																						else
																						{ $c_display_pwc=((floatval($display["size"])*0.10)+(pow(intval($display["res"]),0.5)*0.00255-3.4))*0.7; }
																						if($display['touch']==1){ $c_display_pwc+=(floatval($display["size"])*floatval($display["size"]))/400; }	
																							$c_battery_life+=$c_display_pwc;
																						//echo "consumption:".$display["res"]."res".$c_battery_life."endofc<br>";
																						$c_err+=$display["price"]*$display["err"]/100;
																							foreach($acum_conf as $acum_id)
																							{
																								//Whr (de la baterie) /( (TDP CPU /4+5) + TDP GPU/5) > 4)
																								$acum=$acum_list[$acum_id];
																								
																							//	if($battery_life>0)
																							//		$battery_life=$acum["cap"]/((($cpu["tdp"]/4)+($display["size"]*0.1)+($display["res"]*0.00000075)+$gpu["tdp"]/5));

																								$c_rating+=$acum["rating"]*$acum_i;
																								$c_price+=$acum["price"]; // echo $c_price."acum";
																								//echo "batlife".$c_battery_life_f."batlife<br>";
																								$c_err+=$acum["price"]*$acum["err"]/100;
																										foreach($mdb_conf as $mdb_id)
																										{
																											$mdb=$mdb_list[$mdb_id];
																									//echo $c_battery_life; echo "a";
																											if($mdb["optimus"] && $gpu_bat_life>3){ $c_battery_life-=$gpu_bat_life; $c_battery_life+=3; }
																											$c_rating+=$mdb["rating"]*$mdb_i;
																											$c_price+=$mdb["price"];  //echo $mdb_id."mdbid"; var_dump($mdb_list); echo "list"; var_dump($mdb); echo $c_price."mdb";
																											$c_err+=$mdb["price"]*$mdb["err"]/100; 

																												foreach($war_conf as $war_id)
																												{
																													$war=$war_list[$war_id];
																													$c_rating+=$war["rating"]*$war_i;
																													$c_price+=$war["price"]; // echo $c_price."war";			
																													$c_err+=$war["price"]*$war["err"]/100; 
																													
																														foreach($mem_conf as $mem_id)
																														{
																															$mem=$mem_list[$mem_id];
																															$c_rating+=$mem["rating"]*$mem_i;
																															$c_price+=$mem["price"]; // echo $c_price."mem";
																															$c_err+=$mem["price"]*$mem["err"]/100;
																														
																																foreach($hdd_conf as $hdd_id)
																																{
																																	$c_capacity=0;
																																	$hdd=$hdd_list[$hdd_id];
																																	$c_rating+=$hdd["rating"]*$hdd_i;
																																	$c_price+=$hdd["price"]; // echo $c_price."hdd";	
																																	$c_err+=$hdd["price"]*$hdd["err"]/100;
																																	$c_capacity+=$hdd["cap"];
																																	switch($hdd["type"])
																																	{
																																		case "SSD":
																																		$hdd_battery_life=0.5;
																																		break;
																																		case "HDD":
																																		$hdd_battery_life=1;
																																		break;
																																		case "SSHD":
																																		$hdd_battery_life=0.9;
																																		break;
																																		case "EMMC":
																																		$hdd_battery_life=0.3;
																																		break;																																			
																																		}
																																	//	var_dump($hdd["type"]);
																																		$c_battery_life+=$hdd_battery_life;																																
																																		foreach($sist_conf as $sist_id)
																																		{
																																			$sist=$sist_list[$sist_id];
																																			$c_price+=$sist["price"]; // echo $c_price."sist";
																																			$c_err+=$sist["price"]*$sist["err"]/100; 
																																			if(isset($sist["rating"])){$c_rating+=$sist["rating"]*$sist_i;}

																																				foreach($shdd_conf as $shdd_id)
																																				{
																																					$shdd_battery_life=0;
																																					$shdd=$shdd_list[$shdd_id];
																																					$mdb_2sata=0; if(stripos($mdb["hdd"],"2 x SATA")!==FALSE){ $mdb_2sata=1; }
																																					if((!$mdb_2sata && stripos($hdd["model"],"M.2")===FALSE && (stripos($shdd["model"],"N/A")===FALSE))||( $mdb_2sata && (stripos($hdd["type"],"SSD")===FALSE) && stripos($shdd["model"],"N/A")===FALSE ) ) { break 1; }
																																					$c_rating+=$shdd["rating"]*$shdd_i;
																																					$c_price+=$shdd["price"];  //echo $shdd["price"]."-".$shdd_id."-".$c_price."shdd";
																																					$c_err+=$shdd["price"]*$shdd["err"]/100; 																																					
																																					$c_capacity+=$shdd["cap"];
																																					//var_dump($shdd_conf);
																																																															
																																					switch($shdd["type"])
																																					{
																																						case "SSD":
																																						$shdd_battery_life=0.5;
																																						break;
																																						case "HDD":
																																						$shdd_battery_life=1;
																																						case "SSHD":
																																						$shdd_battery_life=0.9;
																																						break;
																																						case "EMMC":
																																						$shdd_battery_life=0.3;
																																						break;
																																					}
																																							
																																					$c_battery_life+=$shdd_battery_life;											

																																						foreach($wnet_conf as $wnet_id)
																																						{
																																							$wnet=$wnet_list[$wnet_id];
																																							$c_rating+=$wnet["rating"]*$wnet_i;
																																							$c_price+=$wnet["price"]; // var_dump($wnet); echo $c_price."wnet";
																																							$c_err+=$wnet["price"]*$wnet["err"]/100;
																													
																																							foreach($odd_conf as $odd_id)
																																							{
																																								$odd=$odd_list[$odd_id];
																																								$c_rating+=$odd["rating"]*$odd_i;
																																								$c_price+=$odd["price"]; // echo $c_price."odd";
																																								$c_err+=$odd["price"]*$odd["err"]/100;
																															
																																								foreach($chassis_conf as $chassis_id)
																																								{ 
																																									$chassis=$chassis_list[$chassis_id];
																																									$c_rating+=$chassis["rating"]*$chassis_i;
																																									$c_price+=$chassis["price"]; // var_dump($chassis); echo $c_price."chass";
																																									$c_err+=$chassis["price"]*$chassis["err"]/100;

																																									///////FINAL TEST PRICE IS WITHING BOUNDRY ///////
																																									
																																									$c_battery_life_f=floatval($acum["cap"])/($c_battery_life+1);
																																									$c_rating_f=round($c_rating);
																																									//////// INSERT CONFIG IN DB//////////
																																									$ex++; $c_value=$c_rating/$c_price;
																									//echo "<br>".$cpu["price"]." ".$display["price"]." ".$mem["price"]." ".$hdd["price"]." ".$gpu["price"]." ".$wnet_key."-".$wnet["price"]." ".$odd_key."-".$odd["price"]." ".$mdb_key."-".$mdb["price"]." ".$chassis_key."-".$chassis["price"]." ".$acum_key."-".$acum["price"]." ".$war_key."-".$war["price"]." ".$sist_key."-".$sist["price"];
																									//echo "<br>cpu".$cpu_id."-".$cpu["rating"]."-".$cpu_i." display".$display_id."-".$display["rating"]."-".$display_i." mem".$mem_id."-".$mem["rating"]."-".$mem_i." hdd".$hdd_id."-".$hdd["rating"]."-".$hdd_i." shdd".$shdd_id."-".$shdd["rating"]."-".$shdd_i." gpu".$gpu_id."-".$gpu["rating"]."-".$gpu_i." wnet".$wnet_id."-".$wnet["rating"]."-".$wnet_i." odd".$odd_id."-".$odd["rating"]."-".$odd_i." mdb".$mdb_id."-".$mdb["rating"]."-".$mdb_i." chassis".$chassis_id."-".$chassis["rating"]."-".$chassis_i." acum".$acum_id."-".$acum["rating"]."-".$acum_i." war".$war_id."-".$war["rating"]."-".$war_i." ".$sist_id."-".$sist["rating"]."-".$sist_i."aa".$c_rating;
																									//max rand is 4294967294
																									//$randid=rand(1,10000000);
																									$newid=hexdec(hash('fnv1a64',(implode(",",[$raw["id"], $cpu_id, $display_id, $mem_id, $hdd_id, $shdd_id, $gpu_id, $wnet_id, $odd_id, $mdb_id, $chassis_id, $acum_id, $war_id, $sist_id]))));
																									yield array($newid, $raw["id"], $cpu_id, $display_id, $mem_id, $hdd_id, $shdd_id, $gpu_id, $wnet_id, $odd_id, $mdb_id, $chassis_id, $acum_id, $war_id, $sist_id, $c_rating_f, $c_price, $c_value, $c_err, $c_battery_life_f, $c_capacity);
																									$randid=$nr_configs;
																									//$sel2 = "INSERT INTO all_conf (id, model,cpu, display, mem, hdd, shdd, gpu, wnet, odd, mdb, chassis, acum, war, sist, rating, price, value, err, batlife) VALUES ($randid, $raw[id], $cpu_id, $display_id, $mem_id, $hdd_id, $shdd_id, $gpu_id, $wnet_id, $odd_id, $mdb_id, $chassis_id, $acum_id, $war_id, $sist_id, $c_rating, $c_price, $c_value, $c_err, $c_battery_life_f)";
																									/*																				
																									if($nr_configs==11650311)
																									{
																									echo $cpu_id; echo "aaa"; echo $display_id; echo "aaa"; echo $mem_id; echo "aaa"; echo $hdd_id; echo "aaa"; echo $shdd_id; echo "aaa"; echo $gpu_id; echo "aaa"; echo $wnet_id; echo "aaa"; echo $odd_id; echo "aaa"; echo $mdb_id; echo "aaa"; echo $chassis_id; echo "aaa"; echo $acum_id; echo "aaa"; echo $war_id; echo "aaa"; echo $sist_id; echo "aaa"; echo $c_rating; echo "aaa"; echo $c_price; echo "aaa"; echo $c_value; echo "aaa"; echo $c_err; echo "aaa"; echo $c_battery_life_f; echo "aaa"; echo $c_capacity; echo "[break]";
																									echo $cpu["rating"]*$cpu_i; echo "bbb"; echo $display["rating"]*$display_i; echo "bbb"; echo $mem["rating"]*$mem_i; echo "bbb"; echo $hdd["rating"]*$hdd_i; echo "bbb"; echo $shdd["rating"]*$shdd_i; echo "bbb"; echo $gpu["rating"]*$gpu_i; echo "bbb"; echo $wnet["rating"]*$wnet_i; echo "bbb"; echo $odd["rating"]*$odd_i; echo "bbb"; echo $mdb["rating"]*$mdb_i; echo "bbb"; echo $chassis["rating"]*$chassis_i; echo "bbb"; echo $acum["rating"]*$acum_i; echo "bbb"; echo $war["rating"]*$war_i; echo "bbb"; if(isset($sist["rating"])){ echo $sist["rating"]*$sist_i; echo "b";} echo "[break]";
																									echo $cpu_bat_life; echo "ccc"; echo $c_display_pwc; echo "ccc"; echo $hdd_battery_life; echo "ccc"; echo $shdd_battery_life; echo "ccc"; echo $gpu_bat_life;	echo "ccc"; echo $acum["cap"]; echo "[break]";
																									echo $cpu["price"]; echo "ddd"; echo $display["price"]; echo "ddd"; echo $mem["price"]; echo "ddd"; echo $hdd["price"]; echo "ddd"; echo $shdd["price"]; echo "ddd"; echo $gpu["price"]; echo "ddd"; echo $wnet["price"]; echo "ddd"; echo $odd["price"]; echo "ddd"; echo $mdb["price"]; echo "ddd"; echo $chassis["price"]; echo "ddd"; echo $acum["price"]; echo "ddd"; echo $war["price"]; echo "ddd"; if(isset($sist["price"])){ echo $sist["price"]; echo "b";} echo "[EOL]";
																									}
																									*/
																									$nr_configs++;
																									
																									//echo $sel2; echo "<br>";
																									//echo "<br>"; 
																									//monetdb_query($sel2);
																									/*
																									while (!mysqli_query($cons,$sel2))
																									{ 
																										$randid=rand(1,10000000); 
																										$sel2 = "INSERT INTO notebro_temp.$temp_table (id, model,cpu, display, mem, hdd, shdd, gpu, wnet, odd, mdb, chassis, acum, war, sist, rating, price, value, err, batlife) VALUES ($randid, $raw[id], $cpu_id, $display_id, $mem_id, $hdd_id, $shdd_id, $gpu_id, $wnet_id, $odd_id, $mdb_id, $chassis_id, $acum_id, $war_id, $sist_id, $c_rating, $c_price, $c_value, $c_err, $c_battery_life_f)";
																									}
																								*/
																																													
																																
																																									$c_price-=$chassis["price"];
																																									$c_rating-=$chassis["rating"]*$chassis_i; 
																																									$c_err-=$chassis["price"]*$chassis["err"]/100;								
																																								}
																																								$c_price-=$odd["price"]; $c_rating-=$odd["rating"]*$odd_i; $c_err-=$odd["price"]*$odd["err"]/100;
																																							}
																																							$c_price-=$wnet["price"]; $c_rating-=$wnet["rating"]*$wnet_i; $c_err-=$wnet["price"]*$wnet["err"]/100;
																																						}
																																					$c_price-=$shdd["price"]; $c_rating-=$shdd["rating"]*$shdd_i; $c_err-=$shdd["price"]*$shdd["err"]/100; $c_capacity-=$shdd["cap"]; $c_battery_life-=$shdd_battery_life; 
																																				}
																																				$c_price-=$sist["price"]; $c_err-=$sist["price"]*$sist["err"]/100; if(isset($sist["rating"])){$c_rating-=$sist["rating"]*$sist_i;}
																																		}
																																	$c_price-=$hdd["price"]; $c_rating-=$hdd["rating"]*$hdd_i; $c_err-=$hdd["price"]*$hdd["err"]/100; $c_battery_life-=$hdd_battery_life;
																																}
																																$c_price-=$mem["price"]; $c_rating-=$mem["rating"]*$mem_i; $c_err-=$mem["price"]*$mem["err"]/100;
																														}
																														$c_price-=$war["price"]; $c_rating-=$war["rating"]*$war_i; $c_err-=$war["price"]*$war["err"]/100;
																												}		
																												$c_price-=$mdb["price"]; $c_rating-=$mdb["rating"]*$mdb_i; $c_err-=$mdb["price"]*$mdb["err"]/100; if($mdb["optimus"]){ $c_battery_life+=$gpu_bat_life; $c_battery_life-=3; }
																										}
																										$c_price-=$acum["price"]; $c_rating-=$acum["rating"]*$acum_i; $c_err-=$acum["price"]*$acum["err"]/100;
																								}
																								$c_price-=$display["price"]; $c_rating-=$display["rating"]*$display_i; $c_err-=$display["price"]*$display["err"]/100; $c_battery_life-=$c_display_pwc;
																					}
																	$c_price-=$gpu["price"]; $c_rating-=$gpu["rating"]*$gpu_i; $c_err-=$gpu["price"]*$gpu["err"]/100; $c_battery_life-=$gpu_bat_life; //if($testvalue-$c_battery_life) { echo $testvalue." ".$shdd_battery_life." ".$hdd_battery_life." ".$c_display_pwc." ".($gpu["tdp"]/7)." ".$c_battery_life." ".$testvalue-$c_battery_life; echo "<br>"; }
																			}
																	}
																// end of for each	
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}			
			}	
		
// }	
}


function chunk(\Iterator $iterable, $size): \Iterator {
    while ($iterable->valid()) {
        $closure = function() use ($iterable, $size) {
            $count = $size;
            while ($count-- && $iterable->valid()) {
                yield $iterable->current();
                $iterable->next();
            }
        };
        yield $closure();
    }
}


function values_to_str($xs) {
    return "(" . implode(", ", $xs) . ")";
}

function chunk_to_json($chunk) {
	$xss = array_map("config_list_to_dict", $chunk);
	$xss = array_map(function ($xs) { return array_map('intval', $xs); }, $xss);
	$j = json_encode(array("ids" => $xss));
	return $j;
}

function post_request_to_web_service($json_data) {
	$opts=array(
		'http' => array(
			'method' => "POST",
			'header' => "Content-Type: application/x-www-form-urlencoded",
			'content' => $json_data
		)
	);
	$context = stream_context_create($opts);
	$file=file_get_contents('http://0.0.0.0:6667/predict',false,$context);
	return json_decode($file,false);
}

function replace_prices(&$chunk_array, $prices) {
	$i=0;
	foreach($chunk_array as $key => $row)
	{
		$chunk_array[$key][16]=$prices[$i]; //price
		$chunk_array[$key][17]=$chunk_array[$key][15]/$prices[$i]; //value
		$chunk_array[$key][18]=$prices[$i]*0.075; //err
		$i++;
	}
}

$BATCH_SIZE = 15000;
$time_start = microtime(true);

mysqli_query($con,"CALL ABCORDER();");
$query_model = "SELECT DISTINCT id FROM notebro_db.MODEL";
$result = mysqli_query($con,$query_model);

$model_ids = array();
while($row = mysqli_fetch_row($result)) {
	array_push($model_ids, $row[0]);
}

mysqli_free_result($result);

var_dump($model_ids);

foreach($model_ids as $model_id) {
	if(!isset($mdb_list)){ $mdb_list=array();} if(!isset($mem_list)){ $mem_list=array();} if(!isset($shdd_list)){ $shdd_list=array();}
	if(!isset($wnet_list)){ $wnet_list=array();} if(!isset($odd_list)){ $odd_list=array();} if(!isset($chassis_list)){ $chassis_list=array();}

	$configs = generate_configs(
		$con,
		$multicons,
		$model_id,
		$cpu_list,
		$gpu_list,
		$display_list,
		$acum_list,
		$mdb_list,
		$war_list,
		$mem_list,
		$hdd_list,
		$sist_list,
		$shdd_list,
		$wnet_list,
		$odd_list,
		$chassis_list,
		$cpu_s,
		$gpu_s,
		$display_s,
		$acum_s,
		$sist_s,
		$war_s,
		$hdd_s,
		$shdd_s);
		
	
	$create_table = "USE notebro_temp; SET @p0='all_conf_".$model_id."'; CALL allconf_tbl2(@p0);";
$cons=$multicons[$server];
	if (mysqli_multi_query($cons, $create_table)) {
    do {
        // Store first result set
        if ($result=mysqli_store_result($cons)) {
            while ($row=mysqli_fetch_row($result)) {

            }
            mysqli_free_result($result);
        } 
    }
    while (mysqli_more_results($cons) && mysqli_next_result($cons));
} 

mysqli_query($con, "USE notebro_db;");

	if(!isset($memory_limit) || (isset($memory_limit) && $nr_configs<$memory_limit))
	{
		$INSERT_QUERY = "INSERT INTO notebro_temp." . $temp_table . "_" . $model_id . " (id, model, cpu, display, mem, hdd, shdd, gpu, wnet, odd, mdb, chassis, acum, war, sist, rating, price, value, err, batlife, capacity) VALUES ";
		$INSERT_ID_MODEL = "INSERT INTO notebro_temp.$temp_table (id, model) VALUES ";

		//$raw["id"], $cpu_id, $display_id, $mem_id, $hdd_id, $shdd_id, $gpu_id, $wnet_id, $odd_id, $mdb_id, $chassis_id, $acum_id, $war_id, $sist_id,
		insert_function ($configs,$BATCH_SIZE,$INSERT_QUERY,$INSERT_ID_MODEL,$multicons,$server,$model_id,$rcon);
	}
	else
	{ echo "Preventing memory overflow, insertation aborded!<br>"; }
}

function get_price_list($con,$model)
{
	$price_list=NULL;
	$sql="SELECT other from notebro_prices.comp_match where model=$model";
	$price_list=json_decode(mysqli_fetch_assoc(mysqli_query($con,$sql))["other"],true);
	
	if(isset($price_list["nodiscount"]) && $price_list["nodiscount"]!==NULL && $price_list["nodiscount"]!="")
	{ $nodiscount=$price_list["nodiscount"]; }
	else
	{ $nodiscount=0; }

	if(isset($price_list["prod"]) && $price_list["prod"]!==NULL && $price_list["prod"]!="")
	{ $prod=$price_list["prod"]; }
	else
	{ $sql="SELECT prod from notebro_db.MODEL where id=$model"; $prod=mysqli_fetch_assoc(mysqli_query($con,$sql))["prod"]; if(!(isset($prod) && $prod)) { $prod=""; } }

	$gotodan=0;
	if(isset($price_list["webprice"]) && $price_list["webprice"]!==NULL && $price_list["webprice"]!="")
	{
		if(isset($price_list["baseprice"]) && $price_list["baseprice"]!==NULL && $price_list["baseprice"]!="")
		{ 
			if($prod!="Dell" && !$nodiscount ){ $baseprice=$price_list["webprice"]; $discount=$price_list["baseprice"]/$price_list["webprice"]; } else { $baseprice=$price_list["baseprice"]; $discount=1;} 
			$price_list["prod"]=$prod; $price_list["discount"]=$discount; $price_list["baseprice"]=$baseprice;
			return($price_list);
		}
		else 
		{ return NULL; }
	}
	else 
	{ return NULL; }
}

function calc_price($tocalc,$price_list) {	
	$discount = $price_list["discount"];
	$baseprice = $price_list["baseprice"];
	$prod = $price_list["prod"];

	foreach($tocalc as $key=>$val)
	{
		if($key!="model" && (!isset($price_list[$key]) || !isset($price_list[$key][$val]) || is_null($price_list[$key][$val]) || $price_list[$key][$val] === ""))
		{ return null; }
	}
	
	$web_price=intval(($baseprice+$price_list["cpu"][$tocalc["cpu"]]+$price_list["display"][$tocalc["display"]]+$price_list["mem"][$tocalc["mem"]]+$price_list["hdd"][$tocalc["hdd"]]+$price_list["shdd"][$tocalc["shdd"]]+$price_list["odd"][$tocalc["odd"]]+$price_list["wnet"][$tocalc["wnet"]]+$price_list["mdb"][$tocalc["mdb"]]+$price_list["chassis"][$tocalc["chassis"]]+$price_list["acum"][$tocalc["acum"]]+$price_list["sist"][$tocalc["sist"]]+$price_list["gpu"][$tocalc["gpu"]])*$discount);

	switch($prod) {
		case "Lenovo": { $web_price=intval($web_price+$price_list["war"][$tocalc["war"]]); break; }
		case "HP": { $web_price=intval($web_price+$price_list["war"][$tocalc["war"]]*$discount); break; }
		case "Dell": { $web_price=intval($web_price+$price_list["war"][$tocalc["war"]]*$discount)/$discount; break; }
		default: { $web_price=intval($web_price+$price_list["war"][$tocalc["war"]]*$discount); break; }
	}
	return $web_price;
}


function config_list_to_dict($c) {
	return array(
		"MODEL" => $c[1],
		"CPU" => $c[2],
		"DISPLAY" => $c[3],
		"MEM" => $c[4],
		"HDD" => $c[5],
		"SHDD" => $c[6],
		"GPU" => $c[7],
		"WNET" => $c[8],
		"ODD" => $c[9],
		"MDB" => $c[10],
		"CHASSIS" => $c[11],
		"ACUM" => $c[12],
		"WAR" => $c[13],
		"SIST" => $c[14]
	);
}

function update_maybe_values($maybe_values, $values) {
	// Replaces the `null`s in `$maybe_values` with `$values`.
	// Example:
	//     update_maybe_values([1, null, 3], [2])
	//     -> [1, 2, 3]
	$i = 0;
	$updated_values = [];
	foreach ($maybe_values as $maybe_value) {
		if (!is_null($maybe_value)) {
			$value = $maybe_value;
		} else {
			$value = $values[$i];
			$i++;
		}
		array_push($updated_values, $value); 
	}
	return $updated_values;
}

function insert_function($configs,$BATCH_SIZE,$INSERT_QUERY,$INSERT_ID_MODEL,$multicons,$server,$model_id,$rcon)
{
	$reinsert=0;
	$price_list = get_price_list($rcon, $model_id);
	foreach(chunk($configs, $BATCH_SIZE) as $i => $chunk)
	{
		$chunk_array = iterator_to_array($chunk);
		$precomputed_prices = array_map(function ($c) use ($price_list){ return calc_price(array_change_key_case(config_list_to_dict($c)), $price_list); }, $chunk_array);
		$chunk_without_prices = array_values(array_filter($chunk_array, function ($i) use ($precomputed_prices) { return is_null($precomputed_prices[$i]); }, ARRAY_FILTER_USE_KEY));
		$classifier_prices = post_request_to_web_service(chunk_to_json($chunk_without_prices));
		$prices = update_maybe_values($precomputed_prices, $classifier_prices);
		replace_prices($chunk_array, $prices);
		$query = $INSERT_QUERY . implode(", ", array_map("values_to_str", $chunk_array));
		$cons=$multicons[$server];
		{ mysqli_query($cons, $query) or $reinsert=ver_duplicate(mysqli_error($cons)); }
		$query_id_model = $INSERT_ID_MODEL . implode(", ", array_map("values_to_str", array_map(function ($xs) { return array_slice($xs, 0, 2); }, $chunk_array)));
		mysqli_query($cons, $query_id_model); //Duplicate entry '15375138152867000320' for key 'PRIMARY' mysqli_query($sel_db,'what!') or some_func(mysqli_error($sel_db));
		set_time_limit(1000);
		//break 2;
	}
	
	if($reinsert)
	{
		$newid=gmp_add($reinsert,"1");
		$i=0; $new_configs=array();
		foreach($configs as $value)
		{
			if($reinsert==$value[0])
			{$i=1; $value[0]=$newid;}
		
			if($i)
			{ $new_configs[]=$value;}
		}
		insert_function($new_configs,$BATCH_SIZE,$INSERT_QUERY,$INSERT_ID_MODEL,$multicons,$server,$model_id,$rcon);
	}
}

function ver_duplicate($error_str)
{
	preg_match('/Duplicate entry \'(\d+)\'/', $error_str, $matches);
	if(isset($matches[1]) && $matches[1])
	{
		return $matches[1];
	}
	else
	{ echo "Generation failed!"; die($error_str); }
}

// Putting real prices in their place
$rquery="SELECT * FROM notebro_prices.pricing_all_conf WHERE realprice>0 ORDER BY model ASC";
$cons=$multicons[$server];
if ($rresult = mysqli_query($rcon, $rquery))
{
    while ($rrow = mysqli_fetch_assoc($rresult))
	{
		$rateresult=mysqli_query($cons,"SELECT rating FROM notebro_temp.all_conf_".$rrow["model"]." WHERE model=".$rrow["model"]." AND cpu=".$rrow["cpu"]." AND display=".$rrow["display"]." AND mem=".$rrow["mem"]." AND hdd=".$rrow["hdd"]." AND shdd=".$rrow["shdd"]." AND gpu=".$rrow["gpu"]." AND wnet=".$rrow["wnet"]." AND odd=".$rrow["odd"]." AND mdb=".$rrow["mdb"]." AND chassis=".$rrow["chassis"]." AND acum=".$rrow["acum"]." AND war=".$rrow["war"]." AND sist=".$rrow["sist"]." LIMIT 1");
		if($rateresult!==FALSE)
		{
			$getrate=mysqli_fetch_assoc($rateresult);
			$setquery="UPDATE notebro_temp.all_conf_".$rrow["model"]." SET price=".$rrow["realprice"].",value=(".$getrate["rating"]."/".$rrow["realprice"]."),err=(".$rrow["realprice"]."*0.025) WHERE model=".$rrow["model"]." AND cpu=".$rrow["cpu"]." AND display=".$rrow["display"]." AND mem=".$rrow["mem"]." AND hdd=".$rrow["hdd"]." AND shdd=".$rrow["shdd"]." AND gpu=".$rrow["gpu"]." AND wnet=".$rrow["wnet"]." AND odd=".$rrow["odd"]." AND mdb=".$rrow["mdb"]." AND chassis=".$rrow["chassis"]." AND acum=".$rrow["acum"]." AND war=".$rrow["war"]." AND sist=".$rrow["sist"]." LIMIT 1";
			mysqli_query($cons, $setquery);
		}
	}
    if($rresult!==FALSE) {mysqli_free_result($rresult); }
}

$rquery="SELECT * FROM notebro_prices.disabled_configs";
if ($rresult = mysqli_query($rcon, $rquery))
{
    while ($rrow = mysqli_fetch_assoc($rresult))
	{
		$update="UPDATE `notebro_temp`.`all_conf_".$rrow["model"]."` SET price=0 WHERE model=".$rrow["model"]."";
		if($rrow["cpu"]!==NULL){ $update.=" AND cpu IN (".$rrow["cpu"].")"; }
		if($rrow["display"]!==NULL){ $update.=" AND display IN (".$rrow["display"].")"; }
		if($rrow["mem"]!==NULL){ $update.=" AND mem IN (".$rrow["mem"].")"; }
		if($rrow["hdd"]!==NULL){ $update.=" AND hdd IN (".$rrow["hdd"].")"; }
		if($rrow["shdd"]!==NULL){ $update.=" AND shdd IN (".$rrow["shdd"].")"; }
		if($rrow["gpu"]!==NULL){ $update.=" AND gpu IN (".$rrow["gpu"].")"; }
		if($rrow["wnet"]!==NULL){ $update.=" AND wnet IN (".$rrow["wnet"].")"; }
		if($rrow["odd"]!==NULL){ $update.=" AND odd IN (".$rrow["odd"].")"; }
		if($rrow["mdb"]!==NULL){ $update.=" AND mdb IN (".$rrow["mdb"].")"; }
		if($rrow["chassis"]!==NULL){ $update.=" AND chassis IN (".$rrow["chassis"].")"; }
		if($rrow["acum"]!==NULL){ $update.=" AND acum IN (".$rrow["acum"].")"; }
		if($rrow["war"]!==NULL){ $update.=" AND war IN (".$rrow["war"].")"; }
		if($rrow["sist"]!==NULL){ $update.=" AND sist IN (".$rrow["sist"].")"; }
		mysqli_query($cons, $update);
	}
	if($rresult!==FALSE) {mysqli_free_result($rresult); }
}

require_once("best_low_opt.php");
mysqli_close($rcon);

$time_end = microtime(true);

$execution_time = ($time_end - $time_start);
foreach ($multicons as $cons) { mysqli_close($cons); }
printf("Time elapsed: %.6f s\n", $execution_time);
mysqli_close($con);
?>
