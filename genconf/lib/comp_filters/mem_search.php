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

function search_mem ($prod, $capmin, $capmax, $stan, $freqmin, $freqmax, $type, $latmin, $latmax, $voltmin, $voltmax, $misc, $ratemin, $ratemax, $pricemin, $pricemax)
{

	$sel_mem="SELECT id,price,rating,err FROM notebro_db.MEM WHERE ";
	
// Add producers to filter
	$b=0;
	$i=0;
	if(gettype($prod)!="array") { $prod=(array)$prod; }
	foreach($prod as $x)
	{
			
		if($i)
		{  
		$sel_mem.=" OR ";
		}
		else
		{
			
		$sel_mem.=" ( ";
		}
		$b=1;
		$sel_mem.="prod='";
		$sel_mem.=$x;
		$sel_mem.="'";
		$i++;
	
	}
	if($i>0)
		$sel_mem.=" ) ";

// Add cap to filter	
	if($capmin)
	{
	if($b>0)
	$sel_mem.=" AND ";
	$b=1;
	$sel_mem.="cap>=";
	$sel_mem.=$capmin;
	}

 
	if($capmax)
	{
		if($b>0)
		$sel_mem.=" AND ";
		$b=1;
		$sel_mem.="cap<=";
		$sel_mem.=$capmax;
	}	
	
// Add frequency to filter	
	if($freqmin)
	{
	if($b>0)
	$sel_mem.=" AND ";
	$b=1;
	$sel_mem.="freq>=";
	$sel_mem.=$freqmin;
	}

 
	if($freqmax)
	{
		if($b>0)
		$sel_mem.=" AND ";
		$b=1;
		$sel_mem.="freq<=";
		$sel_mem.=$freqmax;
	}	
	
	
		
// Add standard to filter		
	$i=0;
	if(gettype($stan)!="array") { $stan=(array)$stan; }
	foreach($stan as $x)
	{
			
		if($i)
		{  
		$sel_mem.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_mem.=" AND ( ";
		else
		$sel_mem.=" ( ";
		
		}
		$b=1;
		$sel_mem.="stan='";
		$sel_mem.=$x;
		$sel_mem.="'";
		$i++;
	
	}
	if($i>0)
		$sel_mem.=" ) ";	

// Add type to filter	
	$i=0;
	if(gettype($type)!="array") { $type=(array)$type; }
	foreach($type as $x)
	{
			
		if($i)
		{  
		$sel_mem.=" OR ";
		}
		else
		{
		
		if($b>0)
		$sel_mem.=" AND ( ";
		else
		$sel_mem.=" ( ";
		
		}
		$b=1;
		$sel_mem.="type='";
		$sel_mem.=$x;
		$sel_mem.="'";
		$i++;
	
	}
	if($i>0)
		$sel_mem.=" ) ";	
	
// Add latency to filter - smaller is better here		
	if($latmin)
	{
	if($b>0)
	$sel_mem.=" AND ";
	$b=1;
	
	$sel_mem.="lat<=";
	$sel_mem.=$latmin;
	}

 
	if($latmax)
	{
		if($b>0)
		$sel_mem.=" AND ";
		
		$b=1;
		$sel_mem.="lat>=";
		$sel_mem.=$latmax;
	}
	

// Add voltage to filter	
	if($voltmin)
	{
	if($b>0)
	$sel_mem.=" AND ";
	
	$b=1;
	$sel_mem.="volt>=";
	$sel_mem.=$voltmin;
	}

 
	if($voltmax)
	{
		if($b>0)
		$sel_mem.=" AND ";
		
		$b=1;
		$sel_mem.="volt<=";
		$sel_mem.=$voltmax;
	}	

	
// Add MISC to filter
	$i=0; if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
			
		if($i)
		{  
		$sel_mem.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_mem.=" AND ( ";
		else
		$sel_mem.=" ( ";
		}
		$b=1;
		$sel_mem.="FIND_IN_SET('";
		$sel_mem.=$x;
		$sel_mem.="',msc)>0";
		$i++;
	
	}
	if($i>0)
		$sel_mem.=" ) ";	

	// Add rating to filter	
	if($ratemin)
	{
		if($b>0)
		$sel_mem.=" AND ";
		$b=1;
	$sel_mem.="rating>=";
	$sel_mem.=$ratemin;
	}

 
	if($ratemax)
	{
			if($b>0)
			$sel_mem.=" AND ";
			$b=1;
		$sel_mem.="rating<=";
		$sel_mem.=$ratemax;
	}		
	
		
// Add price to filter		
	if ($pricemin)
	{
	if($b>0)
	$sel_mem.=" AND ";
	
	$b=1;
	$sel_mem.="(price+price*err)>=";
	$sel_mem.=$pricemin;
	
	}

 
	if($pricemax)
	{
			if($b>0)
			$sel_mem.=" AND ";
		
		$b=1;
		$sel_mem.="(price-price*err)<=";
		$sel_mem.=$pricemax;
	}
	
	
	
// DO THE SEARCH
	echo "<br>";
	echo $sel_mem;
	echo "<br>";
	
	$result = mysqli_query($GLOBALS['con'], "$sel_mem");
	
	$mem_return = array();
	while($rand = mysqli_fetch_array($result)) 
	{ 

		$mem_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]));

	}
		mysqli_free_result($result);

		return($mem_return);
}

/**********COMMENT/DELETE IF YOU INCLUDE******/
/*
$mem_prod[]="kingston";
$mem_capmin=1;
$mem_capmax=50; //smaller tech is better (so techmin is bigger than techmax)
$pricemin=1;
$pricemax=900;
$mem_stan[]="PC6-8000";
$mem_type[]="ddr5";
//$mem_socket[]="FCBGA1364";
//$mem_socket[]="FPGA946";
//$mem_cachemin=8;

//$mem_turbomin=4.0;
//$mem_coremin=4;
//$mem_intgpu=0;
//$mem_misc[]="AVX";
//$mem_misc[]="HT";
//$mem_ratemin=30;

//COPY this anywhere exactly like it is below. Before you call the function, give values to some of the variables, none of the variables need to have values
$mem_list=search_mem ($mem_prod, $mem_capmin, $mem_capmax, $mem_stan, $mem_freqmin, $mem_freqmax, $mem_type, $mem_latmin, $mem_latmax, $mem_voltmin, $mem_voltmax, $mem_misc, $mem_ratemin, $mem_ratemax, $pricemin,$pricemax);


foreach($mem_list as $x)
{


echo "<br>".$x["price"];
echo " ".$x["rating"];

}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/
?>