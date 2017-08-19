<?php
class Scrape
{
	private $scrapedData;
	private $url;
	private $fields_names;
	private $websiteName;
	private $request_method;

	function __construct()
	{
		$this->scrapedData = array(); // final result with data to be dsiplayed in HTML table
		$this->url = "";
		$this->request_method = "";
		$this->field_names = array();
	}
	// this function allows empty POST data if it's white listed
	private function onWhiteList($value) {
		$whiteListed = array("websiteName", "optn_useHTMLpurifier", "optn_user_agent");
		$valid = false;
		for ($i=0; $i<count($whiteListed); $i++) {
			if ($value == $whiteListed[$i]) {
				$valid = true;
			}
		}
		return $valid;
	}
	private function isEmpty($values)
	{
		$empty = "";
		foreach($values as $key=>$value){
    		if ($value == "" && !$this->onWhiteList($key)) {
				$empty .= "(" . $key . " is required)<br>";
			}
		}
		return $empty;
	}
	public function valData($postData)  // (validateData)
	{
		$errorMsg = "";
		$valid = array("url", "capture_method_", "expression_", "field_name_", "optn_request_method");
		// if data doesn't contain at least 1 val of $valid, set error
		if (count($postData) < count($valid)) {
			$errorMsg .= "Something went wrong!<br>";
		}

		// check if postData not empty
		if (!empty($this->isEmpty($postData))) {
			$errorMsg .= "Some fields are left empty!<br>" . $this->isEmpty($postData);
		}
		return $errorMsg;
	}
	private function getFields($postData)
	{
		// loop through fields and append each row in an array
		$fields = array();
		for ($i=1; $i<=count($postData); $i++) { // values always start with 1 (capture_method_1  and not capture_method_0)
			if (isset($postData['capture_method_' . $i]) && isset($postData['expression_' . $i]) && isset($postData['field_name_' . $i])) {
				array_push($fields, array($postData['capture_method_' . $i], $postData['expression_' . $i], $postData['field_name_' . $i]));
			}
		}
		return $fields;
	}
	private function getRegexResult() {
		// this function gets the regex scraping result from $this->startScraper()
		// and returns the correct, formatted data

		/* NOTE *:
		preg_match_all($reg, $doc, $matches);
		$matches[0] = full match
		$matches[1] = group 1
		$macthes[2] = group 2
		...
		*/
		$argAmount = func_num_args();
		$tmp = array();

		switch ($argAmount) {
			case 1:
				$group1 = func_get_arg(0);
				// if group1 is empty, there's no data found, so display error
				if (!empty($group1)) {
					for ($i=0; $i<count($group1); $i++) {
						array_push($tmp, strip_tags($group1[$i]));
					}
				}else{
					array_push($tmp, "<span>nothing found</span>");
				}
				break;

			default:
				break;
		}
		return $tmp;
	}
	public function startScraper($postData)
	{
		// Set global values
		$this->url = $postData['url'];
		if (!empty($postData['websiteName'])) {
			$this->websiteName = $postData['websiteName'];
		}else{
			$this->websiteName = $this->url;
		}

		// use curl or file_get_contents
		$this->request_method = $postData['optn_request_method'];
		if ($this->request_method == "file_get_contents") {
			$doc = file_get_contents($this->url);
		}else{
			$curl = curl_init($this->url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			// set useragent
			if (!empty($postData['optn_user_agent'])) {
				curl_setopt($curl, CURLOPT_USERAGENT, $postData['optn_user_agent']);
			}
			$doc = curl_exec($curl);
		}

		// purify html if needed
		if (isset($_POST['optn_useHTMLpurifier'])) {
			require_once 'lib/htmlpurifier/HTMLPurifier.auto.php';
			$config = HTMLPurifier_Config::createDefault();
			$purifier = new HTMLPurifier($config);
			$doc = $purifier->purify($doc);
		}
		$fields = $this->getFields($postData);

		// For each field (at the "Fields" tab) the user entered...
		for ($i=0; $i<count($fields); $i++) {
			// ... Get the different sections
			$method = $fields[$i][0];
			$expression = $fields[$i][1];
			$field_name = $fields[$i][2];
			array_push($this->field_names, $field_name); // Push all field_names in an array
			$tmp = array(); // temp result array

			if ($method == "selector") {
				switch ($expression) {
					case preg_match('/h[1-6]/', $expression, $heading) == 1: // check if input is h1, h2, ... or h6
						preg_match_all('/<' . $heading[0] . '>(.*?(?=<))<\/' . $heading[0] . '>/s', $doc, $matches);
						$group = $matches[1]; // see *
						$tmp = $this->getRegexResult($group);
						break;
					case 'title':
						preg_match_all('/<title>(.+)?(?=<)<\/title>/s', $doc, $match);
						$group = $match[1];
						$tmp = $this->getRegexResult($group);
						break;
					case 'img':
						preg_match_all('/<img .*?src=("|\')(.[^"\']+)("|\').*?>/s', $doc, $matches);
						//TOTRY: scrape alt="an image" too
						$group = $matches[2];
						$tmp = $this->getRegexResult($group);
						// add "show" links, to display the img in new tab
						for ($j=0; $j<count($tmp); $j++) {
							$tmp[$j] .= '  <a href="' . $tmp[$j] . '" target="blank">[show]</a>';
						}
						break;
					case 'a':
						preg_match_all('/<a .*?href=("|\')(.+?)("|\').*?>/s', $doc, $matches);
						$group = $matches[2];
						$tmp = $this->getRegexResult($group);
						for ($j=0; $j<count($tmp); $j++) {
							$tmp[$j] .= '  <a href="' . $tmp[$j] . '" target="blank">[open]</a>';
						}
						break;
					case preg_match('/(.+[^\[\]])(\.)(.+)/', $expression, $matches) == 1: // check if asked for class
						// --syntax--
						// p.class to select from <p class="abc">

						// divide and conquer
						$tag = $matches[1];
						$selector = $matches[2];
						$attr = $matches[3];

						preg_match_all('/<' . $tag . ' .*?class=["|\'](' . $attr . ')["|\'].*?>(.+?)<\/' . $tag . '>/s', $doc, $results);

						$group = $results[2];
						$tmp = $this->getRegexResult($group);
						break;
					case preg_match('/(.+)(#)(.+)/', $expression, $matches) == 1: // check if asked for id
						// --syntax--
						// p#id to select from <p id="abc">
						$tag = $matches[1];
						$selector = $matches[2];
						$attr = $matches[3];

						preg_match_all('/<' . $tag . ' .*?id=["|\'](' . $attr . ')["|\'].*?>(.+?)<\/' . $tag . '>/s', $doc, $results);

						$group = $results[2];
						$tmp = $this->getRegexResult($group);
						break;
					case preg_match('/(.+)\[(.+)\]\.(.+)/', $expression, $matches) == 1: // get element with random attr
						// example: <div randomname="value">innertext</div> => get innertext or value

						$tag = $matches[1];
						$attr = $matches[2];
						$selector = $matches[3];

						// value :
						if ($selector == "val") {
							preg_match_all('/<' . $tag . '.*' . $attr . '=["|\'](.+)["|\'].*?>/s', $doc, $results);
							$group = $results[1];
							$tmp = $this->getRegexResult($group);
						}else{
							// innertext
							preg_match_all('/<' . $tag . '.*' . $attr . '=["|\'].+["|\'].*?>(.+)?<\/' . $tag . '>/s', $doc, $results);
							$group = $results[1];
							$tmp = $this->getRegexResult($group);
						}
						break;
					case 'link': // links <link rel="stylesheet" type="text/css" href="test">
						preg_match_all('/<.*link.*rel=["|\']stylesheet["|\'].(.+?)>/s',$doc, $matches);
						$group = $matches[1];
						$tmp = $this->getRegexResult($group);
						break;
					default:
						$regex = '/<.*?' . $expression . '.*?>(.+?)<\/' . $expression . '>/s';
						preg_match_all($regex, $doc, $matches);
						$group = $matches[1];
						$tmp = $this->getRegexResult($group);
						break;
				}
			}
			if ($method == "regex") {
				preg_match_all($expression, $doc, $matches);
				// loop through full match, group1, 2...
				for ($a=0; $a<count($matches); $a++) {
					$group = $matches[$a];
					// add labels to tmp array (group or fm)
					if ($a == 0) {
						array_push($tmp, "<b>Full match: </b>");
					}else{
						array_push($tmp, "<b>Group " . $a . ": </b>");
					}
					// add contents of each group to tmp array
					for ($b=0; $b<count($group); $b++) {
						array_push($tmp, htmlspecialchars($group[$b]));
					}
				}
			}
			array_push($this->scrapedData, $tmp);
		}
	}
	public function getData() {
		return $this->scrapedData;
	}
	public function getURL() {
		return $this->url;
	}
	public function get_field_names() {
		return $this->field_names;
	}
	public function getWebsiteName() {
		return $this->websiteName;
	}
	public function api($getData, $data, $output_method) {
		// TODO
		if ($output_method == "TXT") {

		}
		if ($output_method == "JSON") {
			$json = array_combine(array_keys($getData), array_values($getData));
			return json_encode($json);
		}
	}
}
?>
