<?php
/* TO HELP PHP DEAL WITH EMPTY VARIABLES*/

$comp_list=["cpu","display","mem","hdd","shdd","gpu","wnet","odd","mdb","chassis","acum","war","sist"];

$isadvanced=0; $issimple=0;
$model_model=array(); $prod_model=array(); $fam_model=array(); $msc_model=array();
$cpu_prod=array(); $cpu_model=array(); $cpu_ldmin=0; $cpu_ldmax=0; $cpu_status=0; $cpu_socket=array(); $cpu_techmin=0; $cpu_techmax=0; $cpu_cachemin=0; $cpu_cachemax=0; $cpu_clockmin=0; $cpu_clockmax=0; $cpu_turbomin=0; $cpu_turbomax=0; $cpu_tdpmax=0;$cpu_tdpmin=0; $cpu_coremin=0; $cpu_coremax=0; $cpu_intgpu=0; $cpu_misc=array(); $cpu_ratemin=0; $cpu_ratemax=0;
$display_model=array(); $display_sizemin=0; $display_sizemax=0; $display_format=array(); $display_hresmin=0; $display_hresmax=0; $display_vresmin=0; $display_vresmax=0; $display_surft=array(); $display_backt=array(); $display_touch=array();  $display_misc=array(); $display_resolutions=0; $display_ratingmin=0; $display_ratingmax=0;
$gpu_typegpumin=0; $gpu_typegpumax=0; $gpu_prod=array(); $gpu_model=array(); $gpu_arch=array(); $gpu_techmin=0; $gpu_techmax=0; $gpu_shadermin=0; $gpu_cspeedmin=0; $gpu_cspeedmax=0; $gpu_sspeedmin=0; $gpu_sspeedmax=0; $gpu_mspeedmin=0; $gpu_mspeedmax=0; $gpu_mbwmin=0; $gpu_mbwmax=0; $gpu_mtype=array(); $gpu_maxmemmin=0; $gpu_maxmemmax=0; $gpu_sharem=0; $gpu_powermin=0; $gpu_powermax=0; $gpu_misc=array(); $gpu_ratemin=0; $gpu_ratemax=0;
$acum_tipc=array(); $acum_nrcmin=0; $acum_nrcmax=0; $acum_volt=0; $acum_capmin=0; $acum_capmax=0; $acum_misc=array();
$war_prod=array(); $war_yearsmin=0; $war_yearsmax=0; $war_typewar=0; $war_misc=array(); $war_ratemin=0; $war_ratemax=0;
$hdd_model=array(); $hdd_capmin=0; $hdd_capmax=0; $hdd_type=array(); $hdd_readspeedmin=0; $hdd_readspeedmax=0; $hdd_writesmin=0; $hdd_writesmax=0; $hdd_rpmmin=0; $hdd_rpmmax=0; $hdd_misc=array(); $hdd_ratemin=0; $hdd_ratemax=0;
$nr_hdd=0;
$wnet_prod=array(); $wnet_model=array(); $wnet_misc=array(); $wnet_speedmin=0; $wnet_speedmax=0; $wnet_bluetooth=0; $wnet_ratemin=0; $wnet_ratemax=0;
$sist_sist=array(); $sist_vers=array(); $sist_misc=array();
$odd_type=array(); $odd_prod=array(); $odd_misc=array(); $odd_speedmin=0; $odd_speedmax=0; $odd_ratemin=0; $odd_ratemax=0; $odd_pricemin=0;
$mem_prod=array(); $mem_capmin=0; $mem_capmax=0; $mem_stan=array(); $mem_freqmin=0; $mem_freqmax=0; $mem_type=array(); $mem_latmin=0; $mem_latmax=0; $mem_voltmin=0; $mem_voltmax=0; $mem_misc=array(); $mem_ratemin=0; $mem_ratemax=0;
$mdb_prod=array(); $mdb_model=array(); $mdb_ramcap=array(); $mdb_gpu=array(); $mdb_chip=array(); $mdb_socket=array(); $mdb_interface=array(); $mdb_netw=array(); $mdb_hdd=array(); $mdb_misc=array(); $mdb_ratemin=0; $mdb_ratemax=0; $mdb_wwan=0;
$chassis_prod=array(); $chassis_model=array(); $chassis_thicmin=0; $chassis_thicmax=0; $chassis_depthmin=0; $chassis_depthmax=0; $chassis_widthmin=0; $chassis_widthmax=0; $chassis_color=array(); $chassis_weightmin=0; $chassis_weightmax=0; $chassis_made=array(); $chassis_made[0]="0"; $chassis_ports=array(); $chassis_vports=array(); $chassis_webmin=0; $chassis_webmax=0; $chassis_touch=array(); $chassis_misc=array(); $chassis_stuff=array(); $chassis_ratemin=0; $chassis_ratemax=0; $chassis_extra_stuff=array();
$pricemin=0; $budgetmax=0; $battery_life=0;
$browse_by=0;
$sortby=array();
$diffpisearch=0;
$diffvisearch=0;

//Initialise variables used to collect the components used in the conf generation
$cpu_tdpmin=0.01; $gpu_powermin=0.00; $display_hresmin=0.01; $hdd_capmin=0.01; $war_yearsmin=0; $acum_capmin=0.01; $wnet_ratemin=0.01; $sist_pricemax=1; $odd_speedmin=0.00; $mem_capmin=0.01; $mdb_ratemin=0.01; $chassis_weightmin=0.01; 
$budgetmin = 0; $budgetmax = 99999999999999999999999;
//These variables indicate which components are primarily filters and which are not
$cpu_s=1; $gpu_s=1; $display_s=1; $acum_s=1;  $sist_s=1; $war_s=1; $hdd_s=1;
$shdd_s=0; $wnet_s=0; $shdd_s=0; $mem_s=0; $mdb_s=0; $chassis_s=0; $odd_s=0; 

if($cpu_s)
{
	require_once("lib/comp_filters/cpu_search.php");
	$cpu_selected_data=search_cpu ($cpu_prod, $cpu_model, $cpu_ldmin, $cpu_ldmax, $cpu_status, $cpu_socket, $cpu_techmin, $cpu_techmax, $cpu_cachemin, $cpu_cachemax, $cpu_clockmin, $cpu_clockmax, $cpu_turbomin, $cpu_turbomax, $cpu_tdpmax,$cpu_tdpmin, $cpu_coremin, $cpu_coremax, $cpu_intgpu, $cpu_misc, $cpu_ratemin, $cpu_ratemax, $pricemin, $budgetmax, 0.1);
}

if($display_s)
{
	require_once("lib/comp_filters/display_search.php");
	$display_selected_data=search_display ($display_model, $display_sizemin, $display_sizemax, $display_format, $display_hresmin, $display_hresmax, $display_vresmin, $display_vresmax, $display_surft, $display_backt, $display_touch,  $display_misc, $display_resolutions, $display_ratingmin, $display_ratingmax, $pricemin, $budgetmax, 0.1);
}

if($gpu_s)
{
	require_once("lib/comp_filters/gpu_search.php");
	$gpu_selected_data=search_gpu ($gpu_typegpumin, $gpu_typegpumax, $gpu_prod, $gpu_model, $gpu_arch, $gpu_techmin, $gpu_techmax, $gpu_shadermin, $gpu_cspeedmin, $gpu_cspeedmax, $gpu_sspeedmin, $gpu_sspeedmax, $gpu_mspeedmin, $gpu_mspeedmax, $gpu_mbwmin, $gpu_mbwmax, $gpu_mtype, $gpu_maxmemmin, $gpu_maxmemmax, $gpu_sharem, $gpu_powermin, $gpu_powermax, $gpu_misc, $gpu_ratemin, $gpu_ratemax, $pricemin ,$budgetmax, 0.1);
}

if($acum_s)
{
	require_once("lib/comp_filters/acum_search.php");
	$acum_selected_data=search_acum ($acum_tipc, $acum_nrcmin, $acum_nrcmax, $acum_volt, $acum_capmin, $acum_capmax, $pricemin, $budgetmax, $acum_misc, 0.1);
}

if($war_s)
{
	require_once("lib/comp_filters/war_search.php");
	$war_selected_data=search_war ($war_prod, $war_yearsmin, $war_yearsmax, $war_typewar, $war_misc, $war_ratemin, $war_ratemax, $pricemin,$budgetmax);
}

if($hdd_s)
{
	require_once("lib/comp_filters/hdd_search.php");
	$hdd_selected_data=search_hdd ($hdd_model, $hdd_capmin, $hdd_capmax, $hdd_type, $hdd_readspeedmin, $hdd_readspeedmax, $hdd_writesmin, $hdd_writesmax, $hdd_rpmmin, $hdd_rpmmax, $hdd_misc, $hdd_ratemin, $hdd_ratemax, $pricemin, $budgetmax);
}

if($shdd_s)
{
	$shdd_selected_data=search_hdd ($hdd_model, $hdd_capmin, $hdd_capmax, $hdd_type, $hdd_readspeedmin, $hdd_readspeedmax, $hdd_writesmin, $hdd_writesmax, $hdd_rpmmin, $hdd_rpmmax, $hdd_misc, $hdd_ratemin, $hdd_ratemax, $pricemin, $budgetmax);
	$shdd_selected_data[0]=array("price"=> 0, "rating"=> 0, "err"=> 0, "cap"=> 0, "type"=> "N/A");
}
if($wnet_s)
{
	require_once("lib/comp_filters/wnet_search.php");
	$wnet_selected_data=search_wnet ($wnet_prod, $wnet_model, $wnet_misc, $wnet_speedmin, $wnet_speedmax, $wnet_ratemin, $wnet_ratemax, $pricemin, $budgetmax);
}

if($sist_s)
{
	require_once("lib/comp_filters/sist_search.php");
	$sist_selected_data=search_sist ($sist_sist, $sist_vers, $sist_misc, $pricemin, $budgetmax);
}

if($odd_s)
{
	require_once("lib/comp_filters/odd_search.php");
	$odd_selected_data=search_odd ($odd_type, $odd_prod, $odd_misc, $odd_speedmin, $odd_speedmax, $odd_ratemin, $odd_ratemax, $odd_pricemin, $budgetmax);
}
	
if($mem_s)
{
	require_once("lib/comp_filters/mem_search.php");
	$mem_selected_data=search_mem ($mem_prod, $mem_capmin, $mem_capmax, $mem_stan, $mem_freqmin, $mem_freqmax, $mem_type, $mem_latmin, $mem_latmax, $mem_voltmin, $mem_voltmax, $mem_misc, $mem_ratemin, $mem_ratemax, $pricemin,$budgetmax);
}

if($mdb_s)
{
	require_once("lib/comp_filters/mdb_search.php");
	$mdb_selected_data=search_mdb ($mdb_prod, $mdb_model, $mdb_ramcap, $mdb_gpu, $mdb_chip, $mdb_socket, $mdb_interface, $mdb_netw, $mdb_hdd, $mdb_misc, $mdb_ratemin, $mdb_ratemax, $pricemin,$budgetmax);
}

if($chassis_s)
{
	require_once("lib/comp_filters/chassis_search.php");
	$chassis_selected_data=search_chassis ($chassis_prod, $chassis_model, $chassis_thicmin, $chassis_thicmax, $chassis_depthmin, $chassis_depthmax, $chassis_widthmin, $chassis_widthmax, $chassis_color, $chassis_weightmin, $chassis_weightmax, $chassis_made, $chassis_ports, $chassis_vports, $chassis_web, $chassis_touch, $chassis_misc, $chassis_ratemin, $chassis_ratemax, $pricemin,$budgetmax);
}

//IN CASE I DON'T DO THE FILTER FOR SOME COMPONENTS, NEVER USE THIS FUNCTION ON CPU,GPU,DISPLAY,ACUM,SISTC
function nolist($component,$ids,$tdp)
{
	$component=strtoupper($component);
	switch($component)
	{
		case "SHDD":
		{
			$component="HDD";
		}
		case "HDD":
		{
			$fields="`id`,`price`,`rating`,`err`,`cap`,`type`,`model`";
			break;
		}
		case "DISPLAY":
		{
			$fields="`id`,`price`,`rating`,`err`,`size`, (`hres`*`vres`) AS `res`,`backt`";
			break;
		}
		case "MDB":
		{
			$fields="`id`,`price`,`rating`,`err`,`msc`,`hdd`";
			break;
		}
		default:
		{
			$fields="`id`,`price`,`rating`,`err`";
			break;
		}
	}
	$uni_return=array();
	
	if($ids==0)
	{
		$ids=array();
		$get_ids_query="SELECT `id` FROM `notebro_db`.`".$component."` WHERE 1=1";
		$ids_result = mysqli_query($GLOBALS['con'],$get_ids_query);
		if(have_results($ids_result))
		{	
			while($some_row=mysqli_fetch_row($ids_result))
			{ $ids[]=$some_row[0]; }
			unset($some_row);
			mysqli_free_result($ids_result);
		}
	}
	
	foreach($ids as $x)
	{
		$sel_uni="SELECT ".$fields." FROM `notebro_db`.`".$component."` WHERE `id`='".$x."'";
		$result = mysqli_query($GLOBALS['con'],$sel_uni);

		if($result)
		{
			while($rand = mysqli_fetch_array($result)) 
			{ 
				switch($component)
				{
					case "HDD":
					{
						$uni_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>floatval($rand[3]),"cap"=>intval($rand[4]),"type"=>strval($rand[5]),"model"=>strval($rand[6]));
						break;
					}
					case "MDB":
					{
						
						$uni_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>floatval($rand[3]));
						if(stripos($rand[4],"optimus")===false && stripos($rand[4],"enduro")===false) { $rand[4]=0; } else {$rand[4]=1;}
						$uni_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]),"optimus"=>$rand[4],"hdd"=>$rand[5]);
						break;
					}
					default:
					{
						$uni_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>floatval($rand[3]));
						break;
					}
				}
			}
			mysqli_free_result($result);
		}
	}
	return($uni_return);
}

function show_running_output($str)
{
    /*
	$padSize = ini_get('output_buffering');
	echo str_pad($str,$padSize);
    ob_end_flush();
    if(ob_get_level()>0) { ob_flush(); }
    flush();
    ob_start();
	*/
	echo $str;
}
?>