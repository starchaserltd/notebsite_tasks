#!/usr/bin/php
<?php
require_once("/var/www/vault/etc/con_db.php");

// Open CURL session:
$ch = curl_init("https://openexchangerates.org/api/latest.json?app_id=7537371e89274e929c77d1f8d038bd81");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Get the data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$exchangeRates = json_decode($json);
//var_dump($exchangeRates);

// You can now access the rates inside the parsed object, like so:

if($exchangeRates->rates->RON)
	echo "Exchange rates succesfully updated!";

foreach ($exchangeRates->rates as $country=>$value) {
    
			$sel = "UPDATE exchrate SET rate=$value WHERE code='$country'";
			mysqli_query($con,$sel);
	
}


 /*     
$apikey = '5ce60d6c23ac268f132511d210717c00';
$endpoint = 'https://vatapi.com/v1/vat-rates';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Apikey: '.$apikey));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 40);

$json = curl_exec($ch);
curl_close($ch);

$VATRates = json_decode($json);
// You can now access the rates inside the parsed object, like so:
echo "test";

echo $VATRates->countries[0]->AT->rates->standard->value;
echo $VATRates->countries[0]->rates->standard->value;

var_dump($VATRates);

foreach($VATRates->countries as $i)
{
	var_dump($i);
	echo "<br>";
	/*foreach ($i as $country=>$value)
	{
		//		echo $sel;
		//		echo "<br>";		
		echo $value;
		//echo $country;
		echo "<br>";
	}*/
//}


?>
