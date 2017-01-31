<?php 

require_once("con_db.php");
mysqli_select_db($con,"notebro_db");

echo "Script is working! <br>";

$sql="SELECT id,cpu,gpu FROM MODEL";
$result=mysqli_query($con,$sql);

while($main_row=mysqli_fetch_assoc($result))
{
	$update=1;
//var_dump($main_row); // echo "<br>";

$cpulist=explode(",",$main_row["cpu"]);
$gpulist=explode(",",$main_row["gpu"]);
$gpulist2=explode(",",$main_row["gpu"]);
$newgpulist=array();

	foreach($cpulist as $cpu)
	{
		$sql="SELECT gpu FROM notebro_db.CPU WHERE id=$cpu";
		$result2=mysqli_query($con,$sql);
		$row=mysqli_fetch_assoc($result2);

		if(($key=array_search($row["gpu"],$gpulist))!==FALSE)
		{
			$newgpulist[]=$row["gpu"];
			unset($gpulist[$key]);
		}
	
	}
	
	foreach($gpulist as $gpu)
	{
	
		$sql="SELECT typegpu FROM notebro_db.GPU WHERE id=$gpu";
		$result2=mysqli_query($con,$sql);
		$row=mysqli_fetch_assoc($result2);

		if(intval($row["typegpu"])>0)
		{
			$update=0;

		}
	}
	
$newgpulist=array_unique($newgpulist);

if($update)
{
$newgpulist=array_unique($newgpulist);
}
else
{
$newgpulist=array_unique($gpulist2);
}

$newgpu=implode(",",$newgpulist);
$sql="UPDATE MODEL SET gpu='".$newgpu."' WHERE id=".$main_row["id"];
//echo $sql; echo "<br>";
echo "FOR SAFETY THIS SCRIPT NEEDS MANUAL EDIT TO WORK! <br>";  
// uncomment below
//mysqli_query($con,$sql);	

}

echo "Script executed succesfully, hopefully everything is still there! <br>";
?>