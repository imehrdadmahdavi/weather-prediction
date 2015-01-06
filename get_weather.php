
<?php
global $rss;
$count = 0;

if (isset($_GET['loc_type']) && isset($_GET['loc']) && isset($_GET['temp_unit'])) {

	$loc = urlencode(trim($_GET['loc']));
	echo $loc;
	$loc_type =  trim($_GET['loc_type']);
	$temp_unit = $_GET['temp_unit'];
	if ($loc_type == "city") {
		$url = "http://where.yahooapis.com/v1/places\$and(.q('".$loc."'),.type(7));start=0;count=1?appid=fxvoFhrV34ElnC8.DgDhmM8P9LUNKa18wSzFVb_EDCaJWmnMVPBn.b.F6meOZaw7_y8Oet.C";
		//string urlencode (string $url);
		$xml_yahoo_geo = @simplexml_load_file($url);
		//print_r($xml_yahoo_geo);
		if ($xml_yahoo_geo) {
			//draw_first_row();
			foreach($xml_yahoo_geo->children() as $child)
			{
				$woeid = $child->woeid;
	
				$rss = "http://weather.yahooapis.com/forecastrss?w=$woeid&u=$temp_unit";
				rss_handle($rss, $woeid, $temp_unit);	
			}
			$var_loc = str_replace('+', ' ', $loc);
			$var_loc = ucwords ($var_loc);	
		} else {
			
		}
	} else {
		$url = "http://where.yahooapis.com/v1/concordance/usps/$loc?appid=fxvoFhrV34ElnC8.DgDhmM8P9LUNKa18wSzFVb_EDCaJWmnMVPBn.b.F6meOZaw7_y8Oet.C";
		//echo $url;
		$xml_yahoo_geo = @simplexml_load_file($url);
		//print_r($xml_yahoo_geo);
		if ($xml_yahoo_geo) {
			//draw_first_row();
			$woeid = $xml_yahoo_geo->woeid;
	
			$rss = "http://weather.yahooapis.com/forecastrss?w=$woeid&u=$temp_unit";
			//echo $rss;
			$count = 1;
			
			rss_handle($rss, $woeid, $temp_unit);
		} else {
			
		}	
 	}
 
}

function rss_handle($rss, $woeid, $temp_unit){

global $count;
$xml_yahoo_rss = simplexml_load_file($rss);
if ($xml_yahoo_rss->channel->item->title != "City not found") {
	$count++;
	$weather_tag = $xml_yahoo_rss->channel->item->description;
	$DOM = new DOMDocument;
	$DOM->loadHTML($weather_tag);
	$items = $DOM->getElementsByTagName('img');
	$weather = $items->item(0)->getAttribute('src');

	$yweather_con = $xml_yahoo_rss->channel->item->children('http://xml.weather.yahoo.com/ns/rss/1.0');
	foreach($yweather_con->condition[0]->attributes() as $a => $b)
	{
		$weather_condition[$a] = $b;
	}
	$weather_condition_text = $weather_condition['text'];
	$weather_condition_temp = $weather_condition['temp'];

	$yweather_fore = $xml_yahoo_rss->channel->item->children('http://xml.weather.yahoo.com/ns/rss/1.0');
	$fore_count = 0;
	foreach($yweather_fore->forecast as $a => $b)
	{
		$fore_count++;
	}
	for ($i=0; $i < $fore_count; $i++) { 	
		$day_array[$i] = $yweather_fore->forecast[$i]->attributes()->day;
		$low_array[$i] = $yweather_fore->forecast[$i]->attributes()->low;
		$high_array[$i] = $yweather_fore->forecast[$i]->attributes()->high;
		$text_array[$i] = $yweather_fore->forecast[$i]->attributes()->text;
	}
	// foreach($yweather_fore->forecast as $a => $b)
	// {
	// 	$fore_count++;
	// 	$day_array[$a] = $b->attributes()->day;
	// 	$low_array[$a] = $b->attributes()->low;
	// 	$high_array[$a] = $b->attributes()->high;
	// 	$text_array[$a] = $b->attributes()->text;
	// }
	//var_dump($day_array);

	$yweather_uni = $xml_yahoo_rss->channel->children('http://xml.weather.yahoo.com/ns/rss/1.0');
	foreach($yweather_uni->units[0]->attributes() as $a => $b)
	{
		$weather_units[$a] = $b;
	}
	//echo $weather_units['temperature'];
	$weather_condition_temperature = $weather_units['temperature'];
	$yweather_loc = $xml_yahoo_rss->channel->children('http://xml.weather.yahoo.com/ns/rss/1.0');
	foreach($yweather_loc->location[0]->attributes() as $a => $b)
	{
		$weather_location[$a] = $b;
	}
	//echo $weather_location['city'];
	$weather_location_city = $weather_location['city'];
	$weather_location_region = $weather_location['region'];
	$weather_location_country = $weather_location['country'];
	$geo = $xml_yahoo_rss->channel->item->children('http://www.w3.org/2003/01/geo/wgs84_pos#');
	$geo_lat = $geo->lat;
	$geo_long = $geo->long;
	
	$link = $xml_yahoo_rss->channel->link;
	//echo $link;

	if ($weather == "") {
		$weather = "N/A";
	}
	if ($weather_condition_text == "") {
		$weather_condition_text = "N/A";
	}
	if ($weather_condition_temp == "") {
		$weather_condition_temp = "N/A";
	}
	if ($weather_condition_temperature == "") {
		$weather_condition_temperature = "N/A";
	}
	if ($weather_location_city == "") {
		$weather_location_city = "N/A";
	}
	if ($weather_location_region == "") {
		$weather_location_region = "N/A";
	}
	if ($weather_location_country == "") {
		$weather_location_country = "N/A";
	}
	if ($geo_lat == "") {
		$geo_lat = "N/A";
	}
	if ($geo_long == "") {
		$geo_long = "N/A";
	}
	if ($link == "") {
		$link = "N/A";
	}

	// echo "
	// <weather>
	// <feed>'$rss'</feed>
	// <link>$link</link>
	// <location city='$weather_location_city' region='$weather_location_region' country='$weather_location_country'/>
	// <units temperature='$weather_condition_temperature'/>
	// <condition text='$weather_condition_text' temp='$weather_condition_temp'/>
	// <img>'$weather'</img>";

	// foreach ($weather_forecast as $value)
 //  	{
 //  		$day = $value['day'];
 //  		$low = $value['low'];
 //  		$high = $value['high'];
 //  		$text = $value['text'];
 //  		echo "<forecast day=\"$day\" low=\"$low\" high=\"$high\" text=\"$high\" />";
 //  	}
	// echo "</weather>";
	$rss = "http://weather.yahooapis.com/forecastrss?w=$woeid&amp;u=$temp_unit";	
	$fileContents = "
	<weather>
	<feed>'$rss'</feed>
	<link>'$link'</link>
	<location city='$weather_location_city' region='$weather_location_region' country='$weather_location_country'/>
	<units temperature='$weather_condition_temperature'/>
	<condition text='$weather_condition_text' temp='$weather_condition_temp'/>
	<img>'$weather'</img>";
	//print_r($weather_forecast);
	for ($i=0; $i < $fore_count; $i++)
  	{
  		 	$day = $day_array[$i];
	  		$low =  $low_array[$i];
	  		$high = $high_array[$i];
	  		$text = $text_array[$i];
  			$fileContents.= "<forecast day=\"$day\" low=\"$low\" high=\"$high\" text=\"$text\" />";			
  	}
	$fileContents.= "</weather>";
	//echo $fileContents;
	$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
    $fileContents = trim(str_replace('"', "'", $fileContents));
    $simpleXml = @simplexml_load_string($fileContents);

    echo $fileContents;
    
    //$json = json_encode($simpleXml); 
    //echo $json;
	}
}
?>
