<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//echo getcwd() . "\n";

// "cpu","acum","mem","display","hdd","shdd","gpu","wnet","odd","mdb","chassis","warranty","sist"
require_once("../etc/con_db.php");
$allcomp = array("cpu","acum","mem","display","hdd","shdd","gpu","wnet","odd","mdb","chassis","warranty","sist"); //print_r($allcomp);
$i=0;
for ($i = 0; $i<= count($allcomp); $i++) {

if($allcomp[$i]) {
	if($allcomp[$i]=="shdd") {
																	
							$coloana = "HDD"; 
							$ids = array();
							$modelids = array();


							$sql = "SELECT id FROM notebro_db.".$coloana." ORDER BY id ASC"; //echo $sql;
								$resultid = mysqli_query($con,$sql);
								while($randid = mysqli_fetch_array($resultid)) 
									{ $ids[] = $randid["id"]; }

							$sqli = "SELECT DISTINCT ".$allcomp[$i]." FROM notebro_db.MODEL ORDER BY ".$allcomp[$i]." ASC"; //echo $sqli;
								$resultuse = mysqli_query($con,$sqli); 
								while($randuse = mysqli_fetch_array($resultuse)) 
									{ 	
										$randu = explode(',',$randuse[$allcomp[$i]]);
										foreach($randu as $rand) 
											{ $modelids[] = $rand; }
									}

							$modelids=array_unique($modelids);
							$fulldiff = array_diff($ids, $modelids);//print_r($fulldiff); 


							foreach($ids as $id)
								{
								if(array_search($id,$fulldiff)==FALSE)
									{
		
										$valid = 1;
										$sql = "UPDATE ".$coloana." SET valid = '".$valid."' WHERE id = '".$id."'";

										if(mysqli_query($con, $sql))
										{}
											else
												{ echo "ERROR: Could not able to execute $sql. " . mysqli_error($con); }
									}
									else {}
								}

							echo  $coloana."from SHDD valid was updated successfully.<br>";
								
								
								
								
							}
				else {


				$coloana = strtoupper($allcomp[$i]);
$ids = array();
$modelids = array();

// id-urile cpu-ului
if ($allcomp[$i]=="warranty") 
		{
		$coloana = "WAR";
		$sql = "SELECT id FROM notebro_db.".$coloana." ORDER BY id ASC";
		$resultid = mysqli_query($con,$sql);
		while($randid = mysqli_fetch_array($resultid)) 
		{ $ids[] = $randid["id"]; }	
		}
else {
	$sql = "SELECT id FROM notebro_db.".$coloana." ORDER BY id ASC";
	$resultid = mysqli_query($con,$sql);
	while($randid = mysqli_fetch_array($resultid)) 
	{ $ids[] = $randid["id"]; }	
	
	}
//var_dump($ids);
// id-urile din model
$sqli = "SELECT DISTINCT ".$allcomp[$i]." FROM notebro_db.MODEL ORDER BY ".$allcomp[$i]." ASC"; 
$resultuse = mysqli_query($con,$sqli); 
while($randuse = mysqli_fetch_array($resultuse)) 
	{ 	
		$randu = explode(',',$randuse[$allcomp[$i]]);
		foreach($randu as $rand) 
			{ $modelids[] = $rand; }
	}

$modelids=array_unique($modelids);
$fulldiff = array_diff($ids, $modelids);//print_r($fulldiff); 

//echo "<br>Id-uri Cpu lipsa :".count($fulldiff)."<br>";
//var_dump($fulldiff);
foreach($ids as $id)
{
	if(array_search($id,$fulldiff)==FALSE)
	{
			if ($allcomp[$i]=="warranty") 
		{ 
		$coloana = "WAR";
		$valid = 1;
		$sql = "UPDATE ".$coloana." SET valid = '".$valid."' WHERE id = '".$id."'";
		if(mysqli_query($con, $sql))
		{}
		else
		{ echo "ERROR: Could not able to execute $sql. " . mysqli_error($con); }
		}
		else 
		{
		$valid = 1;
		$sql = "UPDATE ".$coloana." SET valid = '".$valid."' WHERE id = '".$id."'";
		if(mysqli_query($con, $sql))
		{}
		else
		{ echo "ERROR: Could not able to execute $sql. " . mysqli_error($con); }	
		}
	}
	else
	{
		
		$valid = 0;
		$sql = "UPDATE ".$coloana." SET valid = '".$valid."' WHERE id = '".$id."'";

		if(mysqli_query($con, $sql))
		{ }
		else
		{ echo "ERROR: Could not able to execute $sql. " . mysqli_error($con); }
	}
}

echo  $coloana.".valid was updated successfully.<br>";
}}}
mysqli_close($con);
?>