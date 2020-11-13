#!/usr/bin/php
<?php

//all 172.31.2.33 172.31.4.253 172.31.1.219
//active 172.31.2.33 172.31.4.253
//inactive 172.31.1.219

$max_gen_time=19800; //seconds
echo "\r\n"; echo "<br>DATE: *********   ".date('l jS \of F Y h:i:s A')."   ********\r\n<br>";

echo "<br>Doing the ratings!<br>";
$ch = curl_init('http://86.123.134.36/notebro/admin/tasks/rating.php');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-HTTP-Method-Override: GET"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,60);
$result = curl_exec($ch);
if($result === false)
{ echo "\r\n<br><b>ERROR WHILE DOING THE RATINGS:</b><br>\r\n<pre>"; echo curl_error($ch);  echo "</pre>\r\n<br><br>\r\n"; }
else
{ echo "\r\n<br><b>REPLY FROM RATINGS SCRIPT:</b><br>\r\n<pre>"; var_dump($result); echo "</pre>\r\n<br><br>\r\n"; }
curl_close($ch);
echo "<br>";

//require_once("/var/www/vault/tasks/rating.php");
usleep(30000);

//DONE DOING THE RATINGS

//STOPING REPLICATION
require_once("/var/www/vault/genconf/prod_lib/db_super_connect.php"); 
$con_super=db_super_connect();
mysqli_query($con_super,"STOP SLAVE IO_THREAD");
require_once("/var/www/vault/etc/con_sdb.php");

//GETTING SERVER LIST
ini_set('track_errors', 1); //asadsa
$file_address="/var/www/noteb/etc/sservers";
$servers=file($file_address, FILE_SKIP_EMPTY_LINES);
$set=3;
if(isset($_GET["set"])){ $set=intval($_GET["set"]); }

$loop=1;
if($set==3){ $loop=2; $set=0; }
$do_nomen=TRUE;

//RUNNING THE CONF GENERATION SCRIPT FOR EVERY SDB SERVER
while($loop)
{
	if($set==0 || $set==1)
	{
		$servers=file($file_address, FILE_SKIP_EMPTY_LINES);
		$i=0;
		foreach($servers as $line)
		{ $servers[$i]=explode(" ",trim(preg_replace('/\s+/',' ',$line))); $i++; }

		$nr_actvsrv=(count($servers[1])-1);
		if($nr_actvsrv<2)
		{ generation_failed_manage("Number of servers to generate is less than 2. Aborting generation!"); }
		$nr_actvsrvm=round($nr_actvsrv/2,0);
		$movetoinactive=array();
		$nr_mservers=0;
		
		if($set==0)
		{
			for($i=1;$i<=$nr_actvsrvm;$i++)
			{ $movetoinactive[$i]=$servers[1][$i]; $nr_mservers++;}
		}

		if($set==1)
		{	
			$nr_actvsrvm++;
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
		//$myfile = fopen($file_address, "wb") or die ("Error opening file: ".error_get_last()["message"]); fwrite ($myfile,$string); fclose($myfile);
		
		// RUN THE CODE FOR THESE SERVERS //
		echo "\r\n<br><b>Price generation started for servers: </b>".implode(" ",$movetoinactive)."<br>\r\n";
		
		set_time_limit($nr_mservers*$max_gen_time);
		$ch=array();
		$mh = curl_multi_init();
		$i=0;
		foreach($movetoinactive as $value)
		{
			$skey=array_search($value,$servers[0]);
			echo "\r\n<br><b>Starting price generation for server:</b> ".$skey."<br>\r\n";
			
			$ch[$i] = curl_init('http://172.31.0.196/vault/genconf/gen_search.php?s='.$skey.'&prod=1');
			curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch[$i], CURLOPT_TIMEOUT, $max_gen_time);
			curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT ,60);
			curl_setopt($ch[$i], CURLOPT_WRITEFUNCTION, function($curl,$data){ echo $data; if(ob_get_level()>0){ ob_flush(); } flush(); return strlen($data); });
			curl_multi_add_handle($mh, $ch[$i]);
			$i++;
			
			#TESTING GENERATION
			$gen_success_tests=0;
			$cons=dbs_connect($value);
			$TEST_SQL="SELECT * FROM `notebro_temp`.`best_low_opt` LIMIT 1";
			$test_result=mysqli_query($cons[0],$TEST_SQL);
			if(have_results($test_result))
			{
				$test_row=mysqli_fetch_row($test_result);
				if(isset($test_row[0]) && strlen(strval($test_row[0]))>0)
				{ $gen_success_tests++; }
				unset($test_row);
				mysqli_free_result($test_result);
			}
			$TEST_SQL="SELECT * FROM `notebro_temp`.`m_map_table` LIMIT 1";
			$test_result=mysqli_query($cons[0],$TEST_SQL);
			if(have_results($test_result))
			{
				$test_row=mysqli_fetch_row($test_result);
				if(isset($test_row[0]) && strlen(strval($test_row[0]))>0)
				{ $gen_success_tests++; }
				unset($test_row);
				mysqli_free_result($test_result);
			}
			if($gen_success_tests<2)
			{ generation_failed_manage("Generation failed on server ".$value." . Last temporary tables failed to generate!"); }
			#TEST COMPLETED
			
		}
		//curl_setopt($mh, CURLOPT_TIMEOUT, $nr_mservers*$max_gen_time);
		
		// This CODE IS DESGINED TO execute all queries simultaneously, and continue when all are complete.
		// HOWEVER, WE SEND ONLY ONE QUERY AT A TIME.
		$running = null; $mrc=null;
		do
		{
			$mrc = curl_multi_exec($mh, $running);
		} 	while ($running && $mrc == CURLM_CALL_MULTI_PERFORM);
	  
		while ($running && $mrc == CURLM_OK)
		{
			if (curl_multi_select($mh) == -1) { usleep(100); }
			do 
			{
				$mrc = curl_multi_exec($mh, $running);
			}	while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
	  
		// All of our requests are done, we can now access the results
		echo "\r\n<br><b>REPLY FROM THE PRICE GENERATION SCRIPTS IS BUFFERED AND DISPLAYED ONLY WHEN DONE.</b><br>\r\n";
		echo "<b>PRICE GENERATION IS DONE FOR SERVER ".$skey.".</b><br>\r\n";
		$response_1 = curl_multi_getcontent($ch[0]);
		echo "\r\n<br><b>PRICE GENERATION IS OVER FOR THIS SERVER, HERE IS THE OUTPUT (IF THERE IS ANY LEFT):</b><br>\r\n";
		echo $response_1; 
		
		for($i=0;$i<$nr_mservers;$i++)
		{ curl_multi_remove_handle($mh, $ch[$i]); }
		curl_multi_close($mh);

		// Reactivate servers 
		foreach($movetoinactive as $key=>$value)
		{
			unset($servers[2]["i".$key]);
			$servers[1][$key]=$value;
		}
		ksort($servers[2]); ksort($servers[1]);
		$string=""; foreach($servers as $line) { $string.=implode(" ",$line);  $string.="\r\n"; }
		$myfile = fopen($file_address, "wb") or die ("Error opening file: ".error_get_last()["message"]); fwrite ($myfile,$string); fclose($myfile);
		
		//AFTER THE FIRST SERVER IS DONE, THE NOMEN TABLE IS REGENERATED
		if($do_nomen)
		{ echo "\r\n<br><b>NOW GENERATING THE NOMEN TABLE.</b><br><br>\r\n"; require_once("/var/www/vault/tasks/nom_gen.php"); $do_nomen=FALSE; }
	}
	$set=1;
	$loop--;
}
//RESTARTING REPLICATION
mysqli_query($con_super,"START SLAVE"); mysqli_close($con_super);

function generation_failed_manage($error)
{
	echo "\r\n<br><b>".$error."</b><br>";
	exit(0);
}
?>