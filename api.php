<?php
require_once 'lib/scrape.php';
$scraper = new Scrape();

if (isset($_GET)) {
	if (empty($scraper->valData($_GET)) && !empty($_GET['optn_api_output'])) {
		/*
		example:
		http://localhost/EasyScrape/api.php?optn_api_output=JSON&url=http://localhost/EasyScrape/tests/test.html&websiteName=&capture_method_1=selector&expression_1=a&field_name_1=a&optn_request_method=file_get_contents&optn_user_agent=Mozilla/5.0%20(Windows;%20U;%20Windows%20NT%205.1;%20en-US;%20rv:1.8.1.13)%20Gecko/20080311%20Firefox/2.0.0.13*/
		$scraper->startScraper($_GET);
		echo $scraper->api($_GET, $scraper->getData(), $_GET['optn_api_output']);

	}else{
		?><h1> Request invalid </h1><?php
	}
}else{
	?><h1> Request invalid </h1><?php
}
?>