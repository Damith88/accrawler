<?php
require_once dirname(__FILE__) . '/../lib/vendor/simplehtmldom/simple_html_dom.php';

// $url = "http://newsfirst.lk/english/2014/12/";
// $content = html_entity_decode(file_get_contents($url));

// file_put_contents('C:\Users\sdkaruna\Desktop\crawl\nFirst.txt', $content);

$content = html_entity_decode(file_get_contents('C:\Users\sdkaruna\Desktop\crawl\nFirst.txt'));

$html = str_get_html($content);
$titles = $html->find('section/div/div/div[1]/div/div.post-news/div.post_title/h3/a');

// $pagination = $html->find('/html/body/div[1]/section/div/div/div[1]/div/div.pagination/a');
// foreach ( $pagination as $page ) {	
// 	$text = trim($page->plaintext);
// 	if ($text === 'LAST') {
// 		$url = $page->href;
// 		//return this->getPostId($url);
// 	}
// }
//
//$articleCount = count($pagination);
//echo "$articleCount articles found.\n";

$articles = array();
foreach ( $titles as $title ) {
	$url = $title->href;
	$heading = trim($title->title);
	if ($heading != 'Cartoon of the day') {
		echo $url."\n";
		echo $heading."\n\n";
		$articles [] = array ($heading, $url);
	}
}
$articleCount = count($articles);
echo "$articleCount articles found.\n";
//return $articles;