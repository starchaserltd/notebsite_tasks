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

function search_wnet ($prod, $model, $misc, $speedmin, $speedmax, $ratemin, $ratemax, $pricemin, $pricemax)
{

	$sel_wnet="SELECT id,price,rating,err FROM WNET WHERE ";
	
// Add producers to filter
	$b=0;
	$i=0;
	if(gettype($prod)!="array") { $prod=(array)$prod; }
	foreach($prod as $x)
	{
			
		if($i)
		{  
		$sel_wnet.=" OR ";
		}
		else
		{
			
		$sel_wnet.=" ( ";
		}
		$b=1;
		$sel_wnet.="prod='";
		$sel_wnet.=$x;
		$sel_wnet.="'";
		$i++;
	
	}
	if($i>0)
		$sel_wnet.=" ) ";

// conditie daca se cauta si dupa $prod

// Add models to filter	
	$i=0;
	if(gettype($model)!="array") { $model=(array)$model; }
	foreach($model as $x)
	{
			
		if($i)
		{  
		$sel_wnet.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_wnet.=" AND ( ";
		else
		$sel_wnet.=" ( ";
		}
		$b=1;
		$sel_wnet.="model='";
		$sel_wnet.=$x;
		$sel_wnet.="'";
		$i++;
	
	}
	if($i>0)
		$sel_wnet.=" ) ";
		
// Add MISC to filter
	$i=0;
	if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
			
		if($i)
		{  
		$sel_wnet.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_wnet.=" AND ( ";
		else
		$sel_wnet.=" ( ";
		}
		$b=1;

		$sel_wnet.="FIND_IN_SET('";
		$sel_wnet.=$x;
		$sel_wnet.="',msc)>0";
		$i++;
	
	}
	if($i>0)
		$sel_wnet.=" ) ";	
//Add speed to filter

if($speedmin)
	{
	if($b>0)
	$sel_wnet.=" AND ";
		
	$b=1;
	$sel_wnet.="speed>=";
	$sel_wnet.=$speedmin;
	}

 
	if($speedmax)
	{
	if($b>0)
	$sel_wnet.=" AND ";
		
	$b=1;
		$sel_wnet.="speed<=";
		$sel_wnet.=$speedmax;
	}		

	// Add rating to filter	
	if($ratemin)
	{
	if($b>0)
	$sel_wnet.=" AND ";
		
	$b=1;
	$sel_wnet.="rating>=";
	$sel_wnet.=$ratemin;
	}

 
	if($ratemax)
	{
	if($b>0)
	$sel_wnet.=" AND ";
		
	$b=1;
		$sel_wnet.="rating<=";
		$sel_wnet.=$ratemax;
	}		
	
		
// Add price to filter		
	if ($pricemin)
	{
	if($b>0)
	$sel_wnet.=" AND ";
		
	$b=1;
	$sel_wnet.="(price+price*err)>=";
	$sel_wnet.=$pricemin;
	
	}

 
	if($pricemax)
	{
	if($b>0)
	$sel_wnet.=" AND ";
		
	$b=1;
		$sel_wnet.="(price-price*err)<=";
		$sel_wnet.=$pricemax;
	}
	
	
	
// DO THE SEARCH
	
//	echo $sel_wnet;
	
	$result = mysqli_query($GLOBALS['con'], "$sel_wnet");
	
	$wnet_return = array();
	while($rand = mysqli_fetch_array($result)) 
	{ 

		$wnet_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]));

	}
		mysqli_free_result($result);

		return($wnet_return);
}

/**********COMMENT/DELETE IF YOU INCLUDE******/
/*
$wnet_prod[]="INTEL";
$wnet_model[]="AC 8X70";
$wnet_speedmin=500;
$wnet_speedmax=750;

//COPY this anywhere exactly like it is below. Before you call the function, give values to some of the variables, none of the variables need to have values
$wnet_list=search_wnet ($wnet_prod, $wnet_model, $wnet_misc, $wnet_speedmin, $wnet_speedmax, $wnet_ratemin, $wnet_ratemax, $pricemin,$pricemax);


foreach($wnet_list as $x)
{


echo "<br>".$x["price"];
echo " ".$x["rating"];

}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/
?>