<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/WebCrawlerBase.php';

/**
 * NewsFirstCrawler is used to crawl throw http://newsfirst.lk news articles
 *
 * @author damith
 */
class NewsFirstCrawler extends WebCrawlerBase {
	
	private $outputDir = 'C:\Users\sdkaruna\Personal Data\My Projects\DamithFYP\newsfirst_html_pages';
	
	private function checkDate($date) {
		if (is_string($date)) {
			$ret = explode('-', $date);
		} else if ($date instanceof DateTime) {
			$y = $date->format('Y');
			$m = $date->format('m');
			$d = $date->format('d');
			$ret = array($y, $m, $d);
		} else {
			throw new Exception("invalid date");
		}
		return $ret;
	}

	protected function findArticlesForDate($date) {
		list($y, $m, $d) = $this->checkDate($date);
		
		$articles = array();		
		$pageUrl = "http://newsfirst.lk/english/$y/$m";
		$pageNo = 1;
		$lastPage = $this->getLastPageValue($pageUrl);		
		do {
			$content = html_entity_decode($this->retrieveSinglePage($pageUrl));
			
			$html = str_get_html($content);
			$titles = $html->find('section/div/div/div[1]/div/div.post-news/div.post_title/h3/a');	
			
			foreach ( $titles as $title ) {
				$url = $title->href;
				$heading = trim($title->title);
				$articles [] = array ($heading, $url);
			}
			$pageNo++;
			$pageUrl = "http://newsfirst.lk/english/$y/$m/page/$pageNo";
		} while ($lastPage >= $pageNo);
		
		$articleCount = count($articles);
		$this->logMessage("$articleCount articles found.\n");
		return $articles;
	}
	
	private function getLastPageValue($pageUrl) {
		$content = html_entity_decode($this->retrieveSinglePage($pageUrl));			
		$html = str_get_html($content);
		$pagination = $html->find('section/div/div/div[1]/div/div.pagination/a');
		
		foreach ( $pagination as $page ) {
			$text = trim($page->plaintext);
			if ($text === 'LAST') {
				$url = $page->href;
				return $this->getPostId ( $url ); //here we get value after last '/', in this case its last page id
			}
		}
		
		return 1;
	}

	public function execute($date, $resume = false) {
		$articles = $this->findArticlesForDate($date);
		if ($date instanceof DateTime) {
			$date = $date->format('Y-m-d');
		}

		$currDir = getCwd();
		chdir($this->outputDir);
		if (!is_dir($date)) {
			mkdir($date);
		}
		chdir($date);

		$articleId = 1;
		$articleArray = array();
		foreach ($articles as $article) {
			list($heading, $url) = $article;
			// waiting half a second before sending each request
			// this is done to avoid denial of service. (politness)
			//usleep(500000);
			$filename = "$articleId.html";
			if (!$resume || !is_file($filename)) {
				$rawContent = $this->retrieveSinglePage($url);
				file_put_contents($filename, $rawContent);
			} else {
				$rawContent = file_get_contents($filename);
			}
			$articleArray[] = array(
					'heading' => $heading,
					'url' => $url,
					'content' => $this->extractArticleContent($rawContent, $this->getPostId($url)),
					'content' => "test Content",
					'date' => $date
			);
			$articleId++;
		}

		$this->saveIndexFile($articles);

		// setting the current dir back to original dir
		chdir($currDir);
		
		return $this->saveArticles($articleArray);
	}

	protected function saveIndexFile($articles) {
		$fp = fopen('index.txt', 'w');
		foreach ($articles as $article) {
			list($heading, $url) = $article;
			fwrite($fp, "$heading\t$url\n");
		}
		fclose($fp);
	}
	
	private function getPostId($url) {
		return substr($url, strrpos($url, '/') + 1);
	}

	/**
	 * Given the raw html string returns extracted article content
	 * 
	 * @param string $rawHtmlContent
	 * @param string $postId
	 * @return string
	 */
	protected function extractArticleContent($rawHtmlContent, $postId = 1) {
		$content = html_entity_decode(preg_replace('/<img[^>]+>/', '', $rawHtmlContent), ENT_QUOTES | ENT_HTML401);
		$html = str_get_html($content);
		$extractedContent = trim($html->find('div[id="post-'.$postId.'"]', 0));
		
		$extract1 = explode('<hr class="none" />', $extractedContent);
		$extract2 = explode('<p>', $extract1[1]);
		$newsContent = '';
		foreach ($extract2 as $extractedPart) {
			$extractedPart = trim($extractedPart);
			$extract3 = explode('</p>', $extractedPart);
			if (isset($extract3[0]) && $extract3[0] !== '' && strpos($extract3[0], 'Watch report') === false) {
				$newsContent .= $extract3[0]."\n";
			}
		}
		
		return $newsContent;
	}
}
