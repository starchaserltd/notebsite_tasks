<?php

$wp_base_site="http://34.194.182.255/vault/wp";
$new_base_site="http://noteb.com";
$wp_site="http://34.194.182.255/vault/wp/article.php";
$new_site="http://noteb.com/?content/article.php?";
$wp_imgsite="http://34.194.182.255/vault/wp/wp-content/uploads";
$wp_imgsite2="http://notebro.intexim.ro/admin/wp/wp-content/uploads";
$new_imgsite="http://noteb.com/uploads";
$dom=new DOMDocument();
$dom->formatOutput = true;
$dom->load("../wp/sitemap.xml");
$root=$dom->documentElement; // This can differ (I am not sure, it can be only documentElement or documentElement->firstChild or only firstChild)
$elements=$root->getElementsByTagName('url');

foreach ($elements as $element) 
{
    $loc=$element->getElementsByTagName('loc')->item(0)->textContent;
	$newlink=str_replace($wp_site,$new_site,$loc);
	$newlink=str_replace("article.php?/review/","review.php?/",$newlink);
	$newlink=str_replace("/article/","/",$newlink);
	$element->getElementsByTagName('loc')->item(0)->textContent=str_replace($wp_site,$new_site,$newlink);

	if($element->nodeName=="url" && (stripos(($element->nodeValue),"post-page")!==FALSE || (($element->getElementsByTagName('loc')->item(0)->textContent)=="http://34.194.182.255/vault/wp/"))) { $element->getElementsByTagName('loc')->item(0)->textContent=$new_base_site;  }
	//var_dump( $element->nodeValue); echo "<br>";
	if($image=$element->getElementsByTagName('image'));
	{
		foreach ($image as $el)
		{

			$subel=explode("\n",str_replace($wp_imgsite,$new_imgsite,$el->textContent));
			$image=str_replace($wp_imgsite2,$new_imgsite,$subel[1]);
			$caption=$subel[2];
			$title=$subel[3];
			$el->textContent="";
			$el->appendChild($dom->createElement("image:loc",ltrim($image)));
			$el->appendChild($dom->createElement("image:caption",ltrim($caption)));
			$el->appendChild($dom->createElement("image:title",ltrim($title)));
			
		}
		if(!($element->getElementsByTagName('changefreq')))
		{
		$element->appendChild($dom->createElement("lastmod",date("Y-m-d")));
		$element->appendChild($dom->createElement("changefreq","monthly"));
		$element->appendChild($dom->createElement("priority","0.4"));
		}
	}
}

$dom->save("../wp/sitemap.xml");
$dom=new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->load("../wp/sitemap.xml");
$root=$dom->documentElement; 

require_once("../etc/con_db.php");

$result=mysqli_query($con, "SELECT id,model,fam,prod,img_1,img_2,img_3,img_4 FROM notebro_db.MODEL");
while($row=mysqli_fetch_assoc($result))
{
	
	$newel=$root->appendChild($dom->createElement("url"));
	$newel->appendChild($dom->createElement("loc","http://noteb.com/?model/model.php?model_id=".$row["id"]));
		if($row["img_1"]!="")
		{
		$newel2=$newel->appendChild($dom->createElement("image:image"));
		$newel2->appendChild($dom->createElement("image:loc","http://noteb.com/res/img/models/".$row["img_1"]));
		$newel2->appendChild($dom->createElement("image:caption","Official image for ".$row["prod"]." ".$row["fam"]." ".$row["model"]));
		$newel2->appendChild($dom->createElement("image:title",$row["prod"]." ".$row["fam"]." ".$row["model"]));
		}
	$newel->appendChild($dom->createElement("changefreq","monthly"));
	$newel->appendChild($dom->createElement("priority","0.6"));
}

$mainpages=
[
"http://noteb.com/?content/home.php",
"http://noteb.com/?search/adv_search.php?",
"http://noteb.com/?content/articles.php",
"http://noteb.com/?content/reviews.php",
"http://noteb.com/?footer/about.php",
"http://noteb.com/?footer/contact.php",
"https://noteb.com/?public/ireviews.php",
"https://noteb.com/?public/api.php",
"https://noteb.com/?public/api_tos.php",
"https://noteb.com/?model/comp.php?conf0=615592510846506669_839&conf1=7081459548554593121_1192&conf2=6564362557581325102_1466&ex=USD",
"https://noteb.com/?search/search.php?type=99&s_memmin=8&s_memmax=16&ssd=on&s_hddmin=180&s_hddmax=2048&s_dispsizemin=14&s_dispsizemax=15.6&display_type%5B%5D=1&display_type%5B%5D=3&display_type%5B%5D=7&graphics%5B%5D=1&region_type%5B%5D=1&bdgmin=640&bdgmax=1150&exchange=USD"
];

$searchpages=
[
"http://noteb.com/?search/search.php?browse_by=mainstream",
"http://noteb.com/?search/search.php?browse_by=ultraportable",
"http://noteb.com/?search/search.php?browse_by=gaming",
"http://noteb.com/?search/search.php?browse_by=professional",
"http://noteb.com/?search/search.php?browse_by=smalldisplay",
"http://noteb.com/?search/search.php?browse_by=mediumdisplay",
"http://noteb.com/?search/search.php?browse_by=largedisplay"
];

foreach($searchpages as $page)
{
	$newel=$root->appendChild($dom->createElement("url"));
	$newel->appendChild($dom->createElement("loc",$page));
	$newel->appendChild($dom->createElement("changefreq","yearly"));
	$newel->appendChild($dom->createElement("priority","0.7"));	
}

foreach($mainpages as $page)
{
	$newel=$root->appendChild($dom->createElement("url"));
	$newel->appendChild($dom->createElement("loc",$page));
	$newel->appendChild($dom->createElement("changefreq","yearly"));
	$newel->appendChild($dom->createElement("priority","0.9"));	
}

$result=mysqli_query($con, "SELECT brand,pic FROM notebro_site.brands");
while($row=mysqli_fetch_assoc($result))
{
	
	$newel=$root->appendChild($dom->createElement("url"));
	$newel->appendChild($dom->createElement("loc","http://noteb.com/?search/search.php?prod=".ucfirst(strtolower($row["brand"]))."%26browse_by=prod"));
		if($row["pic"]!="")
		{
		$newel2=$newel->appendChild($dom->createElement("image:image"));
		$newel2->appendChild($dom->createElement("image:loc","http://noteb.com/res".$row["pic"]));
		$newel2->appendChild($dom->createElement("image:caption","Logo for ".$row["brand"]));
		$newel2->appendChild($dom->createElement("image:title",$row["brand"]));
		}
	$newel->appendChild($dom->createElement("changefreq","yearly"));
	$newel->appendChild($dom->createElement("priority","0.7"));
}

$result=mysqli_query($con, $sel="SELECT DISTINCT id,mdb,regions,img_1,REGEXP_REPLACE(CONCAT(prod,' ',IFNULL((SELECT fam FROM `notebro_db`.`FAMILIES` WHERE id=idfam),''),' ',IFNULL((SELECT subfam FROM `notebro_db`.`FAMILIES` WHERE id=idfam and showsubfam=1),''),' ',model,' ',submodel),'[[:space:]]+', ' ') as name from `notebro_db`.`MODEL`");
while($row=mysqli_fetch_assoc($result))
{
	
	$newel=$root->appendChild($dom->createElement("url"));
	$newel->appendChild($dom->createElement("loc","https://noteb.com/?model/model.php?model_id=".ucfirst(strtolower($row["id"]))."%26ex=USD"));
		if($row["img_1"]!="")
		{
		$newel2=$newel->appendChild($dom->createElement("image:image"));
		$newel2->appendChild($dom->createElement("image:loc","http://noteb.com/res/img/models/".$row["img_1"]));
		$newel2->appendChild($dom->createElement("image:caption","Image for ".$row["name"]));
		$newel2->appendChild($dom->createElement("image:title",$row["name"]));
		}
	$newel->appendChild($dom->createElement("changefreq","monthly"));
	$newel->appendChild($dom->createElement("priority","0.5"));
}


echo $dom->saveXML();
$dom->save('/var/www/noteb/sitemap/sitemap.xml'); // This saves the XML to a file

//Set this to be your site map URL

// cUrl handler to ping the Sitemap submission URLs for Search Engines…
$i=0;
if($i)
{
	function myCurl($url){
	  $ch = curl_init($url);
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_exec($ch);
	  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	  curl_close($ch);
	  return $httpCode;
	}
	
	$sitemapUrl = "http://noteb.com/sitemap/sitemap.xml";
	//Google
	$url = "http://www.google.com/webmasters/sitemaps/ping?sitemap=".$sitemapUrl;
	$returnCode = myCurl($url);
	echo "<p>Google Sitemaps has been pinged (return code: $returnCode).</p>";

	//Bing / MSN
	$url = "http://www.bing.com/ping?siteMap=".$sitemapUrl;
	$returnCode = myCurl($url);
	echo "<p>Bing / MSN Sitemaps has been pinged (return code: $returnCode).</p>";

	//Yandex
	$url = "http://blogs.yandex.ru/pings/?status=success&url=".$sitemapUrl;
	$returnCode = myCurl($url);
	echo "<p>Yandex.ru Sitemaps has been pinged (return code: $returnCode).</p>";
	
}
?>