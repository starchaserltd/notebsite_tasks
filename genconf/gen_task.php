<?php

//all 172.31.2.33 172.31.4.253 172.31.1.219
//active 172.31.2.33 172.31.4.253
//inactive 172.31.1.219
function db_super_connect()
{
   static $link;
   $link = mysqli_init();

	$user="notebro_super";
	$pass="nBdBnologin&4";
	$database="";
	$host="172.31.13.210";
	$port="3306";
			 
    $con = mysqli_real_connect($link, $host, $user, $pass, $database);

    // If connection was not successful, handle the error
    if($con === false)
	{
       // Handle error - notify administrator, log to a file, show an error screen, etc.
       return mysqli_connect_error();
    }
    return $link;
}

$con_super=db_super_connect();
mysqli_query($con_super,"STOP SLAVE");
require_once("/var/www/vault/tasks/rating.php");

$file_address="/var/www/noteb/etc/sservers";
$servers=file($file_address, FILE_SKIP_EMPTY_LINES);
$set=3; //this needs to come from a get
if(isset($_GET["set"])){ $set=intval($_GET["set"]); }

$loop=1;
if($set==3)
{ $loop=2; $set=0; }
$do_nomen=TRUE;

while($loop)
{
	if($set==0 || $set==1)
	{
		$servers=file($file_address, FILE_SKIP_EMPTY_LINES);
		$i=0;
		foreach($servers as $line)
		{ $servers[$i]=explode(" ",trim(preg_replace('/\s+/', ' ', $line))); $i++; }

		$nr_actvsrv=(count($servers[1])-1);
		$nr_actvsrvm=round($nr_actvsrv/2,0);
		$movetoinactive=array();
		$nr_mservers=0;
		
		if($set==0)
		{
			for($i=1;$i<=$nr_actvsrvm;$i++)
			{ $movetoinactive[$i]=$servers[1][$i]; $nr_mservers++;}
		}

		if($set==1)
		{	$nr_actvsrvm++;
			for($i=$nr_actvsrvm;$i<=$nr_actvsrv;$i++)
			{ $movetoinactive[$i]=$servers[1][$i]; $nr_mservers++; }
		}		

		// Inactivate servers
		foreach($movetoinactive as $key=>$value)
		{
			unset($servers[1][$key]);
			$servers[2]["i".$key]=$value;
		}
		ksort($servers[2]); ksort($servers[1]);
		$string=""; foreach($servers as $line) { $string.=implode(" ",$line);  $string.="\r\n"; }
		$myfile = fopen($file_address, "wb") or die("Unable to open file!"); fwrite ($myfile,$string); fclose($myfile);

		/* RUN THE CODE FOR THESE SERVERS */
		echo "It is working!";
		
		set_time_limit($nr_mservers*7200);
		$ch=array();
		$mh = curl_multi_init();
		curl_setopt($mh, CURLOPT_TIMEOUT, $nr_mservers*7200);
		$i=0;
		foreach($movetoinactive as $value)
		{
			$skey=array_search($value,$servers[0]);
			
			$ch[$i] = curl_init('http://localhost/vault/genconf/gen_search.php?s='.$skey);
			curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch[$i], CURLOPT_TIMEOUT, 7200);
			curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT ,60);
			// build the multi-curl handle, adding both $ch
			curl_multi_add_handle($mh, $ch[$i]);
			$i++;
		}

		// execute all queries simultaneously, and continue when all are complete
		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} 	while ($running);
	  
		// all of our requests are done, we can now access the results
		$response_1 = curl_multi_getcontent($ch[0]);
		echo "$response_1"; 

		for($i=0;$i<$nr_mservers;$i++)
		{
			curl_multi_remove_handle($mh, $ch[$i]);
		}
		curl_multi_close($mh);

		// Reactivate servers 
		foreach($movetoinactive as $key=>$value)
		{
			unset($servers[2]["i".$key]);
			$servers[1][$key]=$value;
		}
		ksort($servers[2]); ksort($servers[1]);
		$string=""; foreach($servers as $line) { $string.=implode(" ",$line);  $string.="\r\n"; }
		$myfile = fopen($file_address, "wb") or die("Unable to open file!"); fwrite ($myfile,$string); fclose($myfile);
	
	if($do_nomen)
	{ require_once("/var/www/vault/tasks/nom_gen.php"); $do_nomen=FALSE; }
	
	}
	$set=1;
	$loop--;
}
mysqli_query($con_super,"START SLAVE");
?>