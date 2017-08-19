<?php
require_once 'lib/scrape.php';
$scraper = new Scrape();
// check if post data is valid
$errorMsg = "";
if (count($_POST) > 0) { // (dynamic data)
	$errorMsg .= $scraper->valData($_POST);
	if (empty($errorMsg)) {
		// let's begin the fun stuff :)
		$scraper->startScraper($_POST);
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>EasyScraper</title>
	<!-- jquery -->
	<script src="includes/jquery/jquery.min.js"></script>
	<!-- bootstrap -->
	<link rel="stylesheet" href="includes/bootstrap/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="includes/bootstrap/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<script src="includes/bootstrap/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<!-- custom links/style sheets -->
	<link rel="stylesheet" type="text/css" href="includes/css/theme.css">
	<script src="includes/js/func.js"></script>
</head>
<body>
	<!-- program title -->
	<div class="container">
		<h1>EasyScraper v1.0</h1>
		<?php if ($errorMsg != "") { ?>
		<div class="alert alert-danger">
  			<strong>Oops!</strong> <?php echo $errorMsg; ?>
		</div>
		<?php } ?>
	</div>
	<!-- tabs -->
	<div id="tabControl" class="container">
		<ul class="nav nav-pills tc-tab">
			<li class="active">
				<a href="#tab1" data-toggle="tab" class="tc-tab-text">Scrape</a>
			</li>
			<li>
				<a href="#tab3" data-toggle="tab" class="tc-tab-text">Fields</a>
			</li>
			<li>
				<a href="#tab4" data-toggle="tab" class="tc-tab-text">Settings</a>
			</li>
			<li>
				<a href="#tab5" data-toggle="tab" class="tc-tab-text">Documentation</a>
			</li>
		</ul>
		<!-- form for posting data-->
		<form method="POST" action="index.php">
			<div class="form-group">
				<!-- tab content -->
				<div class="tab-content clearfix">
					<div class="tab-pane active" id="tab1">
						<!-- url -->
						<label for="url">Website to scrape: </label>
						<input type="text" name="url" id="url" class="form-control" placeholder="http://www.yourwebsitehere.com/" value="http://localhost/EasyScraper/tests/test.html">
						<br><!-- website name -->
						<label for="websiteName">Website Name (optional): </label>
						<input type="text" name="websiteName" id="websiteName" class="form-control" placeholder="awesome website">
					</div>
					<div class="tab-pane" id="tab3">
						<!-- fields table -->
						<table class="table table-bordered text-center" id="fields_table">
							<tr class="active">
								<th class="text-center">Method</th>
								<th class="text-center">Expression</th>
								<th class="text-center">Field name (optional)</th>
								<th class="text-center">Action</th>
							</tr>
							<tr class="field field1">
								<!-- method -->
								<td>
									<div class="btn-group" data-toggle="buttons">
										<label class="btn btn-default">
											<input type="radio" name="capture_method_1" class="capture_method" value="selector">selector
										</label>
										<label class="btn btn-default">
											<input type="radio" name="capture_method_1" class="capture_method" value="regex">regex
										</label>
									</div>
								</td>
								<!-- expression -->
								<td>
									<input type="text" class="form-control expression" placeholder="img, /expr/s, a" name="expression_1">
								</td>
								<!-- field name -->
								<td>
									<input type="text" class="form-control field_name" placeholder="image, header, link..." name="field_name_1">
								</td>
								<!-- action -->
								<td>
									<button type="button" class="btn btn-danger removeFieldBtn" onclick="removeField(1);">
										<span class="glyphicon glyphicon-remove"></span>
									</button>
								</td>
							</tr>
						</table>
						<!-- add field (add row with other words) -->
						<button type="button" class="btn btn-danger" onclick="addField();">Add field</button>
					</div>
					<!-- settings -->
					<div class="tab-pane" id="tab4">
						<h4>Global settings: </h4>
						<input type="checkbox" name="optn_useHTMLpurifier"> use HTML purifier<br>
						<input type="radio" name="optn_request_method" value="file_get_contents" checked="true" onclick="hideUserAgentOption();">file_get_contents
						<br><input type="radio" name="optn_request_method" value="curl" onclick="showUserAgentOption();">curl
						<div class="settings-user-agent" style="display: none;">
							<label for="optn_user_agent">User agent (optional)</label>
							<input type="text" class="form-control" name="optn_user_agent" value="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13">
						</div>


					</div>
					<div class="tab-pane" id="tab5">
						<h4>Selectors</h4>
						<strong>head: </strong>show everything in the <?php echo htmlspecialchars("<head>"); ?> section<br>
						<strong>body: </strong>show everything in the <?php echo htmlspecialchars("<body>"); ?> tag<br>
						<strong>link: </strong>get the link to CSS file(s)<br>
						<strong>nav: </strong>get all contents of a navigation bar<br>
						<strong>center: </strong>show centered text <br>
						<strong>span: </strong>retrieve contents of span element<br>
						<strong>button: </strong>get button content<br>
						<strong>a: </strong>retrieve all links from the webpage<br>
						<strong>img: </strong>get all image links<br>
						<strong>h1: </strong>display the headings h1, h2...h6<br>
						<strong>ol: </strong>retrieve ordered list items<br>
						<strong>select: </strong>options forom list<br>
						<strong>option: </strong>get innertext from single option<br>
						<strong>ul: </strong>retrieve unordered list items<br>
						<strong>table: </strong>show everything from the table<br>
						<strong>tr: </strong>get each table row<br>
						<strong>td: </strong>get table data<br>
						<strong>title: </strong>retrieve the title of the site<br>
						<strong>elem.class: </strong>get the element with the appropriate <i>class</i><br>
						<strong>elem#id: </strong>get the element with the appropriate <i>id</i><br>
						<strong>elem[attribute].inner: </strong>get the innertext of an element with a special attribute name<br>
						<strong>elem[attribute].val: </strong>get the value of a special attribute from an element<br>
						<strong>script: </strong>display JavaScript code of document<br>
						<strong>Other selectors: </strong>just try and hope it works ;)

						<br><br>

						<h4>Settings explained</h4>
						<strong>HTML purifier</strong><br>cleans dirty written HTML code by closing missing tags, adding missing quotes etc.<br> <i>note: using this option may break the functionality of the program</i><br>
						<strong>file_get_contents</strong><br>PHP standard method for downloading files to string. <a href="http://php.net/manual/en/function.file-get-contents.php" target="_blank">click here for more information</a><br>
						<strong>curl</strong><br>PHP supports libcurl, a library that allows you to connect to different types of servers. Curl is used here to fetch the document.<a href="http://php.net/manual/en/book.curl.php" target="_blank">click here for more information</a>

						<br><br>

						<h4>Regex</h4>
						<strong>Note: </strong>Please escape single quotes ' correctly and use // for the expression (<a href="http://php.net/manual/en/function.preg-match.php" target="_blank">PHP standards</a>)

						<br><br>

						<h4>API access</h4>
						<strong>Method :</strong> GET<br>
						<strong>URL: </strong>api.php<br>
						<strong>Parameters :</strong>
						<br>
						Example:
						<br>
						url:http://localhost/EasyScraper/tests/test.html<br>
						websiteName:<br>
						capture_method_1:selector<br>
						expression_1:a<br>
						field_name_1:a<br>
						capture_method_2:selector<br>
						expression_2:img<br>
						field_name_2:img<br>
						optn_request_method:file_get_contents<br>
						optn_user_agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13<br>
						optn_api_output: JSON<br>
						<strong>Avaliable output: </strong>txt, JSON
					</div>
				</div>
				<button type="submit" class="btn btn-black scrapeBtn">Scrape</button>
			</div>
		</form>
	</div>

	<?php
	if (!empty($scraper->getData())) { ?>
	<div class="container" id="resultTable">
		<h2>Results</h2>
		<?php for ($i=0; $i<count($scraper->getURL()); $i++) { //TODO: implement multiple URLs?>
		<table class="table table-bordered">
			<tr class="active">
				<th class="text-center"><?php echo $scraper->getWebsiteName(); ?></th>
				<th class="text-center">Data from <?php echo date('Y-m-d H:i:s'); ?></th>
			</tr>
			<?php for ($a=0; $a<count($scraper->getData()); $a++) { ?>
			<tr>
				<td class="text-center">
					<?php echo $scraper->get_field_names()[$a]; ?>
				</td>
				<td>
					<?php
						for ($b=0; $b<count($scraper->getData()[$a]); $b++) {
							echo $scraper->getData()[$a][$b] . "<br>";
						}
					?>
				</td>
			</tr>
			<?php } ?>
		</table>
		<?php } ?>
	</div>
	<?php } ?>
</body>
</html>
