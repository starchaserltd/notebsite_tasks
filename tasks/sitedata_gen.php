<style>
	.button_plugin {
	color:#fff;
	border-color: #245580;
	margin-bottom:3px;
	padding:10px;
	border-radius:10px;
	background-image: linear-gradient(to bottom,#337ab7 0,#265a88 100%);
	width:300px;
		}
</style>
<p>GENERATING SITE INFO DATA</p>
<button type="button" class="button_plugin" onclick ="javascript:history.go(-1)">GO BACK</button>
<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);


//*************************************FOR THE WORDPRESS ADMIN CONECTION *********************************************
//require_once("../wp/wp-content/plugins/admin_notebro/con/conf.php");
//echo $allowdirect;
//***********************************************************************************************************************
 
$allowdirect = 1;

if(isset($allowdirect) && $allowdirect>0)
{

	echo "It works!<br>";

	if(file_exists("/var/www/vault/genconf")) { chdir("/var/www/vault/genconf"); }
	require_once("../etc/con_db.php");
	require_once("../etc/con_sdb.php");
	$server=1; $multicons=dbs_connect();
	$cons=$multicons[0];

	/////// NUMBER OF CONFIGURATIONS
	$insert="";
	$sel="SELECT COUNT(`id`) AS `nr_confs` FROM `notebro_temp`.`all_conf` LIMIT 1";
	$result = mysqli_query($cons, $sel);
	if(have_results($result))
	{
		$row=mysqli_fetch_assoc($result);
		$data=$row["nr_confs"];
		$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`, `prop`, `prop1`, `prop2`) VALUES ('9999', '".$data."','nr_confs',NULL,NULL);";
		mysqli_query($con,$insert);
		mysqli_free_result($result);
	}
	
	/////// NUMBER OF MODELS
	$insert="";
	$sel="SELECT COUNT(DISTINCT `model`) AS `nr_models` FROM `notebro_temp`.`all_conf` LIMIT 1; ";
	$result = mysqli_query($cons, $sel);
	if(have_results($result))
	{
		$row=mysqli_fetch_assoc($result);
		$data=$row["nr_models"];
		$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`, `prop`, `prop1`, `prop2`) VALUES ('9999', '".$data."','nr_models',NULL,NULL);";
		mysqli_query($con,$insert);
		mysqli_free_result($result);
	}
	
	/////// NUMBER OF P_MODELS
	$insert="";
	$pmodel_list=[];
	$sel="SELECT DISTINCT `model` AS `model` FROM `notebro_temp`.`all_conf`; ";
	$result = mysqli_query($cons, $sel);
	if(have_results($result))
	{
		while($row=mysqli_fetch_assoc($result))
		{
			$sel_p_model="SELECT `p_model` AS `p_model` FROM `notebro_db`.`MODEL` WHERE `id`='".$row["model"]."' LIMIT 1;";
			$result_p = mysqli_query($con, $sel_p_model);
			if(have_results($result_p))
			{
				$row=mysqli_fetch_assoc($result_p);
				$pmodel_list[]=$row["p_model"];
				mysqli_free_result($result_p);
			}				
		}
		
		if(isset($pmodel_list[0]))
		{
			$data=count(array_unique($pmodel_list));
			$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`, `prop`, `prop1`, `prop2`) VALUES ('9999', '".$data."','nr_pmodels',NULL,NULL);";
			mysqli_query($con,$insert);
		}
		mysqli_free_result($result);
	}
	
	/////// NUMBER OF RETAILERS
	$insert="";
	$retailer_list=[];
	$sel="SELECT DISTINCT `retailer` AS `retailer` FROM `notebro_buy`.`FIXED_CONF_PRICES`; ";
	$result = mysqli_query($con, $sel);
	if(have_results($result))
	{
		while($row=mysqli_fetch_assoc($result))
		{
			$retailer_list[]=$row["retailer"];
		}
		mysqli_free_result($result);
	}
	
	$sel="SELECT DISTINCT `retailer` AS `retailer` FROM `notebro_buy`.`VAR_CONF_PRICES`; ";
	$result = mysqli_query($con, $sel);
	if(have_results($result))
	{
		while($row=mysqli_fetch_assoc($result))
		{
			$retailer_list[]=$row["retailer"];
		}
		mysqli_free_result($result);
	}
	
	if(isset($retailer_list[0]))
	{
		$data=count(array_unique($retailer_list));
		//var_dump(array_unique($retailer_list));
		$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`, `prop`, `prop1`, `prop2`) VALUES ('9999', '".$data."','nr_retailers',NULL,NULL);";
		mysqli_query($con,$insert);
	}

	/////// CURRENT TIME
	$insert="";
	$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`, `prop`, `prop1`, `prop2`) VALUES ('9999',NOW(),'gen_time',NULL,NULL);";
	mysqli_query($con,$insert);
	
	mysqli_close($con);

} else {
	echo '<script language="javascript">';
	echo 'alert("You do not have permission!!!")';
	echo '</script>';
	}	
?>