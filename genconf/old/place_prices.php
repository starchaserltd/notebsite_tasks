<?php
require_once("../../etc/session.php");
require_once("../../etc/con_db.php");
require_once("../../etc/con_sdb.php");
require_once("../../etc/con_rdb.php");

$servers=file('/var/www/vault/etc/sservers', FILE_SKIP_EMPTY_LINES);
$i=0;
foreach($servers as $line)
{ $servers[$i]=explode(" ",trim(preg_replace('/\s+/', ' ', $line))); $i++; }

unset($servers[1][0]);
$hosts=$servers[1];
$server2=1;

foreach($hosts as $x)
{
	$server=$server2;
	$multicons=dbs_connect();
	foreach ($multicons as $cons) { var_dump($cons); echo "<br><br>";}
	echo "<br><br>";

	$rquery="SELECT * FROM notebro_prices.pricing_all_conf WHERE realprice>0 ORDER BY model ASC";
	$cons=$multicons[$server];
	if ($rresult = mysqli_query($rcon, $rquery))
	{
		while ($rrow = mysqli_fetch_assoc($rresult))
		{
			$getrate=mysqli_fetch_assoc(mysqli_query($cons,"SELECT rating FROM notebro_temp.all_conf_".$rrow["model"]." WHERE model=".$rrow["model"]." AND cpu=".$rrow["cpu"]." AND display=".$rrow["display"]." AND mem=".$rrow["mem"]." AND hdd=".$rrow["hdd"]." AND shdd=".$rrow["shdd"]." AND gpu=".$rrow["gpu"]." AND wnet=".$rrow["wnet"]." AND odd=".$rrow["odd"]." AND mdb=".$rrow["mdb"]." AND chassis=".$rrow["chassis"]." AND acum=".$rrow["acum"]." AND war=".$rrow["war"]." AND sist=".$rrow["sist"]." LIMIT 1"));
			$setquery="UPDATE notebro_temp.all_conf_".$rrow["model"]." SET price=".$rrow["realprice"].",value=(".$getrate["rating"]."/".$rrow["realprice"]."),err=(".$rrow["realprice"]."*0.025) WHERE model=".$rrow["model"]." AND cpu=".$rrow["cpu"]." AND display=".$rrow["display"]." AND mem=".$rrow["mem"]." AND hdd=".$rrow["hdd"]." AND shdd=".$rrow["shdd"]." AND gpu=".$rrow["gpu"]." AND wnet=".$rrow["wnet"]." AND odd=".$rrow["odd"]." AND mdb=".$rrow["mdb"]." AND chassis=".$rrow["chassis"]." AND acum=".$rrow["acum"]." AND war=".$rrow["war"]." AND sist=".$rrow["sist"]." LIMIT 1";
			mysqli_query($cons, $setquery);
		}
		mysqli_free_result($result);
	}
	mysqli_close($scon);
	$server2++;
}
mysqli_close($rcon);
?>