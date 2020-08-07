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

function search_chassis ($prod, $model, $thicmin, $thicmax, $depthmin, $depthmax, $widthmin, $widthmax, $color, $weightmin, $weightmax, $made, $ports, $vports, $webmp, $touch, $misc, $ratemin, $ratemax, $pricemin, $pricemax)
{

	$sel_chassis="SELECT id,price,rating,err FROM CHASSIS WHERE ";
	
// Add producers to filter
	$b=0;
	$i=0;
	if(gettype($prod)!="array") { $prod=(array)$prod; }
	foreach($prod as $x)
	{
			
		if($i) 	
		{  
		$sel_chassis.=" OR ";
		}
		else
		{
			
		$sel_chassis.=" ( ";
		}
		$b=1;
		$sel_chassis.="prod='";
		$sel_chassis.=$x;
		$sel_chassis.="'";
		$i++;
	
	}
	if($i>0)
		$sel_chassis.=" ) ";


// Add models to filter	
	$i=0;
	if(gettype($model)!="array") { $model=(array)$model; }
	foreach($model as $x)
	{
			
		if($i)
		{  
		$sel_chassis.=" OR ";
		}
		else
		{
	
		if($b>0)
		$sel_chassis.="AND ( ";
		else
		$sel_chassis.=" ( ";	

		}

		$sel_chassis.="model='";
		$sel_chassis.=$x;
		$sel_chassis.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_chassis.=" ) ";
	
	
// Add thickness to filter 	

	if($thicmin)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
	$sel_chassis.="thic>=";
	$sel_chassis.=$thicmin;
	}

 
	if($thicmax)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		
		$b=1;
		$sel_chassis.="thic<=";
		$sel_chassis.=$thicmax;
	}
	
// Add depth to filter 	
	if($depthmin)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
	$sel_chassis.="depth>=";
	$sel_chassis.=$depthmin;
	}

 
	if($depthmax)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
		$sel_chassis.="depth<=";
		$sel_chassis.=$depthmax;
	}

// Add width to filter 	
	if($widthmin)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
	$sel_chassis.="width>=";
	$sel_chassis.=$widthmin;
	}

 
	if($widthmax)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
		$sel_chassis.="width<=";
		$sel_chassis.=$widthmax;
	}	
	

// Add COLOR to filter		
	$i=0;
	if(gettype($color)!="array") { $color=(array)$color; }
	foreach($color as $x)
	{
			
		if($i)
		{  
		$sel_chassis.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_chassis.=" AND ( ";
		else
		$sel_chassis.=" ( ";	

	
		}

		$sel_chassis.="color='";
		$sel_chassis.=$x;
		$sel_chassis.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_chassis.=" ) ";
	
	

// Add weight to filter 	
	if($weightmin)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
	$sel_chassis.="weight>=";
	$sel_chassis.=$weightmin;
	}

 
	if($weightmax)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
		$sel_chassis.="weight<=";
		$sel_chassis.=$weightmax;
	}	
	

	// Add material made of  to filter		
	$i=0;
	if(gettype($made)!="array") { $made=(array)$made; }
	foreach($made as $x)
	{
			
		if($i)
		{  
		$sel_chassis.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_chassis.=" AND ( ";
		else
		$sel_chassis.=" ( ";	
	
		}

		$sel_chassis.="made='";
		$sel_chassis.=$x;
		$sel_chassis.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_chassis.=" ) ";

// Port intreface filter

$i=0;
	if(gettype($ports)!="array") { $ports=(array)$ports; }
	foreach($ports as $x)
	{
			
		if($i)
		{  
		$sel_chassis.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_chassis.=" AND ( ";
		else
		$sel_chassis.=" ( ";	
		}

		$b=1;
		$sel_chassis.="FIND_IN_SET('";
		$sel_chassis.=$x;
		$sel_chassis.="',pi)>0";
		$i++;
	
	}
	if($i>0)
		$sel_chassis.=" ) ";	

	
	// video port intreface filter

$i=0;
	if(gettype($vports)!="array") { $vports=(array)$vports; }
	foreach($vports as $x)
	{
			
		if($i)
		{  
		$sel_chassis.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_chassis.=" AND ( ";
		else
		$sel_chassis.=" ( ";	
		}

		$b=1;
		$sel_chassis.="FIND_IN_SET('";
		$sel_chassis.=$x;
		$sel_chassis.="',vi)>0";
		$i++;
	
	}
	if($i>0)
		$sel_chassis.=" ) ";	


	// Add webcam to filter 	
	if($webmp)
	{
		if($b>0)
		$sel_chassis.=" AND ";
		$b=1;
	$sel_chassis.="web>=";
	$sel_chassis.=$webmp;
	}
	
	
//add touch	
$i=0;
	if(gettype($touch)!="array") { $touch=(array)$touch; }
	foreach($touch as $x)
	{
			
		if($i)
		{  
		$sel_chassis.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_chassis.=" AND ( ";
		else
		$sel_chassis.=" ( ";	
		}

		$b=1;
		$sel_chassis.="FIND_IN_SET('";
		$sel_chassis.=$x;
		$sel_chassis.="',touch)>0";
		$i++;
	
	}
	if($i>0)
		$sel_chassis.=" ) ";	
		
	
	// Add MISC to filter
	$i=0;
	if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
			
		if($i)
		{  
		$sel_chassis.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_chassis.=" AND ( ";
		else
		$sel_chassis.=" ( ";	
		}
		$b=1;
		$sel_chassis.="FIND_IN_SET('";
		$sel_chassis.=$x;
		$sel_chassis.="',msc)>0";
		$i++;
	
	}
	if($i>0)
		$sel_chassis.=" ) ";	
	
	
	// Add rating to filter	
	if($ratemin)
	{
	if($b>0)
	$sel_chassis.=" AND ";
	$b=1;
	$sel_chassis.="rating>=";
	$sel_chassis.=$ratemin;
	}

 
	if($ratemax)
	{
	if($b>0)
	$sel_chassis.=" AND ";
	$b=1;
		$sel_chassis.="rating<=";
		$sel_chassis.=$ratemax;
	}		
	
		
// Add price to filter		
	if ($pricemin)
	{
	if($b>0)
	$sel_chassis.=" AND ";
	$b=1;
	$sel_chassis.="(price+price*err)>=";
	$sel_chassis.=$pricemin;
	
	}

 
	if($pricemax)
	{
	if($b>0)
	$sel_chassis.=" AND ";
	$b=1;
		$sel_chassis.="(price-price*err)<=";
		$sel_chassis.=$pricemax;
	}
	
	
	
// DO THE SEARCH
	
//	echo $sel_chassis;
	
	$result = mysqli_query($GLOBALS['con'], "$sel_chassis");
	
	$chassis_return = array();
	while($rand = mysqli_fetch_array($result)) 
	{ 

		$chassis_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]));

	}
		mysqli_free_result($result);

		return($chassis_return);
}

/**********COMMENT/DELETE IF YOU INCLUDE******/
/*
$chassis_prod[]="CLEVO";
$chassis_model[]="P45-455";
$chassis_color[]="BLACK";
//$chassis_thicmin=14;
//$chassis_thicmax=15;
//$chassis_depthmin=300;
//$chassis_depthmax=340;
$chassis_widthmin=200;
$chassis_widthmax=250; 
$chassis_made[]="Aluminium";
$chassis_ports[]="4 X USB 3.0";
//$chassis_ports[]="4 X USB 2.0";
$chassis_touch[]="5points"; //nu trebuie sa avem spatiu in valoarea ce o dam sa o caute


//COPY this anywhere exactly like it is below. Before you call the function, give values to some of the variables, none of the variables need to have values
$chassis_list=search_chassis ($chassis_prod, $chassis_model, $chassis_thicmin, $chassis_thicmax, $chassis_depthmin, $chassis_depthmax, $chassis_widthmin, $chassis_widthmax, $chassis_color, $chassis_weightmin, $chassis_weightmax, $chassis_made, $chassis_ports, $chassis_vports, $chassis_web, $chassis_touch, $chassis_misc, $chassis_ratemin, $chassis_ratemax, $pricemin,$pricemax);


foreach($chassis_list as $x)
{


echo "<br>".$x["price"];
echo " ".$x["rating"];

}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/
?>