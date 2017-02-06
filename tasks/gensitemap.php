<?php

$wp_site="http://notebro.intexim.ro/admin/wp/article.php";
$new_site="http://noteb.com/?content/article.php?";
$wp_imgsite="http://notebro.intexim.ro/admin/wp/wp-content/uploads";
$new_imgsite="http://noteb.com/uploads";
$dom=new DOMDocument();
$dom->formatOutput = true;
$dom->load("../wp/sitemap-image.xml");
$root=$dom->documentElement; // This can differ (I am not sure, it can be only documentElement or documentElement->firstChild or only firstChild)
$elements=$root->getElementsByTagName('url');

foreach ($elements as $element) 
{
    $loc=$element->getElementsByTagName('loc')->item(0)->textContent;
	$element->getElementsByTagName('loc')->item(0)->textContent=str_replace($wp_site,$new_site,$loc);
	$image=$element->getElementsByTagName('image');
	foreach ($image as $el)
	{
		if(strpos($el->textContent,"\n"))
		{
		$subel=explode("\n",$el->textContent);
		$image=str_replace($wp_imgsite,$new_imgsite,$subel[1]);
		$caption=$subel[2];
		$title=$subel[3];
		$el->textContent="";
		$el->appendChild($dom->createElement("image:loc",ltrim($image)));
		$el->appendChild($dom->createElement("image:caption",ltrim($caption)));
		$el->appendChild($dom->createElement("image:title",ltrim($title)));
		}
	}
	if(!($element->getElementsByTagName('changefreq')))
	{
	$element->appendChild($dom->createElement("lastmod",date("Y-m-d")));
	$element->appendChild($dom->createElement("changefreq","monthly"));
	$element->appendChild($dom->createElement("priority","0.4"));
	}
}

$dom->save("../wp/sitemap-image.xml");
$dom=new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->load("../wp/sitemap-image.xml");
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
"http://noteb.com/?model/comp.php?conf0=81276%26conf1=10860390%26conf2=10506097",
"http://noteb.com/?search/search.php?type=1%26performfocus=1%26graphics=1%26display=1%26storage%5B%5D=1%26warmin=1%26warmax=2%26bdgmin=400%26bdgmax=2710%26exchange=USD"
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
		$newel2->appendChild($dom->createElement("image:loc","http://noteb.com/res/".$row["pic"]));
		$newel2->appendChild($dom->createElement("image:caption","Logo for ".$row["brand"]));
		$newel2->appendChild($dom->createElement("image:title",$row["brand"]));
		}
	$newel->appendChild($dom->createElement("changefreq","yearly"));
	$newel->appendChild($dom->createElement("priority","0.7"));
}


echo $dom->saveXML();

//$dom->saveXML(); // This will return the XML as a string
//$dom->save('../../sitemap/sitemap.xml'); // This saves the XML to a file

/*
	if ($wp_udinra_ping_google == true) {
		$udinra_ping_url ='';
		$udinra_ping_url = "http://www.google.com/webmasters/tools/ping?sitemap=" . urlencode($udinra_tempurl);
		$udinra_response = wp_remote_get( $udinra_ping_url );
		if (is_wp_error($udinra_response)) {
		}
		else {
		if($udinra_response['response']['code']==200)
			{ $udinra_sitemap_response .= "Pinged Google Successfully"."<br>"; }
			else { $udinra_sitemap_response .= "Failed to ping Google.Please submit your image sitemap(sitemap-image.xml) at Google Webmaster."."<br>";}}}

			
			
			*/
			/*
* Sitemap Submitter
* Use this script to submit your site maps automatically to Google, Bing.MSN and Ask
* Trigger this script on a schedule of your choosing or after your site map gets updated.
*/

//Set this to be your site map URL
//$sitemapUrl = "http://www.example.com/sitemap.xml";

// cUrl handler to ping the Sitemap submission URLs for Search Engines…
/*
function myCurl($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return $httpCode;
}

//Google
$url = "http://www.google.com/webmasters/sitemaps/ping?sitemap=".$sitemapUrl;
$returnCode = myCurl($url);
echo "<p>Google Sitemaps has been pinged (return code: $returnCode).</p>";

//Bing / MSN
$url = "http://www.bing.com/ping?siteMap=".$sitemapUrl;
$returnCode = myCurl($url);
echo "<p>Bing / MSN Sitemaps has been pinged (return code: $returnCode).</p>";

//ASK
$url = "http://submissions.ask.com/ping?sitemap=".$sitemapUrl;
$returnCode = myCurl($url);
echo "<p>ASK.com Sitemaps has been pinged (return code: $returnCode).</p>";*/
?>