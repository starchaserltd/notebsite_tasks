<?php 
require_once("etc/con_db.php");

echo "here we start";

$sqltext="SELECT id FROM notebro_db.MODEL";
$result = mysqli_query($con, $sqltext);

$sqltext="SELECT * FROM ";
$i=1;
while ($rand = mysqli_fetch_assoc($result)) {

if($i)
{ $sqltext.="(SELECT * FROM all_conf WHERE model=$rand[id] AND (model IN (4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,46,47,48,49,50,51,52,53,54,56,57,58,60,61,62,63,65,66,68,69,71,72,74,76,77,78,79,80,81,82,83,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142)) ORDER BY value DESC LIMIT 1) t"; $i=0;}
else
{  $sqltext.=" UNION (SELECT * FROM all_conf WHERE model=$rand[id] AND (model IN (4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,46,47,48,49,50,51,52,53,54,56,57,58,60,61,62,63,65,66,68,69,71,72,74,76,77,78,79,80,81,82,83,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142)) ORDER BY value DESC LIMIT 1)"; }

}

echo $sqltext;

?>