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

function search_odd ($type, $prod, $misc, $speedmin, $speedmax, $ratemin, $ratemax, $pricemin, $pricemax)
{

	$sel_odd="SELECT id,price,rating,err FROM ODD WHERE ";
	
// Add producers to filter
	$b=0;
	$i=0;
	if(gettype($type)!="array") { $type=(array)$type; }
	foreach($type as $x)
	{
			
		if($i)
		{  
		$sel_odd.=" OR ";
		}
		else
		{
			
		$sel_odd.=" ( ";
		}
		$b=1;
		$sel_odd.="type='";
		$sel_odd.=$x;
		$sel_odd.="'";
		$i++;
	
	}
	if($i>0)
		$sel_odd.=" ) ";



// Add prod to filter	
	$i=0;
	if(gettype($prod)!="array") { $prod=(array)$prod; }
	foreach($prod as $x)
	{
			
		if($i)
		{  
		$sel_odd.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_odd.=" AND ( ";
		else
		$sel_odd.=" ( ";
		}
		$b=1;
		$sel_odd.="prod='";
		$sel_odd.=$x;
		$sel_odd.="'";
		$i++;
	
	}
	if($i>0)
		$sel_odd.=" ) ";	
		
// Add MISC to filter
	$i=0;
	if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
			
		if($i)
		{  
		$sel_odd.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_odd.=" AND ( ";
		else
		$sel_odd.=" ( ";
		}
		$b=1;

		$sel_odd.="FIND_IN_SET('";
		$sel_odd.=$x;
		$sel_odd.="',msc)>0";
		$i++;
	
	}
	if($i>0)
		$sel_odd.=" ) ";	
//Add speed to filter

if($speedmin)
	{
	if($b>0)
	$sel_odd.=" AND ";
	
	$b=1;
	$sel_odd.="speed>=";
	$sel_odd.=$speedmin;
	}

 
	if($speedmax)
	{
	if($b>0)
	$sel_odd.=" AND ";
	
	$b=1;
		$sel_odd.="speed<=";
		$sel_odd.=$speedmax;
	}		

	// Add rating to filter	
	if($ratemin)
	{
	if($b>0)
	$sel_odd.=" AND ";
	
	$b=1;
	$sel_odd.="rating>=";
	$sel_odd.=$ratemin;
	}

 
	if($ratemax)
	{
	if($b>0)
	$sel_odd.=" AND ";
	
	$b=1;
		$sel_odd.="rating<=";
		$sel_odd.=$ratemax;
	}		
	
		
// Add price to filter		
	if ($pricemin)
	{
	if($b>0)
	$sel_odd.=" AND ";
	
	$b=1;
	$sel_odd.="(price+price*err)>=";
	$sel_odd.=$pricemin;
	
	}

 
	if($pricemax)
	{
	if($b>0)
	$sel_odd.=" AND ";
	
	$b=1;
		$sel_odd.="(price-price*err)<=";
		$sel_odd.=$pricemax;
	}
	
	
	
// DO THE SEARCH
	
//	echo $sel_odd;
	
	$result = mysqli_query($GLOBALS['con'], "$sel_odd");
	
	$odd_return = array();
	while($rand = mysqli_fetch_array($result)) 
	{ 

		$odd_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]));

	}
		mysqli_free_result($result);

		return($odd_return);
}

/**********COMMENT/DELETE IF YOU INCLUDE******/
/*
$odd_type[]="DVD-RW";
$odd_speedmin=20;
$odd_speedmax=40;
//$odd_prod[]="ASUS";
//COPY this anywhere exactly like it is below. Before you call the function, give values to some of the variables, none of the variables need to have values
$odd_list=search_odd ($odd_type, $odd_prod, $odd_misc, $odd_speedmin, $odd_speedmax, $odd_ratemin, $odd_ratemax, $odd_pricemin, $odd_pricemax);


foreach($odd_list as $x)
{


echo "<br>".$x["price"];
echo " ".$x["rating"];

}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/
?>