<?php

function get_price_data($model_id,$comp_list,$con)
{
	global $var_conf_price, $fixed_conf_prices, $var_conf_disabled, $fixed_conf_prices_eq;
	//GETTING VAR_CONF_PRICES
	$SELECT_PRICE_DATA="SELECT `VAR_CONF_PRICES`.`retailer`,`VAR_CONF_PRICES`.`retailer_pid`,`VAR_CONF_PRICES`.`price_data`,`SELLER_DATA`.`region` FROM `notebro_buy`.`VAR_CONF_PRICES` AS `VAR_CONF_PRICES` JOIN `notebro_buy`.`SELLERS` AS `SELLER_DATA` ON `SELLER_DATA`.`name`=`VAR_CONF_PRICES`.`retailer` WHERE `VAR_CONF_PRICES`.`model`='".$model_id."'";
	$select_q_r=mysqli_query($con,$SELECT_PRICE_DATA); $var_conf_price=array();
	if(have_results($select_q_r))
	{
		while($temp_row=mysqli_fetch_assoc($select_q_r))
		{
			$var_conf_price[]=["price_data"=>(json_decode($temp_row["price_data"],true)),"retailer"=>$temp_row["retailer"],"retailer_pid"=>$temp_row["retailer_pid"],"region"=>$temp_row["region"]];
		}
		unset($temp_row); mysqli_free_result($select_q_r);
	}
	//GETTING DISABLED_CONF FOR RETAILER
	$SELECT_PRICE_DATA="SELECT * FROM `notebro_buy`.`DISABLED_CONF` WHERE `model`='".$model_id."' AND `retailer` IS NOT NULL AND `retailer_pid` IS NOT NULL";
	$select_q_r=mysqli_query($con,$SELECT_PRICE_DATA); $var_conf_disabled=array();
	if(have_results($select_q_r))
	{
		while($temp_row=mysqli_fetch_assoc($select_q_r))
		{
			$var_conf_disabled[$temp_row["id"]]=array();
			if(!isset($var_conf_disabled[$temp_row["id"]]["nr_valid"]))
			{ $var_conf_disabled[$temp_row["id"]]["nr_valid"]=0; $var_conf_disabled[$temp_row["id"]]["retailer"]=$temp_row["retailer"]; $var_conf_disabled[$temp_row["id"]]["retailer_pid"]=$temp_row["retailer_pid"]; }
			foreach($comp_list as $comp_name)
			{ $var_conf_disabled[$temp_row["id"]][$comp_name]=array(); if($temp_row[$comp_name]!==NULL){ $var_conf_disabled[$temp_row["id"]][$comp_name]=explode(",",$temp_row[$comp_name]); $var_conf_disabled[$temp_row["id"]]["nr_valid"]++; }else{ $var_conf_disabled[$temp_row["id"]][$comp_name]=NULL; } }
		}
		unset($temp_row); mysqli_free_result($select_q_r);
	}
	//GETTING FIXED CONF			
	$SELECT_PRICE_DATA="SELECT `FIXED_CONF_PRICES`.*,`SELLER_DATA`.`region` FROM `notebro_buy`.`FIXED_CONF_PRICES` AS `FIXED_CONF_PRICES` JOIN `notebro_buy`.`SELLERS` AS `SELLER_DATA` ON `SELLER_DATA`.`name`=`FIXED_CONF_PRICES`.`retailer` WHERE `FIXED_CONF_PRICES`.`model`='".$model_id."'";
	$select_q_r=mysqli_query($con,$SELECT_PRICE_DATA); $fixed_conf_prices=array(); $fixed_conf_prices_eq=array(); 
	if(have_results($select_q_r))
	{
		while($temp_row=mysqli_fetch_assoc($select_q_r))
		{
			$fixed_conf_prices[$temp_row["id"]]=array();
			
			foreach($comp_list as $comp_name)
			{ 
				$fixed_conf_prices[$temp_row["id"]][$comp_name]=-1;
				if($temp_row[$comp_name]!==NULL){ $fixed_conf_prices[$temp_row["id"]][$comp_name]=intval($temp_row[$comp_name]); }else{ unset($fixed_conf_prices[$temp_row["id"]]); break; }
			}
			if(isset($fixed_conf_prices[$temp_row["id"]]))
			{
				$fixed_conf_prices[$temp_row["id"]]["price"]=floatval($temp_row["price"]);
				$fixed_conf_prices[$temp_row["id"]]["retailer"]=$temp_row["retailer"];
				$fixed_conf_prices[$temp_row["id"]]["region"]=$temp_row["region"];
				$fixed_conf_prices[$temp_row["id"]]["retailer_pid"]=$temp_row["retailer_pid"];
				$fixed_conf_prices[$temp_row["id"]]["eq_modifiers"]=json_decode($temp_row["eq_modifiers"],true);
				if(count($fixed_conf_prices[$temp_row["id"]]["eq_modifiers"])>0)
				{
					//GENERATING EQ CONFIGS WITH THEIR PRICES
					$conf_prices_eq=array(); $conf_prices_eq[0]=$fixed_conf_prices[$temp_row["id"]]; unset($conf_prices_eq[0]["eq_modifiers"]);
					foreach($fixed_conf_prices[$temp_row["id"]]["eq_modifiers"] as $comp_name=>$comp_to_process)
					{
						foreach($comp_to_process["eq_id"] as $eq_id=>$eq_data)
						{
							foreach($conf_prices_eq as $some_conf)
							{
								$new_conf=$some_conf;
								$new_conf[$comp_name]=intval($eq_id);
								$new_conf["price"]=$new_conf["price"]+floatval($eq_data);
								$conf_prices_eq[]=$new_conf;
								unset($new_conf);
							}
						}
					}
					unset($conf_prices_eq[0]);
					$fixed_conf_prices_eq[$temp_row["id"]]=$conf_prices_eq;
				}
			}
		}
		unset($temp_row); mysqli_free_result($select_q_r);
	}
}

function is_new_price_conf($prod,$regions,$new_price_conf)
{
	$to_return=False;
	$model_regions=explode(",",$regions);
	foreach($new_price_conf as $values)
	{
		if($prod==$values["prod"])
		{
			foreach($model_regions as $model_region)
			{
				if(in_array($model_region,$values["regions"]))
				{
					$to_return=True;
					break 2;
				}
			}
		}
	}
	return $to_return;
}

function set_price_market_price($conf,$comp_list,$region=2)
{
	$to_return=array(); $to_return["price"]=0; $to_return["price_error"]=0;
	//CHECK DISABLED CONF
	$disabled_confs=array();
	foreach($GLOBALS["var_conf_disabled"] as $disabled_key=>$disabled_data)
	{
		$disb_vote=0;
		foreach($comp_list as $comp)
		{
			if($disabled_data[$comp]!=NULL)
			{
				if(in_array(strval($conf[$comp]),$disabled_data[$comp]))
				{ $disb_vote++; }
				else
				{ $disb_vote=-99999; break; }
			}
			if($disb_vote>=$disabled_data["nr_valid"])
			{ $disabled_confs[$disabled_key]=["retailer"=>$disabled_data["retailer"],"retailer_pid"=>$disabled_data["retailer_pid"]]; break; }
		}
	}
		
	//DOING VAR CONF
	foreach($GLOBALS["var_conf_price"] as $var_conf_data)
	{
		$do_the_calculation=True;
		if(intval($var_conf_data["region"])!=$region) {$do_the_calculation=False;}
		if($do_the_calculation)
		{
			foreach($disabled_confs as $disabled_conf)
			{
				if($var_conf_data["retailer"]==$disabled_conf["retailer"])
				{
					if($disabled_conf["retailer_pid"]==NULL || empty($disabled_conf["retailer_pid"]) || $disabled_conf["retailer_pid"]="")
					{ $do_the_calculation=False; break; }
					elseif($var_conf_data["retailer_pid"]==$disabled_conf["retailer_pid"])
					{ $do_the_calculation=False; break; }
					else
					{}
				}
			}
		}
		if($do_the_calculation && isset($var_conf_data["price_data"]))
		{ 
			$calc_price_array=calc_conf_price($conf,NULL,NULL,NULL,$set_price_list=$var_conf_data["price_data"],$GLOBALS["con"]);
			if($calc_price_array && isset($calc_price_array[0])){ $calc_price=$calc_price_array[0][0]; }
			else {$calc_price=0; }
			#echo "<br>"; var_dump($conf); var_dump($calc_price); echo "</br>";
			if((intval($calc_price)>0) && ($calc_price && $to_return["price"]==0 || intval($calc_price)<$to_return["price"]))
			{ $to_return["price"]=$calc_price; }
			unset($calc_price_array);
		}
		else
		{ 
			#echo "<br>Skipping price calculation for "; var_dump($conf); echo " - "; var_dump($var_conf_data); echo "</br>";
		}
	}
	
	//DOING 2 TRIES: 1 FOR EXACT CONF MATCH, SECOND FOR CONF MATCH WITHOUT WARRANTY
	$tries=2;
	while($tries>0)
	{
		//DOING FIXED CONF
		foreach($GLOBALS["fixed_conf_prices"] as $fixed_conf_data)
		{
			$do_the_calculation=True;
			if(intval($fixed_conf_data["region"])!=$region) {$do_the_calculation=False;}
			if($do_the_calculation)
			{
				foreach($comp_list as $comp)
				{
					if($tries==1 && $comp=="war"){ continue; }
					if($conf[$comp]!=$fixed_conf_data[$comp])
					{ $do_the_calculation=False; break; }
				}
			}
			
			if($do_the_calculation && intval($fixed_conf_data["region"])==$region)
			{
				$calc_price=floatval($fixed_conf_data["price"]);
				//echo "<br>"; var_dump($calc_price); echo "<br>";
				if($to_return["price"]==0 || intval($calc_price)<$to_return["price"])
				{
					if($tries==1)
					{
						$q_ok_to_go=True;
						foreach($GLOBALS["questionable_confs"] as $q_conf_key=>$q_conf)
						{
							$do_q_test=True;
							foreach($comp_list as $comp)
							{
								if($conf[$comp]!=$q_conf[$comp] && $comp!="war")
								{ $do_q_test=False; break; }
							}
							if($do_q_test)
							{
								if(intval($conf["war"])<intval($q_conf["war"]))
								{ $to_return["price"]=$calc_price; }
								else
								{ $q_ok_to_go=False; }
							}
						}
						if($q_ok_to_go)
						{ $to_return["price"]=$calc_price; }
					}
					else
					{
						$to_return["price"]=$calc_price;
					}
				}
				unset($calc_price);
			}
		}
		unset($fixed_conf_data);
			
		//DOING EQUIVALENT FIXED CONF
		foreach($GLOBALS["fixed_conf_prices_eq"] as $base_conf_key=>$eq_conf_data_array)
		{
			$disabled_to_test=array(); $do_the_calculation=True;
			if(intval($GLOBALS["fixed_conf_prices"][$base_conf_key]["region"])!=$region) {$do_the_calculation=False;}
			
			if($do_the_calculation)
			{
				foreach($disabled_confs as $disabled_conf_key=>$disabled_conf)
				{
					if($GLOBALS["fixed_conf_prices"][$base_conf_key]["retailer"]==$disabled_conf["retailer"])
					{
						if($disabled_conf["retailer_pid"]==NULL || empty($disabled_conf["retailer_pid"]) || $disabled_conf["retailer_pid"]="")
						{ $disabled_to_test[$disabled_conf_key]=0; }
						elseif($GLOBALS["fixed_conf_prices"][$base_conf_key]["retailer"]==$disabled_conf["retailer_pid"])
						{ $disabled_to_test[$disabled_conf_key]=0; }
						else
						{}
					}
				}
			}
			
			if($do_the_calculation)
			{
				foreach($eq_conf_data_array as $fixed_conf_data)
				{
					$do_the_calculation=True;		
					foreach($comp_list as $comp)
					{
						if($conf[$comp]!=$fixed_conf_data[$comp])
						{ $do_the_calculation=False; break; }
						else
						{
							//TESTNG IF CONFIGURATION IS DISABLED
							if(count($disabled_to_test)>0)
							{
								foreach($disabled_to_test as $disabled_key=>$disabled_data)
								{
									if($GLOBALS["var_conf_disabled"][$disabled_key][$comp]!=NULL)
									{
										if(in_array(strval($conf[$comp]),$GLOBALS["var_conf_disabled"][$disabled_key][$comp]))
										{ $disabled_to_test[$disabled_key]++; }
										else
										{ $disabled_to_test[$disabled_key]=-99999; break; }
									}
									if($disabled_to_test[$disabled_key]>=$GLOBALS["var_conf_disabled"][$disabled_key]["nr_valid"])
									{ $do_the_calculation=False; break; }
								}
							}
						}
						if(!$do_the_calculation){ break; }
					}
					
					if($do_the_calculation)
					{
						#echo "<br><br>"; var_dump($base_conf_key); var_dump($fixed_conf_data["price"]); var_dump($conf);
						$calc_price=floatval($fixed_conf_data["price"]);
						if($to_return["price"]==0 || ($calc_price<$to_return["price"] && ok_to_replace_existing_price($base_conf_key,$fixed_conf_data)))
						{ $to_return["price"]=$calc_price; }
						unset($calc_price);
					}
				}
			}
		}
		if($to_return["price"]==0)
		{ $tries--; }
		else
		{ if($tries==1){ $GLOBALS["questionable_confs"][]=$conf; } $tries=-1; }
	}

	#var_dump($to_return["price"]); echo "<br>";
	if($to_return["price"]>0){ $to_return["price_error"]=intval($to_return["price"]*0.02); }
	
	return $to_return;
}
?>