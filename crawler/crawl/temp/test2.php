<?php

$extractedContent = file_get_contents('C:\Users\sdkaruna\Desktop\extrac.txt');



$ret = explode('<hr class="none" />', $extractedContent);
$ret2 = explode('<p>', $ret[1]);
$newsContent = '';
foreach ($ret2 as $extractedPart) {
	$extractedPart = trim($extractedPart);
	$ret3 = explode('</p>', $extractedPart);
	if (isset($ret3[0]) && $ret3[0] !== '' ) {
		$newsContent .= $ret3[0]."\n";
	}
}

echo $newsContent;
?>