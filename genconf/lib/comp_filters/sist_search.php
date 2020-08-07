<?php

/* ********* SELECT OS BASED ON FILTERS ***** */

function search_sist ($sist, $vers, $misc, $pricemin, $pricemax)
{
	$sel_sist="SELECT id,price,err,rating FROM notebro_db.SIST WHERE 1=1";
	
	// Add models to filter
	$i=0;
	if(gettype($sist)!="array") { $sist=(array)$sist; }
	foreach($sist as $x)
	{
		if((stripos($x,"+")!==FALSE))
		{
			$newx=explode("+",$x);
			if($i)
			{  
				$sel_sist.=") OR (";
			}
			else
			{
				$sel_sist.=" AND ( ";
			}
		
			$sel_sist.="sist='";
			$sel_sist.=$newx[0];
			$sel_sist.="'";
			$sel_sist.=" AND ";
			$sel_sist.="type='";
			$sel_sist.=$newx[1];
			$sel_sist.="'";
			
			$i++;
		}
		else
		{
			if($i)
			{  
				$sel_sist.=") OR (";
			}
			else
			{
				$sel_sist.=" AND ( ";
			}
	
			$sel_sist.="sist='";
			$sel_sist.=$x;
			$sel_sist.="'";
			$i++;
		}
	}
	if($i>0)
	{ $sel_sist.=" ) "; }

	// Add type to filter	
	$i=0;
	if(gettype($vers)!="array") { $vers=(array)$vers; }
	foreach($vers as $x)
	{
		if($i)
		{  
			$sel_sist.=" OR ";
		}
		else
		{
			$sel_sist.=" AND ( ";
		}
		
		$sel_sist.="vers='";
		$sel_sist.=$x;
		$sel_sist.="'";
		$i++;
	}

	if($i>0)
		$sel_sist.=" ) ";
	
	// Add MISC to filter
	$i=0;
	if(gettype($misc)!="array") { $misc=(array)$misc; }
	foreach($misc as $x)
	{
		if($i)
		{  
			$sel_sist.=" AND ";
		}
		else
		{
			$sel_sist.=" AND ( ";
		}
		
		$sel_sist.="FIND_IN_SET('";
		$sel_sist.=$x;
		$sel_sist.="',msc)>0";
		$i++;
	}
	if($i>0)
	{ $sel_sist.=" ) ";	}

	// Add price to filter
	if($pricemin)
	{
		$sel_sist.=" AND ";
		$sel_sist.="price>=";
		$sel_sist.=$pricemin;
	}
 
	if($pricemax)
	{
		$sel_sist.=" AND ";
		$sel_sist.="price<=";
		$sel_sist.=$pricemax;
	}
	
	// DO THE SEARCH
	# echo "Query to select the OSes:";
    # echo "<br>";
	# echo "<pre>" . $sel_sist . "</pre>";
	
	$result = mysqli_query($GLOBALS['con'], $sel_sist);
	$sist_return = array();
	#var_dump($result); echo "<br>";
	while($rand = mysqli_fetch_array($result)) 
	{ 
		$sist_return[intval($rand[0])]=array("price"=>round(($rand[1]),2),"err"=>intval($rand[2]),"rating"=>round($rand[3],3));
	}
	
	mysqli_free_result($result);
	return($sist_return);
}
?>