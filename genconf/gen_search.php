#!/usr/bin/php
<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo getcwd() . "\n";
*/
$memory_limit=12550000;
require_once("../etc/session.php");

//require_once '/usr/share/php/monetdb/php_monetdb.php';
//$db = monetdb_connect("sql" , "localhost", 50000, "notebro_db" , "nBdBnologin@2", "all_conf");
//monetdb_query("DROP TABLE all_conf");
//monetdb_query('CREATE TABLE all_conf (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, model INT, cpu INT, display INT, mem INT, hdd INT, shdd INT, gpu INT, wnet INT, odd INT, mdb INT, chassis INT, acum INT, war INT, sist INT, rating INT, price INT, value FLOAT,  confg_code INT UNIQUE, err FLOAT, batlife //DECIMAL(10,1), capacity INT)');

/* SIMPLE SEARCH */
//THIS is a security key to prevent unauthorised access of code, basically we allow this script to work only when it has been accessed by solosearch.php 
if(strcmp("kMuGLmlIzCWmkNbtksAh",$_SESSION['auth'])==0)
{
//$_SESSION['auth']=0;
require_once("../etc/con_db.php");
require_once("../etc/con_sdb.php");
require_once("../etc/con_rdb.php");

if(isset($_GET["s"]))
{ $server=(int)$_GET["s"]; }
else
{ $server=0; }


$servers_2=file('/var/www/vault/etc/sservers', FILE_SKIP_EMPTY_LINES);
$i=0;
foreach($servers_2 as $line)
{ $servers_2[$i]=explode(" ",trim(preg_replace('/\s+/', ' ', $line))); $i++; }
unset($servers_2[1][0]);
$hosts_2=$servers_2[1];

//$ip_to_reset=explode(" ",mysqli_get_host_info ($hosts_2[$server]))[0];
shell_exec("ssh -i /var/www/vault/etc/Noteb_sdb.pem centos@".$hosts_2[$server]." -o StrictHostKeyChecking=no -p 2212 'sudo systemctl restart mysql'");
//echo "ssh -i /var/www/vault/etc/Noteb_sdb.pem centos@".$hosts_2[$server]." -o StrictHostKeyChecking=no -p 2212 'sudo systemctl restart mysql'";
sleep(10); ///NU UITA!

function restart_pricer_web_service(){
	$opts=array(
		'http' => array(
			'method' => "GET",
			'header' => "Content-Type: application/x-www-form-urlencoded"
		)
	);
	$context = stream_context_create($opts);
	$file=file_get_contents('http://0.0.0.0:6667/reload-tables',false,$context);
	return json_decode($file,false);
}

echo "<br>"; var_dump(restart_pricer_web_service()); echo "<br>";

//initializare 1 variabila pentru functii
$cpu_tdpmin=0.01; $gpu_powermin=0.00; $display_hresmin=0.01; $hdd_capmin=0.01; $war_yearsmin=0.01; $acum_capmin=0.01; $wnet_ratemin=0.01; $sist_pricemax=1; $odd_speedmin=0.00; $mem_capmin=0.01; $mdb_ratemin=0.01; $chassis_weightmin=0.01; 

//var_dump($db);
$budgetmin = 0;
$budgetmax = 99999999999999999999999;
// Trebuie sa scriem undeva pe ce componente vom filtra, daca nu vrem sa le filtram pe toate. In esenta nu vrem asta, pentru ca ok, procesoarele le filtram mereu, dar parca nu ai vrea sa incarci in memorie 2000 de placi de baza cand tie nu-ti voi trebuie in final decat vreo 40; Acestea sunt standard, restul le definim ad-hoc prin switchuri. Pentru a intelege ce fac aceste variabile uitati-va in gen_config.php
$cpu_s=1; $gpu_s=1; $display_s=1; $acum_s=1;  $sist_s=1; $war_s=1; $hdd_s=1; $shdd_s=0; $wnet_s=0; $shdd_s=0; $mem_s=0; $mdb_s=0; $chassis_s=0; $odd_s=0; 

	
//DO MAIN SEARCH
require_once("lib/init.php");
require("lib/gen_confg.php");

//echo "da";

//	echo "Number of configurations found:".$ex."<br>";
/* 
	$sel3="SELECT * FROM notebro_temp.$temp_table ORDER BY RAND() LIMIT 5";
	$result = mysqli_query($cons, $sel3) ;
		
		while($rand = mysqli_fetch_array($result)) 
		{ 
		echo $rand["model"]." ".$rand["cpu"]." ".$rand["gpu"]." ".$rand["price"]." ".$rand["rating"];
		
		echo "<br>";


		}
			mysqli_free_result($result);
*/		
//SET @sql_text = CONCAT("CREATE TEMPORARY TABLE ",@tablename," (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, model INT(6), cpu INT(6), display INT(6), mem INT(6), hdd INT(6), shhd INT(6), gpu INT(6), wnet INT(6), odd INT(6), mdb INT(6), chassis INT(6), acum INT(6), war INT(6), sist INT(6), rating INT(6), price INT(6), value FLOAT,  confg_code INT(20) UNIQUE) ENGINE MEMORY"); PREPARE stmt

//echo "<br>".$temp_table;
//var_dump($con);
//header("Location: searchresult.php");
exit();
}
else
{
	
	echo "Heh! What are you trying to do?";
}
?>

