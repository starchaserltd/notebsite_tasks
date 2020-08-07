<?php
echo "<b>Starting price generations 2.0 </b><br><br>";
$multicons=dbs_connect();

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

//GET MODELS FOR PROCESSING
$model_ids = array();
$model_select_cond="AND id=4621";
$model_select_cond="";
$query_model = "SELECT DISTINCT `id` FROM `notebro_db`.`MODEL` WHERE 1=1 ".$model_select_cond." ORDER BY `id` ASC";
$result = mysqli_query($con,$query_model);
if(have_results($result))
{
	while($row = mysqli_fetch_row($result)) { array_push($model_ids, $row[0]); }
	mysqli_free_result($result);
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

	$configs=generate_configs($con,$multicons,$model_id,$comp_list);

	$create_table = "USE `notebro_temp`; SET @p0='all_conf_".$model_id."'; CALL allconf_tbl2(@p0); DROP TABLE IF EXISTS 'all_conf_".$model_id."'; CALL allconf_tbl2(@p0); ";
	$cons=$multicons[$server];
	local_multiquery($cons,$create_table);
	
	mysqli_query($con, "USE notebro_db;");
	if($configs->valid())
	{
		var_dump($nr_configs);
		if(!isset($max_configs_limit) || (isset($max_configs_limit) && $nr_configs<$max_configs_limit))
		{
			$INSERT_QUERY = "INSERT INTO `notebro_temp`.`".$temp_table."_".$model_id."` (`id`,`model`,`".implode("`,`",$comp_list)."`,`rating`,`price`,`value`,`err`,`batlife`,`capacity`) VALUES ";
			$INSERT_ID_MODEL = "INSERT INTO `notebro_temp`.`".$temp_table."` (`id`, `model`) VALUES ";
			insert_function ($configs,$BATCH_SIZE,$INSERT_QUERY,$INSERT_ID_MODEL,$multicons,$server);
			show_running_output("<b>TRIED TO INSERT DATA FOR MODEL ID: ".$model_id." !</b><br>");
		}
		else
		{ echo show_running_output("<b>MAXIMUM NUMBER OF CONFIGS REACHED: ".$max_configs_limit." !</b> Aborting insertation for model id: ".$model_id."<br>"); }
	}
	//$time_end = microtime(true); $execution_time = ($time_end - $time_start); printf("<br><b>Time elapsed:</b> %.6f s\n<br><br>", $execution_time); exit();
}

if($prod_server==1){ get_prices_from_all_conf(); }

//SETTING GENERATION VARIABLE TO ZERO
mysqli_query($con,'UPDATE notebro_site.vars SET value=0 WHERE name="genconfig"');


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

//DOING SOME EXTRA POST GENERATION WORK
require_once("gen_presearch_table_2.php");
require_once("gen_map_table.php");
require_once("best_low_opt.php");
	
mysqli_close($rcon);

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
foreach ($multicons as $cons) { mysqli_close($cons); }
printf("<br><b>Time elapsed:</b> %.6f s\n<br><br>", $execution_time);
if($prod_server==0){ require_once("../toplaptop/validconftop.php"); }
mysqli_close($con);
//ob_end_flush();
?>



<?
function get_price_list($con,$model)
{
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

function get_prices_from_all_conf()
{
	// Putting real prices in their place
	$rquery="SELECT * FROM notebro_prices.pricing_all_conf WHERE realprice>0 ORDER BY model ASC";
	$cons=$GLOBALS["multicons"][$server];
	$rcon=$GLOBAL["rcon"];
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
}
?>
