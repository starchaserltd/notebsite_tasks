<?php
function db_connect()
{
   static $link;
   $link = mysqli_init();

	$user="notebro_db";
	$pass="nBdBnologin&4";
	$database="notebro_db";
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

$con=db_connect();
?>
