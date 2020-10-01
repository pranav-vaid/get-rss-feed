<?php

$base_url = $_REQUEST["url"] ? $_REQUEST["url"] : "exit";
// $base_url = "https://dailysuperheroes.com/feed/";

if ($base_url == "exit")
	exit("No URL given for RSS Feed.");
// echo "Url is " . $base_url;

$file = "mdl.xml";

$xml_data = "";

for ($i=1; $i <= 1; $i++) {

	if ($i == 1) {
		// $url = $base_url;
		$url = $base_url;
	} else {
		$url = $base_url . "?paged=" . $i;
	}

	// initialize cURL
	$ch = curl_init($url);

	// set to retutn instead of echo
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($ch);

	curl_close($ch);

	$error = "Page not found";

	if ( strpos($result, $error) )
		break;
	// print_r($result);
	// $matches = array();

	if ($i == 1) {
		// echo "Page " . $i . $result;
		$pattern = "/(<.*<\/item>)(.*)/s";

		$result = get_post_url($result);

		preg_match_all($pattern, $result, $matches, PREG_PATTERN_ORDER);

		$start = $matches[1][0];
		$end = $matches[2][0];

		$xml_data .= $start;

		// echo "Start in 1 page " . $start;
		// echo "End in 1 page " . $end;

	} else {
		$pattern = "/(<item>.*<\/item>)(.*)/s";
		preg_match_all($pattern, $result, $matches, PREG_PATTERN_ORDER);

		$middle = $matches[1][0];

		$xml_data .= $middle;

		// echo "Middle in " . $i . " page " . $middle;
	}
}

$xml_data .= $end;

// echo $xml_data;

if ( write_file($file, $xml_data) )
	printf("%d pages stored", $i - 1);

// store the data in xml file and save it for uploading
function write_file($file, $data) {
	$file_object = fopen($file, "w+");

	fwrite($file_object, $data);

	fclose($file_object);

	return true;
}

// get post url from the rss feed, so that we can get its image
function get_post_url($feed) {

	$feed_xml = new SimpleXMLElement($feed);

	for ($i = 0; $i < 10; $i++) {
		$item = $feed_xml -> channel -> item[$i];

		$item -> featured_image = get_image_url($item -> link);
	}

	return $feed_xml -> asXML();
	// print_r($feed_xml);
}

function get_image_url($post_url) {
	// initialize cURL
	$curl_post = curl_init($post_url);

	// set to retutn instead of echo
	curl_setopt($curl_post,CURLOPT_RETURNTRANSFER, true);

	$post = curl_exec($curl_post);

	curl_close($curl_post);

	// echo $post;

	$pattern = '/<img.*src="(.*)".*\/>/U';
	preg_match_all($pattern, $post, $matches, PREG_PATTERN_ORDER);

	// print_r($matches);

	$image_url = $matches[1][0];

	return $image_url;
}