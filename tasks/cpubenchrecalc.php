<?php
require_once("../etc/con_db.php");

// Cinebench R15 - Single 64-bit
// Cinebench R15 - Multi 64-bit
// Geekbench 4 - Single Core 64-bit
// Geekbench 4 - Multi-Core
// 3DMark - Fire Strike Phyics
// SuperPI 32M

$sql="SELECT COUNT(id) FROM notebro_rate.CPU";
$result=mysqli_query($con,$sql);
$nr=mysqli_fetch_row($result);

$sql="SELECT * FROM notebro_rate.CPU WHERE id=309";
$result=mysqli_query($con,$sql);
$maxel=mysqli_fetch_row($result);

$nr=intval($nr[0]);

for($i=0;$i<$nr;$i++)
{
	$sql="SELECT * FROM notebro_rate.CPU LIMIT ".$i.",1";
	$result=mysqli_query($con,$sql);
	$row=mysqli_fetch_row($result);
	$rate=floatval($row[11])/100;
	
	for($j=4;$j<11;$j++)
	{
		$row[$j]=floatval($row[$j]);
		if($row[$j]==0)
		{ 	if($j!=6)
			{$row[$j]=round((floatval($maxel[$j])*$rate),2);} 
			else {$row[$j]=round((floatval($maxel[$j])/$rate),2); } 
		}
	}

	$sql="UPDATE notebro_rate.CPU SET `Cinebench R15 CPU Single 64Bit`=$row[4], `Cinebench R15 CPU Multi 64Bit`=$row[5], `SuperPI 32M`=$row[6], `PassMark`=$row[7], `Geekbench 4 64bit Single-Core`=$row[8],`Geekbench 4 64bit Multi-Core`=$row[9],`3DMark - Fire Strike Phyics`=$row[10] WHERE id=$row[0]";
	echo $sql;
	echo "<br>";
	//mysqli_query($con,$sql);
}

?>