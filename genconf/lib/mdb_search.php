<?php
//REVIEWED OK
/**********COMMENT/DELETE IF YOU INCLUDE******/
/*
$user="notebro_db";
$pass="nBdBnologin@2";
$database="notebro_db";
$con=mysqli_connect("localhost", $user, $pass, "notebro_db");

if (mysqli_connect_errno($con)) {
    printf("Connect failed: %s\n", mysqli_connect_error());
}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/

function search_mdb ($prod, $model, $ramcap, $gpu, $chip, $socket, $interface, $netw, $hdd, $misc, $ratemin, $ratemax, $pricemin, $pricemax)
{

	$sel_mdb="SELECT id,price,rating,err FROM MDB WHERE ";
	
// Add producers to filter
	$b=0;
	$i=0;
	if(gettype($prod)!="array") { $prod=(array)$prod; }
	foreach($prod as $x)
	{
			
		if($i) 	
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
			
		$sel_mdb.=" ( ";
		}
		$b=1;
		$sel_mdb.="prod='";
		$sel_mdb.=$x;
		$sel_mdb.="'";
		$i++;
	
	}
	if($i>0)
		$sel_mdb.=" ) ";
// conditie daca se cauta si dupa $prod


	
// Add models to filter	
	$i=0;
	if(gettype($model)!="array") { $model=(array)$model; }
	foreach($model as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
		if($b>0)
		$sel_mdb.=" AND ( ";
		else	
		$sel_mdb.=" ( ";
		}

		$sel_mdb.="model='";
		$sel_mdb.=$x;
		$sel_mdb.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_mdb.=" ) ";
	
	
// Add ram to filter 	

	$i=0;
	if(gettype($ramcap)!="array") { $ramcap=(array)$ramcap; }
	foreach($ramcap as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
		if($b>0)
		$sel_mdb.=" AND ( ";
		else	
		$sel_mdb.=" ( ";
		}

		$sel_mdb.="ram='";
		$sel_mdb.=$x;
		$sel_mdb.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_mdb.=" ) ";

// ADD integrated GPU to the filter

	$i=0;
	if(gettype($gpu)!="array") { $gpu=(array)$gpu; }
	foreach($gpu as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
		if($b>0)
		$sel_mdb.=" AND ( ";
		else	
		$sel_mdb.=" ( ";
		}

		$sel_mdb.="gpu=";
		$sel_mdb.=$x;
		$sel_mdb.="";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_mdb.=" ) ";


// ADD Chipset to the filter

	$i=0;
	if(gettype($chip)!="array") { $chip=(array)$chip; }
	foreach($chip as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
		if($b>0)
		$sel_mdb.=" AND ( ";
		else	
		$sel_mdb.=" ( ";
		}

		$sel_mdb.="chipset='";
		$sel_mdb.=$x;
		$sel_mdb.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_mdb.=" ) ";	

	
	// ADD socket to the filter

	$i=0;
	if(gettype($socket)!="array") { $socket=(array)$socket; }
	foreach($socket as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
		if($b>0)
		$sel_mdb.=" AND ( ";
		else	
		$sel_mdb.=" ( ";
		}

		$sel_mdb.="socket='";
		$sel_mdb.=$x;
		$sel_mdb.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_mdb.=" ) ";	
	
	//ADD interfaces to the filter

	$i=0;
	if(gettype($interface)!="array") { $interface=(array)$interface; }
	foreach($interface as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_mdb.=" AND ( ";
		else
		$sel_mdb.=" ( ";	
		}
		$b=1;
		$sel_mdb.="FIND_IN_SET('";
		$sel_mdb.=$x;
		$sel_mdb.="',interface)>0";
		$i++;
	
	}
	if($i>0)
		$sel_mdb.=" ) ";	


	// ADD network to the filter

	$i=0;
	if(gettype($netw)!="array") { $netw=(array)$netw; }
	foreach($netw as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
		if($b>0)
		$sel_mdb.=" AND ( ";
		else	
		$sel_mdb.=" ( ";
		}

		$sel_mdb.="netw='";
		$sel_mdb.=$x;
		$sel_mdb.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_mdb.=" ) ";		
	
	// ADD network to the filter

	$i=0;
	if(gettype($hdd)!="array") { $hdd=(array)$hdd; }
	foreach($hdd as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" OR ";
		}
		else
		{
		if($b>0)
		$sel_mdb.=" AND ( ";
		else	
		$sel_mdb.=" ( ";
		}

		$sel_mdb.="hdd='";
		$sel_mdb.=$x;
		$sel_mdb.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_mdb.=" ) ";	
	
			
	
	// Add MISC to filter
	$i=0; if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
			
		if($i)
		{  
		$sel_mdb.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_mdb.=" AND ( ";
		else
		$sel_mdb.=" ( ";	
		}
		$b=1;
		$sel_mdb.="FIND_IN_SET('";
		$sel_mdb.=$x;
		$sel_mdb.="',msc)>0";
		$i++;
	
	}
	if($i>0)
		$sel_mdb.=" ) ";	
	
	
	// Add rating to filter	
	if($ratemin)
	{
	if($b>0)
	$sel_mdb.=" AND ";
	$b=1;
	$sel_mdb.="rating>=";
	$sel_mdb.=$ratemin;
	}

 
	if($ratemax)
	{
	if($b>0)
	$sel_mdb.=" AND ";
	$b=1;
		$sel_mdb.="rating<=";
		$sel_mdb.=$ratemax;
	}		
	
		
// Add price to filter		
	if ($pricemin)
	{
	if($b>0)
	$sel_mdb.=" AND ";
	$b=1;
	$sel_mdb.="(price+price*err)>=";
	$sel_mdb.=$pricemin;
	
	}

 
	if($pricemax)
	{
	if($b>0)
	$sel_mdb.=" AND ";
	$b=1;
		$sel_mdb.="(price-price*err)<=";
		$sel_mdb.=$pricemax;
	}
	
	
	
// DO THE SEARCH
	
//	echo $sel_mdb;
	
	$result = mysqli_query($GLOBALS['con'], "$sel_mdb");
	
	$mdb_return = array();
	while($rand = mysqli_fetch_array($result)) 
	{ 

		$mdb_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]));

	}
		mysqli_free_result($result);

		return($mdb_return);
}

/**********COMMENT/DELETE IF YOU INCLUDE******/
/*
$mdb_prod[]="DELL";
$mdb_ramcap[]="2 X DDR3";
$mdb_gpu[]=1;
$mdb_gpu[]=2;

//COPY this anywhere exactly like it is below. Before you call the function, give values to some of the variables, none of the variables need to have values
$mdb_list=search_mdb ($mdb_prod, $mdb_model, $mdb_ramcap, $mdb_gpu, $mdb_chip, $mdb_socket, $mdb_interface, $mdb_netw, $mdb_hdd, $mdb_misc, $mdb_ratemin, $mdb_ratemax, $pricemin,$pricemax);


foreach($mdb_list as $x)
{


echo "<br>".$x["price"];
echo " ".$x["rating"];

}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/
?>