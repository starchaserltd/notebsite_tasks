#!/usr/bin/php
<?php
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="description" content="Noteb laptop configuration generation"><title>Noteb laptop configuration generation</title></head><body><br>';

//DEBUG STUFF

echo "<br><b>Debug info:</b><br>";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "File root address: "; echo getcwd() . "\n";
echo "<br><b>End debug inf</b><br><br>";

//END OF DEBUG STUFF

//PARAMETERS
$max_configs_limit=22550000;
$max_model_time_limit=18000;
set_time_limit(14000);
ini_set('memory_limit', '7168M');

$BATCH_SIZE = 15000;

//THIS FILE IS SOMETIMES USED ONLY AS INCLUDED
$allowdirect = 1;

if(isset($allowdirect) && $allowdirect>0)
{
	require_once("../etc/session.php");	
	//THIS is a security key to prevent unauthorised access of code, basically we allow this script to work only when it has been accessed by solosearch.php 

	if(strcmp("kMuGLmlIzCWmkNbtksAh",$_SESSION['auth'])==0)
	{
		//$_SESSION['auth']=0;

		$server=0;  if(isset($_GET["s"])){ $server=intval($_GET["s"]); }
		
		require_once("../etc/con_db.php");
		require_once("../etc/con_rdb.php");
		require_once("../etc/con_sdb.php");
		
		//PRODUCTION SERVER SPECIFIC FUNCTIONS
		$prod_server=0;	if(isset($_GET["prod"])){ $prod_server=intval($_GET["prod"]); }
		$new_prices=0;	if(isset($_GET["new_prices"])){ $new_prices=intval($_GET["new_prices"]); }
		$recover_fom_id=0;	if(isset($_GET["recover_from_id"])){ $recover_fom_id=intval($_GET["recover_from_id"]); }

		if($prod_server)
		{
			//GETTING SERVICE LIST
			$max_configs_limit=12550000;
			$servers_2=file('/var/www/vault/etc/sservers', FILE_SKIP_EMPTY_LINES);
			$i=0;
			foreach($servers_2 as $line){ $servers_2[$i]=explode(" ",trim(preg_replace('/\s+/', ' ', $line))); $i++; }
			unset($servers_2[1][0]); $hosts_2=$servers_2[1];
			//RESETTING MARIADB SERVER REMOTELY 
			if($recover_fom_id<2)
			{ shell_exec("ssh -i /var/www/vault/etc/Noteb_sdb.pem centos@".$hosts_2[$server]." -o StrictHostKeyChecking=no -p 2212 'sudo systemctl restart mysql'"); }
			sleep(10);
			//RELOADING PRICER_WEB_SER
			require_once("prod_lib/noteb-price_ws.php"); 
			echo "Reloading noteb-price webservice:<pre>"; var_dump(restart_pricer_web_service()); echo "</pre><br>";
		}

		$use_script="lib/gen_conf.php";
		//if(isset($_GET["new"])){if(intval($_GET["new"])>0){ $use_script="lib/gen_conf_new.php";} }

		//DO MAIN SEARCH
		require($use_script);

		exit();
	}
	else
	{ echo "Incorrect access rights to this file. You do not have permission!!!"; }
}
else { echo "<br><b>You do not have permission!!!</b></br>"; }
?>