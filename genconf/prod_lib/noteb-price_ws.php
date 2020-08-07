<?php
function restart_pricer_web_service()
{
	$opts=array(
		'http' => array(
			'method' => "GET",
			'header' => "Content-Type: application/x-www-form-urlencoded"
		)
	);
	$context = stream_context_create($opts);
	$file=file_get_contents('http://0.0.0.0:6667/reload-tables',false,$context);
	return json_decode($file,false);
}

function chunk_to_json($chunk)
{
	$xss = array_map("config_list_to_dict", $chunk);
	$xss = array_map(function ($xs) { return array_map('intval', $xs); }, $xss);
	$j = json_encode(array("ids" => $xss));
	return $j;
}

function post_request_to_noteb_price_ws($json_data)
{
	$opts=array(
		'http' => array(
			'method' => "POST",
			'header' => "Content-Type: application/x-www-form-urlencoded",
			'content' => $json_data
		)
	);
	$context = stream_context_create($opts);
	$file=file_get_contents('http://0.0.0.0:6667/predict',false,$context);
	return json_decode($file,false);
}
?>