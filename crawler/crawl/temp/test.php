<?php
require_once dirname(__FILE__) . '/../lib/vendor/simplehtmldom/simple_html_dom.php';

$rawHtmlContent = file_get_contents('http://newsfirst.lk/english/2014/10/sri-lanka-will-developed-nation-2035-treasury-dep-sec-watch-report/59835');
echo "DONE DOWNLOADING!";

$content = html_entity_decode(preg_replace('/<img[^>]+>/', '', $rawHtmlContent), ENT_QUOTES | ENT_HTML401);
$html = str_get_html($content);
$extractedContent = trim($html->find('div[id="post-59835"]', 0));

echo "DONE EXTRACTION!";

$extract1 = explode('<hr class="none" />', $extractedContent);
$extract2 = explode('<p>', $extract1[1]);
$newsContent = '';
foreach ($extract2 as $extractedPart) {
	$extractedPart = trim($extractedPart);
	$extract3 = explode('</p>', $extractedPart);
	if (isset($extract3[0]) && $extract3[0] !== '' ) {
		$newsContent .= $extract3[0]."\n";
	}
}

echo $newsContent;

//TODO : find HTML between <hr class="none" /> tags
?>