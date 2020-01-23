<?php

require_once("../etc/con_db.php");
mysqli_select_db($con,"notebro_rate");

/* CPU RATING */
$sql="SELECT * FROM CPU";
$sql2="SELECT MAX(`Cinebench R15 CPU Single 64Bit`), MAX(`Cinebench R15 CPU Multi 64Bit`), MIN(NULLIF(`SuperPI 32M`,0)), MAX(`PassMark`), MAX(`Geekbench 4 64bit Single-Core`),MAX(`Geekbench 4 64bit Multi-Core`),MAX(`3DMark - Fire Strike Phyics`) FROM CPU;";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);
$result = mysqli_query($con, $sql2);
$maxvalues = mysqli_fetch_array($result);

for($i=0;$i<$rownr;$i++)
{
	$rating=array(); $nr=0; $value=0;
	
	for($j=0;$j<7;$j++)
	{
		$stuff[$i][$j+4]=floatval($stuff[$i][$j+4]);
		if($stuff[$i][$j+4]>0)
		{
			if($j==2){ $value+=floatval($maxvalues[$j])/$stuff[$i][$j+4]; }
			else
			{ $value+=$stuff[$i][$j+4]/floatval($maxvalues[$j]); }

			$nr++;
		}
	}
	
	if($nr){$value/=$nr;}else{$value=0;}
	
	$rating[$stuff[$i][0]]=$value; $stuff[$i][3]=floatval($stuff[$i][3]);
	if($stuff[$i][3]){$value2=($value+((35/$stuff[$i][3])-1)/300)*100;} else { $value2=($value+(0-1)/300)*100;}
	
	$value*=100;
	//echo "<br>";
	//var_dump($rating);
	//$sql="UPDATE CPU SET ratingnew=$value, `rating+tdp`=$value2 WHERE id=".$stuff[$i][0];
	mysqli_select_db($con,"notebro_db"); 
	$sql="UPDATE notebro_db.CPU SET rating='$value' WHERE id='".$stuff[$i][0]."';";
	//echo $sql; echo "<br>";
	mysqli_query($con, $sql);
	$sql="UPDATE notebro_rate.CPU SET ratingnew=$value WHERE id=".$stuff[$i][0];
	//echo $sql; echo "<br>";
	mysqli_query($con, $sql);
}
$sql="SELECT MAX(rating) FROM CPU"; $result = mysqli_query($con, $sql); $stuff = mysqli_fetch_row($result);
$sql="UPDATE notebro_db.CPU SET rating=(rating/".floatval($stuff[0]).")*100"; mysqli_query($con, $sql);


/* GPU RATING */

$sql="SELECT * FROM notebro_rate.GPU";
$sql2="SELECT MAX(`3DMark 2013 Fire Strike Standard GPU`), MAX(`3DMark Time Spy Graphics`),MAX(`3DMark Cloud Gate Graphics Standard`) FROM notebro_rate.GPU WHERE model NOT LIKE '%SLI%' AND model NOT LIKE '%Crossfire%'";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);
$result = mysqli_query($con, $sql2);
$maxvalues = mysqli_fetch_array($result);

for($i=0;$i<$rownr;$i++)
{
$rating=array();
$nr=0;
$value=0;
$first=2;
for($j=0;$j<3;$j++)
{
//j+4 because first 4 columns are not data
if($stuff[$i][$j+4]>0)
{
		$stuff[$i][$j+4]=floatval($stuff[$i][$j+4]);
		//echo $stuff[$i][$j+3]; echo " "; echo $maxvalues[$j]; echo "aa";
		$value+=$stuff[$i][$j+4]/floatval($maxvalues[$j]);
		$nr++;
}

if($first)
{
if($stuff[$i][$j+4]>0)
{
		$stuff[$i][$j+4]=floatval($stuff[$i][$j+4]);
		//echo $stuff[$i][$j+4]; echo " "; echo $maxvalues[$j]; echo "aa";
		$value+=$stuff[$i][$j+4]/floatval($maxvalues[$j]);
		$nr++;
}	
$first--;	
}


//echo "-".$value."-".$nr."b";
}

if($nr){$value/=$nr;}else{$value=0;}
	
$rating[$stuff[$i][0]]=$value;
$stuff[$i][3]=floatval($stuff[$i][3]);
//$value2=($value+((35/$stuff[$i][3])-1)/300)*100; - for TDP
$value*=100;
$value=round($value,4);
//echo "<br>";
//var_dump($rating);
$sql="UPDATE notebro_rate.GPU SET ratingnew=$value WHERE id=".$stuff[$i][0];
mysqli_query($con, $sql);
mysqli_select_db($con,"notebro_db"); 
$sql="UPDATE notebro_db.GPU SET rating=$value WHERE id=".$stuff[$i][0]."";
//if($stuff[$i][0]==503){var_dump($sql);}
mysqli_query($con, $sql);
}




/* HDD RATING */
mysqli_select_db($con,"notebro_db");

$sql="SELECT * FROM HDD WHERE model NOT LIKE '%N/A%'";
$sql2="SELECT MAX(`cap`), MAX(`readspeed`), MAX(`writes`), MIN(`cap`), MIN(`readspeed`), MIN(`writes`) FROM HDD WHERE model NOT LIKE '%N/A%'";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);
$result = mysqli_query($con, $sql2);
$maxminvalues = mysqli_fetch_array($result);
echo $maxminvalues[5]." ".$maxminvalues[2]." ".$maxminvalues[5]."<br>";
$value=array();
//var_dump($maxminvalues);
for($i=0;$i<$rownr;$i++)
{
	$y1 = ($stuff[$i][2] - $maxminvalues[3]) / ($maxminvalues[0] - $maxminvalues[3]);
	$y2 = ($stuff[$i][4] - $maxminvalues[4]) / ($maxminvalues[1] - $maxminvalues[4]);
	$y3 = ($stuff[$i][5] - $maxminvalues[5]) / ($maxminvalues[2] - $maxminvalues[5]);
   $value[$i] = 0.1055 +
		0.0000 * 1 +
		1.3442 * $y1 +
		1.0524 * $y2 +
		0.5089 * $y3 +
		-0.8364 * $y1 ** 2 +
		0.6889 * $y1 * $y2 +
		0.3242 * $y1 * $y3 +
		-1.1796 * $y2 ** 2 +
		-0.5726 * $y2 * $y3 +
		0.1091 * $y3 ** 2;
	$value[$i]*=100;
}

//$minvalue=min($value);
$maxvalue=max($value);

for($i=0;$i<$rownr;$i++)
{
	$value[$i]=($value[$i]/$maxvalue)*100;

if($value[$i]<=0)
{$value[$i]=0.0001;}

//echo "<br>";
//var_dump($value);
$sql="UPDATE HDD SET rating=$value[$i] WHERE id=".$stuff[$i][0];
mysqli_query($con, $sql);
}


/* RAM MEM RATING */
mysqli_select_db($con,"notebro_db");

$sql="SELECT * FROM MEM";
$sql2="SELECT MAX(`cap`), MAX(`freq`), MAX(`lat`/`freq`), MAX(`volt`), MIN(`cap`), MIN(`freq`), MIN(`lat`/`freq`),MIN(`volt`) FROM MEM";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);
$result = mysqli_query($con, $sql2);
$maxminvalues = mysqli_fetch_array($result);

$normalize_cap=$maxminvalues[0]/sqrt($maxminvalues[0]+(pow($maxminvalues[0],2)));
$normalize_speed=($maxminvalues[2])-($maxminvalues[6]);
$normalize_voltage=($maxminvalues[3])-($maxminvalues[7]);
$normalize_freq=($maxminvalues[1])-($maxminvalues[5]);

//var_dump($maxminvalues);
for($i=0;$i<$rownr;$i++)
{

$value=((round((($stuff[$i][2]/sqrt($maxminvalues[0]+pow($stuff[$i][2],2)))/$normalize_cap),2)+0.00001)*0.79+(round(((($maxminvalues[2])-($stuff[$i][6]/$stuff[$i][4]))/$normalize_speed),2)+0.000001)*0.18+((($maxminvalues[3]-$stuff[$i][7])/$normalize_voltage)+0.000001)*0.02+((($stuff[$i][4]-$maxminvalues[5])/$normalize_freq)*0.01)+0.00001)*100;

//echo "<br>";
//var_dump((($stuff[$i][6]/$stuff[$i][4])-$maxminvalues[6])/$normalize_speed);
//var_dump((1/(($stuff[$i][6]/$stuff[$i][4])/($maxminvalues[6]))));
//var_dump($value);
$sql="UPDATE MEM SET rating=$value WHERE id=".$stuff[$i][0];
mysqli_query($con, $sql);
}



/* Display RATING */
mysqli_select_db($con,"notebro_db");
$stuff=array();
$sql="SELECT * FROM DISPLAY";
$sql2="SELECT MAX(`hres`*`vres`) AS `maxresol`, MAX(`size`) as `maxsize`, MIN(`hres`*`vres`) AS `minresol`, MIN(`size`) as `minsize`,MIN(`lum`) as `minlum`,MAX(`lum`) as `maxlum`,MIN(`hdr`) as `minhdr`,MAX(`hdr`) as `maxhdr`,MIN(`hz`) as `minhz`,MAX(`hz`) as `maxhz` FROM DISPLAY";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result,MYSQLI_ASSOC);
$rownr=mysqli_num_rows($result);
$result = mysqli_query($con, $sql2);
$maxminvalues = mysqli_fetch_array($result);

//var_dump($maxminvalues);
$new_stuff=array();
for($i=0;$i<$rownr;$i++)
{
	
//$resrating=($stuff[5]*$stuff[6]-$maxminvalues[2])/($maxminvalues[0]-$maxminvalues[2]+0.000001);
$curres=$stuff[$i]['hres']*$stuff[$i]['vres'];
$resrating=$curres/$maxminvalues['maxresol'];
$sizerating=$stuff[$i]['size']/$maxminvalues['maxsize'];
$ratioparts=explode(":",$stuff[$i]['format']);
$ratiorating= 1-abs(($ratioparts[0]/$ratioparts[1])-1.6);

switch($stuff[$i]['surft']) {
case "Glossy":
$surfaceratio=0.90;
break;
case "Matte":
$surfaceratio=1;
break;
}

switch($stuff[$i]['backt']) {
	case (stripos($stuff[$i]['backt'],"IPS PenTile")!==FALSE):
	{ $surfacetype=0.6; break; }
	case (stripos($stuff[$i]['backt'],"TN WVA")!==FALSE):
	{ $surfacetype=0.5; break; }
	case (stripos($stuff[$i]['backt'],"IPS")!==FALSE):
	{ $surfacetype=0.7; break; }
	case (stripos($stuff[$i]['backt'],"TN")!==FALSE):
	{ $surfacetype=0.35; break;}
	case (stripos($stuff[$i]['backt'],"OLED")!==FALSE):
	{ $surfacetype=1; break; }
}

//sRGB minimum srgb is 50, set it to 45 in range
if(isset($stuff[$i]['sRGB'])&&(intval($stuff[$i]['sRGB'])==0))
{ $stuff[$i]['sRGB']=50;}
$srgbrating=normalisation($stuff[$i]['sRGB'],45,100);

//brightness minimum is 200, set it to 190 in range
if(isset($stuff[$i]['lum'])&&(intval($stuff[$i]['lum'])==0))
{ $stuff[$i]['lum']=250;}
if($maxminvalues['maxlum']>600){$maxminvalues['maxlum']=600+($maxminvalues['maxlum']/600);}
if($stuff[$i]['lum']>600){$stuff[$i]['lum']=600+($stuff[$i]['lum']/600);}
$lumrating=normalisation($stuff[$i]['lum'],190,$maxminvalues['maxlum']);

//hz
if(isset($stuff[$i]['hz'])&&(intval($stuff[$i]['hz'])==0))
{ $stuff[$i]['hz']=60;}
$hzrating=normalisation($stuff[$i]['hz'],60,$maxminvalues['maxhz']);

//hdr
if(isset($stuff[$i]['hdr'])&&(intval($stuff[$i]['hdr'])==0))
{ $stuff[$i]['hdr']=0;}
if(intval($maxminvalues['maxhdr'])>0)
{ $hdrrating=normalisation($stuff[$i]['hdr'],0,$maxminvalues['maxhdr']); }
else
{ $hdrrating=0; }

$new_stuff[$i]=array();
$new_stuff[$i]=explode(",",$stuff[$i]['msc']);

$surfacetype*=(1+pow($hzrating,0.45));
if($hdrrating>0){ $surfacetype*=1.35;}
if($hdrrating==0)
{
	foreach($new_stuff[$i] as $el)
	{ if(stripos($el,"HDR")!==FALSE){ $surfacetype*=1.35;} }
}

if($stuff[$i]['touch']==1) { $touchratio=1; }
elseif($stuff[$i]['touch']==2) {	$touchratio=0.001;}

$value=(0.39*$resrating+0.14*$surfacetype+0.19*$sizerating+0.10*$touchratio+0.05*$surfaceratio+0.05*$ratiorating+0.05*$srgbrating+0.03*$lumrating)*100;
//if($lumrating>1){ var_dump($lumrating); var_dump($stuff[$i]['id']);} echo "<br>";
//var_dump(pow($hzrating,0.45)); echo "<br>";
//echo "<br>";
$sql="UPDATE DISPLAY SET rating=$value WHERE id=".$stuff[$i]['id'];
mysqli_query($con, $sql);
}


/* Warranty RATING */
mysqli_select_db($con,"notebro_db");

$sql="SELECT * FROM WAR";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);


//var_dump($maxminvalues);
for($i=0;$i<$rownr;$i++)
{
	
switch($stuff[$i][1])
{
	case "Standard":
	$bwarrating=11;
	break;
	case "On-Site":
	$bwarrating=14;
	break;
	case "ADP":
	$bwarrating=17;
	break;
	case "On-Site + ADP":
	$bwarrating=20;
	break;
}

$value=$bwarrating*$stuff[$i][2];

//echo "<br>";
//var_dump($stuff[$i][1]);
//var_dump($value);

$sql="UPDATE WAR SET rating=$value WHERE id=".$stuff[$i][0];
mysqli_query($con, $sql);
}



/* Battery RATING */
mysqli_select_db($con,"notebro_db");
$whr_price=0.35; //This is the average-low current price for 1 Whr for Li-Ion batteries 
$sql="SELECT * FROM ACUM";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);
$sql2="SELECT MAX(`cap`) AS maxcap, MIN(`cap`) AS mincap FROM ACUM";
$result = mysqli_query($con, $sql2);
$maxminvalues = mysqli_fetch_array($result);

for($i=0;$i<$rownr;$i++)
{
//var_dump($maxminvalues);

//$value=($stuff[$i][5]-$maxminvalues[1])/($maxminvalues[0]-$maxminvalues[1]);

$value=($stuff[$i][5]/$maxminvalues[0]);
$type_price=0;
if(strcasecmp($stuff[$i][2],"li-ion")==0){$type_price=1;}
if(strcasecmp($stuff[$i][2],"li-pol")==0){$type_price=1.2;}
$value_price=($whr_price*$stuff[$i][5]*$type_price+10);

//echo "<br>";
//var_dump($stuff[$i][1]);
//var_dump($value);
$value*=100;

$sql="UPDATE ACUM SET price=$value_price, err=3, rating=$value WHERE id=".$stuff[$i][0];
mysqli_query($con, $sql);

}


/* Wireless RATING */
mysqli_select_db($con,"notebro_db");
$sql="SELECT * FROM WNET";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);
$sql2="SELECT MAX(`speed`) AS maxspeed, MIN(`speed`) AS minspeed FROM WNET";
$result = mysqli_query($con, $sql2);
$maxminvalues = mysqli_fetch_array($result);

for($i=0;$i<$rownr;$i++)
{
//var_dump($maxminvalues);

//$value=($stuff[$i][5]-$maxminvalues[1])/($maxminvalues[0]-$maxminvalues[1]);

$value=($stuff[$i][4]/$maxminvalues[0])*0.7;

if(stripos($stuff[$i][7],"bluetooth 4")!==false)
{
	$value+=1*0.3;
}
$value*=100;
//echo "<br>";
//var_dump($stuff[$i][1]);
//var_dump($value);

$sql="UPDATE WNET SET rating=$value WHERE id=".$stuff[$i][0];
mysqli_query($con, $sql);

}


/* MDB RATING */
mysqli_select_db($con,"notebro_db");
$sql="SELECT * FROM `MDB`";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result);
$rownr=mysqli_num_rows($result);
$sql2="SELECT id,prod,failrate FROM notebro_db.FAMILIES";
$result = mysqli_query($con, $sql2);
$stuff2 = mysqli_fetch_all($result); $relia=array();
foreach($stuff2 as $val) { $relia[$val[0]]=$val[2]; }

$maxiface=0; $maxrealtek=0; $maxconexant=0; $minrealtek=999999; $minconexant=999999; $maxfam=0; $minfam=9999;

for($i=0;$i<$rownr;$i++)
{

$id=intval($stuff[$i][0]);

if((stripos($stuff[$i][7],"LGA")!==FALSE) || (stripos($stuff[$i][7],"PGA")!==FALSE))
{ $socket[$id]=1; } else { $socket[$id]=0; }

		switch ($stuff[$i][5])
		{
			case "1":
				$gpu[$id] = 0.1;
				break;
			case "2":
				$gpu[$id] = 0.7;
				break;
			case "3":
				$gpu[$id] = 1;
				break;
		}

//evaluating internal interfaces
preg_match_all('/(\d) [xX]/', $stuff[$i][8], $parts);
$iface[$id]=0;
foreach($parts[1] as $x)
{
	$x=intval($x);
	$iface[$id]+=$x;
	
	if($maxiface<$iface[$id])
	$maxiface=$iface[$id];
}

//evaluating network card
preg_match_all('/(10\/100\/1000)|(10\/100)|(1000\/2500)|(2500\/5000)/', $stuff[$i][9], $parts);

foreach($parts[0] as $x)
{
	switch(true)
	{
		case (stripos($x,"/5000")!==FALSE):
		{ $net[$id]=1; break; }
		case (stripos($x,"/2500")!==FALSE):
		{ $net[$id]=0.85; break; }
		case (stripos($x,"10/100/1000")!==FALSE):
		{ $net[$id]=0.75; break; }
		case (stripos($x,"10/100")!==FALSE):
		{ $net[$id]=0.5; break; }
		default:
		{ $net[$id]=0; break;}
	}
}

//echo $id." ".$net[$id]."<br>";

//evaluating hdd interfaces
preg_match_all('/(\d) [xX]/', $stuff[$i][10], $parts);
$hface[$id]=0;
foreach($parts[1] as $x)
{
	$x=intval($x);
	$hface[$id]+=$x;
	
	if($maxiface<$hface[$id])
	$maxiface=$hface[$id];
}

//evaluating msc
$mmsc[$id]=0;
	if(stripos( $stuff[$i][12],"WWAN")!==FALSE)
	{
	
		$mmsc[$id]+=0.3;	
		
	}
	
	if(stripos( $stuff[$i][12],"G-sync")!==FALSE)
	{
	
		$mmsc[$id]+=0.2;	
		
	}
	
	
		if(stripos( $stuff[$i][12],"Freesync")!==FALSE)
	{
	
		$mmsc[$id]+=0.2;	
		
	}
	
	if(stripos($stuff[$i][12],"Realtek")!==FALSE)
	{
		
		$stuff[$i][12]=str_replace("ALC255","ALC3234",$stuff[$i][12]);
		preg_match_all('/Realtek ALC(\d*)/i', $stuff[$i][12], $parts);
		if(isset($parts[1][0]) && $parts[1][0])
		{
		if(intval($parts[1][0])/1000<1) { $parts[1][0]=4000+intval($parts[1][0]); }
		$mmsound[$id][0]="Realtek";
		$mmsound[$id][1]=intval($parts[1][0]);
		
		if($maxrealtek<$mmsound[$id][1])
		{$maxrealtek=$mmsound[$id][1];}
	
		if($minrealtek>$mmsound[$id][1])
		{$minrealtek=$mmsound[$id][1];}
		
		}
		else
		{
		$mmsound[$id][0]="Realtek";
		$mmsound[$id][1]=0;
		}
	}
	elseif(stripos($stuff[$i][12],"Conexant")!==FALSE)
	{
		if(isset($parts[1][0]) && $parts[1][0])
		{
		preg_match_all('/Conexant CX(\d*)/i', $stuff[$i][12], $parts);	
		$mmsound[$id][0]="Conexant";
		if(isset($parts[1][0])){$mmsound[$id][1]=intval($parts[1][0]);}
		if(!isset($mmsound[$id][1])){$mmsound[$id][1]=0;}
		if($maxconexant<$mmsound[$id][1])
		{$maxconexant=$mmsound[$id][1];}
		if($minconexant>$mmsound[$id][1])
		{$minconexant=$mmsound[$id][1];}		
		}
		else
		{
		$mmsound[$id][0]="Conexant";
		$mmsound[$id][1]=0;
		}
	}
	elseif(stripos($stuff[$i][12],"Creative")!==FALSE)
	{
		$mmsound[$id][0]="Creative";
		$mmsound[$id][1]=0.4;
	}
	elseif(stripos($stuff[$i][12],"HD Audio")!==FALSE)
	{
		$mmsound[$id][0]="HD";
		$mmsound[$id][1]=0.25;
		
	}
	elseif(stripos($stuff[$i][12],"Cirrus")!==FALSE)
	{
		$mmsound[$id][0]="Cirrus";
		$mmsound[$id][1]=0.3;
		
	}
	else
	{
		$mmsound[$id][0]="NA";
		$mmsound[$id][1]=0;
	}
	
// Geting realibility 

	$sql3="SELECT idfam from notebro_db.MODEL WHERE FIND_IN_SET ($id,`mdb`)>0";
	$result3=mysqli_query($con,$sql3); $idfam=mysqli_fetch_row($result3);
	
	//var_dump($idfam[0]);
	if(!isset($idfam[0]) || $idfam[0]==NULL || intval($relia[$idfam[0]])==0) { $mmfam[$id]=floatval(18); } else { $mmfam[$id]=floatval($relia[$idfam[0]]); }

	if($maxfam<$mmfam[$id])
	{$maxfam=$mmfam[$id];}

	if($minfam>$mmfam[$id])
	{$minfam=$mmfam[$id];}
}

//normalizations
	for($i=0;$i<$rownr;$i++)
	{
	$id=$stuff[$i][0];
	$iface[$id]=$iface[$id]/$maxiface;
	$hface[$id]=$hface[$id]/$maxiface;
	
	if($mmsound[$id][1]>0)
	{ 	
		if($mmsound[$id][0]=="Creative")
		{ $mmsc[$id]+=$mmsound[$id][1]; }

		if($mmsound[$id][0]=="Cirrus")
		{ $mmsc[$id]+=$mmsound[$id][1]; }

		if($mmsound[$id][0]=="Realtek")
		{  $mmsc[$id]+=sqrt($mmsound[$id][1]/$maxrealtek)*0.25; }

		if($mmsound[$id][0]=="Conexant")
		{ $mmsc[$id]+=sqrt($mmsound[$id][1]/$maxconexant)*0.30; }
	}
	
	if($mmsound[$id][1]==0)
	{ 	
		if($mmsound[$id][0]=="Realtek")
		$mmsc[$id]+=sqrt($minrealtek/$maxrealtek)*0.25;

		if($mmsound[$id][0]=="Conexant")
		$mmsc[$id]+=sqrt($minconexant/$maxconexant)*0.30;

		if($mmsound[$id][0]=="NA")
		{ $mmsc[$id]+=sqrt(($minrealtek+$maxrealtek)/(2*$maxrealtek))*0.25; }
	
		if($mmsound[$id][0]=="HD")
		{ $mmsc[$id]+=sqrt(($minrealtek+$maxrealtek)/(2*$maxrealtek))*0.28; }
	}
	if(!isset($net[$id])){$net[$id]=0;}
//	if($id==633 || $id==24) {  echo $minrealtek; var_dump($mmsc[$id]); }
//if($id==633) { var_dump($id); var_dump($iface[$id]); var_dump($hface[$id]); var_dump($net[633]); var_dump($mmsc[633]); var_dump($mmfam[$id]); var_dump($socket[$id]); var_dump($gpu[$id]); echo "<br>"; }
//if($id==24) { var_dump($id); var_dump($iface[$id]); var_dump($hface[$id]); var_dump($net[24]); var_dump($mmsc[24]); var_dump($mmfam[$id]); var_dump($socket[$id]); var_dump($gpu[$id]); echo "<br>"; }
$value=$iface[$id]*0.1+$hface[$id]*0.11+$net[$id]*0.09+$mmsc[$id]*0.20+(($minfam*$minfam)/($mmfam[$id]*$mmfam[$id]))*0.5+$socket[$id]*0.15+$gpu[$id]*0.05;
$value*=100;

//echo $id." ".$mmsc[$id]." ". $minprod/$mmprod[$id]."<br>";

	$sql="UPDATE MDB SET rating=$value WHERE id=".$id;
	mysqli_query($con, $sql);

	}


/* CHASSIS RATING */
mysqli_select_db($con,"notebro_db");
$sql="SELECT * FROM `CHASSIS`";
$result = mysqli_query($con, $sql);
$stuff = mysqli_fetch_all($result,MYSQLI_ASSOC);
$rownr=mysqli_num_rows($result);
$sql2="SELECT MIN(thic),MIN(depth),MIN(width),MIN(weight),MAX(web) FROM `CHASSIS`";
$result = mysqli_query($con, $sql2);
$stuff2 = mysqli_fetch_all($result);
$maxpi=0; $maxvi=0; $mscmax=0;
$vi=array(); $pi=array(); $web=array(); $made=array(); $key=array(); $rthic=array(); $rdepth=array(); $rwidth=array(); $rweight=array(); $rmsc=array();
for($i=0;$i<$rownr;$i++)
{

$id=$stuff[$i]["id"];	
$rthic[$id]=$stuff2[0][0]/$stuff[$i]["thic"];
$rdepth[$id]=$stuff2[0][1]/$stuff[$i]["depth"];
$rwidth[$id]=$stuff2[0][2]/$stuff[$i]["width"];
$rweight[$id]=$stuff2[0][3]/$stuff[$i]["weight"];

$made[$id]=0;
$numberofmat=substr_count($stuff[$i]["made"] , ",");
//echo var_dump($stuff[$i])." ";
if((stripos($stuff[$i]["made"],"plastic")!==FALSE)&&!(stripos($stuff[$i]["made"],"hard plastic")!==FALSE))
{
	if($numberofmat>0)
	{  $made[$id]+=0.5; }
}
else
{
	$made[$id]+=1;
}

$s_made[$id]=0;
$numberofmat=substr_count($stuff[$i]["s_made"] , ",");
if((stripos($stuff[$i]["s_made"],"plastic")!==FALSE)&&!(stripos($stuff[$i]["s_made"],"hard plastic")!==FALSE))
{
	if($numberofmat>0)
	{  $s_made[$id]+=0.5; }
}
else
{
	$s_made[$id]+=1;
}

$pi[$id]=substr_count($stuff[$i]["pi"] , ",");
$pi[$id]++;
preg_match_all('/(\d) [xX]/', $stuff[$i]["pi"], $parts);
foreach($parts[1] as $x)
{
	$x=intval($x);
	$pi[$id]+=$x;
	$pi[$id]--;

}


if(stripos($stuff[$i]["pi"],"LAN")!==FALSE)
{
	$pi[$id]--;
}

if(stripos($stuff[$i]["pi"],"Thunderbolt")!==FALSE)
{
	$pi[$id]+=2;
}

if(stripos($stuff[$i]["pi"],"card reader")!==FALSE)
{
	$pi[$id]+=1;
}

if($maxpi<$pi[$id])
$maxpi=$pi[$id];




$vi[$id]=substr_count($stuff[$i]["vi"] , ",");
$vi[$id]++;
preg_match_all('/(\d) [xX]/', $stuff[$i]["vi"], $parts);
foreach($parts[1] as $x)
{
	$x=intval($x);
	$vi[$id]+=$x;
	$vi[$id]--;

}

if($maxvi<$vi[$id])
$maxvi=$vi[$id];

$web[$id]=floatval($stuff[$i]["web"])/floatval($stuff2[0][4]);
//13 web


$touch[$id]=1;
//14 for touch

$key[$id]=0;

if(stripos($stuff[$i]["keyboard"],"spill")!==FALSE)
{
	$key[$id]+=0.5;
}

if(stripos($stuff[$i]["keyboard"],"backlit")!==FALSE)
{
	$key[$id]+=0.3;
}

if(stripos($stuff[$i]["keyboard"],"rgb led")!==FALSE)
{
	$key[$id]+=0.2;
}

$rmsc[$id]=substr_count($stuff[$i]["msc"] , ",");
$rmsc[$id]++;
preg_match_all('/(\d) [xX]/', $stuff[$i]["msc"], $parts);

if(stripos($stuff[$i]["msc"],"fingerprint")!==FALSE)
{

	$rmsc[$id]+=3;
	$rmsc[$id]--;
}

if((stripos($stuff[$i]["msc"],"omnisonic")!==FALSE)||(stripos($stuff[$i]["msc"],"jbl")!==FALSE)||(stripos($stuff[$i]["msc"],"klipsch")!==FALSE)||(stripos($stuff[$i]["msc"],"onkyo")!==FALSE)||(stripos($stuff[$i]["msc"],"akg")!==FALSE)||(stripos($stuff[$i]["msc"],"harman")!==FALSE)||(stripos($stuff[$i]["msc"],"olufsen")!==FALSE)||(stripos($stuff[$i]["msc"],"altec")!==FALSE)||(stripos($stuff[$i]["msc"],"sonicmaster")!==FALSE)||(stripos($stuff[$i]["msc"],"dynaudio")!==FALSE))
{
	$rmsc[$id]+=2;
	$rmsc[$id]--;
}

if(stripos($stuff[$i]["msc"],"rear camera")!==FALSE)
{
	$rmsc[$id]+=2;
	$rmsc[$id]--;
}

if(stripos($stuff[$i]["msc"],"GPS")!==FALSE)
{
	$rmsc[$id]+=2;
	$rmsc[$id]--;
}

if($mscmax<$rmsc[$id]) { $mscmax=$rmsc[$id];  }

//echo $id." ".$mscmax." ".$rmsc[$id]."<br>";

}


//normalizations and calculations for chassis, final insertion 
for($i=0;$i<$rownr;$i++)
{
	$id=$stuff[$i]["id"];

	/*
	if($id==1134 || $id==1498)
	{ echo $id." ".$rthic[$id]." ".$rdepth[$id]." ".$rwidth[$id]." ".$rweight[$id]." ".$made[$id]." ".$pi[$id]." ".$vi[$id]." ".$web[$id]." ".$touch[$id]." ".$key[$id]." ".$rmsc[$id]."<br>";
	 echo $id." ".$rthic[$id]." ".$rdepth[$id]." ".$rwidth[$id]." ".$rweight[$id]." ".$made[$id]." ".($pi[$id]/$maxpi)." ".($vi[$id]/$maxvi)." ".$web[$id]." ".$touch[$id]." ".$key[$id]." ".($rmsc[$id]/$mscmax)."<br>";
	echo $rthic[$id]*0.09+$rdepth[$id]*0.04+$rwidth[$id]*0.04+$rweight[$id]*0.18+$made[$id]*0.13+$s_made[$id]*0.02+$pi[$id]*0.15+$vi[$id]*0.1+$web[$id]*0.04+$touch[$id]*0.01+$key[$id]*0.05+$rmsc[$id]*0.15;
	echo "<br>";
	 }
	*/
	
	$value=$rthic[$id]*0.09+$rdepth[$id]*0.04+$rwidth[$id]*0.04+$rweight[$id]*0.18+$made[$id]*0.15+($pi[$id]/$maxpi)*0.15+($vi[$id]/$maxvi)*0.1+$web[$id]*0.04+$touch[$id]*0.01+$key[$id]*0.05+($rmsc[$id]/$mscmax)*0.15;
	$value*=100;
	//echo $id." ".$mmsc[$id]." ". $minprod/$mmprod[$id]."<br>";

	$sql="UPDATE CHASSIS SET rating=$value WHERE id=".$id;
	mysqli_query($con, $sql);

}




?>