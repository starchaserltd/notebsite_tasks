<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//echo getcwd() . "\n";

//*************************************FOR THE WORDPRESS ADMIN CONECTION *********************************************
//require_once("../wp/wp-content/plugins/admin_notebro/con/conf.php");
//echo $allowdirect;
//***********************************************************************************************************************
echo "<br>First validating components!<br>";
$allowdirect = 1;
if(isset($allowdirect) && $allowdirect>0)
{
	$allcomp = array("cpu","acum","mem","display","hdd","shdd","gpu","wnet","odd","mdb","chassis","warranty","sist","regions");
	for ($i = 0; $i<= count($allcomp); $i++)
	{
		if(isset($allcomp[$i]))
		{
			if($allcomp[$i]=="shdd")
			{
				$coloana = "HDD";  $ids = array(); $modelids = array();
				$sql = "SELECT id FROM notebro_db.".$coloana." ORDER BY id ASC"; //echo $sql;
				$resultid = mysqli_query($rcon,$sql);
				while($randid = mysqli_fetch_array($resultid)) { $ids[] = $randid["id"]; }

				$sqli = "SELECT DISTINCT ".$allcomp[$i]." FROM notebro_db.MODEL ORDER BY ".$allcomp[$i]." ASC"; //echo $sqli;
				$resultuse = mysqli_query($rcon,$sqli); 
				while($randuse = mysqli_fetch_array($resultuse)) 
				{ 	
					$randu = explode(',',$randuse[$allcomp[$i]]);
					foreach($randu as $rand) { $modelids[] = $rand; }
				}

				$modelids=array_unique($modelids);
				$fulldiff = array_diff($ids, $modelids);//print_r($fulldiff); 

				foreach($ids as $id)
				{
					if(array_search($id,$fulldiff)==FALSE)
					{
						$valid = 1;
						$sql = "UPDATE ".$coloana." SET valid = '".$valid."' WHERE id = '".$id."'";
						mysqli_query($rcon, $sql);
					}
				}

				echo  $coloana."from SHDD valid was updated successfully.<br>";
			}
			else
			{
				$coloana = strtoupper($allcomp[$i]); $ids = array(); $modelids = array();
				// id-urile cpu-ului
				if ($allcomp[$i]=="warranty")
				{
					$coloana = "WAR";
					$sql = "SELECT id FROM notebro_db.".$coloana." ORDER BY id ASC";
					$resultid = mysqli_query($rcon,$sql);
					while($randid = mysqli_fetch_array($resultid)) { $ids[] = $randid["id"]; }	
				}
				else
				{
					$sql = "SELECT id FROM notebro_db.".$coloana." ORDER BY id ASC";
					$resultid = mysqli_query($rcon,$sql);
					while($randid = mysqli_fetch_array($resultid)) { $ids[] = $randid["id"]; }	
				}
				
				//var_dump($ids);
				// id-urile din model
				
				$sqli = "SELECT DISTINCT ".$allcomp[$i]." FROM notebro_db.MODEL ORDER BY ".$allcomp[$i]." ASC"; 
				$resultuse = mysqli_query($rcon,$sqli); 
				
				while($randuse = mysqli_fetch_array($resultuse)) 
				{ 	
					$randu = explode(',',$randuse[$allcomp[$i]]);
					foreach($randu as $rand) { $modelids[] = $rand; }
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
							mysqli_query($rcon, $sql);
						}
						else 
						{
							$valid = 1;
							$sql = "UPDATE ".$coloana." SET valid = '".$valid."' WHERE id = '".$id."'";
							if(!mysqli_query($rcon, $sql)){ echo "Couldn't update ".$coloana; }
						}
					}
					else
					{
						$valid = 0;
						$sql = "UPDATE ".$coloana." SET valid = '".$valid."' WHERE id = '".$id."'";
						mysqli_query($rcon, $sql);
					}
				}
				
				echo  $coloana.".valid was updated successfully.<br>";
			}
		}
	}
	echo "<br>Validation complete!<br><br><br>";
}
else
{
	echo '<script language="javascript">';
	echo 'alert("You do not have permission!!!")';
	echo '</script>';
}
?>