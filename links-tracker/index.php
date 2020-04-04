<?

/* * * LOG FILES * * */

$jsonFile = 'log.json';
if (file_exists($jsonFile) === false) { die('Error: cannot find JSON log file.'); }
	
$txtFile = 'log.txt';
if (file_exists($txtFile) === false) { die('Error: cannot find text log file.'); }

/* * * CLEAR DATA * * */

if (isset($_GET["clear"]) && !isset($_POST["cleared"])) {
	echo 'Do you really want to clear the logs?'.PHP_EOL.'<form method="post"><input type="submit" name="cleared" value="confirm"></form>';
	exit;
} elseif (isset($_POST["cleared"])) {
	file_put_contents($jsonFile, '');
	file_put_contents($txtFile, 'NO DATA');
	echo 'The logs are cleared.';
	exit;
}

/* * * FETCH URL * * */

$url = isset($_GET["url"]) ? $_GET["url"] : false;

if (filter_var($url, FILTER_VALIDATE_URL) === false) {
	die('Error: missing a redirection URL');
}

/* * * FETCH CURRENT TIME * * */

date_default_timezone_set("Europe/Paris"); 
$downloadTime = date('d M Y H:i', time());

/* * * * * */

$currentDownload = [
	'url' => $url,
	'downloadTime' => $downloadTime,
];

/* * * FETCH JSON DATA * * */

$jsonData = file_get_contents($jsonFile);
$downloads = json_decode($jsonData, true);

if (!$downloads) { $downloads = []; }

/* * * CHECH IF LINK IS ALREADY IN DATA * * */

$newLink = true;
$currentDownloadKey = null;

foreach ($downloads as $key => $download) {
	if ($download['url'] == $currentDownload['url']) {
		$newLink = false;
		$currentDownloadKey = $key;
		break;
	}
}

/* * * IF LINK IS NEW, ADD TO DATA * * */

if ($newLink) {
	$newDownload = [
		'url' => $currentDownload['url'],
		'downloads' => 1,
		'firstDownload' => $currentDownload['downloadTime'],
		'lastDownload' => $currentDownload['downloadTime'],
	];
	array_push($downloads, $newDownload);
}

/* * * ELSE IF LINK IS ALREADY IN DATA, UPDATE LINK  * * */

elseif (!$newLink) {
	$downloads[$currentDownloadKey]['downloads'] += 1;
	$downloads[$currentDownloadKey]['lastDownload'] = $currentDownload['downloadTime'];
}

/* * * OVERWRITE JSON DATA * * */

function sortByNumericalValue($array, $value) {
	usort($array, function($a, $b) use ($value) {
		return $b[$value] - $a[$value];
	});
	return $array;
}

$downloads = sortByNumericalValue($downloads, 'downloads');

file_put_contents($jsonFile, json_encode($downloads));

/* * * COMPILE TXT LOG FILE * * */

// declare titles

$firstColumnTitle = 'URL';
$firstColumnLength = strlen($firstColumnTitle);

$secondColumnTitle = 'CLICKS';
$secondColumnLength = strlen($secondColumnTitle);

$thirdColumnTitle = 'FIRST DOWNLOAD';
$thirdColumnLength = strlen($thirdColumnTitle);

$fourthColumnTitle = 'LAST DOWNLOAD';
$fourthColumnLength = strlen($fourthColumnTitle);

// get totals

$totalLinks = count($downloads).' LINKS';

$totalClicks = 0;
foreach ($downloads as $download) {
	$totalClicks += $download['downloads'];
}
$totalClicks .= ' CLICKS';

// get columns length

foreach ($downloads as $download) {
	$urlLength = strlen($download['url']);
	if ($urlLength > $firstColumnLength) { $firstColumnLength = $urlLength; }

	$downloadsLength = strlen($download['downloads']);
	if ($downloadsLength > $secondColumnLength) { $secondColumnLength = $downloadsLength; }

	$firstDownloadLength = strlen($download['firstDownload']);
	if ($firstDownloadLength > $thirdColumnLength) { $thirdColumnLength = $firstDownloadLength; }

	$lastDownloadLength = strlen($download['lastDownload']);
	if ($lastDownloadLength > $fourthColumnLength) { $fourthColumnLength = $lastDownloadLength; }

	$totalLinksLength = strlen($totalLinks);
	if ($totalLinksLength > $firstColumnLength) { $firstColumnLength = $totalLinksLength; }

	$totalClicksLength = strlen($totalClicks);
	if ($totalClicksLength > $secondColumnLength) { $secondColumnLength = $totalClicksLength; }

}

$firstColumnLength += 4;
$secondColumnLength += 4;
$thirdColumnLength += 4;
$fourthColumnLength += 4;

// declare file

$dataSheet = "";

// write first row

$dataSheet .= $firstColumnTitle;
for ($x=strlen($firstColumnTitle); $x < $firstColumnLength; $x++) { $dataSheet .= ' '; }

$dataSheet .= $secondColumnTitle;
for ($x=strlen($secondColumnTitle); $x < $secondColumnLength; $x++) { $dataSheet .= ' '; }

$dataSheet .= $thirdColumnTitle;
for ($x=strlen($thirdColumnTitle); $x < $thirdColumnLength; $x++) { $dataSheet .= ' '; }

$dataSheet .= $fourthColumnTitle;
for ($x=strlen($fourthColumnTitle); $x < $fourthColumnLength; $x++) { $dataSheet .= ' '; }

$dataSheet .= PHP_EOL;

// write data rows

$dataSheet .= PHP_EOL;

foreach ($downloads as $download) {
	$dataSheet .= $download['url'];
	for ($x=strlen($download['url']); $x < $firstColumnLength; $x++) { $dataSheet .= ' '; }

	$dataSheet .= $download['downloads'];
	for ($x=strlen($download['downloads']); $x < $secondColumnLength; $x++) { $dataSheet .= ' '; }

	$dataSheet .= $download['firstDownload'];
	for ($x=strlen($download['firstDownload']); $x < $thirdColumnLength; $x++) { $dataSheet .= ' '; }

	$dataSheet .= $download['lastDownload'];
	for ($x=strlen($download['lastDownload']); $x < $fourthColumnLength; $x++) { $dataSheet .= ' '; }

	$dataSheet .= PHP_EOL;
}

// write last row

$dataSheet .= PHP_EOL;

$dataSheet .= $totalLinks;
for ($x=strlen($totalLinks); $x < $firstColumnLength; $x++) { $dataSheet .= ' '; }

$dataSheet .= $totalClicks;
for ($x=strlen($totalClicks); $x < $secondColumnLength; $x++) { $dataSheet .= ' '; }

/* * * OVERWRITE TXT LOG FILE * * */

file_put_contents($txtFile, $dataSheet);

/* * * REDIRECT TO URL * * */

header("Location: ".$url);

?>
