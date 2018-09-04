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
<button type="button" class="button_plugin" onclick ="javascript:history.go(-1)">GO BACK</button>
<?php
/*
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
*/

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
require_once("../etc/con_rdb.php");
$multicons=dbs_connect();
$cons=$multicons[0];

//VALIDATE first
//require_once("lib/valid_allcomp.php");

////// Model nomenclature

echo "<br>Rebuilding nomenclature table!<br>";

mysqli_query($con, "TRUNCATE notebro_site.nomen_models;");

$sel="SELECT prod FROM notebro_db.MODEL GROUP BY prod";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	$type=$rand["prod"];
	$insert.="INSERT INTO `notebro_site`.`nomen_models` (`prod`) VALUES ('$type');";
}

 if (mysqli_multi_query($con, $insert)) { echo "New models created successfully<br>"; while(mysqli_more_results($con) && mysqli_next_result($con)) {;} } 
 else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }
 mysqli_free_result($result);

mysqli_query($con, "TRUNCATE notebro_site.nomen;");

/////// SOCKET GENERATION

$sel="SELECT socket,prod,tech,ldate FROM notebro_db.CPU WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(object) [];

while($rand = mysqli_fetch_array($result)) 
{ 
	//var_dump($rand["socket"]);
	//echo "<br>";
	if($rand["socket"])
	{
		if(!(property_exists($object, $rand["socket"])))
		{
			$object->{$rand["socket"]}=new stdClass();
			$object->{$rand["socket"]}->prod=$rand["prod"];
			$object->{$rand["socket"]}->tech=$rand["tech"];
			//$time = date('Y-m-d',strtotime($rand["ldate"]));
			$object->{$rand["socket"]}->ldate=$rand["ldate"];
		}
		else
		{
			if($rand["tech"]>$object->{$rand["socket"]}->tech)
			{ $object->{$rand["socket"]}->tech=$rand["tech"]; }
			//echo "<br>"; echo $rand["socket"]." "; var_dump($rand["ldate"]); var_dump(strtotime($rand["ldate"]));  var_dump($object->{$rand["socket"]}->ldate); var_dump(strtotime($object->{$rand["socket"]}->ldate));
			if(strtotime($rand["ldate"])>strtotime($object->{$rand["socket"]}->ldate))
			{ $object->{$rand["socket"]}->ldate=$rand["ldate"]; }
		}
	}
}

mysqli_free_result($result);

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'socket'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

end($object);
$lastname=key($object);

$insert="";
foreach ($object as $key=>$socket)
{
	$prop=$socket->prod;
	$prop1=$socket->tech;
	$prop2=$socket->ldate;

	$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`, `prop`, `prop1`, `prop2`) VALUES ('$type', '$key' ,'$prop', '$prop1', '$prop2');";
}

//echo "<br>"; echo $insert;

if (mysqli_multi_query($con, $insert)) { echo "New cpu sockets created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }

/////// CPU PRODUCER GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_prod'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT prod FROM notebro_db.CPU WHERE valid=1";
$result = mysqli_query($con, $sel);

$insert=""; 

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["prod"])
	{
		$name=$rand["prod"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) { echo "New cpu producers created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }
mysqli_free_result($result);

/////// CPU CORES GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_core_min' ";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_core_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(cores), MAX(cores) FROM notebro_db.CPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=round($rand[0],2);
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=round($rand[1],2);
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { echo "New cpu cores created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }

/////// CPU Frequency GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_clock_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_clock_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(maxtf), MAX(maxtf) FROM notebro_db.CPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=round($rand[0],2);
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=round($rand[1],2);
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { echo "New cpu max turbo created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }

/////// CPU Lunch Date GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_ldate_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_ldate_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(ldate), MAX(ldate) FROM notebro_db.CPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { echo "New cpu launch date created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }

/////// CPU Tech GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_tech_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_tech_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];


$sel="SELECT MIN(tech), MAX(tech) FROM notebro_db.CPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[1];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[0];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { echo "New cpu tech created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }

//CPU max tech values

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_cputech'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT tech FROM notebro_db.CPU WHERE valid=1 ORDER BY tech ASC";
$result = mysqli_query($con, $sel);

$insert=""; 
$nametech="";
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["tech"]) {   $nametech=$nametech."".$rand["tech"].","; }
}
$nametech = trim($nametech,',');
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$nametech');";

if (mysqli_multi_query($con, $insert)) { echo "New cpu tech 2 created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }
mysqli_free_result($result);

/////// CPU TDP GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_tdp_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_tdp_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(tdp), MAX(tdp) FROM notebro_db.CPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { echo "New cpu tdp created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }

/////// GPU PRODUCER GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_prod'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT prod FROM notebro_db.GPU WHERE valid=1 AND typegpu>0";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["prod"])
	{
		$name=$rand["prod"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) { echo "New GPU producers created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} } 
else { echo "Error: " . $insert. "<br>" . mysqli_error($con); }
mysqli_free_result($result);

//// CPU MSC Generation

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_msc'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT msc FROM notebro_db.CPU WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	$elements=explode(',',$rand[0]);
	
		if(count($elements))
		{
		for($i=0; $i<count($elements); $i++)
		{
			switch($elements[$i])
			{
				case "VT-x":
				$elements[$i]="VT-x/AMD-V";
				break;
				case "VT-x2":
				$elements[$i]="VT-x/AMD-V";
				break;
				case "AMD-V":
				$elements[$i]="VT-x/AMD-V";
				break;
				case "VT-d":
				$elements[$i]="VT-d/AMD-Vi";
				break;
				case "AMD-Vi":
				$elements[$i]="VT-d/AMD-Vi";
				break;
				case "IOMMU":
				$elements[$i]="VT-d/AMD-Vi";
				break;					
				case "AVX":
				$elements[$i]="AVX1.0";
				break;					
				case "HT":
				$elements[$i]="HT/Hyper-threading";
				break;
			}
				
			if(!(in_array($elements[$i],$object)))
			{
				$object[]=$elements[$i];
			}
		}
		}
}

mysqli_free_result($result);

$insert="";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'Intel Core i3','INTEL');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'Intel Core i5','INTEL');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'Intel Core i7/i9','INTEL');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'AMD Ryzen','AMD');";
asort($object);
$i=0;
foreach ($object as $msc)
{
	$propthis="";
	if(stripos($msc,"AMD TC")!==FALSE || stripos($msc,"XFR")!==FALSE)
	{  $propthis="AMD"; }
	
	if(stripos($msc,"BPT")!==FALSE || stripos($msc,"TBT")!==FALSE )
	{  $propthis="INTEL"; }
	
	$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', '$msc','$propthis');"; $i++;
}

if (mysqli_multi_query($con, $insert)) {
    echo "New cpu msc created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

//// GPU Architecture

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_arch'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT arch FROM notebro_db.GPU WHERE valid=1";
$result = mysqli_query($con, $sel);


$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["arch"])
	{
		$name=$rand["arch"];
		$sel2="SELECT DISTINCT prod FROM notebro_db.GPU WHERE arch LIKE '$name'  AND valid=1 LIMIT 1";
		$result2 = mysqli_query($con, $sel2);
		$rand2 = mysqli_fetch_array($result2);
		$prod=$rand2["prod"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', '$name', '$prod');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New gpu_arch created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


mysqli_free_result($result);



/////// GPU MEMORY Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_maxmem_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_maxmem_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(maxmem), MAX(maxmem) FROM notebro_db.GPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New gpu_maxmem created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


/////// GPU power Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_power_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_power_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(power), MAX(power) FROM notebro_db.GPU where typegpu>0 AND valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New gpu_power created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// GPU MEMORY Bus Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_membus_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_membus_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(mbw), MAX(mbw) FROM notebro_db.GPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New gpu_membus created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

//GPU max gpumem values

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_gpumem'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT maxmem FROM notebro_db.GPU WHERE valid=1 ORDER BY maxmem ASC";
$result = mysqli_query($con, $sel);

$insert=""; 
$nametech = "";
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["maxmem"])
	{ 
		$nametech=$nametech."".$rand["maxmem"].",";
		
	}
	
}
$nametech = trim($nametech,',');
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$nametech');";

if (mysqli_multi_query($con, $insert)) {
    echo "New list_gpumem  created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

//GPU max gpumembus values

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_gpumembus'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT mbw FROM notebro_db.GPU WHERE valid=1 ORDER BY mbw ASC";
$result = mysqli_query($con, $sel);

$insert=""; 
$nametech = "";
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["mbw"])
	{ 
		$nametech=$nametech."".$rand["mbw"].",";
		
	}
	
}
$nametech = trim($nametech,',');
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$nametech');";

if (mysqli_multi_query($con, $insert)) {
    echo "New list_gpumembus  created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


/////// GPU Lunch Date GENERATION

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_ldate_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_ldate_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(ldate), MAX(ldate) FROM notebro_db.GPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New gpu_ldate created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

//// GPU MSC Generation

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_msc'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT msc FROM notebro_db.GPU WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];
$propthis=(array) [];
$j=0;
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	$elements=explode(',',$rand[0]);

		if(count($elements))
		{
		for($i=0; $i<count($elements); $i++)
		{
		$k=1;
		switch($elements[$i])
			{
				case (strpos($elements[$i],'Burst') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'FreeSync') !== false):
				$elements[$i]="G-Sync/FreeSync";
				break;
				case "G-Sync":
				$elements[$i]="G-Sync/FreeSync";	
				break;
				case (strpos($elements[$i],'Eyefinity') !== false):
				$propthis[$j]="AMD";
				break;
				case (strpos($elements[$i],'CUDA') !== false):
				$propthis[$j]="NVIDIA";
				break;
				case (strpos($elements[$i],'PhysX') !== false):
				$propthis[$j]="NVIDIA";	
				break;
				case (strpos($elements[$i],'3D Vision') !== false):
				$propthis[$j]="NVIDIA";	
				break;
				case (strpos($elements[$i],'Crossfire') !== false):
				$elements[$i]="Crossfire/SLI";	
				break;
				case (strpos($elements[$i],'App Acceleration') !== false):
				$propthis[$j]="AMD";	
				break;
				case (strpos($elements[$i],'SLI') !== false):
				$elements[$i]="Crossfire/SLI";
				break;
				case (strpos($elements[$i],'Optimus') !== false):
				$elements[$i]="Optimus/Enduro";
				break;
				case (strpos($elements[$i],'Enduro') !== false):
				$elements[$i]="Optimus/Enduro";
				break;
				case (strpos($elements[$i],'Based on') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'ZeroCore') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'Nvidia BatteryBoost') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'Nvidia MFAA') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'Nvidia VXGI') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'AMD TrueAudio') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'AMD TressFX') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'PowerPlay') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'HDMI 1.4') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'GPU Boost') !== false):
				$k=0;
				break;
				case (strpos($elements[$i],'PowerTune') !== false):
				$k=0;
				case (strpos($elements[$i],'QuickSync') !== false):
				$k=0; 
				break;
				/*case (strpos($elements[$i],'OpenGL') !== false):
				$k=0;
				break;*/

			}
			
			
			if(!(in_array($elements[$i],$object))&&$k)
			{
				//var_dump($k);
				$object[]=$elements[$i];
				$j++;
			}
		}
		}
}

mysqli_free_result($result);


$insert="";
$i=0;
foreach ($object as $msc)
{
	
	//echo "a "; echo $msc; echo " "; var_dump($propthis[$i]); echo "a<br>";
	
	if(isset($propthis[$i]) && $propthis[$i])
	{
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', '$msc','$propthis[$i]');";
		//echo $insert;
	}
	else
	{
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', '$msc', 'ALL');";

	}
$i++;
}

if (mysqli_multi_query($con, $insert)) {
    echo "New gpu msc created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Hdd capacity min max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'hdd_cap_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'hdd_cap_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(cap), MAX(cap) FROM notebro_db.HDD WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New hdd_cap created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


/////// MEMORY CAP Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_cap_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_cap_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(cap), MAX(cap) FROM notebro_db.MEM WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New mem_cap created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


/////// MEMORY freq Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_freq_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_freq_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(freq), MAX(freq) FROM notebro_db.MEM WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New mem_freq created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Acum nrc Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_nrc_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_nrc_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(nrc), MAX(nrc) FROM notebro_db.ACUM WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New acum nrc created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Acum cap Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_cap_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_cap_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(cap), MAX(cap) FROM notebro_db.ACUM WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New acum cap created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Price Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'price_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'price_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT DISTINCT id FROM notebro_db.MODEL ";
$models = mysqli_fetch_all(mysqli_query($con, $sel));
$min=99999999; $max=0;
foreach($models as $model)
{
	$sel="SELECT MIN(price), MAX(price) FROM notebro_temp.all_conf_".$model[0]." WHERE price>0";
	$minmaxbudget=mysqli_query($cons, $sel);
	if(isset($minmaxbudget) && $minmaxbudget!=FALSE)
	{
		$rand = mysqli_fetch_array($minmaxbudget);
		if($min>$rand[0] && $rand[0]!=NULL) { $min=$rand[0];} //echo $model[0]; echo " "; var_dump($rand); echo "<br>";
		if($max<$rand[1] && $rand[1]!=NULL) { $max=$rand[1];}
	}
}

if(isset($min) && isset ($max))
{	if($min<170){$min=170; echo "prices are messed up"; }
	$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";
	
	if (mysqli_multi_query($con, $insert)) { 
		echo "New pricese created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
	} else {
		echo "Error: " . $insert. "<br>" . mysqli_error($con);
	}
}



/////// Acum batlife Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'batlife_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'batlife_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT DISTINCT id FROM notebro_db.MODEL";
$models = mysqli_fetch_all(mysqli_query($con, $sel));
$min=99999999; $max=0;
foreach($models as $model)
{
$sel="SELECT MIN(batlife), MAX(batlife) FROM notebro_temp.all_conf_".$model[0];
	$minmaxbat=mysqli_query($cons, $sel);
	if(isset($minmaxbat) && $minmaxbat!=FALSE)
	{
		$rand = mysqli_fetch_array($minmaxbat);
		if(intval($rand[0])<0) { echo $model[0]."<br>"; }
		if($min>$rand[0] && $rand[0]!=NULL) { $min=$rand[0]; /*$mymodel=$model[0];*/ } //echo $model[0]; echo " "; var_dump($rand); echo "<br>";
		if($max<$rand[1] && $rand[1]!=NULL) { $max=$rand[1];}
	}
}
//var_dump($mymodel);
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";
if (mysqli_multi_query($con, $insert)) { 
    echo "New acum batlife created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Chassis thic Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_thic_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_thic_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(thic), MAX(thic) FROM notebro_db.CHASSIS WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New chassis thic created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Chassis width Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_width_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_width_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(width), MAX(width) FROM notebro_db.CHASSIS WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New chassis width created successfully<br>"; while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Chassis weight Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_weight_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_weight_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(weight), MAX(weight) FROM notebro_db.CHASSIS WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New chassis weight created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// Chassis width Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_depth_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_depth_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(depth), MAX(depth) FROM notebro_db.CHASSIS WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New chassis depth created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

///////DISPLAY backt

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_msc'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT backt FROM notebro_db.DISPLAY WHERE valid=1";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["backt"])
	{
		$name=$rand["backt"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', '$name','backt');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New display_backt created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}
mysqli_free_result($result);
 
/////Display msc insert hz from msc

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_msc'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"]; 

$sel="SELECT DISTINCT msc FROM notebro_db.DISPLAY WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];



while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	$elements=explode(',',$rand[0]);
	
	if(count($elements))
	{
		for($i=0; $i<count($elements); $i++)
		{ if ((strpos($elements[$i],'Hz')!==false) && (strpos($elements[$i],'60Hz')===false)) { $object[]=$elements[$i]; } }
	}
}
mysqli_free_result($result);
$object[] = "G-Sync/FreeSync";
$object[] = "80% sRGB or better"; 
$msc=array_unique($object);
$insert="";
foreach ($msc as $value)
{
	$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', '$value','msc');";
}

if (mysqli_multi_query($con, $insert)) {
    echo "New display msc created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// DISPLAY SIZE Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_size_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_size_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(size), MAX(size) FROM notebro_db.DISPLAY WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New display_size created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


//DISPLAY max displaysize values
$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_displaysize'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT size FROM notebro_db.DISPLAY WHERE valid=1 ORDER BY size ASC";
$result = mysqli_query($con, $sel);

$insert=""; 
$nametech = "";
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["size"])
	{ 
		$nametech=$nametech."".$rand["size"].",";
		
	}
	
}
$nametech = trim($nametech,',');
//echo $nametech;
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$nametech');";

if (mysqli_multi_query($con, $insert)) {
    echo "New list_displaysize  created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}




/////// DISPLAY HRES Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_hres_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_hres_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(hres), MAX(hres) FROM notebro_db.DISPLAY WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New display_hres created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}
/////// DISPLAY vres Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_vres_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_vres_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(vres), MAX(vres) FROM notebro_db.DISPLAY WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New display_vres created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


/////// DYSPLAY SURFACE TYPE

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_surft'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT surft FROM notebro_db.DISPLAY WHERE valid=1";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["surft"])
	{
		$name=$rand["surft"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New display_surfacet created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


mysqli_free_result($result);

/////// HDD_RPM

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'hdd_rpm'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT rpm FROM notebro_db.HDD WHERE valid=1";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["rpm"])
	{
		$name=$rand["rpm"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New hdd_rpm created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


mysqli_free_result($result);

/////// HDD TYPE

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'hdd_type'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT type FROM notebro_db.HDD WHERE valid=1";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["type"])
	{
		$name=$rand["type"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New hdd_type created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


mysqli_free_result($result);

/////// MEM TYPE

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_type'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT type FROM notebro_db.MEM WHERE valid=1";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["type"])
	{
		$name=$rand["type"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New mem_type created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


mysqli_free_result($result);



//MEM max memcap values

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_memcap'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT cap FROM notebro_db.MEM WHERE valid=1 ORDER BY cap ASC";
$result = mysqli_query($con, $sel);

$insert=""; 
$nametech = "";
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["cap"])
	{ 
		$nametech=$nametech."".$rand["cap"].",";
		
	}
	
}
$nametech = trim($nametech,',');
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$nametech');";

if (mysqli_multi_query($con, $insert)) {
    echo "New list_memcap  created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}



//MEM max memfreq values

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_memfreq'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT freq FROM notebro_db.MEM WHERE valid=1 ORDER BY freq ASC";
$result = mysqli_query($con, $sel);

$insert=""; 
$nametech = "";
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["freq"])
	{ 
		$nametech=$nametech."".$rand["freq"].",";
		
	}//var_dump($rand["tech"]);
	
}
$nametech = trim($nametech,',');
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$nametech');";

if (mysqli_multi_query($con, $insert)) {
    echo "New list_memfreq  created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}



/////// ODD TYPE

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'odd_type'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT type FROM notebro_db.ODD WHERE valid=1 ORDER BY type DESC";
$result = mysqli_query($con, $sel);

$insert="";

		$name="Any/None";
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
		$name="Any optical drive";
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["type"])
	{
		$name=$rand["type"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}


if (mysqli_multi_query($con, $insert)) {
    echo "New odd_type created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}



mysqli_free_result($result);


/////// WNET SPEED

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'wnet_speed'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT speed FROM notebro_db.WNET WHERE valid=1 ORDER BY speed ASC";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["speed"])
	{
		$name=$rand["speed"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New wnet_speed created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


mysqli_free_result($result);


/////// WARRANTY years Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'war_years_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'war_years_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(years), MAX(years) FROM notebro_db.WAR WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New warranty_years created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// DISPLAY RATIO

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_format'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT format FROM notebro_db.DISPLAY WHERE valid=1 ORDER BY format ASC";
$result = mysqli_query($con, $sel);

$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["format"])
	{
		$name=$rand["format"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New display_format created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


mysqli_free_result($result);



//// DISPLAY RESOLUTIONS

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_res'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];


$sel="SELECT DISTINCT CONCAT_WS('', hres, 'x', vres) AS res FROM notebro_db.DISPLAY WHERE valid=1";
$result = mysqli_query($con, $sel);


$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 

	if($rand["res"])
	{
		$name=$rand["res"];
		$resolution=explode("x",$name);
		$verticalresolutions[]=$resolution[1];
		$sel2="SELECT DISTINCT format FROM notebro_db.DISPLAY WHERE hres=$resolution[0] AND vres=$resolution[1] AND valid=1 LIMIT 1";
		$result2 = mysqli_query($con, $sel2);
		$rand2 = mysqli_fetch_array($result2);
		$prod=$rand2["format"];
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`,`prop1`) VALUES ('$type', '$name', '$prod','$resolution[1]');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New display_resolutions created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

mysqli_free_result($result);


//VERTICAL RESOLUTIONS LIST
$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_verres'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$verticalresolutions=array_unique($verticalresolutions);
asort($verticalresolutions);
$res=implode(",",$verticalresolutions);
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$res');";

if (mysqli_multi_query($con, $insert)) {
    echo "New list_verres created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


//CHASSIS max CHASSISWEB values

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'list_chassisweb'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT web FROM notebro_db.CHASSIS WHERE valid=1 ORDER BY web ASC";
$result = mysqli_query($con, $sel);

$insert=""; 
$nametech = "";
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand["web"])
	{ 
		$nametech=$nametech."".$rand["web"].",";
		
	}//var_dump($rand["tech"]);
	
}
$nametech = trim($nametech,',');
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$nametech');";

if (mysqli_multi_query($con, $insert)) {
    echo "New list_chassisweb  created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}




//// CHASSIS Video interfaces

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mb_vport'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT vi FROM notebro_db.CHASSIS WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];
$elements=array();
while($rand = mysqli_fetch_array($result)) 
{
	if(isset($rand[0])&& $rand[0])
	{
		$elements=explode(',',$rand[0]);
		if(count($elements))
		{
			for($i=0; $i<count($elements); $i++)
			{
				$k=1;
				//var_dump($elements[$i]); var_dump($i);
				if(isset($elements[$i]))
				{
					switch($elements[$i])
					{
						case (strpos($elements[$i],'urst') !== false):
						$k=0;
						break;
						case "FreeSync":
						$elements[$i]="G-Sync/FreeSync";	
						break;
					}
				
					if(!(in_array($elements[$i],$object))&&$k)
					{
						$object[]=$elements[$i];
					}
				}
			}
		}
	}
}

mysqli_free_result($result);
$object=array_unique($object);

$insert="";
$i=0;
foreach ($object as $msc)
{
	

		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$msc');";

$i++;
}

if (mysqli_multi_query($con, $insert)) {
    echo "New MDB Video ports created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

//// CHASSIS Input ports

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mb_port'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT pi FROM notebro_db.CHASSIS WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	$elements=explode(',',$rand[0]);
	
		if(count($elements))
		{
		for($i=0; $i<count($elements); $i++)
		{
		$k=1;
		switch($elements[$i])
			{
				case (stripos($elements[$i],'expansion') !== false):
				$elements[$i]="Expansion slot";	
				$k=0;
				break;
				case "FreeSync":
				$elements[$i]="G-Sync/FreeSync";	
				break;
			}
			
			if(!(in_array($elements[$i],$object))&&$k)
			{
				
				$object[]=$elements[$i];
			}
		}
		}
}
$object=array_unique($object);
mysqli_free_result($result);


$insert="";
$i=0;
foreach ($object as $msc)
{
	

		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$msc');";

$i++;
}

if (mysqli_multi_query($con, $insert)) {
    echo "New MDB INPUT ports created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


//// CHASSIS Material

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'ch_made'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT made FROM notebro_db.CHASSIS WHERE valid=1 ORDER BY made ASC";
$result = mysqli_query($con, $sel);
$object=(array) [];
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	{
				
				foreach(explode(",",$rand[0]) as $x)
				{
				
				
					if(stristr($x,"carbon") && stristr($x,"plastic"))
					{   $object[]="Carbon"; $x="Plastic";  }
					elseif(stristr($x,"glass fiber") && stristr($x,"plastic"))
					{   $object[]="Glass fiber"; $x="Plastic";  }
					elseif(stristr($x,"metal"))
					{  $x="Metal";  }
					elseif(stristr($x,"steel"))
					{  $x="Metal";  }
					elseif(stristr($x,"aluminium"))
					{  $x="Aluminium";  }
					elseif(stristr($x,"magnesium"))
					{  $x="Magnesium";  }
					elseif(stristr($x,"carbon"))
					{  $x="Carbon";  }
				
					$object[]=$x; 
				}
					
	}
		
}
$object=array_unique($object);

mysqli_free_result($result);


$insert="";
$i=0;
foreach ($object as $msc)
{
	$prop="";
	switch($msc)
	{
		case (stripos($msc,'Aluminium') !== false):
		$prop="metal";
		break;
		case (stripos($msc,'Magnesium') !== false):
		$prop="metal";
		break;
		case (stripos($msc,'Lithium') !== false):
		$prop="metal";
		break;
		case (stripos($msc,'Magnalium') !== false):
		$prop="metal";
		break;				
		case (stripos($msc,'Metal') !== false):
		$prop="metal";
		break;
	}
				
	$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', '$msc','$prop');";

$i++;
}

if (mysqli_multi_query($con, $insert)) {
    echo "New CH Materials created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


//// CHASSIS MISC STUFF

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'ch_stuff'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT keyboard FROM notebro_db.CHASSIS WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	{		
		foreach(explode(",",$rand[0]) as $x)
		{
			$z=1;
			if(strcasecmp($x,"Standard")!=0)
			{
				if((stripos($x,"Chiclet")!==FALSE) && (stripos($x,"Backlit")!==FALSE))
				{
					$object[]="Chiclet keyboard";
					$object[]="Backlit keyboard";
					$z=0;
				}
				else
				{	
					if((stripos($x,"Standard")!==FALSE) && (stripos($x,"Backlit")!==FALSE))
					{
						$object[]="Backlit keyboard";
						$z=0;
					}
					
					if((stripos($x,"Sealed")!==FALSE) && (stripos($x,"Backlit")!==FALSE))
					{
						$object[]="Backlit keyboard";
						$z=0;
					}
					
					if(stripos($x,"Sealed")!==FALSE)
					{
						$object[]="Spill resistant";
						$z=0;
					}
					
					if((stripos($x,"Backlit")!==FALSE) && (stripos($x,"Virtual")!==FALSE))
					{
						$object[]="Virtual keyboard";
						$object[]="Backlit keyboard";
						$z=0;
					}
					
					if(stripos($x,"RGB LED")!==FALSE)
					{
						$object[]="Backlit keyboard";
						$z=0;
					}
				}
			}
			else { $z=0; }
			if($z){ $object[]=$x; }		
		}	
	}
}

foreach($object as $key=>$el)
{
	if(stripos($el,"Spill resistant")!==FALSE&&stripos($el,"Spill resistant keyboard")===FALSE)
	{
		$object[$key]="Spill resistant keyboard";
	}
}

$sel="SELECT DISTINCT touch FROM notebro_db.CHASSIS WHERE valid=1";
$result = mysqli_query($con, $sel);
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	{
		foreach(explode(",",$rand[0]) as $x)
		{
			$z=1;
			if(strcasecmp($x,"Force Touch")==0 && $z)
			{ $z=0; }
		
			if(strcasecmp($x,"Trackpad display")==0 && $z)
			{ $z=0; }
			
			if(strcasecmp($x,"Multi-Touch")==0 && $z)
			{ $z=0; }
			
			if(strcasecmp($x,"Standard")==0 && $z)
			{ $z=0; }
		
			if($z)
			{ $object[]=$x; }
		}
	}
}

$sel="SELECT DISTINCT msc FROM notebro_db.CHASSIS WHERE valid=1";
$result = mysqli_query($con, $sel);
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0])
	{		
		foreach(explode(",",$rand[0]) as $x)
		{
			$z=1;
			
			if(strcasecmp($x,"Standard"))
			{
				if(preg_match('/\d+\s*x\d+(.\d)*W\s*(speakers|subwoofer)/i',$x))
				{ $z=0; }
				
				if((strpos($x,"Microphone")!==FALSE) && (strpos($x,"array")===FALSE)  && $z)
				{ $z=0; }
				
				if((strpos($x,"IP")!==FALSE) && $z)
				{ $z=0; }
								
				if(((stripos($x,"swap bridge")!==FALSE) || (stripos($x,"ethernet adapter")!==FALSE) || (stripos($x,"ethernet extension")!==FALSE)) && $z)
				{ $z=0; }
					
				if((stripos($x,"optional")!==FALSE) && $z)
				{ $x=str_ireplace(" (optional)","",$x); }
				
				
				if((stripos($x,"Legacy")!==FALSE) && $z)
				{
					$object[]="Legacy ports";
					$z=0;
				}
										
				if((stripos($x,"olufsen")!==FALSE) || (stripos($x,"jbl")!==FALSE) || (stripos($x,"klipsch")!==FALSE) || (stripos($x,"onkyo")!==FALSE) || (stripos($x,"dynaudio")!==FALSE) || (stripos($x,"altec")!==FALSE) || (stripos($x,"harman")!==FALSE) || (stripos($x,"sonicmaster")!==FALSE) && $z)
				{
					$object[]="Premium speakers";
					$z=0;
				}
				
				if((stripos($x,"X LAN")!==FALSE) && $z){ $z=0; }
				if((stripos($x,"RS-232")!==FALSE) && $z){ $z=0; }
				if((stripos($x,"ExpressCard")!==FALSE) && $z){ $z=0; }
				if((stripos($x,"SmartCard")!==FALSE) && $z){ $z=0; }
				if((stripos($x,"SIM card")!==FALSE) && $z){ $z=0; }	
				if((stripos($x,"Touch Bar")!==FALSE) && $z){ $z=0; }
				if((stripos($x,"VGA")!==FALSE) && $z){ $z=0; }						
				
				if((stripos($x,"Rear camera")!==FALSE) && $z)
				{
					$object[]="Rear camera";
					$z=0;
				}
			}
			else
			{ $z=0; }
		
			if($z) {  $object[]=$x; }
			if($z && $x=="IP65") { echo $rand[0]; }
		}	
	}
}
$object=array_unique($object);	
mysqli_free_result($result);


$insert="";
$i=0;
foreach ($object as $msc)
{
	

		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$msc');";

$i++;
}

if (mysqli_multi_query($con, $insert)) {
    echo "New CH Stuff created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}



/////// CHASSIS WEB Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_web_min'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_web_max'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(web), MAX(web) FROM notebro_db.CHASSIS WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

if (mysqli_multi_query($con, $insert)) { 
    echo "New chassis_web created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}







//// Operating Systems

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'sys_os'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];


$sel="SELECT type,CONCAT_WS('', sist, ' ', vers) AS sys_os FROM notebro_db.SIST WHERE valid=1 ORDER BY sist DESC";
$result = mysqli_query($con, $sel);


$insert="";

while($rand = mysqli_fetch_array($result)) 
{ 

	if($rand["sys_os"])
	{
		
		$name=$rand["sys_os"];
		while(substr($name, -1)=="0")
		{	$name=substr($name, 0, -1);	
		}

		if(substr($name, -1)==".")
		$name=substr($name, 0, -1);
	
	if(substr($name, -1)=="0")
	$name=str_replace(" 0","",$name);

	if($rand["type"])
	$name=$name." ".$rand["type"];
	
		$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type', '$name');";
	}
}

if (mysqli_multi_query($con, $insert)) {
    echo "New operating systems created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

mysqli_free_result($result);

mysqli_close($con);

} else {
	echo '<script language="javascript">';
	echo 'alert("You do not have permission!!!")';
	echo '</script>';
	}
?>