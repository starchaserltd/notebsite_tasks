<?php
//REVIEWED OK
/**********COMMENT/DELETE IF YOU INCLUDE******/
/*$user="notebro_db";
$pass="nBdBnologin@2";
$database="notebro_db";
$con=mysqli_connect("localhost", $user, $pass, "notebro_db");

if (mysqli_connect_errno($con)) {
    printf("Connect failed: %s\n", mysqli_connect_error());
}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/



function search_gpu ($typegpumin, $typegpumax, $prod, $model, $arch, $techmin, $techmax, $shadermin, $cspeedmin, $cspeedmax, $sspeedmin, $sspeedmax, $mspeedmin, $mspeedmax, $mbwmin, $mtype, $maxmemmin, $maxmemmax, $sharem, $powermin, $powermax, $misc, $ratemin, $ratemax, $pricemin, $pricemax, $seltdp)
{

	if($seltdp>0)
	$sel_gpu="SELECT id,model FROM GPU WHERE ";
	else
	$sel_gpu="SELECT id,model FROM GPU WHERE ";
	
// Add Type filter (Integrated / Dedicated / Professional)
	$b=0;
	
	if ($typegpumin != NULL )
	{
			
		$b=1;
		$sel_gpu.="typegpu>=";
		$sel_gpu.=$typegpumin;
	
	}
	
		if ($typegpumax != NULL )
	{
			
		$b=1;
		$sel_gpu.="typegpu<";
		$sel_gpu.=$typegpumax;
	
	}

// Add models to filter	
	$i=0;
	if(gettype($prod)!="array") { $prod=(array)$prod; }
	foreach($prod as $x)
	{
			
		if($i)
		{  
		$sel_gpu.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_gpu.=" AND ( ";
		else
		$sel_gpu.=" ( ";
		}

		$sel_gpu.="prod='";
		$sel_gpu.=$x;
		$sel_gpu.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_gpu.=" ) ";


// Add models to filter	
	$i=0;
	if(gettype($model)!="array") { $prod=(array)$model; }
	foreach($model as $x)
	{
			
		if($i)
		{  
		$sel_gpu.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_gpu.="AND ( ";
		else
		$sel_gpu.=" ( ";
		}

		$sel_gpu.="model='";
		$sel_gpu.=$x;
		$sel_gpu.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_gpu.=" ) ";
		
	
// Add gpu architecture to filter	
	$i=0;
	if(gettype($arch)!="array") { $arch=(array)$arch; }
	foreach($arch as $x)
	{
			
		if($i)
		{  
		$sel_gpu.=" OR ";
		}
		else
		{
			
		if($b>0)
		$sel_gpu.=" AND ( ";
		else
		$sel_gpu.=" ( ";
		
		}

		$sel_gpu.="arch='";
		$sel_gpu.=$x;
		$sel_gpu.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_gpu.=" ) ";
		
// Add tech to filter - smaller is better here		
	if($techmin)
	{
	if($b>0)
	$sel_gpu.=" AND ";
	$b=1;
	$sel_gpu.="tech>=";
	$sel_gpu.=$techmin;
	}

 
	if($techmax)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
		$sel_gpu.="tech<=";
		$sel_gpu.=$techmax;
	}
	
// Minimum Shader model filter
	if($shadermin)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
	$sel_gpu.="shader>=";
	$sel_gpu.=$shadermin;
	}


	
// Add core speed to filter 	
	if($cspeedmin)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
	$sel_gpu.="cspeed>=";
	$sel_gpu.=$cspeedmin;
	}

 
	if($cspeedmax)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
		$sel_gpu.="cspeed<=";
		$sel_gpu.=$cspeedmax;
	}

// Add shader speed to filter 	
	if($sspeedmin)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
	$sel_gpu.="sspeed>=";
	$sel_gpu.=$sspeedmin;
	}

 
	if($sspeedmax)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
		$sel_gpu.="sspeed<=";
		$sel_gpu.=$sspeedmax;
	}	

// Add memory speed to filter 	
	if($mspeedmin)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
	$sel_gpu.="sspeed>=";
	$sel_gpu.=$mspeedmin;
	}

 
	if($mspeedmax)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
		$sel_gpu.="sspeed<=";
		$sel_gpu.=$mspeedmax;
	}
	
	
	// Add memory bus filter 	**************
	if($mbwmin)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
	$sel_gpu.="mbw>=";
	$sel_gpu.=$mbwmin;
	}

                               
// Add memory type to the filter	
	$i=0;
	if(gettype($mtype)!="array") { $mtype=(array)$mtype; }
	foreach($mtype as $x)
	{
		
		if($i)
		{  
		$sel_gpu.=" OR ";
		}
		else
		{
		 if($b>0)
		$sel_gpu.=" AND ( ";
		else
		$sel_gpu.=" ( ";
		}

		$sel_gpu.="mtype='";
		$sel_gpu.=$x;
		$sel_gpu.="'";
		$i++;
		$b=1;
	}

	if($i>0)
		$sel_gpu.=" ) ";

	// Add memory size to filter 	
	if($maxmemmin)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
	$sel_gpu.="maxmem>=";
	$sel_gpu.=$maxmemmin;
	}

 
	if($maxmemmax)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
		$sel_gpu.="maxmem<=";
		$sel_gpu.=$maxmemmax;
	}
	
	// ADD shared memory filter
		if($sharem!=NULL)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
		$sel_gpu.="sharem=";
		$sel_gpu.=$sharem;
	}
	
		// Add TDP filter	
	if($powermin)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
	$sel_gpu.="power>=";
	$sel_gpu.=$powermin;
	}

 
	if($powermax)
	{
		if($b>0)
		$sel_gpu.=" AND ";
		$b=1;
		$sel_gpu.="power<=";
		$sel_gpu.=$powermax;
	}
	
	// Add MISC to filter
	$i=0;
	if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
			
		if($i)
		{  
		$sel_gpu.=" AND ";
		}
		else
		{
			
		if($b>0)
		$sel_gpu.=" AND ( ";
		else
		$sel_gpu.=" ( ";	
		}
		$b=1;
		$sel_gpu.="FIND_IN_SET('";
		$sel_gpu.=$x;
		$sel_gpu.="',msc)>0";
		$i++;
	
	}
	if($i>0)
		$sel_gpu.=" ) ";	
	
	
	// Add rating to filter	
	if($ratemin)
	{
	if($b>0)
	$sel_gpu.=" AND ";
	$b=1;
	$sel_gpu.="rating>=";
	$sel_gpu.=$ratemin;
	}

 
	if($ratemax)
	{
	if($b>0)
	$sel_gpu.=" AND ";
	$b=1;
		$sel_gpu.="rating<=";
		$sel_gpu.=$ratemax;
	}		
	
		
// Add price to filter		
	if ($pricemin)
	{
	if($b>0)
	$sel_gpu.=" AND ";
	$b=1;
	$sel_gpu.="(price+price*err)>=";
	$sel_gpu.=$pricemin;
	
	}

 
	if($pricemax)
	{
	if($b>0)
	$sel_gpu.=" AND ";
	$b=1;
		$sel_gpu.="(price-price*err)<=";
		$sel_gpu.=$pricemax;
	}
	
	
	
// DO THE SEARCH
	
//	echo $sel_gpu;	

	$result = mysqli_query($GLOBALS['con'], "$sel_gpu");
	
	$gpu_return = array();
	while($rand = mysqli_fetch_array($result)) 
	{ 

		if($seltdp>0)
		$gpu_return[]=["id"=>intval($rand[0]), "model"=>strval($rand[1])];		
		else
		$gpu_return[]=["id"=>intval($rand[0]), "model"=>strval($rand[1])];	

	}
		mysqli_free_result($result);

		return($gpu_return);
}

/**********COMMENT/DELETE IF YOU INCLUDE******/
/*
//$gpu_typegpu=0;
$gpu_prod[]="NVIDIA";
$gpu_prod[]="AMD";
$gpu_model[]="firepro25";
$gpu_model[]="nothing";
$gpu_arch[]="kepler";
$gpu_techmin=14;
$gpu_techmax=28;
$gpu_shadermodelmin=3.0;
$gpu_cspeedmin=3;
$gpu_cspeedmax=1000;
$gpu_sspeedmin=1000;
$gpu_sspeedmax=1900;
$gpu_mbw=64;
$gpu_mtype[]="GDDR3";
$gpu_mtype[]="GDDR5";
$gpu_memsizemin=1000;
$gpu_tdpmin=11;


//COPY this anywhere exactly like it is below. Before you call the function, give values to some of the variables, none of the variables need to have values
$gpu_list=search_gpu ($gpu_typegpumin, $gpu_typegpumax, $gpu_prod, $gpu_model, $gpu_arch, $gpu_techmin, $gpu_techmax, $gpu_shadermin, $gpu_cspeedmin, $gpu_cspeedmax, $gpu_sspeedmin, $gpu_sspeedmax, $gpu_mspeedmin, $gpu_mspeedmax, $gpu_mbwmin, $gpu_mbwmax, $gpu_mtype, $gpu_maxmemmin, $gpu_maxmemmax, $gpu_sharem, $gpu_powermin, $gpu_powermax, $gpu_misc, $gpu_ratemin, $gpu_ratemax, $pricemin,$pricemax, $seltdp);


foreach($gpu_list as $x)
{


echo "<br>".$x["price"];
echo " ".$x["rating"];

}
*/
/********** END OF COMMENT/DELETE IF YOU INCLUDE******/
?>