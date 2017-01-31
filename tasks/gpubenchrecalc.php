<?php
require_once("con_db.php");

// 3DMark 2013 Fire Strike Standard GPU
// Cinebench R15 OpenGL 64Bit
// 3DMark Time Spy Graphics
// 3DMark Cloud Gate Graphics Standard 

$sql="SELECT COUNT(id) FROM notebro_rate.GPU";
$result=mysqli_query($con,$sql);
$nr=mysqli_fetch_row($result);

$sql="SELECT * FROM notebro_rate.GPU WHERE id=401";
$result=mysqli_query($con,$sql);
$maxel=mysqli_fetch_row($result);

$nr=intval($nr[0]);

for($i=0;$i<$nr;$i++)
{
	$sql="SELECT * FROM notebro_rate.GPU LIMIT ".$i.",1";
	$result=mysqli_query($con,$sql);
	$row=mysqli_fetch_row($result);
	$rate=floatval($row[11])/100;
	
	for($j=4;$j<11;$j++)
	{
		$row[$j]=floatval($row[$j]);
		if($row[$j]==0)
		{ 	if($j!=99)
			{$row[$j]=round((floatval($maxel[$j])*$rate),2);} 
			else {$row[$j]=round((floatval($maxel[$j])/$rate),2); } 
		}
	}

	$sql="UPDATE notebro_rate.GPU SET `3DMark 2013 Fire Strike Standard GPU`=$row[4], `3DMark Vantage P GPU`=$row[5], `3DMark 2006`=$row[6], `Unigine Heaven 3.0 DX11`=$row[7], `Cinebench R15 OpenGL 64Bit`=$row[8], `3DMark Time Spy Graphics`=$row[9],`3DMark Cloud Gate Graphics Standard`=$row[10] WHERE id=$row[0]";
	echo $sql;
	echo "<br>";
	//mysqli_query($con,$sql);
}

?>