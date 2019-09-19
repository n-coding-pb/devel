<?php
// http://www.aida.de/typo3temp/shippositions.xml

//require_once("aida-lib/simplexml.class.php");

## Alle Merkmale eines Artikels: NEU ##
function getXMLdata2($pageurl) {
	
	$ch = curl_init();
	
//	$pageurl = "http://www.travel-value.de/kiosk/info?cmd=GetArticleInfo&ean=3414202000329&lang=de&loc=92010";
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	curl_setopt ($ch, CURLOPT_URL, $pageurl );
	$xml = curl_exec ( $ch );
	curl_close($ch);
	
/*	$sxml = new simplexml;
	$sxml->ignore_level = 0;
	$data = $sxml->xml_load_data($xml, 'array');
*/	
	$sxml = simplexml_load_string($xml);
	$json = json_encode($sxml);
	$data = json_decode($json,TRUE);
	
	return ($data);

}

	$pageurl = "https://www.aida.de/webcam/shippositions.xml";//"http://d1ozq1nmb5vv1n.cloudfront.net/webcam/shippositions.xml";//"http://medien.aida.de/webcam/shippositions.xml";//"http://www.aida.de/typo3temp/shippositions.xml";

	$data = getXMLdata2($pageurl);
	
//	echo count($data['ship']);
//	print_r($data); exit();

	$ships = array();
	for($i = 0; $i < count($data['ship']); $i++) {
		$ships[$i] = $data['ship'][$i]['@attributes']['ShipName'];
	}
//	print_r($ships);
	
	if(!isset($_GET['Ship']))
		$_GET['Ship'] = 4;

	$ship = $data['ship'][$_GET['Ship']];
	
//	echo 'LAT:'.$ship['data']['@attributes']['latitude'];
	
	echo '<!--';
	print_r ($ship);
//	print_r ($ship['cams']['cam']);
	echo '-->';
	
	## Anpassung der Google-Maps Darstellung: ##
	if($ship['data']['@attributes']['speed'] <= 1) {
		$gmp['zoom'] = 15;
		$gmp['map'] = 'k';
	} elseif($ship['data']['@attributes']['speed'] <= 5) {
		$gmp['zoom'] = 13;
		$gmp['map'] = '';
	} else {
		$gmp['zoom'] = 10;
		$gmp['map'] = 'p';
	}
	
	## Anpassungen XML-Ausgaben: ##
	if($ship['data']['@attributes']['latitude'] >= 0) $pos['lat'] = 'N'; else $pos['lat'] = 'S';
	if($ship['data']['@attributes']['longitude'] >= 0) $pos['lng'] = 'E'; else $pos['lng'] = 'W';
	
?>
<!doctype html>
<html>
<head>
<title>Webcams <?=$ship['@attributes']['ShipName']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Refresh" content="60" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<style type="text/css">
<!--
body,td,th {
	font-size: 11px;
	color: #333;
	font-family: Verdana, Geneva, sans-serif;
}
body {
	background-color: #33afe1;
	background-image: url(http://www.aida.de/fileadmin/www.aida.de/v3/images/beach.jpg);
	background-repeat:no-repeat;
	background-position: top center;
	background-size: cover;
	margin: 0px;
	padding: 0px;
}
td {
	text-align: center;
}
#logo {
	position: relative;
	margin-left: auto;
	margin-right: auto;
	width: 980px;
	height: 184px;
	background-image: url(http://www.aida.de/fileadmin/www.aida.de/v3/images/bg_header.png);
	background-repeat: no-repeat;
	border: 0px solid #f00;
}
#webcams {
	position: relative;
	margin-left: auto;
	margin-right: auto;
	width: 980px;
	background-color: #fff;
}
#cursor {
	position: absolute;
	top: 130px;
	left: 50%;
	width: 90px;
	height: 90px;
	margin-left: -45px;
	border: 0px dashed #f00;
	border-radius: 50%;
	text-align: center;
	z-index: 10000;
	-moz-transform:rotate(<?= $ship['data']['@attributes']['heading'] ?>deg); 
	-webkit-transform:rotate(<?= $ship['data']['@attributes']['heading'] ?>deg); 
	-o-transform:rotate(<?= $ship['data']['@attributes']['heading'] ?>deg); 
	-ms-transform:rotate(<?= $ship['data']['@attributes']['heading'] ?>deg); 
	transform:rotate(<?= $ship['data']['@attributes']['heading'] ?>deg); 
}
#ships {
	position: absolute;
	top: 150px;
	left: 50%;
	margin-left: -490px;
	width: 980px;
	text-align: right;
	border: 0px dotted green;
	z-index: 10002;
}
-->
</style>
</head>
<body>
<div id="ships">
<form name="selectShip" action="<?=$_SERVER['PHP_SELF']?>" method="GET">
<select name="Ship" onChange="document.forms['selectShip'].submit();">
<?
foreach($ships as $key => $value) {
	if($_GET['Ship'] == $key) $s = ' selected'; else $s = '';
	echo '<option value="'.$key.'"'.$s.'>'.$value.'</option>'."\n";
}
?>
</select>
</form>
</div>
<div id="logo"><img src="http://www.aida.de/fileadmin/www.aida.de/v3/images/logo.png" width="222" height="183" /></div>
<div id="webcams">
<table align="center" cellpadding="0" cellspacing="5">
<tr>
<td colspan="3">
<? foreach($ship['cams']['cam'] as $cams) { ?><img name="" src="<?=$cams['@attributes']['img']?>" width="323" height="235" alt="" /><? } ?>
</td>
</tr><tr>
<td>Backbord-Cam</td>
<td>Bug-Cam</td>
<td>Steuerbord-Cam</td>
</tr>
<tr>
<td colspan="3">&nbsp;</td>
</tr>
<tr>
<td>Wind: <?= $ship['data']['@attributes']['windSpeed'] ?> kn, <?= $ship['data']['@attributes']['windAngle']?></td>
<td>Luft: <?= $ship['data']['@attributes']['airTemperature'] ?>&deg;C</td>
<td>Wasser: <?= $ship['data']['@attributes']['waterTemperature'] ?>&deg;C</td>
</tr>
<tr>
<td colspan="3">&nbsp;</td>
</tr>
<tr>
<td>Position: <?= $pos['lat'].$ship['data']['@attributes']['latitude'] ?>, <?= $pos['lng'].$ship['data']['@attributes']['longitude'] ?></td>
<td>Kurs: <?= $ship['data']['@attributes']['course'] ?>&deg; (<?= $ship['data']['@attributes']['heading'] ?>&deg;)</td>
<td>Geschwindigkeit: <?= $ship['data']['@attributes']['speed'] ?> kn (<?= round($ship['data']['@attributes']['speed']*1.852, 1)?> km/h)</td>
</tr>
<tr>
<td colspan="3" style="position:relative;">
<iframe width="970" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.de/maps?f=q&source=s_q&hl=de&geocode=&mrt=loc&ie=UTF8&t=<?=$gmp['map']?>&ll=<?= $ship['data']['@attributes']['latitude'] ?>,<?= $ship['data']['@attributes']['longitude'] ?>&z=<?=$gmp['zoom']?>&output=embed"></iframe><br />
<small style="float:left;"><a href="http://maps.google.de/maps?f=q&source=embed&hl=de&geocode=&mrt=loc&ie=UTF8&t=<?=$gmp['map']?>&ll=<?= $ship['data']['@attributes']['latitude'] ?>,<?= $ship['data']['@attributes']['longitude'] ?>&z=<?=$gmp['zoom']?>&output=embed" style="color:#0000FF;text-align:left" target="_blank">Größere Kartenansicht</a></small>
<small style="float:right;">Ortszeit: <?=substr($ship['data']['@attributes']['localTime'],0,2).':'.substr($ship['data']['@attributes']['localTime'],2)?> Uhr (Zeitverschiebung: <?=$ship['data']['@attributes']['timeOffsetUtc']?> h)</small>
<div id="cursor"><img name="ship" src="aida-lib/ship180.png" alt="Position" width="90" height="90" /></div>
</td>
</tr>
</table>
</div>

&nbsp;
</body>
<script>
</script> 
<HEAD>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
</HEAD>
</html>