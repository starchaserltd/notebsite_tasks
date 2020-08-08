<?php

function get_prices_from_all_conf($batch_size=50)
{
	// Putting real prices in their place
	show_running_output("<br><b>NOT SELECTING PRICES FROM ALL_CONF AND PLACING THEM IN GENERATED DATA</b><br>");
	$cons=$GLOBALS["multicons"][$GLOBALS["server"]];
	$rcon=$GLOBALS["rcon"];
	$get_model_query="SELECT DISTINCT `model` FROM `notebro_temp`.`all_conf`";
	$model_result=mysqli_query($cons,$get_model_query);
	$model_list=array();
	if(have_results($model_result))
	{ 
		while($model_row=mysqli_fetch_assoc($model_result))
		{
			$model=$model_row["model"];
			show_running_output("<br>Putting all_conf prices for model id ".$model.".");
			$rquery="SELECT * FROM notebro_prices.pricing_all_conf WHERE realprice>0 AND `model`='".$model."' ORDER BY model ASC";
			$batch_query=""; $nr_queries=0;
			$rresult=mysqli_query($rcon, $rquery);
			if(have_results($rresult))
			{
				while ($rrow = mysqli_fetch_assoc($rresult))
				{
					$rateresult=mysqli_query($cons,"SELECT rating FROM notebro_temp.all_conf_".$rrow["model"]." WHERE model=".$rrow["model"]." AND cpu=".$rrow["cpu"]." AND display=".$rrow["display"]." AND mem=".$rrow["mem"]." AND hdd=".$rrow["hdd"]." AND shdd=".$rrow["shdd"]." AND gpu=".$rrow["gpu"]." AND wnet=".$rrow["wnet"]." AND odd=".$rrow["odd"]." AND mdb=".$rrow["mdb"]." AND chassis=".$rrow["chassis"]." AND acum=".$rrow["acum"]." AND war=".$rrow["war"]." AND sist=".$rrow["sist"]." LIMIT 1");
					if(have_results($rateresult))
					{
						$getrate=mysqli_fetch_assoc($rateresult);
						$setquery="UPDATE notebro_temp.all_conf_".$rrow["model"]." SET price=".$rrow["realprice"].",value=(".$getrate["rating"]."/".$rrow["realprice"]."),err=(".$rrow["realprice"]."*0.025) WHERE model=".$rrow["model"]." AND cpu=".$rrow["cpu"]." AND display=".$rrow["display"]." AND mem=".$rrow["mem"]." AND hdd=".$rrow["hdd"]." AND shdd=".$rrow["shdd"]." AND gpu=".$rrow["gpu"]." AND wnet=".$rrow["wnet"]." AND odd=".$rrow["odd"]." AND mdb=".$rrow["mdb"]." AND chassis=".$rrow["chassis"]." AND acum=".$rrow["acum"]." AND war=".$rrow["war"]." AND sist=".$rrow["sist"]." LIMIT 1;";
						$batch_query=$batch_query.$setquery; $nr_queries++;
						if($nr_queries>=$batch_size)
						{
							local_multiquery($cons,$batch_query,NULL);
							$batch_query=""; $nr_queries=0;
						}
						else {}
						//mysqli_query($cons, $setquery);
						mysqli_free_result($rateresult);
					}
				}
				if($nr_queries>0){	local_multiquery($cons,$batch_query,NULL); }
				mysqli_free_result($rresult);
			}
		}
		mysqli_free_result($model_result);
	}
	echo "<br>";
}


function old_calc_configurator($chunk_array,$model_id)
{
	$price_list=get_price_list($model_id);
	$precomputed_prices = array_map(function ($c) use ($price_list){ return calc_price(array_change_key_case(config_list_to_dict($c)), $price_list); }, $chunk_array);
	return $precomputed_prices;
}

function get_prices_from_ml($org_chunk_array,$computed_chunk)
{

	$chunk_without_prices = array_values(array_filter($org_chunk_array, function ($i) use ($computed_chunk) { return is_null($computed_chunk[$i]); }, ARRAY_FILTER_USE_KEY));
	$classifier_prices=NULL;
	if(count($chunk_without_prices)>0){ $classifier_prices = post_request_to_noteb_price_ws(chunk_to_json($chunk_without_prices)); }
	//MERGING COMPUTED CHUNK WITH ML CALCULATED CHUNK
	$i=0; $prices=[];
	foreach ($computed_chunk as $computed_value)
	{
		$price_error_margin=0.075;
		if(!is_null($computed_value)) { $value=$computed_value; $price_error_margin=0.025; } else { if ($classifier_prices!==NULL) { $value=$classifier_prices[$i]; $price_error_margin=0.070; $i++; } }
		array_push($prices, ["price"=>floatval($value),"err"=>$price_error_margin]); 
	}
	
	//UPDATING ORIGINAL CHUNK
	$i=0;
	foreach($org_chunk_array as $key=>$row)
	{
		if($prices[$i]["price"]<=2){$prices[$i]["price"]=-1; echo "\n Price is under zero, something is wrong here.";}
		$org_chunk_array[$key]["price"]=$prices[$i]["price"]; //price
		if($org_chunk_array[$key]["price"]>0){ $org_chunk_array[$key]["value"]=(floatval($org_chunk_array[$key]["rating"])/$org_chunk_array[$key]["price"]); } //value
		else { $org_chunk_array[$key]["value"]=0; }
		$org_chunk_array[$key]["err"]=$prices[$i]["price"]*$prices[$i]["err"]; //err
		$i++;
	}
	return $org_chunk_array;
}

function get_price_list($model)
{
	$con=$GLOBALS["rcon"];
	$price_list=NULL;
	$sql="SELECT `other` FROM `notebro_prices`.`comp_match` WHERE `model`='".$model."' ORDER BY `lastcheck` DESC LIMIT 1";
	$price_list=json_decode(mysqli_fetch_assoc(mysqli_query($con,$sql))["other"],true);
	
	if(isset($price_list["nodiscount"]) && $price_list["nodiscount"]!==NULL && $price_list["nodiscount"]!="")
	{ $nodiscount=$price_list["nodiscount"]; }
	else
	{ $nodiscount=0; }

	if(isset($price_list["prod"]) && $price_list["prod"]!==NULL && $price_list["prod"]!="")
	{ $prod=$price_list["prod"]; }
	else
	{ $sql="SELECT `prod` FROM `notebro_db`.`MODEL` WHERE `id`='".$model."'"; $prod=mysqli_fetch_assoc(mysqli_query($con,$sql))["prod"]; if(!(isset($prod) && $prod)) { $prod=""; } }

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

function calc_price($tocalc,$price_list)
{	
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


function config_list_to_dict($c)
{
	$to_return=array();
	$to_return["MODEL"]=$c["model"];
	
	$comp_list=$GLOBALS["comp_list"];
	foreach($comp_list as $comp)
	{ if(isset($c[$comp])) { $to_return[strtoupper($comp)]=$c[$comp]; }else {$to_return=array(); break; } }
	return $to_return;
}
?>