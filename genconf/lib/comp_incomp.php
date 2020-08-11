<?php
if(!isset(${$comp."_selected_data"}[$val])){ echo "<b>Missing data for:</b> ".$comp; var_dump(${$comp."_selected_data"}); echo $val; echo "<br><br>"; $incompatible=True; }
switch($comp)
{
	case "cpu":
	{
		break;
	}
	case "display":
	{
		break;
	}
	case "mem":
	{
		break;
	}
	case "hdd":
	{
		if(isset($result_val["hdd"])&&isset($result_val["shdd"])&&isset($result_val["mdb"]))
		{
			$mdb_2sata=0; if(stripos($mdb_selected_data[$result_val["mdb"]]["hdd"],"2 x SATA")!==FALSE){ $mdb_2sata=1; }
			if((!$mdb_2sata && stripos($hdd_selected_data[$result_val["hdd"]]["model"],"M.2")===FALSE && stripos($hdd_selected_data[$result_val["hdd"]]["model"],"EMMC")===FALSE && (stripos($shdd_selected_data[$result_val["shdd"]]["model"],"N/A")===FALSE))||( $mdb_2sata && (stripos($hdd_selected_data[$result_val["hdd"]]["type"],"SSD")===FALSE) && stripos($shdd_selected_data[$result_val["shdd"]]["model"],"N/A")===FALSE ) )
			{ $incompatible=True; }
		}
		break;
	}
	case "shdd":
	{
		if(isset($result_val["hdd"])&&isset($result_val["shdd"])&&isset($result_val["mdb"]))
		{
			$mdb_2sata=0; if(stripos($mdb_selected_data[$result_val["mdb"]]["hdd"],"2 x SATA")!==FALSE){ $mdb_2sata=1; }
			if((!$mdb_2sata && stripos($hdd_selected_data[$result_val["hdd"]]["model"],"M.2")===FALSE && stripos($hdd_selected_data[$result_val["hdd"]]["model"],"EMMC")===FALSE && (stripos($shdd_selected_data[$result_val["shdd"]]["model"],"N/A")===FALSE))||( $mdb_2sata && (stripos($hdd_selected_data[$result_val["hdd"]]["type"],"SSD")===FALSE) && stripos($shdd_selected_data[$result_val["shdd"]]["model"],"N/A")===FALSE ) )
			{ $incompatible=True; }
		}
		break;
	}
	case "gpu":
	{
		break;
	}
	case "wnet":
	{
		break;
	}
	case "odd":
	{
		break;
	}
	case "mdb":
	{
		if(isset($result_val["hdd"])&&isset($result_val["shdd"])&&isset($result_val["mdb"]))
		{
			$mdb_2sata=0; if(stripos($mdb_selected_data[$result_val["mdb"]]["hdd"],"2 x SATA")!==FALSE){ $mdb_2sata=1; }
			if((!$mdb_2sata && stripos($hdd_selected_data[$result_val["hdd"]]["model"],"M.2")===FALSE && stripos($hdd_selected_data[$result_val["hdd"]]["model"],"EMMC")===FALSE && (stripos($shdd_selected_data[$result_val["shdd"]]["model"],"N/A")===FALSE))||( $mdb_2sata && (stripos($hdd_selected_data[$result_val["hdd"]]["type"],"SSD")===FALSE) && stripos($shdd_selected_data[$result_val["shdd"]]["model"],"N/A")===FALSE ) )
			{ $incompatible=True; }
		}
		break;
	}
	case "chassis":
	{
		break;
	}
	case "acum":
	{
		break;
	}
	case "war":
	{
		break;
	}
	case "sist":
	{
		break;
	}
	default:
	{
		break;
	}
}
?>