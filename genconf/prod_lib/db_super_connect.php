<?php
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
	{ return mysqli_connect_error(); }
    return $link;
}
?>