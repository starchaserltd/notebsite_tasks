<?php

$nrservers=1; // SET NUMBER OF SERVERS
set_time_limit($nrservers*400);

$ch=array();
$mh = curl_multi_init();
curl_setopt($mh, CURLOPT_TIMEOUT, $nrservers*400);
for($i=0;$i<$nrservers;$i++)
{
  $ch[$i] = curl_init('http://localhost/vault/genconf/gen_search.php?s='.$i);
  curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch[$i], CURLOPT_TIMEOUT, 1000);
  curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT ,30);
  // build the multi-curl handle, adding both $ch
  curl_multi_add_handle($mh, $ch[$i]);
}

  // execute all queries simultaneously, and continue when all are complete
  $running = null;
  do
  {
    curl_multi_exec($mh, $running);
  } while ($running);
  
  // all of our requests are done, we can now access the results
  $response_1 = curl_multi_getcontent($ch[0]);
  echo "$response_1"; 

for($i=0;$i<$nrservers;$i++)
{
  curl_multi_remove_handle($mh, $ch[$i]);
}
curl_multi_close($mh)
  
?>