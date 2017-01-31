<?php

function search_cpu ($prod, $model, $ldmin, $ldmax, $status, $socket, $techmin, $techmax, $cachemin, $cachemax, $clockmin, $clockmax, $turbomin, $turbomax, $tdpmax, $tdpmin, $coremin, $coremax, $intgpu, $misc, $ratemin, $ratemax, $pricemin, $pricemax, $seltdp)
{
	if($seltdp>0)
	$sel_cpu="SELECT id,price,rating,err,gpu,tdp FROM CPU WHERE ";
	else
	$sel_cpu="SELECT id,price,rating,err,gpu FROM CPU WHERE ";
	
// Add producers to filter
	$b=0;
	$i=0;
	if(gettype($prod)!="array") { $prod=(array)$prod; }
	foreach($prod as $x)
	{
			
		if($i)
		{  
		$sel_cpu.=" OR ";
		}
		else
		{
			
		$sel_cpu.=" ( ";
		}
		$b=1;
		$sel_cpu.="prod='";
		$sel_cpu.=$x;
		$sel_cpu.="'";
		$i++;
	
	}
	if($i>0)
		$sel_cpu.=" ) ";


// Add models to filter	
	$i=0;
	if(gettype($model)!="array") { $model=(array)$model; }
	foreach($model as $x)
	{
			
		if($i)
		{  
		$sel_cpu.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_cpu.=" AND ( ";
		else
		$sel_cpu.=" ( ";	
		}

		$sel_cpu.="model='";
		$sel_cpu.=$x;
		$sel_cpu.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_cpu.=" ) ";
	
	
// Add date to filter		
	if($ldmin)
	{
	
	if($b>0)
	$sel_cpu.=" AND";
	
	$sel_cpu.=" (";
	$sel_cpu.="ldate BETWEEN '";
	$sel_cpu.=$ldmin;
	}
	else
	{
	if($b>0)
	$sel_cpu.=" AND";
	
	$sel_cpu.=" (";
	$sel_cpu.="ldate BETWEEN ";
	$sel_cpu.="'0000-00-00";
	}

 
	if($ldmax)
	{
		$sel_cpu.="' AND '";
		$sel_cpu.=$ldmax;
		$sel_cpu.="')";
	}
	else
	{
	$sel_cpu.="' AND '";
	$sel_cpu.=date('Y-m-d', strtotime("-1 days"));
	$sel_cpu.="')";
	}

// Add STATUS to filter		
	if($status)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="status=";
	$sel_cpu.=$status;
	}	

// Add SOCKET to filter	
	$i=0;
	if(gettype($socket)!="array") { $socket=(array)$socket; }
	foreach($socket as $x)
	{
			
		if($i)
		{  
		$sel_cpu.=" OR ";
		}
		else
		{
			
		$sel_cpu.=" AND ( ";
		}

		$sel_cpu.="socket='";
		$sel_cpu.=$x;
		$sel_cpu.="'";
		$i++;
	
	}
	if($i>0)
		$sel_cpu.=" ) ";	
	
// Add tech to filter - smaller is better here		
	if($techmin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="tech<=";
	$sel_cpu.=$techmin;
	}

 
	if($techmax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="tech>=";
		$sel_cpu.=$techmax;
	}
	

// Add cache to filter	
	if($cachemin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="cache>=";
	$sel_cpu.=$cachemin;
	}

 
	if($cachemax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="cache<=";
		$sel_cpu.=$cachemax;
	}	
	
	// Add clock to filter	
	if($clockmin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="clocks>=";
	$sel_cpu.=$clockmin;
	}

 
	if($clockmax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="clocks<=";
		$sel_cpu.=$clockmax;
	}	
	
		// Add turbo clock to filter	
	if($turbomin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="maxtf>=";
	$sel_cpu.=$turbomin;
	}

 
	if($turbomax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="maxtf<=";
		$sel_cpu.=$turbomax;
	}	
	
	
	// Add tdp to filter		
	if($tdpmin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="tdp>=";
	$sel_cpu.=$tdpmin;
	}

 
	if($tdpmax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="tdp<=";
		$sel_cpu.=$tdpmax;
	}
	
	// Add cores to filter	
	if($coremin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="cores>=";
	$sel_cpu.=$coremin;
	}

 
	if($coremax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="cores<=";
		$sel_cpu.=$coremax;
	}	
	
// Add Integrated GPU to filter		
	if($intgpu)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="gpu>=";
	$sel_cpu.=$intgpu;
	}	
	
// Add MISC to filter
	$i=0;
	if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
			
		if($i)
		{  
		$sel_cpu.=" AND ";
		}
		else
		{
			
		$sel_cpu.=" AND ( ";
		}

		$sel_cpu.="FIND_IN_SET('";
		$sel_cpu.=$x;
		$sel_cpu.="',msc)>0";
		$i++;
	
	}
	if($i>0)
		$sel_cpu.=" ) ";	

	// Add rating to filter	
	if($ratemin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="rating>=";
	$sel_cpu.=$ratemin;
	}

 
	if($ratemax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="rating<=";
		$sel_cpu.=$ratemax;
	}		
	
		
// Add price to filter		
	if ($pricemin)
	{
	$sel_cpu.=" AND ";
	$sel_cpu.="(price+price*err)>=";
	$sel_cpu.=$pricemin;
	
	}

 
	if($pricemax)
	{
		$sel_cpu.=" AND ";
		$sel_cpu.="(price-price*err)<=";
		$sel_cpu.=$pricemax;
	}
	
	
	
// DO THE SEARCH
	
	//echo $sel_cpu;
	
	$result = mysqli_query($GLOBALS['con'], "$sel_cpu");
	$cpu_return = array();
	while($rand = mysqli_fetch_array($result)) 
	{ 
		if($seltdp>0)
		$cpu_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]),"gpu"=>intval($rand[4]),"tdp"=>intval($rand[5]));		
		else
		$cpu_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"rating"=>round($rand[2],3),"err"=>intval($rand[3]),"gpu"=>intval($rand[4]));

	}
		mysqli_free_result($result);

		return($cpu_return);
}

/**********COMMENT/DELETE IF YOU INCLUDE******/

//$cpu_prod[]="INTEL";
//$cpu_prod[]="AMD";
//$cpu_tdpmin=40;
//$cpu_techmin=22; //smaller tech is better (so techmin is bigger than techmax)
//$cpu_tdpmax=60;
//$cpu_ldmin="2013-04-00";
//$pricemin=500;
//$pricemax=900;
//$cpu_socket[]="FCBGA1364";
//$cpu_socket[]="FPGA946";
//$cpu_cachemin=8;
//$cpu_clockmax=3.2;
//$cpu_turbomin=4.0;
//$cpu_coremin=4;
//$cpu_intgpu=0;
//$cpu_misc[]="AVX";
//$cpu_misc[]="HT";
//$cpu_ratemin=30;

//COPY this anywhere exactly like it is below. Before you call the function, give values to some of the variables, none of the variables need to have values
/*$cpu_list=search_cpu ($cpu_prod, $cpu_model, $cpu_ldmin, $cpu_ldmax, $cpu_status, $cpu_socket, $cpu_techmin, $cpu_techmax, $cpu_cachemin, $cpu_cachemax, $cpu_clockmin, $cpu_clockmax, $cpu_turbomin, $cpu_turbomax, $cpu_tdpmax,$cpu_tdpmin, $cpu_coremin, $cpu_coremax, $cpu_intgpu, $cpu_misc, $cpu_ratemin, $cpu_ratemax, $pricemin, $pricemax, $seltdp);


foreach($cpu_list as $x)
{


echo "<br>".$x["price"];
echo " ".$x["rating"];

}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/
?>
