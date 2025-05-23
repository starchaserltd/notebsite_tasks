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
$server=1; $multicons=dbs_connect();
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_socket'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_coremin' ";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_coremax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_freqmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_freqmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_ldatemin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_ldatemax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_techmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_techmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_techlist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_tdpmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'cpu_tdpmax'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(tdp), MAX(tdp) FROM notebro_db.CPU WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ( (SELECT `id` FROM `notebro_site`.`nomen_key` WHERE `name` LIKE 'cpu_tdps' LIMIT 1), (SELECT GROUP_CONCAT(DISTINCT `tdp` ORDER BY `CPU`.`tdp` ASC) FROM `notebro_db`.`CPU` WHERE `tdp`>1 AND `valid`=1 ) );";

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

$sel="SELECT * FROM `notebro_site`.`nomen_modifiers` WHERE `type`='cpu_msc_ignore'";
$temp_result=mysqli_query($con,$sel);
$cpu_msc_ignore=array();
if(have_results($temp_result))
{
	$temp_row=NULL;
	while($temp_row=mysqli_fetch_assoc($temp_result))
	{
		$cpu_msc_ignore[]=$temp_row["value_to_replace"];
	}
	unset($temp_row);
	mysqli_free_result($temp_result);
}

$sel="SELECT DISTINCT msc FROM notebro_db.CPU WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];
$msc_info=array();
$msc_info["inserted"]=array();
$msc_info["ignored"]=array();
$msc_info["forced_ignored"]=array();
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0]){ $elements=explode(',',$rand[0]); }
	if(count($elements))
	{
		for($i=0; $i<count($elements); $i++)
		{
			$k=true;
			switch($elements[$i])
			{
				case "VT-x":
				{
					$elements[$i]="Virtualization";
					break;
				}
				case "VT-x2":
				{
					$elements[$i]="Virtualization";
					break;
				}
				case "ARM-V":
				{
					$elements[$i]="Virtualization";
					break;
				}
				case "AMD-V":
				{
					$elements[$i]="Virtualization";
					break;
				}
				case "AMD PRO":
				{
					$elements[$i]="Business features";
					break;
				}
				case "vPro":
				{
					$elements[$i]="Business features";
					break;
				}
				case "VT-d":
				{
					$elements[$i]="VT-d/AMD-Vi";
					break;
				}
				case "AMD-Vi":
				{
					$elements[$i]="VT-d/AMD-Vi";
					break;
				}
				case "IOMMU":
				{
					$elements[$i]="VT-d/AMD-Vi";
					break;
				}											
				case "HT":
				{
					$elements[$i]="Multithreading";
					break;
				}
				case "SMT":
				{
					$elements[$i]="Multithreading";
					break;
				}
				default:
				{	
					/*$temp_key=NULL; $temp_val=NULL;
					foreach($cpu_msc_ignore as $temp_key=>$temp_val)
					{
						if(stripos($elements[$i],$temp_val)!==FALSE)
						{
							$k=false;
							break;
						}
					}
					*/
					$msc_info["ignored"][]=$elements[$i];
					$k=0;
					break;
				}
			}
			
			if($k)				
			{
				if(!(in_array($elements[$i],$object)))
				{ $object[]=$elements[$i]; }
			}
		}
	}
}
$msc_info["forced_ignored"]=$cpu_msc_ignore;
unset($cpu_msc_ignore);
mysqli_free_result($result);

$insert="";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'Intel Core 3/i3','INTEL');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'Intel Core 5/i5','INTEL');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'Intel Core 7/9/i7/i9','INTEL');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'Intel Xeon','INTEL');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'AMD Ryzen 3','AMD');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'AMD Ryzen 5','AMD');";
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type', 'AMD Ryzen 7','AMD');";
asort($object);
$object=array_unique($object);
$msc_info["ignored"]=array_unique($msc_info["ignored"]);
$msc_info["forced_ignored"]=array_unique($msc_info["forced_ignored"]);
$msc_info["inserted"]=$object;
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
echo "<br>CPU MSC INFO:<br>";
echo "CPU MSC INSERTED:<br>"; foreach($msc_info["inserted"] as $val){echo $val." | ";} echo "<br>";
echo "CPU MSC IGNORED:<br>"; foreach($msc_info["ignored"] as $val){echo $val." | ";}  echo "<br>";
echo "CPU MSC FORCED IGNORED:<br>"; foreach($msc_info["forced_ignored"] as $val){echo $val." | ";}  echo "<br><br>";
unset($msc_info); 
	
//// GPU Architecture

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_arch'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT arch FROM notebro_db.GPU WHERE valid=1 and typegpu>0";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_memmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_memmax'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(maxmem), MAX(maxmem) FROM notebro_db.GPU WHERE valid=1 AND typegpu>0";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_powermin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_powermax'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT MIN(power), MAX(power) FROM notebro_db.GPU where typegpu>0 AND valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ( (SELECT `id` FROM `notebro_site`.`nomen_key` WHERE `name` LIKE 'gpu_tdps' LIMIT 1), (SELECT GROUP_CONCAT(DISTINCT `power` ORDER BY `GPU`.`power` ASC) FROM `notebro_db`.`GPU` WHERE `typegpu`>0 AND `power`>1 AND `valid`=1 ) );";

if (mysqli_multi_query($con, $insert)) { 
    echo "New gpu_power created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
    echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

/////// GPU MEMORY Bus Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_membusmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_membusmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_memlist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_membuslist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_ldatemin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'gpu_ldatemax'";
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
$msc_info=array();
$msc_info["inserted"]=array();
$msc_info["ignored"]=array();
$msc_info["forced_ignored"]=array();
$propthis=(array) [];
$j=0;
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0]){ $elements=explode(',',$rand[0]); }
	if(count($elements))
	{
		for($i=0; $i<count($elements); $i++)
		{
			$k=1;
			switch($elements[$i])
			{
				case (strpos($elements[$i],'FreeSync') !== false):
				{
					$elements[$i]="G-Sync/FreeSync";
					break;
				}
				case "G-Sync":
				{
					$elements[$i]="G-Sync/FreeSync";	
					break;
				}
				case (strpos($elements[$i],'CUDA') !== false):
				{
					$propthis[$j]="NVIDIA";
					break;
				}
				case (strpos($elements[$i],'Crossfire') !== false):
				{
					$elements[$i]="Crossfire/SLI";	
					break;
				}
				case (strpos($elements[$i],'SLI') !== false):
				{
					$elements[$i]="Crossfire/SLI";
					break;
				}
				case (strpos($elements[$i],'Optimus') !== false):
				{
					$elements[$i]="Optimus/Enduro";
					break;
				}
				case (strpos($elements[$i],'Enduro') !== false):
				{
					$elements[$i]="Optimus/Enduro";
					break;
					
				}
				case (stripos($elements[$i],'ray tracing') !== false):
				{
					$elements[$i]="Ray Tracing";
					break;
				}
				default:
				{ $k=0; break; }
			}
				
			if(!(in_array($elements[$i],$object))&&$k)
			{
				//var_dump($k);
				$object[]=$elements[$i];
				$j++;
			}
			else
			{ if(!in_array($elements[$i],$object)) { $msc_info["ignored"][]=$elements[$i]; } }
		}
	}
}

mysqli_free_result($result);

asort($object);
$object=array_unique($object);
$msc_info["ignored"]=array_unique($msc_info["ignored"]);
$msc_info["inserted"]=$object;
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
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`,`prop`) VALUES ('$type','Replaceable (MXM)', 'ALL');";

if (mysqli_multi_query($con, $insert)) {
	echo "New gpu msc created successfully<br>";
	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;}  
} else {
	echo "Error: " . $insert. "<br>" . mysqli_error($con);
}

echo "<br>GPU MSC INFO:<br>";
echo "GPU MSC INSERTED:<br>"; foreach($msc_info["inserted"] as $val){echo $val." | ";} echo "<br>";
echo "GPU MSC IGNORED:<br>"; foreach($msc_info["ignored"] as $val){echo $val." | ";}  echo "<br>";
echo "GPU MSC FORCED IGNORED:<br>"; foreach($msc_info["forced_ignored"] as $val){echo $val." | ";}  echo "<br><br>";
unset($msc_info);

/////// Hdd capacity min max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'hdd_capmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'hdd_capmax'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type2=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'hdd_sizes'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type3=$rand["id"];

$sel="SELECT MIN(cap), MAX(cap) FROM notebro_db.HDD WHERE valid=1";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));

$min=$rand[0];
$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	
$max=$rand[1];
$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";

$sel="SELECT DISTINCT `cap`,`type` FROM `HDD` WHERE valid=1 ORDER by type";
$query=mysqli_query($con,$sel); $storage_size_list=array();
while($rand = mysqli_fetch_assoc($query))
{ $storage_size_list[$rand["type"]][]=intval($rand["cap"]); }

//Generate possible storage sizes
$f_storage_list=array();
foreach($storage_size_list as $key=>$val)
{
	switch($key)
	{
		case "EMMC":
		{
			foreach($val as $val2)
			{ $f_storage_list[]=$val2;}
			break;
		}
		case "HDD":
		{
			foreach($val as $val2)
			{
				$f_storage_list[]=$val2;
				foreach($storage_size_list["HDD"] as $val3)
				{ $f_storage_list[]=$val2+$val3; }
			}
			break;
		}
		case "SHDD":
		{
			foreach($val as $val2)
			{
				$f_storage_list[]=$val2;
				foreach($storage_size_list["HDD"] as $val3)
				{ $f_storage_list[]=$val2+$val3; }
			}
			break;
		}
		case "SSD":
		{
			foreach($val as $val2)
			{
				$f_storage_list[]=$val2;
				if($val2>90)
				{
					foreach($storage_size_list["HDD"] as $val3)
					{ $f_storage_list[]=$val2+$val3; }
					foreach($storage_size_list["SSD"] as $val3)
					{ if($val3>90){ $f_storage_list[]=$val2+$val3; } }
				}
			}
			break;
		}
		default:
		{
			foreach($val as $val2)
			{ $f_storage_list[]=$val2;}
			break;
		}
	}
}
$f_storage_list=array_unique($f_storage_list); 
sort($f_storage_list);

$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type3', '".implode(",",$f_storage_list)."');";

if (mysqli_multi_query($con, $insert)) { 
	echo "New hdd_cap created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
} else {
	echo "Error: " . $insert. "<br>" . mysqli_error($con);
}


/////// MEMORY CAP Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_capmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_capmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_freqmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_freqmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_nrcmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_nrcmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_capmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_capmax'";
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
{	if($min<120){$min=120; echo "prices are messed up"; }
	$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type1', '$min');";
	$insert.="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('$type2', '$max');";
	
	if (mysqli_multi_query($con, $insert)) { 
		echo "New pricese created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) ) {;} 
	} else {
		echo "Error: " . $insert. "<br>" . mysqli_error($con);
	}
}



/////// Acum batlife Min Max

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_batlifemin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'acum_batlifemax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_thicmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_thicmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_widthmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_widthmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_weightmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_weightmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_depthmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_depthmax'";
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

//this code is if I want to extract something from MSC
while($rand = mysqli_fetch_array($result)) 
{ 

	$elements=array();
	if($rand[0]) { $elements=explode(',',$rand[0]); }
	/*
	if(count($elements))
	{
		for($i=0; $i<count($elements); $i++)
		{ if ((strpos($elements[$i],'Hz')!==false) && (strpos($elements[$i],'60Hz')===false)) { $object[]=$elements[$i]; } }
	}*/
}
mysqli_free_result($result);

$sel="SELECT DISTINCT `hz` FROM `notebro_db`.`DISPLAY` WHERE valid=1 ORDER BY `hz` ASC";
$result = mysqli_query($con, $sel);
$object=(array) [];
while($rand = mysqli_fetch_array($result)) 
{
	$rand["hz"]=intval($rand["hz"]);
	if($rand["hz"]>60){ $object[]=$rand["hz"]."Hz"; }
}
mysqli_free_result($result);

$sel="SELECT DISTINCT `hdr` FROM `notebro_db`.`DISPLAY` WHERE valid=1 ORDER BY `hdr` ASC";
$result = mysqli_query($con, $sel);
while($rand = mysqli_fetch_array($result)) 
{
	$rand["hdr"]=intval($rand["hdr"]);
	if($rand["hdr"]>0){ $object[]="HDR"; }
}	
mysqli_free_result($result);

$sel="SELECT DISTINCT `srgb` FROM `notebro_db`.`DISPLAY` WHERE valid=1 ORDER BY `srgb` ASC";
$result = mysqli_query($con, $sel);
while($rand = mysqli_fetch_array($result)) 
{
	$rand["srgb"]=intval($rand["srgb"]);
	if($rand["srgb"]>80){ $object[] = "80% sRGB or better"; }
}
mysqli_free_result($result);

$sel="SELECT DISTINCT `dci-p3` FROM `notebro_db`.`DISPLAY` WHERE valid=1 ORDER BY `dci-p3` ASC";
$result = mysqli_query($con, $sel);
/*
while($rand = mysqli_fetch_array($result)) 
{
	$rand["dci-p3"]=intval($rand["dci-p3"]);
	if($rand["dci-p3"]>60){ $object[] = "60% DCI-P3 or better"; }
}*/
mysqli_free_result($result);
	
$object[] = "G-Sync/FreeSync";

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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_sizemin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_sizemax'";
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
$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_sizelist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_hresmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_hresmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_vresmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_vresmax'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_caplist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mem_freqlist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'war_yearsmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'war_yearsmax'";
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
$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'display_vreslist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_webcamlist'";
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mdb_vport'";
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
						case ((stripos($elements[$i],'micro HDMI') !== false)||(stripos($elements[$i],'mini HDMI') !== false)):
							$k=0;
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'mdb_port'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type=$rand["id"];

$sel="SELECT DISTINCT pi FROM notebro_db.CHASSIS WHERE valid=1";
$result = mysqli_query($con, $sel);
$object=(array) [];
while($rand = mysqli_fetch_array($result)) 
{ 
	if($rand[0]){ $elements=explode(',',$rand[0]); }
	
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
				case (stripos($elements[$i],'RS-232') !== false):
				$k=0;
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_made'";
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
					elseif(stristr($x,"titanium"))
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
		case (stripos($msc,'Titanium') !== false):
		{ $prop="metal"; break; }
		case (stripos($msc,'Aluminium') !== false):
		{ $prop="metal"; break; }
		case (stripos($msc,'Magnesium') !== false):
		{ $prop="metal"; break; }
		case (stripos($msc,'Lithium') !== false):
		{ $prop="metal"; break; }
		case (stripos($msc,'Magnalium') !== false):
		{ $prop="metal"; break; }			
		case (stripos($msc,'Metal') !== false):
		{ $prop="metal"; break; }
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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_msc'";
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
					
					if((stripos($x,"RGB LED")!==FALSE)||(stripos($x,"RGB keyboard")!==FALSE))
					{
						$object[]="RGB keyboard";
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
										
				if((stripos($x,"omnisonic")!==FALSE) || (stripos($x,"olufsen")!==FALSE) || (stripos($x,"jbl")!==FALSE) || (stripos($x,"klipsch")!==FALSE) || (stripos($x,"onkyo")!==FALSE) || (stripos($x,"dynaudio")!==FALSE) || (stripos($x,"akg")!==FALSE) || (stripos($x,"altec")!==FALSE) || (stripos($x,"harman")!==FALSE) || (stripos($x,"sonicmaster")!==FALSE) && $z)
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
				if((stripos($x,"X DP")!==FALSE) && $z){ $z=0; }
				if((stripos($x,"numpad")!==FALSE) && $z){ $z=0; }				
				
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
$object[]="USB-C Charger";

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

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_webmin'";
$rand = mysqli_fetch_array(mysqli_query($con, $sel));
$type1=$rand["id"];

$sel="SELECT id FROM notebro_site.nomen_key WHERE name LIKE 'chassis_webmax'";
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

$SEL="SELECT `id` FROM `notebro_site`.`nomen_key` WHERE `name` LIKE 'sys_os'";
$rand = mysqli_fetch_array(mysqli_query($con, $SEL));
$type=$rand["id"];

$SEL="SELECT `type`,`vers`,`sist` FROM `notebro_db`.`SIST` WHERE `valid`=1 ORDER BY `sist` DESC";
$result = mysqli_query($con, $SEL);

$INSERT=[];

while($rand = mysqli_fetch_array($result)) 
{ 
	if(floatval($rand["vers"])==0)
	{ $sys_os=$rand["sist"]; }
	else
	{
		$vers=strval($rand["vers"]);
		while(substr($vers, -1)=="0")
		{ $vers=substr($vers, 0, -1); }
		
		if(substr($vers, -1)=="."){ $vers=substr($vers, 0, -1); }
		
		$sys_os=$rand["sist"]." ".$vers;
	
	}
	if((strcasecmp($rand["sist"],"Linux")!=0) && $rand["type"])
	{ $sys_os=$sys_os." ".$rand["type"]; }
	
	$INSERT[]="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('".$type."', '".$sys_os."'); ";
}
$INSERT=array_unique($INSERT);
$INSERT=implode(" ",$INSERT);

if (mysqli_multi_query($con, $INSERT))
{ echo "New operating systems created successfully<br>"; 	while ( mysqli_more_results($con) && mysqli_next_result($con) )  {;} }
else
{ echo "Error: " . $INSERT. "<br>" . mysqli_error($con); }


/// SWAPING options
$sql="SELECT `swap`.*,`nomen`.`name`,`nomen`.`id` AS `org_id` FROM `notebro_site`.`nomen_swap` `swap` JOIN `notebro_site`.`nomen` `nomen` ON `swap`.`prop_name`=`nomen`.`name`";
$result=mysqli_query($con,$sql);
if($result&&mysqli_num_rows($result)>0)
{
	while($row=mysqli_fetch_assoc($result))
	{
		$insert="INSERT INTO `notebro_site`.`nomen` (`type`,`name`) VALUES ('".$row["swap_to_key"]."','".$row["name"]."');";
		mysqli_query($con,$insert);
		if(!(intval($row["duplicate"])))
		{
			$insert="DELETE FROM `notebro_site`.`nomen` WHERE `id`=".$row["org_id"]."";
			mysqli_query($con,$insert);
		}
	}
}
mysqli_free_result($result);

/// Replace and delete options
$sql="SELECT `notebro_site`.`nomen_modifiers`.* FROM `notebro_site`.`nomen_modifiers` WHERE type='replace' ORDER BY `exec_order` ASC;";
$result=mysqli_query($con,$sql);
if($result&&mysqli_num_rows($result)>0)
{
	while($row=mysqli_fetch_assoc($result))
	{
		$sql_replace="UPDATE `notebro_site`.`nomen` SET `nomen`.`name`='".$row["value_to_insert"]."' WHERE `name` LIKE '%".$row["value_to_replace"]."%' LIMIT 1;";
		$result_replace=mysqli_query($con,$sql_replace);
		if($result_replace)
		{ $sql_replace="DELETE FROM `notebro_site`.`nomen` WHERE `name`!='".$row["value_to_insert"]."' AND `name` LIKE '%".$row["value_to_replace"]."%';"; if(!is_bool($result_replace)){ mysqli_free_result($result_replace); }}
		mysqli_query($con,$sql_replace);
	}
}
mysqli_free_result($result);

require_once("sitedata_gen.php");

if(is_resource($con)){mysqli_close($con);}

} else {
	echo '<script language="javascript">';
	echo 'alert("You do not have permission!!!")';
	echo '</script>';
	}
?>