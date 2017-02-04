<?php 

include("../etc/con_db.php");

$sql="SELECT * FROM MDB";
$result=mysqli_query($con,$sql);
while($row = mysqli_fetch_assoc($result))
{

if(stripos($row["submodel"],"WWAN")!==FALSE)
{
		if(stripos($row["msc"],"WWAN")===FALSE)
		{
			$update=$row["msc"].",WWAN card";
			$sql="UPDATE MDB SET MSC='".$update."' WHERE id=".$row['id'];
		mysqli_query($con,$sql);
			
		}
}



}



?>